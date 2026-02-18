<h2>Importar horas del reloj</h2>
<p class="text-muted">Seleccione el archivo Access exportado por el reloj marcador para importar las marcaciones.</p>

<?php
$fechaInicio = $fechaInicio ?? '';
$fechaFin = $fechaFin ?? '';
?>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>
<?php if (!empty($errores)): ?>
    <div class="alert alert-danger">Revise el archivo seleccionado.</div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" class="row g-3 mb-4">
    <div class="col-md-3">
        <label class="form-label" for="fecha_inicio">Fecha inicial (opcional)</label>
        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?php echo htmlspecialchars($fechaInicio); ?>">
    </div>
    <div class="col-md-3">
        <label class="form-label" for="fecha_fin">Fecha final (opcional)</label>
        <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="<?php echo htmlspecialchars($fechaFin); ?>">
    </div>
    <div class="col-md-6">
        <label class="form-label">Archivo Access (.mdb / .accdb)</label>
        <input type="file" name="archivo_access" class="form-control" accept=".mdb,.accdb,.access" required>
        <?php if (!empty($errores['archivo_access'])): ?><div class="text-danger small"><?php echo $errores['archivo_access']; ?></div><?php endif; ?>
    </div>
    <?php if (!empty($errores['fecha'])): ?>
        <div class="col-12 text-danger small"><?php echo htmlspecialchars($errores['fecha']); ?></div>
    <?php endif; ?>
    <div class="col-12">
        <button class="btn btn-success" type="submit">Importar marcaciones</button>
    </div>
</form>

<?php if (!empty($resultado)): ?>
    <div class="alert alert-info">
        <strong>Resultado:</strong>
        Se insertaron <?php echo (int) $resultado['insertados']; ?> registros.
        Se omitieron <?php echo (int) $resultado['omitidos']; ?> registros.
    </div>
<?php endif; ?>