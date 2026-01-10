<h2><?php echo $modoEdicion ? 'Editar funcionario' : 'Registrar funcionario'; ?></h2>
<p class="text-muted">Complete los datos obligatorios marcados con *. Se valida que el documento sea único y que la fecha de nacimiento no supere el día de hoy.</p>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>
<?php if (!empty($errores)): ?>
    <div class="alert alert-danger">Revise los campos obligatorios y las validaciones.</div>
<?php endif; ?>

<?php
$edad = null;
if (!empty($funcionario?->fechaNacimiento)) {
    $hoy = new DateTime('today');
    $edad = $funcionario->fechaNacimiento->diff($hoy)->y;
}
$empresaSeleccionada = $funcionario?->empresaNombre ?? '';
if (!$empresaSeleccionada && !empty($funcionario?->empresaId)) {
    foreach ($empresas as $empresa) {
        if ($empresa->id === $funcionario->empresaId) {
            $empresaSeleccionada = $empresa->razonSocial;
            break;
        }
    }
}
?>

<form method="post" class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="form-label">Nombre completo *</label>
        <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($funcionario->nombre ?? ''); ?>" required>
        <?php if (!empty($errores['nombre'])): ?><div class="text-danger small"><?php echo $errores['nombre']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-6">
        <label class="form-label">Cargo</label>
        <input type="text" name="cargo" class="form-control" value="<?php echo htmlspecialchars($funcionario->cargo ?? ''); ?>">
    </div>
    <div class="col-md-4">
        <label class="form-label">Nro. de documento *</label>
        <input type="text" name="nro_documento" class="form-control" value="<?php echo htmlspecialchars($funcionario->nroDocumento ?? ''); ?>" required>
        <?php if (!empty($errores['nro_documento'])): ?><div class="text-danger small"><?php echo $errores['nro_documento']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label">Nacionalidad *</label>
        <select name="nacionalidad_id" class="form-select" required>
            <option value="">Seleccione...</option>
            <?php foreach ($nacionalidades as $nacionalidad): ?>
                <option value="<?php echo $nacionalidad->id; ?>" <?php echo ($funcionario?->nacionalidadId === $nacionalidad->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($nacionalidad->nombre); ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (!empty($errores['nacionalidad_id'])): ?><div class="text-danger small"><?php echo $errores['nacionalidad_id']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label">Estado civil *</label>
        <div class="d-flex gap-3">
            <?php $estado = $funcionario->estadoCivil ?? 'soltero'; ?>
            <?php foreach (['casado' => 'Casado', 'soltero' => 'Soltero', 'divorciado' => 'Divorciado', 'separado' => 'Separado'] as $valor => $label): ?>
                <div class="form-check">
                    <input class="form-check-input" type="radio" name="estado_civil" id="estado_<?php echo $valor; ?>" value="<?php echo $valor; ?>" <?php echo $estado === $valor ? 'checked' : ''; ?> required>
                    <label class="form-check-label" for="estado_<?php echo $valor; ?>"><?php echo $label; ?></label>
                </div>
            <?php endforeach; ?>
        </div>
        <?php if (!empty($errores['estado_civil'])): ?><div class="text-danger small"><?php echo $errores['estado_civil']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label">Fecha de nacimiento *</label>
        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="form-control" value="<?php echo htmlspecialchars($funcionario?->fechaNacimiento?->format('Y-m-d') ?? ''); ?>" required>
        <?php if (!empty($errores['fecha_nacimiento'])): ?><div class="text-danger small"><?php echo $errores['fecha_nacimiento']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-2">
        <label class="form-label">Edad</label>
        <input type="text" id="edad" class="form-control" value="<?php echo $edad !== null ? $edad . ' años' : ''; ?>" readonly>
    </div>
    <div class="col-md-6">
        <label class="form-label">Dirección *</label>
        <input type="text" name="direccion" class="form-control" value="<?php echo htmlspecialchars($funcionario->direccion ?? ''); ?>" required>
        <?php if (!empty($errores['direccion'])): ?><div class="text-danger small"><?php echo $errores['direccion']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label">Celular *</label>
        <input type="text" name="celular" class="form-control" value="<?php echo htmlspecialchars($funcionario->celular ?? ''); ?>" required>
        <?php if (!empty($errores['celular'])): ?><div class="text-danger small"><?php echo $errores['celular']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label">Salario *</label>
        <input type="number" name="salario" class="form-control" min="0" step="0.01" value="<?php echo htmlspecialchars($funcionario->salario ?? ''); ?>" required>
        <?php if (!empty($errores['salario'])): ?><div class="text-danger small"><?php echo $errores['salario']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label">Adelanto</label>
        <input type="number" name="adelanto" class="form-control" min="0" step="0.01" value="<?php echo htmlspecialchars($funcionario->adelanto ?? 0); ?>">
        <?php if (!empty($errores['adelanto'])): ?><div class="text-danger small"><?php echo $errores['adelanto']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label">Fecha de ingreso *</label>
        <input type="date" name="fecha_ingreso" class="form-control" value="<?php echo htmlspecialchars($funcionario?->fechaIngreso?->format('Y-m-d') ?? ''); ?>" required>
        <?php if (!empty($errores['fecha_ingreso'])): ?><div class="text-danger small"><?php echo $errores['fecha_ingreso']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label">Fecha de salida</label>
        <input type="date" name="fecha_salida" class="form-control" value="<?php echo htmlspecialchars($funcionario?->fechaSalida?->format('Y-m-d') ?? ''); ?>">
        <?php if (!empty($errores['fecha_salida'])): ?><div class="text-danger small"><?php echo $errores['fecha_salida']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label">Empresa *</label>
        <input
            type="text"
            id="empresa_search"
            class="form-control"
            list="empresa_list"
            placeholder="Buscar empresa..."
            value="<?php echo htmlspecialchars($empresaSeleccionada); ?>"
            required
        >
        <input type="hidden" name="empresa_id" id="empresa_id" value="<?php echo htmlspecialchars($funcionario?->empresaId ?? ''); ?>" required>
        <datalist id="empresa_list">
            <?php foreach ($empresas as $empresa): ?>
                <option value="<?php echo htmlspecialchars($empresa->razonSocial); ?>" data-id="<?php echo $empresa->id; ?>"></option>
            <?php endforeach; ?>
        </datalist>
        <?php if (!empty($errores['empresa_id'])): ?><div class="text-danger small"><?php echo $errores['empresa_id']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label">Estado *</label>
        <select name="estado" class="form-select" required>
            <?php $estadoActual = $funcionario->estado ?? 'activo'; ?>
            <option value="activo" <?php echo $estadoActual === 'activo' ? 'selected' : ''; ?>>Activo</option>
            <option value="inactivo" <?php echo $estadoActual === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
        </select>
        <?php if (!empty($errores['estado'])): ?><div class="text-danger small"><?php echo $errores['estado']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-8 align-self-end">
        <div class="d-flex flex-wrap gap-4">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="tiene_ips" id="tiene_ips" <?php echo !empty($funcionario?->tieneIps) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="tiene_ips">Tiene IPS</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="calcula_ips_total" id="calcula_ips_total" <?php echo !empty($funcionario?->calculaIpsTotal) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="calcula_ips_total">Calcula IPS por el total</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="calcula_ips_minimo" id="calcula_ips_minimo" <?php echo !empty($funcionario?->calculaIpsMinimo) ? 'checked' : ''; ?>>
                <label class="form-check-label" for="calcula_ips_minimo">Calcula IPS por el mínimo</label>
            </div>
        </div>
        <?php if (!empty($errores['calculo_ips'])): ?><div class="text-danger small"><?php echo $errores['calculo_ips']; ?></div><?php endif; ?>
    </div>

    <?php if ($modoEdicion): ?>
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($funcionario->id); ?>">
    <?php endif; ?>
    <div class="col-12">
        <button class="btn btn-success" type="submit"><?php echo $modoEdicion ? 'Guardar cambios' : 'Guardar funcionario'; ?></button>
        <a href="<?php echo $baseUrl; ?>/index.php?route=funcionarios/list" class="btn btn-secondary">Volver al listado</a>
    </div>
    <?php if (!empty($errores['general'])): ?>
        <div class="col-12"><div class="alert alert-danger"><?php echo $errores['general']; ?></div></div>
    <?php endif; ?>
</form>

<script>
function calcularEdad() {
    const nacimiento = document.getElementById('fecha_nacimiento').value;
    const edadInput = document.getElementById('edad');
    if (!nacimiento) {
        edadInput.value = '';
        return;
    }

    const nacimientoDate = new Date(nacimiento + 'T00:00:00');
    const hoy = new Date();

    if (isNaN(nacimientoDate.getTime())) {
        edadInput.value = '';
        return;
    }

    let edad = hoy.getFullYear() - nacimientoDate.getFullYear();
    const mes = hoy.getMonth() - nacimientoDate.getMonth();
    if (mes < 0 || (mes === 0 && hoy.getDate() < nacimientoDate.getDate())) {
        edad--;
    }

    edadInput.value = edad >= 0 ? `${edad} años` : '';
}

window.addEventListener('DOMContentLoaded', calcularEdad);
document.getElementById('fecha_nacimiento').addEventListener('change', calcularEdad);

const empresaSearch = document.getElementById('empresa_search');
const empresaHidden = document.getElementById('empresa_id');
const empresaList = document.getElementById('empresa_list');
const form = document.querySelector('form');

function buscarEmpresa(valor) {
    if (!empresaList) {
        return null;
    }
    return Array.from(empresaList.options).find(option => option.value === valor) || null;
}

function sincronizarEmpresa() {
    const valor = empresaSearch.value.trim();
    const match = buscarEmpresa(valor);
    empresaHidden.value = match?.dataset.id || '';
    if (match) {
        empresaSearch.setCustomValidity('');
    } else {
        empresaSearch.setCustomValidity('Seleccione una empresa válida de la lista.');
    }
}

empresaSearch?.addEventListener('input', sincronizarEmpresa);
empresaSearch?.addEventListener('change', sincronizarEmpresa);

const tieneIpsInput = document.getElementById('tiene_ips');
const calculaIpsTotalInput = document.getElementById('calcula_ips_total');
const calculaIpsMinimoInput = document.getElementById('calcula_ips_minimo');

function actualizarIpsChecks() {
    const tieneIps = tieneIpsInput.checked;
    calculaIpsTotalInput.disabled = !tieneIps;
    calculaIpsMinimoInput.disabled = !tieneIps;
    if (!tieneIps) {
        calculaIpsTotalInput.checked = false;
        calculaIpsMinimoInput.checked = false;
    }
}

function manejarIpsExclusivo(event) {
    if (event.target === calculaIpsTotalInput && calculaIpsTotalInput.checked) {
        calculaIpsMinimoInput.checked = false;
    }
    if (event.target === calculaIpsMinimoInput && calculaIpsMinimoInput.checked) {
        calculaIpsTotalInput.checked = false;
    }
}

tieneIpsInput?.addEventListener('change', actualizarIpsChecks);
calculaIpsTotalInput?.addEventListener('change', manejarIpsExclusivo);
calculaIpsMinimoInput?.addEventListener('change', manejarIpsExclusivo);

document.addEventListener('DOMContentLoaded', () => {
    sincronizarEmpresa();
    actualizarIpsChecks();
});

form?.addEventListener('submit', (event) => {
    sincronizarEmpresa();
    if (!form.checkValidity()) {
        event.preventDefault();
        form.reportValidity();
    }
});
</script>