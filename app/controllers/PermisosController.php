<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Permiso;

class PermisosController extends Controller
{
    public function __construct(private Database $db)
    {
    }

    public function index(): void
    {
        Auth::requireSuperUser($this->db, $this->baseUrl());
        $mensaje = $this->consumeFlash();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $total = Permiso::countAll($this->db);
        $pagination = $this->buildPagination($page, $perPage, $total, [
            'route' => 'permisos/list'
        ]);
        $offset = ($pagination['page'] - 1) * $perPage;

        $this->view('permisos/index', [
            'permisos' => Permiso::paginate($this->db, $perPage, $offset),
            'mensaje' => $mensaje,
            'pagination' => $pagination
        ]);
    }

    public function create(): void
    {
        Auth::requireSuperUser($this->db, $this->baseUrl());
        $permiso = null;
        $errores = [];
        $mensaje = $this->consumeFlash();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $permiso = $this->buildPermisoFromRequest();
            $errores = $this->validatePermiso($permiso);

            if (empty($errores)) {
                $permiso->save($this->db);
                $_SESSION['flash'] = 'Permiso creado correctamente.';
                $this->redirect('permisos/list');
            }
        }

        $this->view('permisos/create', [
            'permiso' => $permiso,
            'errores' => $errores,
            'mensaje' => $mensaje,
            'modoEdicion' => false
        ]);
    }

    public function edit(): void
    {
        Auth::requireSuperUser($this->db, $this->baseUrl());
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $permiso = Permiso::find($this->db, $id);

        if (!$permiso) {
            $_SESSION['flash'] = 'Permiso no encontrado.';
            $this->redirect('permisos/list');
        }

        $errores = [];
        $mensaje = $this->consumeFlash();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $permiso = $this->buildPermisoFromRequest($id);
            $errores = $this->validatePermiso($permiso);

            if (empty($errores)) {
                $permiso->update($this->db);
                $_SESSION['flash'] = 'Permiso actualizado correctamente.';
                $this->redirect('permisos/list');
            }
        }

        $this->view('permisos/create', [
            'permiso' => $permiso,
            'errores' => $errores,
            'mensaje' => $mensaje,
            'modoEdicion' => true
        ]);
    }

    public function delete(): void
    {
        Auth::requireSuperUser($this->db, $this->baseUrl());
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                Permiso::deleteById($this->db, $id);
                $_SESSION['flash'] = 'Permiso eliminado correctamente.';
            }
        }

        $this->redirect('permisos/list');
    }

    private function buildPermisoFromRequest(?int $id = null): Permiso
    {
        $clave = trim((string) ($_POST['clave'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));

        return new Permiso(
            clave: $clave,
            descripcion: $descripcion,
            id: $id
        );
    }

    private function validatePermiso(Permiso $permiso): array
    {
        $errores = [];

        if ($permiso->clave === '') {
            $errores['clave'] = 'Ingrese la clave.';
        }

        return $errores;
    }

    private function consumeFlash(): ?string
    {
        $mensaje = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        return $mensaje;
    }

    private function redirect(string $route): void
    {
        $config = $GLOBALS['app_config'] ?? [];
        $baseUrl = rtrim($config['app']['base_url'] ?? '/public', '/');
        header('Location: ' . $baseUrl . '/index.php?route=' . $route);
        exit;
    }

    private function baseUrl(): string
    {
        $config = $GLOBALS['app_config'] ?? [];
        return rtrim($config['app']['base_url'] ?? '/public', '/');
    }
}