<?php include_once __DIR__ . '/../templates/header-panel.php' ?>

<div class="contenedor-md">

    <form id="form-tutelado" class="formulario tarjeta" novalidate>
        <h3>Nuevo tutelado</h3>
        <div class="campo"><label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" placeholder="Nombre del alumno"></div>
        <div class="campo"><label for="curso">Curso (opcional)</label>
            <input type="text" id="curso" name="curso" placeholder="Ej. 3A"></div>
        <div class="campo"><label for="password">Contraseña</label>
            <input type="text" id="password" name="password" placeholder="Mínimo 6 caracteres"></div>
        <div id="msg" class="alerta" hidden></div>
        <input type="submit" class="boton" value="Crear tutelado">
    </form>

    <div id="creado" class="tarjeta aviso" hidden>
        <h3>Entrégale estos datos al alumno</h3>
        <p>Alias: <strong id="c-alias"></strong></p>
        <p>Contraseña: <strong id="c-pass"></strong></p>
        <p class="nota">Solo se muestran ahora. Anótalos antes de salir.</p>
    </div>

    <h3 class="subtitulo">Mis tutelados</h3>
    <?php if (!$lista): ?>
        <p class="vacio">Todavía no tienes tutelados.</p>
    <?php endif; ?>

    <?php foreach ($lista as $fila): $al = $fila['alumno']; ?>
        <article class="tarjeta" data-id="<?= (int) $al->id ?>">
            <header>
                <h4><?= s($al->nombre) ?> <small><?= s($al->curso ?? '') ?></small></h4>
                <p>Alias: <strong><?= s($al->alias) ?></strong></p>
            </header>

            <p class="etiqueta">Tutores</p>
            <ul class="tutores">
                <?php foreach ($fila['tutores'] as $tu): ?>
                    <li>
                        <?= s($tu->nombre) ?> <small><?= s($tu->email) ?></small>
                        <?php if ((int) $tu->id === (int) $al->creado_por): ?><span class="chip">responsable</span><?php endif; ?>
                        <?php if ($fila['soyResponsable'] || (int) $tu->id === (int) $_SESSION['id']): ?>
                            <button type="button" class="btn-link" data-act="desvincular"
                                    data-tutor="<?= (int) $tu->id ?>">Quitar</button>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <div class="acciones-tarjeta">
                <button type="button" class="btn-sec" data-act="vincular">Vincular tutor</button>
                <button type="button" class="btn-sec" data-act="reset">Restablecer contraseña</button>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<?php
$script = vite_script('src/js/tutelados.js');
include_once __DIR__ . '/../templates/footer-panel.php';
