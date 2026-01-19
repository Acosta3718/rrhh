<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Editar liquidación</h2>
    <a href="<?php echo $baseUrl; ?>/index.php?route=liquidaciones/list" class="btn btn-outline-secondary">Volver al listado</a>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>
<?php if (!empty($errores['general'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($errores['general']); ?></div>
<?php endif; ?>

<?php
$detalleActual = $detalle ?? [
    'salario_diario' => $liquidacion->salarioDiario,
    'salario_mes' => $liquidacion->salarioMes,
    'anios_servicio' => $liquidacion->aniosServicio,
    'preaviso_dias' => $liquidacion->preavisoDias,
    'preaviso_monto' => $liquidacion->preavisoMonto,
    'vacaciones_dias' => $liquidacion->vacacionesDias,
    'vacaciones_monto' => $liquidacion->vacacionesMonto,
    'aguinaldo' => $liquidacion->aguinaldo,
    'indemnizacion' => $liquidacion->indemnizacion,
    'total' => $liquidacion->total
];
?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Datos de salida</h5>
                <form method="post" class="row g-3">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars((string) $liquidacion->id); ?>">
                    <div class="col-12">
                        <label class="form-label">Funcionario</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($funcionario?->nombre ?? ''); ?>" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha de salida</label>
                        <input type="date" name="fecha_salida" class="form-control" value="<?php echo htmlspecialchars($_POST['fecha_salida'] ?? $liquidacion->fechaSalida->format('Y-m-d')); ?>" required>
                        <?php if (!empty($errores['fecha_salida'])): ?><div class="text-danger small"><?php echo $errores['fecha_salida']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tipo de salida</label>
                        <select name="tipo_salida" class="form-select" required>
                            <?php foreach ($tiposSalida as $tipo): ?>
                                <option value="<?php echo htmlspecialchars($tipo); ?>" <?php echo (($_POST['tipo_salida'] ?? $liquidacion->tipoSalida) === $tipo) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tipo); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errores['tipo_salida'])): ?><div class="text-danger small"><?php echo $errores['tipo_salida']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Días trabajados en el mes</label>
                        <input type="number" name="dias_trabajados" class="form-control" value="<?php echo htmlspecialchars($_POST['dias_trabajados'] ?? $liquidacion->diasTrabajados); ?>" min="1" max="30" required>
                        <?php if (!empty($errores['dias_trabajados'])): ?><div class="text-danger small"><?php echo $errores['dias_trabajados']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Descuentos</label>
                        <input type="number" name="descuentos" class="form-control" value="<?php echo htmlspecialchars($_POST['descuentos'] ?? $liquidacion->descuentos); ?>" min="0" step="0.01">
                        <?php if (!empty($errores['descuentos'])): ?><div class="text-danger small"><?php echo $errores['descuentos']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Actualizar liquidación</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title text-center text-uppercase">Liquidación de haberes</h5>
                <div class="row small mb-3">
                    <div class="col-md-6">
                        <div><strong>Fecha de salida:</strong> <?php echo htmlspecialchars($_POST['fecha_salida'] ?? $liquidacion->fechaSalida->format('Y-m-d')); ?></div>
                        <div><strong>Tipo de salida:</strong> <?php echo htmlspecialchars($_POST['tipo_salida'] ?? $liquidacion->tipoSalida); ?></div>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <div><strong>Salario diario:</strong> Gs. <?php echo number_format($detalleActual['salario_diario'], 0, ',', '.'); ?></div>
                        <div><strong>Años de servicio:</strong> <?php echo htmlspecialchars((string) $detalleActual['anios_servicio']); ?></div>
                    </div>
                </div>
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Concepto</th>
                            <th class="text-end">Monto Gs.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Salario (<?php echo htmlspecialchars((string) ($_POST['dias_trabajados'] ?? $liquidacion->diasTrabajados)); ?> días trabajados)</td>
                            <td class="text-end"><?php echo number_format($detalleActual['salario_mes'], 0, ',', '.'); ?></td>
                        </tr>
                        <tr>
                            <td>Aguinaldo proporcional</td>
                            <td class="text-end"><?php echo number_format($detalleActual['aguinaldo'], 0, ',', '.'); ?></td>
                        </tr>
                        <tr>
                            <td>Preaviso (<?php echo htmlspecialchars((string) $detalleActual['preaviso_dias']); ?> días)</td>
                            <td class="text-end"><?php echo number_format($detalleActual['preaviso_monto'], 0, ',', '.'); ?></td>
                        </tr>
                        <tr>
                            <td>Vacaciones proporcionales (<?php echo htmlspecialchars((string) $detalleActual['vacaciones_dias']); ?> días)</td>
                            <td class="text-end"><?php echo number_format($detalleActual['vacaciones_monto'], 0, ',', '.'); ?></td>
                        </tr>
                        <tr>
                            <td>Indemnización</td>
                            <td class="text-end"><?php echo number_format($detalleActual['indemnizacion'], 0, ',', '.'); ?></td>
                        </tr>
                        <tr>
                            <td>Descuentos</td>
                            <td class="text-end">-<?php echo number_format((float) ($_POST['descuentos'] ?? $liquidacion->descuentos), 0, ',', '.'); ?></td>
                        </tr>
                        <tr class="table-light fw-bold">
                            <td>Total a cobrar</td>
                            <td class="text-end"><?php echo number_format($detalleActual['total'], 0, ',', '.'); ?></td>
                        </tr>
                    </tbody>
                </table>
                <p class="text-muted small mb-0">El cálculo se actualiza al guardar la liquidación.</p>
            </div>
        </div>
    </div>
</div>