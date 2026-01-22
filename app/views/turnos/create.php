<h2><?php echo $modoEdicion ? 'Editar turno' : 'Crear turno'; ?></h2>
<p class="text-muted">Defina el período y los horarios del turno. Los horarios se usarán para calcular tardanzas y horas extras.</p>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>
<?php if (!empty($errores)): ?>
    <div class="alert alert-danger">Revise los datos obligatorios.</div>
<?php endif; ?>

<form method="post" class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($turno->nombre ?? ''); ?>" required>
        <?php if (!empty($errores['nombre'])): ?><div class="text-danger small"><?php echo $errores['nombre']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-3">
        <label class="form-label">Fecha inicio</label>
        <input type="date" name="fecha_inicio" class="form-control" value="<?php echo htmlspecialchars($turno?->fechaInicio?->format('Y-m-d') ?? ''); ?>" required>
        <?php if (!empty($errores['fecha_inicio'])): ?><div class="text-danger small"><?php echo $errores['fecha_inicio']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-3">
        <label class="form-label">Fecha fin</label>
        <input type="date" name="fecha_fin" class="form-control" value="<?php echo htmlspecialchars($turno?->fechaFin?->format('Y-m-d') ?? ''); ?>" required>
        <?php if (!empty($errores['fecha_fin'])): ?><div class="text-danger small"><?php echo $errores['fecha_fin']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-3">
        <label class="form-label">Hora entrada</label>
        <input type="time" name="hora_entrada" class="form-control" value="<?php echo htmlspecialchars($turno->horaEntrada ?? ''); ?>" required>
        <?php if (!empty($errores['hora_entrada'])): ?><div class="text-danger small"><?php echo $errores['hora_entrada']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-3">
        <label class="form-label">Salida almuerzo</label>
        <input type="time" name="hora_salida_almuerzo" class="form-control" value="<?php echo htmlspecialchars($turno->horaSalidaAlmuerzo ?? ''); ?>" required>
        <?php if (!empty($errores['hora_salida_almuerzo'])): ?><div class="text-danger small"><?php echo $errores['hora_salida_almuerzo']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-3">
        <label class="form-label">Retorno almuerzo</label>
        <input type="time" name="hora_retorno_almuerzo" class="form-control" value="<?php echo htmlspecialchars($turno->horaRetornoAlmuerzo ?? ''); ?>" required>
        <?php if (!empty($errores['hora_retorno_almuerzo'])): ?><div class="text-danger small"><?php echo $errores['hora_retorno_almuerzo']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-3">
        <label class="form-label">Hora salida</label>
        <input type="time" name="hora_salida" class="form-control" value="<?php echo htmlspecialchars($turno->horaSalida ?? ''); ?>" required>
        <?php if (!empty($errores['hora_salida'])): ?><div class="text-danger small"><?php echo $errores['hora_salida']; ?></div><?php endif; ?>
    </div>
    <?php if ($modoEdicion): ?>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($turno->id); ?>">
    <?php endif; ?>
    <div class="col-12">
        <button class="btn btn-success" type="submit"><?php echo $modoEdicion ? 'Guardar cambios' : 'Crear turno'; ?></button>
        <a href="<?php echo $baseUrl; ?>/index.php?route=turnos/list" class="btn btn-secondary">Volver al listado</a>
    </div>
    <?php if (!empty($errores['general'])): ?>
        <div class="col-12"><div class="alert alert-danger"><?php echo $errores['general']; ?></div></div>
    <?php endif; ?>
</form>