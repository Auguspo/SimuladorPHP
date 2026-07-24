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
</head>
<body>
    <div class="topbar">
        <div class="brand">SIMULADOR</div>
        <nav class="nav">
            <a href="/" class="<?= ($activeMenu ?? '') === 'home' ? 'active' : '' ?>">Sesiones</a>
            <a href="/participantes" class="<?= ($activeMenu ?? '') === 'participantes' ? 'active' : '' ?>">Participantes</a>
            <a href="/estadisticas" class="<?= ($activeMenu ?? '') === 'estadisticas' ? 'active' : '' ?>">Estadísticas</a>
            <a href="/opciones" class="<?= ($activeMenu ?? '') === 'opciones' ? 'active' : '' ?>">Opciones</a>
            <a href="/logout" class="logout">Salir</a>
        </nav>
    </div>
    <div class="container">
