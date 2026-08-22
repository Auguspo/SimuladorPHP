<?php

namespace App\Models;

use PDO;

class SessionModel extends BaseModel
{
    public function getLatest(int $limit = 500): array
    {
        $statement = $this->pdo->prepare(
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
                 s.instructor_score,
                 (SELECT COUNT(*) FROM session_events se WHERE se.session_id = s.id) AS events_count,
                 (SELECT SUM(time_ms) FROM session_events se WHERE se.session_id = s.id) AS total_reaction_ms
             FROM sessions s
             JOIN participants p ON p.id = s.participant_id
             LEFT JOIN clutch_metrics cm ON cm.session_id = s.id
             ORDER BY s.tested_at DESC, s.id DESC
             LIMIT :limit'
        );

        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
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
                'instructor_score' => $row['instructor_score'] !== null ? (int) $row['instructor_score'] : null,
                'clutch_count' => $row['clutch_count'] !== null ? (int) $row['clutch_count'] : null,
                'clutch_total_time_s' => $row['clutch_total_time_s'] !== null ? (float) $row['clutch_total_time_s'] : null,
                'events_count' => (int) $row['events_count'],
                'total_reaction_ms' => $row['total_reaction_ms'] !== null ? (int) $row['total_reaction_ms'] : 0,
            ];
        }

        return $sessions;
    }

    public function getByIdOrExternal(string $sessionId): ?array
    {
        $stmt = $this->pdo->prepare('
            SELECT s.id, s.external_id, s.tested_at, s.participant_age, s.participant_weight_kg, s.participant_comment, s.instructor_score,
                   p.name AS participant_name, p.dni AS participant_dni,
                   c.count AS clutch_count, c.total_time_s AS clutch_total_time_s
            FROM sessions s
            JOIN participants p ON s.participant_id = p.id
            LEFT JOIN clutch_metrics c ON s.id = c.session_id
            WHERE s.id = :id OR s.external_id = :external_id
        ');
        
        $idInt = ctype_digit($sessionId) ? (int)$sessionId : 0;
        
        $stmt->execute([':id' => $idInt, ':external_id' => $sessionId]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$session) {
            return null;
        }

        return [
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
        ];
    }

    public function create(string $externalId, int $participantId, string $testedAt, ?int $age, ?float $weight, ?string $comment): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO sessions (
                 external_id, participant_id, tested_at, participant_age, participant_weight_kg, participant_comment
             ) VALUES (
                 :external_id, :participant_id, :tested_at, :participant_age, :participant_weight_kg, :participant_comment
             )'
        );
        $statement->execute([
            ':external_id' => $externalId,
            ':participant_id' => $participantId,
            ':tested_at' => $testedAt,
            ':participant_age' => $age,
            ':participant_weight_kg' => $weight,
            ':participant_comment' => $comment
        ]);
        return (int) $this->pdo->lastInsertId();
    }

    public function update(int $id, ?int $age, ?float $weight, ?string $comment, ?int $instructorScore): void
    {
        $stmt = $this->pdo->prepare('
            UPDATE sessions 
            SET participant_age = :age,
                participant_weight_kg = :weight,
                participant_comment = :comment,
                instructor_score = :score
            WHERE id = :id
        ');
        $stmt->execute([
            ':id' => $id,
            ':age' => $age,
            ':weight' => $weight,
            ':comment' => $comment,
            ':score' => $instructorScore
        ]);
    }

    public function getByParticipant(int $participantId): array
    {
        $stmt = $this->pdo->prepare('
            SELECT s.id, s.external_id, s.tested_at, p.name AS participant_name,
                   (SELECT COUNT(*) FROM session_events se WHERE se.session_id = s.id) AS events_count,
                   (SELECT COUNT(*) FROM session_events se WHERE se.session_id = s.id AND se.result = "ACIERTO") AS aciertos_count
            FROM sessions s
            JOIN participants p ON p.id = s.participant_id
            WHERE s.participant_id = :pid
            ORDER BY s.tested_at DESC
        ');
        $stmt->execute([':pid' => $participantId]);
        
        $sessions = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $sessions[] = [
                'id' => (int) $row['id'],
                'external_id' => $row['external_id'],
                'tested_at' => $row['tested_at'],
                'participant_name' => $row['participant_name'],
                'events_count' => (int) $row['events_count'],
                'aciertos_count' => (int) $row['aciertos_count']
            ];
        }
        return $sessions;
    }
}
