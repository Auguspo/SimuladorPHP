<?php

namespace App\Controllers;

use App\Models\SessionModel;
use App\Models\EventModel;
use Throwable;

class SessionController extends ApiController
{
    private SessionModel $sessionModel;
    private EventModel $eventModel;

    public function __construct()
    {
        $this->sessionModel = new SessionModel();
        $this->eventModel = new EventModel();
    }

    public function getLatest(): void
    {
        try {
            $sessions = $this->sessionModel->getLatest();
            $this->jsonResponse(200, ['ok' => true, 'sessions' => $sessions]);
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $this->jsonResponse(500, ['ok' => false, 'error' => $exception->getMessage()]);
        }
    }

    public function getDetail(?string $sessionId, string $deletedFilter = 'N'): void
    {
        if ($sessionId === null || $sessionId === '') {
            $this->jsonResponse(400, ['ok' => false, 'error' => 'ID de sesión es requerido']);
        }

        try {
            $session = $this->sessionModel->getByIdOrExternal($sessionId);
            if (!$session) {
                $this->jsonResponse(404, ['ok' => false, 'error' => 'Sesion no encontrada']);
            }

            $events = $this->eventModel->getBySession($session['id'], $deletedFilter);
            $session['events'] = $events;

            $this->jsonResponse(200, ['ok' => true, 'session' => $session]);
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $this->jsonResponse(500, ['ok' => false, 'error' => $exception->getMessage()]);
        }
    }

    public function getByParticipant(?string $participantId): void
    {
        if ($participantId === null || !ctype_digit($participantId)) {
            $this->jsonResponse(400, ['ok' => false, 'error' => 'ID invalido']);
        }

        try {
            $sessions = $this->sessionModel->getByParticipant((int) $participantId);
            
            // Extract participant name from first session if available (or empty)
            $participantName = 'Participante';
            if (count($sessions) > 0 && isset($sessions[0]['participant_name'])) {
                $participantName = $sessions[0]['participant_name'];
            }
            
            $this->jsonResponse(200, ['ok' => true, 'participant_name' => $participantName, 'sessions' => $sessions]);
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $this->jsonResponse(500, ['ok' => false, 'error' => $exception->getMessage()]);
        }
    }

    public function updateSession(): void
    {
        $this->requireMethod('POST');
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            $id = $data['id'] ?? null;
            if (!$id) {
                $this->jsonResponse(400, ['ok' => false, 'error' => 'Missing ID']);
            }

            $age = $data['participant_age'] ?? null;
            if ($age === '') $age = null;
            $weight = $data['participant_weight_kg'] ?? null;
            if ($weight === '') $weight = null;
            $comment = $data['participant_comment'] ?? null;
            $score = $data['instructor_score'] ?? null;
            if ($score === '') $score = null;

            $this->sessionModel->update((int)$id, $age !== null ? (int)$age : null, $weight !== null ? (float)$weight : null, $comment, $score !== null ? (int)$score : null);

            $this->jsonResponse(200, ['ok' => true]);
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $this->jsonResponse(500, ['ok' => false, 'error' => 'Internal error']);
        }
    }

    public function updateEventTraction(): void
    {
        $this->requireMethod('POST');
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['event_id']) || !isset($data['traction_mode'])) {
                $this->jsonResponse(400, ['ok' => false, 'error' => 'Missing data']);
            }

            $this->eventModel->updateTraction((int)$data['event_id'], $data['traction_mode']);
            $this->jsonResponse(200, ['ok' => true]);
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $this->jsonResponse(500, ['ok' => false, 'error' => 'Internal error']);
        }
    }

    public function toggleEventDeletion(): void
    {
        $this->requireMethod('POST');
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            if (!isset($data['event_id']) || !isset($data['is_deleted'])) {
                $this->jsonResponse(400, ['ok' => false, 'error' => 'Invalid data']);
            }

            $this->eventModel->toggleDeletion((int)$data['event_id'], (bool)$data['is_deleted']);
            $this->jsonResponse(200, ['ok' => true]);
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $this->jsonResponse(500, ['ok' => false, 'error' => 'Internal Server Error']);
        }
    }
}
