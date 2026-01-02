<h2><?php echo $modoEdicion ? 'Editar nacionalidad' : 'Crear nacionalidad'; ?></h2>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>
<?php if (!empty($errores)): ?>
    <div class="alert alert-danger">Revise los datos obligatorios.</div>
<?php endif; ?>

<form method="post" class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($nacionalidad->nombre ?? ''); ?>" required>
        <?php if (!empty($errores['nombre'])): ?><div class="text-danger small"><?php echo $errores['nombre']; ?></div><?php endif; ?>
    </div>
    <?php if ($modoEdicion): ?>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($nacionalidad->id); ?>">
    <?php endif; ?>
    <div class="col-12">
        <button class="btn btn-success" type="submit"><?php echo $modoEdicion ? 'Guardar cambios' : 'Crear nacionalidad'; ?></button>
        <a href="<?php echo $baseUrl; ?>/index.php?route=nacionalidades/list" class="btn btn-secondary">Volver al listado</a>
    </div>
    <?php if (!empty($errores['general'])): ?>
        <div class="col-12"><div class="alert alert-danger"><?php echo $errores['general']; ?></div></div>
    <?php endif; ?>
</form>