<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Liquidaciones</h2>
    <div class="d-flex gap-2">
        <a href="<?php echo $baseUrl; ?>/index.php?route=liquidaciones/create" class="btn btn-primary">Generar liquidación</a>
    </div>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<form class="row g-3 mb-3" method="get" action="<?php echo $baseUrl; ?>/index.php">
    <input type="hidden" name="route" value="liquidaciones/list">
    <div class="col-md-3">
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
    <div class="col-md-3">
        <label class="form-label">Funcionario</label>
        <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($filtros['nombre'] ?? ''); ?>" placeholder="Nombre">
    </div>
    <div class="col-md-3">
        <label class="form-label">Tipo de salida</label>
        <select name="tipo_salida" class="form-select">
            <option value="">Todos</option>
            <?php foreach ($tiposSalida as $tipo): ?>
                <option value="<?php echo htmlspecialchars($tipo); ?>" <?php echo (!empty($filtros['tipo_salida']) && $filtros['tipo_salida'] === $tipo) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($tipo); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-md-3 align-self-end">
        <button type="submit" class="btn btn-outline-primary">Filtrar</button>
        <a href="<?php echo $baseUrl; ?>/index.php?route=liquidaciones/list" class="btn btn-link">Limpiar</a>
    </div>
</form>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Funcionario</th>
            <th>Empresa</th>
            <th>Fecha de salida</th>
            <th>Tipo</th>
            <th class="text-end">Total</th>
            <th>Creado</th>
            <th class="text-end">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($liquidaciones as $liquidacion): ?>
            <tr>
                <td><?php echo htmlspecialchars($liquidacion->funcionarioNombre ?? ''); ?></td>
                <td><?php echo htmlspecialchars($liquidacion->empresaNombre ?? ''); ?></td>
                <td><?php echo htmlspecialchars($liquidacion->fechaSalida->format('Y-m-d')); ?></td>
                <td><?php echo htmlspecialchars($liquidacion->tipoSalida); ?></td>
                <td class="text-end"><?php echo number_format($liquidacion->total, 0, ',', '.'); ?></td>
                <td><?php echo htmlspecialchars($liquidacion->creadoEn?->format('Y-m-d H:i') ?? ''); ?></td>
                <td class="text-end">
                    <a class="btn btn-sm btn-secondary" href="<?php echo $baseUrl; ?>/index.php?route=liquidaciones/edit&id=<?php echo $liquidacion->id; ?>">Editar</a>
                    <form method="post" action="<?php echo $baseUrl; ?>/index.php?route=liquidaciones/delete" class="d-inline" onsubmit="return confirm('¿Eliminar esta liquidación?');">
                        <input type="hidden" name="id" value="<?php echo $liquidacion->id; ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($liquidaciones)): ?>
            <tr><td colspan="7" class="text-muted">No hay registros.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../layouts/pagination.php'; ?>