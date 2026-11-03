import { esc, post, get, toast, fmtFecha, barra, insignia, abrirHistorial, activarCierres } from './modules/comun.js';

(function () {
  const cont = document.querySelector('#tareas');
  activarCierres();

  async function cargar() {
    const { tareas = [] } = await get('/api/tareas');
    if (!tareas.length) {
      cont.innerHTML = '<p class="vacio">No tienes tareas por ahora.</p>';
      return;
    }
    cont.innerHTML = tareas.map(tarjeta).join('');
  }

  function parte(p) {
    const aviso = p.devuelta
      ? `<div class="aviso-devuelta"><strong>Tu tutor pidió correcciones.</strong>
         ${p.comentario ? `<p>«${esc(p.comentario)}»</p>` : ''}</div>`
      : (p.comentario ? `<p class="comentario">«${esc(p.comentario)}»</p>` : '');
    const boton = p.estado === 'pendiente'
      ? `<button class="btn-prim" data-act="hecha" data-id="${p.id}">Ya lo hice</button>`
      : '';
    return `<li class="parte">
      <div class="parte-fila"><span>${esc(p.titulo)}</span> ${insignia(p.estado, 'tutelado')}</div>
      ${aviso}<div class="parte-acc">${boton}</div></li>`;
  }

  function tarjeta(t) {
    const pr = t.progreso;
    const estado = pr.completa
      ? '<span class="badge-done">Completada</span>'
      : `<span class="prog">${pr.ok} de ${pr.total} partes</span>`;
    return `<article class="tarea-card${t.vencida ? ' vencida' : ''}" data-url="${esc(t.url)}">
      <header>
        <div><h4>${esc(t.titulo)}</h4>
        <p class="meta">${t.fecha_limite ? 'Para el ' + fmtFecha(t.fecha_limite) : 'Sin fecha'}
        ${t.vencida ? '<span class="venc">Vencida</span>' : ''}</p></div>
        ${estado}
      </header>
      ${t.descripcion ? `<p class="desc">${esc(t.descripcion)}</p>` : ''}
      ${barra(t.partes)}
      <ul class="partes">${t.partes.map(parte).join('')}</ul>
      <footer><button class="btn-link" data-act="historial">Historial</button></footer>
    </article>`;
  }

  document.addEventListener('click', async (e) => {
    const b = e.target.closest('[data-act]');
    if (!b) return;
    if (b.dataset.act === 'hecha') {
      b.disabled = true;                       // evita el doble clic
      const r = await post('/api/parte/hecha', { id: b.dataset.id });
      toast(r.mensaje, !r.ok);
      if (r.ok) cargar(); else b.disabled = false;
    }
    if (b.dataset.act === 'historial') abrirHistorial(b.closest('.tarea-card').dataset.url);
  });

  cargar();
})();
