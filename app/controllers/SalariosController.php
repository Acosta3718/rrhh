<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Adelanto;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Parametro;
use App\Models\Salario;
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