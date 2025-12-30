<h2>Registro de Empresas</h2>
<p class="text-muted">Este formulario persiste en sesión para demostrar el flujo MVC; reemplácelo por inserciones MySQL usando el modelo Empresa.</p>
<?php if (!empty($errores)): ?>
    <div class="alert alert-danger">Revise los datos obligatorios.</div>
<?php endif; ?>
<form method="post" class="row g-3 mb-4">
    <div class="col-md-6">
        <label class="form-label">Razón social</label>
        <input type="text" name="razon_social" class="form-control" required>
        <?php if (!empty($errores['razon_social'])): ?><div class="text-danger small"><?php echo $errores['razon_social']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-6">
        <label class="form-label">RUC</label>
        <input type="text" name="ruc" class="form-control" required>
        <?php if (!empty($errores['ruc'])): ?><div class="text-danger small"><?php echo $errores['ruc']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-6">
        <label class="form-label">Correo</label>
        <input type="email" name="correo" class="form-control" required>
        <?php if (!empty($errores['correo'])): ?><div class="text-danger small"><?php echo $errores['correo']; ?></div><?php endif; ?>
    </div>
    <div class="col-md-6">
        <label class="form-label">Teléfono</label>
        <input type="text" name="telefono" class="form-control">
    </div>
    <div class="col-12">
        <button class="btn btn-success" type="submit">Guardar</button>
    </div>
</form>

<h5>Empresas cargadas en sesión</h5>
<table class="table table-striped">
    <thead>
        <tr><th>Razón social</th><th>RUC</th><th>Email</th><th>Teléfono</th></tr>
    </thead>
    <tbody>
    <?php foreach ($empresas as $empresa): ?>
        <tr>
            <td><?php echo htmlspecialchars($empresa->razonSocial); ?></td>
            <td><?php echo htmlspecialchars($empresa->ruc); ?></td>
            <td><?php echo htmlspecialchars($empresa->correo); ?></td>
            <td><?php echo htmlspecialchars($empresa->telefono); ?></td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($empresas)): ?>
        <tr><td colspan="4" class="text-muted">Sin registros aún.</td></tr>
    <?php endif; ?>
    </tbody>
</table>