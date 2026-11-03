<?php $esTutor = ($_SESSION['rol'] ?? '') === 'tutor'; ?>
<aside class="sidebar">
    <div class="contenedor-sidebar">
        <h2>ClaudeTask</h2>
        <div class="cerrar-menu">
            <img id="cerrar-menu" src="build/img/cerrar.svg" alt="imagen cerrar menu">
        </div>
    </div>
    <nav class="sidebar-nav">
        <?php if ($esTutor): ?>
            <a class="<?= ($titulo === 'Tareas') ? 'activo' : '' ?>" href="/dashboard">Tareas</a>
            <a class="<?= ($titulo === 'Nueva tarea') ? 'activo' : '' ?>" href="/crear-tarea">Nueva tarea</a>
            <a class="<?= ($titulo === 'Tutelados') ? 'activo' : '' ?>" href="/tutelados">Tutelados</a>
        <?php else: ?>
            <a class="<?= ($titulo === 'Mis tareas') ? 'activo' : '' ?>" href="/mis-tareas">Mis tareas</a>
        <?php endif; ?>
        <a class="<?= ($titulo === 'Perfil') ? 'activo' : '' ?>" href="/perfil">Perfil</a>
    </nav>
    <div class="cerrar-sesion-mobile">
        <a href="/logout" class="cerrar-sesion">Cerrar Sesión</a>
    </div>
</aside>
