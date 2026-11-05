<?php

require_once __DIR__ . '/../includes/app.php';


use Controllers\LoginController;
use Controllers\TutorController;
use Controllers\AlumnoController;
use Controllers\PerfilController;
use Controllers\TareaController;
use Controllers\ParteController;
use MVC\Router;

$router = new Router();

// Acceso
$router->get('/', [LoginController::class, 'login']);
$router->post('/', [LoginController::class, 'login']);
$router->get('/logout', [LoginController::class, 'logout']);

// Crear cuenta (tutores)
$router->get('/crear', [LoginController::class, 'crear']);
$router->post('/crear', [LoginController::class, 'crear']);

// Olvidé mi password
$router->get('/olvide', [LoginController::class, 'olvide']);
$router->post('/olvide', [LoginController::class, 'olvide']);

// Nuevo password
$router->get('/reestablecer', [LoginController::class, 'reestablecer']);
$router->post('/reestablecer', [LoginController::class, 'reestablecer']);

// Confirmar cuenta
$router->get('/mensaje', [LoginController::class, 'mensaje']);
$router->get('/confirmar', [LoginController::class, 'confirmar']);

// Zona del tutor
$router->get('/dashboard', [TutorController::class, 'index']);
$router->get('/crear-tarea', [TutorController::class, 'crear_tarea']);
$router->get('/tutelados', [TutorController::class, 'tutelados']);

// Zona del tutelado
$router->get('/mis-tareas', [AlumnoController::class, 'index']);

// Perfil (ambos roles)
$router->get('/perfil', [PerfilController::class, 'perfil']);
$router->post('/perfil', [PerfilController::class, 'perfil']);
$router->get('/cambiar-password', [PerfilController::class, 'cambiar_password']);
$router->post('/cambiar-password', [PerfilController::class, 'cambiar_password']);

// API de tareas
$router->get('/api/tareas', [TareaController::class, 'index']);
$router->post('/api/tarea', [TareaController::class, 'crear']);
$router->post('/api/tarea/actualizar', [TareaController::class, 'actualizar']);
$router->post('/api/tarea/eliminar', [TareaController::class, 'eliminar']);
$router->post('/api/tarea/duplicar', [TareaController::class, 'duplicar']);
$router->get('/api/historial', [TareaController::class, 'historial']);

// API de partes
$router->post('/api/parte/hecha', [ParteController::class, 'marcar_hecha']);
$router->post('/api/parte/revisar', [ParteController::class, 'revisar']);

// API de tutelados y vínculos
$router->post('/api/tutelado', [TutorController::class, 'crear_tutelado']);
$router->post('/api/tutelado/password', [TutorController::class, 'reset_password']);
$router->post('/api/vinculo', [TutorController::class, 'vincular']);
$router->post('/api/vinculo/eliminar', [TutorController::class, 'desvincular']);

// Comprueba y valida las rutas, que existan y les asigna las funciones del Controlador
$router->comprobarRutas();
