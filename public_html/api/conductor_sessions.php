<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/bootstrap.php';
require_once PROJECT_ROOT . '/private/auth.php';

use App\Controllers\SessionController;

$controller = new SessionController();
$participantId = isset($_GET['id']) ? trim((string) $_GET['id']) : null;

$controller->getByParticipant($participantId);
