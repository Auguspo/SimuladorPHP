<?php
declare(strict_types=1);
require_once dirname(__DIR__) . '/bootstrap.php';

use App\Controllers\TelemetryController;

$controller = new TelemetryController();
$controller->ingest();
