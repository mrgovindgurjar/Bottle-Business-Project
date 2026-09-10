(() => {
  const root = document.getElementById('designStudio');
  if (!root || !window.JALVAN_DESIGN) return;

  const initial = window.JALVAN_DESIGN.version?.design_data || {};
  const state = {
    global: { qr_url: '', ...(initial.global || {}) },
    front: { ...(initial.front || {}) },
    back: { ...(initial.back || {}) },
    activeSide: 'front'
  };
  const defaults = {
    front: { background:'#ffffff', accent:'#111827', title:'PURE TASTE', subtitle:'Premium drinking water', body:'CUSTOM BRANDED WATER', footer:'PURE • SAFE • PREMIUM', logo_x:50, logo_y:16, logo_scale:1, qr_x:50, qr_y:74, qr_size:22 },
    back: { background:'#ffffff', accent:'#111827', title:'OUR MENU', subtitle:'Scan to explore', body:'Your brand. Your story. Your bottle.', footer:'THANK YOU • VISIT AGAIN', logo_x:50, logo_y:16, logo_scale:1, qr_x:50, qr_y:74, qr_size:22 }
  };
  state.front = {...defaults.front, ...state.front};
  state.back = {...defaults.back, ...state.back};

  const side = () => state[state.activeSide];
  const qs = s => root.querySelector(s);
  const contentFields = qs('#contentFields');

  function esc(v='') { return String(v).replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m])); }
  function field(key, label, type='text') {
    const value = side()[key] ?? '';
    return `<label>${label}<input data-key="${key}" type="${type}" value="${esc(value)}"></label>`;
  }
  function renderFields() {
    const s = side();
    contentFields.innerHTML = `
      ${field('title','Headline')}
      ${field('subtitle','Subtitle')}
      ${field('body','Body / offer')}
      ${field('footer','Footer text')}
      <div class="ds-field-note">Tip: keep critical legal/printing text short. The approved artwork can later be handed to production.</div>`;
    contentFields.querySelectorAll('[data-key]').forEach(input => input.addEventListener('input', e => { side()[e.target.dataset.key] = e.target.value; render(); }));
  }

  function render() {
    const s = side();
    const label = qs('#previewLabel');
    label.style.background = s.background;
    label.style.color = s.accent;
    qs('#previewTitle').textContent = s.title || 'YOUR BRAND';
    qs('#previewSubtitle').textContent = s.subtitle || '';
    qs('#previewBody').textContent = s.body || '';
    qs('#previewFooter').textContent = s.footer || '';
    qs('#previewLogo').style.transform = `translateX(-50%) scale(${Number(s.logo_scale || 1)})`;
    qs('#previewLogo').style.top = `${Number(s.logo_y || 16)}%`;
    qs('#previewLogo').style.background = s.accent;
    qs('#previewLogo').style.color = s.background;
    qs('#previewQr').style.left = `${Number(s.qr_x || 50)}%`;
    qs('#previewQr').style.top = `${Number(s.qr_y || 74)}%`;
    qs('#previewQr').style.width = `${Number(s.qr_size || 22)}%`;
    qs('#previewQr').style.height = `${Number(s.qr_size || 22)}%`;
    const qr = state.global.qr_url;
    qs('#previewQr').title = qr || 'Add QR destination';
    qs('#bgColor').value = s.background;
    qs('#accentColor').value = s.accent;
    qs('#logoScale').value = s.logo_scale || 1;
    qs('#logoY').value = s.logo_y || 16;
    qs('#qrSize').value = s.qr_size || 22;
    qs('#qrUrl').value = state.global.qr_url || '';
  }

  root.querySelectorAll('[data-tab]').forEach(btn => btn.addEventListener('click', () => {
    root.querySelectorAll('[data-tab]').forEach(x=>x.classList.remove('active')); btn.classList.add('active');
    root.querySelectorAll('[data-panel]').forEach(x=>x.classList.toggle('active', x.dataset.panel===btn.dataset.tab));
  }));
  root.querySelectorAll('[data-side]').forEach(btn => btn.addEventListener('click', () => {
    state.activeSide = btn.dataset.side; root.querySelectorAll('[data-side]').forEach(x=>x.classList.toggle('active',x===btn)); renderFields(); render();
  }));
  root.querySelectorAll('[data-view]').forEach(btn => btn.addEventListener('click', () => {
    state.activeSide = btn.dataset.view; root.querySelectorAll('[data-view]').forEach(x=>x.classList.toggle('active',x===btn)); root.querySelectorAll('[data-side]').forEach(x=>x.classList.toggle('active',x.dataset.side===state.activeSide)); renderFields(); render();
  }));

  qs('#bgColor').addEventListener('input', e => { side().background=e.target.value; render(); });
  qs('#accentColor').addEventListener('input', e => { side().accent=e.target.value; render(); });
  qs('#logoScale').addEventListener('input', e => { side().logo_scale=e.target.value; render(); });
  qs('#logoY').addEventListener('input', e => { side().logo_y=e.target.value; render(); });
  qs('#qrSize').addEventListener('input', e => { side().qr_size=e.target.value; render(); });
  qs('#qrUrl').addEventListener('input', e => { state.global.qr_url=e.target.value; render(); });
  qs('#fontFamily').addEventListener('change', e => { state.global.font=e.target.value; });
  qs('#designType').addEventListener('change', e => { state.global.design_type=e.target.value; });

  root.querySelectorAll('.ds-style-presets button').forEach(btn => btn.addEventListener('click', () => { side().background=btn.dataset.bg; side().accent=btn.dataset.accent; render(); }));
  qs('#zoomReset').addEventListener('click', () => { qs('.ds-bottle').style.transform='scale(1)'; });

  qs('#logoUpload').addEventListener('change', async e => {
    const file=e.target.files[0]; if(!file) return;
    const form=new FormData(); form.append('logo',file); form.append('_token',document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value || '');
    try { const r=await fetch(`{{ route('admin.designs.logo',[$design,$version]) }}`,{method:'POST',body:form,headers:{'X-Requested-With':'XMLHttpRequest'}}); if(!r.ok) throw new Error(); location.reload(); } catch(e) { alert('Logo upload failed. Please try again.'); }
  });

  function prepare(formId, dataInputId) {
    const form=document.getElementById(formId); document.getElementById(dataInputId).value=JSON.stringify({...state, activeSide:undefined});
    document.getElementById('versionNameInput').value=qs('#versionName').value;
    document.getElementById('changeNoteInput').value=qs('#changeNote').value;
    form.submit();
  }
  qs('#saveDesignBtn').addEventListener('click', () => prepare('designSaveForm','designDataInput'));
  qs('#newVersionBtn').addEventListener('click', () => { document.getElementById('newVersionDataInput').value=JSON.stringify({...state, activeSide:undefined}); document.getElementById('newVersionNoteInput').value=qs('#changeNote').value; document.getElementById('newVersionForm').submit(); });

  renderFields(); render();
})();
