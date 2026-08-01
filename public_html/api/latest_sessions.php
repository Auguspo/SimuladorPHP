<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';
require_once PROJECT_ROOT . '/private/auth.php';
require_once PROJECT_ROOT . '/private/db.php';


header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = db();
    $statement = $pdo->prepare(
        'SELECT
             s.id,
             s.external_id,
             s.tested_at,
             s.participant_age,
             s.participant_weight_kg,
             s.participant_comment,
             p.name AS participant_name,
             p.dni AS participant_dni,
             cm.count AS clutch_count,
             cm.total_time_s AS clutch_total_time_s,
             (SELECT COUNT(*) FROM session_events se WHERE se.session_id = s.id) AS events_count,
             (SELECT SUM(time_ms) FROM session_events se WHERE se.session_id = s.id) AS total_reaction_ms
         FROM sessions s
         JOIN participants p ON p.id = s.participant_id
         LEFT JOIN clutch_metrics cm ON cm.session_id = s.id
         ORDER BY s.tested_at DESC, s.id DESC
         LIMIT 500'
    );

    $statement->execute();
    $sessions = [];

    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $sessions[] = [
            'id' => (int) $row['id'],
            'external_id' => $row['external_id'],
            'tested_at' => $row['tested_at'],
            'participant_name' => $row['participant_name'],
            'participant_dni' => $row['participant_dni'],
            'participant_age' => $row['participant_age'] !== null ? (int) $row['participant_age'] : null,
            'participant_weight_kg' => $row['participant_weight_kg'] !== null ? (float) $row['participant_weight_kg'] : null,
            'participant_comment' => $row['participant_comment'],
            'clutch_count' => $row['clutch_count'] !== null ? (int) $row['clutch_count'] : null,
            'clutch_total_time_s' => $row['clutch_total_time_s'] !== null ? (float) $row['clutch_total_time_s'] : null,
            'events_count' => (int) $row['events_count'],
            'total_reaction_ms' => $row['total_reaction_ms'] !== null ? (int) $row['total_reaction_ms'] : 0,
        ];
    }

    echo json_encode(['ok' => true, 'sessions' => $sessions], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Internal Server Error'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
