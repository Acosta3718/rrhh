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
$creditosMostrados = [];
$debitosMostrados = [];
$creditosDisponibles = [];
$debitosDisponibles = [];

foreach ($tipos as $tipo) {
    $tieneMovimiento = array_key_exists($tipo->id, $movimientosPorTipo);
    if ($tipo->tipo === 'credito') {
        if ($tieneMovimiento) {
            $creditosMostrados[] = $tipo;
        } else {
            $creditosDisponibles[] = $tipo;
        }
    } else {
        if ($tieneMovimiento) {
            $debitosMostrados[] = $tipo;
        } else {
            $debitosDisponibles[] = $tipo;
        }
    }
}
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
            <div class="card-body">
                <div class="row g-3" id="creditos-rows">
                    <div class="col-md-4">
                        <label class="form-label">Salario base *</label>
                        <input type="number" name="salario_base" class="form-control" min="0" step="0.01" value="<?php echo htmlspecialchars($salario->salarioBase); ?>" required>
                        <?php if (!empty($errores['salario_base'])): ?><div class="text-danger small"><?php echo $errores['salario_base']; ?></div><?php endif; ?>
                    </div>
                    <?php foreach ($creditosMostrados as $tipo): ?>
                        <div class="col-md-4" data-tipo-id="<?php echo $tipo->id; ?>" data-target="creditos" data-descripcion="<?php echo htmlspecialchars($tipo->descripcion); ?>" data-estado="<?php echo htmlspecialchars($tipo->estado); ?>">
                            <label class="form-label">
                                <?php echo htmlspecialchars($tipo->descripcion); ?>
                                <?php if ($tipo->estado === 'inactivo'): ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </label>
                            <div class="input-group">
                                <input type="number" name="movimientos[<?php echo $tipo->id; ?>]" class="form-control" min="0" step="0.01" value="<?php echo htmlspecialchars($movimientosPorTipo[$tipo->id] ?? 0); ?>">
                                <button class="btn btn-outline-danger js-remove-tipo" type="button">Quitar</button>
                            </div>
                            <?php if (!empty($errores['movimientos'][$tipo->id])): ?><div class="text-danger small"><?php echo $errores['movimientos'][$tipo->id]; ?></div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="row g-3 align-items-end mt-1">
                    <div class="col-md-6">
                        <label class="form-label">Agregar tipo de crédito</label>
                        <select class="form-select js-tipo-select" id="creditos-select">
                            <?php foreach ($creditosDisponibles as $tipo): ?>
                                <option value="<?php echo $tipo->id; ?>" data-descripcion="<?php echo htmlspecialchars($tipo->descripcion); ?>" data-estado="<?php echo htmlspecialchars($tipo->estado); ?>">
                                    <?php echo htmlspecialchars($tipo->descripcion); ?><?php echo $tipo->estado === 'inactivo' ? ' (Inactivo)' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-primary js-agregar-tipo" data-target="creditos">Agregar</button>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-12 text-end">
                        <strong>Total créditos: <?php echo number_format($creditosTotal, 0, ',', '.'); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-header">Débitos</div>
            <div class="card-body">
                <div class="row g-3" id="debitos-rows">
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
                    <?php foreach ($debitosMostrados as $tipo): ?>
                        <div class="col-md-4" data-tipo-id="<?php echo $tipo->id; ?>" data-target="debitos" data-descripcion="<?php echo htmlspecialchars($tipo->descripcion); ?>" data-estado="<?php echo htmlspecialchars($tipo->estado); ?>">
                            <label class="form-label">
                                <?php echo htmlspecialchars($tipo->descripcion); ?>
                                <?php if ($tipo->estado === 'inactivo'): ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </label>
                            <div class="input-group">
                                <input type="number" name="movimientos[<?php echo $tipo->id; ?>]" class="form-control" min="0" step="0.01" value="<?php echo htmlspecialchars($movimientosPorTipo[$tipo->id] ?? 0); ?>">
                                <button class="btn btn-outline-danger js-remove-tipo" type="button">Quitar</button>
                            </div>
                            <?php if (!empty($errores['movimientos'][$tipo->id])): ?><div class="text-danger small"><?php echo $errores['movimientos'][$tipo->id]; ?></div><?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="row g-3 align-items-end mt-1">
                    <div class="col-md-6">
                        <label class="form-label">Agregar tipo de débito</label>
                        <select class="form-select js-tipo-select" id="debitos-select">
                            <?php foreach ($debitosDisponibles as $tipo): ?>
                                <option value="<?php echo $tipo->id; ?>" data-descripcion="<?php echo htmlspecialchars($tipo->descripcion); ?>" data-estado="<?php echo htmlspecialchars($tipo->estado); ?>">
                                    <?php echo htmlspecialchars($tipo->descripcion); ?><?php echo $tipo->estado === 'inactivo' ? ' (Inactivo)' : ''; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-primary js-agregar-tipo" data-target="debitos">Agregar</button>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-12 text-end">
                        <strong>Total débitos: <?php echo number_format($debitosTotal, 0, ',', '.'); ?></strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-success">Guardar cambios</button>
        <a href="<?php echo $baseUrl; ?>/index.php?route=salarios/list" class="btn btn-link">Cancelar</a>
    </div>
</form>

<script>
    (() => {
        const agregarTipo = (target) => {
            const select = document.getElementById(`${target}-select`);
            const rows = document.getElementById(`${target}-rows`);
            if (!select || !rows) {
                return;
            }
            const option = select.selectedOptions[0];
            if (!option) {
                return;
            }

            const tipoId = option.value;
            const descripcion = option.dataset.descripcion || option.textContent;
            const estado = option.dataset.estado || '';
            crearFilaMovimiento(rows, target, tipoId, descripcion, estado);

            option.remove();
            if (!select.options.length) {
                select.disabled = true;
                const button = document.querySelector(`.js-agregar-tipo[data-target="${target}"]`);
                if (button) {
                    button.disabled = true;
                }
            }
        };

            const crearFilaMovimiento = (rows, target, tipoId, descripcion, estado) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'col-md-4';
            wrapper.dataset.tipoId = tipoId;
            wrapper.dataset.target = target;
            wrapper.dataset.descripcion = descripcion;
            wrapper.dataset.estado = estado;

            const label = document.createElement('label');
            label.className = 'form-label';
            label.textContent = descripcion;

            if (estado === 'inactivo') {
                const badge = document.createElement('span');
                badge.className = 'badge bg-secondary ms-2';
                badge.textContent = 'Inactivo';
                label.appendChild(badge);
            }

            const inputGroup = document.createElement('div');
            inputGroup.className = 'input-group';

            const input = document.createElement('input');
            input.type = 'number';
            input.name = `movimientos[${tipoId}]`;
            input.className = 'form-control';
            input.min = '0';
            input.step = '0.01';
            input.value = '0';

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.className = 'btn btn-outline-danger js-remove-tipo';
            removeButton.textContent = 'Quitar';

            inputGroup.appendChild(input);
            inputGroup.appendChild(removeButton);
            wrapper.appendChild(label);
            wrapper.appendChild(inputGroup);
            rows.appendChild(wrapper);
        };

            const removerTipo = (button) => {
            const wrapper = button.closest('[data-tipo-id]');
            if (!wrapper) {
                return;
            }
            const target = wrapper.dataset.target;
            const tipoId = wrapper.dataset.tipoId;
            const descripcion = wrapper.dataset.descripcion || '';
            const estado = wrapper.dataset.estado || '';
            const select = document.getElementById(`${target}-select`);
            const addButton = document.querySelector(`.js-agregar-tipo[data-target="${target}"]`);

            if (select) {
                const option = document.createElement('option');
                option.value = tipoId;
                option.dataset.descripcion = descripcion;
                option.dataset.estado = estado;
                option.textContent = descripcion + (estado === 'inactivo' ? ' (Inactivo)' : '');
                select.appendChild(option);
                select.disabled = false;
            }

            if (addButton) {
                addButton.disabled = false;
            }

            wrapper.remove();
        };

        document.querySelectorAll('.js-agregar-tipo').forEach((button) => {
            button.addEventListener('click', () => agregarTipo(button.dataset.target));
        });

        document.addEventListener('click', (event) => {
            const target = event.target;
            if (target instanceof HTMLElement && target.classList.contains('js-remove-tipo')) {
                removerTipo(target);
            }
        });

        document.querySelectorAll('.js-tipo-select').forEach((select) => {
            if (!select.options.length) {
                select.disabled = true;
                const target = select.id.replace('-select', '');
                const button = document.querySelector(`.js-agregar-tipo[data-target="${target}"]`);
                if (button) {
                    button.disabled = true;
                }
            }
        });
    })();
</script>