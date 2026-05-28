<?php
declare(strict_types=1);

require_once __DIR__ . '/cors.php';
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'message' => 'Metodo no permitido. Use POST.'
    ]);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'message' => 'JSON invalido.'
    ]);
    exit;
}

$nombre = trim((string)($payload['nombre'] ?? ''));
$email = trim((string)($payload['email'] ?? ''));
$asunto = trim((string)($payload['asunto'] ?? ''));
$mensaje = trim((string)($payload['mensaje'] ?? ''));

if ($nombre === '' || $email === '' || $asunto === '' || $mensaje === '') {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'message' => 'Todos los campos son obligatorios.'
    ]);
    exit;
}

$connection = createDataBaseConnection();

$createTableSql = <<<SQL
CREATE TABLE IF NOT EXISTS formulario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    asunto VARCHAR(150) NOT NULL,
    mensaje TEXT NOT NULL,
    enviado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
SQL;

if ($connection->connect_error || !$connection->query($createTableSql)) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'No se pudo crear o verificar la tabla de formulario.'
    ]);
    $connection->close();
    exit;
}

$statement = $connection->prepare(
    'INSERT INTO formulario (nombre, email, asunto, mensaje) VALUES (?, ?, ?, ?)' 
);

if ($statement === false) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'No se pudo preparar la insercion en la BD.'
    ]);
    $connection->close();
    exit;
}

$statement->bind_param('ssss', $nombre, $email, $asunto, $mensaje);

if (!$statement->execute()) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'message' => 'Error al guardar el formulario en la base de datos.'
    ]);
    $statement->close();
    $connection->close();
    exit;
}

$response = [
    'ok' => true,
    'message' => 'Formulario enviado correctamente.',
    'id' => $statement->insert_id
];

$statement->close();
$connection->close();

echo json_encode($response);
