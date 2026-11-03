<?php include_once __DIR__ . '/../templates/header-panel.php' ?>

<div class="contenedor-md">
    <div id="tareas" class="lista-tareas" aria-live="polite"></div>
</div>

<!-- Historial -->
<dialog id="dlg-historial" class="modal-dialogo">
    <h3>Historial</h3>
    <ul id="lista-historial" class="historial"></ul>
    <div class="acciones-modal">
        <button type="button" class="btn-sec" data-act="cerrar">Cerrar</button>
    </div>
</dialog>

<?php
$script = vite_script('src/js/alumno.js');
include_once __DIR__ . '/../templates/footer-panel.php';
