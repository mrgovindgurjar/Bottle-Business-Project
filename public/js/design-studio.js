/* JALVAN ERP — Design Studio Editor
 * Phase 3/4 interaction fix
 * - Safe DOM initialization
 * - Delegated event handling
 * - Null-safe controls
 * - Legacy design_data normalization
 * - Drag/select/layers/history/save/upload/workflow
 */
(() => {
  const init = () => {
    const root = document.getElementById('designStudio');
    if (!root) return;
    if (!window.JALVAN_DESIGN) {
      console.error('[JALVAN Design Studio] window.JALVAN_DESIGN is missing.');
      return;
    }

    const payload = window.JALVAN_DESIGN || {};
    const version = payload.version || {};
    const old = version.design_data || {};
    const $ = (selector, scope = root) => scope.querySelector(selector);
    const $$ = (selector, scope = root) => [...scope.querySelectorAll(selector)];
    const on = (selector, event, handler) => {
      String(event).split(/\s+/).filter(Boolean).forEach((eventName) => root.addEventListener(eventName, (e) => {
        const target = e.target.closest(selector);
        if (target && root.contains(target)) handler(e, target);
      }));
    };
    const clone = (value) => JSON.parse(JSON.stringify(value));
    const esc = (value) => String(value ?? '').replace(/[&<>'"]/g, (m) => ({
      '&':'&amp;', '<':'&lt;', '>':'&gt;', "'":'&#039;', '"':'&quot;'
    }[m]));
    const uid = () => 'el_' + Date.now().toString(36) + Math.random().toString(36).slice(2, 8);
    const num = (value, fallback = 0) => {
      const n = Number(value);
      return Number.isFinite(n) ? n : fallback;
    };
    const clamp = (value, min, max) => Math.max(min, Math.min(max, num(value), max));
    const setValue = (selector, value) => { const el = $(selector); if (el) el.value = value ?? ''; };
    const setText = (selector, value) => { const el = $(selector); if (el) el.textContent = value ?? ''; };
    const setDisabled = (selector, disabled) => { const el = $(selector); if (el) el.disabled = !!disabled; };
    const toHex = (value, fallback = '#111827') => /^#[0-9a-f]{6}$/i.test(String(value || '')) ? value : fallback;

    const defaults = {
      global: { qr_url:'', font:'Inter', show_artwork:false, print_width_mm:70, print_height_mm:160, print_dpi:300 },
      front: { background:'#ffffff', accent:'#111827', elements:[] },
      back: { background:'#ffffff', accent:'#111827', elements:[] }
    };

    const legacyToElements = (side) => {
      const s = old[side] || {};
      const accent = s.accent || '#111827';
      const font = old.global?.font || 'Inter';
      return [
        {id:uid(),type:'logo',name:'Logo',x:num(s.logo_x,50),y:num(s.logo_y,16),w:28,rotate:0,opacity:1,color:accent,border:'#ffffff',locked:false,scale:num(s.logo_scale,1),text:''},
        {id:uid(),type:'text',name:'Headline',x:num(s.title_x,50),y:num(s.title_y,38),w:82,rotate:0,opacity:1,color:accent,border:'#ffffff',locked:false,size:30,weight:800,align:'center',font,text:s.title || (side === 'front' ? 'PURE TASTE' : 'OUR MENU'),scale:1,shape:'rounded'},
        {id:uid(),type:'text',name:'Subtitle',x:num(s.subtitle_x,50),y:num(s.subtitle_y,49),w:82,rotate:0,opacity:1,color:accent,border:'#ffffff',locked:false,size:15,weight:500,align:'center',font,text:s.subtitle || (side === 'front' ? 'Premium drinking water' : 'Scan to explore'),scale:1,shape:'rounded'},
        {id:uid(),type:'text',name:'Body',x:num(s.body_x,50),y:num(s.body_y,58),w:84,rotate:0,opacity:1,color:accent,border:'#ffffff',locked:false,size:16,weight:600,align:'center',font,text:s.body || (side === 'front' ? 'CUSTOM BRANDED WATER' : 'Your brand. Your story. Your bottle.'),scale:1,shape:'rounded'},
        {id:uid(),type:'qr',name:'QR Code',x:num(s.qr_x,50),y:num(s.qr_y,74),w:num(s.qr_size,22),rotate:0,opacity:1,color:'#111827',border:'#ffffff',locked:false,size:100,weight:400,align:'center',font:'Inter',text:'',scale:1,shape:'rounded'},
        {id:uid(),type:'text',name:'Footer',x:num(s.footer_x,50),y:num(s.footer_y,94),w:82,rotate:0,opacity:1,color:accent,border:'#ffffff',locked:false,size:13,weight:700,align:'center',font,text:s.footer || (side === 'front' ? 'PURE • SAFE • PREMIUM' : 'THANK YOU • VISIT AGAIN'),scale:1,shape:'rounded'}
      ];
    };

    const normalizeElement = (e, i, source) => ({
      id: e.id || uid(),
      type: e.type || 'text',
      name: e.name || `${e.type || 'Element'} ${i + 1}`,
      x: clamp(e.x ?? 50, 0, 100),
      y: clamp(e.y ?? 50, 0, 100),
      w: clamp(e.w ?? 40, 1, 100),
      h: clamp(e.h ?? (e.type === 'shape' ? 18 : 20), 1, 100),
      rotate: num(e.rotate, 0),
      opacity: clamp(e.opacity ?? 1, 0, 1),
      color: e.color || source.accent || '#111827',
      border: e.border || '#ffffff',
      locked: Boolean(e.locked),
      size: num(e.size, e.type === 'text' ? 20 : 100),
      weight: num(e.weight, 500),
      align: e.align || 'center',
      font: e.font || old.global?.font || 'Inter',
      text: e.text || '',
      scale: num(e.scale, 1),
      shape: e.shape || 'rounded'
    });

    const normalizeSide = (side) => {
      const source = old[side] || {};
      let elements = Array.isArray(source.elements) ? clone(source.elements) : legacyToElements(side);
      elements = elements.map((e, i) => normalizeElement(e, i, source));
      return {
        background: source.background || '#ffffff',
        accent: source.accent || '#111827',
        elements
      };
    };

    const state = {
      global: {...defaults.global, ...(old.global || {})},
      front: normalizeSide('front'),
      back: normalizeSide('back'),
      activeSide: 'front',
      selectedId: null,
      zoom: 1,
      grid: false
    };

    const art = {
      front: version.front_artwork_path || '',
      back: version.back_artwork_path || ''
    };
    const assetBase = String(window.JALVAN_ASSET_BASE || '/storage').replace(/\/$/, '');
    const assetUrl = (path) => path ? `${assetBase}/${String(path).replace(/^\//, '')}` : '';

    const history = [];
    let historyIndex = -1;
    let restoring = false;
    let drag = null;

    const current = () => state[state.activeSide];
    const selected = () => current().elements.find((e) => e.id === state.selectedId) || null;

    function snapshot() {
      return JSON.stringify(clone({global: state.global, front: state.front, back: state.back}));
    }

    function updateHistoryButtons() {
      setDisabled('#undoBtn', historyIndex <= 0);
      setDisabled('#redoBtn', historyIndex >= history.length - 1);
    }

    function pushHistory() {
      if (restoring) return;
      const snap = snapshot();
      if (history[historyIndex] === snap) return;
      history.splice(historyIndex + 1);
      history.push(snap);
      if (history.length > 40) history.shift();
      historyIndex = history.length - 1;
      updateHistoryButtons();
    }

    function restoreSnapshot(snap) {
      if (!snap) return;
      try {
        restoring = true;
        const parsed = JSON.parse(snap);
        state.global = parsed.global || clone(defaults.global);
        state.front = parsed.front || clone(defaults.front);
        state.back = parsed.back || clone(defaults.back);
        state.selectedId = null;
      } catch (error) {
        console.error('[JALVAN Design Studio] Could not restore history snapshot:', error);
      } finally {
        restoring = false;
      }
      render();
    }

    function undo() {
      if (historyIndex <= 0) return;
      historyIndex -= 1;
      restoreSnapshot(history[historyIndex]);
    }

    function redo() {
      if (historyIndex >= history.length - 1) return;
      historyIndex += 1;
      restoreSnapshot(history[historyIndex]);
    }

    function markUnsaved() {
      const el = $('#saveState');
      if (el) { el.textContent = 'Unsaved changes'; el.classList.add('dirty'); }
    }

    function markSaved() {
      const el = $('#saveState');
      if (el) { el.textContent = 'Saved'; el.classList.remove('dirty'); }
    }

    function select(id) {
      state.selectedId = id || null;
      renderLabel();
      renderLayers();
      renderInspector();
    }

    function elementStyle(e) {
      const z = current().elements.indexOf(e) + 1;
      return [
        `left:${e.x}%`, `top:${e.y}%`, `width:${e.w}%`, `opacity:${e.opacity}`,
        `transform:translate(-50%,-50%) rotate(${e.rotate}deg) scale(${e.type === 'logo' ? e.scale : 1})`,
        `color:${toHex(e.color)}`, `border-color:${toHex(e.border, '#ffffff')}`,
        `font-family:${esc(e.font)}`, `font-size:${Math.max(6, e.size)}px`,
        `font-weight:${e.weight}`, `text-align:${e.align}`, `z-index:${z}`
      ].join(';') + ';';
    }

    function elementHtml(e) {
      const cls = `ds3-el ds3-el-${e.type} ${e.id === state.selectedId ? 'selected' : ''} ${e.locked ? 'locked' : ''}`;
      const style = elementStyle(e);
      if (e.type === 'logo') {
        const logo = version.logo_path ? `<img src="${esc(assetUrl(version.logo_path))}" alt="Logo">` : '<b>J</b>';
        return `<div class="${cls}" data-id="${esc(e.id)}" style="${style}"><span class="ds3-element-tag">LOGO</span>${logo}</div>`;
      }
      if (e.type === 'qr') {
        return `<div class="${cls}" data-id="${esc(e.id)}" style="${style}"><span class="ds3-element-tag">QR</span><div class="ds3-qr-render" id="qr-${esc(e.id)}"></div></div>`;
      }
      if (e.type === 'shape') {
        return `<div class="${cls}" data-id="${esc(e.id)}" style="${style}height:${Math.max(4,e.h || e.size/2)}%;background:${toHex(e.color)};border:1px solid ${toHex(e.border,'#ffffff')};border-radius:${e.shape === 'circle' ? '999px' : '12px'};"><span class="ds3-element-tag">SHAPE</span></div>`;
      }
      if (e.type === 'divider') {
        return `<div class="${cls}" data-id="${esc(e.id)}" style="${style}height:0;border-top:2px solid ${toHex(e.color)};"><span class="ds3-element-tag">DIVIDER</span></div>`;
      }
      if (e.type === 'artwork') {
        const image = art[state.activeSide] ? `<img src="${esc(assetUrl(art[state.activeSide]))}" alt="Artwork">` : '<b>Upload artwork</b>';
        return `<div class="${cls}" data-id="${esc(e.id)}" style="${style}height:${Math.max(10,e.h || e.size)}%;background:rgba(255,255,255,.2);">${image}<span class="ds3-element-tag">ARTWORK</span></div>`;
      }
      return `<div class="${cls}" data-id="${esc(e.id)}" style="${style}">${esc(e.text || 'Text')}</div>`;
    }

    function renderQr(e) {
      const holder = $(`#qr-${CSS.escape(e.id)}`);
      if (!holder) return;
      holder.innerHTML = '';
      const url = state.global.qr_url || 'https://jalvan.in';
      if (window.QRCode) {
        try {
          new window.QRCode(holder, {
            text: url,
            width: 160,
            height: 160,
            correctLevel: window.QRCode.CorrectLevel?.M
          });
        } catch (error) {
          console.warn('[JALVAN Design Studio] QR render failed:', error);
          holder.innerHTML = '<span>QR</span>';
        }
      } else {
        holder.innerHTML = '<span>QR</span>';
      }
    }

    function renderLabel() {
      const label = $('#previewLabel');
      if (!label) return;
      const side = current();
      label.style.background = side.background;
      label.style.color = side.accent;
      label.style.fontFamily = state.global.font || 'Inter';
      label.classList.toggle('show-grid', state.grid);
      label.innerHTML = '';
      if (state.global.show_artwork && art[state.activeSide]) {
        label.insertAdjacentHTML('beforeend', `<img class="ds3-reference-art" src="${esc(assetUrl(art[state.activeSide]))}" alt="Reference artwork">`);
      }
      side.elements.forEach((e) => label.insertAdjacentHTML('beforeend', elementHtml(e)));
      side.elements.filter((e) => e.type === 'qr').forEach(renderQr);
    }

    function renderLayers() {
      const box = $('#layersList');
      if (!box) return;
      box.innerHTML = current().elements.slice().reverse().map((e) => {
        const icon = e.type === 'text' ? 'T' : e.type === 'qr' ? '⌗' : e.type === 'logo' ? '◉' : e.type === 'shape' ? '◇' : e.type === 'divider' ? '—' : '▧';
        return `<button type="button" class="ds3-layer ${e.id === state.selectedId ? 'active' : ''}" data-layer="${esc(e.id)}"><span class="layer-icon">${icon}</span><span>${esc(e.name)}</span><small>${e.locked ? 'Locked' : ''}</small></button>`;
      }).join('');
    }

    function renderInspector() {
      const e = selected();
      const empty = $('#inspectorEmpty');
      const fields = $('#inspectorFields');
      if (empty) empty.hidden = !!e;
      if (fields) fields.hidden = !e;
      setText('#inspectorTitle', e ? e.name : 'No element selected');
      if (!e) return;
      setValue('#elText', e.text || '');
      setValue('#elFont', e.font || state.global.font || 'Inter');
      setValue('#elSize', e.size || 20);
      setValue('#elWeight', e.weight || 500);
      setValue('#elX', e.x);
      setValue('#elY', e.y);
      setValue('#elW', e.w);
      setValue('#elRotate', e.rotate || 0);
      setValue('#elOpacity', e.opacity ?? 1);
      setValue('#elColor', toHex(e.color));
      setValue('#elBorder', toHex(e.border, '#ffffff'));
      setValue('#elH', e.h || 20);
      setValue('#elShape', e.shape || 'rounded');
      setDisabled('#elText', ['logo','qr','shape','divider','artwork'].includes(e.type));
      setDisabled('#elFont', e.type !== 'text');
      setDisabled('#elSize', ['logo','qr'].includes(e.type));
    }

    function render() {
      renderLabel();
      renderLayers();
      renderInspector();
      setValue('#bgColor', toHex(current().background));
      setValue('#accentColor', toHex(current().accent));
      setValue('#fontFamily', state.global.font || 'Inter');
      setValue('#printWidth', state.global.print_width_mm || 70);
      setValue('#printHeight', state.global.print_height_mm || 160);
      setValue('#printDpi', state.global.print_dpi || 300);
      setValue('#qrUrl', state.global.qr_url || '');
      setValue('#versionName', version.name || '');
      setValue('#changeNote', version.change_note || '');
      $$('[data-side]').forEach((button) => button.classList.toggle('active', button.dataset.side === state.activeSide));
      $$('[data-panel]').forEach((button) => button.classList.toggle('active', button.dataset.panel === (document.querySelector('[data-panel].active')?.dataset.panel || 'elements')));
      applyZoom();
      updateHistoryButtons();
    }

    function applyZoom() {
      const bottle = $('.ds3-bottle');
      if (bottle) bottle.style.transform = `scale(${state.zoom})`;
      setText('#zoomValue', `${Math.round(state.zoom * 100)}%`);
    }

    function updateElement(key, value) {
      const e = selected();
      if (!e || e.locked) return;
      e[key] = value;
      pushHistory();
      renderLabel();
      renderLayers();
      renderInspector();
      markUnsaved();
    }

    function add(type) {
      const names = {text:'Text', qr:'QR Code', logo:'Logo', shape:'Shape', divider:'Divider', artwork:'Artwork'};
      if (!names[type]) return;
      const e = {
        id:uid(), type, name:names[type], x:50, y:type === 'text' ? 35 : 50,
        w:type === 'shape' ? 70 : type === 'qr' ? 24 : 70,
        h:type === 'shape' ? 18 : type === 'artwork' ? 30 : 20,
        rotate:0, opacity:1, color:current().accent, border:'#ffffff', locked:false,
        size:type === 'text' ? 22 : type === 'shape' ? 18 : 100,
        weight:600, align:'center', font:state.global.font || 'Inter',
        text:type === 'text' ? 'New text' : '', scale:1, shape:'rounded'
      };
      current().elements.push(e);
      state.selectedId = e.id;
      pushHistory();
      render();
      markUnsaved();
    }

    function duplicate() {
      const e = selected();
      if (!e) return;
      const copy = clone(e);
      copy.id = uid();
      copy.name = `${e.name} copy`;
      copy.x = clamp(e.x + 3, 0, 100);
      copy.y = clamp(e.y + 3, 0, 100);
      current().elements.push(copy);
      state.selectedId = copy.id;
      pushHistory();
      render();
      markUnsaved();
    }

    function removeSelected() {
      const e = selected();
      if (!e || e.locked) return;
      current().elements = current().elements.filter((item) => item.id !== e.id);
      state.selectedId = null;
      pushHistory();
      render();
      markUnsaved();
    }

    function moveLayer(direction) {
      const elements = current().elements;
      const index = elements.findIndex((e) => e.id === state.selectedId);
      if (index < 0) return;
      const next = index + direction;
      if (next < 0 || next >= elements.length) return;
      [elements[index], elements[next]] = [elements[next], elements[index]];
      pushHistory();
      render();
      markUnsaved();
    }

    function prepareData() {
      return {
        global: clone(state.global),
        front: clone(state.front),
        back: clone(state.back)
      };
    }

    function submitForm(formId, dataFieldId, data) {
      const form = document.getElementById(formId);
      const field = document.getElementById(dataFieldId);
      if (!form || !field) {
        console.error(`[JALVAN Design Studio] Missing form or input: ${formId} / ${dataFieldId}`);
        return false;
      }
      field.value = JSON.stringify(data);
      form.submit();
      return true;
    }

    async function upload(file, kind) {
      if (!file) return;
      const max = kind === 'logo' ? 4 : 6;
      if (file.size > max * 1024 * 1024) {
        alert(`Maximum file size is ${max}MB.`);
        return;
      }
      const url = kind === 'logo' ? root.dataset.logoUrl : root.dataset.artworkUrl;
      if (!url) {
        alert('Upload endpoint is missing on this page.');
        console.error('[JALVAN Design Studio] Missing upload URL:', kind);
        return;
      }
      const token = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value || '';
      const form = new FormData();
      form.append(kind, file);
      form.append('_token', token);
      try {
        const response = await fetch(url, {
          method:'POST', body:form,
          headers:{'X-Requested-With':'XMLHttpRequest','Accept':'application/json'}
        });
        const text = await response.text();
        let json = {};
        try { json = text ? JSON.parse(text) : {}; } catch (_) {}
        if (!response.ok) throw new Error(json.message || `Upload failed (${response.status})`);
        if (kind === 'logo') {
          window.location.reload();
          return;
        }
        if (json.front_artwork_path) art.front = json.front_artwork_path;
        if (json.back_artwork_path) art.back = json.back_artwork_path;
        state.global.show_artwork = true;
        render();
        markUnsaved();
      } catch (error) {
        console.error('[JALVAN Design Studio] Upload failed:', error);
        alert(error.message || 'Upload failed. Please try again.');
      }
    }

    function startDrag(event, elementNode) {
      const id = elementNode.dataset.id;
      const e = current().elements.find((item) => item.id === id);
      if (!e || e.locked) return;
      select(id);
      event.preventDefault();
      const label = $('#previewLabel');
      if (!label) return;
      const rect = label.getBoundingClientRect();
      drag = {
        id, pointerId:event.pointerId,
        startX:event.clientX, startY:event.clientY,
        x:e.x, y:e.y, width:rect.width, height:rect.height
      };
      try { elementNode.setPointerCapture(event.pointerId); } catch (_) {}
    }

    root.addEventListener('pointerdown', (event) => {
      const node = event.target.closest('.ds3-el');
      if (node && root.contains(node)) startDrag(event, node);
    });

    window.addEventListener('pointermove', (event) => {
      if (!drag) return;
      const e = current().elements.find((item) => item.id === drag.id);
      if (!e) return;
      e.x = clamp(drag.x + ((event.clientX - drag.startX) / drag.width) * 100, 1, 99);
      e.y = clamp(drag.y + ((event.clientY - drag.startY) / drag.height) * 100, 1, 99);
      renderLabel();
      renderInspector();
    });

    window.addEventListener('pointerup', () => {
      if (!drag) return;
      drag = null;
      pushHistory();
      markUnsaved();
    });

    // Selection and layer clicks.
    on('.ds3-el', 'click', (event, node) => { event.stopPropagation(); select(node.dataset.id); });
    on('[data-layer]', 'click', (event, button) => { event.preventDefault(); select(button.dataset.layer); });

    // Add buttons: supports both data-add and data-action markup.
    on('[data-add]', 'click', (event, button) => { event.preventDefault(); add(button.dataset.add); });
    on('[data-action]', 'click', (event, button) => {
      const action = button.dataset.action;
      const actions = {
        'add-text':'text', 'add-qr':'qr', 'add-shape':'shape', 'add-logo':'logo', 'add-divider':'divider', 'add-artwork':'artwork'
      };
      if (actions[action]) { event.preventDefault(); add(actions[action]); return; }
      if (action === 'undo') { event.preventDefault(); undo(); }
      else if (action === 'redo') { event.preventDefault(); redo(); }
      else if (action === 'duplicate') { event.preventDefault(); duplicate(); }
      else if (action === 'delete') { event.preventDefault(); removeSelected(); }
      else if (action === 'layer-up') { event.preventDefault(); moveLayer(1); }
      else if (action === 'layer-down') { event.preventDefault(); moveLayer(-1); }
      else if (action === 'toggle-grid') { event.preventDefault(); state.grid = !state.grid; renderLabel(); button.classList.toggle('active', state.grid); }
      else if (action === 'zoom-in') { event.preventDefault(); state.zoom = clamp(state.zoom + .1, .6, 1.5); applyZoom(); }
      else if (action === 'zoom-out') { event.preventDefault(); state.zoom = clamp(state.zoom - .1, .6, 1.5); applyZoom(); }
    });

    // Direct ID fallback for older Phase 3 markup.
    // Uses delegated selectors so a button that has both an id and data-action
    // still fires exactly once.
    on('#undoBtn, [data-action="undo"]', 'click', (event) => { event.preventDefault(); undo(); });
    on('#redoBtn, [data-action="redo"]', 'click', (event) => { event.preventDefault(); redo(); });
    on('#duplicateElement, #inspectorDuplicate, [data-action="duplicate"]', 'click', (event) => { event.preventDefault(); duplicate(); });
    on('#deleteElement, #inspectorDelete, [data-action="delete"]', 'click', (event) => { event.preventDefault(); removeSelected(); });
    on('#layerUp, [data-action="layer-up"]', 'click', (event) => { event.preventDefault(); moveLayer(1); });
    on('#layerDown, [data-action="layer-down"]', 'click', (event) => { event.preventDefault(); moveLayer(-1); });
    on('#gridBtn, [data-action="toggle-grid"]', 'click', (event) => { event.preventDefault(); state.grid = !state.grid; renderLabel(); });
    on('#zoomIn, [data-action="zoom-in"]', 'click', (event) => { event.preventDefault(); state.zoom = clamp(state.zoom + .1, .6, 1.5); applyZoom(); });
    on('#zoomOut, [data-action="zoom-out"]', 'click', (event) => { event.preventDefault(); state.zoom = clamp(state.zoom - .1, .6, 1.5); applyZoom(); });
    on('#fitBtn, [data-action="fit"]', 'click', (event) => { event.preventDefault(); state.zoom = 1; applyZoom(); });
    on('#lockElement, [data-action="lock"]', 'click', (event) => {
      event.preventDefault();
      const e = selected();
      if (!e) return;
      e.locked = !e.locked;
      pushHistory();
      render();
      markUnsaved();
    });
    on('#alignCenter, [data-action="align-center"]', 'click', (event) => { event.preventDefault(); updateElement('x', 50); });
    on('#showArtwork, [data-action="show-artwork"]', 'click', (event) => { event.preventDefault(); state.global.show_artwork = true; renderLabel(); markUnsaved(); });
    on('#hideArtwork, [data-action="hide-artwork"]', 'click', (event) => { event.preventDefault(); state.global.show_artwork = false; renderLabel(); markUnsaved(); });

    // Side switch.
    on('[data-side]', 'click', (event, button) => {
      event.preventDefault();
      if (!['front','back'].includes(button.dataset.side)) return;
      state.activeSide = button.dataset.side;
      state.selectedId = null;
      render();
    });

    // Panel tabs.
    on('[data-panel]', 'click', (event, button) => {
      event.preventDefault();
      const panel = button.dataset.panel;
      $$('[data-panel]').forEach((item) => item.classList.toggle('active', item === button));
      $$('[data-panel-view]').forEach((item) => item.classList.toggle('active', item.dataset.panelView === panel));
    });
    on('[data-panel-view]', 'click', (event, button) => {
      const panel = button.dataset.panelView;
      $$('[data-panel-view]').forEach((item) => item.classList.toggle('active', item === button));
      $$('[data-panel]').forEach((item) => item.classList.toggle('active', item.dataset.panel === panel));
    });

    // Inspector controls.
    const inspectorMap = {
      elText:'text', elFont:'font', elSize:'size', elWeight:'weight', elX:'x', elY:'y', elW:'w', elH:'h',
      elRotate:'rotate', elOpacity:'opacity', elColor:'color', elBorder:'border', elShape:'shape'
    };
    on('#elText, #elFont, #elSize, #elWeight, #elX, #elY, #elW, #elH, #elRotate, #elOpacity, #elColor, #elBorder, #elShape', 'input', (event, field) => {
      const key = inspectorMap[field.id];
      if (!key) return;
      let value = field.value;
      if (['elSize','elWeight','elX','elY','elW','elH','elRotate','elOpacity'].includes(field.id)) value = num(value);
      updateElement(key, value);
    });
    on('#elFont, #elShape', 'change', (event, field) => {
      const key = inspectorMap[field.id];
      if (key) updateElement(key, field.value);
    });
    on('[data-align]', 'click', (event, button) => { event.preventDefault(); updateElement('align', button.dataset.align); });

    // Global style/print settings.
    on('#bgColor', 'input', (event, field) => { current().background = field.value; pushHistory(); renderLabel(); markUnsaved(); });
    on('#accentColor', 'input', (event, field) => { current().accent = field.value; pushHistory(); renderLabel(); markUnsaved(); });
    on('#fontFamily', 'change', (event, field) => { state.global.font = field.value; pushHistory(); render(); markUnsaved(); });
    on('#qrUrl', 'input', (event, field) => { state.global.qr_url = field.value; renderLabel(); markUnsaved(); });
    on('#printWidth', 'input', (event, field) => { state.global.print_width_mm = num(field.value, 70); markUnsaved(); });
    on('#printHeight', 'input', (event, field) => { state.global.print_height_mm = num(field.value, 160); markUnsaved(); });
    on('#printDpi', 'input change', (event, field) => { state.global.print_dpi = num(field.value, 300); markUnsaved(); });
    on('.ds3-presets button', 'click', (event, button) => {
      event.preventDefault();
      current().background = button.dataset.bg || current().background;
      current().accent = button.dataset.accent || current().accent;
      state.global.font = button.dataset.font || state.global.font;
      pushHistory(); render(); markUnsaved();
    });

    // Upload inputs.
    on('#logoUpload', 'change', (event, input) => upload(input.files?.[0], 'logo'));
    on('#frontArtworkUpload', 'change', (event, input) => upload(input.files?.[0], 'front_artwork'));
    on('#backArtworkUpload', 'change', (event, input) => upload(input.files?.[0], 'back_artwork'));

    // Save / version / preview / export / print.
    const save = () => {
      const form = document.getElementById('designSaveForm');
      const dataInput = document.getElementById('designDataInput');
      if (!form || !dataInput) { console.error('[JALVAN Design Studio] Save form is missing.'); return; }
      dataInput.value = JSON.stringify(prepareData());
      setValue('#versionNameInput', $('#versionName')?.value || version.name || '');
      setValue('#changeNoteInput', $('#changeNote')?.value || '');
      setText('#saveState', 'Saving…');
      form.submit();
    };
    const newVersion = () => {
      if (!window.confirm('Create a new version from the current design?')) return;
      const form = document.getElementById('newVersionForm');
      const dataInput = document.getElementById('newVersionDataInput');
      if (!form || !dataInput) { console.error('[JALVAN Design Studio] New version form is missing.'); return; }
      dataInput.value = JSON.stringify(prepareData());
      setValue('#newVersionNoteInput', $('#changeNote')?.value || 'Created from previous version');
      form.submit();
    };
    const preview = () => {
      const url = root.dataset.previewUrl || root.dataset.showUrl;
      if (url) window.open(url, '_blank');
    };
    const exportSide = async (sideName) => {
      if (!window.html2canvas) { alert('Export library is not loaded.'); return; }
      const oldSide = state.activeSide;
      state.activeSide = sideName;
      state.selectedId = null;
      renderLabel();
      await new Promise((resolve) => setTimeout(resolve, 200));
      const label = $('#previewLabel');
      if (!label) return;
      try {
        const canvas = await window.html2canvas(label, {
          backgroundColor:null,
          scale:Math.max(2, (state.global.print_dpi || 300) / 150),
          useCORS:true
        });
        const link = document.createElement('a');
        link.download = `${payload.design?.design_code || 'JALVAN'}-V${version.version_no || 1}-${sideName}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
      } catch (error) {
        console.error('[JALVAN Design Studio] Export failed:', error);
        alert('Export failed. Please try again.');
      } finally {
        state.activeSide = oldSide;
        render();
      }
    };
    const printLabel = () => {
      const label = $('#previewLabel');
      if (!label) return;
      const w = window.open('', '_blank');
      if (!w) return;
      w.document.write(`<html><head><title>JALVAN Label Preview</title><style>body{margin:0;display:grid;place-items:center;min-height:100vh;background:#eee}.ds3-label{position:relative;width:70mm;height:160mm;overflow:hidden}</style></head><body>${label.outerHTML}</body></html>`);
      w.document.close();
      setTimeout(() => w.print(), 500);
    };

    on('#saveDesignBtn', 'click', (event) => { event.preventDefault(); save(); });
    on('#newVersionBtn', 'click', (event) => { event.preventDefault(); newVersion(); });
    on('#previewBtn', 'click', (event) => { event.preventDefault(); preview(); });
    on('#exportFront', 'click', (event) => { event.preventDefault(); exportSide('front'); });
    on('#exportBack', 'click', (event) => { event.preventDefault(); exportSide('back'); });
    on('#printLabel', 'click', (event) => { event.preventDefault(); printLabel(); });

    // Keyboard shortcuts.
    document.addEventListener('keydown', (event) => {
      if (!root || !document.body.contains(root)) return;
      if (['INPUT','TEXTAREA','SELECT'].includes(document.activeElement?.tagName)) return;
      if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'z') {
        event.preventDefault(); event.shiftKey ? redo() : undo(); return;
      }
      if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'y') {
        event.preventDefault(); redo(); return;
      }
      const e = selected();
      if (!e || e.locked) return;
      if (event.key === 'Delete' || event.key === 'Backspace') { event.preventDefault(); removeSelected(); return; }
      let dx = 0, dy = 0;
      if (event.key === 'ArrowLeft') dx = -.5;
      if (event.key === 'ArrowRight') dx = .5;
      if (event.key === 'ArrowUp') dy = -.5;
      if (event.key === 'ArrowDown') dy = .5;
      if (dx || dy) {
        event.preventDefault();
        e.x = clamp(e.x + dx, 1, 99); e.y = clamp(e.y + dy, 1, 99);
        pushHistory(); renderLabel(); renderInspector(); markUnsaved();
      }
    });

    // Click empty label to deselect.
    on('#previewLabel', 'click', (event, label) => {
      if (event.target === label) select(null);
    });

    // Initial history/render. This also proves the editor JS initialized.
    pushHistory();
    render();
    root.dataset.editorReady = 'true';
    console.info('[JALVAN Design Studio] Editor initialized successfully.');
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, {once:true});
  } else {
    init();
  }
})();
