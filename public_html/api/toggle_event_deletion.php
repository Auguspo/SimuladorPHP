<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';
require_once PROJECT_ROOT . '/private/auth.php';
require_once PROJECT_ROOT . '/private/db.php';

header('Content-Type: application/json; charset=utf-8');

function json_resp(int $code, array $payload): void {
    http_response_code($code);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        json_resp(405, ['ok' => false, 'error' => 'Método no permitido']);
    }

    $userRole = $_SESSION['role'] ?? 'visualizador';
    if ($userRole === 'visualizador') {
        json_resp(403, ['ok' => false, 'error' => 'Los usuarios con rol visualizador no tienen permiso para eliminar o restaurar eventos']);
    }

    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        $data = $_POST;
    }

    $eventId = (int)($data['event_id'] ?? $data['id'] ?? 0);
    if ($eventId <= 0) {
        json_resp(400, ['ok' => false, 'error' => 'ID de evento no válido']);
    }

    $isDeleted = isset($data['is_deleted']) ? (bool)$data['is_deleted'] : true;

    $pdo = db();
    $stmt = $pdo->prepare('UPDATE session_events SET is_deleted = :is_deleted WHERE id = :id');
    $stmt->execute([
        ':is_deleted' => $isDeleted ? 1 : 0,
        ':id' => $eventId
    ]);

    json_resp(200, [
        'ok' => true,
        'event_id' => $eventId,
        'is_deleted' => $isDeleted,
        'message' => $isDeleted ? 'Evento descartado de la sesión' : 'Evento restaurado'
    ]);
} catch (Throwable $e) {
    error_log($e->getMessage());
    json_resp(500, ['ok' => false, 'error' => 'Error al procesar solicitud']);
}
