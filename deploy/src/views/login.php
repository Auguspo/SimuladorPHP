<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - Simulador Telemetría</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/css/style.css" />
</head>
<body class="login-container">
    <div class="card login-card">
        <div class="login-header">
            <h1 class="brand" style="font-size: 1.75rem;">SIMULADOR</h1>
            <p style="color: var(--text-muted); margin-top: 0.5rem;">Ingreso al Panel de Control</p>
        </div>
        
        <?php if (!empty($error)): ?>
            <div style="background: rgba(239,68,68,0.1); color: var(--danger); padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; text-align: center; border: 1px solid rgba(239,68,68,0.3);">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="/login">
            <div class="form-group">
                <label for="name">Usuario</label>
                <input type="text" id="name" name="name" required autofocus />
            </div>
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required />
            </div>
            <button type="submit" class="btn btn-block" style="margin-top: 1rem;">Iniciar Sesión</button>
        </form>
    </div>
</body>
</html>
