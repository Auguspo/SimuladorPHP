<?php

declare(strict_types=1);

if (!defined('PROJECT_ROOT')) {
    require_once dirname(__DIR__, 2) . '/public_html/bootstrap.php';
}
require_once PROJECT_ROOT . '/private/db.php';
require_once PROJECT_ROOT . '/private/auth.php';

class UserController {

    public function handleProfileUpdate(): array {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            return ['ok' => false, 'error' => 'Sesión expirada'];
        }

        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($firstName === '') {
            return ['ok' => false, 'error' => 'El nombre es obligatorio'];
        }

        $pdo = db();

        // 1. Si se solicita cambio de contraseña
        if ($newPassword !== '') {
            if ($newPassword !== $confirmPassword) {
                return ['ok' => false, 'error' => 'La nueva contraseña y su confirmación no coinciden'];
            }
            if (strlen($newPassword) < 6) {
                return ['ok' => false, 'error' => 'La nueva contraseña debe tener al menos 6 caracteres'];
            }
            if ($currentPassword === '') {
                return ['ok' => false, 'error' => 'Debe ingresar su contraseña actual para realizar el cambio'];
            }

            // Verificar la contraseña actual
            $stmt = $pdo->prepare('SELECT password_hash FROM users WHERE id = :id');
            $stmt->execute([':id' => $userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
                return ['ok' => false, 'error' => 'La contraseña actual es incorrecta'];
            }

            // Actualizar contraseña y nombre/apellido
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $updateStmt = $pdo->prepare(
                'UPDATE users SET first_name = :first_name, last_name = :last_name, password_hash = :hash WHERE id = :id'
            );
            $updateStmt->execute([
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':hash' => $newHash,
                ':id' => $userId
            ]);
        } else {
            // Solo actualizar nombre y apellido
            $updateStmt = $pdo->prepare(
                'UPDATE users SET first_name = :first_name, last_name = :last_name WHERE id = :id'
            );
            $updateStmt->execute([
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':id' => $userId
            ]);
        }

        // Actualizar datos en la sesión
        $_SESSION['first_name'] = $firstName;
        $_SESSION['last_name'] = $lastName;

        return ['ok' => true, 'message' => 'Perfil actualizado correctamente'];
    }

    public function handleCreateUser(): array {
        $currentUserRole = $_SESSION['role'] ?? 'visualizador';
        if ($currentUserRole === 'visualizador') {
            return ['ok' => false, 'error' => 'No tienes permisos para crear usuarios'];
        }

        $username = trim($_POST['username'] ?? '');
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = trim($_POST['role'] ?? 'visualizador');

        if ($username === '' || $firstName === '' || $password === '') {
            return ['ok' => false, 'error' => 'Usuario, Nombre y Contraseña son obligatorios'];
        }

        if (strlen($password) < 6) {
            return ['ok' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres'];
        }

        // REGLA CLAVE: El rol 'master' NUNCA se puede asignar
        if ($role === 'master' || !in_array($role, ['instructor', 'visualizador'], true)) {
            return ['ok' => false, 'error' => 'El rol seleccionado no es válido. Solo se pueden asignar roles instructor o visualizador.'];
        }

        $pdo = db();
        try {
            $stmt = $pdo->prepare(
                'INSERT INTO users (name, first_name, last_name, password_hash, role, is_active)
                 VALUES (:name, :first_name, :last_name, :hash, :role, 1)'
            );
            $stmt->execute([
                ':name' => $username,
                ':first_name' => $firstName,
                ':last_name' => $lastName,
                ':hash' => password_hash($password, PASSWORD_DEFAULT),
                ':role' => $role
            ]);

            return ['ok' => true, 'message' => "Usuario '{$username}' creado exitosamente como {$role}"];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                return ['ok' => false, 'error' => 'El nombre de usuario o DNI ya existe'];
            }
            return ['ok' => false, 'error' => 'Error en la base de datos: ' . $e->getMessage()];
        }
    }

    public function handleUpdateRole(): array {
        $currentUserRole = $_SESSION['role'] ?? 'visualizador';
        if ($currentUserRole === 'visualizador') {
            return ['ok' => false, 'error' => 'No tienes permisos para modificar roles'];
        }

        $targetUserId = (int)($_POST['target_user_id'] ?? 0);
        $newRole = trim($_POST['new_role'] ?? '');

        if ($targetUserId <= 0) {
            return ['ok' => false, 'error' => 'Usuario no válido'];
        }

        // REGLA CLAVE: El rol 'master' NUNCA se puede asignar
        if ($newRole === 'master' || !in_array($newRole, ['instructor', 'visualizador'], true)) {
            return ['ok' => false, 'error' => 'Solo se puede alternar entre los roles instructor y visualizador'];
        }

        $pdo = db();
        // Verificar que el usuario a modificar NO sea 'master'
        $stmt = $pdo->prepare('SELECT role FROM users WHERE id = :id');
        $stmt->execute([':id' => $targetUserId]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$targetUser) {
            return ['ok' => false, 'error' => 'El usuario no existe'];
        }

        if ($targetUser['role'] === 'master') {
            return ['ok' => false, 'error' => 'No se puede modificar el rol de un usuario Master'];
        }

        $update = $pdo->prepare('UPDATE users SET role = :role WHERE id = :id');
        $update->execute([':role' => $newRole, ':id' => $targetUserId]);

        return ['ok' => true, 'message' => 'Rol actualizado correctamente'];
    }

    public function handleToggleStatus(): array {
        $currentUserRole = $_SESSION['role'] ?? 'visualizador';
        if ($currentUserRole === 'visualizador') {
            return ['ok' => false, 'error' => 'No tienes permisos para modificar el estado de usuarios'];
        }

        $targetUserId = (int)($_POST['target_user_id'] ?? 0);
        if ($targetUserId <= 0) {
            return ['ok' => false, 'error' => 'Usuario no válido'];
        }

        $pdo = db();
        $stmt = $pdo->prepare('SELECT role, is_active FROM users WHERE id = :id');
        $stmt->execute([':id' => $targetUserId]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$targetUser) {
            return ['ok' => false, 'error' => 'El usuario no existe'];
        }

        if ($targetUser['role'] === 'master') {
            return ['ok' => false, 'error' => 'No se puede bloquear o desactivar a un usuario Master'];
        }

        // Alternar is_active (1 -> 0 ó 0 -> 1)
        $newStatus = empty($targetUser['is_active']) ? 1 : 0;
        $update = $pdo->prepare('UPDATE users SET is_active = :status WHERE id = :id');
        $update->execute([':status' => $newStatus, ':id' => $targetUserId]);

        $statusText = $newStatus === 1 ? 'activado' : 'bloqueado';
        return ['ok' => true, 'message' => "El usuario ha sido {$statusText} exitosamente"];
    }

    public function handleResetPassword(): array {
        $currentUserRole = $_SESSION['role'] ?? 'visualizador';
        if ($currentUserRole === 'visualizador') {
            return ['ok' => false, 'error' => 'No tienes permisos para cambiar contraseñas de otros usuarios'];
        }

        $targetUserId = (int)($_POST['target_user_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';

        if ($targetUserId <= 0 || strlen($newPassword) < 6) {
            return ['ok' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres'];
        }

        $pdo = db();
        $stmt = $pdo->prepare('SELECT role FROM users WHERE id = :id');
        $stmt->execute([':id' => $targetUserId]);
        $targetUser = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$targetUser) {
            return ['ok' => false, 'error' => 'El usuario no existe'];
        }

        if ($targetUser['role'] === 'master' && $currentUserRole !== 'master') {
            return ['ok' => false, 'error' => 'No puedes cambiar la contraseña de un usuario Master'];
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $update = $pdo->prepare('UPDATE users SET password_hash = :hash WHERE id = :id');
        $update->execute([':hash' => $newHash, ':id' => $targetUserId]);

        return ['ok' => true, 'message' => 'Contraseña restablecida con éxito'];
    }

    public function getAllUsers(): array {
        $pdo = db();
        $stmt = $pdo->query(
            'SELECT id, role, name, first_name, last_name, dni, is_active, created_at FROM users ORDER BY id ASC'
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    public function getSettings(): array {
        $pdo = db();
        $stmt = $pdo->query('SELECT setting_key, setting_value FROM system_settings');
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        $settings = [
            'fast_threshold_ms' => 300,
            'slow_threshold_ms' => 450,
            'max_timeout_ms' => 8000,
        ];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = (int)$row['setting_value'];
        }
        return $settings;
    }

    public function handleUpdateSettings(): array {
        $currentUserRole = $_SESSION['role'] ?? 'visualizador';
        if ($currentUserRole === 'visualizador') {
            return ['ok' => false, 'error' => 'No tienes permisos para modificar la configuración del sistema'];
        }

        $fastMs = filter_var($_POST['fast_threshold_ms'] ?? null, FILTER_VALIDATE_INT);
        $slowMs = filter_var($_POST['slow_threshold_ms'] ?? null, FILTER_VALIDATE_INT);
        $maxTimeoutMs = 8000; // Máximo fijo de 8 segundos (timeout)

        if ($fastMs === false || $slowMs === false) {
            return ['ok' => false, 'error' => 'Los límites deben ser números enteros en milisegundos'];
        }

        if ($fastMs < 0 || $fastMs > $maxTimeoutMs || $slowMs < 0 || $slowMs > $maxTimeoutMs) {
            return ['ok' => false, 'error' => 'Los límites deben estar en el rango de 0 a 8.000 ms (8 seg)'];
        }

        // Regla: El límite de acierto (rápido) y de fallo (lento) no pueden ser iguales
        if ($fastMs === $slowMs) {
            return ['ok' => false, 'error' => 'El límite de acierto (rápido) y el límite de fallo (lento) no pueden ser iguales.'];
        }

        if ($fastMs >= $slowMs) {
            return ['ok' => false, 'error' => 'El límite de acierto (rápido) debe ser menor que el límite de fallo (lento)'];
        }

        $pdo = db();
        $stmt = $pdo->prepare(
            'INSERT INTO system_settings (setting_key, setting_value) VALUES (:key, :val)
             ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
        );

        $stmt->execute([':key' => 'fast_threshold_ms', ':val' => (string)$fastMs]);
        $stmt->execute([':key' => 'slow_threshold_ms', ':val' => (string)$slowMs]);
        $stmt->execute([':key' => 'max_timeout_ms', ':val' => (string)$maxTimeoutMs]);

        return ['ok' => true, 'message' => 'Configuración de límites de reacción actualizada correctamente'];
    }

    public function getCurrentUser(int $userId): ?array {
        $pdo = db();
        $stmt = $pdo->prepare(
            'SELECT id, role, name, first_name, last_name, dni, is_active, created_at FROM users WHERE id = :id'
        );
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ?: null;
    }
}
