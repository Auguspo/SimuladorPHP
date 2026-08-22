<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/bootstrap.php';
require_once PROJECT_ROOT . '/private/auth.php';

use App\Controllers\ParticipantController;

$controller = new ParticipantController();
$controller->getAll();
