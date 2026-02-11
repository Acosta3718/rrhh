<?php $pagination = $pagination ?? []; ?>

<h2>Usuarios</h2>
<p class="text-muted">Gestione las cuentas de acceso.</p>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<div class="mb-3">
    <a class="btn btn-primary" href="<?php echo $baseUrl; ?>/index.php?route=usuarios/create">Nuevo usuario</a>
</div>

<div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Activo</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario): ?>
                <tr>
                    <td><?php echo htmlspecialchars($usuario->nombre); ?></td>
                    <td><?php echo htmlspecialchars($usuario->correo); ?></td>
                    <td>
                        <?php echo $usuario->activo ? '<span class="badge bg-success">Sí</span>' : '<span class="badge bg-secondary">No</span>'; ?>
                    </td>
                    <td class="text-end">
                        <a class="btn btn-sm btn-outline-primary" href="<?php echo $baseUrl; ?>/index.php?route=usuarios/edit&id=<?php echo (int) $usuario->id; ?>">Editar</a>
                        <form method="post" action="<?php echo $baseUrl; ?>/index.php?route=usuarios/delete" class="d-inline">
                            <input type="hidden" name="id" value="<?php echo (int) $usuario->id; ?>">
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar usuario?')">Eliminar</button>
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