<h2><?php echo $modoEdicion ? 'Editar empresa' : 'Registro de Empresas'; ?></h2>
<p class="text-muted">Las empresas se guardan en MySQL usando el modelo <code>Empresa</code>. Complete el formulario para crear o actualizar registros.</p>
<?php if ($mensaje): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>
<?php if (!empty($errores['general'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($errores['general']); ?></div>
<?php endif; ?>
<?php if (!empty($errores) && empty($errores['general'])): ?>
    <div class="alert alert-danger">Revise los datos obligatorios.</div>
<?php endif; ?>
<form method="post" class="row g-3 mb-4" action="<?php echo $baseUrl; ?>/index.php?route=<?php echo $modoEdicion ? 'empresas/edit&id=' . (int) $empresa->id : 'empresas/create'; ?>">
    <?php if ($modoEdicion): ?>
        <input type="hidden" name="id" value="<?php echo (int) $empresa->id; ?>">
    <?php endif; ?>
    <div class="col-md-6">
        <label class="form-label">Razón social</label>
        <input type="text" name="razon_social" class="form-control" required value="<?php echo htmlspecialchars($empresa->razonSocial ?? ''); ?>">
        <?php if (!empty($errores['razon_social'])): ?><div class="text-danger small"><?php echo $errores['razon_social']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-6">
        <label class="form-label">RUC</label>
        <input type="text" name="ruc" class="form-control" required value="<?php echo htmlspecialchars($empresa->ruc ?? ''); ?>">
        <?php if (!empty($errores['ruc'])): ?><div class="text-danger small"><?php echo $errores['ruc']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-6">
        <label class="form-label">Correo</label>
        <input type="email" name="correo" class="form-control" required value="<?php echo htmlspecialchars($empresa->correo ?? ''); ?>">
        <?php if (!empty($errores['correo'])): ?><div class="text-danger small"><?php echo $errores['correo']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-6">
        <label class="form-label">Teléfono</label>
        <input type="text" name="telefono" class="form-control" value="<?php echo htmlspecialchars($empresa->telefono ?? ''); ?>">
    </div>
    <div class="col-md-12">
        <label class="form-label">Dirección</label>
        <input type="text" name="direccion" class="form-control" required value="<?php echo htmlspecialchars($empresa->direccion ?? ''); ?>">
        <?php if (!empty($errores['direccion'])): ?><div class="text-danger small"><?php echo $errores['direccion']; ?></div><?php endif; ?>
    </div>
    <div class="col-12">
        <button class="btn btn-success" type="submit"><?php echo $modoEdicion ? 'Actualizar' : 'Guardar'; ?></button>
        <?php if ($modoEdicion): ?>
            <a href="<?php echo $baseUrl; ?>/index.php?route=empresas/create" class="btn btn-secondary ms-2">Cancelar</a>
        <?php endif; ?>
    </div>
</form>