<h2>Iniciar sesión</h2>
<p class="text-muted">Ingrese sus credenciales para acceder al sistema.</p>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>
<?php if (!empty($errores['general'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($errores['general']); ?></div>
<?php endif; ?>

<form method="post" class="card p-4" style="max-width: 480px;">
    <div class="mb-3">
        <label for="correo" class="form-label">Correo</label>
        <input type="email" name="correo" id="correo" class="form-control" required>
        <?php if (!empty($errores['correo'])): ?>
            <div class="text-danger small mt-1"><?php echo htmlspecialchars($errores['correo']); ?></div>
        <?php endif; ?>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Contraseña</label>
        <input type="password" name="password" id="password" class="form-control" required>
        <?php if (!empty($errores['password'])): ?>
            <div class="text-danger small mt-1"><?php echo htmlspecialchars($errores['password']); ?></div>
        <?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary">Ingresar</button>
</form>