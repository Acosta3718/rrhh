<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Funcionarios</h2>
    <a href="<?php echo $baseUrl; ?>/index.php?route=funcionarios/create" class="btn btn-primary">Nuevo funcionario</a>
</div>

<form class="row g-3 mb-3" method="get" action="<?php echo $baseUrl; ?>/index.php">
    <input type="hidden" name="route" value="funcionarios/list">
    <div class="col-md-4">
        <label class="form-label">Buscar por nombre</label>
        <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($filtros['nombre'] ?? ''); ?>" placeholder="Ingrese nombre">
    </div>
    <div class="col-md-4">
        <label class="form-label">Empresa</label>
        <select name="empresa_id" class="form-select">
            <option value="">Todas</option>
            <?php foreach ($empresas as $empresa): ?>
                <option value="<?php echo $empresa->id; ?>" <?php echo (!empty($filtros['empresa_id']) && (int)$filtros['empresa_id'] === $empresa->id) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($empresa->razonSocial); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-4 align-self-end">
        <button type="submit" class="btn btn-outline-primary">Filtrar</button>
        <a href="<?php echo $baseUrl; ?>/index.php?route=funcionarios/list" class="btn btn-link">Limpiar</a>
    </div>
</form>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Documento</th>
            <th>Celular</th>
            <th>Salario</th>
            <th>Fecha ingreso</th>
            <th>Nacionalidad</th>
            <th>Estado civil</th>
            <th>ID reloj</th>
            <th>Turno</th>
            <th>Empresa</th>
            <th>Estado</th>
            <th>Tiene IPS</th>
            <th class="text-end">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($funcionarios as $funcionario): ?>
            <tr>
                <td><?php echo htmlspecialchars($funcionario->nombre); ?></td>
                <td><?php echo htmlspecialchars($funcionario->nroDocumento); ?></td>
                <td><?php echo htmlspecialchars($funcionario->celular); ?></td>
                <td><?php echo number_format($funcionario->salario, 0, ',', '.'); ?></td>
                <td><?php echo htmlspecialchars($funcionario->fechaIngreso->format('Y-m-d')); ?></td>
                <td><?php echo htmlspecialchars($funcionario->nacionalidadId ? ($funcionario->nacionalidadNombre ?? '') : ''); ?></td>
                <td class="text-capitalize"><?php echo htmlspecialchars($funcionario->estadoCivil); ?></td>
                <td><?php echo htmlspecialchars($funcionario->nroIdReloj ?? ''); ?></td>
                <td><?php echo htmlspecialchars($funcionario->turnoNombre ?? ''); ?></td>
                <td><?php echo htmlspecialchars($funcionario->empresaNombre ?? ''); ?></td>
                <td class="text-capitalize"><?php echo htmlspecialchars($funcionario->estado); ?></td>
                <td><?php echo $funcionario->tieneIps ? 'Sí' : 'No'; ?></td>
                <td class="text-end">
                    <a class="btn btn-sm btn-secondary" href="<?php echo $baseUrl; ?>/index.php?route=funcionarios/edit&id=<?php echo $funcionario->id; ?>">Editar</a>
                    <form action="<?php echo $baseUrl; ?>/index.php?route=funcionarios/delete" method="post" class="d-inline" onsubmit="return confirm('¿Confirma eliminar el funcionario?');">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($funcionario->id); ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($funcionarios)): ?>
            <tr><td colspan="13" class="text-muted">No hay registros.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../layouts/pagination.php'; ?>