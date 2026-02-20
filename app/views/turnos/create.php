<h2><?php echo $modoEdicion ? 'Editar turno' : 'Crear turno'; ?></h2>
<p class="text-muted">Defina el período y los horarios del turno por día. Puede desactivar días no laborables y dejar horas en cero cuando no apliquen.</p>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>
<?php if (!empty($errores)): ?>
    <div class="alert alert-danger">Revise los datos obligatorios.</div>
<?php endif; ?>

<form method="post" class="row g-3 mb-4" id="turno-form">
    <div class="col-md-6">
        <label class="form-label">Nombre</label>
        <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($turno->nombre ?? ''); ?>" required>
        <?php if (!empty($errores['nombre'])): ?><div class="text-danger small"><?php echo $errores['nombre']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-3">
        <label class="form-label">Fecha inicio</label>
        <input type="date" name="fecha_inicio" class="form-control" value="<?php echo htmlspecialchars($turno?->fechaInicio?->format('Y-m-d') ?? ''); ?>" required>
        <?php if (!empty($errores['fecha_inicio'])): ?><div class="text-danger small"><?php echo $errores['fecha_inicio']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-3">
        <label class="form-label">Fecha fin</label>
        <input type="date" name="fecha_fin" class="form-control" value="<?php echo htmlspecialchars($turno?->fechaFin?->format('Y-m-d') ?? ''); ?>" required>
        <?php if (!empty($errores['fecha_fin'])): ?><div class="text-danger small"><?php echo $errores['fecha_fin']; ?></div><?php endif; ?>
    </div>
    <div class="col-12 d-flex justify-content-between align-items-center">
        <label class="form-label mb-0">Horarios por día</label>
        <button type="button" class="btn btn-outline-primary btn-sm" id="copiar-primer-dia">Copiar lunes a días activos</button>
    </div>
    <div class="col-12">
        <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Día</th>
                        <th>Trabaja</th>
                        <th>Entrada</th>
                        <th>Salida almuerzo</th>
                        <th>Entrada almuerzo</th>
                        <th>Salida</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $labels = [
                        'lunes' => 'Lunes',
                        'martes' => 'Martes',
                        'miercoles' => 'Miércoles',
                        'jueves' => 'Jueves',
                        'viernes' => 'Viernes',
                        'sabado' => 'Sábado',
                        'domingo' => 'Domingo'
                    ];
                    foreach (\App\Models\Turno::diasSemana() as $dia):
                        $horario = $turno->horariosPorDia[$dia] ?? [];
                        $activo = (bool) ($horario['activo'] ?? true);
                    ?>
                    <tr data-dia="<?php echo $dia; ?>" data-horario-row>
                        <td><?php echo $labels[$dia] ?? ucfirst($dia); ?></td>
                        <td>
                            <input type="hidden" name="horarios_por_dia[<?php echo $dia; ?>][activo]" value="0">
                            <div class="form-check form-switch m-0">
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    name="horarios_por_dia[<?php echo $dia; ?>][activo]"
                                    value="1"
                                    data-dia-toggle
                                    <?php echo $activo ? 'checked' : ''; ?>
                                >
                            </div>
                        </td>
                            <?php foreach (['hora_entrada', 'hora_salida_almuerzo', 'hora_retorno_almuerzo', 'hora_salida'] as $campo): ?>
                            <td>
                                <input
                                    type="time"
                                    step="60"
                                    name="horarios_por_dia[<?php echo $dia; ?>][<?php echo $campo; ?>]"
                                    data-hora-campo="<?php echo $campo; ?>"
                                    class="form-control form-control-sm"
                                    value="<?php echo htmlspecialchars($horario[$campo] ?? '00:00'); ?>"
                                >
                                <?php if (!empty($errores[$dia . '_' . $campo])): ?><div class="text-danger small"><?php echo $errores[$dia . '_' . $campo]; ?></div><?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <small class="text-muted">Si una hora queda vacía se guarda como 00:00. Para días no laborables, desactive "Trabaja".</small>
    </div>
    <?php if ($modoEdicion): ?>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($turno->id); ?>">
    <?php endif; ?>
    <div class="col-12">
        <button class="btn btn-success" type="submit"><?php echo $modoEdicion ? 'Guardar cambios' : 'Crear turno'; ?></button>
        <a href="<?php echo $baseUrl; ?>/index.php?route=turnos/list" class="btn btn-secondary">Volver al listado</a>
    </div>
    <?php if (!empty($errores['general'])): ?>
        <div class="col-12"><div class="alert alert-danger"><?php echo $errores['general']; ?></div></div>
    <?php endif; ?>
</form>

<script>
(function () {
    const form = document.getElementById('turno-form');
    if (!form) {
        return;
    }

    const hourFields = ['hora_entrada', 'hora_salida_almuerzo', 'hora_retorno_almuerzo', 'hora_salida'];

    function toggleRowState(row) {
        const toggle = row.querySelector('[data-dia-toggle]');
        const active = Boolean(toggle && toggle.checked);
        const inputs = row.querySelectorAll('input[type="time"]');

        inputs.forEach((input) => {
            input.disabled = !active;
            if (!active) {
                input.value = '00:00';
            } else if (!input.value) {
                input.value = '00:00';
            }
        });
    }

    form.querySelectorAll('[data-horario-row]').forEach((row) => {
        toggleRowState(row);
        const toggle = row.querySelector('[data-dia-toggle]');
        if (toggle) {
            toggle.addEventListener('change', function () {
                toggleRowState(row);
            });
        }
    });

    form.addEventListener('submit', function () {
        form.querySelectorAll('[data-horario-row]').forEach((row) => {
            const active = Boolean(row.querySelector('[data-dia-toggle]')?.checked);
            hourFields.forEach((field) => {
                const input = row.querySelector('[data-hora-campo="' + field + '"]');
                if (!input) {
                    return;
                }
                if (!active || !input.value) {
                    input.disabled = false;
                    input.value = '00:00';
                }
            });
        });
    });

    const copyButton = document.getElementById('copiar-primer-dia');
    if (!copyButton) {
        return;
    }

    copyButton.addEventListener('click', function () {
        const monday = form.querySelector('tr[data-dia="lunes"]');
        if (!monday) {
            return;
        }

        const sourceValues = {};
        hourFields.forEach((field) => {
            sourceValues[field] = monday.querySelector('[data-hora-campo="' + field + '"]')?.value || '00:00';
        });

        form.querySelectorAll('[data-horario-row]').forEach((row) => {
            if (row.dataset.dia === 'lunes') {
                return;
            }

            const active = Boolean(row.querySelector('[data-dia-toggle]')?.checked);
            if (!active) {
                return;
            }

            hourFields.forEach((field) => {
                const input = row.querySelector('[data-hora-campo="' + field + '"]');
                if (input) {
                    input.value = sourceValues[field];
                }
            });
        });
    });
})();
</script>