<?php
$usuario = $usuario ?? null;
$errores = $errores ?? [];
$roles = $roles ?? [];
$rolesSeleccionados = $rolesSeleccionados ?? [];
$modoEdicion = $modoEdicion ?? false;
?>

<h2><?php echo $modoEdicion ? 'Editar usuario' : 'Nuevo usuario'; ?></h2>
<p class="text-muted">Complete los datos y asigne roles.</p>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<form method="post" class="card p-4">
    <?php if ($modoEdicion && $usuario): ?>
        <input type="hidden" name="id" value="<?php echo (int) $usuario->id; ?>">
    <?php endif; ?>
    <div class="mb-3">
        <label class="form-label" for="nombre">Nombre</label>
        <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario->nombre ?? ''); ?>" required>
        <?php if (!empty($errores['nombre'])): ?>
            <div class="text-danger small mt-1"><?php echo htmlspecialchars($errores['nombre']); ?></div>
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <label class="form-label" for="correo">Correo</label>
        <input type="email" class="form-control" id="correo" name="correo" value="<?php echo htmlspecialchars($usuario->correo ?? ''); ?>" required>
        <?php if (!empty($errores['correo'])): ?>
            <div class="text-danger small mt-1"><?php echo htmlspecialchars($errores['correo']); ?></div>
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <label class="form-label" for="password">Contraseña <?php echo $modoEdicion ? '(opcional)' : ''; ?></label>
        <input type="password" class="form-control" id="password" name="password" <?php echo $modoEdicion ? '' : 'required'; ?>>
        <?php if (!empty($errores['password'])): ?>
            <div class="text-danger small mt-1"><?php echo htmlspecialchars($errores['password']); ?></div>
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <label class="form-label" for="roles">Roles</label>
        <select class="form-select" id="roles" name="roles[]" multiple size="5">
            <?php foreach ($roles as $rol): ?>
                <?php $seleccionado = in_array((int) $rol->id, $rolesSeleccionados, true); ?>
                <option value="<?php echo (int) $rol->id; ?>" <?php echo $seleccionado ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($rol->nombre); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <div class="form-text">Use Ctrl/Cmd para seleccionar varios.</div>
    </div>
    <div class="form-check mb-3">
        <input class="form-check-input" type="checkbox" name="activo" id="activo" <?php echo ($usuario?->activo ?? true) ? 'checked' : ''; ?>>
        <label class="form-check-label" for="activo">Activo</label>
    </div>
    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Guardar</button>
        <a class="btn btn-outline-secondary" href="<?php echo $baseUrl; ?>/index.php?route=usuarios/list">Cancelar</a>
    </div>
</form>