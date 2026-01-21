<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Salarios</h2>
    <div class="d-flex gap-2">
        <a href="<?php echo $baseUrl; ?>/index.php?route=salarios/create" class="btn btn-primary">Generar salario</a>
    </div>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<form class="row g-3 mb-3" method="get" action="<?php echo $baseUrl; ?>/index.php">
    <input type="hidden" name="route" value="salarios/list">
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
        <a href="<?php echo $baseUrl; ?>/index.php?route=salarios/list" class="btn btn-link">Limpiar</a>
    </div>
</form>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Funcionario</th>
            <th>Empresa</th>
            <th>Año</th>
            <th>Mes</th>
            <th>Créditos</th>
            <th>Débitos</th>
            <th>Total</th>
            <th>Fecha</th>
            <th class="text-end">Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($salarios as $salario): ?>
            <tr>
                <td><?php echo htmlspecialchars($salario->funcionarioNombre ?? ''); ?></td>
                <td><?php echo htmlspecialchars($salario->empresaNombre ?? ''); ?></td>
                <td><?php echo htmlspecialchars($salario->anio); ?></td>
                <td><?php echo htmlspecialchars($salario->mes); ?></td>
                <?php
                $totalesMovimiento = $movimientosTotales[$salario->id ?? 0] ?? ['creditos' => 0.0, 'debitos' => 0.0];
                $creditos = $salario->salarioBase + $totalesMovimiento['creditos'];
                $debitos = $salario->adelanto + $salario->ips + $totalesMovimiento['debitos'];
                $total = $creditos - $debitos;
                ?>
                <td><?php echo number_format($creditos, 0, ',', '.'); ?></td>
                <td><?php echo number_format($debitos, 0, ',', '.'); ?></td>
                <td><?php echo number_format($total, 0, ',', '.'); ?></td>
                <td><?php echo htmlspecialchars($salario->creadoEn?->format('Y-m-d H:i') ?? ''); ?></td>
                <td class="text-end">
                    <a class="btn btn-sm btn-secondary" href="<?php echo $baseUrl; ?>/index.php?route=salarios/edit&id=<?php echo $salario->id; ?>">Editar</a>
                    <form action="<?php echo $baseUrl; ?>/index.php?route=salarios/delete" method="post" class="d-inline" onsubmit="return confirm('¿Confirma eliminar el salario?');">
                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($salario->id); ?>">
                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
        <?php if (empty($salarios)): ?>
            <tr><td colspan="9" class="text-muted">No hay registros.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<?php require __DIR__ . '/../layouts/pagination.php'; ?>