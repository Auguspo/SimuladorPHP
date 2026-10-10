<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/bootstrap.php';
require_once PROJECT_ROOT . '/private/auth.php';

use App\Controllers\ParticipantController;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$controller = new ParticipantController();
$controller->deleteParticipant();
