<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Turnos</h2>
    <a href="<?php echo $baseUrl; ?>/index.php?route=turnos/create" class="btn btn-primary">Nuevo turno</a>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Inicio</th>
            <th>Fin</th>
            <th>Entrada</th>
            <th>Salida almuerzo</th>
            <th>Retorno almuerzo</th>
            <th>Salida</th>
            <th class="text-end">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($turnos as $turno): ?>
            <tr>
                <td><?php echo htmlspecialchars($turno->nombre); ?></td>
                <td><?php echo htmlspecialchars($turno->fechaInicio?->format('Y-m-d') ?? ''); ?></td>
                <td><?php echo htmlspecialchars($turno->fechaFin?->format('Y-m-d') ?? ''); ?></td>
                <td><?php echo htmlspecialchars($turno->horaEntrada); ?></td>
                <td><?php echo htmlspecialchars($turno->horaSalidaAlmuerzo); ?></td>
                <td><?php echo htmlspecialchars($turno->horaRetornoAlmuerzo); ?></td>
                <td><?php echo htmlspecialchars($turno->horaSalida); ?></td>
                <td class="text-end">
                    <a class="btn btn-sm btn-secondary" href="<?php echo $baseUrl; ?>/index.php?route=turnos/edit&id=<?php echo $turno->id; ?>">Editar</a>
                    <form action="<?php echo $baseUrl; ?>/index.php?route=turnos/delete" method="post" class="d-inline" onsubmit="return confirm('¿Confirma eliminar el turno?');">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($turno->id); ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($turnos)): ?>
            <tr><td colspan="8" class="text-muted">No hay registros.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../layouts/pagination.php'; ?>