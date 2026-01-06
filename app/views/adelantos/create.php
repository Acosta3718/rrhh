<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Generar adelantos</h2>
    <a href="<?php echo $baseUrl; ?>/index.php?route=adelantos/list" class="btn btn-outline-secondary">Volver al listado</a>
</div>

<?php if (!empty($mensaje)): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h5 class="card-title">Generar por empresa</h5>
                <p class="text-muted">Crea adelantos para todos los funcionarios activos de la empresa usando el monto configurado en cada ficha.</p>
                <form method="post" class="row g-3">
                    <input type="hidden" name="modo" value="empresa">
                    <div class="col-12">
                        <label class="form-label">Empresa *</label>
                        <select name="empresa_id" class="form-select" required>
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
                        <input type="number" name="anio" class="form-control" value="<?php echo date('Y'); ?>" required>
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
                        <label class="form-label">Funcionario *</label>
                        <select name="funcionario_id" id="funcionario_id" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($funcionarios as $funcionario): ?>
                                <option
                                    value="<?php echo $funcionario->id; ?>"
                                    data-monto="<?php echo htmlspecialchars($funcionario->adelanto); ?>"
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
                        <input type="number" name="anio" class="form-control" value="<?php echo date('Y'); ?>" required>
                        <?php if (!empty($erroresIndividual['anio'])): ?><div class="text-danger small"><?php echo $erroresIndividual['anio']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Monto *</label>
                        <input type="number" name="monto" id="monto" class="form-control" min="0" step="0.01" required>
                        <div class="form-text" id="monto-help">Se tomará como máximo el adelanto configurado en el funcionario.</div>
                        <?php if (!empty($erroresIndividual['monto'])): ?><div class="text-danger small"><?php echo $erroresIndividual['monto']; ?></div><?php endif; ?>
                        <?php if (!empty($erroresIndividual['periodo'])): ?><div class="text-danger small"><?php echo $erroresIndividual['periodo']; ?></div><?php endif; ?>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Generar adelanto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
const funcionarioSelect = document.getElementById('funcionario_id');
const montoInput = document.getElementById('monto');
const helpText = document.getElementById('monto-help');

function actualizarMontoSugerido() {
    const option = funcionarioSelect.selectedOptions[0];
    if (!option) return;
    const monto = option.dataset.monto;
    const empresa = option.dataset.empresa;
    if (monto) {
        montoInput.value = monto;
        montoInput.max = monto;
        helpText.textContent = `Monto máximo según ficha: ${monto}. Empresa: ${empresa}`;
    } else {
        helpText.textContent = 'Seleccione un funcionario para ver el monto máximo.';
    }
}

funcionarioSelect?.addEventListener('change', actualizarMontoSugerido);
document.addEventListener('DOMContentLoaded', actualizarMontoSugerido);
</script>