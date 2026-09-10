import api from './src/utils/api.js';
import captures from './design-captures.json';
const index=Number(new URLSearchParams(location.search).get('index')||0),capture=captures[index],state=capture.state;
localStorage.clear();if(state!=='login')localStorage.setItem('admin_token','design-preview-only');
const admin={id:1,name:'System Administrator',email:'admin@example.invalid',role:'super_admin',status:'active'};
const student={id:1,first_name:'Alex',last_name:'Santos',full_name:'Alex Santos',email:'student@example.invalid',student_number:'CDM-2026-00001',admission_year:'2026-2027',account_status:'active',exam_taken:true,exam_score:85,recommendation:{course:{name:'Bachelor of Science in Information Technology'}}};
const course={id:1,code:'BSIT',name:'Bachelor of Science in Information Technology',duration:'4 years',program_type:'degree',institute:'Institute of Computer Studies',is_active:true,is_recommendable:true,recommendations_count:1,description:'Develop skills in software, networks, databases, cybersecurity, and emerging digital technologies.',subjects:['Programming','Networking','Database systems'],career_paths:['Software developer','Systems analyst'],display_order:1};
const q={id:1,question_number:1,question_text:'A laptop originally costs ₱30,000. It has a 15% discount. What is the sale price?',category:'General Mathematics',difficulty_level:'easy',correct_answer:'B',option_a:'₱24,500',option_b:'₱25,500',option_c:'₱26,500',option_d:'₱27,500',is_active:true};
const result={id:1,student,total_score:state==='decision'?60:85,official_score:state==='decision'?60:null,official_status:state==='decision'?'published':'pending',official_outcome:state==='decision'?'FAILED':'PENDING',attempt_number:state==='decision'?2:1,exam_date:'2026-09-11T08:00:00Z',result_version:'preview'};
const paged=data=>({data,current_page:1,last_page:1,total:data.length,prev_page_url:null,next_page_url:null});
api.defaults.adapter=async config=>{let data={},path=config.url;
if(path.includes('/admin/auth/profile'))data={admin};
else if(path.includes('/admin/dashboard'))data={stats:{students:100,active_students:98,exam_submissions:80,exam_students:70,pending_results:12,passed:50,programs:9,questions:100},latest_results:[result],recommendation_summary:{generated_students:48,ai_generated_students:45,top_programs:[{code:'BSIT',students:18},{code:'BSBA-HRM',students:15},{code:'BSED-SCI',students:15}]}};
else if(path.endsWith('/admin/students/1'))data={student};
else if(path.includes('/admin/students'))data=paged([student]);
else if(path.includes('/admin/programs')||path.includes('/courses'))data={courses:[course]};
else if(path.includes('/admin/questions'))data=paged([q]);
else if(path.includes('/admin/results/import'))data={message:'Preview ready. Review before saving.',preview_id:'preview',rows:[{applicant_number:student.student_number,result_id:1,old_score:null,score:85}],errors:[]};
else if(path.includes('/admin/results'))data={...paged([result]),matching_ids:[1],eligible_ids:[1]};
else if(path.includes('/admin/admins'))data={admins:[admin]};
else if(path.includes('/admin/student-logs'))data=paged([{id:1,student,action:'login',auth_method:'email_otp',ip_address:'192.0.2.1',user_agent:'Chrome/ Windows',created_at:'2026-09-11T08:00:00Z'}]);
else if(path.includes('/admin/activity-logs'))data=paged([{id:1,admin,action:'result.published',subject_type:'ExamResult',subject_id:1,ip_address:'192.0.2.1',created_at:'2026-09-11T08:00:00Z'}]);
else if(path.includes('/admin/operations'))data={database:'OK',scheduler_ok:false,scheduler_last_run:null,latest_backup:null,pending_email_count:0,failed_emails:[]};
return {data,status:200,statusText:'OK',headers:{},config};};
history.replaceState(null,'',capture.path);await import('./src/main.js');await new Promise(r=>setTimeout(r,2500));
const click=text=>Array.from(document.querySelectorAll('button')).find(b=>b.textContent.trim()===text)?.click();
if(state==='student-detail')click('View');
if(state==='program-edit'||state==='question-edit')click('Edit');
if(state==='invite')click('Invite administrator');
if(state==='decision'){document.querySelector('.decision-filter input')?.click();await new Promise(r=>setTimeout(r,500));click('Select all eligible');const area=document.querySelector('textarea');if(area){area.value='Registrar reviewed the final attempt and supporting records.';area.dispatchEvent(new Event('input',{bubbles:true}));}}
if(state==='import'){const input=document.querySelector('input[type=file]'),dt=new DataTransfer();dt.items.add(new File(['applicant_number,result_id,result_version,score\nCDM-2026-00001,1,preview,85'],'results.csv',{type:'text/csv'}));input.files=dt.files;input.dispatchEvent(new Event('change',{bubbles:true}));await new Promise(r=>setTimeout(r,300));click('Preview import');}
await new Promise(r=>setTimeout(r,800));const s=document.createElement('script');s.src='https://mcp.figma.com/mcp/html-to-design/capture.js';document.head.appendChild(s);await new Promise((r,j)=>{s.onload=r;s.onerror=j});await new Promise(r=>setTimeout(r,1000));
window.figma.captureForDesign({captureId:capture.id,endpoint:`https://mcp.figma.com/mcp/capture/${capture.id}/submit?bindVariables=true`,selector:'body'});


