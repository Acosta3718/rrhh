<?php
use App\Models\Aguinaldo;

/** @var Aguinaldo[] $aguinaldos */
/** @var int $copias */
/** @var string $baseUrl */
/** @var string $urlDuplicado */
/** @var bool $duplicado */
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Impresión de aguinaldos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f7fb;
        }
        .print-actions {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #f5f7fb;
            padding: 1rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .comprobante {
            border: 1px solid #2f4fc5;
            padding: 26px;
            background: #fff;
            margin-bottom: 24px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            page-break-inside: avoid;
        }
        .comprobante h2 {
            font-size: 1.35rem;
            margin: 0;
        }
        .linea-firma {
            border-top: 1px dashed #000;
            width: 300px;
            margin: 32px auto 6px auto;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #fff;
            }
            .comprobante {
                box-shadow: none;
                margin-bottom: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container py-3">
        <div class="print-actions d-flex gap-2 align-items-center no-print">
            <a href="<?php echo $baseUrl; ?>/index.php?route=aguinaldos/prints" class="btn btn-outline-secondary">Volver</a>
            <button class="btn btn-primary" onclick="window.print()">Imprimir</button>
            <?php if (!$duplicado): ?>
                <a class="btn btn-outline-primary" href="<?php echo htmlspecialchars($urlDuplicado); ?>">Imprimir con duplicado</a>
            <?php endif; ?>
            <span class="text-muted small ms-2">Use “Guardar como PDF” en el diálogo de impresión.</span>
        </div>

        <?php foreach ($aguinaldos as $aguinaldo): ?>
            <?php
            $empresaNombre = strtoupper($aguinaldo->empresaNombre ?? '');
            $empresaRuc = $aguinaldo->empresaRuc ?? '';
            $empresaDireccion = $aguinaldo->empresaDireccion ?? '';
            $funcionarioNombre = strtoupper($aguinaldo->funcionarioNombre ?? '');
            $funcionarioDocumento = $aguinaldo->funcionarioDocumento ?? '';
            $montoFormateado = number_format($aguinaldo->monto, 0, ',', '.');
            $fechaEmision = $aguinaldo->creadoEn ?? new DateTime();
            $fechaTexto = $fechaEmision->format('d') . '/' . $fechaEmision->format('m') . '/' . $fechaEmision->format('Y');
            ?>
            <?php for ($i = 0; $i < $copias; $i++): ?>
                <div class="comprobante">
                    <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
                        <div>
                            <h2 class="fw-bold">RECIBO DE AGUINALDO</h2>
                            <div class="fw-semibold text-uppercase"><?php echo htmlspecialchars($empresaNombre); ?></div>
                            <small class="fw-semibold">RUC: <?php echo htmlspecialchars($empresaRuc); ?></small>
                            <?php if (!empty($empresaDireccion)): ?>
                                <div class="text-muted small"><?php echo htmlspecialchars($empresaDireccion); ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="text-end small">
                            <div class="fw-semibold">Año: <?php echo htmlspecialchars((string) $aguinaldo->anio); ?></div>
                            <div><?php echo htmlspecialchars($fechaTexto); ?></div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <p class="mb-2">Funcionario: <span class="fw-semibold"><?php echo htmlspecialchars($funcionarioNombre); ?></span></p>
                        <p class="mb-2">Documento: <span class="fw-semibold"><?php echo htmlspecialchars($funcionarioDocumento); ?></span></p>
                        <p class="mb-0">Monto del aguinaldo: <span class="fw-semibold">Gs. <?php echo htmlspecialchars($montoFormateado); ?></span></p>
                    </div>

                    <div class="linea-firma"></div>
                    <div class="text-center small">
                        <div class="fw-semibold"><?php echo htmlspecialchars($funcionarioNombre); ?></div>
                        <div>C.I. <?php echo htmlspecialchars($funcionarioDocumento); ?></div>
                        <?php if ($copias > 1): ?>
                            <div class="text-muted mt-1"><?php echo $i === 0 ? 'ORIGINAL' : 'DUPLICADO'; ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endfor; ?>
        <?php endforeach; ?>
    </div>
</body>
</html>