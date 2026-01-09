<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Adelanto;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Parametro;
use App\Models\Salario;
use App\Models\SalarioMovimiento;
use App\Models\TipoMovimiento;
use DateTime;

class SalariosController extends Controller
{
    public function __construct(private Database $db)
    {
    }

    public function index(): void
    {
        $mensaje = $this->consumeFlash();
        $empresaId = isset($_GET['empresa_id']) && $_GET['empresa_id'] !== '' ? (int) $_GET['empresa_id'] : null;
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? (int) $_GET['anio'] : null;
        $mes = isset($_GET['mes']) && $_GET['mes'] !== '' ? (int) $_GET['mes'] : null;
        $nombre = $_GET['nombre'] ?? null;

        $this->view('salarios/index', [
            'salarios' => Salario::search($this->db, $empresaId, $anio, $mes, $nombre),
            'empresas' => Empresa::all($this->db),
            'filtros' => [
                'empresa_id' => $empresaId,
                'anio' => $anio,
                'mes' => $mes,
                'nombre' => $nombre
            ],
            'mensaje' => $mensaje
        ]);
    }

    public function create(): void
    {
        $empresas = Empresa::all($this->db);
        $funcionarios = Funcionario::search($this->db, null, null, 'activo');
        $erroresEmpresa = [];
        $erroresIndividual = [];
        $mensaje = $this->consumeFlash();
        $parametros = Parametro::getCurrent($this->db);
        $aporteObrero = $parametros?->aporteObrero ?? 0.0;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $modo = $_POST['modo'] ?? '';
            if ($modo === 'empresa') {
                $empresaId = (int) ($_POST['empresa_id'] ?? 0);
                $anio = (int) ($_POST['anio'] ?? date('Y'));
                $mes = (int) ($_POST['mes'] ?? date('n'));
                $anioActual = (int) date('Y');

                if ($empresaId <= 0) {
                    $erroresEmpresa['empresa_id'] = 'Seleccione una empresa';
                }
                if ($anio < 2000) {
                    $erroresEmpresa['anio'] = 'Ingrese un año válido';
                } elseif ($anio > $anioActual) {
                    $erroresEmpresa['anio'] = 'El año no puede ser mayor al actual';
                }
                if ($mes < 1 || $mes > 12) {
                    $erroresEmpresa['mes'] = 'Seleccione un mes válido';
                }

                if (empty($erroresEmpresa)) {
                    $resultado = Salario::generarParaEmpresa($this->db, $empresaId, $anio, $mes, $aporteObrero);
                    $creados = $resultado['creados'];
                    $omitidos = $resultado['omitidos'];
                    $_SESSION['flash'] = "Salarios generados: {$creados}.";
                    if (!empty($omitidos)) {
                        $_SESSION['flash'] .= ' Omitidos: ' . implode(', ', $omitidos);
                    }
                    $this->redirect('salarios/list');
                }
            } elseif ($modo === 'individual') {
                $funcionarioId = (int) ($_POST['funcionario_id'] ?? 0);
                $anio = (int) ($_POST['anio'] ?? date('Y'));
                $mes = (int) ($_POST['mes'] ?? date('n'));

                $funcionario = $funcionarioId > 0 ? Funcionario::find($this->db, $funcionarioId) : null;
                if (!$funcionario) {
                    $erroresIndividual['funcionario_id'] = 'Seleccione un funcionario válido';
                } elseif ($funcionario->estado !== 'activo') {
                    $erroresIndividual['funcionario_id'] = 'El funcionario debe estar activo';
                }

                $adelanto = $funcionario?->id ? Adelanto::findByFuncionarioPeriodo($this->db, $funcionario->id, $anio, $mes) : null;
                $montoAdelanto = $adelanto?->monto ?? 0.0;
                $ips = $funcionario?->tieneIps ? ($funcionario->salario * $aporteObrero) : 0.0;
                $salarioNeto = ($funcionario?->salario ?? 0) - $montoAdelanto - $ips;

                $salario = new Salario(
                    funcionarioId: $funcionarioId,
                    empresaId: $funcionario?->empresaId ?? 0,
                    salarioBase: $funcionario?->salario ?? 0,
                    adelanto: $montoAdelanto,
                    ips: $ips,
                    salarioNeto: $salarioNeto,
                    anio: $anio,
                    mes: $mes
                );

                $erroresIndividual = array_merge($erroresIndividual, $salario->validate());

                if ($funcionario && Salario::existsForPeriod($this->db, $funcionarioId, $anio, $mes)) {
                    $erroresIndividual['periodo'] = 'Ya existe un salario para este funcionario en el período seleccionado';
                }

                if (empty($erroresIndividual)) {
                    $salario->creadoEn = new DateTime();
                    $salario->save($this->db);
                    $_SESSION['flash'] = 'Salario generado correctamente.';
                    $this->redirect('salarios/list');
                }
            }
        }

        $this->view('salarios/create', [
            'empresas' => $empresas,
            'funcionarios' => $funcionarios,
            'erroresEmpresa' => $erroresEmpresa,
            'erroresIndividual' => $erroresIndividual,
            'mensaje' => $mensaje,
            'aporteObrero' => $aporteObrero
        ]);
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $salario = Salario::find($this->db, $id);

        if (!$salario) {
            $_SESSION['flash'] = 'Salario no encontrado.';
            $this->redirect('salarios/list');
        }

        $funcionario = Funcionario::find($this->db, $salario->funcionarioId);
        $tipos = TipoMovimiento::all($this->db);
        $movimientos = SalarioMovimiento::listBySalario($this->db, $salario->id ?? 0);
        $movimientosPorTipo = [];
        foreach ($movimientos as $movimiento) {
            $movimientosPorTipo[$movimiento->tipoMovimientoId] = $movimiento->monto;
        }

        $errores = [];
        $mensaje = $this->consumeFlash();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $anioAnterior = $salario->anio;
            $mesAnterior = $salario->mes;

            $salario->anio = (int) ($_POST['anio'] ?? $salario->anio);
            $salario->mes = (int) ($_POST['mes'] ?? $salario->mes);
            $salario->salarioBase = (float) ($_POST['salario_base'] ?? 0);
            $salario->adelanto = (float) ($_POST['adelanto'] ?? 0);
            $salario->ips = (float) ($_POST['ips'] ?? 0);

            $errores = $salario->validate();

            if ($salario->adelanto < 0) {
                $errores['adelanto'] = 'El adelanto no puede ser negativo';
            }
            if ($salario->ips < 0) {
                $errores['ips'] = 'El IPS no puede ser negativo';
            }

            if (($anioAnterior !== $salario->anio || $mesAnterior !== $salario->mes)
                && Salario::existsForPeriodExcludingId(
                    $this->db,
                    $salario->funcionarioId,
                    $salario->anio,
                    $salario->mes,
                    $salario->id ?? 0
                )) {
                $errores['periodo'] = 'Ya existe un salario para este funcionario en el período seleccionado';
            }

            $movimientosInput = $_POST['movimientos'] ?? [];
            $movimientosToSave = [];
            $tiposMap = [];

            foreach ($tipos as $tipo) {
                $tiposMap[$tipo->id] = $tipo->tipo;
            }

            foreach ($movimientosInput as $tipoId => $monto) {
                $tipoId = (int) $tipoId;
                $monto = (float) $monto;

                if ($monto < 0) {
                    $errores['movimientos'][$tipoId] = 'El monto no puede ser negativo';
                } elseif ($monto > 0) {
                    $movimientosToSave[$tipoId] = $monto;
                }
            }

            $creditosTotal = $salario->salarioBase;
            $debitosTotal = $salario->adelanto + $salario->ips;

            foreach ($movimientosToSave as $tipoId => $monto) {
                if (($tiposMap[$tipoId] ?? '') === 'credito') {
                    $creditosTotal += $monto;
                } else {
                    $debitosTotal += $monto;
                }
            }

            $salario->salarioNeto = $creditosTotal - $debitosTotal;

            if (empty($errores)) {
                $salario->update($this->db);
                SalarioMovimiento::replaceForSalario($this->db, $salario->id ?? 0, $movimientosToSave);
                $_SESSION['flash'] = 'Salario actualizado correctamente.';
                $this->redirect('salarios/list');
            }

            $movimientosPorTipo = $movimientosToSave;
        }

        $this->view('salarios/edit', [
            'salario' => $salario,
            'funcionario' => $funcionario,
            'tipos' => $tipos,
            'movimientosPorTipo' => $movimientosPorTipo,
            'errores' => $errores,
            'mensaje' => $mensaje
        ]);
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                SalarioMovimiento::deleteBySalarioId($this->db, $id);
                if (Salario::deleteById($this->db, $id)) {
                    $_SESSION['flash'] = 'Salario eliminado correctamente.';
                }
            }
        }

        $this->redirect('salarios/list');
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