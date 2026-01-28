<h2><?php echo $modoEdicion ? 'Editar feriado' : 'Crear feriado'; ?></h2>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>
<?php if (!empty($errores)): ?>
    <div class="alert alert-danger">Revise los datos obligatorios.</div>
<?php endif; ?>

<form method="post" class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="form-label">Descripción</label>
        <input type="text" name="descripcion" class="form-control" value="<?php echo htmlspecialchars($feriado->descripcion ?? ''); ?>" required>
        <?php if (!empty($errores['descripcion'])): ?><div class="text-danger small"><?php echo $errores['descripcion']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label">Fecha</label>
        <input type="date" name="fecha" class="form-control" value="<?php echo htmlspecialchars($feriado->fecha?->format('Y-m-d') ?? ''); ?>" required>
        <?php if (!empty($errores['fecha'])): ?><div class="text-danger small"><?php echo $errores['fecha']; ?></div><?php endif; ?>
    </div>
    <?php if ($modoEdicion): ?>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($feriado->id); ?>">
    <?php endif; ?>
    <div class="col-12">
        <button class="btn btn-success" type="submit"><?php echo $modoEdicion ? 'Guardar cambios' : 'Crear feriado'; ?></button>
        <a href="<?php echo $baseUrl; ?>/index.php?route=feriados/list" class="btn btn-secondary">Volver al listado</a>
    </div>
    <?php if (!empty($errores['general'])): ?>
        <div class="col-12"><div class="alert alert-danger"><?php echo $errores['general']; ?></div></div>
    <?php endif; ?>
</form>