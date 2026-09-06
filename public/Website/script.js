if(window.Lenis){
  window.aquaLenis=new Lenis({duration:1.05,smoothWheel:true,syncTouch:true});
  function raf(t){window.aquaLenis.raf(t);requestAnimationFrame(raf)} requestAnimationFrame(raf);
}
window.addEventListener("load",()=>{
  const loader=document.getElementById("pageLoader");
  requestAnimationFrame(()=>setTimeout(()=>loader?.classList.add("loaded"),650));
});

const line=document.getElementById("scrollLine");
window.addEventListener("scroll",()=>{
  const max=document.documentElement.scrollHeight-window.innerHeight;
  line.style.width=(window.scrollY/Math.max(max,1)*100)+"%";
});

if(window.gsap){
  // Hero copy: never gets trapped behind the generic .reveal observer.
  gsap.from(".hero h1",{y:55,opacity:0,duration:1.05,ease:"power4.out",delay:.45});
  gsap.from(".hero .lead",{y:25,opacity:0,duration:.8,ease:"power3.out",delay:.78});
  gsap.from(".hero-actions",{y:20,opacity:0,duration:.7,ease:"power3.out",delay:.95});
  gsap.from(".hero-proof",{y:15,opacity:0,duration:.65,ease:"power3.out",delay:1.08});
  gsap.from(".hero-bottle-wrap",{scale:.72,opacity:0,rotationZ:-3,duration:1.35,ease:"power4.out",delay:.35});
  gsap.to(".hero-ring",{rotation:360,duration:30,repeat:-1,ease:"none"});
  gsap.to(".floating-sample.sample-one",{y:-12,duration:2.6,repeat:-1,yoyo:true,ease:"sine.inOut"});
  gsap.to(".floating-sample.sample-two",{y:13,duration:3.1,repeat:-1,yoyo:true,ease:"sine.inOut"});
  gsap.to(".bottle-callout",{y:-7,duration:2.2,repeat:-1,yoyo:true,ease:"sine.inOut"});

  // Product-ad sequence: bottle -> water -> branding -> QR.
  const adStatus=document.getElementById("heroAdStatus");
  const adSteps=[...document.querySelectorAll(".hero-ad-steps span")];
  const adStates=["PLAIN BOTTLE","WATER FILLED","CUSTOM BRANDING","SMART QR READY"];
  const adTimeline=gsap.timeline({repeat:-1,repeatDelay:1.1});
  adTimeline
    .set(".hb-label",{opacity:0,scale:.88})
    .set(".hb-water",{opacity:.18,scaleY:.15,transformOrigin:"bottom"})
    .set(".mini-qr",{opacity:0,scale:.4})
    .set(".hb-label-sweep",{xPercent:-110})
    .to(".hb-water",{opacity:1,scaleY:1,duration:1.15,ease:"power2.out"},.4)
    .call(()=>{if(adStatus)adStatus.textContent=adStates[1];adSteps.forEach((x,i)=>x.classList.toggle("active",i===1))})
    .to(".hb-label",{opacity:1,scale:1,duration:.75,ease:"back.out(1.5)"},1.65)
    .to(".hb-label-sweep",{xPercent:110,duration:.9,ease:"power2.inOut"},1.75)
    .call(()=>{if(adStatus)adStatus.textContent=adStates[2];adSteps.forEach((x,i)=>x.classList.toggle("active",i===2))})
    .to(".mini-qr",{opacity:1,scale:1,duration:.55,ease:"back.out(1.8)"},2.75)
    .call(()=>{if(adStatus)adStatus.textContent=adStates[3];adSteps.forEach((x,i)=>x.classList.toggle("active",i===3))})
    .to(".mini-qr",{boxShadow:"0 0 0 8px rgba(255,255,255,.12)",duration:.45,repeat:3,yoyo:true},3.2)
    .to(".hb-label",{opacity:0,scale:.92,duration:.5},5.0)
    .to(".hb-water",{scaleY:.15,opacity:.18,duration:.7,ease:"power2.in"},5.1)
    .call(()=>{if(adStatus)adStatus.textContent=adStates[0];adSteps.forEach((x,i)=>x.classList.toggle("active",i===0))});
}

const observer=new IntersectionObserver(entries=>{
  entries.forEach(entry=>{
    if(entry.isIntersecting){
      if(window.gsap) gsap.to(entry.target,{opacity:1,y:0,duration:.8,ease:"power3.out"});
      else {entry.target.style.opacity=1;entry.target.style.transform="none";}
      observer.unobserve(entry.target);
    }
  });
},{threshold:.12});
document.querySelectorAll(".reveal").forEach(el=>observer.observe(el));

const stage=document.getElementById("heroStage");
const bottle=document.getElementById("heroBottle");
if(stage&&bottle){
  stage.addEventListener("mousemove",e=>{
    const r=stage.getBoundingClientRect();
    const x=(e.clientX-r.left)/r.width-.5;
    const y=(e.clientY-r.top)/r.height-.5;
    bottle.style.transform=`translateZ(30px) rotateY(${x*26}deg) rotateX(${y*-9}deg)`;
  });
  stage.addEventListener("mouseleave",()=>bottle.style.transform="");
}

document.querySelectorAll(".tilt-card").forEach(card=>{
  card.addEventListener("mousemove",e=>{
    const r=card.getBoundingClientRect(),x=(e.clientX-r.left)/r.width-.5,y=(e.clientY-r.top)/r.height-.5;
    card.style.transform=`perspective(1300px) rotateX(${y*-3}deg) rotateY(${x*4}deg) translateY(-4px)`;
  });
  card.addEventListener("mouseleave",()=>card.style.transform="");
});

// Simple design preview: intentionally a visual concept, not a final production proof.
const brandInput=document.getElementById("brandName");
const previewBrand=document.getElementById("previewBrand");
const dbLabel=document.querySelector(".db-label");
brandInput?.addEventListener("input",()=>{
  const value=(brandInput.value.trim()||"YOUR BRAND").toUpperCase();
  previewBrand.innerHTML=value.replace(/\s+/g,"<br>");
});
document.querySelectorAll(".tone").forEach(btn=>{
  btn.addEventListener("click",()=>{
    document.querySelectorAll(".tone").forEach(x=>x.classList.remove("active"));
    btn.classList.add("active");
    dbLabel.style.background=btn.dataset.tone;
  });
});

let qty=500;
const qtyEl=document.getElementById("qty");
document.getElementById("qtyMinus")?.addEventListener("click",()=>{qty=Math.max(50,qty-50);qtyEl.textContent=qty.toLocaleString("en-IN")});
document.getElementById("qtyPlus")?.addEventListener("click",()=>{qty=Math.min(5000,qty+50);qtyEl.textContent=qty.toLocaleString("en-IN")});

document.getElementById("requestDesign")?.addEventListener("click",()=>{
  const name=(brandInput.value.trim()||"YOUR BRAND");
  const toast=document.getElementById("toast");
  toast.querySelector(".toast-body").textContent=`Design request ready for ${name} — ${qty.toLocaleString("en-IN")} bottles. Connect this button to your Laravel enquiry endpoint.`;
  new bootstrap.Toast(toast,{delay:3200}).show();
});

/* =========================================================
   Advanced Brand Studio
========================================================= */
const studioBottle=document.getElementById("studioBottle"), studioStage=document.getElementById("studioStage");
let studioRotation=0, dragging=false, dragStart=0, startRotation=0;
function applyStudioRotation(){if(studioBottle)studioBottle.style.transform=`rotateY(${studioRotation}deg)`;}
if(studioStage&&studioBottle){
  studioStage.addEventListener("pointerdown",e=>{dragging=true;dragStart=e.clientX;startRotation=studioRotation;studioStage.setPointerCapture?.(e.pointerId)});
  studioStage.addEventListener("pointermove",e=>{if(dragging){studioRotation=startRotation+(e.clientX-dragStart)*.55;applyStudioRotation()}});
  studioStage.addEventListener("pointerup",()=>dragging=false);
  studioStage.addEventListener("pointercancel",()=>dragging=false);
}
const studioBrand=document.getElementById("studioBrand");
function updateStudioBrand(){
  const v=(studioBrand?.value.trim()||"YOUR BRAND").toUpperCase();
  document.getElementById("frontBrand").innerHTML=v.replace(/\s+/g,"<br>");
  document.getElementById("backBrand").textContent=v;
}
studioBrand?.addEventListener("input",updateStudioBrand);

document.getElementById("logoInput")?.addEventListener("change",e=>{
  const f=e.target.files?.[0]; if(!f)return;
  const r=new FileReader();
  r.onload=()=>{const img=document.getElementById("logoPreview");img.src=r.result;img.style.display="block"};
  r.readAsDataURL(f);
});
document.querySelectorAll(".label-style").forEach(btn=>btn.addEventListener("click",()=>{
  document.querySelectorAll(".label-style").forEach(x=>x.classList.remove("active"));btn.classList.add("active");
  document.querySelectorAll(".s-label").forEach(x=>x.style.background=btn.dataset.style);
}));
document.querySelectorAll(".studio-tab").forEach(tab=>tab.addEventListener("click",()=>{
  document.querySelectorAll(".studio-tab").forEach(x=>x.classList.remove("active"));
  tab.classList.add("active");
  const back=tab.dataset.face==="back";
  studioBottle?.classList.toggle("face-back",back);
  // Keep the product upright; the Back button changes the artwork face.
  studioRotation=0;
  applyStudioRotation();
}));

let studioQty=500;
function updateStudioQty(){
  document.getElementById("studioQty").textContent=studioQty.toLocaleString("en-IN");
  const unit=studioQty>=1000?24:studioQty>=500?26:30;
  document.getElementById("studioEstimate").textContent="₹"+(studioQty*unit).toLocaleString("en-IN");
}
document.getElementById("studioMinus")?.addEventListener("click",()=>{studioQty=Math.max(50,studioQty-50);updateStudioQty()});
document.getElementById("studioPlus")?.addEventListener("click",()=>{studioQty=Math.min(5000,studioQty+50);updateStudioQty()});

function makeQr(){
  if(!window.QRCode)return;
  const url=document.getElementById("qrUrl")?.value.trim()||"https://yourbrand.com/menu";
  ["frontQr","backQr"].forEach((id,i)=>{
    const el=document.getElementById(id); if(!el)return;
    el.innerHTML="";
    new QRCode(el,{text:url,width:i?25:32,height:i?25:32,colorDark:"#111828",colorLight:"#ffffff",correctLevel:QRCode.CorrectLevel.M});
  });
}
document.getElementById("qrUrl")?.addEventListener("input",makeQr);

document.getElementById("submitDesign")?.addEventListener("click",()=>{
  const name=document.getElementById("studioBrand")?.value.trim()||"YOUR BRAND";
  const toast=document.getElementById("toast");
  toast.querySelector(".toast-body").innerHTML=`<b>Design idea ready for ${name}.</b><br><small>${studioQty.toLocaleString("en-IN")} bottles · Final artwork will be discussed and approved before production.</small>`;
  new bootstrap.Toast(toast,{delay:4500}).show();
});
updateStudioBrand();updateStudioQty();setTimeout(makeQr,600);

/* =========================================================
   V5 — DESIGN REQUEST WIZARD
========================================================= */
const requestModal=document.getElementById("requestModal");
const requestFooter=document.getElementById("requestFooter");
const requestProgress=document.getElementById("requestProgress");
const requestStepLabel=document.getElementById("requestStepLabel");
const requestHint=document.getElementById("requestHint");
const requestNext=document.getElementById("requestNext");
const requestBack=document.getElementById("requestBack");
let requestStep=1;
const requestData={size:"500ml",batch:"500–999"};

function openRequest(){
  if(!requestModal)return;
  requestModal.classList.add("open");requestModal.setAttribute("aria-hidden","false");
  document.body.classList.add("modal-open");
  window.aquaLenis?.stop();
  requestStep=1;renderRequestStep();
}
function closeRequest(){
  requestModal?.classList.remove("open");requestModal?.setAttribute("aria-hidden","true");
  document.body.classList.remove("modal-open");
  window.aquaLenis?.start();
}
document.querySelectorAll("#requestModal [data-close-request]").forEach(x=>x.addEventListener("click",closeRequest));
document.addEventListener("keydown",e=>{if(e.key==="Escape"&&requestModal?.classList.contains("open"))closeRequest()});

document.getElementById("submitDesign")?.addEventListener("click",openRequest);
document.getElementById("requestDesign")?.addEventListener("click",openRequest);

document.querySelectorAll(".choice").forEach(btn=>btn.addEventListener("click",()=>{
  const group=btn.dataset.group;
  document.querySelectorAll(`.choice[data-group="${group}"]`).forEach(x=>x.classList.remove("active"));
  btn.classList.add("active");requestData[group]=btn.dataset.value;
  updateRequestSummary();
}));

document.getElementById("rqFile")?.addEventListener("change",e=>{
  const name=e.target.files?.[0]?.name||"Upload logo or artwork";
  document.getElementById("rqFileName").textContent=name;
});

function updateRequestSummary(){
  const b=document.getElementById("rqBusiness")?.value.trim()||"—";
  const phone=document.getElementById("rqPhone")?.value.trim()||"—";
  document.getElementById("sumBusiness").textContent=b;
  document.getElementById("sumBottle").textContent=`${requestData.size} · ${requestData.batch}`;
  document.getElementById("sumContact").textContent=phone;
}
["rqBusiness","rqPhone"].forEach(id=>document.getElementById(id)?.addEventListener("input",updateRequestSummary));

function validateRequestStep(){
  if(requestStep===1){
    const business=document.getElementById("rqBusiness")?.value.trim();
    const name=document.getElementById("rqName")?.value.trim();
    const phone=document.getElementById("rqPhone")?.value.trim();
    if(!business||!name||!phone){
      showRequestToast("Please enter business name, your name and mobile number.");
      return false;
    }
  }
  return true;
}
function renderRequestStep(){
  document.querySelectorAll(".request-step").forEach(s=>s.classList.remove("active"));
  const current=document.querySelector(`.request-step[data-step="${requestStep}"]`);
  current?.classList.add("active");
  requestProgress.style.width=(requestStep/4*100)+"%";
  requestStepLabel.textContent=String(requestStep).padStart(2,"0")+" / 04";
  requestHint.textContent=`Step ${requestStep} of 4`;
  requestBack.style.visibility=requestStep===1?"hidden":"visible";
  requestNext.textContent=requestStep===4?"Send request ↗":"Continue →";
  updateRequestSummary();
}
requestNext?.addEventListener("click",()=>{
  if(!validateRequestStep())return;
  if(requestStep<4){requestStep++;renderRequestStep();return;}
  submitRequestDemo();
});
requestBack?.addEventListener("click",()=>{if(requestStep>1){requestStep--;renderRequestStep()}});

function submitRequestDemo(){
  const success=document.getElementById("requestSuccess");
  document.querySelectorAll(".request-step").forEach(s=>s.classList.remove("active"));
  success.classList.add("show");
  requestFooter.style.display="none";
  requestProgress.style.width="100%";
  requestStepLabel.textContent="DONE";
  /*
    Laravel integration point:
    POST /design-requests
    FormData: business, name, phone, email, type, size, batch,
    branding, qr_url, artwork, notes, preview_config
  */
}
function showRequestToast(message){
  const toast=document.getElementById("toast");
  if(!toast)return;
  toast.querySelector(".toast-body").textContent=message;
  new bootstrap.Toast(toast,{delay:3000}).show();
}

/* requestDialogWheelGuard: keep mouse wheel inside the form */
const requestDialog=document.querySelector(".request-dialog");
requestDialog?.addEventListener("wheel",e=>e.stopPropagation(),{passive:true});
requestDialog?.addEventListener("touchmove",e=>e.stopPropagation(),{passive:true});
