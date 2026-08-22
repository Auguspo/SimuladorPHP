    </div>

    <!-- Modal genérico para mensajes de error o éxito -->
    <div id="genericMessageModal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; justify-content: center; align-items: center; padding: 1rem;">
        <div class="card" style="width: 100%; max-width: 400px; text-align: center;">
            <div id="genericMessageIcon" style="margin-bottom: 1rem;"></div>
            <h3 id="genericMessageTitle" style="margin-top: 0; font-size: 1.25rem;">Mensaje</h3>
            <p id="genericMessageText" style="color: var(--text-muted); margin-bottom: 1.5rem;"></p>
            <button class="btn btn-primary" style="width: 100%;" onclick="document.getElementById('genericMessageModal').style.display='none'">Aceptar</button>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            const error = params.get('error');
            const msj = params.get('msj');
            
            if (error === 'true' && msj) {
                const modal = document.getElementById('genericMessageModal');
                document.getElementById('genericMessageTitle').textContent = 'Error';
                document.getElementById('genericMessageTitle').style.color = 'var(--danger)';
                document.getElementById('genericMessageText').textContent = msj;
                document.getElementById('genericMessageIcon').innerHTML = '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="var(--danger)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>';
                modal.style.display = 'flex';
                
                // Limpiar la URL sin recargar
                const newUrl = window.location.pathname;
                window.history.replaceState({}, document.title, newUrl);
            }
        });
    </script>
</body>
</html>
