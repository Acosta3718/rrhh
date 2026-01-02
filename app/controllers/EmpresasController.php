<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Empresa;
use PDOException;

class EmpresasController extends Controller
{
    public function __construct(private Database $db)
    {
    }

    public function create(): void
    {
        $empresa = null;
        $errores = [];
        $mensaje = $this->consumeFlash();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $empresa = $this->buildEmpresaFromRequest();
            $errores = $empresa->validate();

            if (empty($errores)) {
                if (Empresa::existsByRuc($this->db, $empresa->ruc)) {
                    $errores['ruc'] = 'El RUC ya está registrado para otra empresa.';
                }
            }

            if (empty($errores)) {
                try {
                    $empresa->save($this->db);
                    $_SESSION['flash'] = 'Empresa creada correctamente.';
                    $this->redirect('empresas/create');
                } catch (PDOException $e) {
                    $errores['general'] = 'No se pudo crear la empresa. Verifique si el RUC ya existe.';
                }
            }
        }

        $this->view('empresas/create', [
            'empresa' => $empresa,
            'errores' => $errores,
            'mensaje' => $mensaje,
            'modoEdicion' => false
        ]);
    }

    public function index(): void
    {
        $mensaje = $this->consumeFlash();

        $this->view('empresas/index', [
            'empresas' => Empresa::all($this->db),
            'mensaje' => $mensaje
        ]);
    }

    public function edit(): void
    {
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $empresa = Empresa::find($this->db, $id);

        if (!$empresa) {
            $_SESSION['flash'] = 'Empresa no encontrada.';
            $this->redirect('empresas/create');
        }

        $errores = [];
        $mensaje = $this->consumeFlash();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $empresa = $this->buildEmpresaFromRequest($id);
            $errores = $empresa->validate();

            if (empty($errores)) {
                if (Empresa::existsByRuc($this->db, $empresa->ruc, $empresa->id)) {
                    $errores['ruc'] = 'El RUC ya está registrado para otra empresa.';
                }
            }

            if (empty($errores)) {
                try {
                    $empresa->update($this->db);
                    $_SESSION['flash'] = 'Empresa actualizada correctamente.';
                    $this->redirect('empresas/list');
                } catch (PDOException $e) {
                    $errores['general'] = 'No se pudo actualizar la empresa. Verifique si el RUC ya existe.';
                }
            }
        }

        $this->view('empresas/create', [
            'empresa' => $empresa,
            'errores' => $errores,
            'mensaje' => $mensaje,
            'modoEdicion' => true
        ]);
    }

    public function delete(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0 && Empresa::deleteById($this->db, $id)) {
                $_SESSION['flash'] = 'Empresa eliminada correctamente.';
            }
        }

        $this->redirect('empresas/list');
    }

    private function redirect(string $route): void
    {
        $config = $GLOBALS['app_config'] ?? [];
        $baseUrl = rtrim($config['app']['base_url'] ?? '/public', '/');
        header('Location: ' . $baseUrl . '/index.php?route=' . $route);
        exit;
    }

    private function buildEmpresaFromRequest(?int $id = null): Empresa
    {
        return new Empresa(
            razonSocial: $_POST['razon_social'] ?? '',
            ruc: $_POST['ruc'] ?? '',
            correo: $_POST['correo'] ?? '',
            telefono: $_POST['telefono'] ?? '',
            direccion: $_POST['direccion'] ?? '',
            id: $id
        );
    }

    private function consumeFlash(): ?string
    {
        $mensaje = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        return $mensaje;
    }
}