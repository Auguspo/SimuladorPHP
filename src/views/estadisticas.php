<?php
$pageTitle = 'Estadísticas';
$activeMenu = 'estadisticas';
require __DIR__ . '/layout/header.php';
?>

<div class="card">
    <h1 style="margin-top: 0;">Estadísticas Generales</h1>
    <p style="color: var(--text-muted);">Próximamente: Análisis de tiempos de reacción, precisión por estímulo y evolución de participantes.</p>
    
    <div class="empty-state" style="margin-top: 2rem; border: 1px dashed var(--border); border-radius: 8px;">
        Aquí se mostrarán los gráficos de barras y líneas usando Chart.js (o similar).
    </div>
</div>

<?php require __DIR__ . '/layout/footer.php'; ?>
