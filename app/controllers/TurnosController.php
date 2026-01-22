<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Turno;
use DateTime;
use PDOException;

class TurnosController extends Controller
{
    public function __construct(private Database $db)
    {
    }

    public function index(): void
    {
        $mensaje = $this->consumeFlash();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $total = Turno::countAll($this->db);
        $pagination = $this->buildPagination($page, $perPage, $total, [
            'route' => 'turnos/list'
        ]);
        $offset = ($pagination['page'] - 1) * $perPage;

        $this->view('turnos/index', [
            'turnos' => Turno::paginate($this->db, $perPage, $offset),
            'mensaje' => $mensaje,
            'pagination' => $pagination
        ]);
    }

    public function create(): void
    {
        $turno = null;
        $errores = [];
        $mensaje = $this->consumeFlash();
        $modoEdicion = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $turno = $this->buildFromRequest();
            $errores = $turno->validate();

            if (empty($errores)) {
                try {
                    $turno->save($this->db);
                    $_SESSION['flash'] = 'Turno creado correctamente.';
                    $this->redirect('turnos/list');
                } catch (PDOException $e) {
                    $errores['general'] = 'No se pudo guardar el turno.';
                }
            }
        }

        $this->view('turnos/create', [
            'turno' => $turno,
            'errores' => $errores,
            'mensaje' => $mensaje,
            'modoEdicion' => $modoEdicion
        ]);
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $turno = Turno::find($this->db, $id);

        if (!$turno) {
            $_SESSION['flash'] = 'Turno no encontrado.';
            $this->redirect('turnos/list');
        }

        $errores = [];
        $mensaje = $this->consumeFlash();
        $modoEdicion = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $turno = $this->buildFromRequest($id);
            $errores = $turno->validate();

            if (empty($errores)) {
                try {
                    $turno->update($this->db);
                    $_SESSION['flash'] = 'Turno actualizado correctamente.';
                    $this->redirect('turnos/list');
                } catch (PDOException $e) {
                    $errores['general'] = 'No se pudo actualizar el turno.';
                }
            }
        }

        $this->view('turnos/create', [
            'turno' => $turno,
            'errores' => $errores,
            'mensaje' => $mensaje,
            'modoEdicion' => $modoEdicion
        ]);
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0 && Turno::deleteById($this->db, $id)) {
                $_SESSION['flash'] = 'Turno eliminado correctamente.';
            }
        }

        $this->redirect('turnos/list');
    }

    private function buildFromRequest(?int $id = null): Turno
    {
        $fechaInicio = !empty($_POST['fecha_inicio']) ? new DateTime($_POST['fecha_inicio']) : null;
        $fechaFin = !empty($_POST['fecha_fin']) ? new DateTime($_POST['fecha_fin']) : null;

        return new Turno(
            nombre: $_POST['nombre'] ?? '',
            fechaInicio: $fechaInicio,
            fechaFin: $fechaFin,
            horaEntrada: $_POST['hora_entrada'] ?? '',
            horaSalidaAlmuerzo: $_POST['hora_salida_almuerzo'] ?? '',
            horaRetornoAlmuerzo: $_POST['hora_retorno_almuerzo'] ?? '',
            horaSalida: $_POST['hora_salida'] ?? '',
            id: $id
        );
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