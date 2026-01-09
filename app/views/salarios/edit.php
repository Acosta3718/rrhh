<?php
$creditosTotal = $salario->salarioBase;
$debitosTotal = $salario->adelanto + $salario->ips;
foreach ($tipos as $tipo) {
    $monto = (float) ($movimientosPorTipo[$tipo->id] ?? 0);
    if ($tipo->tipo === 'credito') {
        $creditosTotal += $monto;
    } else {
        $debitosTotal += $monto;
    }
}
$netoCalculado = $creditosTotal - $debitosTotal;
?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Editar salario</h2>
    <div class="d-flex gap-2">
        <a href="<?php echo $baseUrl; ?>/index.php?route=salarios/list" class="btn btn-outline-secondary">Volver al listado</a>
        <a href="<?php echo $baseUrl; ?>/index.php?route=salarios/create" class="btn btn-primary">Generar nuevo</a>
    </div>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<form method="post" class="row g-3">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($salario->id); ?>">
    <div class="col-md-6">
        <label class="form-label">Funcionario</label>
        <input type="text" class="form-control" value="<?php echo htmlspecialchars($funcionario?->nombre ?? $salario->funcionarioNombre ?? ''); ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label">Empresa</label>
        <input type="text" class="form-control" value="<?php echo htmlspecialchars($funcionario?->empresaNombre ?? $salario->empresaNombre ?? ''); ?>" readonly>
    </div>
    <div class="col-md-4">
        <label class="form-label">Mes *</label>
        <select name="mes" class="form-select" required>
            <?php for ($m = 1; $m <= 12; $m++): ?>
                <option value="<?php echo $m; ?>" <?php echo ($salario->mes === $m) ? 'selected' : ''; ?>>
                    <?php echo $m; ?>
                </option>
            <?php endfor; ?>
        </select>
        <?php if (!empty($errores['mes'])): ?><div class="text-danger small"><?php echo $errores['mes']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label">Año *</label>
        <input type="number" name="anio" class="form-control" value="<?php echo htmlspecialchars($salario->anio); ?>" max="<?php echo date('Y'); ?>" required>
        <?php if (!empty($errores['anio'])): ?><div class="text-danger small"><?php echo $errores['anio']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label">Neto calculado</label>
        <input type="text" class="form-control" value="<?php echo number_format($netoCalculado, 0, ',', '.'); ?>" readonly>
        <?php if (!empty($errores['periodo'])): ?><div class="text-danger small"><?php echo $errores['periodo']; ?></div><?php endif; ?>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">Créditos</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Salario base *</label>
                    <input type="number" name="salario_base" class="form-control" min="0" step="0.01" value="<?php echo htmlspecialchars($salario->salarioBase); ?>" required>
                    <?php if (!empty($errores['salario_base'])): ?><div class="text-danger small"><?php echo $errores['salario_base']; ?></div><?php endif; ?>
                </div>
                <?php foreach ($tipos as $tipo): ?>
                    <?php if ($tipo->tipo !== 'credito') { continue; } ?>
                    <div class="col-md-4">
                        <label class="form-label">
                            <?php echo htmlspecialchars($tipo->descripcion); ?>
                            <?php if ($tipo->estado === 'inactivo'): ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </label>
                        <input type="number" name="movimientos[<?php echo $tipo->id; ?>]" class="form-control" min="0" step="0.01" value="<?php echo htmlspecialchars($movimientosPorTipo[$tipo->id] ?? 0); ?>">
                        <?php if (!empty($errores['movimientos'][$tipo->id])): ?><div class="text-danger small"><?php echo $errores['movimientos'][$tipo->id]; ?></div><?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <div class="col-12 text-end">
                    <strong>Total créditos: <?php echo number_format($creditosTotal, 0, ',', '.'); ?></strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">Débitos</div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Adelanto</label>
                    <input type="number" name="adelanto" class="form-control" min="0" step="0.01" value="<?php echo htmlspecialchars($salario->adelanto); ?>">
                    <?php if (!empty($errores['adelanto'])): ?><div class="text-danger small"><?php echo $errores['adelanto']; ?></div><?php endif; ?>
                </div>
                <div class="col-md-4">
                    <label class="form-label">IPS</label>
                    <input type="number" name="ips" class="form-control" min="0" step="0.01" value="<?php echo htmlspecialchars($salario->ips); ?>">
                    <?php if (!empty($errores['ips'])): ?><div class="text-danger small"><?php echo $errores['ips']; ?></div><?php endif; ?>
                </div>
                <?php foreach ($tipos as $tipo): ?>
                    <?php if ($tipo->tipo !== 'debito') { continue; } ?>
                    <div class="col-md-4">
                        <label class="form-label">
                            <?php echo htmlspecialchars($tipo->descripcion); ?>
                            <?php if ($tipo->estado === 'inactivo'): ?>
                                <span class="badge bg-secondary">Inactivo</span>
                            <?php endif; ?>
                        </label>
                        <input type="number" name="movimientos[<?php echo $tipo->id; ?>]" class="form-control" min="0" step="0.01" value="<?php echo htmlspecialchars($movimientosPorTipo[$tipo->id] ?? 0); ?>">
                        <?php if (!empty($errores['movimientos'][$tipo->id])): ?><div class="text-danger small"><?php echo $errores['movimientos'][$tipo->id]; ?></div><?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <div class="col-12 text-end">
                    <strong>Total débitos: <?php echo number_format($debitosTotal, 0, ',', '.'); ?></strong>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-success">Guardar cambios</button>
        <a href="<?php echo $baseUrl; ?>/index.php?route=salarios/list" class="btn btn-link">Cancelar</a>
    </div>
</form>