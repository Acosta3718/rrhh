<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Adelantos</h2>
    <div class="d-flex gap-2">
        <a href="<?php echo $baseUrl; ?>/index.php?route=adelantos/create" class="btn btn-primary">Generar adelanto</a>
    </div>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<form class="row g-3 mb-3" method="get" action="<?php echo $baseUrl; ?>/index.php">
    <input type="hidden" name="route" value="adelantos/list">
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
    <div class="col-md-2">
        <label class="form-label">Mes</label>
        <select name="mes" class="form-select">
            <option value="">Todos</option>
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?php echo $m; ?>" <?php echo (!empty($filtros['mes']) && (int)$filtros['mes'] === $m) ? 'selected' : ''; ?>>
                    <?php echo $m; ?>
                </option>
            <?php endfor; ?>
        </select>
    </div>
    <div class="col-md-2">
        <label class="form-label">Año</label>
        <input type="number" name="anio" class="form-control" value="<?php echo htmlspecialchars($filtros['anio'] ?? ''); ?>" placeholder="YYYY">
    </div>
    <div class="col-md-2 align-self-end">
        <button type="submit" class="btn btn-outline-primary">Filtrar</button>
        <a href="<?php echo $baseUrl; ?>/index.php?route=adelantos/list" class="btn btn-link">Limpiar</a>
    </div>
</form>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Funcionario</th>
            <th>Empresa</th>
            <th>Año</th>
            <th>Mes</th>
            <th>Monto</th>
            <th>Fecha</th>
            <th class="text-end">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($adelantos as $adelanto): ?>
            <tr>
                <td><?php echo htmlspecialchars($adelanto->funcionarioNombre ?? ''); ?></td>
                <td><?php echo htmlspecialchars($adelanto->empresaNombre ?? ''); ?></td>
                <td><?php echo htmlspecialchars($adelanto->anio); ?></td>
                <td><?php echo htmlspecialchars($adelanto->mes); ?></td>
                <td><?php echo number_format($adelanto->monto, 0, ',', '.'); ?></td>
                <td><?php echo htmlspecialchars($adelanto->creadoEn?->format('Y-m-d H:i') ?? ''); ?></td>
                <td class="text-end">
                    <a class="btn btn-sm btn-outline-primary" href="<?php echo $baseUrl; ?>/index.php?route=adelantos/print&id=<?php echo $adelanto->id; ?>" target="_blank">Imprimir</a>
                    <a class="btn btn-sm btn-secondary" href="<?php echo $baseUrl; ?>/index.php?route=adelantos/edit&id=<?php echo $adelanto->id; ?>">Editar</a>
                    <form action="<?php echo $baseUrl; ?>/index.php?route=adelantos/delete" method="post" class="d-inline" onsubmit="return confirm('¿Confirma eliminar el adelanto?');">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($adelanto->id); ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($adelantos)): ?>
            <tr><td colspan="7" class="text-muted">No hay registros.</td></tr>
        <?php endif; ?>
    </tbody>
</table>