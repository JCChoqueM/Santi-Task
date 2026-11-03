// Escapa texto antes de ponerlo en innerHTML (equivale a esc() de ClaudeTask)
export function esc(s) {
  return String(s ?? '').replace(/[&<>"']/g, (c) => ({
    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
  }[c]));
}

// POST con token CSRF. datos puede ser un objeto o un FormData.
export async function post(url, datos = {}) {
  const fd = datos instanceof FormData ? datos : new FormData();
  if (!(datos instanceof FormData)) {
    for (const [k, v] of Object.entries(datos)) {
      if (Array.isArray(v)) v.forEach((x) => fd.append(k + '[]', x));
      else fd.append(k, v ?? '');
    }
  }
  fd.set('csrf', window.CSRF);
  const r = await fetch(url, { method: 'POST', body: fd });
  const json = await r.json().catch(() => ({ tipo: 'error', mensaje: 'Respuesta no válida' }));
  return { ok: r.ok && json.tipo !== 'error', ...json };
}

export async function get(url) {
  const r = await fetch(url);
  return r.json();
}

// Aviso temporal abajo en pantalla
export function toast(mensaje, error = false) {
  let t = document.querySelector('#toast');
  if (!t) {
    t = document.createElement('div');
    t.id = 'toast';
    t.setAttribute('role', 'status');
    document.body.appendChild(t);
  }
  t.textContent = mensaje;
  t.className = error ? 'toast error' : 'toast';
  t.hidden = false;
  clearTimeout(t._h);
  t._h = setTimeout(() => (t.hidden = true), 3000);
}

// Fecha "2026-11-05" -> "5 nov"
export function fmtFecha(f) {
  if (!f) return '';
  const d = new Date(f + 'T00:00:00');
  return d.toLocaleDateString('es', { day: 'numeric', month: 'short' }).replace('.', '');
}

// Barra segmentada: una celda por parte
export function barra(partes) {
  return '<div class="seg" aria-hidden="true">' +
    partes.map((p) => `<i class="s-${p.estado}"></i>`).join('') + '</div>';
}

export function insignia(estado, rol) {
  const textos = {
    tutor:    { pendiente: 'Pendiente', hecha: 'Por revisar', aprobada: 'Aprobada' },
    tutelado: { pendiente: 'Por hacer', hecha: 'Esperando al tutor', aprobada: 'Aprobada' },
  };
  return `<span class="est est-${estado}">${textos[rol][estado]}</span>`;
}

const ACCIONES = { hecha: 'marcó como hecha', aprobar: 'aprobó', devolver: 'devolvió', reabrir: 'reabrió' };

export async function abrirHistorial(url) {
  const dlg = document.querySelector('#dlg-historial');
  const ul = document.querySelector('#lista-historial');
  const { historial = [] } = await get('/api/historial?url=' + encodeURIComponent(url));
  ul.innerHTML = historial.length
    ? historial.map((h) =>
        `<li><strong>${esc(h.por)}</strong> ${ACCIONES[h.accion] || esc(h.accion)} «${esc(h.parte)}»
         <small>${esc(h.cuando)}</small></li>`).join('')
    : '<li>Sin movimientos todavía.</li>';
  dlg.showModal();
}

// Cierra cualquier <dialog> con data-act="cerrar"
export function activarCierres() {
  document.addEventListener('click', (e) => {
    if (e.target.closest('[data-act="cerrar"]')) e.target.closest('dialog').close();
  });
}
