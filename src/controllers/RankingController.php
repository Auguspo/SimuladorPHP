<?php

namespace App\Controllers;

use PDO;
use Throwable;

class RankingController extends ApiController
{
    public function getRanking(): void
    {
        try {
            $pdo = \db();
            $sql = "
                SELECT s.id as session_id, p.id as participant_id, p.name as participant_name, 
                       p.age as participant_age, s.tested_at, sc.totalScore
                FROM session_scorings sc
                JOIN sessions s ON s.id = sc.session_id
                JOIN participants p ON p.id = s.participant_id
                ORDER BY sc.totalScore DESC, s.tested_at DESC
                LIMIT 100
            ";
            
            $stmt = $pdo->query($sql);
            $ranking = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            $this->jsonResponse(200, ['ok' => true, 'ranking' => $ranking]);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $this->jsonResponse(500, ['ok' => false, 'error' => 'Internal Server Error']);
        }
    }
}
