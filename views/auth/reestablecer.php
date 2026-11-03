<div class="contenedor reestablecer">

    <?php include_once __DIR__ . '/../templates/nombre-sitio.php'; ?>
    <!--section contenedor-sm -->
    <div class="contenedor-sm">
        <p class="descripcion-pagina">Coloca tu nuevo password</p>
        <?php include_once __DIR__ . '/../templates/alertas.php'; ?>


        <?php if ($mostrar) { ?>
            <!-- BLOQUE formulario[inicio]-->
            <form  method="POST" class="formulario">


                <!-- subBloque2  Password[inicio] -->
                <div class="campo">
                    <label for="password">Password</label>
                    <input type="password" id="password" placeholder="Tu Password" name="password">
                </div>
                <!-- !subBloque2  fin - Password[fin] -->

                <input type="submit" class="boton" value="Guardar Password">
            </form>
            <!-- !BLOQUE fin - formulario[fin]-->
        <?php } ?>
    </div>

    <!-- section2 enlaces[inicio] -->
    <div class="acciones">
        <a href="/crear">Aun no tienes una cuenta? Obtener una</a>
        <a href="/olvide">Olvidaste tu password</a>

    </div>
    <!-- !section2 fin - enlaces[fin] -->

    <!-- !section contenedor-sm -->
</div>