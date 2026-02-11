<?php
$rol = $rol ?? null;
$errores = $errores ?? [];
$permisos = $permisos ?? [];
$permisosSeleccionados = $permisosSeleccionados ?? [];
$modoEdicion = $modoEdicion ?? false;
?>

<h2><?php echo $modoEdicion ? 'Editar rol' : 'Nuevo rol'; ?></h2>
<p class="text-muted">Defina nombre y permisos asociados.</p>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<form method="post" class="card p-4">
    <?php if ($modoEdicion && $rol): ?>
        <input type="hidden" name="id" value="<?php echo (int) $rol->id; ?>">
    <?php endif; ?>
    <div class="mb-3">
        <label class="form-label" for="nombre">Nombre</label>
        <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($rol->nombre ?? ''); ?>" required>
        <?php if (!empty($errores['nombre'])): ?>
            <div class="text-danger small mt-1"><?php echo htmlspecialchars($errores['nombre']); ?></div>
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <label class="form-label" for="descripcion">Descripción</label>
        <textarea class="form-control" id="descripcion" name="descripcion" rows="3"><?php echo htmlspecialchars($rol->descripcion ?? ''); ?></textarea>
    </div>
    <div class="mb-3">
        <label class="form-label" for="permisos">Permisos</label>
        <select class="form-select" id="permisos" name="permisos[]" multiple size="6">
            <?php foreach ($permisos as $permiso): ?>
                <?php $seleccionado = in_array((int) $permiso->id, $permisosSeleccionados, true); ?>
                <option value="<?php echo (int) $permiso->id; ?>" <?php echo $seleccionado ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($permiso->clave); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="form-text">Use Ctrl/Cmd para seleccionar varios.</div>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a class="btn btn-outline-secondary" href="<?php echo $baseUrl; ?>/index.php?route=roles/list">Cancelar</a>
    </div>
</form>