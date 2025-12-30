<h2>Registro de Funcionarios</h2>
<p class="text-muted">Al enviar se calcula un neto estimado (IPS obrero) y se guarda en sesión para fines demostrativos.</p>
<?php if (!empty($errores)): ?>
    <div class="alert alert-danger">Revise los datos obligatorios.</div>
<?php endif; ?>
<form method="post" class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="form-label">Nombre completo</label>
        <input type="text" name="nombre" class="form-control" required>
        <?php if (!empty($errores['nombre'])): ?><div class="text-danger small"><?php echo $errores['nombre']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-6">
        <label class="form-label">Cargo</label>
        <input type="text" name="cargo" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Salario base</label>
        <input type="number" name="salario" class="form-control" min="0" step="0.01" required>
        <?php if (!empty($errores['salario'])): ?><div class="text-danger small"><?php echo $errores['salario']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label">Fecha de ingreso</label>
        <input type="date" name="fecha_ingreso" class="form-control" required>
    </div>
    <div class="col-md-4">
        <label class="form-label">Empresa (ID)</label>
        <input type="number" name="empresa_id" class="form-control" min="1" required>
        <?php if (!empty($errores['empresa_id'])): ?><div class="text-danger small"><?php echo $errores['empresa_id']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-4">
        <label class="form-label">Bonificación</label>
        <input type="number" name="bonificacion" class="form-control" value="0" step="0.01">
    </div>
    <div class="col-md-4">
        <label class="form-label">Horas extra</label>
        <input type="number" name="horas_extra" class="form-control" value="0" step="0.01">
    </div>
    <div class="col-md-4">
        <label class="form-label">IPS aporte obrero (%)</label>
        <input type="number" name="tasa_ips_obrero" class="form-control" value="0.09" step="0.001">
    </div>
    <div class="col-12">
        <button class="btn btn-success" type="submit">Guardar y calcular</button>
    </div>
</form>

<?php if ($resumen): ?>
    <div class="alert alert-info">
        <strong>Resumen estimado:</strong> Bruto <?php echo number_format($resumen['gross'], 0, ',', '.'); ?> - IPS <?php echo number_format($resumen['ips'], 0, ',', '.'); ?> = Neto <?php echo number_format($resumen['net'], 0, ',', '.'); ?>
    </div>
<?php endif; ?>

<h5>Funcionarios cargados en sesión</h5>
<table class="table table-striped">
    <thead>
        <tr><th>Nombre</th><th>Cargo</th><th>Salario</th><th>Empresa</th></tr>
    </thead>
    <tbody>
    <?php foreach ($funcionarios as $funcionario): ?>
        <tr>
            <td><?php echo htmlspecialchars($funcionario->nombre); ?></td>
            <td><?php echo htmlspecialchars($funcionario->cargo); ?></td>
            <td><?php echo number_format($funcionario->salario, 0, ',', '.'); ?></td>
            <td><?php echo htmlspecialchars($funcionario->empresaId); ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($funcionarios)): ?>
        <tr><td colspan="4" class="text-muted">Sin registros aún.</td></tr>
    <?php endif; ?>
    </tbody>
</table>