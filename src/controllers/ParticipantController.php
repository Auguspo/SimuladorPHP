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
}
