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
                        <input
                            type="text"
                            class="form-control"
                            id="empresa_search_general"
                            placeholder="Escriba para buscar..."
                            list="empresa_list"
                            data-hidden-target="empresa_id_general"
                            data-list-target="empresa_list"
                            required
                        >
                        <input type="hidden" name="empresa_id" id="empresa_id_general" required>
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
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="duplicado" value="1" id="duplicado_empresa">
                            <label class="form-check-label" for="duplicado_empresa">Imprimir original y duplicado</label>
                        </div>
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
                        <input
                            type="text"
                            class="form-control"
                            id="empresa_search_individual"
                            placeholder="Escriba para buscar..."
                            list="empresa_list"
                            data-hidden-target="empresa_id_individual"
                            data-list-target="empresa_list"
                            required
                        >
                        <input type="hidden" name="empresa_id" id="empresa_id_individual" required>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Buscar funcionario</label>
                        <input
                            type="text"
                            class="form-control"
                            id="funcionario_search_individual"
                            placeholder="Escriba para buscar..."
                            list="funcionario_list"
                            data-hidden-target="funcionario_id_individual"
                            data-list-target="funcionario_list"
                            required
                        >
                        <input type="hidden" name="funcionario_id" id="funcionario_id_individual" required>
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

<datalist id="empresa_list">
    <?php foreach ($empresas as $empresa): ?>
        <option value="<?php echo htmlspecialchars($empresa->razonSocial); ?>" data-id="<?php echo $empresa->id; ?>"></option>
    <?php endforeach; ?>
</datalist>
<datalist id="funcionario_list">
    <?php foreach ($funcionarios as $funcionario): ?>
        <option
            value="<?php echo htmlspecialchars($funcionario->nombre); ?> (<?php echo htmlspecialchars($funcionario->empresaNombre ?? ''); ?>)"
            data-id="<?php echo $funcionario->id; ?>"
            data-empresa="<?php echo $funcionario->empresaId; ?>"
        ></option>
    <?php endforeach; ?>
</datalist>

<script>
const empresaList = document.getElementById('empresa_list');
const funcionarioList = document.getElementById('funcionario_list');
const empresaInputIndividual = document.getElementById('empresa_search_individual');
const funcionarioInput = document.getElementById('funcionario_search_individual');
const forms = document.querySelectorAll('form');

const funcionariosData = Array.from(funcionarioList?.options || []).map(option => ({
    value: option.value,
    id: option.dataset.id,
    empresa: option.dataset.empresa
}));

function buscarOpcion(list, value) {
    if (!list) return null;
    return Array.from(list.options).find(option => option.value === value) || null;
}

function sincronizarInput(input) {
    const hiddenId = input.dataset.hiddenTarget;
    const listId = input.dataset.listTarget;
    const hidden = document.getElementById(hiddenId);
    const list = document.getElementById(listId);
    const match = buscarOpcion(list, input.value.trim());
    if (hidden) {
        hidden.value = match?.dataset.id || '';
    }
    if (match) {
        input.setCustomValidity('');
    } else {
        input.setCustomValidity('Seleccione una opción válida de la lista.');
    }
}

function renderFuncionarios(empresaId) {
    if (!funcionarioList) return;
    funcionarioList.innerHTML = '';
    funcionariosData.forEach(item => {
        if (empresaId && item.empresa !== empresaId) {
            return;
        }
        const option = document.createElement('option');
        option.value = item.value;
        option.dataset.id = item.id;
        option.dataset.empresa = item.empresa;
        funcionarioList.appendChild(option);
    });
}

document.querySelectorAll('[data-hidden-target]').forEach(input => {
    input.addEventListener('input', () => sincronizarInput(input));
    input.addEventListener('change', () => sincronizarInput(input));
});

empresaInputIndividual?.addEventListener('input', () => {
    sincronizarInput(empresaInputIndividual);
    renderFuncionarios(document.getElementById('empresa_id_individual')?.value || '');
    if (funcionarioInput) {
        funcionarioInput.value = '';
        sincronizarInput(funcionarioInput);
    }
});

forms.forEach(form => {
    form.addEventListener('submit', event => {
        form.querySelectorAll('[data-hidden-target]').forEach(input => sincronizarInput(input));
        if (!form.checkValidity()) {
            event.preventDefault();
            form.reportValidity();
        }
    });
});

document.addEventListener('DOMContentLoaded', () => {
    renderFuncionarios('');
});
</script>