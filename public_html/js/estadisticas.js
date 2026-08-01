async function initStatsPage() {
    await loadParticipantsDropdown();
    await loadStats();
}

async function loadParticipantsDropdown() {
    const select = document.getElementById('filterParticipant');
    if (!select) return;

    try {
        const response = await fetch('/api/participants');
        if (!response.ok) return;

        const json = await response.json();
        if (json.ok && Array.isArray(json.participants)) {
            let options = '<option value="">Todos los conductores</option>';
            json.participants.forEach(p => {
                options += `<option value="${p.id}">${escapeHtml(p.name)} (${escapeHtml(p.dni)})</option>`;
            });
            select.innerHTML = options;
        } else {
            select.innerHTML = '<option value="">Todos los conductores</option>';
        }
    } catch (e) {
        console.error('Error al cargar participantes en filtro:', e);
        select.innerHTML = '<option value="">Todos los conductores</option>';
    }
}

async function loadStats() {
    const container = document.getElementById('stats-container');
    const participantId = document.getElementById('filterParticipant')?.value || '';
    const fromDate = document.getElementById('filterFrom')?.value || '';
    const toDate = document.getElementById('filterTo')?.value || '';
    const deletedFilter = document.getElementById('filterDeleted')?.value || 'N';

    const params = new URLSearchParams();
    if (participantId) params.append('participant_id', participantId);
    if (fromDate) params.append('from', fromDate);
    if (toDate) params.append('to', toDate);
    if (deletedFilter) params.append('deleted', deletedFilter);

    const queryString = params.toString() ? `?${params.toString()}` : '';

    try {
        const response = await fetch(`/api/stats${queryString}`);
        if (response.redirected && response.url.includes('login')) {
            window.location.href = '/login';
            return;
        }

        const json = await response.json();
        if (!json.ok) {
            container.innerHTML = `<div class="empty-state">Error al cargar las estadísticas: ${escapeHtml(json.error || '')}</div>`;
            return;
        }

        renderDashboard(json);
    } catch (err) {
        console.error(err);
        container.innerHTML = '<div class="empty-state">No se pudo conectar con el servidor.</div>';
    }
}

function applyStatsFilters() {
    loadStats();
}

function clearFilters() {
    const participantSelect = document.getElementById('filterParticipant');
    const fromInput = document.getElementById('filterFrom');
    const toInput = document.getElementById('filterTo');
    const deletedSelect = document.getElementById('filterDeleted');

    if (participantSelect) participantSelect.value = '';
    if (fromInput) fromInput.value = '';
    if (toInput) toInput.value = '';
    if (deletedSelect) deletedSelect.value = 'N';

    loadStats();
}

function renderDashboard(data) {
    const container = document.getElementById('stats-container');
    const k = data.kpis;
    const t = data.thresholds || { fast_ms: 300, slow_ms: 450, max_timeout_ms: 8000 };
    const ranges = data.speed_ranges;
    const totalSpeed = (ranges.fast + ranges.normal + ranges.slow) || 1;
    const fastPct = Math.round((ranges.fast / totalSpeed) * 100);
    const normalPct = Math.round((ranges.normal / totalSpeed) * 100);
    const slowPct = 100 - fastPct - normalPct;

    const hasFilters = Boolean(data.filters?.participant_id || data.filters?.from || data.filters?.to || (data.filters?.deleted && data.filters.deleted !== 'N'));

    const html = `
        ${hasFilters ? `
            <div style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3); color: #60a5fa; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-size: 0.875rem;">
                🔍 Mostrando estadísticas filtradas para las condiciones seleccionadas (Filtro Borrados: <b>${escapeHtml(data.filters.deleted || 'N')}</b>).
            </div>
        ` : ''}

        <!-- TOP KPI CARDS CON COMAS DECIMALES Y FORMATO AR -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 1.25rem; margin-bottom: 2rem;">
            
            <div style="background: rgba(15,23,42,0.6); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem;">
                <div style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Total de Sesiones</div>
                <div style="font-size: 2rem; font-weight: 700; color: #60a5fa; margin-top: 0.25rem;">${k.total_sessions}</div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">${k.total_participants} conductor(es) evaluado(s)</div>
            </div>

            <div style="background: rgba(15,23,42,0.6); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem;">
                <div style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Tasa Global de Precisión</div>
                <div style="font-size: 2rem; font-weight: 700; color: #34d399; margin-top: 0.25rem;">${formatNumberAR(k.accuracy_rate, 1)}%</div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;">${k.total_events} eventos evaluados</div>
            </div>

            <div style="background: rgba(15,23,42,0.6); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem;">
                <div style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Reacción Promedio</div>
                <div style="font-size: 2rem; font-weight: 700; color: #a78bfa; margin-top: 0.25rem;" class="nowrap">${formatNumberAR(k.avg_reaction_ms, 1)} <span style="font-size: 1rem; font-weight: 400;">ms</span></div>
                <div style="font-size: 0.8rem; color: var(--text-muted); margin-top: 0.25rem;" class="nowrap">Récord mínimo: <b style="color: #34d399;">${k.min_reaction_ms} ms</b></div>
            </div>

            <div style="background: rgba(15,23,42,0.6); border: 1px solid var(--border); border-radius: 12px; padding: 1.25rem;">
                <div style="color: var(--text-muted); font-size: 0.75rem; text-transform: uppercase; font-weight: 600;">Uso de Embrague / Sesión</div>
                <div style="font-size: 2rem; font-weight: 700; color: #facc15; margin-top: 0.25rem;" class="nowrap">${formatNumberAR(k.avg_clutch_count, 1)} <span style="font-size: 1rem; font-weight: 400;">acc</span></div>
                <div style="font-size: 0.85rem; color: var(--text-muted); margin-top: 0.25rem; white-space: nowrap;">
                    Tiempo medio accionamiento: <span class="nowrap" style="color: var(--text-main); font-weight: 500;">${formatNumberAR(k.avg_clutch_time_s, 2)} s</span>
                </div>
            </div>
        </div>

        <!-- BARRA DE DISTRIBUCION DE VELOCIDADES DE REACCION (UMBRALES CONFIGURABLES) -->
        <div style="background: rgba(15,23,42,0.4); border: 1px solid var(--border); border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                <h3 style="margin: 0; font-size: 1.1rem; color: var(--text-main);">Distribución de Velocidad de Reacción</h3>
                <span style="font-size: 0.85rem; color: var(--text-muted);">${k.total_events} eventos totales</span>
            </div>

            <!-- Multi-color Progress Bar -->
            <div style="display: flex; height: 16px; border-radius: 999px; overflow: hidden; background: rgba(0,0,0,0.3); margin-bottom: 1rem;">
                <div style="width: ${fastPct}%; background: #34d399;" title="Rápido (<${t.fast_ms}ms): ${ranges.fast}"></div>
                <div style="width: ${normalPct}%; background: #60a5fa;" title="Normal (${t.fast_ms}-${t.slow_ms}ms): ${ranges.normal}"></div>
                <div style="width: ${slowPct}%; background: #f87171;" title="Lento (>${t.slow_ms}ms): ${ranges.slow}"></div>
            </div>

            <div style="display: flex; gap: 1.5rem; flex-wrap: wrap; font-size: 0.85rem;">
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 12px; height: 12px; border-radius: 50%; background: #34d399;"></div>
                    <span>Rápido (&lt;${t.fast_ms} ms): <b>${ranges.fast}</b> (${fastPct}%)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 12px; height: 12px; border-radius: 50%; background: #60a5fa;"></div>
                    <span>Normal (${t.fast_ms}–${t.slow_ms} ms): <b>${ranges.normal}</b> (${normalPct}%)</span>
                </div>
                <div style="display: flex; align-items: center; gap: 0.5rem;">
                    <div style="width: 12px; height: 12px; border-radius: 50%; background: #f87171;"></div>
                    <span>Lento (&gt;${t.slow_ms} ms): <b>${ranges.slow}</b> (${slowPct}%)</span>
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(340px, 1fr)); gap: 1.5rem;">
            
            <!-- SECCIÓN: RANKING DE CONDUCTORES -->
            <div style="background: rgba(15,23,42,0.4); border: 1px solid var(--border); border-radius: 12px; padding: 1.5rem;">
                <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem; color: var(--text-main);">
                    🏆 Ranking de Conductores (Menor Tiempo Reacción)
                </h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Conductor</th>
                                <th>Pruebas</th>
                                <th>Avg (ms)</th>
                                <th>Mín (ms)</th>
                                <th>Precisión</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${renderLeaderboard(data.driver_leaderboard)}
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- SECCIÓN: RENDIMIENTO POR ESTÍMULO -->
            <div style="background: rgba(15,23,42,0.4); border: 1px solid var(--border); border-radius: 12px; padding: 1.5rem;">
                <h3 style="margin-top: 0; margin-bottom: 1rem; font-size: 1.1rem; color: var(--text-main);">
                    🎯 Desglose por Tipo de Estímulo
                </h3>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Estímulo</th>
                                <th>Evaluaciones</th>
                                <th>Promedio (ms)</th>
                                <th>Aciertos</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${renderStimulusBreakdown(data.stimulus_breakdown)}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    `;

    container.innerHTML = html;
}

function renderLeaderboard(drivers) {
    if (!drivers || !drivers.length) {
        return '<tr><td colspan="5" style="text-align: center; color: var(--text-muted);">Sin datos suficientes para este filtro</td></tr>';
    }

    return drivers.map((d, index) => {
        const medal = index === 0 ? '🥇' : index === 1 ? '🥈' : index === 2 ? '🥉' : `#${index + 1}`;
        const avg = formatNumberAR(parseFloat(d.avg_reaction_ms) || 0, 1);
        const best = Math.round(parseFloat(d.best_reaction_ms) || 0);
        const acc = formatNumberAR(parseFloat(d.accuracy_pct) || 0, 1);

        return `
            <tr>
                <td><b>${medal}</b> ${escapeHtml(d.name)}</td>
                <td>${d.sessions_count}</td>
                <td><b style="color: #a78bfa;">${avg} ms</b></td>
                <td><span style="color: #34d399;">${best} ms</span></td>
                <td><span class="badge ${parseFloat(d.accuracy_pct) >= 80 ? 'badge-active' : 'badge-blocked'}">${acc}%</span></td>
            </tr>
        `;
    }).join('');
}

function renderStimulusBreakdown(list) {
    if (!list || !list.length) {
        return '<tr><td colspan="4" style="text-align: center; color: var(--text-muted);">Sin datos de estímulos para este filtro</td></tr>';
    }

    return list.map(s => {
        const count = parseInt(s.count) || 0;
        const aciertos = parseInt(s.aciertos) || 0;
        const pctVal = count > 0 ? (aciertos / count) * 100 : 0;
        const pctStr = formatNumberAR(pctVal, 1);
        const avg = formatNumberAR(parseFloat(s.avg_ms) || 0, 1);

        return `
            <tr>
                <td><b>${escapeHtml(s.stimulus)}</b></td>
                <td>${count}</td>
                <td><b style="color: #60a5fa;">${avg} ms</b></td>
                <td><span class="badge ${pctVal >= 80 ? 'badge-active' : 'badge-blocked'}">${aciertos}/${count} (${pctStr}%)</span></td>
            </tr>
        `;
    }).join('');
}

function escapeHtml(val) {
    return String(val)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

initStatsPage();
