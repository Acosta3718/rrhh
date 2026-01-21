<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Nacionalidades</h2>
    <a href="<?php echo $baseUrl; ?>/index.php?route=nacionalidades/create" class="btn btn-primary">Nueva nacionalidad</a>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<table class="table table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th class="text-end">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($nacionalidades as $nacionalidad): ?>
            <tr>
                <td><?php echo htmlspecialchars($nacionalidad->id); ?></td>
                <td><?php echo htmlspecialchars($nacionalidad->nombre); ?></td>
                <td class="text-end">
                    <a class="btn btn-sm btn-secondary" href="<?php echo $baseUrl; ?>/index.php?route=nacionalidades/edit&id=<?php echo $nacionalidad->id; ?>">Editar</a>
                    <form action="<?php echo $baseUrl; ?>/index.php?route=nacionalidades/delete" method="post" class="d-inline" onsubmit="return confirm('¿Confirma eliminar la nacionalidad?');">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($nacionalidad->id); ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($nacionalidades)): ?>
            <tr><td colspan="3" class="text-muted">No hay registros.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../layouts/pagination.php'; ?>