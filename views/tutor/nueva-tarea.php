<?php include_once __DIR__ . '/../templates/header-panel.php' ?>

<div class="contenedor-sm">
    <?php if (!$tutelados): ?>
        <p class="vacio">Primero crea un tutelado en la sección <a href="/tutelados">Tutelados</a>.</p>
    <?php else: ?>
    <form id="form-tarea" class="formulario" novalidate>
        <div class="campo">
            <label for="tutelado_id">Para</label>
            <select id="tutelado_id" name="tutelado_id" required>
                <?php foreach ($tutelados as $al): ?>
                    <option value="<?= (int) $al->id ?>"><?= s($al->nombre) ?> (<?= s($al->alias) ?>)<?= $al->curso ? ' · ' . s($al->curso) : '' ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="campo">
            <label for="titulo">Título</label>
            <input type="text" id="titulo" name="titulo" placeholder="Ej. Ejercicios de fracciones" required>
        </div>
        <div class="campo">
            <label for="descripcion">Descripción</label>
            <textarea id="descripcion" name="descripcion" rows="3" placeholder="Instrucciones"></textarea>
        </div>
        <div class="campo">
            <label for="fecha_limite">Fecha límite</label>
            <input type="date" id="fecha_limite" name="fecha_limite">
        </div>
        <div class="campo">
            <label>Partes</label>
            <div id="partes"></div>
            <button type="button" class="btn-sec" id="add-parte">+ Añadir parte</button>
        </div>
        <div id="msg" class="alerta" hidden></div>
        <input type="submit" class="boton" value="Crear tarea">
    </form>
    <?php endif; ?>
</div>

<?php
$script = vite_script('src/js/nueva-tarea.js');
include_once __DIR__ . '/../templates/footer-panel.php';
