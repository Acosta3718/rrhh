<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Editar aguinaldo</h2>
    <div class="d-flex gap-2">
        <a href="<?php echo $baseUrl; ?>/index.php?route=aguinaldos/list" class="btn btn-outline-secondary">Volver al listado</a>
        <a href="<?php echo $baseUrl; ?>/index.php?route=aguinaldos/create" class="btn btn-primary">Generar nuevo</a>
    </div>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<form method="post" class="row g-3">
    <input type="hidden" name="id" value="<?php echo htmlspecialchars($aguinaldo->id); ?>">
    <div class="col-md-6">
        <label class="form-label">Funcionario</label>
        <input type="text" class="form-control" value="<?php echo htmlspecialchars($funcionario?->nombre ?? ''); ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label">Empresa</label>
        <input type="text" class="form-control" value="<?php echo htmlspecialchars($funcionario?->empresaNombre ?? ''); ?>" readonly>
    </div>
    <div class="col-md-4">
        <label class="form-label">Año *</label>
        <input type="number" name="anio" class="form-control" value="<?php echo htmlspecialchars($aguinaldo->anio); ?>" max="<?php echo date('Y'); ?>" required>
        <?php if (!empty($errores['anio'])): ?><div class="text-danger small"><?php echo $errores['anio']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label">Monto *</label>
        <input type="number" name="monto" class="form-control" min="0" step="0.01" value="<?php echo htmlspecialchars($aguinaldo->monto); ?>" required>
        <?php if (!empty($errores['monto'])): ?><div class="text-danger small"><?php echo $errores['monto']; ?></div><?php endif; ?>
        <?php if (!empty($errores['periodo'])): ?><div class="text-danger small"><?php echo $errores['periodo']; ?></div><?php endif; ?>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-success">Guardar cambios</button>
        <a href="<?php echo $baseUrl; ?>/index.php?route=aguinaldos/list" class="btn btn-link">Cancelar</a>
    </div>
</form>

<div class="card mt-4">
    <div class="card-body">
        <h5 class="card-title">Totales percibidos por mes</h5>
        <?php if (!empty($totalesPorMes)): ?>
            <?php $totalAnual = array_sum($totalesPorMes); ?>
            <ul class="list-group list-group-flush">
                <?php
                $meses = [
                    1 => 'Enero',
                    2 => 'Febrero',
                    3 => 'Marzo',
                    4 => 'Abril',
                    5 => 'Mayo',
                    6 => 'Junio',
                    7 => 'Julio',
                    8 => 'Agosto',
                    9 => 'Septiembre',
                    10 => 'Octubre',
                    11 => 'Noviembre',
                    12 => 'Diciembre',
                ];
                ?>
                <?php foreach ($totalesPorMes as $mes => $total): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        <span><?php echo htmlspecialchars($meses[$mes] ?? (string) $mes); ?></span>
                        <strong>Gs. <?php echo number_format($total, 0, ',', '.'); ?></strong>
                    </li>
                <?php endforeach; ?>
                <li class="list-group-item d-flex justify-content-between align-items-center fw-semibold">
                    <span>Total anual</span>
                    <strong>Gs. <?php echo number_format($totalAnual, 0, ',', '.'); ?></strong>
                </li>
            </ul>
        <?php else: ?>
            <p class="text-muted mb-0">No hay salarios registrados para el año seleccionado.</p>
        <?php endif; ?>
    </div>
</div>