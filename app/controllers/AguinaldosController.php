<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Aguinaldo;
use App\Models\Empresa;
use App\Models\Funcionario;
use DateTime;

class AguinaldosController extends Controller
{
    public function __construct(private Database $db)
    {
    }

    public function index(): void
    {
        $mensaje = $this->consumeFlash();
        $empresaId = isset($_GET['empresa_id']) && $_GET['empresa_id'] !== '' ? (int) $_GET['empresa_id'] : null;
        $anio = isset($_GET['anio']) && $_GET['anio'] !== '' ? (int) $_GET['anio'] : null;
        $nombre = $_GET['nombre'] ?? null;
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $total = Aguinaldo::countSearch($this->db, $empresaId, $anio, $nombre);
        $pagination = $this->buildPagination($page, $perPage, $total, [
            'route' => 'aguinaldos/list',
            'empresa_id' => $empresaId,
            'anio' => $anio,
            'nombre' => $nombre
        ]);
        $offset = ($pagination['page'] - 1) * $perPage;
        $aguinaldos = Aguinaldo::search($this->db, $empresaId, $anio, $nombre, $perPage, $offset);
        $totalesAnuales = [];

        foreach ($aguinaldos as $aguinaldo) {
            $totalesAnuales[$aguinaldo->id ?? 0] = Aguinaldo::totalCobradoAnual(
                $this->db,
                $aguinaldo->funcionarioId,
                $aguinaldo->anio
            );
        }

        $this->view('aguinaldos/index', [
            'aguinaldos' => $aguinaldos,
            'totalesAnuales' => $totalesAnuales,
            'empresas' => Empresa::all($this->db),
            'filtros' => [
                'empresa_id' => $empresaId,
                'anio' => $anio,
                'nombre' => $nombre
            ],
            'mensaje' => $mensaje,
            'pagination' => $pagination
        ]);
    }

    public function create(): void
    {
        $empresas = Empresa::all($this->db);
        $funcionarios = Funcionario::search($this->db, null, null, 'activo');
        $erroresEmpresa = [];
        $erroresIndividual = [];
        $mensaje = $this->consumeFlash();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $modo = $_POST['modo'] ?? '';
            if ($modo === 'empresa') {
                $empresaId = (int) ($_POST['empresa_id'] ?? 0);
                $anio = (int) ($_POST['anio'] ?? date('Y'));
                $anioActual = (int) date('Y');

                if ($empresaId <= 0) {
                    $erroresEmpresa['empresa_id'] = 'Seleccione una empresa';
                }
                if ($anio < 2000) {
                    $erroresEmpresa['anio'] = 'Ingrese un año válido';
                } elseif ($anio > $anioActual) {
                    $erroresEmpresa['anio'] = 'El año no puede ser mayor al actual';
                }

                if (empty($erroresEmpresa)) {
                    $resultado = Aguinaldo::generarParaEmpresa($this->db, $empresaId, $anio);
                    $creados = $resultado['creados'];
                    $omitidos = $resultado['omitidos'];
                    $_SESSION['flash'] = "Aguinaldos generados: {$creados}.";
                    if (!empty($omitidos)) {
                        $_SESSION['flash'] .= ' Omitidos: ' . implode(', ', $omitidos);
                    }
                    $this->redirect('aguinaldos/list');
                }
            } elseif ($modo === 'individual') {
                $funcionarioId = (int) ($_POST['funcionario_id'] ?? 0);
                $anio = (int) ($_POST['anio'] ?? date('Y'));

                $funcionario = $funcionarioId > 0 ? Funcionario::find($this->db, $funcionarioId) : null;
                if (!$funcionario) {
                    $erroresIndividual['funcionario_id'] = 'Seleccione un funcionario válido';
                } elseif ($funcionario->estado !== 'activo') {
                    $erroresIndividual['funcionario_id'] = 'El funcionario debe estar activo';
                }

                $totalCobrado = $funcionario ? Aguinaldo::totalCobradoAnual($this->db, $funcionario->id ?? 0, $anio) : 0.0;
                $monto = Aguinaldo::calcularMontoDesdeTotal($totalCobrado);

                if ($totalCobrado <= 0) {
                    $erroresIndividual['monto'] = 'No hay salarios generados para el año seleccionado.';
                }

                $aguinaldo = new Aguinaldo(
                    funcionarioId: $funcionarioId,
                    empresaId: $funcionario?->empresaId ?? 0,
                    monto: $monto,
                    anio: $anio
                );

                $erroresIndividual = array_merge($erroresIndividual, $aguinaldo->validate());

                if ($funcionario && Aguinaldo::existsForPeriod($this->db, $funcionarioId, $anio)) {
                    $erroresIndividual['periodo'] = 'Ya existe un aguinaldo para este funcionario en el año seleccionado';
                }

                if (empty($erroresIndividual)) {
                    $aguinaldo->creadoEn = new DateTime();
                    $aguinaldo->save($this->db);
                    $_SESSION['flash'] = 'Aguinaldo generado correctamente.';
                    $this->redirect('aguinaldos/list');
                }
            }
        }

        $this->view('aguinaldos/create', [
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
        $aguinaldo = Aguinaldo::find($this->db, $id);

        if (!$aguinaldo) {
            $_SESSION['flash'] = 'Aguinaldo no encontrado.';
            $this->redirect('aguinaldos/list');
        }

        $errores = [];
        $mensaje = $this->consumeFlash();
        $funcionario = Funcionario::find($this->db, $aguinaldo->funcionarioId);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $monto = (float) ($_POST['monto'] ?? $aguinaldo->monto);
            $anio = (int) ($_POST['anio'] ?? $aguinaldo->anio);

            $anioAnterior = $aguinaldo->anio;

            $aguinaldo->monto = $monto;
            $aguinaldo->anio = $anio;

            $errores = $aguinaldo->validate();

            if (empty($errores)) {
                if ($anioAnterior !== $anio && Aguinaldo::existsForPeriod($this->db, $aguinaldo->funcionarioId, $anio)) {
                    $errores['periodo'] = 'Ya existe un aguinaldo para este funcionario en el año seleccionado';
                }
            }

            if (empty($errores)) {
                $aguinaldo->update($this->db);
                $_SESSION['flash'] = 'Aguinaldo actualizado correctamente.';
                $this->redirect('aguinaldos/list');
            }
        }

        $totalesPorMes = Aguinaldo::totalesPercibidosPorMes(
            $this->db,
            $aguinaldo->funcionarioId,
            $aguinaldo->anio
        );

        $this->view('aguinaldos/edit', [
            'aguinaldo' => $aguinaldo,
            'funcionario' => $funcionario,
            'totalesPorMes' => $totalesPorMes,
            'errores' => $errores,
            'mensaje' => $mensaje
        ]);
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0 && Aguinaldo::deleteById($this->db, $id)) {
                $_SESSION['flash'] = 'Aguinaldo eliminado correctamente.';
            }
        }

        $this->redirect('aguinaldos/list');
    }

    public function prints(): void
    {
        $mensaje = $this->consumeFlash();
        $formatos = $this->formatosDisponibles();
        $formatoSeleccionado = $this->normalizarFormato($_GET['formato'] ?? null);

        $this->view('aguinaldos/prints', [
            'empresas' => Empresa::all($this->db),
            'funcionarios' => Funcionario::all($this->db),
            'mensaje' => $mensaje,
            'formatos' => $formatos,
            'formatoSeleccionado' => $formatoSeleccionado
        ]);
    }

    public function printCompany(): void
    {
        $empresaId = (int) ($_GET['empresa_id'] ?? 0);
        $anio = (int) ($_GET['anio'] ?? date('Y'));
        $duplicado = isset($_GET['duplicado']) && $_GET['duplicado'] === '1';
        $formato = $this->normalizarFormato($_GET['formato'] ?? null);

        if ($empresaId <= 0 || $anio < 2000 || $anio > (int) date('Y')) {
            $_SESSION['flash'] = 'Seleccione una empresa y año válidos para imprimir.';
            $this->redirect('aguinaldos/prints');
        }

        $aguinaldos = Aguinaldo::search($this->db, $empresaId, $anio);
        if (empty($aguinaldos)) {
            $_SESSION['flash'] = 'No se encontraron aguinaldos para la empresa y año seleccionados.';
            $this->redirect('aguinaldos/prints');
        }

        $config = $GLOBALS['app_config'] ?? [];
        $baseUrl = rtrim($config['app']['base_url'] ?? '/public', '/');
        $copias = $duplicado ? 2 : 1;
        $urlDuplicado = $baseUrl . '/index.php?route=aguinaldos/print-company&empresa_id=' . $empresaId
            . '&anio=' . $anio . '&duplicado=1&formato=' . urlencode($formato);

        $vista = match ($formato) {
            'formato_1' => 'print_formato_1.php',
            'formato_2' => 'print_formato_2.php',
            default => 'print_formato_1.php'
        };

        require __DIR__ . '/../views/aguinaldos/' . $vista;
    }

    public function printIndividual(): void
    {
        $empresaId = (int) ($_GET['empresa_id'] ?? 0);
        $funcionarioId = (int) ($_GET['funcionario_id'] ?? 0);
        $anio = (int) ($_GET['anio'] ?? date('Y'));
        $duplicado = isset($_GET['duplicado']) && $_GET['duplicado'] === '1';
        $formato = $this->normalizarFormato($_GET['formato'] ?? null);

        if ($empresaId <= 0 || $funcionarioId <= 0 || $anio < 2000 || $anio > (int) date('Y')) {
            $_SESSION['flash'] = 'Seleccione una empresa, funcionario y año válidos para imprimir.';
            $this->redirect('aguinaldos/prints');
        }

        $aguinaldo = Aguinaldo::findByFuncionarioPeriodo($this->db, $funcionarioId, $anio);
        if (!$aguinaldo || $aguinaldo->empresaId !== $empresaId) {
            $_SESSION['flash'] = 'No se encontró un aguinaldo con los filtros indicados.';
            $this->redirect('aguinaldos/prints');
        }

        $config = $GLOBALS['app_config'] ?? [];
        $baseUrl = rtrim($config['app']['base_url'] ?? '/public', '/');
        $copias = $duplicado ? 2 : 1;
        $urlDuplicado = $baseUrl . '/index.php?route=aguinaldos/print-individual&empresa_id=' . $empresaId
            . '&funcionario_id=' . $funcionarioId . '&anio=' . $anio
            . '&duplicado=1&formato=' . urlencode($formato);
        $aguinaldos = [$aguinaldo];

        $vista = match ($formato) {
            'formato_1' => 'print_formato_1.php',
            'formato_2' => 'print_formato_2.php',
            default => 'print_formato_1.php'
        };

        require __DIR__ . '/../views/aguinaldos/' . $vista;
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

    private function formatosDisponibles(): array
    {
        return [
            'formato_1' => 'Formato 1 - Recibo detallado',
            'formato_2' => 'Formato 2 - Comprobante simple'
        ];
    }

    private function normalizarFormato(?string $formato): string
    {
        $formatos = $this->formatosDisponibles();
        if ($formato && isset($formatos[$formato])) {
            return $formato;
        }

        return (string) array_key_first($formatos);
    }
}