<?php
require_once __DIR__ . '/bootstrap.php';
require_once PROJECT_ROOT . '/private/auth.php';

use App\Controllers\PageController;

$controller = new PageController();
$controller->sesion();
