<?php
require_once dirname(__DIR__) . '/src/controllers/AuthController.php';

$controller = new AuthController();
$controller->login();
