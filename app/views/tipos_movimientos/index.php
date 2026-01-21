<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Tipos de crédito/débito</h2>
    <div class="d-flex gap-2">
        <a href="<?php echo $baseUrl; ?>/index.php?route=tipos-movimientos/create" class="btn btn-primary">Crear tipo</a>
    </div>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Descripción</th>
            <th>Estado</th>
            <th>Tipo</th>
            <th class="text-end">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($tipos as $tipo): ?>
            <tr>
                <td><?php echo htmlspecialchars($tipo->descripcion); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($tipo->estado)); ?></td>
                <td><?php echo htmlspecialchars(ucfirst($tipo->tipo)); ?></td>
                <td class="text-end">
                    <a class="btn btn-sm btn-secondary" href="<?php echo $baseUrl; ?>/index.php?route=tipos-movimientos/edit&id=<?php echo $tipo->id; ?>">Editar</a>
                    <form action="<?php echo $baseUrl; ?>/index.php?route=tipos-movimientos/delete" method="post" class="d-inline" onsubmit="return confirm('¿Confirma eliminar el tipo?');">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($tipo->id); ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($tipos)): ?>
            <tr><td colspan="4" class="text-muted">No hay registros.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../layouts/pagination.php'; ?>