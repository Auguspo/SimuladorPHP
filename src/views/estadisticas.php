<?php
$pageTitle = 'Estadísticas Generales';
$activeMenu = 'estadisticas';
require __DIR__ . '/layout/header.php';
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h1 style="margin: 0; font-size: 1.75rem;">Estadísticas & Análisis de Telemetría</h1>
            <p style="color: var(--text-muted); margin: 0.25rem 0 0 0;">Análisis competitivo de tiempos de reacción, precisión por estímulo y uso de embrague.</p>
        </div>
    </div>

    <!-- Barra de Filtros (Participante y Rango de Fechas) -->
    <div style="background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; margin-bottom: 2rem;">
        <div style="font-size: 0.875rem; font-weight: 600; color: var(--text-main); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
            Filtros de Análisis
        </div>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: end;">
            <div>
                <label for="filterParticipant" style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.35rem;">Conductor / Participante</label>
                <select id="filterParticipant" onchange="applyStatsFilters()">
                    <option value="">Cargando participantes...</option>
                </select>
            </div>
            <div>
                <label for="filterFrom" style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.35rem;">Fecha Desde</label>
                <input type="date" id="filterFrom" onchange="applyStatsFilters()" />
            </div>
            <div>
                <label for="filterTo" style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.35rem;">Fecha Hasta</label>
                <input type="date" id="filterTo" onchange="applyStatsFilters()" />
            </div>
            <div>
                <label for="filterDeleted" style="display: block; font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.35rem;">Eventos Borrados</label>
                <select id="filterDeleted" onchange="applyStatsFilters()">
                    <option value="N" selected>N (Vigentes - Predeterminado)</option>
                    <option value="Y">Y (Solo Borrados)</option>
                    <option value="ALL">All (Todos los eventos)</option>
                </select>
            </div>
            <div>
                <button type="button" class="btn btn-secondary btn-block btn-sm" onclick="clearFilters()" style="padding: 0.65rem 1rem;">Limpiar Filtros</button>
            </div>
        </div>
    </div>

    <div id="stats-container">
        <div class="empty-state">Cargando métricas del sistema...</div>
    </div>
</div>

<script src="/js/estadisticas.js?v=2"></script>

<?php require __DIR__ . '/layout/footer.php'; ?>
