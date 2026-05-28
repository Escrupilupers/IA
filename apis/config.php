<?php
declare(strict_types=1);
function createDataBaseConnection(): mysqli
{
    $connection = new mysqli('localhost', 'root', '', 'restaurante');
    if ($connection->connect_error) {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'Error al conectar a la base de datos']);
        exit;
    }
    $connection->set_charset('utf8mb4');
    return $connection;
}