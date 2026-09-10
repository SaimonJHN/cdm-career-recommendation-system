import fs from 'node:fs';
const tabs=await (await fetch('http://localhost:9334/json/list')).json();
const tab=tabs.find(t=>t.type==='page'&&t.url.includes(process.env.DESIGN_HOST || 'localhost:5173'));
const ws=new WebSocket(tab.webSocketDebuggerUrl);
await new Promise(r=>ws.addEventListener('open',r,{once:true}));
ws.send(JSON.stringify({id:1,method:process.argv[2],params:process.argv[2].startsWith('Emulation.')?JSON.parse(fs.readFileSync('design-params.json','utf8')):['Page.navigate','Target.createTarget'].includes(process.argv[2])?{url:process.argv[3]}:{expression:process.argv[3]==='@file'?fs.readFileSync('design-expression.js','utf8'):process.argv[3],returnByValue:true}}));
await new Promise(r=>ws.addEventListener('message',e=>{const d=JSON.parse(e.data);if(d.id===1){console.log(JSON.stringify(d));ws.close();r();}}));


