<?php
$pageTitle = 'Detalle de sesión';
$activeMenu = 'home';
require __DIR__ . '/layout/header.php';
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h1 style="margin: 0;">Detalle de sesión</h1>
        <a href="javascript:history.back()" class="btn" style="background: transparent; border: 1px solid var(--border); color: var(--text-main);">Volver</a>
    </div>
    
    <p class="status" id="status" style="color: var(--text-muted); font-size: 0.875rem;">Cargando sesión...</p>
    <div id="content"></div>
</div>

<script src="/js/sesion.js?v=2"></script>

<?php require __DIR__ . '/layout/footer.php'; ?>
