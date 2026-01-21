<?php
use App\Models\Liquidacion;

/** @var Liquidacion $liquidacion */
/** @var int $copias */
/** @var string $baseUrl */
/** @var string $urlDuplicado */
/** @var ?\App\Models\Funcionario $funcionario */

$empresaNombre = strtoupper($liquidacion->empresaNombre ?? '');
$funcionarioNombre = strtoupper($liquidacion->funcionarioNombre ?? '');
$funcionarioDocumento = $funcionario?->nroDocumento ?? '';
$funcionarioCargo = $funcionario?->cargo ?? '';
$fechaSalida = $liquidacion->fechaSalida->format('d/m/Y');
$fechaEmision = $liquidacion->creadoEn?->format('d/m/Y H:i') ?? '';
$total = number_format($liquidacion->total, 0, ',', '.');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Impresión de liquidación</title>
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
            <a href="<?php echo $baseUrl; ?>/index.php?route=liquidaciones/prints" class="btn btn-outline-secondary">Volver</a>
            <button class="btn btn-primary" onclick="window.print()">Imprimir</button>
            <a class="btn btn-outline-primary" href="<?php echo htmlspecialchars($urlDuplicado); ?>">Imprimir con copia</a>
        </div>

        <?php for ($i = 0; $i < $copias; $i++): ?>
            <div class="comprobante">
                <div class="d-flex justify-content-between align-items-start border-bottom pb-3 mb-3">
                    <div>
                        <h2 class="fw-bold mb-1"><?php echo htmlspecialchars($empresaNombre); ?></h2>
                        <div class="text-muted small">Comprobante de liquidación</div>
                    </div>
                    <div class="text-end small">
                        <div class="fw-semibold"><?php echo htmlspecialchars($fechaEmision); ?></div>
                        <?php if ($copias > 1): ?>
                            <div class="text-muted"><?php echo $i === 0 ? 'ORIGINAL' : 'COPIA'; ?></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted">Funcionario</h6>
                        <p class="mb-1 fw-semibold"><?php echo htmlspecialchars($funcionarioNombre); ?></p>
                        <?php if (!empty($funcionarioDocumento)): ?>
                            <p class="mb-1 small">C.I. <?php echo htmlspecialchars($funcionarioDocumento); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($funcionarioCargo)): ?>
                            <p class="mb-0 small text-muted"><?php echo htmlspecialchars($funcionarioCargo); ?></p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <h6 class="text-uppercase text-muted">Detalle de salida</h6>
                        <p class="mb-1">Fecha de salida: <strong><?php echo htmlspecialchars($fechaSalida); ?></strong></p>
                        <p class="mb-1">Tipo: <strong><?php echo htmlspecialchars($liquidacion->tipoSalida); ?></strong></p>
                        <p class="mb-0">Años de servicio: <strong><?php echo htmlspecialchars((string) $liquidacion->aniosServicio); ?></strong></p>
                    </div>
                </div>

                <div class="table-responsive mb-4">
                    <table class="table table-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Concepto</th>
                                <th class="text-end">Monto (Gs.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Salario del mes (<?php echo $liquidacion->diasTrabajados; ?> días)</td>
                                <td class="text-end"><?php echo number_format($liquidacion->salarioMes, 0, ',', '.'); ?></td>
                            </tr>
                            <tr>
                                <td>Preaviso (<?php echo $liquidacion->preavisoDias; ?> días)</td>
                                <td class="text-end"><?php echo number_format($liquidacion->preavisoMonto, 0, ',', '.'); ?></td>
                            </tr>
                            <tr>
                                <td>Vacaciones (<?php echo $liquidacion->vacacionesDias; ?> días)</td>
                                <td class="text-end"><?php echo number_format($liquidacion->vacacionesMonto, 0, ',', '.'); ?></td>
                            </tr>
                            <tr>
                                <td>Indemnización</td>
                                <td class="text-end"><?php echo number_format($liquidacion->indemnizacion, 0, ',', '.'); ?></td>
                            </tr>
                            <tr>
                                <td>Aguinaldo proporcional</td>
                                <td class="text-end"><?php echo number_format($liquidacion->aguinaldo, 0, ',', '.'); ?></td>
                            </tr>
                            <tr>
                                <td>Descuentos</td>
                                <td class="text-end text-danger">-<?php echo number_format($liquidacion->descuentos, 0, ',', '.'); ?></td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="table-light">
                                <th class="text-end">Total liquidación</th>
                                <th class="text-end"><?php echo htmlspecialchars($total); ?></th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                <div class="linea-firma"></div>
                <div class="text-center">
                    <div class="fw-semibold"><?php echo htmlspecialchars($funcionarioNombre); ?></div>
                    <?php if (!empty($funcionarioDocumento)): ?>
                        <div class="small">C.I. <?php echo htmlspecialchars($funcionarioDocumento); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endfor; ?>
    </div>
</body>
</html>