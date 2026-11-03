<div class="barra-mobile">
    <h1>ClaudeTask</h1>
    <div class="menu">
        <button type="button" id="mobile-menu" class="icono-btn" aria-label="Abrir menú">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </div>
</div>

<div class="barra">
    <p>Hola: <span><?= s($_SESSION['nombre'] ?? '') ?></span>
        <small class="rol"><?= ($_SESSION['rol'] ?? '') === 'tutor' ? 'Tutor' : 'Alumno' ?></small></p>
    <a href="/logout" class="cerrar-sesion">Cerrar Sesión</a>
</div>
