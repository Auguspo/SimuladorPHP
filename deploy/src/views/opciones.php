<?php
$pageTitle = 'Opciones';
$activeMenu = 'opciones';
require __DIR__ . '/layout/header.php';
?>

<div class="card">
    <h1 style="margin-top: 0;">Opciones</h1>
    <p style="color: var(--text-muted);">Configuración y administración de la cuenta.</p>
    
    <ul class="option-list" style="margin: 2rem 0 0 0; padding: 0; list-style: none;">
        <li style="padding: 1rem 0; border-bottom: 1px solid var(--border);">
            <a href="#" style="color: var(--primary); text-decoration: none; font-weight: 500;">Cambiar contraseña</a>
        </li>
        <li style="padding: 1rem 0; border-bottom: 1px solid var(--border);">
            <a href="#" style="color: var(--primary); text-decoration: none; font-weight: 500;">Configuración de perfil</a>
        </li>
        <li style="padding: 1rem 0;">
            <a href="/logout" style="color: var(--danger); text-decoration: none; font-weight: 500;">Cerrar sesión</a>
        </li>
    </ul>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
