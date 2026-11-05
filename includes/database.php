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

// La conexion siempre en utf8mb4: sin esto usa el predeterminado del servidor (latin1 en DOM Cloud)
// y las tildes se guardan doblemente codificadas
if (!mysqli_set_charset($db, 'utf8mb4')) {
    error_log('MySQL: no se pudo fijar utf8mb4: ' . mysqli_error($db));
    http_response_code(500);
    echo 'Error de conexión con la base de datos.';
    exit;
}
