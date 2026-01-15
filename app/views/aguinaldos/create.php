<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Generar aguinaldos</h2>
    <a href="<?php echo $baseUrl; ?>/index.php?route=aguinaldos/list" class="btn btn-outline-secondary">Volver al listado</a>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Generar por empresa</h5>
                <p class="text-muted">Calcula el aguinaldo anual de todos los funcionarios activos con base en el total cobrado del año seleccionado.</p>
                <form method="post" class="row g-3">
                    <input type="hidden" name="modo" value="empresa">
                    <div class="col-12">
                        <label class="form-label">Buscar empresa</label>
                        <input type="text" class="form-control mb-2" placeholder="Escriba para buscar..." list="empresa_list" id="empresa_search" required>
                        <input type="hidden" name="empresa_id" id="empresa_id">
                        <datalist id="empresa_list">
                            <?php foreach ($empresas as $empresa): ?>
                                <option value="<?php echo htmlspecialchars($empresa->razonSocial); ?>" data-id="<?php echo $empresa->id; ?>"></option>
                            <?php endforeach; ?>
                        </datalist>
                        <?php if (!empty($erroresEmpresa['empresa_id'])): ?><div class="text-danger small"><?php echo $erroresEmpresa['empresa_id']; ?></div><?php endif; ?>
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
                <p class="text-muted">El monto se calcula como total cobrado en el año / 12.</p>
                <form method="post" class="row g-3">
                    <input type="hidden" name="modo" value="individual">
                    <div class="col-12">
                        <label class="form-label">Buscar funcionario</label>
                        <input type="text" class="form-control mb-2" placeholder="Escriba para buscar..." list="funcionario_list" id="funcionario_search" required>
                        <input type="hidden" name="funcionario_id" id="funcionario_id">
                        <datalist id="funcionario_list">
                            <?php foreach ($funcionarios as $funcionario): ?>
                                <option
                                    value="<?php echo htmlspecialchars($funcionario->nombre); ?> (<?php echo htmlspecialchars($funcionario->empresaNombre ?? ''); ?>)"
                                    data-id="<?php echo $funcionario->id; ?>"
                                >
                                </option>
                            <?php endforeach; ?>
                        </datalist>
                        <?php if (!empty($erroresIndividual['funcionario_id'])): ?><div class="text-danger small"><?php echo $erroresIndividual['funcionario_id']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Año *</label>
                        <input type="number" name="anio" class="form-control" value="<?php echo date('Y'); ?>" max="<?php echo date('Y'); ?>" required>
                        <?php if (!empty($erroresIndividual['anio'])): ?><div class="text-danger small"><?php echo $erroresIndividual['anio']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-12">
                        <?php if (!empty($erroresIndividual['monto'])): ?><div class="text-danger small"><?php echo $erroresIndividual['monto']; ?></div><?php endif; ?>
                        <?php if (!empty($erroresIndividual['periodo'])): ?><div class="text-danger small"><?php echo $erroresIndividual['periodo']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Generar aguinaldo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const empresaSearch = document.getElementById('empresa_search');
const empresaIdInput = document.getElementById('empresa_id');
const funcionarioSearch = document.getElementById('funcionario_search');
const funcionarioIdInput = document.getElementById('funcionario_id');
const empresaList = document.getElementById('empresa_list');
const funcionarioList = document.getElementById('funcionario_list');

function buscarOpcionPorValor(list, value) {
    if (!list || !value) return null;
    return Array.from(list.options).find(option => option.value === value) || null;
}

function actualizarEmpresaSeleccionada() {
    const option = buscarOpcionPorValor(empresaList, empresaSearch?.value);
    empresaIdInput.value = option?.dataset.id || '';
}

function actualizarFuncionarioSeleccionado() {
    const option = buscarOpcionPorValor(funcionarioList, funcionarioSearch?.value);
    funcionarioIdInput.value = option?.dataset.id || '';
}

empresaSearch?.addEventListener('input', actualizarEmpresaSeleccionada);
funcionarioSearch?.addEventListener('input', actualizarFuncionarioSeleccionado);
document.addEventListener('DOMContentLoaded', () => {
    actualizarEmpresaSeleccionada();
    actualizarFuncionarioSeleccionado();
});
</script>