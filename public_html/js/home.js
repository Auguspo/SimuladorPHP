let sessionsData = [];

async function loadSessions() {
    const status = document.getElementById('status');
    const container = document.getElementById('table-container');

    try {
        const response = await fetch('/api/latest_sessions');
        if (response.redirected && response.url.includes('login')) {
            window.location.href = '/login';
            return;
        }
        
        const json = await response.json();

        if (!json.ok) {
            status.textContent = 'Error al cargar sesiones.';
            container.innerHTML = '<div class="empty-state">No se pudieron obtener las sesiones. Revisa el servidor.</div>';
            return;
        }

        sessionsData = json.sessions || [];
        if (!sessionsData.length) {
            status.textContent = 'No hay sesiones registradas aún.';
            container.innerHTML = '<div class="empty-state">Aún no hay datos para mostrar.</div>';
            return;
        }

        applySearch();
    } catch (error) {
        status.textContent = 'Error de red al obtener sesiones.';
        container.innerHTML = '<div class="empty-state">No se pudo conectar con el servidor.</div>';
        console.error(error);
    }
}

function applySearch() {
    const input = document.getElementById('searchInput');
    const query = (input.value || '').toLowerCase().trim();
    const filtered = sessionsData.filter(session => {
        const haystack = `${session.external_id} ${session.participant_name} ${session.participant_dni}`.toLowerCase();
        return haystack.includes(query);
    });

    const status = document.getElementById('status');
    if (!filtered.length) {
        status.textContent = 'No se encontraron coincidencias.';
        document.getElementById('table-container').innerHTML = '<div class="empty-state">No hay resultados para la búsqueda.</div>';
        return;
    }

    status.textContent = `Mostrando ${filtered.length} sesión(es) de ${sessionsData.length} recientes.`;
    document.getElementById('table-container').innerHTML = renderTable(filtered);
}

function renderTable(sessions) {
    const rows = sessions.map(session => `
        <tr>
            <td>${escapeHtml(session.tested_at)}</td>
            <td><span class="badge" style="background: rgba(96, 165, 250, 0.1); color: #60a5fa; padding: 4px 8px; border-radius: 4px; font-size: 0.8em; font-family: monospace;">${escapeHtml(session.external_id)}</span></td>
            <td style="font-weight: 500;">${escapeHtml(session.participant_name)}</td>
            <td>${escapeHtml(session.participant_dni)}</td>
            <td>${session.participant_age ?? '-'}</td>
            <td>${session.participant_weight_kg !== null ? session.participant_weight_kg.toFixed(2) : '-'}</td>
            <td>${escapeHtml(session.participant_comment || '-')}</td>
            <td>${session.events_count}</td>
            <td><a href="/sesion/${session.id}" class="btn" style="padding: 0.25rem 0.75rem; font-size: 0.875rem;">Ver</a></td>
        </tr>
    `).join('');

    return `
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>ID</th>
                    <th>Conductor</th>
                    <th>DNI</th>
                    <th>Edad</th>
                    <th>Peso (kg)</th>
                    <th>Comentario</th>
                    <th>Eventos</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>${rows}</tbody>
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

document.getElementById('searchInput').addEventListener('input', applySearch);
loadSessions();
