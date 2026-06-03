<?php
declare(strict_types=1);

require_once __DIR__ . '/config.php';

session_start();

$conn = createDataBaseConnection();

// Crear tabla de usuarios si no existe
$createSql = "CREATE TABLE IF NOT EXISTS usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(255) NOT NULL,
  usuario VARCHAR(255) UNIQUE NOT NULL,
  contrasena VARCHAR(255) NOT NULL,
  rol VARCHAR(50) DEFAULT 'user',
  activo TINYINT(1) DEFAULT 1
)
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
$conn->query($createSql);

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) $input = [];

// Verificar si es login
if ($method === 'POST' && isset($input['login'])) {
    $usuario = $conn->real_escape_string($input['usuario'] ?? '');
    $contrasena = $input['contrasena'] ?? '';

    if (empty($usuario) || empty($contrasena)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Usuario y contraseña requeridos']);
        $conn->close();
        exit;
    }

    $stmt = $conn->prepare('SELECT id, nombre, usuario, rol, contrasena FROM usuarios WHERE usuario = ? AND activo = 1');
    $stmt->bind_param('s', $usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($contrasena, $user['contrasena']) && $user['rol'] === 'admin') {
        $_SESSION['admin_user'] = [
            'id' => $user['id'],
            'nombre' => $user['nombre'],
            'usuario' => $user['usuario'],
            'rol' => $user['rol']
        ];
        echo json_encode(['ok' => true, 'message' => 'Login exitoso']);
    } else {
        http_response_code(401);
        echo json_encode(['ok' => false, 'message' => 'Credenciales inválidas o permisos insuficientes']);
    }
    $stmt->close();
    $conn->close();
    exit;
}

// Verificar si es logout
if ($method === 'POST' && isset($input['logout'])) {
    session_destroy();
    echo json_encode(['ok' => true, 'message' => 'Logout exitoso']);
    $conn->close();
    exit;
}

// Verificar autenticación para otras operaciones
if (!isset($_SESSION['admin_user']) || $_SESSION['admin_user']['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['ok' => false, 'message' => 'No autenticado o sin permisos de admin']);
    $conn->close();
    exit;
}

// GET - Listar usuarios
if ($method === 'GET') {
    $res = $conn->query('SELECT id, nombre, usuario, rol, activo FROM usuarios ORDER BY id DESC');
    $list = [];
    while ($row = $res->fetch_assoc()) {
        $row['id'] = (int)$row['id'];
        $row['activo'] = (int)$row['activo'];
        $list[] = $row;
    }
    echo json_encode(['ok' => true, 'usuarios' => $list]);
    $conn->close();
    exit;
}

// POST - Crear usuario
if ($method === 'POST') {
    $nombre = $conn->real_escape_string($input['nombre'] ?? '');
    $usuario = $conn->real_escape_string($input['usuario'] ?? '');
    $contrasena = $conn->real_escape_string($input['contrasena'] ?? '');
    $rol = $conn->real_escape_string($input['rol'] ?? 'user');
    $activo = isset($input['activo']) ? (int)$input['activo'] : 1;

    if (empty($nombre) || empty($usuario) || empty($contrasena)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Los campos nombre, usuario y contrasena son requeridos.']);
        $conn->close();
        exit;
    }

    $hashedPassword = password_hash($contrasena, PASSWORD_BCRYPT);

    $stmt = $conn->prepare('INSERT INTO usuarios (nombre, usuario, contrasena, rol, activo) VALUES (?, ?, ?, ?, ?)');
    $stmt->bind_param('ssssi', $nombre, $usuario, $hashedPassword, $rol, $activo);
    if ($stmt->execute()) {
        echo json_encode(['ok' => true, 'id' => $stmt->insert_id]);
    } else {
        if ($conn->errno === 1062) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'El usuario ya existe.']);
        } else {
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'No se pudo insertar.']);
        }
    }
    $stmt->close();
    $conn->close();
    exit;
}

// PUT - Actualizar usuario
if ($method === 'PUT') {
    $id = (int)($input['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'ID invalido.']);
        $conn->close();
        exit;
    }

    $nombre = $conn->real_escape_string($input['nombre'] ?? '');
    $usuario = $conn->real_escape_string($input['usuario'] ?? '');
    $contrasena = $input['contrasena'] ?? '';
    $rol = $conn->real_escape_string($input['rol'] ?? 'user');
    $activo = isset($input['activo']) ? (int)$input['activo'] : 1;

    if (empty($nombre) || empty($usuario)) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'Los campos nombre y usuario son requeridos.']);
        $conn->close();
        exit;
    }

    if (!empty($contrasena)) {
        $hashedPassword = password_hash($contrasena, PASSWORD_BCRYPT);
        $stmt = $conn->prepare('UPDATE usuarios SET nombre = ?, usuario = ?, contrasena = ?, rol = ?, activo = ? WHERE id = ?');
        $stmt->bind_param('ssssii', $nombre, $usuario, $hashedPassword, $rol, $activo, $id);
    } else {
        $stmt = $conn->prepare('UPDATE usuarios SET nombre = ?, usuario = ?, rol = ?, activo = ? WHERE id = ?');
        $stmt->bind_param('sssii', $nombre, $usuario, $rol, $activo, $id);
    }

    if ($stmt->execute()) {
        if ($stmt->affected_rows === 0) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'message' => 'Usuario no encontrado.']);
        } else {
            echo json_encode(['ok' => true]);
        }
    } else {
        if ($conn->errno === 1062) {
            http_response_code(400);
            echo json_encode(['ok' => false, 'message' => 'El usuario ya existe.']);
        } else {
            http_response_code(500);
            echo json_encode(['ok' => false, 'message' => 'No se pudo actualizar.']);
        }
    }
    $stmt->close();
    $conn->close();
    exit;
}

// DELETE - Eliminar usuario
if ($method === 'DELETE') {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($input['id'] ?? 0);
    if ($id <= 0) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'message' => 'ID invalido.']);
        $conn->close();
        exit;
    }

    $stmt = $conn->prepare('DELETE FROM usuarios WHERE id = ?');
    $stmt->bind_param('i', $id);
    if ($stmt->execute()) {
        if ($stmt->affected_rows === 0) {
            http_response_code(404);
            echo json_encode(['ok' => false, 'message' => 'Usuario no encontrado.']);
        } else {
            echo json_encode(['ok' => true]);
        }
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
