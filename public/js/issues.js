document.addEventListener('DOMContentLoaded',()=>{
 document.querySelectorAll('.issue-page form').forEach(form=>form.addEventListener('submit',()=>{const btn=form.querySelector('button[type="submit"],button.issue-btn.primary'); if(btn && !form.dataset.noLock){btn.disabled=true; btn.dataset.originalText=btn.textContent; btn.textContent='Saving...';}}));
});
