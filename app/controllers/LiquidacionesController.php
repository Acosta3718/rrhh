<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Liquidacion;
use DateTime;

class LiquidacionesController extends Controller
{
    public function __construct(private Database $db)
    {
    }

    public function index(): void
    {
        $mensaje = $this->consumeFlash();
        $empresaId = isset($_GET['empresa_id']) && $_GET['empresa_id'] !== '' ? (int) $_GET['empresa_id'] : null;
        $nombre = $_GET['nombre'] ?? null;
        $tipoSalida = $_GET['tipo_salida'] ?? null;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $total = Liquidacion::countSearch($this->db, $empresaId, $nombre, $tipoSalida);
        $pagination = $this->buildPagination($page, $perPage, $total, [
            'route' => 'liquidaciones/list',
            'empresa_id' => $empresaId,
            'nombre' => $nombre,
            'tipo_salida' => $tipoSalida
        ]);
        $offset = ($pagination['page'] - 1) * $perPage;

        $this->view('liquidaciones/index', [
            'liquidaciones' => Liquidacion::search($this->db, $empresaId, $nombre, $tipoSalida, $perPage, $offset),
            'empresas' => Empresa::all($this->db),
            'filtros' => [
                'empresa_id' => $empresaId,
                'nombre' => $nombre,
                'tipo_salida' => $tipoSalida
            ],
            'tiposSalida' => Liquidacion::TIPOS_SALIDA,
            'mensaje' => $mensaje,
            'pagination' => $pagination
        ]);
    }

    public function create(): void
    {
        $funcionarios = Funcionario::all($this->db);
        $errores = [];
        $mensaje = $this->consumeFlash();
        $detalle = null;
        $liquidacionGuardada = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $funcionarioId = (int) ($_POST['funcionario_id'] ?? 0);
            $fechaSalidaRaw = $_POST['fecha_salida'] ?? '';
            $tipoSalida = $_POST['tipo_salida'] ?? '';
            $diasTrabajados = (int) ($_POST['dias_trabajados'] ?? 30);
            $descuentos = (float) ($_POST['descuentos'] ?? 0);

            $funcionario = $funcionarioId > 0 ? Funcionario::find($this->db, $funcionarioId) : null;
            if (!$funcionario) {
                $errores['funcionario_id'] = 'Seleccione un funcionario válido';
            }

            $fechaSalida = $fechaSalidaRaw ? new DateTime($fechaSalidaRaw) : null;
            if (!$fechaSalida) {
                $errores['fecha_salida'] = 'Ingrese una fecha válida';
            }

            if ($funcionario && ($funcionario->fechaSalida || Liquidacion::existsByFuncionario($this->db, $funcionario->id ?? 0))) {
                $errores['funcionario_id'] = 'El funcionario ya tiene una liquidación registrada.';
            }

            if (!$errores && $funcionario && $fechaSalida) {
                $detalle = Liquidacion::calcularDetalle($funcionario, $fechaSalida, $tipoSalida, $diasTrabajados, $descuentos);

                $liquidacion = new Liquidacion(
                    funcionarioId: $funcionario->id ?? 0,
                    empresaId: $funcionario->empresaId,
                    fechaSalida: $fechaSalida,
                    tipoSalida: $tipoSalida,
                    diasTrabajados: $diasTrabajados,
                    descuentos: $descuentos,
                    salarioDiario: $detalle['salario_diario'],
                    salarioMes: $detalle['salario_mes'],
                    aniosServicio: $detalle['anios_servicio'],
                    preavisoDias: $detalle['preaviso_dias'],
                    preavisoMonto: $detalle['preaviso_monto'],
                    vacacionesDias: $detalle['vacaciones_dias'],
                    vacacionesMonto: $detalle['vacaciones_monto'],
                    indemnizacion: $detalle['indemnizacion'],
                    aguinaldo: $detalle['aguinaldo'],
                    total: $detalle['total']
                );

                $errores = array_merge($errores, $liquidacion->validate());

                if (empty($errores)) {
                    $pdo = $this->db->pdo();
                    $pdo->beginTransaction();

                    try {
                        if (!$liquidacion->save($this->db)) {
                            throw new \RuntimeException('No se pudo guardar la liquidación.');
                        }

                        $funcionario->fechaSalida = $fechaSalida;
                        if (!$funcionario->update($this->db)) {
                            throw new \RuntimeException('No se pudo actualizar la fecha de salida del funcionario.');
                        }

                        $pdo->commit();
                        $mensaje = 'Liquidación generada correctamente.';
                        $liquidacionGuardada = $liquidacion;
                    } catch (\Throwable $exception) {
                        $pdo->rollBack();
                        $errores['general'] = 'No se pudo generar la liquidación. Intente nuevamente.';
                    }
                }
            }
        }

        $this->view('liquidaciones/create', [
            'funcionarios' => $funcionarios,
            'tiposSalida' => Liquidacion::TIPOS_SALIDA,
            'errores' => $errores,
            'detalle' => $detalle,
            'liquidacionGuardada' => $liquidacionGuardada,
            'mensaje' => $mensaje
        ]);
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $liquidacion = Liquidacion::find($this->db, $id);

        if (!$liquidacion) {
            $_SESSION['flash'] = 'Liquidación no encontrada.';
            $this->redirect('liquidaciones/list');
        }

        $errores = [];
        $mensaje = $this->consumeFlash();
        $funcionario = Funcionario::find($this->db, $liquidacion->funcionarioId);
        $detalle = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $fechaSalidaRaw = $_POST['fecha_salida'] ?? '';
            $tipoSalida = $_POST['tipo_salida'] ?? $liquidacion->tipoSalida;
            $diasTrabajados = (int) ($_POST['dias_trabajados'] ?? $liquidacion->diasTrabajados);
            $descuentos = (float) ($_POST['descuentos'] ?? $liquidacion->descuentos);

            $fechaSalida = $fechaSalidaRaw ? new DateTime($fechaSalidaRaw) : null;
            if (!$fechaSalida) {
                $errores['fecha_salida'] = 'Ingrese una fecha válida';
            }

            if (!$errores && $funcionario && $fechaSalida) {
                $detalle = Liquidacion::calcularDetalle($funcionario, $fechaSalida, $tipoSalida, $diasTrabajados, $descuentos);
                $salarioDiario = (float) ($_POST['salario_diario'] ?? $detalle['salario_diario']);
                $salarioMes = (float) ($_POST['salario_mes'] ?? $detalle['salario_mes']);
                $aniosServicio = (int) ($_POST['anios_servicio'] ?? $detalle['anios_servicio']);
                $preavisoDias = (int) ($_POST['preaviso_dias'] ?? $detalle['preaviso_dias']);
                $preavisoMonto = (float) ($_POST['preaviso_monto'] ?? $detalle['preaviso_monto']);
                $vacacionesDias = (int) ($_POST['vacaciones_dias'] ?? $detalle['vacaciones_dias']);
                $vacacionesMonto = (float) ($_POST['vacaciones_monto'] ?? $detalle['vacaciones_monto']);
                $aguinaldo = (float) ($_POST['aguinaldo'] ?? $detalle['aguinaldo']);
                $indemnizacion = (float) ($_POST['indemnizacion'] ?? $detalle['indemnizacion']);
                $total = (float) ($_POST['total'] ?? $detalle['total']);
                $detalle = [
                    'salario_diario' => $salarioDiario,
                    'salario_mes' => $salarioMes,
                    'anios_servicio' => $aniosServicio,
                    'preaviso_dias' => $preavisoDias,
                    'preaviso_monto' => $preavisoMonto,
                    'vacaciones_dias' => $vacacionesDias,
                    'vacaciones_monto' => $vacacionesMonto,
                    'aguinaldo' => $aguinaldo,
                    'indemnizacion' => $indemnizacion,
                    'total' => $total
                ];

                $liquidacion->fechaSalida = $fechaSalida;
                $liquidacion->tipoSalida = $tipoSalida;
                $liquidacion->diasTrabajados = $diasTrabajados;
                $liquidacion->descuentos = $descuentos;
                $liquidacion->salarioDiario = $salarioDiario;
                $liquidacion->salarioMes = $salarioMes;
                $liquidacion->aniosServicio = $aniosServicio;
                $liquidacion->preavisoDias = $preavisoDias;
                $liquidacion->preavisoMonto = $preavisoMonto;
                $liquidacion->vacacionesDias = $vacacionesDias;
                $liquidacion->vacacionesMonto = $vacacionesMonto;
                $liquidacion->indemnizacion = $indemnizacion;
                $liquidacion->aguinaldo = $aguinaldo;
                $liquidacion->total = $total;

                $errores = array_merge($errores, $liquidacion->validate());

                if (empty($errores)) {
                    $pdo = $this->db->pdo();
                    $pdo->beginTransaction();

                    try {
                        if (!$liquidacion->update($this->db)) {
                            throw new \RuntimeException('No se pudo actualizar la liquidación.');
                        }

                        if ($funcionario) {
                            $funcionario->fechaSalida = $fechaSalida;
                            if (!$funcionario->update($this->db)) {
                                throw new \RuntimeException('No se pudo actualizar la fecha de salida del funcionario.');
                            }
                        }

                        $pdo->commit();
                        $_SESSION['flash'] = 'Liquidación actualizada correctamente.';
                        $this->redirect('liquidaciones/list');
                    } catch (\Throwable $exception) {
                        $pdo->rollBack();
                        $errores['general'] = 'No se pudo actualizar la liquidación. Intente nuevamente.';
                    }
                }
            }
        }

        $this->view('liquidaciones/edit', [
            'liquidacion' => $liquidacion,
            'funcionario' => $funcionario,
            'tiposSalida' => Liquidacion::TIPOS_SALIDA,
            'errores' => $errores,
            'detalle' => $detalle,
            'mensaje' => $mensaje
        ]);
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $liquidacion = Liquidacion::find($this->db, $id);
                if ($liquidacion) {
                    $pdo = $this->db->pdo();
                    $pdo->beginTransaction();

                    try {
                        if (!Liquidacion::deleteById($this->db, $id)) {
                            throw new \RuntimeException('No se pudo eliminar la liquidación.');
                        }

                        $funcionario = Funcionario::find($this->db, $liquidacion->funcionarioId);
                        if ($funcionario) {
                            $funcionario->fechaSalida = null;
                            if (!$funcionario->update($this->db)) {
                                throw new \RuntimeException('No se pudo actualizar la fecha de salida del funcionario.');
                            }
                        }

                        $pdo->commit();
                        $_SESSION['flash'] = 'Liquidación eliminada correctamente.';
                    } catch (\Throwable $exception) {
                        $pdo->rollBack();
                        $_SESSION['flash'] = 'No se pudo eliminar la liquidación. Intente nuevamente.';
                    }
                }
            }
        }

        $this->redirect('liquidaciones/list');
    }

    public function prints(): void
    {
        $mensaje = $this->consumeFlash();

        $this->view('liquidaciones/prints', [
            'funcionarios' => Funcionario::all($this->db),
            'mensaje' => $mensaje
        ]);
    }

    public function print(): void
    {
        $funcionarioId = (int) ($_GET['funcionario_id'] ?? 0);
        $duplicado = isset($_GET['duplicado']) && $_GET['duplicado'] === '1';

        if ($funcionarioId <= 0) {
            $_SESSION['flash'] = 'Seleccione un funcionario válido para imprimir.';
            $this->redirect('liquidaciones/prints');
        }

        $liquidacion = Liquidacion::findLatestByFuncionario($this->db, $funcionarioId);

        if (!$liquidacion) {
            $_SESSION['flash'] = 'No se encontró una liquidación para el funcionario seleccionado.';
            $this->redirect('liquidaciones/prints');
        }

        $config = $GLOBALS['app_config'] ?? [];
        $baseUrl = rtrim($config['app']['base_url'] ?? '/public', '/');
        $copias = $duplicado ? 2 : 1;
        $urlDuplicado = $baseUrl . '/index.php?route=liquidaciones/print&funcionario_id=' . $funcionarioId . '&duplicado=1';
        $funcionario = Funcionario::find($this->db, $funcionarioId);

        require __DIR__ . '/../views/liquidaciones/print.php';
    }

    private function redirect(string $route): void
    {
        $config = $GLOBALS['app_config'] ?? [];
        $baseUrl = rtrim($config['app']['base_url'] ?? '/public', '/');
        header('Location: ' . $baseUrl . '/index.php?route=' . $route);
        exit;
    }

    private function consumeFlash(): ?string
    {
        $mensaje = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        return $mensaje;
    }
}