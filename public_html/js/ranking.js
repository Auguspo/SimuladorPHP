async function loadRanking() {
    const status = document.getElementById('status');
    const table = document.getElementById('rankingTable');
    const tbody = document.getElementById('rankingTableBody');
    
    try {
        const response = await fetch('/api/ranking.php');
        if (response.redirected && response.url.includes('login')) {
            window.location.href = '/login';
            return;
        }
        
        const json = await response.json();
        if (!json.ok) {
            status.textContent = 'Error al cargar el ranking.';
            document.getElementById('content').innerHTML = `<div class="empty-state">${escapeHtml(json.error || 'No se pudo cargar')}</div>`;
            return;
        }
        
        const ranking = json.ranking || [];
        if (!ranking.length) {
            status.textContent = 'No hay datos de scoring registrados.';
            document.getElementById('content').innerHTML = '<div class="empty-state">No hay evaluaciones guardadas aún.</div>';
            return;
        }
        
        status.style.display = 'none';
        table.style.display = 'table';
        
        let html = '';
        ranking.forEach((r, index) => {
            const pos = index + 1;
            let badge = pos;
            if (pos === 1) badge = '🥇';
            if (pos === 2) badge = '🥈';
            if (pos === 3) badge = '🥉';
            
            html += `
            <tr>
                <td style="text-align: center; font-size: 1.2rem;">${badge}</td>
                <td>
                    <a href="/participantes/${r.participant_id}" style="color: var(--primary); text-decoration: none; font-weight: 500;">
                        ${escapeHtml(r.participant_name)}
                    </a>
                </td>
                <td>${formatDateAR(r.tested_at)}</td>
                <td style="font-weight: bold; color: #38a169; font-size: 1.1rem;">${r.totalScore} pts</td>
                <td style="text-align: right;">
                    <a href="/sesion/${r.session_id}" class="btn btn-sm">Ver Sesión</a>
                </td>
            </tr>`;
        });
        
        tbody.innerHTML = html;
        
    } catch (error) {
        console.error(error);
        status.textContent = 'Error de red.';
    }
}

function formatDateAR(dateString) {
    if (!dateString) return '-';
    const date = new Date(dateString);
    if (isNaN(date.getTime())) return dateString;
    return new Intl.DateTimeFormat('es-AR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    }).format(date);
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

loadRanking();
