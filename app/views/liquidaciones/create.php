<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Generar liquidación</h2>
    <a href="<?php echo $baseUrl; ?>/index.php?route=liquidaciones/list" class="btn btn-outline-secondary">Volver al listado</a>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>
<?php if (!empty($errores['general'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($errores['general']); ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Datos de salida</h5>
                <form method="post" class="row g-3">
                    <div class="col-12">
                        <label class="form-label">Buscar funcionario</label>
                        <input type="text" class="form-control mb-2" placeholder="Escriba para buscar..." list="funcionario_list" id="funcionario_search" required>
                        <input type="hidden" name="funcionario_id" id="funcionario_id">
                        <datalist id="funcionario_list">
                            <?php foreach ($funcionarios as $funcionario): ?>
                                <option
                                    value="<?php echo htmlspecialchars($funcionario->nombre); ?> (<?php echo htmlspecialchars($funcionario->empresaNombre ?? ''); ?>)"
                                    data-id="<?php echo $funcionario->id; ?>"
                                ></option>
                            <?php endforeach; ?>
                        </datalist>
                        <?php if (!empty($errores['funcionario_id'])): ?><div class="text-danger small"><?php echo $errores['funcionario_id']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha de salida</label>
                        <input type="date" name="fecha_salida" class="form-control" value="<?php echo htmlspecialchars($_POST['fecha_salida'] ?? date('Y-m-d')); ?>" required>
                        <?php if (!empty($errores['fecha_salida'])): ?><div class="text-danger small"><?php echo $errores['fecha_salida']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tipo de salida</label>
                        <select name="tipo_salida" class="form-select" required>
                            <option value="">Seleccione</option>
                            <?php foreach ($tiposSalida as $tipo): ?>
                                <option value="<?php echo htmlspecialchars($tipo); ?>" <?php echo (($_POST['tipo_salida'] ?? '') === $tipo) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($tipo); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($errores['tipo_salida'])): ?><div class="text-danger small"><?php echo $errores['tipo_salida']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Días trabajados en el mes</label>
                        <input type="number" name="dias_trabajados" class="form-control" value="<?php echo htmlspecialchars($_POST['dias_trabajados'] ?? 30); ?>" min="1" max="30" required>
                        <?php if (!empty($errores['dias_trabajados'])): ?><div class="text-danger small"><?php echo $errores['dias_trabajados']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Descuentos</label>
                        <input type="number" name="descuentos" class="form-control" value="<?php echo htmlspecialchars($_POST['descuentos'] ?? 0); ?>" min="0" step="0.01">
                        <?php if (!empty($errores['descuentos'])): ?><div class="text-danger small"><?php echo $errores['descuentos']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Calcular y guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title text-center text-uppercase">Liquidación de haberes</h5>
                <?php if ($detalle): ?>
                    <?php if (!empty($liquidacionGuardada?->id)): ?>
                        <div class="d-flex justify-content-end mb-2">
                            <a class="btn btn-sm btn-outline-secondary" href="<?php echo $baseUrl; ?>/index.php?route=liquidaciones/edit&id=<?php echo $liquidacionGuardada->id; ?>">Editar liquidación</a>
                        </div>
                    <?php endif; ?>
                    <div class="row small mb-3">
                        <div class="col-md-6">
                            <div><strong>Fecha de salida:</strong> <?php echo htmlspecialchars($_POST['fecha_salida'] ?? ''); ?></div>
                            <div><strong>Tipo de salida:</strong> <?php echo htmlspecialchars($_POST['tipo_salida'] ?? ''); ?></div>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <div><strong>Salario diario:</strong> Gs. <?php echo number_format($detalle['salario_diario'], 0, ',', '.'); ?></div>
                            <div><strong>Años de servicio:</strong> <?php echo htmlspecialchars((string) $detalle['anios_servicio']); ?></div>
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
                                <td>Salario (<?php echo htmlspecialchars((string) ($_POST['dias_trabajados'] ?? 0)); ?> días trabajados)</td>
                                <td class="text-end"><?php echo number_format($detalle['salario_mes'], 0, ',', '.'); ?></td>
                            </tr>
                            <tr>
                                <td>Aguinaldo proporcional</td>
                                <td class="text-end"><?php echo number_format($detalle['aguinaldo'], 0, ',', '.'); ?></td>
                            </tr>
                            <tr>
                                <td>Preaviso (<?php echo htmlspecialchars((string) $detalle['preaviso_dias']); ?> días)</td>
                                <td class="text-end"><?php echo number_format($detalle['preaviso_monto'], 0, ',', '.'); ?></td>
                            </tr>
                            <tr>
                                <td>Vacaciones proporcionales (<?php echo htmlspecialchars((string) $detalle['vacaciones_dias']); ?> días)</td>
                                <td class="text-end"><?php echo number_format($detalle['vacaciones_monto'], 0, ',', '.'); ?></td>
                            </tr>
                            <tr>
                                <td>Indemnización</td>
                                <td class="text-end"><?php echo number_format($detalle['indemnizacion'], 0, ',', '.'); ?></td>
                            </tr>
                            <tr>
                                <td>Descuentos</td>
                                <td class="text-end">-<?php echo number_format((float) ($_POST['descuentos'] ?? 0), 0, ',', '.'); ?></td>
                            </tr>
                            <tr class="table-light fw-bold">
                                <td>Total a cobrar</td>
                                <td class="text-end"><?php echo number_format($detalle['total'], 0, ',', '.'); ?></td>
                            </tr>
                        </tbody>
                    </table>
                    <p class="text-muted small mb-0">El cálculo incluye salario diario, preaviso, vacaciones y aguinaldo proporcional según la antigüedad indicada.</p>
                <?php else: ?>
                    <p class="text-muted mb-0">Complete los datos para visualizar el detalle de la liquidación.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
const funcionarioSearch = document.getElementById('funcionario_search');
const funcionarioIdInput = document.getElementById('funcionario_id');
const funcionarioList = document.getElementById('funcionario_list');

function buscarOpcionPorValor(list, value) {
    if (!list || !value) return null;
    return Array.from(list.options).find(option => option.value === value) || null;
}

function actualizarFuncionarioSeleccionado() {
    const option = buscarOpcionPorValor(funcionarioList, funcionarioSearch?.value);
    funcionarioIdInput.value = option?.dataset.id || '';
}

funcionarioSearch?.addEventListener('input', actualizarFuncionarioSeleccionado);
document.addEventListener('DOMContentLoaded', actualizarFuncionarioSeleccionado);
</script>