<?php

namespace App\Models;

use PDO;

class ParticipantModel extends BaseModel
{
    public function getAll(): array
    {
        $statement = $this->pdo->query(
            'SELECT p.id, p.name, p.dni, p.deleted, COUNT(s.id) AS sessions_count
             FROM participants p
             LEFT JOIN sessions s ON s.participant_id = p.id
             GROUP BY p.id, p.name, p.dni, p.deleted
             ORDER BY p.name ASC'
        );

        $participants = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $participants[] = [
                'id' => (int) $row['id'],
                'name' => $row['name'],
                'dni' => $row['dni'],
                'deleted' => $row['deleted'],
                'sessions_count' => (int) $row['sessions_count'],
            ];
        }

        return $participants;
    }

    public function getById(int $id): ?array
    {
        $statement = $this->pdo->prepare('SELECT id, name, dni FROM participants WHERE id = :id');
        $statement->execute([':id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if ($row === false) {
            return null;
        }

        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'dni' => $row['dni']
        ];
    }

    public function getOrCreate(string $name, string $dni): int
    {
        $statement = $this->pdo->prepare(
            'INSERT INTO participants (name, dni)
             VALUES (:name, :dni)
             ON DUPLICATE KEY UPDATE name = VALUES(name), id = LAST_INSERT_ID(id)'
        );
        $statement->execute([
            ':name' => $name,
            ':dni' => $dni,
        ]);
        return (int) $this->pdo->lastInsertId();
    }
    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE participants SET deleted = 'Y' WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
