const header = document.querySelector('.site-header');
const navToggle = document.querySelector('.nav-toggle');
const navLinks = document.querySelector('.nav-links');
const navAnchors = [...document.querySelectorAll('.nav-links a[href^="#"]')];
const sections = [...document.querySelectorAll('main section[id]')];
const root = document.documentElement;
let lastY = window.scrollY;
let ticking = false;

function closeMenu(){
  navLinks.classList.remove('open');
  navToggle.setAttribute('aria-expanded','false');
  navToggle.setAttribute('aria-label','Open navigation menu');
}

navToggle.addEventListener('click',()=>{
  const isOpen = navLinks.classList.toggle('open');
  navToggle.setAttribute('aria-expanded',String(isOpen));
  navToggle.setAttribute('aria-label',isOpen?'Close navigation menu':'Open navigation menu');
});
navAnchors.forEach(link=>link.addEventListener('click',closeMenu));
document.addEventListener('click',(event)=>{
  if(window.innerWidth<=760 && navLinks.classList.contains('open') && !event.target.closest('.nav')) closeMenu();
});

function rgbFromHsl(h,s,l){
  s/=100;l/=100;const k=n=>(n+h/30)%12;const a=s*Math.min(l,1-l);const f=n=>l-a*Math.max(-1,Math.min(k(n)-3,Math.min(9-k(n),1)));
  return `${Math.round(255*f(0))}, ${Math.round(255*f(8))}, ${Math.round(255*f(4))}`;
}

function updateScrollEffects(){
  const y=window.scrollY;
  const max=Math.max(1,document.documentElement.scrollHeight-window.innerHeight);
  const p=Math.min(1,y/max);
  header.classList.toggle('scrolled',y>10);
  if(y>lastY+6 && y>110 && !navLinks.classList.contains('open')) header.classList.add('header-hidden');
  else if(y<lastY-4 || y<70) header.classList.remove('header-hidden');
  lastY=y;

  const marker=y+140; let current='';
  sections.forEach(section=>{if(marker>=section.offsetTop) current=section.id;});
  navAnchors.forEach(link=>link.classList.toggle('active',link.getAttribute('href')===`#${current}`));

  const hue=184 + p*58 + Math.sin(p*Math.PI*2)*9;
  const hue2=220 + p*62 + Math.cos(p*Math.PI*2)*10;
  root.style.setProperty('--hue',hue.toFixed(1));
  root.style.setProperty('--hue2',hue2.toFixed(1));
  root.style.setProperty('--accent-rgb',rgbFromHsl(hue,92,65));
  root.style.setProperty('--accent2-rgb',rgbFromHsl(hue2,92,68));
  root.style.setProperty('--scroll-progress',p.toFixed(4));

  const a=document.querySelector('.orb-a'), b=document.querySelector('.orb-b'), c=document.querySelector('.orb-c');
  if(a) a.style.transform=`translate3d(${p*150}px, ${p*420}px, 0) rotate(${p*120}deg)`;
  if(b) b.style.transform=`translate3d(${-p*180}px, ${p*260}px, 0) rotate(${-p*150}deg)`;
  if(c) c.style.transform=`translate3d(${Math.sin(p*6)*120}px, ${-p*220}px, 0) rotate(${p*180}deg)`;

  document.querySelectorAll('[data-parallax]').forEach(el=>{
    const amount=parseFloat(el.dataset.parallax||'0.05');
    const rect=el.getBoundingClientRect();
    const center=rect.top+rect.height/2-window.innerHeight/2;
    el.style.transform=`translate3d(0, ${-center*amount}px, 0)`;
  });
  ticking=false;
}

window.addEventListener('scroll',()=>{if(!ticking){requestAnimationFrame(updateScrollEffects);ticking=true;}},{passive:true});
window.addEventListener('resize',updateScrollEffects,{passive:true});
updateScrollEffects();

const observer=new IntersectionObserver((entries)=>{
  entries.forEach(entry=>{if(entry.isIntersecting){entry.target.classList.add('visible');observer.unobserve(entry.target);}});
},{threshold:.12,rootMargin:'0px 0px -4% 0px'});
document.querySelectorAll('.reveal').forEach((el,i)=>{el.style.transitionDelay=`${Math.min((i%5)*55,220)}ms`;observer.observe(el);});

function attachGlow(el){
  el.addEventListener('pointermove',(e)=>{
    const r=el.getBoundingClientRect();
    el.style.setProperty('--mx',`${e.clientX-r.left}px`);
    el.style.setProperty('--my',`${e.clientY-r.top}px`);
  });
  el.addEventListener('pointerleave',()=>{
    el.style.setProperty('--mx','50%');
    el.style.setProperty('--my','50%');
  });
}
document.querySelectorAll('.glow-follow,.hover-water,.color-mix-card,.weather-temp-panel').forEach(attachGlow);

// Small magnetic movement on buttons for a more premium feel.
document.querySelectorAll('.magnetic').forEach(btn=>{
  btn.addEventListener('pointermove',(e)=>{
    const r=btn.getBoundingClientRect();
    const x=(e.clientX-r.left-r.width/2)*.08; const y=(e.clientY-r.top-r.height/2)*.12;
    btn.style.transform=`translate(${x}px,${y}px) translateY(-2px)`;
  });
  btn.addEventListener('pointerleave',()=>btn.style.transform='');
});

// Lightweight floating spark particles.
const field=document.getElementById('spark-field');
if(field && !window.matchMedia('(prefers-reduced-motion: reduce)').matches){
  for(let i=0;i<26;i++){
    const s=document.createElement('span'); s.className='spark';
    s.style.left=`${Math.random()*100}%`; s.style.top=`${Math.random()*120}%`;
    s.style.setProperty('--dur',`${12+Math.random()*18}s`);
    s.style.setProperty('--delay',`${-Math.random()*20}s`);
    s.style.setProperty('--dx',`${-80+Math.random()*160}px`);
    s.style.opacity=(.12+Math.random()*.28).toFixed(2);
    field.appendChild(s);
  }
}

document.getElementById('year').textContent=new Date().getFullYear();
