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
        <div style="display: flex; gap: 0.5rem;" id="header-buttons">
            <a href="javascript:history.back()" class="btn btn-secondary btn-sm" style="display: inline-flex; align-items: center; gap: 0.5rem;">
                ← Volver
            </a>
            <!-- Botón de exportar a PDF (Oculto temporalmente)
            <button class="btn btn-secondary btn-sm" onclick="window.print()" style="display: flex; align-items: center; gap: 0.4rem;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
                Exportar PDF
            </button>
            -->

            <!-- Botón de exportar Scoring -->
            <a id="btnExportScoring" href="#" class="btn btn-success btn-sm" style="display: none; align-items: center; gap: 0.4rem; text-decoration: none;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                Exportar Excel
            </a>
            <!-- Botón de Scoring -->
            <button id="btnScoring" class="btn btn-primary btn-sm" onclick="openScoringModal()" style="display: none;">
                Scoring
            </button>
            <!-- Botón de editar (se muestra por JS si tiene permisos) -->
            <button id="btnEditSession" class="btn btn-primary btn-sm" style="display: none;" onclick="openEditModal()">
                Editar Datos
            </button>
        </div>
    </div>
    
    <p class="status" id="status" style="color: var(--text-muted); font-size: 0.875rem;">Cargando sesión...</p>
    <div id="content"></div>
</div>

<!-- Modal para editar la sesión -->
<div id="editSessionModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; justify-content: center; align-items: center;">
    <div class="card" style="width: 100%; max-width: 500px; margin: 20px;">
        <h2 style="margin-top: 0; font-size: 1.25rem;">Editar Detalles de Sesión</h2>
        <form id="editSessionForm" onsubmit="submitEditSession(event)">
            <input type="hidden" id="editSessionId">
            <div class="form-group">
                <label>Edad</label>
                <input type="number" id="editAge" style="width: 100%; background: rgba(15,23,42,0.7); border: 1px solid var(--border); padding: 8px; color: white;">
            </div>
            <div class="form-group">
                <label>Peso (kg)</label>
                <input type="number" step="0.1" id="editWeight" style="width: 100%; background: rgba(15,23,42,0.7); border: 1px solid var(--border); padding: 8px; color: white;">
            </div>
            <!-- Puntaje ahora se maneja por la tabla session_scorings
            <div class="form-group">
                <label>Puntaje Sesion</label>
                <input type="number" id="editScore" placeholder="Ej: 10" style="width: 100%; background: rgba(15,23,42,0.7); border: 1px solid var(--border); padding: 8px; color: white;">
            </div>
            -->
            <div class="form-group">
                <label>Comentarios</label>
                <textarea id="editComment" rows="4" style="width: 100%; background: rgba(15,23,42,0.7); border: 1px solid var(--border); padding: 8px; color: white; resize: vertical;"></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1rem;">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal para Scoring -->
<div id="scoringModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; justify-content: center; align-items: center; overflow-y: auto; padding: 1rem;">
    <div class="card" style="width: 100%; max-width: 800px; margin: auto; max-height: 90vh; overflow-y: auto;">
        
        <h2 style="margin-top: 0; font-size: 1.25rem; margin-bottom: 1.5rem;">Scoring</h2>

        <form id="scoringForm" onsubmit="submitScoring(event)">
            <input type="hidden" id="scoringSessionId">
            
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; text-align: center;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border);">
                            <th style="padding: 0.5rem; text-align: left; width: 40%;">Preguntas</th>
                            <th style="padding: 0.5rem; width: 10%; border-left: 1px solid rgba(255,255,255,0.1);">Muy malo</th>
                            <th style="padding: 0.5rem; width: 10%; border-left: 1px solid rgba(255,255,255,0.1);">Malo</th>
                            <th style="padding: 0.5rem; width: 10%; border-left: 1px solid rgba(255,255,255,0.1);">Regular</th>
                            <th style="padding: 0.5rem; width: 10%; border-left: 1px solid rgba(255,255,255,0.1);">Bueno</th>
                            <th style="padding: 0.5rem; width: 10%; border-left: 1px solid rgba(255,255,255,0.1);">Muy bueno</th>
                            <th style="padding: 0.5rem; width: 10%; border-left: 1px solid rgba(255,255,255,0.1);">Puntaje</th>
                        </tr>
                    </thead>
                    <tbody id="scoringTableBody">
                        <!-- Generado por JS -->
                    </tbody>
                    <tfoot>
                        <tr style="border-top: 2px solid var(--border);">
                            <td colspan="6" style="text-align: right; padding: 1rem; font-weight: bold; font-size: 1rem;">TOTAL:</td>
                            <td style="color: var(--primary); font-weight: bold; font-size: 1.25rem;" id="scoringTotal">0</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div style="display: flex; justify-content: flex-end; gap: 1rem; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary" onclick="closeScoringModal()">Cancelar</button>
                <button type="submit" class="btn btn-primary" id="btnSaveScoring">Guardar Scoring</button>
            </div>
        </form>
    </div>
</div>

<script src="/js/sesion_v2.js?v=<?= date('Ymd') ?>"></script>

<?php require __DIR__ . '/layout/footer.php'; ?>
