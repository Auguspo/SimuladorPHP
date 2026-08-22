<?php

namespace App\Models;

use PDO;

class EventModel extends BaseModel
{
    public function getBySession(int $sessionId, string $deletedFilter = 'N'): array
    {
        $eventsQuery = 'SELECT id, event_number, stimulus, result, time_ms, is_deleted, traction_mode FROM session_events WHERE session_id = :session_id';
        if ($deletedFilter === 'N') {
            $eventsQuery .= ' AND is_deleted = 0';
        } elseif ($deletedFilter === 'Y') {
            $eventsQuery .= ' AND is_deleted = 1';
        }
        $eventsQuery .= ' ORDER BY event_number ASC';

        $stmtEvents = $this->pdo->prepare($eventsQuery);
        $stmtEvents->execute([':session_id' => $sessionId]);
        
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

        return $events;
    }

    public function create(int $sessionId, int $eventNumber, string $stimulus, string $result, int $timeMs, string $tractionMode): void
    {
        $eventStatement = $this->pdo->prepare(
            'INSERT INTO session_events (session_id, event_number, stimulus, result, time_ms, traction_mode)
             VALUES (:session_id, :event_number, :stimulus, :result, :time_ms, :traction_mode)'
        );
        $eventStatement->execute([
            ':session_id' => $sessionId,
            ':event_number' => $eventNumber,
            ':stimulus' => $stimulus,
            ':result' => $result,
            ':time_ms' => $timeMs,
            ':traction_mode' => $tractionMode
        ]);
    }

    public function updateTraction(int $eventId, string $tractionMode): void
    {
        $stmt = $this->pdo->prepare('UPDATE session_events SET traction_mode = :mode WHERE id = :id');
        $stmt->execute([':mode' => $tractionMode, ':id' => $eventId]);
    }

    public function toggleDeletion(int $eventId, bool $isDeleted): void
    {
        $stmt = $this->pdo->prepare('UPDATE session_events SET is_deleted = :val WHERE id = :id');
        $stmt->execute([
            ':val' => $isDeleted ? 1 : 0,
            ':id' => $eventId
        ]);
    }
}
