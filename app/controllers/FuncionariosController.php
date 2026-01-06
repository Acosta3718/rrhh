<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Empresa;
use App\Models\Funcionario;
use App\Models\Nacionalidad;    
use DateTime;
use PDOException;

class FuncionariosController extends Controller
{
    public function __construct(private Database $db)
    {
    }

    public function create(): void
    {
        $funcionario = null;
        $errores = [];
        $mensaje = $this->consumeFlash();
        $modoEdicion = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $funcionario = $this->buildFromRequest();
            $errores = $funcionario->validate();

            if (empty($errores) && Funcionario::existsByDocumento($this->db, $funcionario->nroDocumento)) {
                $errores['nro_documento'] = 'El número de documento ya existe.';
            }

            if (empty($errores)) {
                try {
                    $funcionario->save($this->db);
                    $_SESSION['flash'] = 'Funcionario creado correctamente.';
                    $this->redirect('funcionarios/list');
                } catch (PDOException $e) {
                    $errores['general'] = 'No se pudo guardar el funcionario.';
                }
            }
        }

        $this->view('funcionarios/create', [
            'funcionario' => $funcionario,
            'errores' => $errores,
            'mensaje' => $mensaje,
            'modoEdicion' => $modoEdicion,
            'nacionalidades' => Nacionalidad::all($this->db),
            'empresas' => Empresa::all($this->db)
        ]);
    }

    public function index(): void
    {
        $mensaje = $this->consumeFlash();
        $empresaId = isset($_GET['empresa_id']) && $_GET['empresa_id'] !== '' ? (int) $_GET['empresa_id'] : null;
        $nombre = $_GET['nombre'] ?? null;

        $this->view('funcionarios/index', [
            'funcionarios' => Funcionario::search($this->db, $empresaId, $nombre),
            'empresas' => Empresa::all($this->db),
            'filtros' => [
                'empresa_id' => $empresaId,
                'nombre' => $nombre
            ],
            'mensaje' => $mensaje
        ]);
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $funcionario = Funcionario::find($this->db, $id);

        if (!$funcionario) {
            $_SESSION['flash'] = 'Funcionario no encontrado.';
            $this->redirect('funcionarios/list');
        }

        $errores = [];
        $mensaje = $this->consumeFlash();
        $modoEdicion = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $funcionario = $this->buildFromRequest($id);
            $errores = $funcionario->validate();

            if (empty($errores) && Funcionario::existsByDocumento($this->db, $funcionario->nroDocumento, $id)) {
                $errores['nro_documento'] = 'El número de documento ya existe.';
            }

            if (empty($errores)) {
                try {
                    $funcionario->update($this->db);
                    $_SESSION['flash'] = 'Funcionario actualizado correctamente.';
                    $this->redirect('funcionarios/list');
                } catch (PDOException $e) {
                    $errores['general'] = 'No se pudo actualizar el funcionario.';
                }
            }
        }

        $this->view('funcionarios/create', [
            'funcionario' => $funcionario,
            'errores' => $errores,
            'mensaje' => $mensaje,
            'modoEdicion' => $modoEdicion,
            'nacionalidades' => Nacionalidad::all($this->db),
            'empresas' => Empresa::all($this->db)
        ]);
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0 && Funcionario::deleteById($this->db, $id)) {
                $_SESSION['flash'] = 'Funcionario eliminado correctamente.';
            }
        }

        $this->redirect('funcionarios/list');
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

    private function buildFromRequest(?int $id = null): Funcionario
    {
        $fechaNacimiento = !empty($_POST['fecha_nacimiento']) ? new DateTime($_POST['fecha_nacimiento']) : null;
        $fechaIngreso = !empty($_POST['fecha_ingreso']) ? new DateTime($_POST['fecha_ingreso']) : null;
        $fechaSalida = !empty($_POST['fecha_salida']) ? new DateTime($_POST['fecha_salida']) : null;

        return new Funcionario(
            nombre: $_POST['nombre'] ?? '',
            cargo: $_POST['cargo'] ?? '',
            nroDocumento: $_POST['nro_documento'] ?? '',
            direccion: $_POST['direccion'] ?? '',
            celular: $_POST['celular'] ?? '',
            salario: (float) ($_POST['salario'] ?? 0),
            fechaIngreso: $fechaIngreso,
            empresaId: (int) ($_POST['empresa_id'] ?? 0),
            fechaNacimiento: $fechaNacimiento,
            nacionalidadId: !empty($_POST['nacionalidad_id']) ? (int) $_POST['nacionalidad_id'] : null,
            estadoCivil: $_POST['estado_civil'] ?? 'soltero',
            estado: $_POST['estado'] ?? 'activo',
            adelanto: (float) ($_POST['adelanto'] ?? 0),
            tieneIps: isset($_POST['tiene_ips']),
            fechaSalida: $fechaSalida,
            id: $id
        );
    }
}