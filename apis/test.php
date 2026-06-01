<?php
// Simple test endpoint - no strict types
header("Content-Type: application/json; charset=utf-8");

$response = [
    'ok' => true,
    'message' => 'Connection OK',
    'php_version' => phpversion(),
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
    'timestamp' => date('Y-m-d H:i:s')
];

echo json_encode($response, JSON_PRETTY_PRINT);
