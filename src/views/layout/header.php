<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$userFirstName = $_SESSION['first_name'] ?? $_SESSION['name'] ?? '';
$userLastName = $_SESSION['last_name'] ?? '';
$userRole = $_SESSION['role'] ?? 'visualizador';

$initials = '';
if ($userFirstName !== '') {
    $initials .= strtoupper(mb_substr($userFirstName, 0, 1));
}
if ($userLastName !== '') {
    $initials .= strtoupper(mb_substr($userLastName, 0, 1));
}
if ($initials === '') {
    $initials = 'U';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= htmlspecialchars($pageTitle ?? 'Simulador Telemetría') ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css" />
    <script src="/js/formatters.js?v=1"></script>
</head>
<body>
    <div class="topbar">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <div class="brand">SIMULADOR</div>
        </div>
        <nav class="nav">
            <a href="/" class="<?= ($activeMenu ?? '') === 'home' ? 'active' : '' ?>">Sesiones</a>
            <a href="/participantes" class="<?= ($activeMenu ?? '') === 'participantes' ? 'active' : '' ?>">Participantes</a>
            <a href="/estadisticas" class="<?= ($activeMenu ?? '') === 'estadisticas' ? 'active' : '' ?>">Estadísticas</a>
            
            <a href="/opciones" class="<?= ($activeMenu ?? '') === 'opciones' ? 'active' : '' ?>" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                <div class="avatar-circle" title="<?= htmlspecialchars(trim($userFirstName . ' ' . $userLastName)) ?>">
                    <?= htmlspecialchars($initials) ?>
                </div>
                <span>Menú</span>
            </a>
            
        </nav>
    </div>
    <div class="container">

