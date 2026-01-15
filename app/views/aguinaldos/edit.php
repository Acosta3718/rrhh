<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Editar aguinaldo</h2>
    <div class="d-flex gap-2">
        <a href="<?php echo $baseUrl; ?>/index.php?route=aguinaldos/list" class="btn btn-outline-secondary">Volver al listado</a>
        <a href="<?php echo $baseUrl; ?>/index.php?route=aguinaldos/create" class="btn btn-primary">Generar nuevo</a>
    </div>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<form method="post" class="row g-3">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($aguinaldo->id); ?>">
    <div class="col-md-6">
        <label class="form-label">Funcionario</label>
        <input type="text" class="form-control" value="<?php echo htmlspecialchars($funcionario?->nombre ?? ''); ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label">Empresa</label>
        <input type="text" class="form-control" value="<?php echo htmlspecialchars($funcionario?->empresaNombre ?? ''); ?>" readonly>
    </div>
    <div class="col-md-4">
        <label class="form-label">Año *</label>
        <input type="number" name="anio" class="form-control" value="<?php echo htmlspecialchars($aguinaldo->anio); ?>" max="<?php echo date('Y'); ?>" required>
        <?php if (!empty($errores['anio'])): ?><div class="text-danger small"><?php echo $errores['anio']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label">Monto *</label>
        <input type="number" name="monto" class="form-control" min="0" step="0.01" value="<?php echo htmlspecialchars($aguinaldo->monto); ?>" required>
        <?php if (!empty($errores['monto'])): ?><div class="text-danger small"><?php echo $errores['monto']; ?></div><?php endif; ?>
        <?php if (!empty($errores['periodo'])): ?><div class="text-danger small"><?php echo $errores['periodo']; ?></div><?php endif; ?>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-success">Guardar cambios</button>
        <a href="<?php echo $baseUrl; ?>/index.php?route=aguinaldos/list" class="btn btn-link">Cancelar</a>
    </div>
</form>