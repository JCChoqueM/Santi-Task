<div class="barra-mobile">
    <h1>ClaudeTask</h1>
    <div class="menu">
        <img id="mobile-menu" src="build/img/menu.svg" alt="imagen menu">
    </div>
</div>

<div class="barra">
    <p>Hola: <span><?= s($_SESSION['nombre'] ?? '') ?></span>
        <small class="rol"><?= ($_SESSION['rol'] ?? '') === 'tutor' ? 'Tutor' : 'Alumno' ?></small></p>
    <a href="/logout" class="cerrar-sesion">Cerrar Sesión</a>
</div>
