<div class="contenedor login">
   
<?php include_once __DIR__ . '/../templates/nombre-sitio.php';?>
    <!--section contenedor-sm -->
    <div class="contenedor-sm">
        <p class="descripcion-pagina">Iniciar Sesión</p>
<?php include_once __DIR__ . '/../templates/alertas.php';?>

        <!-- BLOQUE formulario[inicio]-->
        <form action="/" method="POST" class="formulario" novalidate>
            <!-- subBloque  email[inicio]-->
            <div class="campo">
                <label for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    placeholder="Tu Email"
                    name="email">
            </div>
            <!-- !subBloque fin email - [fin]-->

            <!-- subBloque2  Password[inicio] -->
            <div class="campo">
                <label for="password">Password</label>
                <input
                    type="password"
                    id="password"
                    placeholder="Tu Password"
                    name="password">
            </div>
            <!-- !subBloque2  fin - Password[fin] -->

            <input type="submit" class="boton" value="Iniciar Sesión">
        </form>
        <!-- !BLOQUE fin - formulario[fin]-->

    </div>

    <!-- section2 enlaces[inicio] -->
    <div class="acciones">
        <a href="/crear">Aun no tienes una cuenta? Obtener una</a>
        <a href="/olvide">Olvidaste tu password</a>

    </div>
    <!-- !section2 fin - enlaces[fin] -->

    <!-- !section contenedor-sm -->
</div>