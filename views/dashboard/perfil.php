<?php include_once __DIR__ . '/header-dashboard.php' ?>
<div class="contenedor-sm">
   <?php include_once __DIR__ . '/../templates/alertas.php'; ?>


   <a
      href="/cambiar-password"
      class="enlace"
   >Cambiar Password</a>

   <form
      class="formulario"
      method="POST"
      action="/perfil"
   >
      <?php
      /** @var \Model\Usuario $usuario */
      ?>
      <!-- section  Nombre[inicio] -->
      <div class="campo">
         <label for="nombre">Nombre</label>
         <input
            type="text"
            value="<?php echo $usuario->nombre; ?>"
            id="nombre"
            name="nombre"
            placeholder="Tu Nombre"
         >
      </div>
      <!-- !section  fin - Nombre[fin] -->
      <!-- section1 email[inicio] -->
      <div class="campo">
         <label for="nombre">Email</label>
         <input
            type="text"
            value="<?php echo $usuario->email; ?>"
            id="email"
            name="email"
            placeholder="Tu email"
         >
      </div>
      <!-- !section1 fin - email[fin] -->

      <input
         type="submit"
         class="boton"
         value="Guardar Cambios"
      >

   </form>

</div>
<?php include_once __DIR__ . '/footer-dashboard.php' ?>