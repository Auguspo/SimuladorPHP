<?php

if (!defined('PROJECT_ROOT')) {
    require_once dirname(__DIR__, 2) . '/public_html/bootstrap.php';
}
require_once PROJECT_ROOT . '/private/db.php';

class AuthController {
    public function login() {
        session_start();
        
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($name !== '' && $password !== '') {
                $pdo = db();
                $stmt = $pdo->prepare('SELECT id, name, first_name, last_name, password_hash, role, is_active FROM users WHERE name = :name');
                $stmt->execute(['name' => $name]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password_hash'])) {
                    if (empty($user['is_active'])) {
                        $error = 'Tu cuenta se encuentra bloqueada o desactivada. Contacta a un administrador.';
                    } else {
                        $_SESSION['user_id'] = (int)$user['id'];
                        $_SESSION['role'] = $user['role'];
                        $_SESSION['name'] = $user['name'];
                        $_SESSION['first_name'] = $user['first_name'] ?: $user['name'];
                        $_SESSION['last_name'] = $user['last_name'] ?: '';
                        
                        header('Location: /');
                        exit;
                    }
                } else {
                    $error = 'Credenciales inválidas';
                }
            } else {
                $error = 'Por favor complete todos los campos';
            }
        }

        require PROJECT_ROOT . '/src/views/login.php';
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: /login');
        exit;
    }
}
