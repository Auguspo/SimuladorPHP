<?php

namespace App\Models;

use PDO;

class ClutchModel extends BaseModel
{
    public function create(int $sessionId, int $count, float $totalTimeS): void
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO clutch_metrics (session_id, count, total_time_s)
             VALUES (:session_id, :count, :total_time_s)'
        );
        $statement->execute([
            ':session_id' => $sessionId,
            ':count' => $count,
            ':total_time_s' => $totalTimeS,
        ]);
    }

    public function getBySession(int $sessionId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT count, total_time_s FROM clutch_metrics WHERE session_id = :id');
        $stmt->execute([':id' => $sessionId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) return null;

        return [
            'count' => (int) $row['count'],
            'total_time_s' => (float) $row['total_time_s']
        ];
    }
}
