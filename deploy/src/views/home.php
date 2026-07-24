<?php
$pageTitle = 'Últimas Sesiones';
$activeMenu = 'home';
require __DIR__ . '/layout/header.php';
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h1 style="margin: 0;">Últimas sesiones</h1>
    </div>
    
    <div class="toolbar" style="margin-bottom: 1.5rem;">
        <input id="searchInput" type="text" placeholder="Buscar por nombre o DNI" style="width: 100%; max-width: 400px; background: rgba(15,23,42,0.5); border: 1px solid var(--border); padding: 0.75rem 1rem; border-radius: 8px; color: white;" />
    </div>
    
    <p class="status" id="status" style="color: var(--text-muted); font-size: 0.875rem;">Cargando datos...</p>

    <div id="table-container"></div>
</div>

<script src="/js/home.js?v=2"></script>

<?php require __DIR__ . '/layout/footer.php'; ?>
