let currentSessionId = null;

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

        status.textContent = `Sesión #${escapeHtml(json.session.id || sessionId)} cargada.`;
        container.innerHTML = renderSession(json.session, deletedFilter);
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
            if (canEdit) {
                if (isDeleted) {
                    actionBtn = `<button class="btn btn-success btn-sm" onclick="toggleEventDeletion(${event.id}, false)">Restaurar</button>`;
                } else {
                    actionBtn = `<button class="btn btn-danger btn-sm" onclick="toggleEventDeletion(${event.id}, true)">Borrar</button>`;
                }
            }

            return `
                <tr ${rowStyle}>
                    <td>#${escapeHtml(String(event.event_number))}</td>
                    <td>${escapeHtml(event.stimulus)}</td>
                    <td>
                        <span class="badge" style="background: ${event.result === 'ACIERTO' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'}; color: ${event.result === 'ACIERTO' ? 'var(--success)' : 'var(--danger)'}; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: 600;">
                            ${escapeHtml(event.result)}
                        </span>
                        ${isDeleted ? '<span class="badge badge-blocked" style="margin-left: 0.5rem;">BORRADO</span>' : ''}
                    </td>
                    <td>${formatIntAR(event.time_ms)} ms</td>
                    ${canEdit ? `<td style="text-align: right;">${actionBtn}</td>` : ''}
                </tr>
            `;
        }).join('')
        : `<tr><td colspan="${canEdit ? 5 : 4}" style="text-align: center; color: var(--text-muted);">No hay eventos para el filtro seleccionado.</td></tr>`;

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
                    <option value="N" ${deletedFilter === 'N' ? 'selected' : ''}>N (Vigentes - Predeterminado)</option>
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
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

loadSession();
