<?php require_once 'views/encabezado.php'; ?>

<?php
$e = $data['empleado'] ?? null;
$isEdit = !is_null($e);
$action = $isEdit
    ? "index.php?page=empleados&action=edit&id={$e['id']}"
    : "index.php?page=empleados&action=create";
?>

<div class="page-header">
    <h1><?= $isEdit ? 'Editar Empleado' : 'Nuevo Empleado' ?></h1>
    <a href="index.php?page=empleados" class="btn btn-ghost">← Volver</a>
</div>

<div class="form-card">
    <form method="POST" action="<?= $action ?>">
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
        <input type="email" name="email" value="<?= htmlspecialchars($e['email'] ?? '') ?>">
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
        <select name="rol">
            <option value="empleado" <?= ($e['rol'] ?? '') == 'empleado' ? 'selected' : '' ?>>Empleado</option>
            <option value="admin" <?= ($e['rol'] ?? '') == 'admin' ? 'selected' : '' ?>>Administrador</option>
            <option value="encargado_repuesto" <?= ($e['rol'] ?? '') == 'encargado_repuesto' ? 'selected' : '' ?>>Encargado de Repuesto</option>
        </select>
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
        <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:10px; padding:14px; background:var(--bg-dark); border:1px solid var(--border); border-radius:8px;">
            <?php
            // Traigo los módulos que el usuario tiene permitidos actualmente
            $permisoModel = new PermisoModel();
            $permisosActuales = $permisoModel->getByUsuario($e['id']);

            // Lista de módulos disponibles para asignar
            $modulosDisponibles = [
                'vehiculos'   => 'Vehículos',
                'repuestos'   => 'Repuestos',
                'pedidos'     => 'Pedidos',
                'proveedores' => 'Proveedores',
                'empleados'   => 'Empleados',
            ];

            // Genero un checkbox por cada módulo, marcando los que ya tiene
            foreach ($modulosDisponibles as $key => $nombre):
                $checked = in_array($key, $permisosActuales) ? 'checked' : '';
            ?>
            <label style="display:flex; align-items:center; gap:8px; cursor:pointer; color:var(--text-primary);">
                <input type="checkbox" name="modulos[]" value="<?= $key ?>" <?= $checked ?>>
                <?= $nombre ?>
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

<button type="submit" class="btn btn-green"><?= $isEdit ? 'Actualizar' : 'Guardar' ?></button>

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
    });
    </script>

    </form>
</div>

<?php require_once 'views/pie_pagina.php'; ?>