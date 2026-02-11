<?php
$permiso = $permiso ?? null;
$errores = $errores ?? [];
$modoEdicion = $modoEdicion ?? false;
?>

<h2><?php echo $modoEdicion ? 'Editar permiso' : 'Nuevo permiso'; ?></h2>
<p class="text-muted">Complete la clave y descripción.</p>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<form method="post" class="card p-4">
    <?php if ($modoEdicion && $permiso): ?>
        <input type="hidden" name="id" value="<?php echo (int) $permiso->id; ?>">
    <?php endif; ?>
    <div class="mb-3">
        <label class="form-label" for="clave">Clave</label>
        <input type="text" class="form-control" id="clave" name="clave" value="<?php echo htmlspecialchars($permiso->clave ?? ''); ?>" required>
        <?php if (!empty($errores['clave'])): ?>
            <div class="text-danger small mt-1"><?php echo htmlspecialchars($errores['clave']); ?></div>
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <label class="form-label" for="descripcion">Descripción</label>
        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($permiso->descripcion ?? ''); ?></textarea>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a class="btn btn-outline-secondary" href="<?php echo $baseUrl; ?>/index.php?route=permisos/list">Cancelar</a>
    </div>
</form>