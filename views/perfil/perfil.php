<?php include_once __DIR__ . '/../templates/header-panel.php' ?>
<div class="contenedor-sm">
   <?php include_once __DIR__ . '/../templates/alertas.php'; ?>

   <a href="/cambiar-password" class="enlace">Cambiar Password</a>

   <form class="formulario" method="POST" action="/perfil">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

      <div class="campo">
         <label for="nombre">Nombre</label>
         <input type="text" id="nombre" name="nombre" value="<?= s($usuario->nombre) ?>" placeholder="Tu Nombre">
      </div>

      <?php if ($usuario->rol === 'tutor'): ?>
      <div class="campo">
         <label for="email">Email</label>
         <input type="text" id="email" name="email" value="<?= s($usuario->email ?? '') ?>" placeholder="Tu email">
      </div>
      <?php else: ?>
      <div class="campo">
         <label>Alias</label>
         <input type="text" value="<?= s($usuario->alias) ?>" disabled>
      </div>
      <?php endif; ?>

      <input type="submit" class="boton" value="Guardar Cambios">
   </form>
</div>
<?php include_once __DIR__ . '/../templates/footer-panel.php' ?>
