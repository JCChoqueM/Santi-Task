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
    session_start();
}

require 'funciones.php';
require 'database.php';

// Conectarnos a la base de datos
use Model\ActiveRecord;
ActiveRecord::setDB($db);