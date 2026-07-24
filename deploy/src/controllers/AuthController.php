<?php

require_once dirname(__DIR__, 2) . '/private/db.php';

class AuthController {
    public function login() {
        session_start();
        
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $password = $_POST['password'] ?? '';

            if ($name && $password) {
                $pdo = db();
                $stmt = $pdo->prepare('SELECT id, password_hash, role FROM users WHERE name = :name');
                $stmt->execute(['name' => $name]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password_hash'])) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['name'] = $name;
                    
                    header('Location: /');
                    exit;
                } else {
                    $error = 'Credenciales inválidas';
                }
            } else {
                $error = 'Por favor complete todos los campos';
            }
        }

        require dirname(__DIR__) . '/views/login';
    }

    public function logout() {
        session_start();
        session_destroy();
        header('Location: /login');
        exit;
    }
}
