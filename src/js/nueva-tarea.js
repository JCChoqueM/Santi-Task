import { post, toast } from './modules/comun.js';

(function () {
  const form = document.querySelector('#form-tarea');
  if (!form) return;
  const cont = document.querySelector('#partes');
  const msg = document.querySelector('#msg');

  function fila() {
    const d = document.createElement('div');
    d.className = 'np-row';
    d.innerHTML = `<input type="text" class="np-in" placeholder="Parte de la tarea" aria-label="Parte de la tarea">
      <button type="button" class="btn-link" data-quitar>Quitar</button>`;
    cont.appendChild(d);
  }
  fila();

  document.querySelector('#add-parte').addEventListener('click', fila);
  cont.addEventListener('click', (e) => {
    if (e.target.matches('[data-quitar]')) e.target.closest('.np-row').remove();
  });

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    msg.hidden = true;
    const fd = new FormData(form);
    cont.querySelectorAll('.np-in').forEach((i) => fd.append('partes[]', i.value));

    const r = await post('/api/tarea', fd);
    if (!r.ok) {
      msg.textContent = r.mensaje;
      msg.className = 'alerta error';
      msg.hidden = false;
      return;
    }
    toast('Tarea creada');
    window.location.href = '/dashboard';
  });
})();
