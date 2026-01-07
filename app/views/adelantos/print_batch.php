<?php
use App\Models\Adelanto;

/** @var Adelanto[] $adelantos */
/** @var array $meses */
/** @var string $baseUrl */

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Impresión de adelantos por empresa</title>
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
            border: 1px solid #3157c4;
            padding: 28px;
            background: #fff;
            margin-bottom: 24px;
            min-height: 560px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }
        .linea-firma {
            border-top: 1px dashed #000;
            width: 320px;
            margin: 38px auto 6px auto;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #fff;
            }
            .comprobante {
                margin: 0 0 16px 0;
                box-shadow: none;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>
    <div class="container py-3">
        <div class="print-actions d-flex gap-2 align-items-center no-print">
            <a href="<?php echo $baseUrl; ?>/index.php?route=adelantos/prints" class="btn btn-outline-secondary">Volver</a>
            <button class="btn btn-primary" onclick="window.print()">Imprimir</button>
            <span class="text-muted small ms-2">Use “Guardar como PDF” en el diálogo de impresión.</span>
        </div>

        <?php foreach ($adelantos as $adelanto): ?>
            <?php
            $empresaNombre = strtoupper($adelanto->empresaNombre ?? '');
            $empresaRuc = $adelanto->empresaRuc ?? '';
            $empresaDireccion = $adelanto->empresaDireccion ?? '';
            $funcionarioNombre = strtoupper($adelanto->funcionarioNombre ?? '');
            $funcionarioDocumento = $adelanto->funcionarioDocumento ?? '';
            $montoFormateado = number_format($adelanto->monto, 0, ',', '.');
            $fechaEmision = $adelanto->creadoEn ?? new DateTime();
            $fechaTexto = $fechaEmision->format('d') . ' de ' . ($meses[(int) $fechaEmision->format('n')] ?? $fechaEmision->format('F')) . ' del ' . $fechaEmision->format('Y');
            $conceptoMes = ($meses[$adelanto->mes] ?? $adelanto->mes) . ' del ' . $adelanto->anio;
            ?>
            <div class="comprobante">
                <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
                    <div>
                        <h2 class="fw-bold"><?php echo htmlspecialchars($empresaNombre); ?></h2>
                        <small class="fw-bold">RUC: <?php echo htmlspecialchars($empresaRuc); ?></small>
                        <?php if (!empty($empresaDireccion)): ?>
                            <div class="text-muted small"><?php echo htmlspecialchars($empresaDireccion); ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="text-end small">
                        <div class="fw-semibold text-uppercase"><?php echo htmlspecialchars($empresaDireccion ?: ''); ?></div>
                        <div><?php echo htmlspecialchars($fechaTexto); ?></div>
                    </div>
                </div>

                <div class="mb-4">
                    <p class="mb-2">
                        Recibí de: <span class="fw-semibold"><?php echo htmlspecialchars($empresaNombre); ?></span>
                    </p>
                    <p class="mb-2">
                        La suma de: <span class="fw-semibold">Guaraníes <?php echo htmlspecialchars($montoFormateado); ?></span>
                    </p>
                    <p class="mb-0">
                        En concepto de adelanto de salario correspondiente al mes de <span class="fw-semibold"><?php echo htmlspecialchars($conceptoMes); ?></span>
                    </p>
                </div>

                <div class="linea-firma"></div>
                <div class="text-center">
                    <div class="fw-semibold"><?php echo htmlspecialchars($funcionarioNombre); ?></div>
                    <div class="small">C.I. <?php echo htmlspecialchars($funcionarioDocumento); ?></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>