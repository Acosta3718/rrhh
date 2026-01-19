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
            background: #f6f7fb;
        }
        .print-actions {
            position: sticky;
            top: 0;
            z-index: 100;
            background: #f6f7fb;
            padding: 1rem 0;
            border-bottom: 1px solid #e5e7eb;
        }
        .tarjeta {
            background: #fff;
            border: 1px solid #cbd5f0;
            border-left: 6px solid #4f46e5;
            padding: 20px 24px;
            margin-bottom: 20px;
            box-shadow: 0 6px 18px rgba(0,0,0,0.05);
            page-break-inside: avoid;
        }
        .tarjeta h3 {
            font-size: 1.1rem;
            margin: 0;
        }
        .detalle-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 8px 24px;
            font-size: 0.9rem;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #fff;
            }
            .tarjeta {
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
            $funcionarioNombre = strtoupper($aguinaldo->funcionarioNombre ?? '');
            $funcionarioDocumento = $aguinaldo->funcionarioDocumento ?? '';
            $montoFormateado = number_format($aguinaldo->monto, 0, ',', '.');
            $fechaEmision = $aguinaldo->creadoEn ?? new DateTime();
            $fechaTexto = $fechaEmision->format('d-m-Y');
            ?>
            <?php for ($i = 0; $i < $copias; $i++): ?>
                <div class="tarjeta">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h3 class="fw-bold">Comprobante de Aguinaldo</h3>
                            <div class="text-uppercase small text-muted"><?php echo htmlspecialchars($empresaNombre); ?></div>
                        </div>
                        <div class="text-end small">
                            <div class="fw-semibold">Año <?php echo htmlspecialchars((string) $aguinaldo->anio); ?></div>
                            <div>Emitido: <?php echo htmlspecialchars($fechaTexto); ?></div>
                        </div>
                    </div>

                    <div class="detalle-grid mb-3">
                        <div><span class="text-muted">Funcionario:</span> <?php echo htmlspecialchars($funcionarioNombre); ?></div>
                        <div><span class="text-muted">Documento:</span> <?php echo htmlspecialchars($funcionarioDocumento); ?></div>
                        <div><span class="text-muted">RUC empresa:</span> <?php echo htmlspecialchars($empresaRuc); ?></div>
                        <div><span class="text-muted">Monto:</span> <span class="fw-semibold">Gs. <?php echo htmlspecialchars($montoFormateado); ?></span></div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center small">
                        <span>Recibí conforme</span>
                        <span class="text-muted">__________________________</span>
                        <?php if ($copias > 1): ?>
                            <span class="text-muted"><?php echo $i === 0 ? 'ORIGINAL' : 'DUPLICADO'; ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endfor; ?>
        <?php endforeach; ?>
    </div>
</body>
</html>