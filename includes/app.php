<?php 

require __DIR__ . '/../vendor/autoload.php';

// Cargar .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Errores: en desarrollo se muestran; en cualquier otro caso (o si falta ENVIRONMENT) solo se registran
$esDesarrollo = ($_ENV['ENVIRONMENT'] ?? '') === 'development';
error_reporting(E_ALL);
ini_set('display_errors', $esDesarrollo ? '1' : '0');
ini_set('log_errors', '1');

if (session_status() === PHP_SESSION_NONE) {
    // Cookie de sesion: no legible desde JavaScript, no se envia en peticiones de otros sitios y,
    // si APP_URL es https, solo viaja por HTTPS
    ini_set('session.use_strict_mode', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => str_starts_with((string) ($_ENV['APP_URL'] ?? ''), 'https://'),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require 'funciones.php';
enviarCabecerasSeguridad();
require 'database.php';

// Conectarnos a la base de datos
use Model\ActiveRecord;
ActiveRecord::setDB($db);