<?php
require_once __DIR__ . '/bootstrap.php';
require_once PROJECT_ROOT . '/src/controllers/AuthController.php';

$controller = new AuthController();
$controller->logout();

