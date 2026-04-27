<?php
require_once 'views/encabezado.php';
?>

<div class="page-header">
    <h1>＋ Nuevo Movimiento</h1>
    <a href="index.php?page=movimientos" class="btn btn-ghost">← Volver</a>
</div>

<div class="form-card">
    <form method="POST" action="index.php?page=movimientos&action=create">
        <div class="form-row">
            <div class="form-group">
                <label>Tipo de Movimiento</label>
                <select name="tipo" required>
                    <option value="Entrada">📥 Entrada</option>
                    <option value="Salida">📤 Salida</option>
                    <option value="Ajuste">🔁 Ajuste</option>
                </select>
            </div>
            <div class="form-group">
                <label>Cantidad</label>
                <input type="number" name="cantidad" value="0" min="0" required>
            </div>
        </div>
        <div class="form-group">
            <label>Descripción</label>
            <input type="text" name="descripcion" required placeholder="Ej: Entrada 10 Filtros de Aceite...">
        </div>
        <button type="submit" class="btn btn-primary">✅ Registrar Movimiento</button>
    </form>
</div>

<?php require_once 'views/pie_pagina.php'; ?>
