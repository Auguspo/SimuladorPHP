<?php

namespace App\Controllers;

use App\Models\ParticipantModel;
use Throwable;

class ParticipantController extends ApiController
{
    private ParticipantModel $participantModel;

    public function __construct()
    {
        $this->participantModel = new ParticipantModel();
    }

    public function getAll(): void
    {
        try {
            $participants = $this->participantModel->getAll();
            $this->jsonResponse(200, ['ok' => true, 'participants' => $participants]);
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $this->jsonResponse(500, ['ok' => false, 'error' => 'Internal Server Error']);
        }
    }

    public function deleteParticipant(): void
    {
        $data = json_decode(file_get_contents('php://input'), true);
        $id = (int)($data['id'] ?? 0);

        if ($id <= 0) {
            $this->jsonResponse(400, ['ok' => false, 'error' => 'ID inválido']);
            return;
        }

        try {
            $this->participantModel->delete($id);
            $this->jsonResponse(200, ['ok' => true]);
        } catch (Throwable $exception) {
            error_log($exception->getMessage());
            $this->jsonResponse(500, ['ok' => false, 'error' => 'Internal Server Error']);
        }
    }
}
