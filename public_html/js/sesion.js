let currentSessionData = null;

async function loadSession() {
    const status = document.getElementById('status');
    const container = document.getElementById('content');
    const params = new URLSearchParams(window.location.search);
    let sessionId = params.get('id');

    if (!sessionId) {
        const pathParts = window.location.pathname.split('/').filter(Boolean);
        sessionId = pathParts[pathParts.length - 1] || null;
    }

    if (!sessionId) {
        status.textContent = 'ID de sesión no encontrado.';
        container.innerHTML = '<div class="empty-state">No se pudo cargar la sesión sin un ID válido.</div>';
        return;
    }

    currentSessionId = sessionId;
    const deletedFilter = document.getElementById('sessionDeletedFilter')?.value || 'N';

    try {
        const response = await fetch(`/api/session_detail?id=${encodeURIComponent(sessionId)}&deleted=${deletedFilter}`);
        
        if (response.redirected && response.url.includes('login')) {
            window.location.href = '/login';
            return;
        }

        const json = await response.json();

        if (!json.ok) {
            status.textContent = 'Error al cargar la sesión.';
            container.innerHTML = `<div class="empty-state">${escapeHtml(json.error || 'Sesión no encontrada.')}</div>`;
            return;
        }

        currentSessionData = json.session;
        status.textContent = `Sesión #${escapeHtml(json.session.id || sessionId)} cargada.`;
        container.innerHTML = renderSession(json.session, deletedFilter);
        
        const canEdit = (window.CURRENT_USER_ROLE === 'instructor' || window.CURRENT_USER_ROLE === 'master');
        const editBtn = document.getElementById('btnEditSession');
        if (editBtn) {
            editBtn.style.display = canEdit ? 'inline-block' : 'none';
        }
        
        const scoringBtn = document.getElementById('btnScoring');
        if (scoringBtn) {
            scoringBtn.style.display = canEdit ? 'inline-block' : 'none';
        }
    } catch (error) {
        status.textContent = 'Error de red al obtener la sesión.';
        container.innerHTML = '<div class="empty-state">No se pudo conectar con el servidor.</div>';
        console.error(error);
    }
}

function renderSession(session, deletedFilter) {
    const canEdit = (window.CURRENT_USER_ROLE && window.CURRENT_USER_ROLE !== 'visualizador');

    const eventsRows = session.events && session.events.length > 0 
        ? session.events.map(event => {
            const isDeleted = Boolean(event.is_deleted);
            const rowStyle = isDeleted ? 'style="opacity: 0.5; text-decoration: line-through; background: rgba(239,68,68,0.05);"' : '';

            let actionBtn = '';
            let tractionField = '';
            
            if (canEdit) {
                if (isDeleted) {
                    actionBtn = `<button class="btn btn-success btn-sm" onclick="toggleEventDeletion(${event.id}, false)">Restaurar</button>`;
                } else {
                    actionBtn = `<button class="btn btn-danger btn-sm" onclick="toggleEventDeletion(${event.id}, true)">Borrar</button>`;
                }
                
                tractionField = `
                    <select onchange="updateEventTraction(${event.id}, this.value)" style="padding: 2px 4px; border-radius: 4px; background: rgba(15,23,42,0.7); border: 1px solid var(--border); color: white; font-size: 0.85em;">
                        <option value="Indefinido" ${event.traction_mode === 'Indefinido' ? 'selected' : ''}>-</option>
                        <option value="2H" ${event.traction_mode === '2H' ? 'selected' : ''}>2H</option>
                        <option value="4H" ${event.traction_mode === '4H' ? 'selected' : ''}>4H</option>
                        <option value="4L" ${event.traction_mode === '4L' ? 'selected' : ''}>4L</option>
                    </select>
                `;
            } else {
                tractionField = `<span style="color: var(--text-muted);">${escapeHtml(event.traction_mode || 'Indefinido')}</span>`;
            }

            return `
                <tr ${rowStyle}>
                    <td data-label="#">#${escapeHtml(String(event.event_number))}</td>
                    <td data-label="Estímulo">${escapeHtml(event.stimulus)}</td>
                    <td data-label="Tracción">${tractionField}</td>
                    <td data-label="Resultado">
                        <span class="badge" style="background: ${event.result === 'ACIERTO' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'}; color: ${event.result === 'ACIERTO' ? 'var(--success)' : 'var(--danger)'}; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: 600;">
                            ${escapeHtml(event.result)}
                        </span>
                        ${isDeleted ? '<span class="badge badge-blocked" style="margin-left: 0.5rem;">BORRADO</span>' : ''}
                    </td>
                    <td data-label="Tiempo (ms)">${formatIntAR(event.time_ms)} ms</td>
                    ${canEdit ? `<td data-label="Acciones" style="text-align: right;">${actionBtn}</td>` : ''}
                </tr>
            `;
        }).join('')
        : `<tr><td colspan="${canEdit ? 6 : 5}" style="text-align: center; color: var(--text-muted);">No hay eventos para el filtro seleccionado.</td></tr>`;

    return `
        <!-- METRICAS DE SESION -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 1rem; margin-top: 1rem;">
            <div style="background: rgba(15,23,42,0.5); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem;">
                <label style="display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 0.25rem;">Conductor</label>
                <span style="font-size: 1.1rem; font-weight: 600; color: var(--text-main);">${escapeHtml(session.participant_name)}</span>
                <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem;">DNI: ${escapeHtml(session.participant_dni)}</div>
            </div>
            
            <div style="background: rgba(15,23,42,0.5); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem;">
                <label style="display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 0.25rem;">Fecha de prueba</label>
                <span style="font-size: 1.1rem; font-weight: 600; color: var(--text-main);">${formatDateAR(session.tested_at)}</span>
            </div>
            
            <div style="background: rgba(15,23,42,0.5); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem;">
                <label style="display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 0.25rem;">Demografía</label>
                <span style="font-size: 1.1rem; font-weight: 600; color: var(--text-main);">Edad: ${session.participant_age ?? '-'}</span>
                <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem;">Peso: ${session.participant_weight_kg !== null ? formatNumberAR(session.participant_weight_kg, 2) + ' kg' : '-'}</div>
            </div>
            
            <div style="background: rgba(15,23,42,0.5); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                <label style="display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 0.25rem;">Puntaje Sesion</label>
                <span style="font-size: 2.5rem; font-weight: 700; color: ${session.instructor_score !== null ? 'var(--primary)' : 'var(--text-muted)'}; line-height: 1;">${session.instructor_score ?? '-'}</span>
            </div>
            
            <div style="background: rgba(15,23,42,0.5); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem;">
                <label style="display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 0.25rem;">Métricas de Embrague</label>
                <span style="font-size: 1.1rem; font-weight: 600; color: var(--text-main);">${session.clutch_count ?? '-'} acciones</span>
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem;" class="nowrap">
                    Tiempo total: <span class="nowrap" style="color: var(--text-main); font-weight: 500;">${session.clutch_total_time_s !== null ? formatNumberAR(session.clutch_total_time_s, 2) + ' s' : '-'}</span>
                </div>
            </div>
            
            <div style="grid-column: 1 / -1; background: rgba(15,23,42,0.5); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem;">
                <label style="display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 0.25rem;">Comentario</label>
                <span style="font-size: 1rem; color: var(--text-main);">${escapeHtml(session.participant_comment || 'Sin comentario')}</span>
            </div>
        </div>

        <!-- BARRA DE EVENTOS & FILTRO Y/N/ALL -->
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2rem; margin-bottom: 1rem; flex-wrap: wrap; gap: 1rem;">
            <h2 style="margin: 0; font-size: 1.25rem;">Eventos de Reacción</h2>
            
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <label for="sessionDeletedFilter" style="font-size: 0.85rem; color: var(--text-muted);">Filtro de Borrados:</label>
                <select id="sessionDeletedFilter" onchange="loadSession()" style="padding: 0.35rem 0.75rem; font-size: 0.85rem; border-radius: 6px; width: auto; background: rgba(15,23,42,0.7);">
                    <option value="N" ${deletedFilter === 'N' ? 'selected' : ''}>N (Vigentes)</option>
                    <option value="Y" ${deletedFilter === 'Y' ? 'selected' : ''}>Y (Solo Borrados)</option>
                    <option value="ALL" ${deletedFilter === 'ALL' ? 'selected' : ''}>All (Todos los eventos)</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Estímulo</th>
                        <th>Tracción</th>
                        <th>Resultado</th>
                        <th>Tiempo (ms)</th>
                        ${canEdit ? '<th style="text-align: right;">Acciones</th>' : ''}
                    </tr>
                </thead>
                <tbody>${eventsRows}</tbody>
            </table>
        </div>
    `;
}

// NUEVO: Funciones para Modal de Edición de Sesión
function openEditModal() {
    if (!currentSessionData) return;
    document.getElementById('editSessionId').value = currentSessionData.id;
    document.getElementById('editAge').value = currentSessionData.participant_age || '';
    document.getElementById('editWeight').value = currentSessionData.participant_weight_kg || '';
    document.getElementById('editScore').value = currentSessionData.instructor_score || '';
    document.getElementById('editComment').value = currentSessionData.participant_comment || '';
    document.getElementById('editSessionModal').style.display = 'flex';
}

function closeEditModal() {
    document.getElementById('editSessionModal').style.display = 'none';
}

async function submitEditSession(e) {
    e.preventDefault();
    const btn = e.target.querySelector('button[type="submit"]');
    btn.disabled = true;
    btn.textContent = 'Guardando...';
    
    const payload = {
        session_id: document.getElementById('editSessionId').value,
        participant_age: document.getElementById('editAge').value,
        participant_weight_kg: document.getElementById('editWeight').value,
        instructor_score: document.getElementById('editScore').value,
        participant_comment: document.getElementById('editComment').value
    };
    
    try {
        const response = await fetch('/api/update_session', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        
        const json = await response.json();
        if (json.ok) {
            closeEditModal();
            loadSession(); // reload data
        } else {
            alert(json.error || 'No se pudo actualizar');
        }
    } catch (e) {
        alert('Error de red');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Guardar Cambios';
    }
}

// NUEVO: Función para actualizar la tracción de un evento
async function updateEventTraction(eventId, newTraction) {
    try {
        const response = await fetch('/api/update_event_traction', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                event_id: eventId,
                traction_mode: newTraction
            })
        });
        const json = await response.json();
        if (!json.ok) {
            alert(json.error || 'Error al actualizar tracción');
            loadSession(); // reset view
        }
    } catch (e) {
        alert('Error de red');
        loadSession(); // reset view
    }
}

async function toggleEventDeletion(eventId, isDeleted) {
    if (!eventId) return;

    try {
        const response = await fetch('/api/toggle_event_deletion', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                event_id: eventId,
                is_deleted: isDeleted
            })
        });

        const json = await response.json();
        if (!json.ok) {
            alert(json.error || 'No se pudo actualizar el estado del evento.');
            return;
        }

        // Recargar el detalle de sesión
        loadSession();
    } catch (e) {
        console.error('Error al cambiar borrado del evento:', e);
        alert('Error de conexión al actualizar el evento.');
    }
}

function escapeHtml(value) {
    if (value === null || value === undefined) return '';
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

loadSession();

const scoringQuestions = [
    { id: 'tiempoReaccionFrenadas', label: 'Tiempo de reacción ante frenadas' },
    { id: 'usoSistemaActivoPasivo', label: 'Uso de sistema activo y pasivo' },
    { id: 'frenadoAceleracionProgresiva', label: 'Frenado y aceleración progresiva' },
    { id: 'respetoSenalesViales', label: 'Respeto de señales viales' },
    { id: 'usoSenalizacionLuminaria', label: 'Uso de señalización luminaria' },
    { id: 'tomaDecisionesSeguras', label: 'Toma de decisiones seguras' },
    { id: 'evitacionManiobrasPeligrosas', label: 'Evitación de maniobras peligrosas o temerarias' },
    { id: 'velocidadAdecuadaContexto', label: 'Velocidad adecuada al contexto' },
    { id: 'conduccionSuavePredecible', label: 'Conducción suave y predecible' },
    { id: 'maniobrasEvasivasSeguras', label: 'Maniobras evasivas seguras' },
    { id: 'evaluacionCorrectaSalidasRiesgo', label: 'Evaluación correcta de salidas de riesgo' }
];

function initScoringModal() {
    const tbody = document.getElementById('scoringTableBody');
    if (!tbody) return;
    
    let html = '';
    scoringQuestions.forEach((q, index) => {
        const bg = index % 2 === 0 ? 'rgba(0,0,0,0.02)' : 'transparent';
        html += `
        <tr style="background: ${bg}; border-bottom: 1px solid #e2e8f0;">
            <td style="padding: 0.75rem; text-align: left; font-weight: 500; border: 1px solid #e2e8f0;">${q.label}</td>
            <td style="border: 1px solid #e2e8f0;"><input type="radio" name="${q.id}" value="1" onchange="updateScoringTotal()" required></td>
            <td style="border: 1px solid #e2e8f0;"><input type="radio" name="${q.id}" value="2" onchange="updateScoringTotal()"></td>
            <td style="border: 1px solid #e2e8f0;"><input type="radio" name="${q.id}" value="3" onchange="updateScoringTotal()"></td>
            <td style="border: 1px solid #e2e8f0;"><input type="radio" name="${q.id}" value="4" onchange="updateScoringTotal()"></td>
            <td style="border: 1px solid #e2e8f0;"><input type="radio" name="${q.id}" value="5" onchange="updateScoringTotal()"></td>
            <td style="border: 1px solid #e2e8f0; font-weight: bold; background: #f7fafc;" id="val_${q.id}">0</td>
        </tr>`;
    });
    tbody.innerHTML = html;
}

function updateScoringTotal() {
    let total = 0;
    scoringQuestions.forEach(q => {
        const selected = document.querySelector(`input[name="${q.id}"]:checked`);
        const val = selected ? parseInt(selected.value) : 0;
        document.getElementById(`val_${q.id}`).textContent = val;
        total += val;
    });
    document.getElementById('scoringTotal').textContent = total;
}

async function openScoringModal() {
    if (!currentSessionData) return;
    document.getElementById('scoringSessionId').value = currentSessionData.id;
    
    // Check role before allowing edits
    const canEdit = (window.CURRENT_USER_ROLE === 'instructor' || window.CURRENT_USER_ROLE === 'admin' || window.CURRENT_USER_ROLE === 'master');
    document.getElementById('btnSaveScoring').style.display = canEdit ? 'inline-block' : 'none';
    
    // Clear current form
    document.getElementById('scoringForm').reset();
    updateScoringTotal();
    
    // Disable inputs if not allowed
    document.querySelectorAll('#scoringTableBody input[type="radio"]').forEach(el => {
        el.disabled = !canEdit;
    });

    try {
        const res = await fetch(`/api/scoring?session_id=${currentSessionData.id}`);
        const json = await res.json();
        
        if (json.ok && json.scoring) {
            const data = json.scoring;
            scoringQuestions.forEach(q => {
                if (data[q.id]) {
                    const radio = document.querySelector(`input[name="${q.id}"][value="${data[q.id]}"]`);
                    if (radio) radio.checked = true;
                }
            });
            updateScoringTotal();
        }
    } catch (e) {
        console.error(e);
    }
    
    document.getElementById('scoringModal').style.display = 'flex';
}

function closeScoringModal() {
    document.getElementById('scoringModal').style.display = 'none';
}

async function submitScoring(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSaveScoring');
    btn.disabled = true;
    btn.textContent = 'Guardando...';
    
    const payload = {
        session_id: document.getElementById('scoringSessionId').value,
        totalScore: document.getElementById('scoringTotal').textContent
    };
    
    scoringQuestions.forEach(q => {
        const selected = document.querySelector(`input[name="${q.id}"]:checked`);
        payload[q.id] = selected ? parseInt(selected.value) : 0;
    });
    
    try {
        const res = await fetch('/api/scoring', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (json.ok) {
            closeScoringModal();
        } else {
            alert(json.error || 'Error al guardar el scoring');
        }
    } catch (e) {
        alert('Error de conexión');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Guardar Scoring';
    }
}

// Call init on load
initScoringModal();
