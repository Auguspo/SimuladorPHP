<?php

require_once __DIR__ . '/bootstrap.php';
require_once PROJECT_ROOT . '/private/auth.php';
require_once PROJECT_ROOT . '/src/controllers/UserController.php';

$userController = new UserController();
$currentUserId = (int)($_SESSION['user_id'] ?? 0);
$currentUserRole = $_SESSION['role'] ?? 'visualizador';

$successMessage = null;
$errorMessage = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $result = $userController->handleProfileUpdate();
        if ($result['ok']) {
            $successMessage = $result['message'];
        } else {
            $errorMessage = $result['error'];
        }
    } elseif ($action === 'create_user') {
        $result = $userController->handleCreateUser();
        if ($result['ok']) {
            $successMessage = $result['message'];
        } else {
            $errorMessage = $result['error'];
        }
    } elseif ($action === 'update_role') {
        $result = $userController->handleUpdateRole();
        if ($result['ok']) {
            $successMessage = $result['message'];
        } else {
            $errorMessage = $result['error'];
        }
    } elseif ($action === 'toggle_status') {
        $result = $userController->handleToggleStatus();
        if ($result['ok']) {
            $successMessage = $result['message'];
        } else {
            $errorMessage = $result['error'];
        }
    } elseif ($action === 'reset_password') {
        $result = $userController->handleResetPassword();
        if ($result['ok']) {
            $successMessage = $result['message'];
        } else {
            $errorMessage = $result['error'];
        }
    } elseif ($action === 'update_settings') {
        $result = $userController->handleUpdateSettings();
        if ($result['ok']) {
            $successMessage = $result['message'];
        } else {
            $errorMessage = $result['error'];
        }
    }
}

$currentUserData = $userController->getCurrentUser($currentUserId);
$systemSettings = $userController->getSettings();
$allUsersList = [];
if ($currentUserRole !== 'visualizador') {
    $allUsersList = $userController->getAllUsers();
}

require_once PROJECT_ROOT . '/src/views/opciones.php';
