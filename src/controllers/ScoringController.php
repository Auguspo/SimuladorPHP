<?php

namespace App\Controllers;

use App\Models\ScoringModel;
use App\Models\SessionModel;
use Throwable;

class ScoringController extends ApiController
{
    private ScoringModel $scoringModel;
    private SessionModel $sessionModel;

    public function __construct()
    {
        $this->scoringModel = new ScoringModel();
        $this->sessionModel = new SessionModel();
    }

    public function handleRequest(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->getScoring();
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->saveScoring();
        } else {
            $this->jsonResponse(405, ['ok' => false, 'error' => 'Method not allowed']);
        }
    }

    private function getScoring(): void
    {
        $sessionId = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;
        if ($sessionId <= 0) {
            $this->jsonResponse(400, ['ok' => false, 'error' => 'Invalid session ID']);
        }

        try {
            $scoring = $this->scoringModel->getBySessionId($sessionId);
            $this->jsonResponse(200, ['ok' => true, 'scoring' => $scoring]);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $this->jsonResponse(500, ['ok' => false, 'error' => 'Internal Server Error']);
        }
    }

    private function saveScoring(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $role = $_SESSION['role'] ?? 'visualizador';
        if ($role !== 'instructor' && $role !== 'admin') {
            $this->jsonResponse(403, ['ok' => false, 'error' => 'Unauthorized: Only instructors can edit scorings']);
        }

        $input = file_get_contents('php://input');
        $data = json_decode($input, true);

        if (!$data || empty($data['session_id'])) {
            $this->jsonResponse(400, ['ok' => false, 'error' => 'Invalid data']);
        }

        $sessionId = (int)$data['session_id'];

        try {
            $this->scoringModel->saveScoring($sessionId, $data);
            $this->jsonResponse(200, ['ok' => true, 'message' => 'Scoring saved successfully']);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $this->jsonResponse(500, ['ok' => false, 'error' => 'Internal Server Error']);
        }
    }
}
