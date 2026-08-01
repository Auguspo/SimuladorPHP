/**
 * Formateadores de fecha y números para Argentina (DD/MM/YYYY y separador decimal coma ',')
 */

function formatDateAR(dateStr) {
    if (!dateStr) return '-';
    // dateStr puede ser 'YYYY-MM-DD HH:MM:SS' o 'YYYY-MM-DDTHH:MM:SS'
    const cleanStr = String(dateStr).trim();
    const datePart = cleanStr.split(' ')[0].split('T')[0];
    const parts = datePart.split('-');
    if (parts.length === 3 && parts[0].length === 4) {
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }
    return cleanStr;
}

function formatNumberAR(num, decimals = 2) {
    if (num === null || num === undefined || isNaN(num)) return '-';
    const val = parseFloat(num);
    const formatted = val.toFixed(decimals);
    return formatted.replace('.', ',');
}

function formatIntAR(num) {
    if (num === null || num === undefined || isNaN(num)) return '-';
    return String(num).replace(/\B(?=(\d{3})+(?!\d))/g, ".");
}
