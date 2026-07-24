<?php
$pageTitle = 'Sesiones del conductor';
$activeMenu = 'participantes';
require __DIR__ . '/layout/header.php';
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h1 style="margin: 0;">Sesiones del conductor</h1>
    </div>
    <p id="status" style="color: var(--text-muted); font-size: 0.875rem;">Cargando...</p>
    <div id="content"></div>
</div>

<script src="/js/conductor.js?v=2"></script>

<?php require __DIR__ . '/layout/footer.php'; ?>
