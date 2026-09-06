
const portalViews = document.querySelectorAll(".portal-view");
const portalNav = document.querySelectorAll(".portal-nav");

function showPortalView(view){
  portalViews.forEach(v=>v.classList.toggle("active",v.id==="view-"+view));
  portalNav.forEach(n=>n.classList.toggle("active",n.dataset.view===view));
  window.scrollTo({top:0,behavior:"smooth"});
}
portalNav.forEach(n=>n.addEventListener("click",()=>showPortalView(n.dataset.view)));

function portalToast(message){
  const el=document.getElementById("portalToast");
  el.textContent=message;el.classList.add("show");
  clearTimeout(window.portalToastTimer);
  window.portalToastTimer=setTimeout(()=>el.classList.remove("show"),3200);
}
function approveDesign(){
  portalToast("Design approved. Your artwork is now ready for production.");
  document.querySelector(".design-status").innerHTML="<span>●</span> Approved by you";
  document.querySelector(".design-status").style.background="#eaf9f3";
  document.querySelector(".design-status").style.color="#0c875b";
  const btn=document.querySelector(".design-actions .portal-primary");
  if(btn){btn.textContent="Approved ✓";btn.disabled=true;btn.style.opacity=".65"}
}
function requestRevision(){
  const note=prompt("What would you like us to change?");
  if(note && note.trim()) portalToast("Revision request saved. Your AquaForm team will review it.");
}
document.querySelector(".portal-logout")?.addEventListener("click",()=>portalToast("Demo mode: Laravel authentication will be connected in V7."));
document.querySelector(".portal-notify")?.addEventListener("click",()=>portalToast("You have 2 updates: production progress and design approval."));
document.querySelectorAll(".document-card button").forEach(b=>b.addEventListener("click",()=>portalToast("Demo document action — Laravel file storage will be connected in V7.")));
document.querySelectorAll(".order-list-card .portal-primary").forEach(b=>b.addEventListener("click",()=>portalToast("Reorder flow is ready for Laravel integration.")));
document.querySelector(".profile-card .portal-primary")?.addEventListener("click",()=>portalToast("Profile saved in demo mode."));
window.addEventListener("load",()=>setTimeout(()=>document.getElementById("portalLoader")?.classList.add("hide"),450));
