import { test, expect } from '@playwright/test';
const student={id:1,first_name:'Browser',last_name:'Test',full_name:'Browser Test',email:'browser@example.invalid',student_number:'BROWSER-1',admission_year:'TEST'};
const questions=Array.from({length:100},(_,i)=>({id:i+1,category:['General Mathematics','Science','Reading Comprehension','Logical Reasoning','Digital Literacy'][Math.floor(i/20)],question_text:`Browser question ${i+1}`,option_a:'First answer',option_b:'Second answer',option_c:'Third answer',option_d:'Fourth answer'}));
const fulfill=(route,data,status=200)=>route.fulfill({status,contentType:'application/json',body:JSON.stringify(data)});
async function authenticated(page,handler){
  await page.addInitScript(()=>{localStorage.setItem('auth_token','synthetic-browser-token');localStorage.setItem('admin_token','synthetic-admin-token')});
  await page.route('**/api/**',async route=>{
    const path=new URL(route.request().url()).pathname;
    if(path.endsWith('/auth/profile')&&!path.includes('/admin/'))return fulfill(route,{success:true,student});
    if(path.endsWith('/admin/auth/profile'))return fulfill(route,{admin:{id:1,name:'Registrar',role:'admissions_staff',status:'active'}});
    return handler(route,path);
  });
}
test('results failure shows retry instead of no completed exam',async({page})=>{
  let failed=true;await authenticated(page,(route,path)=>path.endsWith('/exam/result')?fulfill(route,failed?{message:'Temporary failure'}:{exam_completed:true,official_status:'published',official_result:{outcome:'PASSED',score:85}},failed?503:200):fulfill(route,{}));
  await page.goto('/results');await expect(page.getByRole('alert')).toContainText('Temporary failure');await expect(page.getByText('No completed exam yet')).toHaveCount(0);
  failed=false;await page.getByRole('button',{name:'Retry',exact:true}).click();await expect(page.getByRole('heading',{name:'PASSED',exact:true})).toBeVisible();
});
test('exam restores saved answers, saves edits, and reloads without restarting',async({page})=>{
  let session={success:true,session_id:'session',attempt_number:1,revision:0,position:0,exam_completed:false,deadline:new Date(Date.now()+3600000).toISOString(),questions,answers:Object.fromEntries(questions.map(q=>[q.id,q.id===1?'B':null]))};
  await authenticated(page,(route,path)=>{if(path.endsWith('/exam/session')){if(route.request().method()==='PUT'){const body=route.request().postDataJSON();session={...session,answers:body.answers,position:body.position,revision:session.revision+1}}return fulfill(route,{...session,server_now:new Date().toISOString()})}return fulfill(route,{},404)});
  await page.goto('/exam');await expect(page.getByRole('radio').nth(1)).toBeChecked();await expect(page.getByRole('button',{name:'Begin Examination',exact:true})).toHaveCount(0);
  await page.keyboard.press('c');await expect(page.getByRole('radio').nth(2)).toBeChecked();await expect.poll(()=>session.answers[1]).toBe('C');
  await page.reload();await expect(page.getByRole('radio').nth(2)).toBeChecked();await expect(page.getByText('Browser question 1',{exact:true})).toBeVisible();
});
test('Registrar pass displays PASSED without internal details or numeric score',async({page})=>{
  await authenticated(page,route=>fulfill(route,{exam_completed:true,official_status:'published',official_result:{outcome:'PASSED',score:null,percentage:null,remarks:null}}));
  await page.goto('/results');await expect(page.getByRole('heading',{name:'PASSED',exact:true})).toBeVisible();await expect(page.locator('.official-score')).toHaveCount(0);await expect(page.getByText('Passed by Registrar decision')).toHaveCount(0);
});
test('password recovery sends email and displays delivery status',async({page})=>{
  let email;await page.route('**/api/auth/forgot-password',route=>{email=route.request().postDataJSON().email;return fulfill(route,{message:'If eligible, a reset link will be sent.'})});
  await page.goto('/forgot-password');await page.getByLabel('Email',{exact:true}).fill('browser@example.invalid');await page.getByRole('button',{name:'Send reset link'}).click();await expect(page.getByRole('status')).toContainText('reset link');expect(email).toBe('browser@example.invalid');
});
test('Registrar can select pending rows and approve calculated scores',async({page})=>{
  let approved=false;const row={id:1,student,total_score:85,official_score:null,official_status:'pending',official_outcome:'PENDING',attempt_number:1,exam_date:new Date().toISOString(),result_version:'a'.repeat(64)};
  await authenticated(page,(route,path)=>{if(path.endsWith('/approve-batch')){approved=true;return fulfill(route,{message:'1 result approved'})}if(path.endsWith('/admin/results'))return fulfill(route,{data:[row],matching_ids:[1],total:1,current_page:1});return fulfill(route,{})});
  page.on('dialog',dialog=>dialog.accept());await page.goto('http://127.0.0.1:5190/results');await page.locator('select').nth(1).selectOption('pending');await page.getByRole('button',{name:'Select all matching pending results'}).click();await page.getByRole('button',{name:'Approve 1 selected using system scores'}).click();await expect.poll(()=>approved).toBe(true);
});

test('mobile recommendation menu requires a submitted exam and then permits guidance', async ({ page }) => {
  await page.setViewportSize({ width: 390, height: 844 });
  let completed = false;
  await authenticated(page, (route, path) => {
    if (path.endsWith('/results/recommendation')) return fulfill(route, completed ? {
      recommendation: { status: 'ready', method: 'Exam alignment only', limitation: 'Preliminary guidance.', tied_top_codes: ['BSIT'], ai_status: 'not_requested', ranked_programs: [{ course_code: 'BSIT', course_name: 'Information Technology', score: 80 }] }, interests: {}
    } : { message: 'Complete the entrance exam first.' }, completed ? 200 : 403);
    return fulfill(route, {});
  });
  await page.goto('/recommendation');
  await page.getByRole('button', { name: 'Toggle menu' }).click();
  await expect(page.locator('.mobile-nav').getByRole('link', { name: 'My Recommendation', exact: true })).toBeVisible();
  await page.locator('.mobile-nav').getByRole('link', { name: 'My Recommendation', exact: true }).click();
  await expect(page.getByRole('alert')).toContainText('Complete the entrance exam first.');
  await expect(page.getByRole('link', { name: 'Take the exam', exact: true })).toBeVisible();
  await expect(page.getByRole('button', { name: 'Generate My AI Guidance' })).toHaveCount(0);
  completed = true;
  await page.reload();
  await expect(page.getByRole('button', { name: 'Generate My AI Guidance' })).toBeVisible();
  await expect(page.getByRole('combobox')).toHaveCount(5);
  await expect(page.getByRole('heading', { name: 'Information Technology', exact: true })).toBeVisible();
});

test('narrow mobile dashboard keeps navigation and cards within viewport', async ({ page }) => {
  await page.setViewportSize({width:320,height:478});
  await authenticated(page,(route,path)=> {
    if(path.endsWith('/results/recommendation'))return fulfill(route,{recommendation:{status:'insufficient_evidence',message:'There is not enough verified exam evidence to rank programs. Please discuss your interests and assessment with a school adviser.'},interests:{}});
    if(path.endsWith('/student/dashboard'))return fulfill(route,{success:true,exam_taken:true});
    if(path.endsWith('/student/status'))return fulfill(route,{status:'PENDING',attempts_used:1,timeline:[],notifications:[]});
    return fulfill(route,{});
  });
  await page.goto('/dashboard');
  await expect(page.getByRole('link',{name:'Review my guidance',exact:true})).toBeVisible();
  await expect(page.getByRole('button',{name:'Toggle menu'})).toBeInViewport();
  await expect(page.getByRole('button',{name:'Logout',exact:true})).toBeInViewport();
  expect(await page.evaluate(()=>document.documentElement.scrollWidth <= window.innerWidth)).toBe(true);
  await page.getByRole('button',{name:'Toggle menu'}).click();
  await expect(page.locator('.mobile-nav').getByRole('link',{name:'My Recommendation',exact:true})).toBeVisible();
});
