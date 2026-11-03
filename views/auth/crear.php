<?php
/** @var \Model\Usuario $usuario */
/** @var array $alertas */
?>
<div class="contenedor crear">

    <?php include_once __DIR__ . '/../templates/nombre-sitio.php'; ?>
    <!--section contenedor-sm -->
    <div class="contenedor-sm">
        <p class="descripcion-pagina">Crea tu cuenta en ClaudeTask</p>

        <?php include_once __DIR__ . '/../templates/alertas.php'; ?>

        <!-- BLOQUE formulario[inicio]-->
        <form action="/crear" method="POST" class="formulario">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            <!-- subBloque 1 nombre[inicio]-->
            <div class="campo">
                <label for="nombre">Nombre</label>
                <input
                    type="text"
                    id="nombre"
                    placeholder="Tu Nombre"
                    name="nombre"
                    value="<?php echo $usuario->nombre; ?>">
            </div>
            <!-- !subBloque 1 fin - nombre[fin]-->

            <!-- subBloque2  email[inicio]-->
            <div class="campo">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    placeholder="Tu Email"
                    name="email"
                    value="<?php echo $usuario->email; ?>">
            </div>
            <!-- !subBloque2 fin email - [fin]-->

            <!-- subBloque3  Password[inicio] -->
            <div class="campo">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    placeholder="Tu Password"
                    name="password">
            </div>
            <!-- !subBloque3  fin - Password[fin] -->

            <!-- subBloque3  Password2[inicio] -->
            <div class="campo">
                <label for="password2">Repetir Password</label>
                <input
                    type="password"
                    id="password2"
                    placeholder="Repite tu Password"
                    name="password2">
            </div>
            <!-- !subBloque3  fin - Password2[fin] -->

            <input type="submit" class="boton" value="Crear Cuenta">
        </form>
        <!-- !BLOQUE fin - formulario[fin]-->

    </div>

    <!-- section2 enlaces[inicio] -->
    <div class="acciones">
        <a href="/">¿Ya tienes una cuenta? Iniciar Sesión</a>
        <a href="/olvide">Olvidaste tu password</a>

    </div>
    <!-- !section2 fin - enlaces[fin] -->

    <!-- !section contenedor-sm -->
</div>