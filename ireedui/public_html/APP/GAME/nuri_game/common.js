const VERSION = '20260607_04';
const state = { color:'#ff4b8b', character:null, painted:0, audio:null, bgmTimer:null };

const messages = [
  'わあ、きれいだね。',
  'そのいろ、すてきだよ。',
  'じょうずにぬれたね。',
  'かわいくなったね。',
  'もっとぬってみよう。'
];

const colorNames = {
  '#ff4b8b':'ピンク', '#ff3b30':'あか', '#ffcc00':'きいろ', '#34c759':'みどり', '#007aff':'あお',
  '#af52de':'むらさき', '#ff9500':'オレンジ', '#8e5a2b':'ちゃいろ', '#111111':'くろ', '#ffffff':'しろ'
};

const charInfo = {
  niko:{ name:'にこちゃん', guide:'にこちゃんを、すきないろでぬってね。' },
  pota:{ name:'ぽたちゃん', guide:'ぽたちゃんを、すきないろでぬってね。' },
  pika:{ name:'ぴかちゃん', guide:'ぴかちゃんを、すきないろでぬってね。' },
  pan:{ name:'ぱんくん', guide:'ぱんくんを、すきないろでぬってね。' },
  ribo:{ name:'りぼちゃん', guide:'りぼちゃんを、すきないろでぬってね。' }
};

function speak(text, rate=1.08, volume=1){
  try{
    if(!('speechSynthesis' in window)) return;
    speechSynthesis.cancel();
    const u = new SpeechSynthesisUtterance(text);
    u.lang = 'ja-JP';
    u.rate = rate;
    u.pitch = 1.25;
    u.volume = volume;
    const voices = speechSynthesis.getVoices();
    const ja = voices.find(v => v.lang && v.lang.toLowerCase().startsWith('ja'));
    if(ja) u.voice = ja;
    speechSynthesis.speak(u);
  }catch(e){}
}

function initAudio(){
  if(state.audio) return state.audio;
  const AudioContext = window.AudioContext || window.webkitAudioContext;
  if(!AudioContext) return null;
  const ctx = new AudioContext();
  state.audio = ctx;
  return ctx;
}

async function resumeAudio(){
  const ctx = initAudio();
  if(ctx && ctx.state === 'suspended'){
    try{ await ctx.resume(); }catch(e){}
  }
  startBgm();
}

function tone(freq, dur=0.12, vol=0.035, when=0, type='sine'){
  const ctx = initAudio();
  if(!ctx) return;
  const osc = ctx.createOscillator();
  const gain = ctx.createGain();
  osc.type = type;
  osc.frequency.setValueAtTime(freq, ctx.currentTime + when);
  gain.gain.setValueAtTime(0.0001, ctx.currentTime + when);
  gain.gain.exponentialRampToValueAtTime(vol, ctx.currentTime + when + 0.02);
  gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + when + dur);
  osc.connect(gain).connect(ctx.destination);
  osc.start(ctx.currentTime + when);
  osc.stop(ctx.currentTime + when + dur + 0.03);
}

function startBgm(){
  if(state.bgmTimer) return;
  const loop = () => {
    const ctx = initAudio();
    if(!ctx || ctx.state !== 'running') return;
    const notes = [523,659,784,659,587,698,880,698];
    notes.forEach((n,i)=>tone(n,0.18,0.010,i*0.24));
  };
  loop();
  state.bgmTimer = setInterval(loop, 2600);
}

function paintSound(){
  tone(880,0.07,0.045,0);
  tone(1175,0.08,0.035,0.07);
}

function setTalk(text, shouldSpeak=true){
  const el = document.getElementById('talkText');
  if(el) el.textContent = text;
  if(shouldSpeak) speak(text, 1.08, 1);
}

function svgDefs(){
  return `<defs>
    <linearGradient id="gWhite" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffffff"/><stop offset="1" stop-color="#f2f2f2"/></linearGradient>
    <linearGradient id="gPink" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ffe4f0"/><stop offset="1" stop-color="#ff9fc3"/></linearGradient>
    <linearGradient id="gBlue" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ddf5ff"/><stop offset="1" stop-color="#54b7ff"/></linearGradient>
    <linearGradient id="gYellow" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff6a4"/><stop offset="1" stop-color="#ffc43b"/></linearGradient>
    <linearGradient id="gCream" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#fff0ce"/><stop offset="1" stop-color="#e9a95b"/></linearGradient>
    <linearGradient id="gPurple" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#f0ddff"/><stop offset="1" stop-color="#bb73df"/></linearGradient>
  </defs>`;
}

function faceParts(){
  return `
    <circle class="part" data-part="face" cx="170" cy="130" r="74" fill="url(#gWhite)"/>
    <ellipse class="eye" cx="142" cy="125" rx="10" ry="15"/><ellipse class="eye" cx="198" cy="125" rx="10" ry="15"/>
    <circle class="shine" cx="145" cy="119" r="3.5"/><circle class="shine" cx="201" cy="119" r="3.5"/>
    <circle class="cheek" cx="119" cy="148" r="14"/><circle class="cheek" cx="221" cy="148" r="14"/>
    <path class="mouth" d="M148 160 Q170 181 192 160"/>`;
}

function baseBody(){
  return `
    <path class="part" data-part="body" d="M105 210 Q170 175 235 210 L255 310 Q170 342 85 310 Z" fill="url(#gWhite)"/>
    <path class="part" data-part="left-arm" d="M102 226 Q65 242 52 270 Q70 286 102 258" fill="url(#gWhite)"/>
    <path class="part" data-part="right-arm" d="M238 226 Q275 242 288 270 Q270 286 238 258" fill="url(#gWhite)"/>
    <path class="part" data-part="left-leg" d="M125 303 L112 342 Q132 352 149 320" fill="url(#gWhite)"/>
    <path class="part" data-part="right-leg" d="M215 303 L228 342 Q208 352 191 320" fill="url(#gWhite)"/>`;
}

function baseSvg(kind){
  const defs = svgDefs();
  const face = faceParts();
  const body = baseBody();
  if(kind==='niko') return `${defs}${body}
    <path class="part" data-part="hood" d="M92 118 Q112 36 170 42 Q228 36 248 118 Q240 202 170 208 Q100 202 92 118 Z" fill="url(#gPink)"/>
    ${face}
    <path class="part" data-part="heart" d="M158 30 C135 8 105 28 124 58 L170 100 L216 58 C235 28 205 8 182 30 L170 42 Z" fill="#ff7bac"/>
    <path class="part" data-part="mark" d="M170 250 C154 230 128 246 143 270 L170 294 L197 270 C212 246 186 230 170 250 Z" fill="url(#gWhite)"/>`;

  if(kind==='pota') return `${defs}${body}
    <path class="part" data-part="drop" d="M170 20 C108 82 76 128 92 174 Q109 230 170 230 Q231 230 248 174 C264 128 232 82 170 20 Z" fill="url(#gBlue)"/>
    ${face}
    <path class="part" data-part="top-drop" d="M203 21 C195 34 195 43 205 49 C215 43 215 34 203 21 Z" fill="#a9e3ff"/>
    <path class="part" data-part="chest" d="M170 244 C150 266 142 286 170 300 C198 286 190 266 170 244 Z" fill="url(#gWhite)"/>`;

  if(kind==='pika') return `${defs}
    <path class="part" data-part="star-body" d="M170 25 L207 89 L280 100 L228 151 L241 225 L170 190 L99 225 L112 151 L60 100 L133 89 Z" fill="url(#gYellow)"/>
    <path class="part" data-part="body" d="M105 210 Q170 175 235 210 L255 310 Q170 342 85 310 Z" fill="url(#gYellow)"/>
    <path class="part" data-part="left-arm" d="M102 226 Q65 242 52 270 Q70 286 102 258" fill="url(#gYellow)"/>
    <path class="part" data-part="right-arm" d="M238 226 Q275 242 288 270 Q270 286 238 258" fill="url(#gYellow)"/>
    <path class="part" data-part="left-leg" d="M125 303 L112 342 Q132 352 149 320" fill="url(#gYellow)"/>
    <path class="part" data-part="right-leg" d="M215 303 L228 342 Q208 352 191 320" fill="url(#gYellow)"/>
    ${face}
    <path class="part" data-part="star" d="M170 232 L184 260 L216 264 L193 286 L199 318 L170 303 L141 318 L147 286 L124 264 L156 260 Z" fill="url(#gWhite)"/>`;

  if(kind==='pan') return `${defs}${body}
    <path class="part" data-part="cape" d="M92 222 Q50 263 46 330 Q89 302 121 268" fill="#ff4b4b"/>
    <circle class="part" data-part="head" cx="170" cy="128" r="82" fill="url(#gCream)"/>
    <path class="part" data-part="hair" d="M118 62 Q145 42 170 58 Q195 42 222 62 Q207 78 170 76 Q133 78 118 62 Z" fill="#b66a25"/>
    ${face.replace('fill="url(#gWhite)"','fill="#ffe6c8"')}
    <circle class="part" data-part="nose" cx="170" cy="144" r="16" fill="#ff6b00"/>
    <path class="part" data-part="belt" d="M102 285 Q170 306 238 285 L238 302 Q170 322 102 302 Z" fill="url(#gWhite)"/>
    <circle class="part" data-part="button" cx="170" cy="264" r="14" fill="#ffcc00"/>`;

  return `${defs}${body}
    <path class="part" data-part="left-ear" d="M105 105 C92 64 98 20 119 12 C136 6 148 28 145 69 C142 102 130 128 116 132 C111 133 108 123 105 105 Z" fill="url(#gWhite)"/>
    <path class="part" data-part="right-ear" d="M235 105 C248 64 242 20 221 12 C204 6 192 28 195 69 C198 102 210 128 224 132 C229 133 232 123 235 105 Z" fill="url(#gWhite)"/>
    <path class="part" data-part="left-inner-ear" d="M116 100 C108 70 112 34 124 28 C133 24 139 39 137 69 C135 94 129 114 121 116 C118 117 117 110 116 100 Z" fill="#ffd7e8"/>
    <path class="part" data-part="right-inner-ear" d="M224 100 C232 70 228 34 216 28 C207 24 201 39 203 69 C205 94 211 114 219 116 C222 117 223 110 224 100 Z" fill="#ffd7e8"/>
    <path class="part" data-part="hood" d="M87 122 Q107 50 170 50 Q233 50 253 122 Q243 207 170 214 Q97 207 87 122 Z" fill="url(#gPink)"/>
    ${face}
    <path class="part" data-part="ribbon-left" d="M188 60 C205 44 224 50 225 73 C209 83 193 79 188 60 Z" fill="url(#gPurple)"/>
    <path class="part" data-part="ribbon-right" d="M182 60 C165 44 146 50 145 73 C161 83 177 79 182 60 Z" fill="url(#gPurple)"/>
    <circle class="part" data-part="ribbon-center" cx="185" cy="62" r="13" fill="#9850c9"/>
    <path class="part" data-part="bow" d="M154 242 Q170 222 186 242 Q170 260 154 242 Z" fill="url(#gWhite)"/>`;
}

function renderCharacter(kind){
  const svg = document.getElementById('characterSvg');
  svg.innerHTML = baseSvg(kind);
  svg.querySelectorAll('.part').forEach(el=>{
    el.addEventListener('click', async ()=>{
      await resumeAudio();
      el.setAttribute('fill', state.color);
      el.classList.add('painted');
      state.painted++;
      paintSound();
      const text = messages[state.painted % messages.length];
      setTalk(text, true);
    });
  });
}

async function selectCharacter(kind){
  await resumeAudio();
  state.character = kind;
  state.painted = 0;
  document.getElementById('menuScreen').classList.add('hidden');
  document.getElementById('gameScreen').classList.remove('hidden');
  renderCharacter(kind);
  setTalk(charInfo[kind].guide, true);
}

function backMenu(){
  state.character = null;
  document.getElementById('gameScreen').classList.add('hidden');
  document.getElementById('menuScreen').classList.remove('hidden');
  setTalk('すきなキャラクターをえらんでね。', true);
}

window.addEventListener('DOMContentLoaded', ()=>{
  resumeAudio();
  setTimeout(()=>speak('すきなキャラクターをえらんでね。',1.08,1), 350);

  document.querySelectorAll('.character-card').forEach(btn=>{
    btn.addEventListener('click', ()=>selectCharacter(btn.dataset.character));
  });
  document.querySelectorAll('.color-chip').forEach(btn=>{
    btn.addEventListener('click', async ()=>{
      await resumeAudio();
      document.querySelectorAll('.color-chip').forEach(b=>b.classList.remove('active'));
      btn.classList.add('active');
      state.color = btn.dataset.color;
      tone(700,0.07,0.03);
      const name = colorNames[state.color] || 'このいろ';
      setTalk(name + 'でぬってみよう。', true);
    });
  });
  document.getElementById('backBtn').addEventListener('click', backMenu);

  ['pointerdown','touchstart','click'].forEach(ev=>{
    document.addEventListener(ev, resumeAudio, {once:true, passive:true});
  });
});
