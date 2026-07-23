let audioCtx = null;
let jaVoice = null;
let repeatTimer = null;
let speakTimer = null;
let currentGame = '';
let currentQuestion = null;
let answered = false;
let audioReady = false;
let orderState = null;

const $ = (id) => document.getElementById(id);
const pick = (arr) => arr[Math.floor(Math.random() * arr.length)];
function shuffle(arr){ return arr.map(v=>[Math.random(),v]).sort((a,b)=>a[0]-b[0]).map(x=>x[1]); }

const games = {
  onaji: { title:'おなじもの', make: makeOnaji },
  shape: { title:'かたち', make: makeShape },
  ookii: { title:'おおきさ', make: makeOokii },
  animal: { title:'どうぶつ', make: makeAnimal },
  nakama: { title:'なかまはずれ', make: makeNakama },
  order: { title:'じゅんばん', make: makeOrder },
  iro: { title:'いろ', make: makeIro }
};

function prepareSpeech(){
  try{
    if(!('speechSynthesis' in window)) return;
    const voices = speechSynthesis.getVoices() || [];
    jaVoice = voices.find(v => v.lang === 'ja-JP') || voices.find(v => (v.lang || '').startsWith('ja')) || null;
  }catch(e){}
}

function prepareAudio(){
  try{
    if(!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
    if(audioCtx.state === 'suspended') audioCtx.resume();
  }catch(e){}
}

function unlockAudio(){
  audioReady = true;
  prepareAudio();
  prepareSpeech();
}

function speak(text, opts={}){
  if(!text) return;
  try{
    prepareSpeech();
    speechSynthesis.cancel();
    const u = new SpeechSynthesisUtterance(text);
    u.lang = 'ja-JP';
    u.rate = opts.rate || 1.02;
    u.pitch = opts.pitch || 1.16;
    u.volume = 1;
    if(jaVoice) u.voice = jaVoice;
    speechSynthesis.speak(u);
  }catch(e){}
}

function speakNow(text){
  clearTimeout(speakTimer);
  speak(text, {rate:1.02});
}

function speakInstruction(){
  if(currentQuestion && !answered) speakNow(currentQuestion.instruction);
}

function startVoiceLoop(delay=80){
  stopVoiceLoop();
  speakTimer = setTimeout(speakInstruction, delay);
  repeatTimer = setInterval(() => {
    if(!answered) speakInstruction();
  }, 5200);
}

function stopVoiceLoop(){
  clearTimeout(speakTimer);
  if(repeatTimer){ clearInterval(repeatTimer); repeatTimer = null; }
}

function tone(type){
  try{
    prepareAudio();
    if(!audioCtx) return;
    const t = audioCtx.currentTime;
    const o = audioCtx.createOscillator();
    const g = audioCtx.createGain();
    o.connect(g); g.connect(audioCtx.destination);
    if(type === 'ok'){
      o.frequency.setValueAtTime(880, t);
      o.frequency.setValueAtTime(1320, t + .09);
      o.frequency.setValueAtTime(1560, t + .18);
    }else{
      o.frequency.setValueAtTime(180, t);
      o.frequency.setValueAtTime(130, t + .16);
    }
    g.gain.setValueAtTime(.28, t);
    g.gain.exponentialRampToValueAtTime(.01, t + .32);
    o.start(t); o.stop(t + .32);
  }catch(e){}
}

function showMenu(){
  stopVoiceLoop();
  currentGame = '';
  $('gameScreen').classList.add('hidden');
  $('menuScreen').classList.remove('hidden');
  history.replaceState(null, '', 'menu.php?v=' + (window.GAME_VER || Date.now()));
}

function openGame(game){
  unlockAudio();
  currentGame = game;
  $('menuScreen').classList.add('hidden');
  $('gameScreen').classList.remove('hidden');
  $('gameTitle').textContent = games[game].title;
  history.replaceState(null, '', 'menu.php?game=' + encodeURIComponent(game) + '&v=' + (window.GAME_VER || Date.now()));
  nextQuestion(true);
}

function nextQuestion(first=false){
  if(!currentGame) return;
  answered = false;
  $('msg').textContent = '';
  $('msg').style.color = '';
  currentQuestion = games[currentGame].make();
  renderQuestion(currentQuestion);
  startVoiceLoop(first ? 90 : 180);
  if(!audioReady){
    // 直接URLを開いた場合の保険。メニューから開いた場合は表示されません。
    setTimeout(() => {
      if(!audioReady && currentGame && !answered) $('tapStart').classList.remove('hidden');
    }, 700);
  }
}

function renderQuestion(q){
  $('targetArea').innerHTML = q.target || '';
  $('choiceArea').className = q.choiceClass || 'choices';
  $('choiceArea').innerHTML = '';
  q.choices.forEach(ch => {
    const b = document.createElement('button');
    b.className = 'btn';
    if(ch.style) b.setAttribute('style', ch.style);
    b.innerHTML = ch.html;
    b.addEventListener('click', () => handleChoice(ch.value));
    $('choiceArea').appendChild(b);
  });
}

function disableButtons(){ document.querySelectorAll('#choiceArea .btn').forEach(b => b.disabled = true); }

function handleChoice(value){
  unlockAudio();
  if(answered || !currentQuestion) return;
  if(currentQuestion.type === 'order') return handleOrder(value);
  if(value === currentQuestion.answer) correct(); else wrong();
}

function correct(){
  if(answered) return;
  answered = true;
  stopVoiceLoop();
  disableButtons();
  $('msg').textContent = 'すごい！';
  $('msg').style.color = '#ff7a00';
  tone('ok');
  setTimeout(() => speakNow('すごい。せいかい。つぎのもんだいだよ。'), 120);
  setTimeout(() => nextQuestion(false), 2100);
}

function wrong(){
  $('msg').textContent = 'もういちど！';
  $('msg').style.color = '#4b8bff';
  tone('ng');
  setTimeout(() => speakNow('もういちど。' + (currentQuestion ? currentQuestion.instruction : '')), 180);
}

function makeOnaji(){
  const items=[['🚗','くるま'],['🐶','いぬ'],['🍎','りんご'],['⭐','ほし'],['🐱','ねこ'],['🚌','バス']];
  const ans = pick(items);
  const choices = shuffle(items).map(i => ({html:i[0], value:i[1]}));
  return {answer:ans[1], instruction:'おなじものを、さがしてね。' + ans[1] + 'はどれかな。', target:`<div class="target-box"><div class="big-emoji">${ans[0]}</div></div>`, choices};
}

function makeShape(){
  const shapes=[
    ['まる','<div class="shape-circle"></div>'],
    ['さんかく','<div class="shape-triangle"></div>'],
    ['しかく','<div class="shape-square"></div>'],
    ['ほし','<div class="shape-star">★</div>']
  ];
  const ans=pick(shapes);
  return {answer:ans[0], instruction:ans[0]+'を、タップしてね。', choices:shuffle(shapes).map(s=>({html:s[1], value:s[0]}))};
}

function makeOokii(){
  const ans = Math.random() < .5 ? 'おおきい' : 'ちいさい';
  const left = Math.random() < .5 ? 'おおきい' : 'ちいさい';
  const right = left === 'おおきい' ? 'ちいさい' : 'おおきい';
  const mk = v => `<div class="size-box"><div class="${v==='おおきい'?'circle-big':'circle-small'}"></div></div>`;
  return {answer:ans, instruction:ans+'ほうを、タップしてね。', choices:[{html:mk(left), value:left},{html:mk(right), value:right}]};
}

function makeAnimal(){
  const animals=[['🐶','いぬ','わんわん'],['🐱','ねこ','にゃーにゃー'],['🐮','うし','もーもー'],['🐸','かえる','けろけろ'],['🐤','ひよこ','ぴよぴよ'],['🐷','ぶた','ぶーぶー']];
  const ans=pick(animals);
  return {answer:ans[1], instruction:ans[2]+'。'+ans[1]+'はどれかな。', choices:shuffle(animals).map(a=>({html:a[0], value:a[1]}))};
}

function makeNakama(){
  const sets=[
    [['🍎','たべもの'],['🍌','たべもの'],['🍇','たべもの'],['🚗','のりもの']],
    [['🚗','のりもの'],['🚌','のりもの'],['🚲','のりもの'],['🐶','どうぶつ']],
    [['🐶','どうぶつ'],['🐱','どうぶつ'],['🐮','どうぶつ'],['⭐','もの']],
    [['⭐','かたち'],['🔺','かたち'],['🔵','かたち'],['🍎','たべもの']]
  ];
  const q=pick(sets);
  const counts={}; q.forEach(x=>counts[x[1]]=(counts[x[1]]||0)+1);
  const ans=q.find(x=>counts[x[1]]===1)[0];
  return {answer:ans, instruction:'ひとつだけ、ちがうものは、どれかな。', choices:shuffle(q).map(x=>({html:x[0], value:x[0]}))};
}

function makeIro(){
  const colors=[['あか','#ff4b4b','🔴'],['あお','#4b8bff','🔵'],['きいろ','#ffd93d','🟡'],['みどり','#4cd964','🟢'],['むらさき','#a66cff','🟣'],['オレンジ','#ff9500','🟠']];
  const ans=pick(colors);
  return {answer:ans[0], instruction:ans[0]+'を、タップしてね。', choices:shuffle(colors).map(c=>({html:c[2], value:c[0], style:'background:'+c[1]}))};
}

function makeOrder(){
  const items=['🍎','🚗','⭐','🐶','🍌','🚌'];
  const seq=shuffle(items).slice(0,2);
  const choices=shuffle(seq.concat(shuffle(items.filter(x=>!seq.includes(x))).slice(0,2)));
  orderState = {seq, pos:0, phase:'show'};
  const q = {type:'order', answer:'', instruction:'じゅんばんを、おぼえてね。', target:`<div id="seqBox" class="seq"><span>${seq[0]}</span><span>${seq[1]}</span></div>`, choices:[]};
  setTimeout(() => {
    if(currentQuestion !== q || answered) return;
    orderState.phase='answer';
    q.instruction='おなじじゅんばんで、タップしてね。';
    q.choices=choices.map(c=>({html:c, value:c}));
    q.target='';
    renderQuestion(q);
    startVoiceLoop(80);
  }, 2600);
  return q;
}

function handleOrder(value){
  if(!orderState || orderState.phase !== 'answer') return;
  if(value === orderState.seq[orderState.pos]){
    tone('ok');
    orderState.pos++;
    if(orderState.pos >= orderState.seq.length) correct();
  }else{
    orderState.pos = 0;
    wrong();
  }
}

document.addEventListener('DOMContentLoaded', () => {
  prepareSpeech();
  document.querySelectorAll('.menu-btn').forEach(btn => btn.addEventListener('click', () => openGame(btn.dataset.game)));
  $('backMenu').addEventListener('click', showMenu);
  $('restartGame').addEventListener('click', () => { unlockAudio(); nextQuestion(true); });
  $('tapStart').addEventListener('click', () => { unlockAudio(); $('tapStart').classList.add('hidden'); startVoiceLoop(40); });
  document.addEventListener('pointerdown', () => { if(currentGame) unlockAudio(); }, {capture:true});
  if(window.START_GAME && games[window.START_GAME]){
    // 直接 game パラメータで開いた場合だけ。メニュー経由では openGame が使われる。
    currentGame = window.START_GAME;
    $('menuScreen').classList.add('hidden');
    $('gameScreen').classList.remove('hidden');
    $('gameTitle').textContent = games[currentGame].title;
    nextQuestion(true);
  }
});

if('speechSynthesis' in window){
  speechSynthesis.onvoiceschanged = prepareSpeech;
}
