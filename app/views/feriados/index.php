<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Feriados</h2>
    <a href="<?php echo $baseUrl; ?>/index.php?route=feriados/create" class="btn btn-primary">Nuevo feriado</a>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Fecha</th>
            <th>Descripción</th>
            <th class="text-end">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($feriados as $feriado): ?>
            <tr>
                <td><?php echo htmlspecialchars($feriado->id); ?></td>
                <td><?php echo htmlspecialchars($feriado->fecha?->format('Y-m-d')); ?></td>
                <td><?php echo htmlspecialchars($feriado->descripcion); ?></td>
                <td class="text-end">
                    <a class="btn btn-sm btn-secondary" href="<?php echo $baseUrl; ?>/index.php?route=feriados/edit&id=<?php echo $feriado->id; ?>">Editar</a>
                    <form action="<?php echo $baseUrl; ?>/index.php?route=feriados/delete" method="post" class="d-inline" onsubmit="return confirm('¿Confirma eliminar el feriado?');">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($feriado->id); ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($feriados)): ?>
            <tr><td colspan="4" class="text-muted">No hay registros.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../layouts/pagination.php'; ?>