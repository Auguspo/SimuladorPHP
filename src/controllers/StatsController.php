<?php

namespace App\Controllers;

use App\Models\StatsModel;
use Throwable;

class StatsController extends ApiController
{
    private StatsModel $statsModel;

    public function __construct()
    {
        $this->statsModel = new StatsModel();
    }

    public function getStats(): void
    {
        try {
            $participantId = isset($_GET['participant_id']) && ctype_digit($_GET['participant_id']) ? (int)$_GET['participant_id'] : null;
            $fromDate = !empty($_GET['from']) ? trim((string)$_GET['from']) : null;
            $toDate = !empty($_GET['to']) ? trim((string)$_GET['to']) : null;
            $deletedFilter = strtoupper(trim((string)($_GET['deleted'] ?? 'N')));

            $stats = $this->statsModel->getStats($participantId, $fromDate, $toDate, $deletedFilter);
            
            $stats['ok'] = true;
            $stats['filters'] = [
                'participant_id' => $participantId,
                'from' => $fromDate,
                'to' => $toDate,
                'deleted' => $deletedFilter
            ];

            $this->jsonResponse(200, $stats);
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $this->jsonResponse(500, ['ok' => false, 'error' => 'Error al calcular estadísticas']);
        }
    }
}
