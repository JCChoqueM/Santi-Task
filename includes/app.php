<?php 

require __DIR__ . '/../vendor/autoload.php';

// Cargar .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require 'funciones.php';
require 'database.php';

// Conectarnos a la base de datos
use Model\ActiveRecord;
ActiveRecord::setDB($db);