<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrarse — Kayrom</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/auth.css">
</head>
<body>

<div class="auth-card wide">

    <div class="auth-logo">
        <img src="public/img/dpv.png" alt="Logo" onerror="this.style.display='none'">
    </div>

    <hr class="auth-divider">

    <h2 class="auth-title">Crear cuenta</h2>
    <p class="auth-subtitle">Ingresá tus datos para registrarte</p>

    <?php if (!empty($data['error'])): ?>
    <div class="auth-alert error"><?= htmlspecialchars($data['error']) ?></div>
    <?php endif; ?>
    <?php if (!empty($data['success'])): ?>
    <div class="auth-alert success"><?= htmlspecialchars($data['success']) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php?page=registrar_usuario&action=registrar_usuario" class="auth-form">
        <div id="toast-error" class="toast-error"></div>

        <div class="form-grid">

            <div class="field-group">
                <label>Nombre <span class="req">*</span></label>
                <input type="text" name="nombre"
                    value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>"
                    placeholder="Nombre">
            </div>

            <div class="field-group">
                <label>Apellido</label>
                <input type="text" name="apellido"
                    value="<?= htmlspecialchars($_POST['apellido'] ?? '') ?>"
                    placeholder="Apellido">
            </div>

            <div class="field-group">
                <label>DNI</label>
                <input type="text" name="dni"
                value="<?= htmlspecialchars($_POST['dni'] ?? '') ?>"
                placeholder="DNI">
            </div>

            <div class="field-group">
                <label>Teléfono</label>
                <input type="text" name="telefono_persona"
                    value="<?= htmlspecialchars($_POST['telefono_persona'] ?? '') ?>"
                    placeholder="Teléfono">
            </div>

            <div class="field-group" style="grid-column: span 2;">
                <label>Domicilio</label>
                <input type="text" name="domicilio"
                    value="<?= htmlspecialchars($_POST['domicilio'] ?? '') ?>"
                    placeholder="Dirección">
            </div>

            <div class="field-group" style="grid-column: span 2;">
                <label>Email <span class="req">*</span></label>
                <div class="field-wrap">
                    <span class="field-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </span>
                    <input type="email" name="email"
                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                        placeholder="correo@ejemplo.com"
                        autocomplete="email">
                </div>
            </div>

            <div class="field-group">
                <label>Usuario <span class="req">*</span></label>
                <div class="field-wrap">
                    <span class="field-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </span>
                    <input type="text" name="nombre_usuario"
                        value="<?= htmlspecialchars($_POST['nombre_usuario'] ?? '') ?>"
                        placeholder="nombre_usuario" >
                </div>
            </div>

            <div class="field-group">
                <label>Rol <span class="req">*</span></label>
                <?php
                    $rolActual  = $_POST['rol'] ?? 'empleado';
                    $rolesFijos = ['empleado', 'admin'];
                    $esRolFijo  = in_array($rolActual, $rolesFijos);
                ?>
                <select id="rol-select" onchange="elegirRol(this.value)">
                    <option value="empleado" <?= $rolActual === 'empleado' ? 'selected' : '' ?>>Empleado</option>
                    <option value="admin" <?= $rolActual === 'admin' ? 'selected' : '' ?>>Administrador</option>
                    <option value="otro" <?= !$esRolFijo ? 'selected' : '' ?>>Otro (escribir)…</option>
                </select>
                <input type="text" name="rol" id="rol-input"
                    value="<?= htmlspecialchars($rolActual) ?>"
                    placeholder="Escribí el rol"
                    style="margin-top:8px; <?= $esRolFijo ? 'display:none;' : '' ?>">

                <script>
                function elegirRol(v) {
                    const input = document.getElementById('rol-input');
                    if (v === 'otro') {
                        input.style.display = 'block';
                        input.value = '';
                        input.focus();
                    } else {
                        input.style.display = 'none';
                        input.value = v;
                    }
                }
                </script>
            </div>

            <div class="field-group">
                <label>Estado</label>
                <select name="activo">
                    <option value="1" <?= (($_POST['activo'] ?? '1') == '1') ? 'selected' : '' ?>>Activo</option>
                    <option value="0" <?= (($_POST['activo'] ?? '1') == '0') ? 'selected' : '' ?>>Inactivo</option>
                </select>
            </div>
            
           

            <div class="field-group">
                <label>Contraseña <span class="req">*</span></label>
                <div class="field-wrap pass-wrap">
                    <span class="field-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </span>
                    <input type="password" id="pass1" name="contrasena_usuario"
                        placeholder="Mínimo 8 caracteres" 
                        minlength="8"
                        pattern="(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}"
                        title="La contraseña debe tener al menos 8 caracteres, una letra mayúscula y un carácter especial."
                        required>
                    <button type="button" class="toggle-pass" onclick="togglePass('pass1',this)">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>

            <div class="field-group">
                <label>Confirmar contraseña <span class="req">*</span></label>
                <div class="field-wrap pass-wrap">
                    <span class="field-icon">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    </span>
                    <input type="password" id="pass2" name="contrasene_confirm"
                        placeholder="Repetí la contraseña"
                        minlength="8"
                        pattern="(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{8,}"
                        title="La contraseña debe tener al menos 8 caracteres, una letra mayúscula y un carácter especial."
                        required>
                    <button type="button" class="toggle-pass" onclick="togglePass('pass2',this)">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    </button>
                </div>
            </div>
                <div class="field-group" style="grid-column: span 2;" id="modulos-wrap">
                    <label>Módulos permitidos</label>
                    <div class="modules-grid">
                    <?php
                    // Lista de módulos que se le pueden asignar a un usuario, con su ícono
                        $modulosDisponibles = [
                            'vehiculos'   => ['Vehículos',   '<rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>'],
                            'repuestos'   => ['Repuestos',   '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>'],
                            'pedidos'     => ['Pedidos',     '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>'],
                            'proveedores' => ['Proveedores', '<path d="m11 17 2 2a1 1 0 1 0 3-3"/><path d="m14 14 2.5 2.5a1 1 0 1 0 3-3l-3.88-3.88a3 3 0 0 0-4.24 0l-.88.88a1 1 0 1 1-3-3l2.81-2.81a5.79 5.79 0 0 1 7.06-.87l.47.28a2 2 0 0 0 1.42.25L21 4"/><path d="m21 3 1 11h-2"/><path d="M3 3 2 14l6.5 6.5a1 1 0 1 0 3-3"/><path d="M3 4h8"/>'],
                            'empleados'   => ['Empleados',   '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>'],
                    ];
                    // Por si el form se recarga, recuerdo los que ya estaban marcados
                    $modulosMarcados = $_POST['modulos'] ?? [];
                    foreach ($modulosDisponibles as $key => $mod):
                        [$nombreMod, $iconoSvg] = $mod;
                        $checked = in_array($key, $modulosMarcados);
                    ?>
                    <label class="module-chip <?= $checked ? 'selected' : '' ?>" onclick="toggleModuleChip(this)">
                        <input type="checkbox" name="modulos[]" value="<?= $key ?>" <?= $checked ? 'checked' : '' ?>>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= $iconoSvg ?></svg>
                        <?= $nombreMod ?>
                        <span class="chip-check">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </span>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <script>
        function toggleModuleChip(label) {
            const checkbox = label.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            label.classList.toggle('selected', checkbox.checked);
        }
        </script>

        <button type="submit" class="auth-btn">Registrarme</button>

        <script>
        function mostrarError(mensaje) {
            const toast = document.getElementById('toast-error');
            toast.textContent = mensaje;
            setTimeout(() => { toast.textContent = ''; }, 3500);
        }

        document.querySelector('.auth-form').addEventListener('submit', function(e) {
            const nombre   = document.querySelector('input[name="nombre"]').value.trim();
            const usuario  = document.querySelector('input[name="nombre_usuario"]').value.trim();
            const email    = document.querySelector('input[name="email"]').value.trim();
            const pass     = document.querySelector('input[name="contrasena_usuario"]').value;
            const pass2    = document.querySelector('input[name="contrasene_confirm"]').value;

            if (!nombre)  { e.preventDefault(); mostrarError('Ingresá el nombre'); return; }
            if (!usuario) { e.preventDefault(); mostrarError('Ingresá el nombre de usuario'); return; }
            if (!email)   { e.preventDefault(); mostrarError('Ingresá el correo electrónico'); return; }
            if (!pass)    { e.preventDefault(); mostrarError('Ingresá la contraseña'); return; }
            if (!pass2)   { e.preventDefault(); mostrarError('Confirmá la contraseña'); return; }
            if (pass !== pass2) { e.preventDefault(); mostrarError('Las contraseñas no coinciden'); return; }
        });
        </script>

    </form>

    <p class="auth-switch">
        ¿Ya tenés cuenta?
        <a href="index.php?page=iniciar_sesion&action=iniciar_sesion">Iniciar sesión</a>
    </p>

</div>

<script>
function togglePass(id, btn) {
    const input = document.getElementById(id);
    const isPass = input.type === 'password';
    input.type = isPass ? 'text' : 'password';
    btn.innerHTML = isPass
        ? '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
        : '<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
}
</script>
</body>
</html>
