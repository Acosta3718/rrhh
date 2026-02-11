<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Permiso;
use App\Models\Rol;

class RolesController extends Controller
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
        $total = Rol::countAll($this->db);
        $pagination = $this->buildPagination($page, $perPage, $total, [
            'route' => 'roles/list'
        ]);
        $offset = ($pagination['page'] - 1) * $perPage;

        $this->view('roles/index', [
            'roles' => Rol::paginate($this->db, $perPage, $offset),
            'mensaje' => $mensaje,
            'pagination' => $pagination
        ]);
    }

    public function create(): void
    {
        Auth::requireSuperUser($this->db, $this->baseUrl());
        $rol = null;
        $errores = [];
        $mensaje = $this->consumeFlash();
        $permisos = Permiso::all($this->db);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$rol, $errores, $permisoIds] = $this->buildRolFromRequest();

            if (empty($errores)) {
                $rol->save($this->db);
                $rol->setPermissions($this->db, $permisoIds);
                $_SESSION['flash'] = 'Rol creado correctamente.';
                $this->redirect('roles/list');
            }
        }

        $this->view('roles/create', [
            'rol' => $rol,
            'errores' => $errores,
            'mensaje' => $mensaje,
            'permisos' => $permisos,
            'permisosSeleccionados' => [],
            'modoEdicion' => false
        ]);
    }

    public function edit(): void
    {
        Auth::requireSuperUser($this->db, $this->baseUrl());
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $rol = Rol::find($this->db, $id);

        if (!$rol) {
            $_SESSION['flash'] = 'Rol no encontrado.';
            $this->redirect('roles/list');
        }

        $errores = [];
        $mensaje = $this->consumeFlash();
        $permisos = Permiso::all($this->db);
        $permisosSeleccionados = array_map(
            fn($permiso) => (int) $permiso['id'],
            $rol->permisos($this->db)
        );

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$rol, $errores, $permisoIds] = $this->buildRolFromRequest($id);

            if (empty($errores)) {
                $rol->update($this->db);
                $rol->setPermissions($this->db, $permisoIds);
                $_SESSION['flash'] = 'Rol actualizado correctamente.';
                $this->redirect('roles/list');
            }

            $permisosSeleccionados = $permisoIds;
        }

        $this->view('roles/create', [
            'rol' => $rol,
            'errores' => $errores,
            'mensaje' => $mensaje,
            'permisos' => $permisos,
            'permisosSeleccionados' => $permisosSeleccionados,
            'modoEdicion' => true
        ]);
    }

    public function delete(): void
    {
        Auth::requireSuperUser($this->db, $this->baseUrl());
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                Rol::deleteById($this->db, $id);
                $_SESSION['flash'] = 'Rol eliminado correctamente.';
            }
        }

        $this->redirect('roles/list');
    }

    private function buildRolFromRequest(?int $id = null): array
    {
        $errores = [];
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $descripcion = trim((string) ($_POST['descripcion'] ?? ''));
        $permisos = array_map('intval', $_POST['permisos'] ?? []);

        if ($nombre === '') {
            $errores['nombre'] = 'Ingrese el nombre del rol.';
        }

        $rol = new Rol(
            nombre: $nombre,
            descripcion: $descripcion,
            id: $id
        );

        return [$rol, $errores, $permisos];
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