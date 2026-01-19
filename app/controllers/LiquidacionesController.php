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

        $this->view('liquidaciones/index', [
            'liquidaciones' => Liquidacion::search($this->db, $empresaId, $nombre, $tipoSalida),
            'empresas' => Empresa::all($this->db),
            'filtros' => [
                'empresa_id' => $empresaId,
                'nombre' => $nombre,
                'tipo_salida' => $tipoSalida
            ],
            'tiposSalida' => Liquidacion::TIPOS_SALIDA,
            'mensaje' => $mensaje
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

                $liquidacion->fechaSalida = $fechaSalida;
                $liquidacion->tipoSalida = $tipoSalida;
                $liquidacion->diasTrabajados = $diasTrabajados;
                $liquidacion->descuentos = $descuentos;
                $liquidacion->salarioDiario = $detalle['salario_diario'];
                $liquidacion->salarioMes = $detalle['salario_mes'];
                $liquidacion->aniosServicio = $detalle['anios_servicio'];
                $liquidacion->preavisoDias = $detalle['preaviso_dias'];
                $liquidacion->preavisoMonto = $detalle['preaviso_monto'];
                $liquidacion->vacacionesDias = $detalle['vacaciones_dias'];
                $liquidacion->vacacionesMonto = $detalle['vacaciones_monto'];
                $liquidacion->indemnizacion = $detalle['indemnizacion'];
                $liquidacion->aguinaldo = $detalle['aguinaldo'];
                $liquidacion->total = $detalle['total'];

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
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0 && Liquidacion::deleteById($this->db, $id)) {
                $_SESSION['flash'] = 'Liquidación eliminada correctamente.';
            }
        }

        $this->redirect('liquidaciones/list');
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