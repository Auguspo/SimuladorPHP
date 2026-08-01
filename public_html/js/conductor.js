async function loadConductorSessions() {
    const status = document.getElementById('status');
    const container = document.getElementById('content');
    const params = new URLSearchParams(window.location.search);
    let participantId = params.get('id');

    if (!participantId) {
        const pathParts = window.location.pathname.split('/').filter(Boolean);
        participantId = pathParts[pathParts.length - 1] || null;
    }

    if (!participantId) {
        status.textContent = 'No se encontró el conductor.';
        container.innerHTML = '<div class="empty-state">Falta el identificador del conductor.</div>';
        return;
    }

    try {
        const response = await fetch(`/api/conductor_sessions?id=${encodeURIComponent(participantId)}`);
        
        if (response.redirected && response.url.includes('login')) {
            window.location.href = '/login';
            return;
        }

        const json = await response.json();
        if (!json.ok) {
            status.textContent = 'Error al cargar las sesiones.';
            container.innerHTML = `<div class="empty-state">${escapeHtml(json.error || 'No se pudo cargar')}</div>`;
            return;
        }

        const sessions = json.sessions || [];
        if (!sessions.length) {
            status.textContent = 'No hay sesiones para este conductor.';
            container.innerHTML = '<div class="empty-state">No hay sesiones registradas para este conductor.</div>';
            return;
        }

        status.textContent = `Mostrando ${sessions.length} sesión(es) para ${escapeHtml(json.participant_name)}.`;
        container.innerHTML = renderTable(sessions);
    } catch (error) {
        status.textContent = 'Error de red.';
        container.innerHTML = '<div class="empty-state">No se pudo conectar con el servidor.</div>';
        console.error(error);
    }
}

function renderTable(sessions) {
    const rows = sessions.map(session => `
        <tr>
            <td>#${session.id}</td>
            <td>${formatDateAR(session.tested_at)}</td>
            <td>${session.events_count}</td>
            <td><a href="/sesion/${session.id}" class="btn" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;">Ver</a></td>
        </tr>
    `).join('');

    return `
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha</th>
                        <th>Eventos</th>
                        <th>Detalle</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>`;
}

function escapeHtml(value) {
    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

loadConductorSessions();
