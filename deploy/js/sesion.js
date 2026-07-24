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

    try {
        const response = await fetch(`/api/session_detail?id=${encodeURIComponent(sessionId)}`);
        
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

        status.textContent = `Sesión ${escapeHtml(sessionId)} cargada.`;
        container.innerHTML = renderSession(json.session);
    } catch (error) {
        status.textContent = 'Error de red al obtener la sesión.';
        container.innerHTML = '<div class="empty-state">No se pudo conectar con el servidor.</div>';
        console.error(error);
    }
}

function renderSession(session) {
    const eventsRows = session.events.map(event => `
        <tr>
            <td>${escapeHtml(String(event.event_number))}</td>
            <td>${escapeHtml(event.stimulus)}</td>
            <td><span class="badge" style="background: ${event.result === 'ACIERTO' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)'}; color: ${event.result === 'ACIERTO' ? 'var(--success)' : 'var(--danger)'}; padding: 4px 8px; border-radius: 4px; font-size: 0.85em; font-weight: 600;">${escapeHtml(event.result)}</span></td>
            <td>${escapeHtml(String(event.time_ms))}</td>
        </tr>
    `).join('');

    return `
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem; margin-top: 1rem;">
            <div style="background: rgba(15,23,42,0.3); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem;">
                <label style="display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 0.25rem;">Conductor</label>
                <span style="font-size: 1.1rem; font-weight: 500;">${escapeHtml(session.participant_name)}</span>
                <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem;">DNI: ${escapeHtml(session.participant_dni)}</div>
            </div>
            
            <div style="background: rgba(15,23,42,0.3); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem;">
                <label style="display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 0.25rem;">Fecha de prueba</label>
                <span style="font-size: 1.1rem; font-weight: 500;">${escapeHtml(session.tested_at)}</span>
            </div>
            
            <div style="background: rgba(15,23,42,0.3); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem;">
                <label style="display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 0.25rem;">Demografía</label>
                <span style="font-size: 1.1rem; font-weight: 500;">Edad: ${session.participant_age ?? '-'}</span>
                <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem;">Peso: ${session.participant_weight_kg !== null ? session.participant_weight_kg.toFixed(2) + ' kg' : '-'}</div>
            </div>
            
            <div style="background: rgba(15,23,42,0.3); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem;">
                <label style="display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 0.25rem;">Métricas de Embrague</label>
                <span style="font-size: 1.1rem; font-weight: 500;">${session.clutch_count ?? '-'} acciones</span>
                <div style="font-size: 0.875rem; color: var(--text-muted); margin-top: 0.25rem;">Tiempo total: ${session.clutch_total_time_s !== null ? session.clutch_total_time_s.toFixed(3) + ' s' : '-'}</div>
            </div>
            
            <div style="grid-column: 1 / -1; background: rgba(15,23,42,0.3); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem;">
                <label style="display: block; font-size: 0.75rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; margin-bottom: 0.25rem;">Comentario</label>
                <span style="font-size: 1rem;">${escapeHtml(session.participant_comment || 'Sin comentario')}</span>
            </div>
        </div>

        <h2 style="margin-top: 2rem; margin-bottom: 1rem; font-size: 1.25rem;">Eventos de Reacción</h2>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Estímulo</th>
                    <th>Resultado</th>
                    <th>Tiempo (ms)</th>
                </tr>
            </thead>
            <tbody>${eventsRows}</tbody>
        </table>
    `;
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
