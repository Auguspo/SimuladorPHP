<?php
$pageTitle = 'Ranking General';
$activeMenu = 'ranking';
require __DIR__ . '/layout/header.php';
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
        <h1 style="margin: 0;">Ranking de Mejores Puntajes</h1>
    </div>
    
    <p id="status" style="color: var(--text-muted); font-size: 0.875rem;">Cargando ranking...</p>
    
    <div id="content">
        <div class="table-responsive">
            <table id="rankingTable" style="display: none;">
                <thead>
                    <tr>
                        <th style="width: 60px;">Posición</th>
                        <th>Conductor</th>
                        <th>Fecha de Evaluación</th>
                        <th>Puntaje Scoring</th>
                        <th style="text-align: right;">Acciones</th>
                    </tr>
                </thead>
                <tbody id="rankingTableBody">
                    <!-- Data populated by JS -->
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="/js/ranking.js?v=<?= date('Ymd') ?>"></script>

<?php require __DIR__ . '/layout/footer.php'; ?>
