<h2>Nómina: parámetros y cálculos de ejemplo</h2>
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Aporte IPS</h6>
                <p class="mb-1">Obrero: <?php echo $tasasIps['aporte_obrero'] * 100; ?>%</p>
                <p class="mb-0">Patronal: <?php echo $tasasIps['aporte_patronal'] * 100; ?>%</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Vacaciones por antigüedad</h6>
                <p class="mb-0">Tenés <?php echo $diasVacaciones; ?> días para <?php echo $aniosAntiguedad; ?> años.</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <h6 class="card-title">Aguinaldo estimado</h6>
                <p class="mb-0">Gs. <?php echo number_format($aguinaldo, 0, ',', '.'); ?></p>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">Resumen de salario de ejemplo</div>
    <div class="card-body">
        <p class="mb-1">Bruto: Gs. <?php echo number_format($salarioNeto['gross'], 0, ',', '.'); ?></p>
        <p class="mb-1">IPS obrero: Gs. <?php echo number_format($salarioNeto['ips'], 0, ',', '.'); ?></p>
        <p class="fw-bold">Neto: Gs. <?php echo number_format($salarioNeto['net'], 0, ',', '.'); ?></p>
        <p class="text-muted small mb-0">Integra en este mismo módulo liquidaciones, adelantos, préstamos y vacaciones extendiendo CalculadoraNomina y agregando tablas específicas.</p>
    </div>
</div>