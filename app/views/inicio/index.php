<div class="row">
    <div class="col-12 mb-3">
        <h1>Arquitectura MVC para RRHH</h1>
        <p class="text-muted">Base inicial en PHP con Bootstrap 5 y MySQL.</p>
        <div class="alert <?php echo $conexionActiva ? 'alert-success' : 'alert-warning'; ?>">
            <?php echo $conexionActiva ? 'Conexión a base de datos configurada.' : 'Configure config/config.php para conectar a MySQL.'; ?>
        </div>
    </div>
</div>
<div class="row g-3">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Registro de empresas</h5>
                <p class="card-text">Carga de razón social, RUC, contacto y teléfonos.</p>
                <a href="<?php echo $baseUrl; ?>/index.php?route=empresas/create" class="btn btn-primary">Abrir módulo</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Funcionarios</h5>
                <p class="card-text">Registro y cálculo preliminar de salario neto, IPS y aguinaldo.</p>
                <a href="<?php echo $baseUrl; ?>/index.php?route=funcionarios/create" class="btn btn-primary">Abrir módulo</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Nómina y parámetros</h5>
                <p class="card-text">Vista rápida de parámetros IPS, vacaciones por antigüedad y ejemplos de aguinaldo.</p>
                <a href="<?php echo $baseUrl; ?>/index.php?route=nomina/overview" class="btn btn-primary">Abrir módulo</a>
            </div>
        </div>
    </div>
</div>