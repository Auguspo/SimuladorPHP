window.statusModalCallback = null;

window.statusModal = function(title, message, isError = false, showCancel = false, callback = null) {
    const modal = document.getElementById('statusModalElement');
    const titleEl = document.getElementById('statusTitle');
    const textEl = document.getElementById('statusText');
    const iconEl = document.getElementById('statusIcon');
    const btnOk = document.getElementById('btnStatusOk');
    const btnCancel = document.getElementById('btnStatusCancel');
    
    window.statusModalCallback = callback;
    
    titleEl.textContent = title;
    textEl.textContent = message;
    
    if (isError) {
        titleEl.style.color = 'var(--danger)';
        iconEl.innerHTML = '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
        btnOk.className = 'btn btn-danger';
    } else {
        titleEl.style.color = 'var(--success)';
        if (showCancel) {
            titleEl.style.color = 'var(--text-main)';
            iconEl.innerHTML = '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>';
            btnOk.className = 'btn btn-primary';
        } else {
            iconEl.innerHTML = '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--success)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';
            btnOk.className = 'btn btn-primary';
        }
    }
    
    if (showCancel) {
        btnCancel.style.display = 'block';
        btnOk.style.width = 'auto';
    } else {
        btnCancel.style.display = 'none';
        btnOk.style.width = '100%';
    }
    
    modal.style.display = 'flex';
}

window.closeStatusModal = function(result) {
    document.getElementById('statusModalElement').style.display = 'none';
    if (window.statusModalCallback) {
        window.statusModalCallback(result);
        window.statusModalCallback = null;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const params = new URLSearchParams(window.location.search);
    const error = params.get('error');
    const msj = params.get('msj');
    
    if (error === 'true' && msj) {
        window.statusModal('Error', msj, true);
        // Limpiar la URL sin recargar
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
});
