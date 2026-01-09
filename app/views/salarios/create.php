<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Generar salarios</h2>
    <a href="<?php echo $baseUrl; ?>/index.php?route=salarios/list" class="btn btn-outline-secondary">Volver al listado</a>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Generar por empresa</h5>
                <p class="text-muted">Crea salarios para todos los funcionarios activos de la empresa usando el salario cargado en cada ficha. Se descuenta adelanto del período e IPS si corresponde.</p>
                <form method="post" class="row g-3">
                    <input type="hidden" name="modo" value="empresa">
                    <div class="col-12">
                        <label class="form-label">Buscar empresa</label>
                        <input type="text" class="form-control mb-2" placeholder="Escriba para filtrar..." data-filter-target="empresa_id">
                        <label class="form-label">Empresa *</label>
                        <select name="empresa_id" id="empresa_id" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($empresas as $empresa): ?>
                                <option value="<?php echo $empresa->id; ?>"><?php echo htmlspecialchars($empresa->razonSocial); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($erroresEmpresa['empresa_id'])): ?><div class="text-danger small"><?php echo $erroresEmpresa['empresa_id']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mes *</label>
                        <select name="mes" class="form-select" required>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo (int)date('n') === $m ? 'selected' : ''; ?>>
                                    <?php echo $m; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <?php if (!empty($erroresEmpresa['mes'])): ?><div class="text-danger small"><?php echo $erroresEmpresa['mes']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Año *</label>
                        <input type="number" name="anio" class="form-control" value="<?php echo date('Y'); ?>" max="<?php echo date('Y'); ?>" required>
                        <?php if (!empty($erroresEmpresa['anio'])): ?><div class="text-danger small"><?php echo $erroresEmpresa['anio']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success">Generar para toda la empresa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Generar individual</h5>
                <form method="post" class="row g-3">
                    <input type="hidden" name="modo" value="individual">
                    <div class="col-12">
                        <label class="form-label">Buscar funcionario</label>
                        <input type="text" class="form-control mb-2" placeholder="Escriba para filtrar..." data-filter-target="funcionario_id">
                        <label class="form-label">Funcionario *</label>
                        <select name="funcionario_id" id="funcionario_id" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($funcionarios as $funcionario): ?>
                                <option
                                    value="<?php echo $funcionario->id; ?>"
                                    data-salario="<?php echo htmlspecialchars($funcionario->salario); ?>"
                                    data-ips="<?php echo $funcionario->tieneIps ? '1' : '0'; ?>"
                                    data-empresa="<?php echo htmlspecialchars($funcionario->empresaNombre ?? ''); ?>"
                                >
                                    <?php echo htmlspecialchars($funcionario->nombre); ?> (<?php echo htmlspecialchars($funcionario->empresaNombre ?? ''); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (!empty($erroresIndividual['funcionario_id'])): ?><div class="text-danger small"><?php echo $erroresIndividual['funcionario_id']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mes *</label>
                        <select name="mes" class="form-select" required>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo (int)date('n') === $m ? 'selected' : ''; ?>>
                                    <?php echo $m; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                        <?php if (!empty($erroresIndividual['mes'])): ?><div class="text-danger small"><?php echo $erroresIndividual['mes']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Año *</label>
                        <input type="number" name="anio" class="form-control" value="<?php echo date('Y'); ?>" max="<?php echo date('Y'); ?>" required>
                        <?php if (!empty($erroresIndividual['anio'])): ?><div class="text-danger small"><?php echo $erroresIndividual['anio']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-12">
                        <div class="form-text" id="salario-help">
                            Se tomará el salario de la ficha del funcionario, se descontará el adelanto generado en el período y el IPS obrero (<?php echo number_format($aporteObrero * 100, 2, ',', '.'); ?>%) si corresponde.
                        </div>
                        <?php if (!empty($erroresIndividual['periodo'])): ?><div class="text-danger small"><?php echo $erroresIndividual['periodo']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Generar salario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const filterInputs = document.querySelectorAll('[data-filter-target]');
const funcionarioSelect = document.getElementById('funcionario_id');
const helpText = document.getElementById('salario-help');
const aporteObrero = <?php echo json_encode($aporteObrero); ?>;

function filtrarOpciones(input) {
    const selectId = input.getAttribute('data-filter-target');
    const select = document.getElementById(selectId);
    if (!select) return;
    const termino = input.value.toLowerCase();
    Array.from(select.options).forEach(option => {
        if (!option.value) return;
        const texto = option.textContent.toLowerCase();
        option.hidden = !texto.includes(termino);
    });
}

function actualizarDetalle() {
    const option = funcionarioSelect?.selectedOptions[0];
    if (!option || !helpText) return;
    const salario = option.dataset.salario;
    const empresa = option.dataset.empresa;
    const tieneIps = option.dataset.ips === '1';
    if (salario) {
        const porcentajeIps = tieneIps ? `${(aporteObrero * 100).toFixed(2)}%` : '0%';
        helpText.textContent = `Salario base: ${salario}. Empresa: ${empresa}. IPS obrero aplicado: ${porcentajeIps}. El adelanto del período se descuenta automáticamente.`;
    }
}

filterInputs.forEach(input => {
    input.addEventListener('input', () => filtrarOpciones(input));
});

funcionarioSelect?.addEventListener('change', actualizarDetalle);
document.addEventListener('DOMContentLoaded', actualizarDetalle);
</script>