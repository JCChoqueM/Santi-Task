<?php

$db = mysqli_connect(
    $_ENV['DB_HOST'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS'],
    $_ENV['DB_NAME']
);

if (!$db) {
    // El detalle (errno y mensaje de MySQL) solo va al log, nunca a la página
    error_log('MySQL: ' . mysqli_connect_errno() . ' ' . mysqli_connect_error());
    http_response_code(500);
    echo 'Error de conexión con la base de datos.';
    exit;
}
