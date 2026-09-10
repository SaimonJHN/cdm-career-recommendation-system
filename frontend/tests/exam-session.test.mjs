import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import test from 'node:test';
import assert from 'node:assert/strict';
const source=readFileSync(new URL('../src/pages/Exam.vue',import.meta.url),'utf8').split('<script setup>')[1].split('</script>')[0].replace(/^import .*;$/gm,'');
const questions=Array.from({length:100},(_,i)=>({id:i+1,category:['General Mathematics','Science','Reading Comprehension','Logical Reasoning','Digital Literacy'][Math.floor(i/20)],question_text:'Question'}));
function setup(server={session:null},now=0,storage=new Map()){
  let tick, posts=0;
  const response=()=>({data:structuredClone({success:true,...server.session,server_now:new Date(now).toISOString()})});
  const api={get:async path=>{
    if(path==='/auth/profile')return{data:{student:{id:1}}};
    if(path==='/exam/session'){if(!server.session||server.session.exam_completed)throw{response:{status:404}};return response()}
    if(path==='/exam/result'){if(!server.result)throw{response:{status:404}};return{data:server.result}}
    return{data:{questions,time_limit:120}};
  },post:async(path,body)=>{
    if(path==='/exam/session'){server.session??={session_id:'test-session',revision:0,attempt_number:server.result?.can_retake?2:1,questions,answers:Object.fromEntries(questions.map(q=>[q.id,null])),deadline:new Date(now+7200000).toISOString(),position:0,exam_completed:false};return response()}
    posts++;if(body.revision!==server.session.revision)throw{response:{status:409,data:{message:'Changed in another tab'}}};server.session.answers=structuredClone(body.answers);server.session.exam_completed=true;return response();
  },put:async(_,body)=>{if(body.revision!==server.session.revision)throw{response:{status:409,data:{message:'Changed in another tab'}}};server.session.answers=structuredClone(body.answers);server.session.position=body.position;server.session.revision++;return response()}};
  const Swal={fire:async()=>({isConfirmed:true}),close(){},isVisible:()=>false};
  const ctx=vm.createContext({ref:value=>({value}),computed:fn=>({get value(){return fn()}}),api,Swal,console:{error(){}},onMounted(){},onBeforeUnmount(){},Date:{now:()=>now,parse:Date.parse},setInterval:fn=>{tick=fn;return 1},clearInterval(){},setTimeout:()=>1,clearTimeout(){},localStorage:{getItem:key=>storage.get(key)||null,setItem:(key,value)=>storage.set(key,value),removeItem:key=>storage.delete(key)}});
  vm.runInContext(source+'\nglobalThis.exam={loadExam,startExam,submitExam,syncProgress,saveProgress,examStarted,examCompleted,answers,timeRemaining,currentQuestion,nextQuestion,previousQuestion,handleAnswerKey,loadError,progressWarning,submitting,showAnswerList};',ctx);
  return{...ctx.exam,api,Swal,server,advance:ms=>now+=ms,tick:()=>tick(),posts:()=>posts};
}
test('start requires loaded instructions and creates a server session',async()=>{const e=setup();await e.startExam();assert.equal(e.examStarted.value,false);await e.loadExam();await e.startExam();assert.equal(e.examStarted.value,true);assert.equal(e.timeRemaining.value,7200)});
test('answers and position survive another device without browser storage',async()=>{const server={session:null};const a=setup(server);await a.loadExam();await a.startExam();a.answers.value[1]='C';a.nextQuestion();await a.syncProgress();const b=setup(server,600000);await b.loadExam();assert.equal(b.answers.value[1],'C');assert.equal(b.currentQuestion.value.id,2);assert.equal(b.timeRemaining.value,6600)});
test('browser storage failure still allows server saves',async()=>{const storage=new Map();storage.set=()=>{throw Error('blocked')};const e=setup({session:null},0,storage);await e.loadExam();await e.startExam();e.answers.value[1]='A';e.saveProgress();await e.syncProgress();assert.equal(e.server.session.answers[1],'A')});
test('stale revisions show conflict rather than completion',async()=>{const e=setup();await e.loadExam();await e.startExam();e.server.session.revision++;await e.submitExam();assert.equal(e.examCompleted.value,false);assert.ok(e.loadError.value)});
test('offline save preserves answers and reports unsaved changes',async()=>{const e=setup();await e.loadExam();await e.startExam();e.answers.value[1]='B';e.api.put=async()=>{throw Error('offline')};await assert.rejects(e.syncProgress());assert.equal(e.answers.value[1],'B');assert.ok(e.progressWarning.value)});
test('retake has its own final session and cannot submit twice',async()=>{const e=setup({session:null,result:{exam_completed:true,attempts_used:1,can_retake:true}});await e.loadExam();await e.startExam();assert.equal(e.server.session.attempt_number,2);await e.submitExam();assert.equal(e.examCompleted.value,true);await e.submitExam();assert.equal(e.posts(),1)});
test('exhausted attempts cannot start again',async()=>{const e=setup({session:null,result:{exam_completed:true,attempts_used:2,can_retake:false}});await e.loadExam();await e.startExam();assert.equal(e.examCompleted.value,true);assert.equal(e.server.session,null)});
test('keyboard navigation keeps answers and respects dialogs',async()=>{const e=setup();await e.loadExam();await e.startExam();e.handleAnswerKey({key:'B',preventDefault(){}});assert.equal(e.answers.value[1],'B');for(let i=0;i<20;i++)e.nextQuestion();assert.equal(e.currentQuestion.value.category,'Science');e.showAnswerList.value=true;e.handleAnswerKey({key:'A',preventDefault(){throw Error('locked')}});assert.equal(e.answers.value[21],null)});
test('cancel submission leaves session active',async()=>{const e=setup();await e.loadExam();await e.startExam();e.Swal.fire=async()=>({isConfirmed:false});await e.submitExam();assert.equal(e.posts(),0);assert.equal(e.examStarted.value,true)});
