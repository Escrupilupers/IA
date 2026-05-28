<?php
declare(strict_types=1);

require_once __DIR__ . '/cors.php';
require __DIR__ . '/config.php';

function isSha1Hash(string $value): bool
{
    return strlen($value) === 40 && ctype_xdigit($value);
}

function isValidPassword(string $plainPassword, string $storedPassword): bool
{
    if ($storedPassword === '') {
        return false;
    }

    if (isSha1Hash($storedPassword)) {
        return hash_equals(strtolower($storedPassword), sha1($plainPassword));
    }

    return hash_equals($storedPassword, $plainPassword);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'ok' => false,
        'exists' => false,
        'message' => 'Metodo no permitido.'
    ]);
    exit;
}

$payload = json_decode(file_get_contents('php://input'), true);

if (!is_array($payload)) {
    http_response_code(400);
    echo json_encode([
        'ok' => false,
        'exists' => false,
        'message' => 'JSON invalido.'
    ]);
    exit;
}

$username = trim((string)($payload['username'] ?? ''));
$password = (string)($payload['password'] ?? '');

if ($username === '' || $password === '') {
    http_response_code(422);
    echo json_encode([
        'ok' => false,
        'exists' => false,
        'message' => 'Usuario y contrasena son obligatorios.'
    ]);
    exit;
}

$connection = createDataBaseConnection();
$statement = $connection->prepare(
    'SELECT id, nombre, usuario, contrasena, rol, activo
     FROM usuarios
     WHERE usuario = ?
     LIMIT 1'
);

if ($statement === false) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'exists' => false,
        'message' => 'No se pudo preparar la consulta.'
    ]);
    exit;
}

$statement->bind_param('s', $username);
$statement->execute();
$result = $statement->get_result();
$user = $result ? $result->fetch_assoc() : null;

if (!$user) {
    http_response_code(404);
    echo json_encode([
        'ok' => false,
        'exists' => false,
        'message' => 'El usuario no existe en la BD.'
    ]);
    $statement->close();
    $connection->close();
    exit;
}

if ((int)($user['activo'] ?? 0) !== 1) {
    http_response_code(403);
    echo json_encode([
        'ok' => false,
        'exists' => true,
        'message' => 'El usuario existe en la BD, pero esta inactivo.'
    ]);
    $statement->close();
    $connection->close();
    exit;
}

if (!isValidPassword($password, (string)$user['contrasena'])) {
    http_response_code(401);
    echo json_encode([
        'ok' => false,
        'exists' => true,
        'message' => 'El usuario existe en la BD, pero la contrasena es incorrecta.'
    ]);
    $statement->close();
    $connection->close();
    exit;
}

echo json_encode([
    'ok' => true,
    'exists' => true,
    'message' => 'Usuario encontrado en la BD.',
    'user' => [
        'id' => (int)$user['id'],
        'nombre' => $user['nombre'],
        'usuario' => $user['usuario'],
        'rol' => $user['rol']
    ]
]);

$statement->close();
$connection->close();
