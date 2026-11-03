import { esc, post, get, toast, fmtFecha, barra, insignia, abrirHistorial, activarCierres } from './modules/comun.js';

(function () {
  const cont = document.querySelector('#tareas');
  const fTutelado = document.querySelector('#f-tutelado');
  const fRevisar = document.querySelector('#f-revisar');
  let tareas = [];

  activarCierres();

  async function cargar() {
    const q = new URLSearchParams();
    if (fTutelado.value) q.set('tutelado', fTutelado.value);
    if (fRevisar.checked) q.set('porRevisar', '1');
    const data = await get('/api/tareas?' + q);
    tareas = data.tareas || [];
    pintar();
  }

  function pintarParte(p) {
    let acciones = '';
    if (p.estado === 'hecha') {
      acciones = `
        <input type="text" class="com" placeholder="Comentario (opcional)" maxlength="500" aria-label="Comentario">
        <button class="btn-prim" data-act="aprobar" data-id="${p.id}">Aprobar</button>
        <button class="btn-sec" data-act="devolver" data-id="${p.id}">Devolver</button>`;
    } else if (p.estado === 'aprobada') {
      acciones = `<button class="btn-link" data-act="reabrir" data-id="${p.id}">Reabrir</button>`;
    }
    const com = p.comentario ? `<p class="comentario">«${esc(p.comentario)}»</p>` : '';
    return `<li class="parte" data-id="${p.id}">
      <div class="parte-fila"><span>${esc(p.titulo)}</span> ${insignia(p.estado, 'tutor')}</div>
      ${com}<div class="parte-acc">${acciones}</div></li>`;
  }

  function pintar() {
    if (!tareas.length) {
      cont.innerHTML = '<p class="vacio">No hay tareas para mostrar.</p>';
      return;
    }
    cont.innerHTML = tareas.map((t) => {
      const pr = t.progreso;
      const estado = pr.completa
        ? '<span class="badge-done">Completada</span>'
        : `<span class="prog">${pr.ok} de ${pr.total} partes</span>`;
      const venc = t.vencida ? '<span class="venc">Vencida</span>' : '';
      return `<article class="tarea-card" data-url="${esc(t.url)}">
        <header>
          <div><h4>${esc(t.titulo)}</h4>
          <p class="meta">${t.fecha_limite ? 'Para el ' + fmtFecha(t.fecha_limite) : 'Sin fecha'} ${venc}</p></div>
          ${estado}
        </header>
        ${t.descripcion ? `<p class="desc">${esc(t.descripcion)}</p>` : ''}
        ${barra(t.partes)}
        <ul class="partes">${t.partes.map(pintarParte).join('')}</ul>
        <footer>
          <button class="btn-link" data-act="editar">Editar</button>
          <button class="btn-link" data-act="duplicar">Duplicar</button>
          <button class="btn-link" data-act="historial">Historial</button>
          <button class="btn-link peligro" data-act="eliminar">Eliminar</button>
        </footer>
      </article>`;
    }).join('');
  }

  // ---- Revisar partes ----
  async function revisar(btn, accion) {
    const li = btn.closest('.parte');
    const comentario = li.querySelector('.com')?.value || '';
    const r = await post('/api/parte/revisar', { id: btn.dataset.id, accion, comentario });
    toast(r.ok ? 'Hecho' : r.mensaje, !r.ok);
    if (r.ok) cargar();
  }

  // ---- Editar ----
  const dlgEditar = document.querySelector('#dlg-editar');
  const formEditar = document.querySelector('#form-editar');
  const listaPartes = document.querySelector('#editar-partes');

  function filaParte(id = '', titulo = '') {
    return `<div class="np-row">
      <input type="text" class="np-in" data-id="${id}" value="${esc(titulo)}" placeholder="Parte" aria-label="Parte">
      <button type="button" class="btn-link" data-act="quitar-parte">Quitar</button></div>`;
  }

  function abrirEditar(url) {
    const t = tareas.find((x) => x.url === url);
    formEditar.url.value = t.url;
    formEditar.titulo.value = t.titulo;
    formEditar.descripcion.value = t.descripcion || '';
    formEditar.fecha_limite.value = t.fecha_limite || '';
    listaPartes.innerHTML = t.partes.map((p) => filaParte(p.id, p.titulo)).join('');
    dlgEditar.showModal();
  }

  formEditar.addEventListener('submit', async (e) => {
    e.preventDefault();
    const partes = [...listaPartes.querySelectorAll('.np-in')].map((i) => ({
      id: i.dataset.id ? Number(i.dataset.id) : null,
      titulo: i.value,
    }));
    const fd = new FormData(formEditar);
    fd.set('partes', JSON.stringify(partes));
    const r = await post('/api/tarea/actualizar', fd);
    toast(r.ok ? 'Tarea actualizada' : r.mensaje, !r.ok);
    if (r.ok) { dlgEditar.close(); cargar(); }
  });

  // ---- Duplicar ----
  const dlgDup = document.querySelector('#dlg-duplicar');
  const formDup = document.querySelector('#form-duplicar');
  formDup.addEventListener('submit', async (e) => {
    e.preventDefault();
    const r = await post('/api/tarea/duplicar', new FormData(formDup));
    toast(r.ok ? `Copiada a ${r.copiadas} tutelado(s)` : r.mensaje, !r.ok);
    if (r.ok) { dlgDup.close(); cargar(); }
  });

  // ---- Delegación de eventos ----
  document.addEventListener('click', async (e) => {
    const b = e.target.closest('[data-act]');
    if (!b) return;
    const act = b.dataset.act;
    const url = b.closest('.tarea-card')?.dataset.url;

    if (['aprobar', 'devolver', 'reabrir'].includes(act)) return revisar(b, act);
    if (act === 'editar') return abrirEditar(url);
    if (act === 'duplicar') {
      formDup.reset();
      formDup.url.value = url;
      return dlgDup.showModal();
    }
    if (act === 'historial') return abrirHistorial(url);
    if (act === 'eliminar') {
      if (!confirm('¿Eliminar esta tarea con sus partes y su historial?')) return;
      const r = await post('/api/tarea/eliminar', { url });
      toast(r.ok ? 'Tarea eliminada' : r.mensaje, !r.ok);
      if (r.ok) cargar();
    }
    if (act === 'add-parte-edit') listaPartes.insertAdjacentHTML('beforeend', filaParte());
    if (act === 'quitar-parte') b.closest('.np-row').remove();
  });

  fTutelado.addEventListener('change', cargar);
  fRevisar.addEventListener('change', cargar);
  cargar();
})();
