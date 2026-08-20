<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';
require_once PROJECT_ROOT . '/private/auth.php';
require_once PROJECT_ROOT . '/private/db.php';


header('Content-Type: application/json; charset=utf-8');

function json_response(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

try {
    $sessionId = isset($_GET['id']) ? trim((string) $_GET['id']) : null;
    if ($sessionId === null || $sessionId === '') {
        json_response(400, ['ok' => false, 'error' => 'ID de sesión es requerido']);
    }

    $pdo = db();
    
    $deletedFilter = strtoupper(trim($_GET['deleted'] ?? 'N'));

    $stmt = $pdo->prepare('
        SELECT s.id, s.external_id, s.tested_at, s.participant_age, s.participant_weight_kg, s.participant_comment, s.instructor_score,
               p.name AS participant_name, p.dni AS participant_dni,
               c.count AS clutch_count, c.total_time_s AS clutch_total_time_s
        FROM sessions s
        JOIN participants p ON s.participant_id = p.id
        LEFT JOIN clutch_metrics c ON s.id = c.session_id
        WHERE s.id = :id OR s.external_id = :external_id
    ');
    
    // Si $sessionId es numerico lo usamos para :id, de lo contrario :id = 0
    $idInt = ctype_digit($sessionId) ? (int)$sessionId : 0;
    
    $stmt->execute([':id' => $idInt, ':external_id' => $sessionId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        json_response(404, ['ok' => false, 'error' => 'Sesion no encontrada']);
    }

    $eventsQuery = 'SELECT id, event_number, stimulus, result, time_ms, is_deleted, traction_mode FROM session_events WHERE session_id = :session_id';
    if ($deletedFilter === 'N') {
        $eventsQuery .= ' AND is_deleted = 0';
    } elseif ($deletedFilter === 'Y') {
        $eventsQuery .= ' AND is_deleted = 1';
    }
    $eventsQuery .= ' ORDER BY event_number ASC';

    $stmtEvents = $pdo->prepare($eventsQuery);
    $stmtEvents->execute([':session_id' => $session['id']]);
    
    $events = [];
    while ($eventRow = $stmtEvents->fetch(PDO::FETCH_ASSOC)) {
        $events[] = [
            'id' => (int) $eventRow['id'],
            'event_number' => (int) $eventRow['event_number'],
            'stimulus' => $eventRow['stimulus'],
            'result' => $eventRow['result'],
            'time_ms' => (int) $eventRow['time_ms'],
            'is_deleted' => !empty($eventRow['is_deleted']),
            'traction_mode' => $eventRow['traction_mode'],
        ];
    }

    $sessionResponse = [
        'id' => (int) $session['id'],
        'external_id' => $session['external_id'],
        'tested_at' => $session['tested_at'],
        'participant_name' => $session['participant_name'],
        'participant_dni' => $session['participant_dni'],
        'participant_age' => $session['participant_age'] !== null ? (int) $session['participant_age'] : null,
        'participant_weight_kg' => $session['participant_weight_kg'] !== null ? (float) $session['participant_weight_kg'] : null,
        'participant_comment' => $session['participant_comment'],
        'instructor_score' => $session['instructor_score'] !== null ? (int) $session['instructor_score'] : null,
        'clutch_count' => $session['clutch_count'] !== null ? (int) $session['clutch_count'] : null,
        'clutch_total_time_s' => $session['clutch_total_time_s'] !== null ? (float) $session['clutch_total_time_s'] : null,
        'events' => $events,
    ];

    echo json_encode(['ok' => true, 'session' => $sessionResponse], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Internal Server Error'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
