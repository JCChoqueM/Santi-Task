import { post, toast } from './modules/comun.js';

(function () {
  const form = document.querySelector('#form-tutelado');
  const msg = document.querySelector('#msg');
  const creado = document.querySelector('#creado');
  const enviar = form.querySelector('[type="submit"]');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    msg.hidden = true;
    enviar.disabled = true;               // evita crear dos tutelados con un doble clic
    let r;
    try {
      r = await post('/api/tutelado', new FormData(form));
    } finally {
      enviar.disabled = false;
    }
    if (!r.ok) {
      msg.textContent = r.mensaje;
      msg.className = 'alerta error';
      msg.hidden = false;
      return;
    }
    // textContent: nunca innerHTML con datos del servidor
    document.querySelector('#c-alias').textContent = r.alias;
    document.querySelector('#c-pass').textContent = r.password;
    creado.hidden = false;
    form.reset();
    toast('Tutelado creado');
    // La lista se refresca al recargar; el aviso se añade una sola vez
    if (!document.querySelector('#nota-recarga')) {
      document.querySelector('.subtitulo').insertAdjacentHTML('afterend',
        '<p class="nota" id="nota-recarga">Recarga la página para ver al nuevo tutelado en la lista.</p>');
    }
  });

  document.addEventListener('click', async (e) => {
    const b = e.target.closest('[data-act]');
    if (!b) return;
    const id = b.closest('[data-id]')?.dataset.id;

    if (b.dataset.act === 'reset') {
      const pw = prompt('Nueva contraseña (mínimo 6 caracteres):');
      if (!pw) return;
      const r = await post('/api/tutelado/password', { id, password: pw });
      toast(r.mensaje, !r.ok);
    }
    if (b.dataset.act === 'vincular') {
      const correo = prompt('Correo del tutor que quieres vincular:');
      if (!correo) return;
      const r = await post('/api/vinculo', { tutelado_id: id, correo });
      toast(r.mensaje, !r.ok);
      if (r.ok) setTimeout(() => location.reload(), 900);   // deja ver el aviso
    }
    if (b.dataset.act === 'desvincular') {
      if (!confirm('¿Quitar a este tutor?')) return;
      const r = await post('/api/vinculo/eliminar', { tutelado_id: id, tutor_id: b.dataset.tutor });
      toast(r.mensaje, !r.ok);
      if (r.ok) setTimeout(() => location.reload(), 900);
    }
  });
})();
