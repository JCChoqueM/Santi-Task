<div class="contenedor olvide">

    <?php include_once __DIR__ . '/../templates/nombre-sitio.php'; ?>
    <!--section contenedor-sm -->
    <div class="contenedor-sm">
        <p class="descripcion-pagina">Recupera tu acceso a ClaudeTask</p>
        <?php include_once __DIR__ . '/../templates/alertas.php'; ?>
        <!-- BLOQUE formulario[inicio]-->
        <form action="/olvide" method="POST" class="formulario" novalidate>
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

            <!-- subBloque2  email[inicio]-->
            <div class="campo">
                <label for="email">Email</label>
                <input type="email" id="email" placeholder="Tu Email" name="email">
            </div>
            <!-- !subBloque2 fin email - [fin]-->



            <input type="submit" class="boton" value="Enviar Instrucciones">
        </form>
        <!-- !BLOQUE fin - formulario[fin]-->

    </div>

    <!-- section2 enlaces[inicio] -->
    <div class="acciones">
        <a href="/">¿Ya tienes una cuenta? Iniciar Sesión</a>
        <a href="/crear">Aun no tienes una cuenta? Obtener una</a>

    </div>
    <!-- !section2 fin - enlaces[fin] -->

    <!-- !section contenedor-sm -->
</div>