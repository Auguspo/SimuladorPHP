let participantsData = [];
let currentSortColumn = null;
let currentSortOrder = 'none'; // 'none', 'asc', 'desc'
let currentPage = 1;
let itemsPerPage = 50;

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

        currentPage = 1;
        applySearch();
    } catch (error) {
        status.textContent = 'Error de red al obtener participantes.';
        container.innerHTML = '<div class="empty-state">No se pudo conectar con el servidor.</div>';
        console.error(error);
    }
}

function sortParticipants(columnKey) {
    if (currentSortColumn === columnKey) {
        if (currentSortOrder === 'none') {
            currentSortOrder = 'asc';
        } else if (currentSortOrder === 'asc') {
            currentSortOrder = 'desc';
        } else {
            currentSortOrder = 'none';
            currentSortColumn = null;
        }
    } else {
        currentSortColumn = columnKey;
        currentSortOrder = 'asc';
    }
    applySearch();
}

function changeItemsPerPage(newLimit) {
    itemsPerPage = parseInt(newLimit, 10) || 50;
    currentPage = 1;
    applySearch();
}

function goToPage(page) {
    currentPage = page;
    applySearch();
}

function applySearch() {
    const input = document.getElementById('searchInput');
    const query = (input.value || '').toLowerCase().trim();
    
    let filtered = participantsData.filter(participant => {
        const haystack = `${participant.name} ${participant.dni}`.toLowerCase();
        return haystack.includes(query);
    });

    if (currentSortColumn && currentSortOrder !== 'none') {
        filtered.sort((a, b) => {
            let valA = a[currentSortColumn];
            let valB = b[currentSortColumn];

            if (valA === null || valA === undefined) valA = '';
            if (valB === null || valB === undefined) valB = '';

            if (typeof valA === 'number' && typeof valB === 'number') {
                return currentSortOrder === 'asc' ? valA - valB : valB - valA;
            }

            valA = String(valA).toLowerCase();
            valB = String(valB).toLowerCase();

            if (valA < valB) return currentSortOrder === 'asc' ? -1 : 1;
            if (valA > valB) return currentSortOrder === 'asc' ? 1 : -1;
            return 0;
        });
    }

    const status = document.getElementById('status');
    const container = document.getElementById('table-container');

    if (!filtered.length) {
        status.textContent = 'No se encontraron coincidencias.';
        container.innerHTML = '<div class="empty-state">No hay resultados para la búsqueda.</div>';
        return;
    }

    const totalFiltered = filtered.length;
    const totalPages = Math.max(1, Math.ceil(totalFiltered / itemsPerPage));

    if (currentPage > totalPages) {
        currentPage = totalPages;
    }
    if (currentPage < 1) {
        currentPage = 1;
    }

    const startIndex = (currentPage - 1) * itemsPerPage;
    const endIndex = Math.min(startIndex + itemsPerPage, totalFiltered);
    const paginatedParticipants = filtered.slice(startIndex, endIndex);

    let sortText = '';
    if (currentSortColumn && currentSortOrder !== 'none') {
        const orderLabel = currentSortOrder === 'asc' ? 'ascendente (A-Z / 1-9)' : 'descendente (Z-A / 9-1)';
        sortText = ` • Ordenado por [<b>${getColLabel(currentSortColumn)}</b>] ${orderLabel}`;
    }

    status.innerHTML = `Mostrando ${startIndex + 1}–${endIndex} de ${totalFiltered} conductor(es).${sortText}`;
    container.innerHTML = renderTable(paginatedParticipants, totalFiltered, totalPages);
}

function getColLabel(col) {
    const labels = {
        'name': 'Nombre',
        'dni': 'DNI',
        'sessions_count': 'Sesiones registradas'
    };
    return labels[col] || col;
}

function getSortIndicator(col) {
    if (currentSortColumn !== col || currentSortOrder === 'none') {
        return '<span style="opacity: 0.35; font-size: 0.8rem; margin-left: 0.25rem;">↕</span>';
    }
    return currentSortOrder === 'asc' 
        ? '<span style="color: #60a5fa; font-weight: bold; font-size: 0.8rem; margin-left: 0.25rem;">▲</span>' 
        : '<span style="color: #60a5fa; font-weight: bold; font-size: 0.8rem; margin-left: 0.25rem;">▼</span>';
}

function renderTable(participants, totalFiltered, totalPages) {
    const rows = participants.map(participant => `
        <tr>
            <td style="font-weight: 500;"><a href="/participantes/${participant.id}" style="color: var(--primary); text-decoration: none;">${escapeHtml(participant.name)}</a></td>
            <td>${escapeHtml(participant.dni)}</td>
            <td>${participant.sessions_count}</td>
        </tr>
    `).join('');

    const startItem = totalFiltered > 0 ? (currentPage - 1) * itemsPerPage + 1 : 0;
    const endItem = Math.min(currentPage * itemsPerPage, totalFiltered);

    const paginationBar = `
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 1.5rem; flex-wrap: wrap; gap: 1rem; padding-top: 1rem; border-top: 1px solid var(--border);">
            <div style="font-size: 0.875rem; color: var(--text-muted);">
                Mostrando <b>${startItem}–${endItem}</b> de <b>${totalFiltered}</b> conductores
            </div>
            <div style="display: flex; align-items: center; gap: 1.25rem; flex-wrap: wrap;">
                <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: var(--text-muted);">
                    <span>Límite:</span>
                    <select onchange="changeItemsPerPage(this.value)" style="padding: 0.35rem 0.6rem; font-size: 0.85rem; border-radius: 6px; width: auto; background: rgba(15,23,42,0.7);">
                        <option value="10" ${itemsPerPage === 10 ? 'selected' : ''}>10 por pág</option>
                        <option value="25" ${itemsPerPage === 25 ? 'selected' : ''}>25 por pág</option>
                        <option value="50" ${itemsPerPage === 50 ? 'selected' : ''}>50 por pág</option>
                        <option value="100" ${itemsPerPage === 100 ? 'selected' : ''}>100 por pág</option>
                    </select>
                </div>
                <div style="display: flex; gap: 0.5rem; align-items: center;">
                    <button class="btn btn-secondary btn-sm" onclick="goToPage(${currentPage - 1})" ${currentPage <= 1 ? 'disabled style="opacity:0.4; cursor:not-allowed;"' : ''}>« Anterior</button>
                    <span style="font-size: 0.875rem; color: var(--text-main); font-weight: 500; padding: 0 0.5rem;">Página ${currentPage} de ${totalPages}</span>
                    <button class="btn btn-secondary btn-sm" onclick="goToPage(${currentPage + 1})" ${currentPage >= totalPages ? 'disabled style="opacity:0.4; cursor:not-allowed;"' : ''}>Siguiente »</button>
                </div>
            </div>
        </div>
    `;

    return `
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th onclick="sortParticipants('name')" style="cursor: pointer; user-select: none;" title="Ordenar por Nombre">Nombre${getSortIndicator('name')}</th>
                        <th onclick="sortParticipants('dni')" style="cursor: pointer; user-select: none;" title="Ordenar por DNI">DNI${getSortIndicator('dni')}</th>
                        <th onclick="sortParticipants('sessions_count')" style="cursor: pointer; user-select: none;" title="Ordenar por Sesiones">Sesiones registradas${getSortIndicator('sessions_count')}</th>
                    </tr>
                </thead>
                <tbody>${rows}</tbody>
            </table>
        </div>
        ${paginationBar}
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

document.getElementById('searchInput').addEventListener('input', () => {
    currentPage = 1;
    applySearch();
});
loadParticipants();
