<?php include_once __DIR__ . '/../templates/header-panel.php' ?>

<div class="contenedor-md">
    <div class="filtros-tutor">
        <div class="campo">
            <label for="f-tutelado">Tutelado</label>
            <select id="f-tutelado">
                <option value="">Todos</option>
                <?php foreach ($tutelados as $al): ?>
                    <option value="<?= (int) $al->id ?>"><?= s($al->nombre) ?> (<?= s($al->alias) ?>)<?= $al->curso ? ' · ' . s($al->curso) : '' ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <label class="check">
            <input type="checkbox" id="f-revisar"> Solo por revisar
        </label>
    </div>

    <div id="tareas" class="lista-tareas" aria-live="polite"></div>
</div>

<!-- Editar tarea -->
<dialog id="dlg-editar" class="modal-dialogo">
    <form id="form-editar" method="dialog" class="formulario">
        <h3>Editar tarea</h3>
        <input type="hidden" name="url">
        <div class="campo"><label>Título</label><input type="text" name="titulo" required></div>
        <div class="campo"><label>Descripción</label><textarea name="descripcion" rows="3"></textarea></div>
        <div class="campo"><label>Fecha límite</label><input type="date" name="fecha_limite"></div>
        <div class="campo">
            <label>Partes</label>
            <div id="editar-partes"></div>
            <button type="button" class="btn-sec" data-act="add-parte-edit">+ Añadir parte</button>
        </div>
        <div class="acciones-modal">
            <button type="button" class="btn-sec" data-act="cerrar">Cancelar</button>
            <button type="submit" class="btn-prim">Guardar</button>
        </div>
    </form>
</dialog>

<!-- Duplicar tarea -->
<dialog id="dlg-duplicar" class="modal-dialogo">
    <form id="form-duplicar" method="dialog" class="formulario">
        <h3>Duplicar tarea</h3>
        <input type="hidden" name="url">
        <p>Elige a quién se copia:</p>
        <?php foreach ($tutelados as $al): ?>
            <label class="check">
                <input type="checkbox" name="destinos[]" value="<?= (int) $al->id ?>"> <?= s($al->nombre) ?> (<?= s($al->alias) ?>)<?= $al->curso ? ' · ' . s($al->curso) : '' ?>
            </label>
        <?php endforeach; ?>
        <div class="acciones-modal">
            <button type="button" class="btn-sec" data-act="cerrar">Cancelar</button>
            <button type="submit" class="btn-prim">Duplicar</button>
        </div>
    </form>
</dialog>

<!-- Historial -->
<dialog id="dlg-historial" class="modal-dialogo">
    <h3>Historial</h3>
    <ul id="lista-historial" class="historial"></ul>
    <div class="acciones-modal">
        <button type="button" class="btn-sec" data-act="cerrar">Cerrar</button>
    </div>
</dialog>

<?php
$script = vite_script('src/js/tutor.js');
include_once __DIR__ . '/../templates/footer-panel.php';
