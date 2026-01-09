<div class="d-flex justify-content-between align-items-center mb-3">
    <h2><?php echo $modoEdicion ? 'Editar tipo de crédito/débito' : 'Crear tipo de crédito/débito'; ?></h2>
    <div class="d-flex gap-2">
        <a href="<?php echo $baseUrl; ?>/index.php?route=tipos-movimientos/list" class="btn btn-outline-secondary">Volver al listado</a>
        <?php if ($modoEdicion): ?>
            <a href="<?php echo $baseUrl; ?>/index.php?route=tipos-movimientos/create" class="btn btn-primary">Crear nuevo</a>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>
<?php if (!empty($errores['general'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($errores['general']); ?></div>
<?php endif; ?>

<form method="post" class="row g-3">
    <?php if ($modoEdicion && $tipoMovimiento): ?>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($tipoMovimiento->id); ?>">
    <?php endif; ?>
    <div class="col-md-6">
        <label class="form-label">Descripción *</label>
        <input type="text" name="descripcion" class="form-control" value="<?php echo htmlspecialchars($tipoMovimiento?->descripcion ?? ''); ?>" required>
        <?php if (!empty($errores['descripcion'])): ?><div class="text-danger small"><?php echo $errores['descripcion']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-3">
        <label class="form-label">Estado *</label>
        <select name="estado" class="form-select" required>
            <?php $estadoActual = $tipoMovimiento?->estado ?? 'activo'; ?>
            <option value="activo" <?php echo ($estadoActual === 'activo') ? 'selected' : ''; ?>>Activo</option>
            <option value="inactivo" <?php echo ($estadoActual === 'inactivo') ? 'selected' : ''; ?>>Inactivo</option>
        </select>
        <?php if (!empty($errores['estado'])): ?><div class="text-danger small"><?php echo $errores['estado']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-3">
        <label class="form-label d-block">Tipo *</label>
        <?php $tipoActual = $tipoMovimiento?->tipo ?? 'credito'; ?>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="tipo" id="tipoCredito" value="credito" <?php echo ($tipoActual === 'credito') ? 'checked' : ''; ?>>
            <label class="form-check-label" for="tipoCredito">Crédito</label>
        </div>
        <div class="form-check">
            <input class="form-check-input" type="radio" name="tipo" id="tipoDebito" value="debito" <?php echo ($tipoActual === 'debito') ? 'checked' : ''; ?>>
            <label class="form-check-label" for="tipoDebito">Débito</label>
        </div>
        <?php if (!empty($errores['tipo'])): ?><div class="text-danger small"><?php echo $errores['tipo']; ?></div><?php endif; ?>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-success">Guardar</button>
        <a href="<?php echo $baseUrl; ?>/index.php?route=tipos-movimientos/list" class="btn btn-link">Cancelar</a>
    </div>
</form>