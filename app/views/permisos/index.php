<?php $pagination = $pagination ?? []; ?>

<h2>Permisos</h2>
<p class="text-muted">Administre los permisos disponibles.</p>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<div class="mb-3">
    <a class="btn btn-primary" href="<?php echo $baseUrl; ?>/index.php?route=permisos/create">Nuevo permiso</a>
</div>

<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>Clave</th>
                <th>Descripción</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($permisos as $permiso): ?>
                <tr>
                    <td><?php echo htmlspecialchars($permiso->clave); ?></td>
                    <td><?php echo htmlspecialchars($permiso->descripcion); ?></td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="<?php echo $baseUrl; ?>/index.php?route=permisos/edit&id=<?php echo (int) $permiso->id; ?>">Editar</a>
                        <form method="post" action="<?php echo $baseUrl; ?>/index.php?route=permisos/delete" class="d-inline">
                            <input type="hidden" name="id" value="<?php echo (int) $permiso->id; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar permiso?')">Eliminar</button>
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