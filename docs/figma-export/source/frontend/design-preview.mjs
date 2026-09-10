import api from './src/utils/api.js';
import desktopCaptures from './design-captures.json';
import mobileCaptures from './design-mobile.json';
const captures=new URLSearchParams(location.search).has('mobile')?mobileCaptures:desktopCaptures;
const index=Number(new URLSearchParams(location.search).get('index')||0),capture=captures[index],state=capture.state;
const categories=['General Mathematics','Science','Reading Comprehension','Logical Reasoning','Digital Literacy'];
const before=['before','instructions'].includes(state);
const student={id:1,first_name:'Alex',last_name:'Santos',full_name:'Alex Santos',email:'student@example.invalid',student_number:'CDM-2026-00001',admission_year:'2026-2027',account_status:'active',exam_taken:!before,profile_picture:null,gender:'Female',contact_number:'09123456789',address:'Rodriguez, Rizal'};
const names=[['BSIT','Bachelor of Science in Information Technology'],['BSED-SCI','Bachelor of Secondary Education Major in Science'],['BSBA-HRM','Bachelor of Science in Business Administration Major in Human Resource Management'],['BEED-GEN','Bachelor of Elementary Education Major in General Education'],['BECED','Bachelor of Early Childhood Education'],['BTLED-ICT','Bachelor of Technology and Livelihood Education Major in ICT'],['BSCPE','Bachelor of Science in Computer Engineering'],['BSENTREP','Bachelor of Science in Entrepreneurship'],['TCP','Teacher Certificate Program']];
const courses=names.map(([code,name],i)=>({id:i+1,code,name,duration:code==='TCP'?'Varies':'4 years',description:'Explore the subjects, skills, and career opportunities associated with this program.',subjects:['Core foundations','Applied learning','Professional practice'],career_paths:code==='BSIT'?['Software developer','Systems analyst','Network administrator']:['Specialist roles in this field','Further study and professional practice'],is_active:true}));
const questions=Array.from({length:100},(_,i)=>({id:i+1,category:categories[Math.floor(i/20)],question_text:i===0?'A laptop originally costs ₱30,000. It has a 15% discount. What is the sale price?':`Sample assessment question ${i+1}`,option_a:'₱24,500',option_b:'₱25,500',option_c:'₱26,500',option_d:'₱27,500'}));
const ranked=courses.slice(0,3).map((c,i)=>({course_id:c.id,course_code:c.code,course_name:c.name,score:88-i*6,exam_match:85-i*5,interest_match:100-i*10}));
const recommendation={status:'ready',ranked_programs:ranked,tied_top_codes:['BSIT'],method:state==='ratings'?'Exam alignment only':'80% exam alignment + 20% stated interests',limitation:'Preliminary guidance, not admission eligibility or a probability of success. Short assessments provide limited evidence. The weighting has not been validated against student outcomes.',ai_status:state==='ratings'?'not_requested':'generated',ai_explanations:Object.fromEntries(ranked.map(p=>[p.course_code,{explanation:'Your assessment and interest ratings align with the categories weighted for this program.',next_step:'Explore the program subjects and discuss your options with a school adviser.'}])),evidence:Object.fromEntries(categories.map(c=>[c,{correct:17,total:20}]))};
const guest=['login','register','forgot','reset','download'].includes(state);
localStorage.clear();if(!guest)localStorage.setItem('auth_token','design-preview-only');
api.defaults.adapter=async config=>{
 const path=config.url;let data={},status=200;
 if(path.includes('/auth/profile'))data={success:true,student};
 else if(path.includes('/courses'))data={courses};
 else if(path.includes('/student/dashboard'))data={success:true,exam_taken:!before,student_number:student.student_number,admission_year:student.admission_year};
 else if(path.includes('/student/status'))data={status:before?'NOT_STARTED':'PASSED',attempts_used:before?0:1,timeline:before?[]:[{attempt:1,status:'PASSED',submitted_at:'2026-09-11T08:00:00Z'}],notifications:before?[]:[{id:1,message:'Your official result is available. Open Results to view it.',link:'/results',read_at:null}]};
 else if(path.includes('/exam/session')){if(state==='active')data={success:true,session_id:'preview',revision:0,attempt_number:1,position:0,exam_completed:false,time_limit:120,server_now:new Date().toISOString(),deadline:new Date(Date.now()+7200000).toISOString(),questions,answers:Object.fromEntries(questions.map(q=>[q.id,null]))};else status=404;}
 else if(path.includes('/exam/questions'))data={success:true,questions:questions.map(q=>({id:q.id,category:q.category})),total_questions:100,time_limit:120,passing_score:75};
 else if(path.includes('/exam/result')){if(before)status=404;else data={success:true,exam_completed:true,attempts_used:['failed','completed'].includes(state)?2:1,can_retake:state==='retake',official_status:state==='pending'?'pending':'published',message:'Your assessment has been submitted. Your official result status will appear after the Registrar publishes it.',official_result:state==='pending'?null:{outcome:state==='retake'?'RETAKE':state==='failed'?'FAILED':'PASSED',score:state==='retake'||state==='failed'?60:85,published_at:'2026-09-11T08:00:00Z',remarks:null}};}
 else if(path.includes('/results/recommendation')){if(before){status=403;data={message:'Complete the entrance exam first.'};}else data={recommendation,interests:state==='ratings'?{}:Object.fromEntries(categories.map(c=>[c,4]))};}
 else if(path.includes('/assistant/knowledge'))data={topics:[]};
 if(status>=400)throw {response:{status,data},config};return {data,status,statusText:'OK',headers:{},config};
};
history.replaceState(null,'',capture.path);
await import('./src/main.js');
await new Promise(r=>setTimeout(r,2500));
document.querySelectorAll('.scroll-reveal').forEach(n=>{n.style.opacity='1';n.style.transform='none'});
if(state==='programs')document.querySelectorAll('details').forEach(n=>n.open=true);
const script=document.createElement('script');script.src='https://mcp.figma.com/mcp/html-to-design/capture.js';document.head.appendChild(script);await new Promise((resolve,reject)=>{script.onload=resolve;script.onerror=reject});
await new Promise(r=>setTimeout(r,1000));
window.figma.captureForDesign({captureId:capture.id,endpoint:`https://mcp.figma.com/mcp/capture/${capture.id}/submit?bindVariables=true`,selector:'body'}).then(r=>window.captureResult=r).catch(e=>window.captureError=String(e));
