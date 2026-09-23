// Funciones de la pantalla de Opciones

function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(el => {
        // Special case: subtabs shouldn't be affected if we are just switching main tabs
        // But since our profile subtabs don't have 'tab-content' class, this is safe.
        el.style.display = 'none';
    });
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    
    // El usersTab tiene un display flex/grid interno, pero originalmente se mostraba como block
    document.getElementById(tabId).style.display = 'block';
    btn.classList.add('active');
}

function switchProfileSubTab(tab) {
    document.getElementById('subtabPersonal').style.display = tab === 'personal' ? 'block' : 'none';
    document.getElementById('subtabSecurity').style.display = tab === 'security' ? 'block' : 'none';
    
    const btnP = document.getElementById('btnTabPersonal');
    const btnS = document.getElementById('btnTabSecurity');
    
    if(tab === 'personal') {
        btnP.style.background = 'rgba(113, 255, 0, 0.05)';
        btnP.style.borderColor = 'var(--primary)';
        btnP.style.color = 'var(--primary)';
        btnS.style.background = 'transparent';
        btnS.style.borderColor = 'transparent';
        btnS.style.color = 'var(--text-muted)';
    } else {
        btnS.style.background = 'rgba(113, 255, 0, 0.05)';
        btnS.style.borderColor = 'var(--primary)';
        btnS.style.color = 'var(--primary)';
        btnP.style.background = 'transparent';
        btnP.style.borderColor = 'transparent';
        btnP.style.color = 'var(--text-muted)';
    }
}

function toggleCreateUserForm() {
    const el = document.getElementById('createUserContainer');
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function promptResetPassword(userId, username) {
    const newPwd = prompt('Ingresa la nueva contraseÃ±a para el usuario (' + username + '):');
    if (newPwd && newPwd.trim().length >= 6) {
        document.getElementById('reset_target_user_id').value = userId;
        document.getElementById('reset_new_password').value = newPwd.trim();
        document.getElementById('resetPasswordForm').submit();
    } else if (newPwd !== null) {
        alert('La contraseÃ±a debe tener al menos 6 caracteres.');
    }
}

function validatePasswordUpdate(form) {
    const current = form.current_password.value;
    const newPwd = form.new_password.value;
    const confirmPwd = form.confirm_password.value;

    if (!current || !newPwd || !confirmPwd) {
        alert('? Todos los campos son obligatorios para cambiar la contraseña.');
        return false;
    }
    
    if (newPwd.length < 6) {
        alert('? La nueva contraseña debe tener al menos 6 caracteres.');
        return false;
    }

    if (newPwd !== confirmPwd) {
        alert('? La nueva contraseña y su confirmación no coinciden.');
        return false;
    }

    return true;
}

