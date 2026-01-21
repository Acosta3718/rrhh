<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Aguinaldos</h2>
    <div class="d-flex gap-2">
        <a href="<?php echo $baseUrl; ?>/index.php?route=aguinaldos/create" class="btn btn-primary">Generar aguinaldo</a>
    </div>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<form class="row g-3 mb-3" method="get" action="<?php echo $baseUrl; ?>/index.php">
    <input type="hidden" name="route" value="aguinaldos/list">
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
    <div class="col-md-4">
        <label class="form-label">Funcionario</label>
        <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($filtros['nombre'] ?? ''); ?>" placeholder="Nombre">
    </div>
    <div class="col-md-2">
        <label class="form-label">Año</label>
        <input type="number" name="anio" class="form-control" value="<?php echo htmlspecialchars($filtros['anio'] ?? ''); ?>" placeholder="YYYY">
    </div>
    <div class="col-md-2 align-self-end">
        <button type="submit" class="btn btn-outline-primary">Filtrar</button>
        <a href="<?php echo $baseUrl; ?>/index.php?route=aguinaldos/list" class="btn btn-link">Limpiar</a>
    </div>
</form>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Funcionario</th>
            <th>Empresa</th>
            <th>Año</th>
            <th>Total anual</th>
            <th>Monto</th>
            <th>Fecha</th>
            <th class="text-end">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($aguinaldos as $aguinaldo): ?>
            <tr>
                <td><?php echo htmlspecialchars($aguinaldo->funcionarioNombre ?? ''); ?></td>
                <td><?php echo htmlspecialchars($aguinaldo->empresaNombre ?? ''); ?></td>
                <td><?php echo htmlspecialchars($aguinaldo->anio); ?></td>
                <td><?php echo number_format($totalesAnuales[$aguinaldo->id ?? 0] ?? 0, 0, ',', '.'); ?></td>
                <td><?php echo number_format($aguinaldo->monto, 0, ',', '.'); ?></td>
                <td><?php echo htmlspecialchars($aguinaldo->creadoEn?->format('Y-m-d H:i') ?? ''); ?></td>
                <td class="text-end">
                    <a class="btn btn-sm btn-secondary" href="<?php echo $baseUrl; ?>/index.php?route=aguinaldos/edit&id=<?php echo $aguinaldo->id; ?>">Editar</a>
                    <form action="<?php echo $baseUrl; ?>/index.php?route=aguinaldos/delete" method="post" class="d-inline" onsubmit="return confirm('¿Confirma eliminar el aguinaldo?');">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($aguinaldo->id); ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($aguinaldos)): ?>
            <tr><td colspan="7" class="text-muted">No hay registros.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../layouts/pagination.php'; ?>