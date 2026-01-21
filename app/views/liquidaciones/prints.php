<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Impresión de liquidaciones</h2>
    <a href="<?php echo $baseUrl; ?>/index.php?route=liquidaciones/list" class="btn btn-outline-secondary">Volver al listado</a>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-info"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-body">
        <h5 class="card-title">Imprimir liquidación por funcionario</h5>
        <p class="text-muted">Busque al funcionario y genere la impresión de su última liquidación registrada.</p>
        <form method="get" action="<?php echo $baseUrl; ?>/index.php" target="_blank" class="row g-3">
            <input type="hidden" name="route" value="liquidaciones/print">
            <div class="col-md-8">
                <label class="form-label">Buscar funcionario</label>
                <input
                    type="text"
                    class="form-control"
                    id="funcionario_search"
                    placeholder="Escriba para buscar..."
                    list="funcionario_list"
                    data-hidden-target="funcionario_id"
                    data-list-target="funcionario_list"
                    required
                >
                <input type="hidden" name="funcionario_id" id="funcionario_id" required>
            </div>
            <div class="col-md-4 align-self-end">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="duplicado" value="1" id="duplicado_liquidacion">
                    <label class="form-check-label" for="duplicado_liquidacion">Imprimir original y copia</label>
                </div>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Imprimir liquidación</button>
            </div>
        </form>
    </div>
</div>

<datalist id="funcionario_list">
    <?php foreach ($funcionarios as $funcionario): ?>
        <option
            value="<?php echo htmlspecialchars($funcionario->nombre); ?> (<?php echo htmlspecialchars($funcionario->empresaNombre ?? ''); ?>)"
            data-id="<?php echo $funcionario->id; ?>"
        ></option>
    <?php endforeach; ?>
</datalist>

<script>
const funcionarioList = document.getElementById('funcionario_list');

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

document.querySelectorAll('[data-hidden-target]').forEach(input => {
    input.addEventListener('input', () => sincronizarInput(input));
    input.addEventListener('change', () => sincronizarInput(input));
});

document.querySelector('form')?.addEventListener('submit', event => {
    document.querySelectorAll('[data-hidden-target]').forEach(input => sincronizarInput(input));
    if (!event.target.checkValidity()) {
        event.preventDefault();
        event.target.reportValidity();
    }
});
</script>