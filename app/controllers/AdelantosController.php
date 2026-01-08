<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Adelanto;
use App\Models\Empresa;
use App\Models\Funcionario;
use DateTime;

class AdelantosController extends Controller
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

        $this->view('adelantos/index', [
            'adelantos' => Adelanto::search($this->db, $empresaId, $anio, $mes, $nombre),
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
        $funcionarios = Funcionario::all($this->db);
        $erroresEmpresa = [];
        $erroresIndividual = [];
        $mensaje = $this->consumeFlash();

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
                    $resultado = Adelanto::generarParaEmpresa($this->db, $empresaId, $anio, $mes);
                    $creados = $resultado['creados'];
                    $omitidos = $resultado['omitidos'];
                    $_SESSION['flash'] = "Adelantos generados: {$creados}.";
                    if (!empty($omitidos)) {
                        $_SESSION['flash'] .= ' Omitidos: ' . implode(', ', $omitidos);
                    }
                    $this->redirect('adelantos/list');
                }
            } elseif ($modo === 'individual') {
                $funcionarioId = (int) ($_POST['funcionario_id'] ?? 0);
                $anio = (int) ($_POST['anio'] ?? date('Y'));
                $mes = (int) ($_POST['mes'] ?? date('n'));
                $monto = (float) ($_POST['monto'] ?? 0);

                $funcionario = $funcionarioId > 0 ? Funcionario::find($this->db, $funcionarioId) : null;
                if (!$funcionario) {
                    $erroresIndividual['funcionario_id'] = 'Seleccione un funcionario válido';
                }

                $adelanto = new Adelanto(
                    funcionarioId: $funcionarioId,
                    empresaId: $funcionario?->empresaId ?? 0,
                    monto: $monto,
                    anio: $anio,
                    mes: $mes
                );

                $erroresIndividual = array_merge($erroresIndividual, $adelanto->validate($funcionario?->adelanto));

                if ($funcionario && Adelanto::existsForPeriod($this->db, $funcionarioId, $anio, $mes)) {
                    $erroresIndividual['periodo'] = 'Ya existe un adelanto para este funcionario en el período seleccionado';
                }

                if (empty($erroresIndividual)) {
                    $adelanto->creadoEn = new DateTime();
                    $adelanto->save($this->db);
                    $_SESSION['flash'] = 'Adelanto generado correctamente.';
                    $this->redirect('adelantos/list');
                }
            }
        }

        $this->view('adelantos/create', [
            'empresas' => $empresas,
            'funcionarios' => $funcionarios,
            'erroresEmpresa' => $erroresEmpresa,
            'erroresIndividual' => $erroresIndividual,
            'mensaje' => $mensaje
        ]);
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $adelanto = Adelanto::find($this->db, $id);

        if (!$adelanto) {
            $_SESSION['flash'] = 'Adelanto no encontrado.';
            $this->redirect('adelantos/list');
        }

        $errores = [];
        $mensaje = $this->consumeFlash();
        $funcionario = Funcionario::find($this->db, $adelanto->funcionarioId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $monto = (float) ($_POST['monto'] ?? $adelanto->monto);
            $anio = (int) ($_POST['anio'] ?? $adelanto->anio);
            $mes = (int) ($_POST['mes'] ?? $adelanto->mes);

            $anioAnterior = $adelanto->anio;
            $mesAnterior = $adelanto->mes;

            $adelanto->monto = $monto;
            $adelanto->anio = $anio;
            $adelanto->mes = $mes;

            $errores = $adelanto->validate($funcionario?->adelanto);

            if (empty($errores)) {
                if (($anioAnterior !== $anio || $mesAnterior !== $mes) && Adelanto::existsForPeriod($this->db, $adelanto->funcionarioId, $anio, $mes)) {
                    $errores['periodo'] = 'Ya existe un adelanto para este funcionario en el período seleccionado';
                }
            }

            if (empty($errores)) {
                $adelanto->update($this->db);
                $_SESSION['flash'] = 'Adelanto actualizado correctamente.';
                $this->redirect('adelantos/list');
            }
        }

        $this->view('adelantos/edit', [
            'adelanto' => $adelanto,
            'funcionario' => $funcionario,
            'errores' => $errores,
            'mensaje' => $mensaje
        ]);
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0 && Adelanto::deleteById($this->db, $id)) {
                $_SESSION['flash'] = 'Adelanto eliminado correctamente.';
            }
        }

        $this->redirect('adelantos/list');
    }

    public function print(): void
    {
        $id = (int) ($_GET['id'] ?? 0);
        $duplicado = isset($_GET['duplicado']) && $_GET['duplicado'] === '1';
        $adelanto = Adelanto::find($this->db, $id);

        if (!$adelanto) {
            $_SESSION['flash'] = 'Adelanto no encontrado.';
            $this->redirect('adelantos/list');
        }

        $copias = $duplicado ? 2 : 1;

        $config = $GLOBALS['app_config'] ?? [];
        $baseUrl = rtrim($config['app']['base_url'] ?? '/public', '/');
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
        $fechaEmision = $adelanto->creadoEn ?? new DateTime();
        $mesNombre = $meses[$adelanto->mes] ?? (string) $adelanto->mes;

        require __DIR__ . '/../views/adelantos/print.php';
    }

    public function prints(): void
    {
        $mensaje = $this->consumeFlash();

        $this->view('adelantos/prints', [
            'empresas' => Empresa::all($this->db),
            'funcionarios' => Funcionario::all($this->db),
            'mensaje' => $mensaje
        ]);
    }

    public function printCompany(): void
    {
        $empresaId = (int) ($_GET['empresa_id'] ?? 0);
        $anio = (int) ($_GET['anio'] ?? date('Y'));
        $mes = (int) ($_GET['mes'] ?? date('n'));
        $duplicado = isset($_GET['duplicado']) && $_GET['duplicado'] === '1';

        if ($empresaId <= 0 || $mes < 1 || $mes > 12 || $anio < 2000 || $anio > (int) date('Y')) {
            $_SESSION['flash'] = 'Seleccione una empresa y período válidos para imprimir.';
            $this->redirect('adelantos/prints');
        }

        $adelantos = Adelanto::search($this->db, $empresaId, $anio, $mes);
        if (empty($adelantos)) {
            $_SESSION['flash'] = 'No se encontraron adelantos para la empresa y período seleccionados.';
            $this->redirect('adelantos/prints');
        }

        $config = $GLOBALS['app_config'] ?? [];
        $baseUrl = rtrim($config['app']['base_url'] ?? '/public', '/');
        $copias = $duplicado ? 2 : 1;
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];

        require __DIR__ . '/../views/adelantos/print_batch.php';
    }

    public function printIndividual(): void
    {
        $empresaId = (int) ($_GET['empresa_id'] ?? 0);
        $funcionarioId = (int) ($_GET['funcionario_id'] ?? 0);
        $anio = (int) ($_GET['anio'] ?? date('Y'));
        $mes = (int) ($_GET['mes'] ?? date('n'));
        $duplicado = isset($_GET['duplicado']) && $_GET['duplicado'] === '1';

        if ($empresaId <= 0 || $funcionarioId <= 0 || $mes < 1 || $mes > 12 || $anio < 2000 || $anio > (int) date('Y')) {
            $_SESSION['flash'] = 'Seleccione una empresa, funcionario y período válidos para imprimir.';
            $this->redirect('adelantos/prints');
        }

        $adelanto = Adelanto::findByFuncionarioPeriodo($this->db, $funcionarioId, $anio, $mes);
        if (!$adelanto || $adelanto->empresaId !== $empresaId) {
            $_SESSION['flash'] = 'No se encontró un adelanto con los filtros indicados.';
            $this->redirect('adelantos/prints');
        }

        $copias = $duplicado ? 2 : 1;

        $config = $GLOBALS['app_config'] ?? [];
        $baseUrl = rtrim($config['app']['base_url'] ?? '/public', '/');
        $meses = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
        $fechaEmision = $adelanto->creadoEn ?? new DateTime();
        $mesNombre = $meses[$adelanto->mes] ?? (string) $adelanto->mes;

        require __DIR__ . '/../views/adelantos/print.php';
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