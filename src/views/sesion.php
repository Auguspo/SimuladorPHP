<?php
$pageTitle = 'Detalle de Sesión';
$activeMenu = 'home';
require __DIR__ . '/layout/header.php';
$userRole = $_SESSION['role'] ?? 'visualizador';
?>

<script>
    window.CURRENT_USER_ROLE = '<?= htmlspecialchars($userRole) ?>';
</script>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
        <h1 style="margin: 0;">Detalle de Sesión</h1>
        <a href="javascript:history.back()" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.5rem;">
            ← Volver
        </a>
    </div>
    
    <p class="status" id="status" style="color: var(--text-muted); font-size: 0.875rem;">Cargando sesión...</p>
    <div id="content"></div>
</div>

<script src="/js/sesion.js?v=3"></script>

<?php require __DIR__ . '/layout/footer.php'; ?>
