<?php
/**
 * Script de verificación de la instalación del panel admin
 * Ejecutar en: http://localhost/9amm/IA/apis/check.php
 */

declare(strict_types=1);

echo "<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Verificación - Panel Admin</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; margin-bottom: 30px; }
        .check { margin-bottom: 20px; padding: 15px; border-radius: 5px; border-left: 4px solid #ddd; }
        .check.ok { background: #d4edda; border-left-color: #28a745; }
        .check.error { background: #f8d7da; border-left-color: #dc3545; }
        .check.warning { background: #fff3cd; border-left-color: #ffc107; }
        .check h3 { margin-bottom: 5px; }
        .check p { color: #555; font-size: 14px; }
        .code { background: #f8f9fa; padding: 10px; border-radius: 3px; margin-top: 10px; font-family: monospace; overflow-x: auto; font-size: 12px; }
        .next-steps { background: #e7f3ff; padding: 20px; border-radius: 5px; margin-top: 30px; }
        .next-steps h2 { color: #004085; margin-bottom: 15px; }
        .next-steps ol { margin-left: 20px; }
        .next-steps li { margin-bottom: 10px; color: #004085; }
        a { color: #667eea; text-decoration: none; }
        a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>✅ Verificación - Panel de Administración</h1>";

$checks = [];

// 1. Verificar PHP version
$checks[] = [
    'titulo' => 'Versión de PHP',
    'estado' => version_compare(PHP_VERSION, '7.4.0', '>=') ? 'ok' : 'error',
    'mensaje' => 'PHP ' . PHP_VERSION,
    'requerido' => 'PHP 7.4 o superior'
];

// 2. Verificar extensión mysqli
$checks[] = [
    'titulo' => 'Extensión MySQLi',
    'estado' => extension_loaded('mysqli') ? 'ok' : 'error',
    'mensaje' => extension_loaded('mysqli') ? 'MySQLi está disponible' : 'MySQLi no está instalado',
    'requerido' => 'Requerido para conexión a MySQL'
];

// 3. Verificar config.php
$config_exists = file_exists(__DIR__ . '/config.php');
$checks[] = [
    'titulo' => 'Archivo config.php',
    'estado' => $config_exists ? 'ok' : 'error',
    'mensaje' => $config_exists ? 'config.php existe' : 'config.php no encontrado',
    'requerido' => 'Debe existir en /apis/config.php'
];

// 4. Verificar cors.php
$cors_exists = file_exists(__DIR__ . '/cors.php');
$checks[] = [
    'titulo' => 'Archivo cors.php',
    'estado' => $cors_exists ? 'ok' : 'error',
    'mensaje' => $cors_exists ? 'cors.php existe' : 'cors.php no encontrado',
    'requerido' => 'Debe existir en /apis/cors.php'
];

// 5. Intentar conexión a BD
$db_check = 'error';
$db_message = 'No se pudo conectar';
if ($config_exists) {
    try {
        require_once __DIR__ . '/config.php';
        $conn = @createDataBaseConnection();
        if ($conn) {
            $db_check = 'ok';
            $db_message = 'Conexión exitosa a ' . $conn->get_charset()->charset;
            $conn->close();
        }
    } catch (Exception $e) {
        $db_message = 'Error: ' . $e->getMessage();
    }
}
$checks[] = [
    'titulo' => 'Conexión a Base de Datos',
    'estado' => $db_check,
    'mensaje' => $db_message,
    'requerido' => 'Base de datos restaurante debe existir'
];

// 6. Verificar tabla usuarios
$usuarios_table = 'error';
$usuarios_message = 'No verificado';
if ($db_check === 'ok') {
    try {
        require_once __DIR__ . '/config.php';
        $conn = createDataBaseConnection();
        $result = $conn->query("SHOW TABLES LIKE 'usuarios'");
        if ($result && $result->num_rows > 0) {
            $usuarios_table = 'ok';
            $usuarios_message = 'Tabla usuarios existe';
            
            // Contar usuarios
            $count_result = $conn->query("SELECT COUNT(*) as count FROM usuarios");
            if ($count_result) {
                $row = $count_result->fetch_assoc();
                $usuarios_message .= ' (' . $row['count'] . ' registro' . ($row['count'] != 1 ? 's' : '') . ')';
            }
        } else {
            $usuarios_table = 'warning';
            $usuarios_message = 'Tabla usuarios no existe (se creará automáticamente)';
        }
        $conn->close();
    } catch (Exception $e) {
        $usuarios_message = 'Error: ' . $e->getMessage();
    }
}
$checks[] = [
    'titulo' => 'Tabla Usuarios',
    'estado' => $usuarios_table,
    'mensaje' => $usuarios_message,
    'requerido' => 'Se crea automáticamente en primer acceso'
];

// 7. Verificar archivos necesarios
$archivos = ['auth.php', 'usuarios.php', 'admin-panel.html'];
foreach ($archivos as $archivo) {
    $archivo_exists = file_exists(__DIR__ . '/' . $archivo);
    $checks[] = [
        'titulo' => "Archivo $archivo",
        'estado' => $archivo_exists ? 'ok' : 'error',
        'mensaje' => $archivo_exists ? "$archivo existe" : "$archivo no encontrado",
        'requerido' => "Debe existir en /apis/$archivo"
    ];
}

// Renderizar checks
foreach ($checks as $check) {
    echo "<div class='check {$check['estado']}'>
        <h3>" . ($check['estado'] === 'ok' ? '✅' : ($check['estado'] === 'error' ? '❌' : '⚠️')) . " {$check['titulo']}</h3>
        <p><strong>Estado:</strong> {$check['mensaje']}</p>
        <p><strong>Requerido:</strong> {$check['requerido']}</p>
    </div>";
}

// Contar estados
$errors = array_filter($checks, fn($c) => $c['estado'] === 'error');
$warnings = array_filter($checks, fn($c) => $c['estado'] === 'warning');

echo "<div class='next-steps'>";
if (empty($errors)) {
    echo "<h2>🎉 ¡Todo está configurado correctamente!</h2>
    <p style='margin-bottom: 15px;'>Tu panel de administración está listo para usar.</p>
    <ol>
        <li><strong>Accede al panel:</strong> <a href='admin-panel.html' target='_blank'>Abrir Panel Admin</a></li>
        <li><strong>Credenciales iniciales:</strong>
            <div class='code'>
Usuario: admin<br>
Contraseña: admin123
            </div>
        </li>
        <li><strong>Siguiente:</strong> Lee la <a href='GUIA-RAPIDA.md' target='_blank'>Guía Rápida</a> para aprender a usar el panel</li>
    </ol>";
} elseif (count($errors) > 0) {
    echo "<h2>❌ Se encontraron " . count($errors) . " error(es)</h2>
    <p>Por favor soluciona los problemas marcados arriba antes de usar el panel.</p>
    <p style='margin-top: 15px;'><strong>Acciones recomendadas:</strong></p>
    <ol>";
    
    if (array_filter($errors, fn($c) => strpos($c['titulo'], 'MySQLi') !== false)) {
        echo "<li>Instala la extensión MySQLi de PHP</li>";
    }
    if (array_filter($errors, fn($c) => strpos($c['titulo'], 'Base de Datos') !== false)) {
        echo "<li>Verifica que MySQL esté corriendo y la BD 'restaurante' exista</li>";
        echo "<li>Verifica credenciales en <code>apis/config.php</code></li>";
    }
    if (array_filter($errors, fn($c) => strpos($c['titulo'], 'archivo') !== false)) {
        echo "<li>Verifica que los archivos estén en la carpeta <code>apis/</code></li>";
    }
    
    echo "</ol>";
} else {
    echo "<h2>⚠️ Se encontraron " . count($warnings) . " advertencia(s)</h2>
    <p>Tu panel está funcionando pero se recomienda revisar las advertencias.</p>";
}

echo "</div>
    </div>
</body>
</html>";
?>
