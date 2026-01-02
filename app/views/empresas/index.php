<h2>Empresas registradas</h2>
<p class="text-muted">Listado de empresas disponibles con opciones para editar o eliminar.</p>
<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>
<div class="d-flex justify-content-end mb-3">
    <a href="<?php echo $baseUrl; ?>/index.php?route=empresas/create" class="btn btn-primary">Crear empresa</a>
<?php if (empty($empresas)): ?>
</div>
    <div class="alert alert-info">Aún no hay empresas registradas.</div>
<?php else: ?>
</div>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Razón social</th>
            <th>RUC</th>
            <th>Email</th>
            <th>Teléfono</th>
            <th>Dirección</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($empresas as $empresaItem): ?>
        <tr>
            <td><?php echo htmlspecialchars($empresaItem->razonSocial); ?></td>
            <td><?php echo htmlspecialchars($empresaItem->ruc); ?></td>
            <td><?php echo htmlspecialchars($empresaItem->correo); ?></td>
            <td><?php echo htmlspecialchars($empresaItem->telefono); ?></td>
            <td><?php echo htmlspecialchars($empresaItem->direccion); ?></td>
            <td class="text-nowrap">
                <a class="btn btn-sm btn-outline-primary" href="<?php echo $baseUrl; ?>/index.php?route=empresas/edit&id=<?php echo (int) $empresaItem->id; ?>">Editar</a>
                <form method="post" action="<?php echo $baseUrl; ?>/index.php?route=empresas/delete" class="d-inline" onsubmit="return confirm('¿Eliminar la empresa seleccionada?');">
                    <input type="hidden" name="id" value="<?php echo (int) $empresaItem->id; ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger ms-1">Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>