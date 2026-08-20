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
    echo json_encode(['ok' => false, 'error' => 'No tienes permisos para editar sesiones']);
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

$sessionId = $data['session_id'] ?? null;
if (!$sessionId) {
    echo json_encode(['ok' => false, 'error' => 'session_id es requerido']);
    exit;
}

$age = isset($data['participant_age']) && $data['participant_age'] !== '' ? (int)$data['participant_age'] : null;
$weight = isset($data['participant_weight_kg']) && $data['participant_weight_kg'] !== '' ? (float)$data['participant_weight_kg'] : null;
$comment = isset($data['participant_comment']) ? trim($data['participant_comment']) : null;
$score = isset($data['instructor_score']) && $data['instructor_score'] !== '' ? (int)$data['instructor_score'] : null;

try {
    $pdo = db();
    $stmt = $pdo->prepare('
        UPDATE sessions 
        SET participant_age = :age, 
            participant_weight_kg = :weight, 
            participant_comment = :comment,
            instructor_score = :score
        WHERE id = :id
    ');
    
    $stmt->execute([
        ':age' => $age,
        ':weight' => $weight,
        ':comment' => $comment,
        ':score' => $score,
        ':id' => $sessionId
    ]);
    
    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'error' => 'Error de base de datos']);
}
