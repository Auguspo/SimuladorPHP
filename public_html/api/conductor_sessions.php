<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';
require_once PROJECT_ROOT . '/private/auth.php';
require_once PROJECT_ROOT . '/private/db.php';


header('Content-Type: application/json; charset=utf-8');

try {
    $participantId = isset($_GET['id']) ? trim((string) $_GET['id']) : null;
    if ($participantId === null || $participantId === '') {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'ID de conductor requerido'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }

    $pdo = db();
    $statement = $pdo->prepare(
        'SELECT p.id, p.name, s.id AS session_id, s.external_id, s.tested_at,
                (SELECT COUNT(*) FROM session_events se WHERE se.session_id = s.id) AS events_count
         FROM participants p
         LEFT JOIN sessions s ON s.participant_id = p.id
         WHERE p.id = :participant_id
         ORDER BY s.tested_at DESC, s.id DESC'
    );
    $statement->execute([':participant_id' => (int) $participantId]);

    $sessions = [];
    $participantName = null;
    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        if ($participantName === null) {
            $participantName = $row['name'];
        }
        if ($row['session_id'] === null) {
            continue;
        }
        $sessions[] = [
            'id' => (int) $row['session_id'],
            'external_id' => $row['external_id'],
            'tested_at' => $row['tested_at'],
            'events_count' => (int) $row['events_count'],
        ];
    }

    echo json_encode(['ok' => true, 'participant_name' => $participantName, 'sessions' => $sessions], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Internal Server Error'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
