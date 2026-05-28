<?php
declare(strict_types=1);

require_once __DIR__ . '/cors.php';
require __DIR__ . '/config.php';

$conn = createDataBaseConnection();

// Ensure table exists
$createSql = "CREATE TABLE IF NOT EXISTS rutas (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  origin VARCHAR(255) NOT NULL,
  destination VARCHAR(255) NOT NULL,
  schedule VARCHAR(255) DEFAULT NULL
)
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
$conn->query($createSql);

$method = $_SERVER['REQUEST_METHOD'];

function requireAdmin($data)
{
    if (is_array($data) && isset($data['rol']) && $data['rol'] === 'admin') return true;
    if (isset($_GET['rol']) && $_GET['rol'] === 'admin') return true;
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'Se requiere rol admin.']);
    return false;
}

if ($method === 'GET') {
    $res = $conn->query('SELECT id, name, origin, destination, schedule FROM rutas ORDER BY id DESC');
    $list = [];
    while ($row = $res->fetch_assoc()) {
        $row['id'] = (int)$row['id'];
        $list[] = $row;
    }
    echo json_encode(['ok' => true, 'routes' => $list]);
    $conn->close();
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) $input = [];

if ($method === 'POST') {
    if (!requireAdmin($input)) { $conn->close(); exit; }
    $name = $conn->real_escape_string($input['name'] ?? '');
    $origin = $conn->real_escape_string($input['origin'] ?? '');
    $destination = $conn->real_escape_string($input['destination'] ?? '');
    $schedule = $conn->real_escape_string($input['schedule'] ?? null);

    $stmt = $conn->prepare('INSERT INTO rutas (name, origin, destination, schedule) VALUES (?, ?, ?, ?)');
    $stmt->bind_param('ssss', $name, $origin, $destination, $schedule);
    if ($stmt->execute()) {
        echo json_encode(['ok' => true, 'id' => $stmt->insert_id]);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'No se pudo insertar.']);
    }
    $stmt->close();
    $conn->close();
    exit;
}

if ($method === 'PUT') {
    if (!requireAdmin($input)) { $conn->close(); exit; }
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'ID invalido.']);
        $conn->close();
        exit;
    }
    $name = $conn->real_escape_string($input['name'] ?? '');
    $origin = $conn->real_escape_string($input['origin'] ?? '');
    $destination = $conn->real_escape_string($input['destination'] ?? '');
    $schedule = $conn->real_escape_string($input['schedule'] ?? null);

    $stmt = $conn->prepare('UPDATE rutas SET name = ?, origin = ?, destination = ?, schedule = ? WHERE id = ?');
    $stmt->bind_param('ssssi', $name, $origin, $destination, $schedule, $id);
    if ($stmt->execute()) {
        echo json_encode(['ok' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'No se pudo actualizar.']);
    }
    $stmt->close();
    $conn->close();
    exit;
}

if ($method === 'DELETE') {
    // allow rol via query param or body
    if (!requireAdmin($input)) { $conn->close(); exit; }
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($input['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'ID invalido.']);
        $conn->close();
        exit;
    }
    $stmt = $conn->prepare('DELETE FROM rutas WHERE id = ?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        echo json_encode(['ok' => true]);
    } else {
        http_response_code(500);
        echo json_encode(['ok' => false, 'message' => 'No se pudo eliminar.']);
    }
    $stmt->close();
    $conn->close();
    exit;
}

http_response_code(405);
echo json_encode(['ok' => false, 'message' => 'Metodo no permitido.']);
$conn->close();
