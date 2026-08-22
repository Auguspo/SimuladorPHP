import os

with open('src/views/sesion.php', 'r', encoding='utf-8') as f:
    content = f.read()

start_marker = "<!-- Modal para Scoring -->"
end_marker = "<script src=\"/js/sesion.js"

start_idx = content.find(start_marker)
end_idx = content.find(end_marker)

if start_idx != -1 and end_idx != -1:
    new_modal = """<!-- Modal para Scoring -->
<div id="scoringModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 1000; justify-content: center; align-items: center; overflow-y: auto; padding: 1rem;">
    <div class="card" style="width: 100%; max-width: 800px; margin: auto; max-height: 90vh; overflow-y: auto;">
        
        <h2 style="margin-top: 0; font-size: 1.25rem; margin-bottom: 1.5rem;">Completar Scoring</h2>

        <form id="scoringForm" onsubmit="submitScoring(event)">
            <input type="hidden" id="scoringSessionId">
            
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem; text-align: center;">
                    <thead>
                        <tr style="border-bottom: 2px solid var(--border);">
                            <th style="padding: 0.5rem; text-align: left; width: 40%;">Preguntas</th>
                            <th style="padding: 0.5rem; width: 10%;">Muy malo (1)</th>
                            <th style="padding: 0.5rem; width: 10%;">Malo (2)</th>
                            <th style="padding: 0.5rem; width: 10%;">Regular (3)</th>
                            <th style="padding: 0.5rem; width: 10%;">Bueno (4)</th>
                            <th style="padding: 0.5rem; width: 10%;">Muy bueno (5)</th>
                            <th style="padding: 0.5rem; width: 10%;">VALOR</th>
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

"""
    new_content = content[:start_idx] + new_modal + content[end_idx:]
    with open('src/views/sesion.php', 'w', encoding='utf-8') as f:
        f.write(new_content)
