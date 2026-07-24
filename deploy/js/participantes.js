let participantsData = [];

async function loadParticipants() {
    const status = document.getElementById('status');
    const container = document.getElementById('table-container');

    try {
        const response = await fetch('/api/participants');
        if (response.redirected && response.url.includes('login')) {
            window.location.href = '/login';
            return;
        }

        const json = await response.json();

        if (!json.ok) {
            status.textContent = 'Error al cargar participantes.';
            container.innerHTML = '<div class="empty-state">No se pudieron obtener los participantes.</div>';
            return;
        }

        participantsData = json.participants || [];
        if (!participantsData.length) {
            status.textContent = 'No hay participantes registrados aún.';
            container.innerHTML = '<div class="empty-state">Aún no hay datos para mostrar.</div>';
            return;
        }

        applySearch();
    } catch (error) {
        status.textContent = 'Error de red al obtener participantes.';
        container.innerHTML = '<div class="empty-state">No se pudo conectar con el servidor.</div>';
        console.error(error);
    }
}

function applySearch() {
    const input = document.getElementById('searchInput');
    const query = (input.value || '').toLowerCase().trim();
    const filtered = participantsData.filter(participant => {
        const haystack = `${participant.name} ${participant.dni}`.toLowerCase();
        return haystack.includes(query);
    });

    const status = document.getElementById('status');
    if (!filtered.length) {
        status.textContent = 'No se encontraron coincidencias.';
        document.getElementById('table-container').innerHTML = '<div class="empty-state">No hay resultados para la búsqueda.</div>';
        return;
    }

    status.textContent = `Mostrando ${filtered.length} conductor(es) de ${participantsData.length}.`;
    document.getElementById('table-container').innerHTML = renderTable(filtered);
}

function renderTable(participants) {
    const rows = participants.map(participant => `
        <tr>
            <td style="font-weight: 500;"><a href="/participantes/${participant.id}" style="color: var(--primary); text-decoration: none;">${escapeHtml(participant.name)}</a></td>
            <td>${escapeHtml(participant.dni)}</td>
            <td>${participant.sessions_count}</td>
        </tr>
    `).join('');

    return `
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>DNI</th>
                    <th>Sesiones registradas</th>
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
loadParticipants();
