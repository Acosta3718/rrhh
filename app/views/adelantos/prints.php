<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Impresión de adelantos</h2>
    <a href="<?php echo $baseUrl; ?>/index.php?route=adelantos/list" class="btn btn-outline-secondary">Volver al listado</a>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Imprimir por empresa</h5>
                <p class="text-muted">Genera la impresión de todos los adelantos de una empresa para un período.</p>
                <form method="get" action="<?php echo $baseUrl; ?>/index.php" target="_blank" class="row g-3">
                    <input type="hidden" name="route" value="adelantos/print-company">
                    <div class="col-12">
                        <label class="form-label">Buscar empresa</label>
                        <input type="text" class="form-control mb-2" placeholder="Escriba para filtrar..." data-filter-target="empresa_id_general">
                        <label class="form-label">Empresa *</label>
                        <select name="empresa_id" id="empresa_id_general" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($empresas as $empresa): ?>
                                <option value="<?php echo $empresa->id; ?>"><?php echo htmlspecialchars($empresa->razonSocial); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mes *</label>
                        <select name="mes" class="form-select" required>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo (int) date('n') === $m ? 'selected' : ''; ?>>
                                    <?php echo $m; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Año *</label>
                        <input type="number" name="anio" class="form-control" value="<?php echo date('Y'); ?>" max="<?php echo date('Y'); ?>" required>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Imprimir empresa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Imprimir por funcionario</h5>
                <p class="text-muted">Seleccione la empresa y luego el funcionario para imprimir su adelanto.</p>
                <form method="get" action="<?php echo $baseUrl; ?>/index.php" target="_blank" class="row g-3">
                    <input type="hidden" name="route" value="adelantos/print-individual">
                    <div class="col-12">
                        <label class="form-label">Buscar empresa</label>
                        <input type="text" class="form-control mb-2" placeholder="Escriba para filtrar..." data-filter-target="empresa_id_individual">
                        <label class="form-label">Empresa *</label>
                        <select name="empresa_id" id="empresa_id_individual" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($empresas as $empresa): ?>
                                <option value="<?php echo $empresa->id; ?>"><?php echo htmlspecialchars($empresa->razonSocial); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Buscar funcionario</label>
                        <input type="text" class="form-control mb-2" placeholder="Escriba para filtrar..." data-filter-target="funcionario_id_individual">
                        <label class="form-label">Funcionario *</label>
                        <select name="funcionario_id" id="funcionario_id_individual" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($funcionarios as $funcionario): ?>
                                <option value="<?php echo $funcionario->id; ?>" data-empresa="<?php echo $funcionario->empresaId; ?>">
                                    <?php echo htmlspecialchars($funcionario->nombre); ?> (<?php echo htmlspecialchars($funcionario->empresaNombre ?? ''); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Mes *</label>
                        <select name="mes" class="form-select" required>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?php echo $m; ?>" <?php echo (int) date('n') === $m ? 'selected' : ''; ?>>
                                    <?php echo $m; ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Año *</label>
                        <input type="number" name="anio" class="form-control" value="<?php echo date('Y'); ?>" max="<?php echo date('Y'); ?>" required>
                    </div>
                    <div class="col-12 form-check">
                        <input class="form-check-input" type="checkbox" name="duplicado" value="1" id="duplicado_individual">
                        <label class="form-check-label" for="duplicado_individual">Imprimir original y duplicado</label>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success">Imprimir funcionario</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const filterInputs = document.querySelectorAll('[data-filter-target]');
const empresaSelectIndividual = document.getElementById('empresa_id_individual');
const funcionarioSelect = document.getElementById('funcionario_id_individual');

function filtrarOpciones(input) {
    const selectId = input.getAttribute('data-filter-target');
    const select = document.getElementById(selectId);
    if (!select) return;
    const termino = input.value.toLowerCase();
    Array.from(select.options).forEach(option => {
        if (!option.value) return;
        const texto = option.textContent.toLowerCase();
        const coincideBusqueda = texto.includes(termino);
        if (selectId === 'funcionario_id_individual') {
            const empresaId = empresaSelectIndividual.value;
            const coincideEmpresa = !empresaId || option.dataset.empresa === empresaId;
            option.hidden = !(coincideBusqueda && coincideEmpresa);
        } else {
            option.hidden = !coincideBusqueda;
        }
    });
}

function filtrarFuncionariosPorEmpresa() {
    const empresaId = empresaSelectIndividual.value;
    Array.from(funcionarioSelect.options).forEach(option => {
        if (!option.value) return;
        const coincideEmpresa = !empresaId || option.dataset.empresa === empresaId;
        option.hidden = !coincideEmpresa;
    });
    if (funcionarioSelect.value && funcionarioSelect.selectedOptions[0]?.hidden) {
        funcionarioSelect.value = '';
    }
}

filterInputs.forEach(input => {
    input.addEventListener('input', () => filtrarOpciones(input));
});

empresaSelectIndividual?.addEventListener('change', () => {
    filtrarFuncionariosPorEmpresa();
    const input = document.querySelector('[data-filter-target="funcionario_id_individual"]');
    if (input) {
        filtrarOpciones(input);
    }
});
document.addEventListener('DOMContentLoaded', filtrarFuncionariosPorEmpresa);
</script>