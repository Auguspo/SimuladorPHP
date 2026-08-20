<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';
require_once PROJECT_ROOT . '/private/auth.php';
require_once PROJECT_ROOT . '/private/db.php';

header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$userRole = $_SESSION['role'] ?? 'visualizador';
if ($userRole === 'visualizador') {
    echo json_encode(['ok' => false, 'error' => 'No tienes permisos para editar eventos']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'error' => 'Metodo no permitido']);
    exit;
}

$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody, true);
if (!is_array($data)) {
    echo json_encode(['ok' => false, 'error' => 'JSON invalido']);
    exit;
}

$eventId = $data['event_id'] ?? null;
$tractionMode = $data['traction_mode'] ?? null;

if (!$eventId || !$tractionMode) {
    echo json_encode(['ok' => false, 'error' => 'Faltan parametros']);
    exit;
}

if (!in_array($tractionMode, ['2H', '4H', '4L', 'Indefinido'], true)) {
    echo json_encode(['ok' => false, 'error' => 'Modo de traccion invalido']);
    exit;
}

try {
    $pdo = db();
    $stmt = $pdo->prepare('UPDATE session_events SET traction_mode = :mode WHERE id = :id');
    $stmt->execute([':mode' => $tractionMode, ':id' => (int)$eventId]);
    
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => 'Error de base de datos']);
}
