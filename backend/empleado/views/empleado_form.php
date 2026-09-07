<?php
$modoModal = $data['modal'] ?? false;
require_once $modoModal ? 'compartidoCREO/views/encabezado_modal.php' : 'compartidoCREO/views/encabezado.php';
?>

<?php
$e = $data['empleado'] ?? null;
$isEdit = !is_null($e);
$action = $isEdit
    ? "index.php?page=empleados&action=edit&id={$e['id']}" . ($modoModal ? '&modal=1' : '')
    : "index.php?page=empleados&action=create" . ($modoModal ? '&modal=1' : '');
?>

<div class="page-header">
    <h1><?= $isEdit ? 'Editar Empleado' : 'Nuevo Empleado' ?></h1>
    <?php if (!$modoModal): ?>
    <a href="index.php?page=empleados" class="btn btn-ghost">← Volver</a>
    <?php endif; ?>
</div>

<div class="form-card" style="<?= $modoModal ? 'max-width:none;' : '' ?>">
    <form method="POST" action="<?= $action ?>">
        <?php if ($modoModal): ?><input type="hidden" name="modal" value="1"><?php endif; ?>
        <div id="toast-error" class="toast-error"></div>

        <?php if (!empty($data['error'])): ?>
        <div class="auth-alert error"><?= $data['error'] ?></div>
        <?php endif; ?>

<div class="form-row">
    <div class="form-group">
        <label>Nombre</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($e['nombre'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label>Apellido</label>
        <input type="text" name="apellido" value="<?= htmlspecialchars($e['apellido'] ?? '') ?>">
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label>Email</label>
        <input type="text" name="email" value="<?= htmlspecialchars($e['email'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label>Usuario</label>
        <input type="text" name="nombre_usuario" value="<?= htmlspecialchars($e['nombre_usuario'] ?? '') ?>">
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label>DNI</label>
        <input type="text" name="dni" value="<?= htmlspecialchars($e['dni'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label>Teléfono</label>
        <input type="text" name="telefono_persona" value="<?= htmlspecialchars($e['telefono_persona'] ?? '') ?>">
    </div>
</div>

<div class="form-row">
    <div class="form-group">
        <label>Domicilio</label>
        <input type="text" name="domicilio" value="<?= htmlspecialchars($e['domicilio'] ?? '') ?>">
    </div>
    <div class="form-group">
        <label>Rol</label>
        <?php
            $rolActual  = $e['rol'] ?? 'empleado';
            $rolesFijos = ['empleado', 'admin'];
            $esRolFijo  = in_array($rolActual, $rolesFijos);
        ?>
        <select id="rol-select" onchange="elegirRol(this.value)">
            <option value="empleado" <?= $rolActual === 'empleado' ? 'selected' : '' ?>>Empleado</option>
            <option value="admin" <?= $rolActual === 'admin' ? 'selected' : '' ?>>Administrador</option>
            <option value="__otro__" <?= !$esRolFijo ? 'selected' : '' ?>>Otro (escribir)…</option>
        </select>
        <input type="text" name="rol" id="rol-input"
            value="<?= htmlspecialchars($rolActual) ?>"
            placeholder="Escribí el rol"
            style="margin-top:8px; <?= $esRolFijo ? 'display:none;' : '' ?>">

        <script>
        function elegirRol(v) {
            const input = document.getElementById('rol-input');
            if (v === '__otro__') {
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
</div>

<div class="form-row">
    <div class="form-group">
        <label>Estado</label>
        <select name="activo">
            <option value="1" <?= ($e['activo'] ?? 1) == 1 ? 'selected' : '' ?>>Activo</option>
            <option value="0" <?= ($e['activo'] ?? 1) == 0 ? 'selected' : '' ?>>Inactivo</option>
        </select>
    </div>
    <?php if ($isEdit && ($e['rol'] ?? '') !== 'admin'): ?>
    <div class="form-group" style="grid-column: span 2;">
        <label>Módulos permitidos</label>
        <div class="modules-grid">
            <?php
            // Traigo los módulos que el usuario tiene permitidos actualmente
            $permisoModel = new PermisoModel();
            $permisosActuales = $permisoModel->getByUsuario($e['id']);

            // Lista de módulos disponibles para asignar, con su ícono
            $modulosDisponibles = [
                'vehiculos'   => ['Vehículos',   '<rect x="1" y="3" width="15" height="13" rx="2"/><path d="M16 8h4l3 5v3h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>'],
                'repuestos'   => ['Repuestos',   '<path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/>'],
                'pedidos'     => ['Pedidos',     '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>'],
                'proveedores' => ['Proveedores', '<path d="m11 17 2 2a1 1 0 1 0 3-3"/><path d="m14 14 2.5 2.5a1 1 0 1 0 3-3l-3.88-3.88a3 3 0 0 0-4.24 0l-.88.88a1 1 0 1 1-3-3l2.81-2.81a5.79 5.79 0 0 1 7.06-.87l.47.28a2 2 0 0 0 1.42.25L21 4"/><path d="m21 3 1 11h-2"/><path d="M3 3 2 14l6.5 6.5a1 1 0 1 0 3-3"/><path d="M3 4h8"/>'],
                'empleados'   => ['Empleados',   '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>'],
            ];

            // Genero una tarjeta clickeable por cada módulo, marcando los que ya tiene
            foreach ($modulosDisponibles as $key => $mod):
                [$nombre, $iconoSvg] = $mod;
                $checked = in_array($key, $permisosActuales);
            ?>
            <label class="module-chip <?= $checked ? 'selected' : '' ?>" onclick="toggleModuleChip(this)">
                <input type="checkbox" name="modulos[]" value="<?= $key ?>" <?= $checked ? 'checked' : '' ?>>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><?= $iconoSvg ?></svg>
                <?= $nombre ?>
                <span class="chip-check">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
            </label>
            <?php endforeach; ?>
        </div>
    </div>
    <?php elseif ($isEdit && ($e['rol'] ?? '') === 'admin'): ?>
    <!-- Si es admin, no muestro checkboxes, solo el mensaje -->
    <div class="form-group" style="grid-column: span 2;">
        <label>Módulos permitidos</label>
        <div style="padding:14px; background:var(--bg-dark); border:1px solid var(--border); border-radius:8px; color:var(--text-muted); font-size:13px;">
            El administrador tiene acceso a todos los módulos automáticamente.
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function toggleModuleChip(label) {
    const checkbox = label.querySelector('input[type="checkbox"]');
    checkbox.checked = !checkbox.checked;
    label.classList.toggle('selected', checkbox.checked);
}
</script>

<button type="submit" class="btn btn-primary"><?= $isEdit ? 'Actualizar' : 'Guardar' ?></button>

    <script>
    function mostrarError(mensaje) {
        const toast = document.getElementById('toast-error');
        toast.textContent = mensaje;
        setTimeout(() => { toast.textContent = ''; }, 3500);
    }

    document.querySelector('form').addEventListener('submit', function(e) {
        const inputs = document.querySelectorAll('form input[name], form select[name]');
        for (const input of inputs) {
            if (!input.value.trim()) {
                e.preventDefault();
                const label = input.closest('.form-group')?.querySelector('label')?.textContent.replace('*', '').trim() || 'Este campo';
                mostrarError('Completá: ' + label);
                return;
            }
        }

        const email = document.querySelector('input[name="email"]').value.trim();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            e.preventDefault();
            mostrarError('Ingresá un email válido');
            return;
        }
    });
    </script>

    </form>
</div>

<?php require_once $modoModal ? 'compartidoCREO/views/pie_pagina_modal.php' : 'compartidoCREO/views/pie_pagina.php'; ?>