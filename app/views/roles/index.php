<?php $pagination = $pagination ?? []; ?>

<h2>Roles</h2>
<p class="text-muted">Administre los roles del sistema.</p>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<div class="mb-3">
    <a class="btn btn-primary" href="<?php echo $baseUrl; ?>/index.php?route=roles/create">Nuevo rol</a>
</div>

<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($roles as $rol): ?>
                <tr>
                    <td><?php echo htmlspecialchars($rol->nombre); ?></td>
                    <td><?php echo htmlspecialchars($rol->descripcion); ?></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="<?php echo $baseUrl; ?>/index.php?route=roles/edit&id=<?php echo (int) $rol->id; ?>">Editar</a>
                        <form method="post" action="<?php echo $baseUrl; ?>/index.php?route=roles/delete" class="d-inline">
                            <input type="hidden" name="id" value="<?php echo (int) $rol->id; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar rol?')">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($pagination['totalPages']) && $pagination['totalPages'] > 1): ?>
    <nav>
        <ul class="pagination">
            <?php for ($i = 1; $i <= $pagination['totalPages']; $i++): ?>
                <?php $active = $i === $pagination['page'] ? 'active' : ''; ?>
                <li class="page-item <?php echo $active; ?>">
                    <a class="page-link" href="<?php echo $baseUrl; ?>/index.php?<?php echo http_build_query(array_merge($pagination['params'], ['page' => $i])); ?>"><?php echo $i; ?></a>
                </li>
            <?php endfor; ?>
        </ul>
    </nav>
<?php endif; ?>