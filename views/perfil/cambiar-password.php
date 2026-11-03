<?php include_once __DIR__ . '/../templates/header-panel.php' ?>
<div class="contenedor-sm">
   <?php include_once __DIR__ . '/../templates/alertas.php'; ?>
   <a
      href="/perfil"
      class="enlace"
   >Volver a Perfil</a>
   <form
      class="formulario"
      method="POST"
      action="/cambiar-password"
   >
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
      <?php
      /** @var \Model\Usuario $usuario */
      ?>
      <!-- section  Password Actual[inicio] -->
      <div class="campo">
         <label for="nombre">Password Actual</label>
         <input
            type="password"
            name="password_actual"
            placeholder="Tu Password Actual"
         >
      </div>
      <!-- !section  fin - Password Actual[fin] -->
      <!-- section1 Password Nuevo[inicio] -->
      <div class="campo">
         <label for="nombre">Password Nuevo</label>
         <input
            type="password"
            name="password_nuevo"
            placeholder="Tu Password Nuevo"
         >
      </div>
      <!-- !section1 fin - Password Nuevo[fin] -->

      <input
         type="submit"
         class="boton"
         value="Guardar Cambios"
      >

   </form>

</div>
<?php include_once __DIR__ . '/../templates/footer-panel.php' ?>