<?php
$pageTitle = 'Menú Principal';
$activeMenu = 'opciones';
require __DIR__ . '/layout/header.php';

$userData = $currentUserData ?? [
    'name' => $_SESSION['name'] ?? '',
    'first_name' => $_SESSION['first_name'] ?? '',
    'last_name' => $_SESSION['last_name'] ?? '',
    'role' => $_SESSION['role'] ?? 'visualizador'
];

$role = $userData['role'] ?? 'visualizador';
$canManageUsers = ($role === 'master' || $role === 'instructor');
?>

<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h1 style="margin: 0; font-size: 1.75rem;">Menú Principal & Perfil</h1>
            <p style="color: var(--text-muted); margin: 0.25rem 0 0 0;">Gestión de cuenta y administración de accesos del sistema.</p>
        </div>
        <div>
            <a href="/logout" class="btn btn-danger" style="display: inline-flex; align-items: center; gap: 0.5rem; text-decoration: none;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                    <polyline points="16 17 21 12 16 7"></polyline>
                    <line x1="21" y1="12" x2="9" y2="12"></line>
                </svg>
                Cerrar Sesión
            </a>
        </div>
    </div>

    <!-- Mensajes de Alerta -->
    <?php if (!empty($successMessage)): ?>
        <div class="alert alert-success" style="margin-top: 1.5rem;">
            ✓ <?= htmlspecialchars($successMessage) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($errorMessage)): ?>
        <div class="alert alert-error" style="margin-top: 1.5rem;">
            ⚠ <?= htmlspecialchars($errorMessage) ?>
        </div>
    <?php endif; ?>

    <!-- Naves de Pestañas (Tabs) -->
    <div class="tab-header" style="margin-top: 2rem;">
        <button type="button" class="tab-btn active" onclick="switchTab('profileTab', this)">Mi Perfil</button>
        <?php if ($canManageUsers): ?>
            <button type="button" class="tab-btn" onclick="switchTab('usersTab', this)">Gestión de Usuarios (<?= count($allUsersList ?? []) ?>)</button>
            <button type="button" class="tab-btn" onclick="switchTab('settingsTab', this)">Límites de Reacción</button>
        <?php endif; ?>
    </div>

    <!-- PESTAÑA 1: MI PERFIL -->
    <div id="profileTab" class="tab-content">
        <!-- Banner de Perfil con Avatar de Iniciales -->
        <div style="display: flex; align-items: center; gap: 1.5rem; background: rgba(15, 23, 42, 0.5); border: 1px solid var(--border); padding: 1.25rem 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
            <div class="avatar-large">
                <?= htmlspecialchars($initials) ?>
            </div>
            <div>
                <h2 style="margin: 0; font-size: 1.35rem; color: var(--text-main);">
                    <?= htmlspecialchars(trim(($userData['first_name'] ?? '') . ' ' . ($userData['last_name'] ?? '')) ?: $userData['name']) ?>
                </h2>
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.35rem;">
                    <span style="color: var(--text-muted); font-size: 0.875rem;">Usuario: <code><?= htmlspecialchars($userData['name'] ?? '') ?></code></span>
                    <span class="badge badge-<?= htmlspecialchars($role) ?>">
                        ★ <?= htmlspecialchars(strtoupper($role)) ?>
                    </span>
                </div>
            </div>
        </div>

        <form method="POST" action="/opciones">
            <input type="hidden" name="action" value="update_profile" />
            
            <h3 style="margin-top: 0; color: var(--text-main); font-size: 1.2rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
                Información Personal
            </h3>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="first_name">Nombre</label>
                    <input type="text" id="first_name" name="first_name" value="<?= htmlspecialchars($userData['first_name'] ?? '') ?>" required />
                </div>
                <div class="form-group">
                    <label for="last_name">Apellido</label>
                    <input type="text" id="last_name" name="last_name" value="<?= htmlspecialchars($userData['last_name'] ?? '') ?>" />
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="username_readonly">Usuario (Lectura Única)</label>
                    <input type="text" id="username_readonly" value="<?= htmlspecialchars($userData['name'] ?? '') ?>" readonly style="opacity: 0.7; cursor: not-allowed; background: rgba(0,0,0,0.3);" />
                </div>
                <div class="form-group">
                    <label>Tipo de Usuario / Rol</label>
                    <div style="padding-top: 0.5rem;">
                        <span class="badge badge-<?= htmlspecialchars($role) ?>">
                            ★ <?= htmlspecialchars(strtoupper($role)) ?>
                        </span>
                    </div>
                </div>
            </div>

            <h3 style="margin-top: 2rem; color: var(--text-main); font-size: 1.2rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
                Seguridad & Contraseña
            </h3>
            <p style="color: var(--text-muted); font-size: 0.875rem;">Completa los campos solo si deseas cambiar tu contraseña actual.</p>

            <div class="form-group">
                <label for="current_password">Contraseña Actual</label>
                <input type="password" id="current_password" name="current_password" placeholder="Tu contraseña actual para confirmar cambios" />
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="new_password">Nueva Contraseña</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Mínimo 6 caracteres" />
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmar Nueva Contraseña</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Repite la nueva contraseña" />
                </div>
            </div>

            <div style="margin-top: 1.5rem; text-align: right;">
                <button type="submit" class="btn">Guardar Cambios de Perfil</button>
            </div>
        </form>
    </div>

    <!-- PESTAÑA 2: GESTIÓN DE USUARIOS (Instructores & Master) -->
    <?php if ($canManageUsers): ?>
    <div id="usersTab" class="tab-content" style="display: none;">
        
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <div>
                <h3 style="margin: 0; font-size: 1.2rem;">Lista de Usuarios Registrados</h3>
                <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0.25rem 0 0 0;">
                    Administra roles, accesos y contraseñas. El rol <b>master</b> no puede ser asignado.
                </p>
            </div>
            <button type="button" class="btn btn-sm" onclick="toggleCreateUserForm()">+ Crear Nuevo Usuario</button>
        </div>

        <!-- FORMULARIO NUEVO USUARIO (Desplegable) -->
        <div id="createUserContainer" style="display: none; background: rgba(15, 23, 42, 0.6); border: 1px solid var(--border); padding: 1.5rem; border-radius: 12px; margin-bottom: 2rem;">
            <h4 style="margin-top: 0; color: var(--primary);">Registrar Nuevo Usuario</h4>
            <form method="POST" action="/opciones">
                <input type="hidden" name="action" value="create_user" />
                <div class="form-row">
                    <div class="form-group">
                        <label for="new_username">Usuario / Login</label>
                        <input type="text" id="new_username" name="username" placeholder="ej. jperez" required />
                    </div>
                    <div class="form-group">
                        <label for="new_fname">Nombre</label>
                        <input type="text" id="new_fname" name="first_name" placeholder="Juan" required />
                    </div>
                    <div class="form-group">
                        <label for="new_lname">Apellido</label>
                        <input type="text" id="new_lname" name="last_name" placeholder="Pérez" />
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="new_user_pwd">Contraseña</label>
                        <input type="password" id="new_user_pwd" name="password" placeholder="Mínimo 6 caracteres" required />
                    </div>
                    <div class="form-group">
                        <label for="new_role">Tipo de Usuario / Rol</label>
                        <select id="new_role" name="role" required>
                            <option value="visualizador">Visualizador (Solo Lectura)</option>
                            <option value="instructor">Instructor (Administrador de pruebas)</option>
                        </select>
                        <small style="color: var(--text-muted); display: block; margin-top: 0.25rem;">El rol Master no está permitido.</small>
                    </div>
                </div>
                <div style="display: flex; gap: 1rem; justify-content: flex-end;">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="toggleCreateUserForm()">Cancelar</button>
                    <button type="submit" class="btn btn-sm">Crear Usuario</button>
                </div>
            </form>
        </div>

        <!-- TABLA DE USUARIOS -->
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre Completo</th>
                    <th>Usuario</th>
                    <th>Rol / Permiso</th>
                    <th>Estado (`is_active`)</th>
                    <th style="text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach (($allUsersList ?? []) as $u): ?>
                    <?php 
                        $isMaster = ($u['role'] === 'master'); 
                        $isSelf = ($u['id'] == $_SESSION['user_id']);
                        $isActive = !empty($u['is_active']);
                    ?>
                    <tr>
                        <td>#<?= htmlspecialchars($u['id']) ?></td>
                        <td>
                            <strong><?= htmlspecialchars(trim(($u['first_name'] ?? '') . ' ' . ($u['last_name'] ?? '')) ?: $u['name']) ?></strong>
                            <?php if ($isSelf): ?>
                                <span style="font-size: 0.75rem; color: var(--primary); margin-left: 0.5rem;">(Tú)</span>
                            <?php endif; ?>
                        </td>
                        <td><code><?= htmlspecialchars($u['name']) ?></code></td>
                        <td>
                            <?php if ($isMaster): ?>
                                <span class="badge badge-master">★ MASTER</span>
                            <?php else: ?>
                                <form method="POST" action="/opciones" style="display: inline-block;">
                                    <input type="hidden" name="action" value="update_role" />
                                    <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>" />
                                    <select name="new_role" onchange="this.form.submit()" style="padding: 0.25rem 0.5rem; font-size: 0.85rem; border-radius: 6px;">
                                        <option value="instructor" <?= $u['role'] === 'instructor' ? 'selected' : '' ?>>Instructor</option>
                                        <option value="visualizador" <?= $u['role'] === 'visualizador' ? 'selected' : '' ?>>Visualizador</option>
                                    </select>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($isActive): ?>
                                <span class="badge badge-active">Activo (True)</span>
                            <?php else: ?>
                                <span class="badge badge-blocked">Bloqueado (False)</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                <!-- Botón Bloquear / Desbloquear -->
                                <?php if (!$isMaster && !$isSelf): ?>
                                    <form method="POST" action="/opciones" style="display: inline;">
                                        <input type="hidden" name="action" value="toggle_status" />
                                        <input type="hidden" name="target_user_id" value="<?= $u['id'] ?>" />
                                        <?php if ($isActive): ?>
                                            <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('¿Deseas bloquear el acceso a este usuario?')">Bloquear</button>
                                        <?php else: ?>
                                            <button type="submit" class="btn btn-success btn-sm">Desbloquear</button>
                                        <?php endif; ?>
                                    </form>
                                <?php endif; ?>

                                <!-- Botón Reset Contraseña -->
                                <?php if (!$isMaster || $role === 'master'): ?>
                                    <button type="button" class="btn btn-secondary btn-sm" onclick="promptResetPassword(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name']) ?>')">Clave</button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Form Oculto para Resetear Contraseña -->
        <form id="resetPasswordForm" method="POST" action="/opciones" style="display: none;">
            <input type="hidden" name="action" value="reset_password" />
            <input type="hidden" id="reset_target_user_id" name="target_user_id" value="" />
            <input type="hidden" id="reset_new_password" name="new_password" value="" />
        </form>

    </div>
    <?php endif; ?>

    <!-- PESTAÑA 3: LÍMITES DE REACCIÓN -->
    <?php if ($canManageUsers): ?>
    <div id="settingsTab" class="tab-content" style="display: none;">
        <h3 style="margin-top: 0; color: var(--text-main); font-size: 1.2rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem;">
            Configuración de Umbrales de Reacción (0 a 8.000 ms)
        </h3>
        <p style="color: var(--text-muted); font-size: 0.875rem;">
            Define los valores en milisegundos para clasificar el desempeño. El límite máximo de tiempo de espera (timeout) está fijado en 8.000 ms (8 segundos). El límite de acierto y el límite de fallo no pueden ser iguales.
        </p>

        <form method="POST" action="/opciones" style="max-width: 550px; margin-top: 1.5rem;">
            <input type="hidden" name="action" value="update_settings" />

            <div class="form-group">
                <label for="fast_threshold_ms">Límite de Acierto / Rápido (&lt; ms)</label>
                <input type="number" id="fast_threshold_ms" name="fast_threshold_ms" value="<?= (int)($systemSettings['fast_threshold_ms'] ?? 300) ?>" min="0" max="8000" required />
                <small style="color: var(--text-muted);">Reacciones por debajo de este valor son rápidas (ej. 300 ms).</small>
            </div>

            <div class="form-group">
                <label for="slow_threshold_ms">Límite de Fallo / Lento (&gt; ms)</label>
                <input type="number" id="slow_threshold_ms" name="slow_threshold_ms" value="<?= (int)($systemSettings['slow_threshold_ms'] ?? 450) ?>" min="0" max="8000" required />
                <small style="color: var(--text-muted);">Reacciones por encima de este valor se consideran lentas. Debe ser mayor al límite de acierto.</small>
            </div>

            <div class="form-group">
                <label>Límite Superior (Timeout del Sistema)</label>
                <input type="text" value="8.000 ms (8,00 seg)" readonly style="opacity: 0.7; cursor: not-allowed; background: rgba(0,0,0,0.3);" />
            </div>

            <div style="margin-top: 1.5rem;">
                <button type="submit" class="btn">Guardar Umbrales de Reacción</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

</div>

<script>
function switchTab(tabId, btn) {
    document.querySelectorAll('.tab-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
    document.getElementById(tabId).style.display = 'block';
    btn.classList.add('active');
}

function toggleCreateUserForm() {
    const el = document.getElementById('createUserContainer');
    el.style.display = el.style.display === 'none' ? 'block' : 'none';
}

function promptResetPassword(userId, username) {
    const newPwd = prompt('Ingresa la nueva contraseña para el usuario (' + username + '):');
    if (newPwd && newPwd.trim().length >= 6) {
        document.getElementById('reset_target_user_id').value = userId;
        document.getElementById('reset_new_password').value = newPwd.trim();
        document.getElementById('resetPasswordForm').submit();
    } else if (newPwd !== null) {
        alert('La contraseña debe tener al menos 6 caracteres.');
    }
}
</script>

<?php require __DIR__ . '/layout/footer.php'; ?>
