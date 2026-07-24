<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/private/auth.php';
require_once dirname(__DIR__) . '/private/db.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $pdo = db();
    $statement = $pdo->query(
        'SELECT p.id, p.name, p.dni, COUNT(s.id) AS sessions_count
         FROM participants p
         LEFT JOIN sessions s ON s.participant_id = p.id
         GROUP BY p.id, p.name, p.dni
         ORDER BY p.name ASC'
    );

    $participants = [];
    while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
        $participants[] = [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'dni' => $row['dni'],
            'sessions_count' => (int) $row['sessions_count'],
        ];
    }

    echo json_encode(['ok' => true, 'participants' => $participants], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Internal Server Error'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
}
