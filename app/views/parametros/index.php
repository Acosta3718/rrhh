<?php
$valores = [
    'salario_minimo' => $parametros?->salarioMinimo ?? '',
    'mayoria_edad' => $parametros?->mayoriaEdad ?? '',
    'aporte_obrero' => $parametros?->aporteObrero ?? '',
    'aporte_patronal' => $parametros?->aportePatronal ?? '',
    'vacaciones10' => $parametros?->vacaciones10 ?? '',
    'vacaciones5' => $parametros?->vacaciones5 ?? '',
    'vacaciones1' => $parametros?->vacaciones1 ?? ''
];
?>

<div class="row">
    <div class="col-12 mb-3">
        <h1>Parámetros generales</h1>
        <p class="text-muted">Configura los valores base para nómina, IPS y vacaciones.</p>
        <?php if (!empty($mensaje)) : ?>
            <div class="alert alert-success"><?php echo $mensaje; ?></div>
        <?php endif; ?>
        <?php if (!empty($errores['general'])) : ?>
            <div class="alert alert-danger"><?php echo $errores['general']; ?></div>
        <?php endif; ?>
    </div>
</div>

<form method="post" class="row g-3">
    <div class="col-md-4">
        <label class="form-label" for="salario_minimo">Salario mínimo</label>
        <input type="number" step="0.01" min="0" class="form-control" id="salario_minimo" name="salario_minimo" value="<?php echo htmlspecialchars((string) $valores['salario_minimo']); ?>">
        <?php if (!empty($errores['salario_minimo'])) : ?>
            <div class="text-danger small"><?php echo $errores['salario_minimo']; ?></div>
        <?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="mayoria_edad">Mayoría de edad</label>
        <input type="number" min="0" class="form-control" id="mayoria_edad" name="mayoria_edad" value="<?php echo htmlspecialchars((string) $valores['mayoria_edad']); ?>">
        <?php if (!empty($errores['mayoria_edad'])) : ?>
            <div class="text-danger small"><?php echo $errores['mayoria_edad']; ?></div>
        <?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="aporte_obrero">Aporte obrero</label>
        <input type="number" step="0.0001" min="0" class="form-control" id="aporte_obrero" name="aporte_obrero" value="<?php echo htmlspecialchars((string) $valores['aporte_obrero']); ?>">
        <?php if (!empty($errores['aporte_obrero'])) : ?>
            <div class="text-danger small"><?php echo $errores['aporte_obrero']; ?></div>
        <?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="aporte_patronal">Aporte patronal</label>
        <input type="number" step="0.0001" min="0" class="form-control" id="aporte_patronal" name="aporte_patronal" value="<?php echo htmlspecialchars((string) $valores['aporte_patronal']); ?>">
        <?php if (!empty($errores['aporte_patronal'])) : ?>
            <div class="text-danger small"><?php echo $errores['aporte_patronal']; ?></div>
        <?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="vacaciones10">Vacaciones 10 años</label>
        <input type="number" min="0" class="form-control" id="vacaciones10" name="vacaciones10" value="<?php echo htmlspecialchars((string) $valores['vacaciones10']); ?>">
        <?php if (!empty($errores['vacaciones10'])) : ?>
            <div class="text-danger small"><?php echo $errores['vacaciones10']; ?></div>
        <?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="vacaciones5">Vacaciones 5 años</label>
        <input type="number" min="0" class="form-control" id="vacaciones5" name="vacaciones5" value="<?php echo htmlspecialchars((string) $valores['vacaciones5']); ?>">
        <?php if (!empty($errores['vacaciones5'])) : ?>
            <div class="text-danger small"><?php echo $errores['vacaciones5']; ?></div>
        <?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label" for="vacaciones1">Vacaciones 1 año</label>
        <input type="number" min="0" class="form-control" id="vacaciones1" name="vacaciones1" value="<?php echo htmlspecialchars((string) $valores['vacaciones1']); ?>">
        <?php if (!empty($errores['vacaciones1'])) : ?>
            <div class="text-danger small"><?php echo $errores['vacaciones1']; ?></div>
        <?php endif; ?>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-primary">
            <?php echo $parametros?->id ? 'Actualizar parámetros' : 'Guardar parámetros'; ?>
        </button>
    </div>
</form>