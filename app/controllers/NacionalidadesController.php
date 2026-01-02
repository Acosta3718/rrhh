<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Nacionalidad;
use PDOException;

class NacionalidadesController extends Controller
{
    public function __construct(private Database $db)
    {
    }

    public function create(): void
    {
        $nacionalidad = null;
        $errores = [];
        $mensaje = $this->consumeFlash();
        $modoEdicion = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nacionalidad = $this->buildFromRequest();
            $errores = $nacionalidad->validate();

            if (empty($errores) && Nacionalidad::existsByNombre($this->db, $nacionalidad->nombre)) {
                $errores['nombre'] = 'La nacionalidad ya existe.';
            }

            if (empty($errores)) {
                try {
                    $nacionalidad->save($this->db);
                    $_SESSION['flash'] = 'Nacionalidad creada correctamente.';
                    $this->redirect('nacionalidades/create');
                } catch (PDOException $e) {
                    $errores['general'] = 'No se pudo crear la nacionalidad.';
                }
            }
        }

        $this->view('nacionalidades/create', [
            'nacionalidad' => $nacionalidad,
            'errores' => $errores,
            'mensaje' => $mensaje,
            'modoEdicion' => $modoEdicion
        ]);
    }

    public function index(): void
    {
        $mensaje = $this->consumeFlash();

        $this->view('nacionalidades/index', [
            'nacionalidades' => Nacionalidad::all($this->db),
            'mensaje' => $mensaje
        ]);
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $nacionalidad = Nacionalidad::find($this->db, $id);

        if (!$nacionalidad) {
            $_SESSION['flash'] = 'Nacionalidad no encontrada.';
            $this->redirect('nacionalidades/list');
        }

        $errores = [];
        $mensaje = $this->consumeFlash();
        $modoEdicion = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nacionalidad = $this->buildFromRequest($id);
            $errores = $nacionalidad->validate();

            if (empty($errores) && Nacionalidad::existsByNombre($this->db, $nacionalidad->nombre, $id)) {
                $errores['nombre'] = 'La nacionalidad ya existe.';
            }

            if (empty($errores)) {
                try {
                    $nacionalidad->update($this->db);
                    $_SESSION['flash'] = 'Nacionalidad actualizada correctamente.';
                    $this->redirect('nacionalidades/list');
                } catch (PDOException $e) {
                    $errores['general'] = 'No se pudo actualizar la nacionalidad.';
                }
            }
        }

        $this->view('nacionalidades/create', [
            'nacionalidad' => $nacionalidad,
            'errores' => $errores,
            'mensaje' => $mensaje,
            'modoEdicion' => $modoEdicion
        ]);
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0 && Nacionalidad::deleteById($this->db, $id)) {
                $_SESSION['flash'] = 'Nacionalidad eliminada correctamente.';
            }
        }

        $this->redirect('nacionalidades/list');
    }

    private function buildFromRequest(?int $id = null): Nacionalidad
    {
        return new Nacionalidad(
            nombre: $_POST['nombre'] ?? '',
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