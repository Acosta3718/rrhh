<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\TipoMovimiento;
use PDOException;

class TiposMovimientosController extends Controller
{
    public function __construct(private Database $db)
    {
    }

    public function create(): void
    {
        $tipoMovimiento = null;
        $errores = [];
        $mensaje = $this->consumeFlash();
        $modoEdicion = false;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tipoMovimiento = $this->buildFromRequest();
            $errores = $tipoMovimiento->validate();

            if (empty($errores) && TipoMovimiento::existsByDescripcion($this->db, $tipoMovimiento->descripcion)) {
                $errores['descripcion'] = 'El tipo ya existe.';
            }

            if (empty($errores)) {
                try {
                    $tipoMovimiento->save($this->db);
                    $_SESSION['flash'] = 'Tipo creado correctamente.';
                    $this->redirect('tipos-movimientos/create');
                } catch (PDOException $e) {
                    $errores['general'] = 'No se pudo crear el tipo.';
                }
            }
        }

        $this->view('tipos_movimientos/create', [
            'tipoMovimiento' => $tipoMovimiento,
            'errores' => $errores,
            'mensaje' => $mensaje,
            'modoEdicion' => $modoEdicion
        ]);
    }

    public function index(): void
    {
        $mensaje = $this->consumeFlash();

        $this->view('tipos_movimientos/index', [
            'tipos' => TipoMovimiento::all($this->db),
            'mensaje' => $mensaje
        ]);
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $tipoMovimiento = TipoMovimiento::find($this->db, $id);

        if (!$tipoMovimiento) {
            $_SESSION['flash'] = 'Tipo no encontrado.';
            $this->redirect('tipos-movimientos/list');
        }

        $errores = [];
        $mensaje = $this->consumeFlash();
        $modoEdicion = true;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $tipoMovimiento = $this->buildFromRequest($id);
            $errores = $tipoMovimiento->validate();

            if (empty($errores) && TipoMovimiento::existsByDescripcion($this->db, $tipoMovimiento->descripcion, $id)) {
                $errores['descripcion'] = 'El tipo ya existe.';
            }

            if (empty($errores)) {
                try {
                    $tipoMovimiento->update($this->db);
                    $_SESSION['flash'] = 'Tipo actualizado correctamente.';
                    $this->redirect('tipos-movimientos/list');
                } catch (PDOException $e) {
                    $errores['general'] = 'No se pudo actualizar el tipo.';
                }
            }
        }

        $this->view('tipos_movimientos/create', [
            'tipoMovimiento' => $tipoMovimiento,
            'errores' => $errores,
            'mensaje' => $mensaje,
            'modoEdicion' => $modoEdicion
        ]);
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0 && TipoMovimiento::deleteById($this->db, $id)) {
                $_SESSION['flash'] = 'Tipo eliminado correctamente.';
            }
        }

        $this->redirect('tipos-movimientos/list');
    }

    private function buildFromRequest(?int $id = null): TipoMovimiento
    {
        return new TipoMovimiento(
            descripcion: $_POST['descripcion'] ?? '',
            estado: $_POST['estado'] ?? 'activo',
            tipo: $_POST['tipo'] ?? 'credito',
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