<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Feriado;
use DateTime;
use PDOException;

class FeriadosController extends Controller
{
    public function __construct(private Database $db)
    {
    }

    public function create(): void
    {
        $feriado = null;
        $errores = [];
        $mensaje = $this->consumeFlash();
        $modoEdicion = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $feriado = $this->buildFromRequest();
            $errores = $feriado->validate();

            if (empty($errores) && $feriado->fecha && Feriado::existsByFecha($this->db, $feriado->fecha)) {
                $errores['fecha'] = 'Ya existe un feriado para la fecha seleccionada.';
            }

            if (empty($errores)) {
                try {
                    $feriado->save($this->db);
                    $_SESSION['flash'] = 'Feriado creado correctamente.';
                    $this->redirect('feriados/create');
                } catch (PDOException $e) {
                    $errores['general'] = 'No se pudo crear el feriado.';
                }
            }
        }

        $this->view('feriados/create', [
            'feriado' => $feriado,
            'errores' => $errores,
            'mensaje' => $mensaje,
            'modoEdicion' => $modoEdicion
        ]);
    }

    public function index(): void
    {
        $mensaje = $this->consumeFlash();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $total = Feriado::countAll($this->db);
        $pagination = $this->buildPagination($page, $perPage, $total, [
            'route' => 'feriados/list'
        ]);
        $offset = ($pagination['page'] - 1) * $perPage;

        $this->view('feriados/index', [
            'feriados' => Feriado::paginate($this->db, $perPage, $offset),
            'mensaje' => $mensaje,
            'pagination' => $pagination
        ]);
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $feriado = Feriado::find($this->db, $id);

        if (!$feriado) {
            $_SESSION['flash'] = 'Feriado no encontrado.';
            $this->redirect('feriados/list');
        }

        $errores = [];
        $mensaje = $this->consumeFlash();
        $modoEdicion = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $feriado = $this->buildFromRequest($id);
            $errores = $feriado->validate();

            if (empty($errores) && $feriado->fecha && Feriado::existsByFecha($this->db, $feriado->fecha, $id)) {
                $errores['fecha'] = 'Ya existe un feriado para la fecha seleccionada.';
            }

            if (empty($errores)) {
                try {
                    $feriado->update($this->db);
                    $_SESSION['flash'] = 'Feriado actualizado correctamente.';
                    $this->redirect('feriados/list');
                } catch (PDOException $e) {
                    $errores['general'] = 'No se pudo actualizar el feriado.';
                }
            }
        }

        $this->view('feriados/create', [
            'feriado' => $feriado,
            'errores' => $errores,
            'mensaje' => $mensaje,
            'modoEdicion' => $modoEdicion
        ]);
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0 && Feriado::deleteById($this->db, $id)) {
                $_SESSION['flash'] = 'Feriado eliminado correctamente.';
            }
        }

        $this->redirect('feriados/list');
    }

    private function buildFromRequest(?int $id = null): Feriado
    {
        $fecha = $_POST['fecha'] ?? '';
        $fechaObj = null;
        if ($fecha) {
            try {
                $fechaObj = new DateTime($fecha);
            } catch (\Exception $e) {
                $fechaObj = null;
            }
        }

        return new Feriado(
            descripcion: $_POST['descripcion'] ?? '',
            fecha: $fechaObj,
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