<?php
use App\Models\Salario;

/** @var Salario[] $salarios */
/** @var array $meses */
/** @var string $baseUrl */
/** @var int $copias */
/** @var array $movimientosTotales */
/** @var string $urlDuplicado */
/** @var bool $duplicado */

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Impresión de salarios</title>
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
        .recibo-salario {
            border: 1px solid #3053c7;
            background: #fff;
            padding: 20px 24px;
            margin-bottom: 24px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
            page-break-inside: avoid;
        }
        .recibo-salario h2 {
            font-size: 1.15rem;
            margin: 0;
        }
        .recibo-salario table {
            font-size: 0.85rem;
        }
        .recibo-salario .totales {
            font-size: 0.95rem;
        }
        .linea-firma {
            border-top: 1px dashed #000;
            width: 260px;
            margin: 28px auto 6px auto;
        }
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: #fff;
            }
            .recibo-salario {
                box-shadow: none;
                margin-bottom: 16px;
            }
        }
    </style>
</head>
<body>
    <div class="container py-3">
        <div class="print-actions d-flex gap-2 align-items-center no-print">
            <a href="<?php echo $baseUrl; ?>/index.php?route=salarios/prints" class="btn btn-outline-secondary">Volver</a>
            <button class="btn btn-primary" onclick="window.print()">Imprimir</button>
            <?php if (!$duplicado): ?>
                <a class="btn btn-outline-primary" href="<?php echo htmlspecialchars($urlDuplicado); ?>">Imprimir con duplicado</a>
            <?php endif; ?>
            <span class="text-muted small ms-2">Use “Guardar como PDF” en el diálogo de impresión.</span>
        </div>

        <?php foreach ($salarios as $salario): ?>
            <?php
            $empresaNombre = strtoupper($salario->empresaNombre ?? '');
            $funcionarioNombre = strtoupper($salario->funcionarioNombre ?? '');
            $funcionarioDocumento = $salario->funcionarioDocumento ?? '';
            $periodoTexto = ($meses[$salario->mes] ?? $salario->mes) . ' del ' . $salario->anio;
            $fechaEmision = $salario->creadoEn ?? new DateTime();
            $fechaTexto = $fechaEmision->format('d') . '-' . $fechaEmision->format('m') . '-' . $fechaEmision->format('Y');
            $movimientos = $movimientosTotales[$salario->id ?? 0] ?? ['creditos' => 0.0, 'debitos' => 0.0];
            $diasTrabajados = 30;
            $hsExtras = 0.0;
            $otrosIngresos = (float) ($movimientos['creditos'] ?? 0.0);
            $totalRemuneracion = $salario->salarioBase + $hsExtras + $otrosIngresos;
            $otrosDebitos = (float) ($movimientos['debitos'] ?? 0.0);
            $totalDeducciones = $salario->ips + $salario->adelanto + $otrosDebitos;
            $bonificacion = 0.0;
            $neto = $salario->salarioNeto + $bonificacion;
            ?>
            <?php for ($i = 0; $i < $copias; $i++): ?>
                <div class="recibo-salario">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <div class="fw-bold text-uppercase"><?php echo htmlspecialchars($empresaNombre); ?></div>
                        <div class="text-end">
                            <div class="fw-semibold">LIQUIDACIÓN DE SUELDOS</div>
                            <div class="small text-muted">(Art.235 del C. de Trabajo)</div>
                        </div>
                    </div>

                    <div class="mb-2 small">
                        <div><strong>Nombre y Apellido:</strong> <?php echo htmlspecialchars($funcionarioNombre); ?></div>
                        <div>Período de Pago: <?php echo htmlspecialchars($periodoTexto); ?></div>
                    </div>

                    <table class="table table-bordered mb-2 text-center align-middle">
                        <thead class="table-light">
                            <tr>
                                <th colspan="5">REMUNERACIÓN</th>
                                <th colspan="3">DEDUCCIONES</th>
                            </tr>
                            <tr>
                                <th>Días Trab.</th>
                                <th>Sueldo</th>
                                <th>Hs. Extras</th>
                                <th>Otros Ingresos</th>
                                <th>Total (1)</th>
                                <th>I.P.S.</th>
                                <th>Anticipo</th>
                                <th>Total (2)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><?php echo htmlspecialchars((string) $diasTrabajados); ?></td>
                                <td class="text-end"><?php echo number_format($salario->salarioBase, 0, ',', '.'); ?></td>
                                <td class="text-end"><?php echo number_format($hsExtras, 0, ',', '.'); ?></td>
                                <td class="text-end"><?php echo number_format($otrosIngresos, 0, ',', '.'); ?></td>
                                <td class="text-end"><?php echo number_format($totalRemuneracion, 0, ',', '.'); ?></td>
                                <td class="text-end"><?php echo number_format($salario->ips, 0, ',', '.'); ?></td>
                                <td class="text-end"><?php echo number_format($salario->adelanto, 0, ',', '.'); ?></td>
                                <td class="text-end"><?php echo number_format($totalDeducciones, 0, ',', '.'); ?></td>
                            </tr>
                            <tr>
                                <td colspan="6" class="text-end fw-semibold">Bonificación Familiar (3)</td>
                                <td colspan="2" class="text-end"><?php echo number_format($bonificacion, 0, ',', '.'); ?></td>
                            </tr>
                            <tr class="table-light">
                                <td colspan="6" class="text-end fw-semibold">NETO A COBRAR 1-2+3</td>
                                <td colspan="2" class="text-end fw-semibold"><?php echo number_format($neto, 0, ',', '.'); ?></td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="d-flex justify-content-between small">
                        <div>Fecha: <?php echo htmlspecialchars($fechaTexto); ?></div>
                        <div>Recibí Conforme: <span class="text-muted">........................................</span></div>
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