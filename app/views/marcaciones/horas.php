<?php
$errores = $errores ?? [];
$funcionarios = $funcionarios ?? [];
$funcionarioSeleccionado = $funcionarioSeleccionado ?? null;
$fechaInicio = $fechaInicio ?? '';
$fechaFin = $fechaFin ?? '';
$horasPorDia = $horasPorDia ?? [];
$diasPeriodo = $diasPeriodo ?? [];
$feriados = $feriados ?? [];
$mensaje = $mensaje ?? null;
$totalMinutosPeriodo = (int) ($totalMinutosPeriodo ?? 0);
$funcionarioLabel = '';

if ($funcionarioSeleccionado) {
    $funcionarioLabel = sprintf(
        '%s - Doc: %s - ID reloj: %s',
        $funcionarioSeleccionado->nombre,
        $funcionarioSeleccionado->nroDocumento,
        $funcionarioSeleccionado->nroIdReloj
    );
}


function minutosAHoras(int $minutos): string
{
    $horas = intdiv(max(0, $minutos), 60);
    $resto = max(0, $minutos) % 60;

    return sprintf('%02d:%02d', $horas, $resto);
}

function calcularMinutosRegistro(array $registro): int
{
    $segmentos = [
        [$registro['entrada'] ?? null, $registro['salida_almuerzo'] ?? null],
        [$registro['entrada_almuerzo'] ?? null, $registro['salida'] ?? null]
    ];

    $total = 0;
    foreach ($segmentos as [$inicio, $fin]) {
        if (!$inicio || !$fin) {
            continue;
        }
        $inicioObj = DateTime::createFromFormat('H:i', $inicio);
        $finObj = DateTime::createFromFormat('H:i', $fin);
        if (!$inicioObj || !$finObj) {
            continue;
        }
        $minutos = (int) round(($finObj->getTimestamp() - $inicioObj->getTimestamp()) / 60);
        $total += max(0, $minutos);
    }

    return $total;
}

$nombreDias = [
    0 => 'Domingo',
    1 => 'Lunes',
    2 => 'Martes',
    3 => 'Miércoles',
    4 => 'Jueves',
    5 => 'Viernes',
    6 => 'Sábado'
];
?>

<h2>Horas importadas por funcionario</h2>
<p class="text-muted">Filtre por funcionario y rango de fechas para ver las marcaciones diarias.</p>
<?php if ($mensaje): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<form method="get" class="row g-3 mb-4">
    <input type="hidden" name="route" value="marcaciones/horas">
    <input type="hidden" name="funcionario_id" id="funcionario_id" value="<?php echo htmlspecialchars($funcionarioSeleccionado->id ?? ''); ?>">
    <div class="col-md-6">
        <label for="filtro-funcionario" class="form-label">Buscar funcionario</label>
        <input
            type="search"
            id="filtro-funcionario"
            class="form-control"
            placeholder="Escriba nombre, documento o ID reloj"
            autocomplete="off"
            value="<?php echo htmlspecialchars($funcionarioLabel); ?>"
            required
        >
        <div class="form-text">Solo se listan funcionarios con ID de reloj cargado.</div>
        <?php if (!empty($errores['funcionario_id'])): ?>
            <div class="text-danger small mt-1"><?php echo htmlspecialchars($errores['funcionario_id']); ?></div>
        <?php endif; ?>
        <div id="lista-funcionarios" class="list-group mt-2" style="max-height: 220px; overflow-y: auto; display: none;"></div>
    </div>
    <div class="col-md-3">
        <label for="fecha_inicio" class="form-label">Fecha inicial</label>
        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?php echo htmlspecialchars($fechaInicio); ?>" required>
    </div>
    <div class="col-md-3">
        <label for="fecha_fin" class="form-label">Fecha final</label>
        <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="<?php echo htmlspecialchars($fechaFin); ?>" required>
    </div>
    <div class="col-12">
        <?php if (!empty($errores['fecha'])): ?>
            <div class="text-danger small mb-2"><?php echo htmlspecialchars($errores['fecha']); ?></div>
        <?php endif; ?>
        <button type="submit" class="btn btn-primary">Filtrar</button>
    </div>
</form>

<?php if ($funcionarioSeleccionado && $fechaInicio && $fechaFin && empty($errores)): ?>
    <div class="card">
        <div class="card-body">
            <h5 class="card-title mb-3">Marcaciones de <?php echo htmlspecialchars($funcionarioSeleccionado->nombre); ?></h5>
            <?php if (!empty($errores['horas'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($errores['horas']); ?></div>
            <?php endif; ?>
            <?php if (empty($diasPeriodo)): ?>
                <p class="text-muted mb-0">No hay marcaciones importadas en el rango seleccionado.</p>
            <?php else: ?>
                <form method="post" class="table-responsive">
                    <input type="hidden" name="route" value="marcaciones/horas">
                    <input type="hidden" name="funcionario_id" value="<?php echo htmlspecialchars((string) $funcionarioSeleccionado->id); ?>">
                    <input type="hidden" name="fecha_inicio" value="<?php echo htmlspecialchars($fechaInicio); ?>">
                    <input type="hidden" name="fecha_fin" value="<?php echo htmlspecialchars($fechaFin); ?>">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>Día</th>
                                <th>Fecha</th>
                                <th>Entrada</th>
                                <th>Salida almuerzo</th>
                                <th>Entrada almuerzo</th>
                                <th>Salida</th>
                                <th>Total horas día</th>
                                <th>Aplicar</th>
                                <th>Observación</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($diasPeriodo as $dia): ?>
                                <?php
                                $fechaKey = $dia->format('Y-m-d');
                                $registro = $horasPorDia[$fechaKey] ?? null;
                                $diaSemana = (int) $dia->format('w');
                                $esFinDeSemana = $diaSemana === 0 || $diaSemana === 6;
                                $feriado = $feriados[$fechaKey] ?? null;
                                $observacion = '';

                                if ($feriado) {
                                    $observacion = 'Feriado: ' . $feriado->descripcion;
                                } elseif ($esFinDeSemana) {
                                    $observacion = 'Fin de semana';
                                } elseif (!$registro) {
                                    $observacion = 'No trabajado';
                                }
                                $minutosDia = $registro ? calcularMinutosRegistro($registro) : 0;
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($nombreDias[$diaSemana] ?? ''); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($fechaKey); ?>
                                        <input type="hidden" name="dias[]" value="<?php echo htmlspecialchars($fechaKey); ?>">
                                    </td>
                                    <td>
                                        <input type="time" class="form-control form-control-sm" name="entrada[<?php echo htmlspecialchars($fechaKey); ?>]" value="<?php echo htmlspecialchars($registro['entrada'] ?? ''); ?>">
                                    </td>
                                    <td>
                                        <input type="time" class="form-control form-control-sm" name="salida_almuerzo[<?php echo htmlspecialchars($fechaKey); ?>]" value="<?php echo htmlspecialchars($registro['salida_almuerzo'] ?? ''); ?>">
                                    </td>
                                    <td>
                                        <input type="time" class="form-control form-control-sm" name="entrada_almuerzo[<?php echo htmlspecialchars($fechaKey); ?>]" value="<?php echo htmlspecialchars($registro['entrada_almuerzo'] ?? ''); ?>">
                                    </td>
                                    <td>
                                        <input type="time" class="form-control form-control-sm" name="salida[<?php echo htmlspecialchars($fechaKey); ?>]" value="<?php echo htmlspecialchars($registro['salida'] ?? ''); ?>">
                                    </td>
                                    <td class="fw-semibold text-nowrap"><?php echo htmlspecialchars(minutosAHoras($minutosDia)); ?></td>
                                    <td class="text-center">
                                        <?php $aplicarMarcacion = $registro['aplicar'] ?? true; ?>
                                        <input class="form-check-input" type="checkbox" name="aplicar[<?php echo htmlspecialchars($fechaKey); ?>]" <?php echo $aplicarMarcacion ? 'checked' : ''; ?>>
                                    </td>
                                    <td>
                                        <?php if ($observacion): ?>
                                            <span class="badge bg-secondary"><?php echo htmlspecialchars($observacion); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">--</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr>
                                <th colspan="6" class="text-end">Total horas del rango:</th>
                                <th class="text-nowrap"><?php echo htmlspecialchars(minutosAHoras($totalMinutosPeriodo)); ?></th>
                                <th colspan="2"></th>
                            </tr>
                        </tfoot>
                    </table>
                <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-success">Actualizar horas</button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<script>
    const funcionarios = <?php
        echo json_encode(array_map(function ($funcionario) {
            return [
                'id' => $funcionario->id,
                'label' => sprintf(
                    '%s - Doc: %s - ID reloj: %s',
                    $funcionario->nombre,
                    $funcionario->nroDocumento,
                    $funcionario->nroIdReloj
                ),
                'search' => strtolower(sprintf(
                    '%s %s %s',
                    $funcionario->nombre,
                    $funcionario->nroDocumento,
                    $funcionario->nroIdReloj
                ))
            ];
        }, $funcionarios));
    ?>;

    const formulario = document.querySelector('form');
    const inputBusqueda = document.getElementById('filtro-funcionario');
    const inputFuncionarioId = document.getElementById('funcionario_id');
    const lista = document.getElementById('lista-funcionarios');

    const renderLista = (termino = '') => {
        const texto = termino.toLowerCase().trim();
        const resultados = funcionarios.filter((funcionario) => {
            return texto === '' || funcionario.search.includes(texto);
        });

        lista.innerHTML = '';
        resultados.forEach((funcionario) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'list-group-item list-group-item-action';
            item.textContent = funcionario.label;
            item.addEventListener('click', () => {
                inputBusqueda.value = funcionario.label;
                inputFuncionarioId.value = funcionario.id;
                lista.style.display = 'none';
            });
            lista.appendChild(item);
        });

        lista.style.display = resultados.length ? 'block' : 'none';
    };

    inputBusqueda.addEventListener('input', () => {
        inputFuncionarioId.value = '';
        renderLista(inputBusqueda.value);
    });

    inputBusqueda.addEventListener('focus', () => {
        renderLista(inputBusqueda.value);
    });

    document.addEventListener('click', (event) => {
        if (!lista.contains(event.target) && event.target !== inputBusqueda) {
            lista.style.display = 'none';
        }
    });

    formulario.addEventListener('submit', (event) => {
        if (!inputFuncionarioId.value) {
            const texto = inputBusqueda.value.toLowerCase().trim();
            const coincidencias = funcionarios.filter((funcionario) => {
                return funcionario.label.toLowerCase() === texto;
            });
            if (coincidencias.length === 1) {
                inputFuncionarioId.value = coincidencias[0].id;
                inputBusqueda.setCustomValidity('');
                return;
            }
            inputBusqueda.setCustomValidity('Seleccione un funcionario de la lista.');
            inputBusqueda.reportValidity();
            event.preventDefault();
        } else {
            inputBusqueda.setCustomValidity('');
        }
    });
</script>