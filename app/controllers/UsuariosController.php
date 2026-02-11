<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Models\Rol;
use App\Models\Usuario;

class UsuariosController extends Controller
{
    public function __construct(private Database $db)
    {
    }

    public function index(): void
    {
        Auth::requirePermission($this->db, 'usuarios.manage', $this->baseUrl());
        $mensaje = $this->consumeFlash();
        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 10;
        $total = Usuario::countAll($this->db);
        $pagination = $this->buildPagination($page, $perPage, $total, [
            'route' => 'usuarios/list'
        ]);
        $offset = ($pagination['page'] - 1) * $perPage;

        $this->view('usuarios/index', [
            'usuarios' => Usuario::paginate($this->db, $perPage, $offset),
            'mensaje' => $mensaje,
            'pagination' => $pagination
        ]);
    }

    public function create(): void
    {
        Auth::requirePermission($this->db, 'usuarios.manage', $this->baseUrl());
        $usuario = null;
        $errores = [];
        $mensaje = $this->consumeFlash();
        $roles = Rol::all($this->db);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$usuario, $errores, $rolIds] = $this->buildUsuarioFromRequest();

            if (empty($errores)) {
                if (Usuario::findByCorreo($this->db, $usuario->correo)) {
                    $errores['correo'] = 'El correo ya está registrado.';
                }
            }

            if (empty($errores)) {
                $usuario->save($this->db);
                $usuario->setRoles($this->db, $rolIds);
                $_SESSION['flash'] = 'Usuario creado correctamente.';
                $this->redirect('usuarios/list');
            }
        }

        $this->view('usuarios/create', [
            'usuario' => $usuario,
            'errores' => $errores,
            'mensaje' => $mensaje,
            'roles' => $roles,
            'rolesSeleccionados' => [],
            'modoEdicion' => false
        ]);
    }

    public function edit(): void
    {
        Auth::requirePermission($this->db, 'usuarios.manage', $this->baseUrl());
        $id = (int) ($_GET['id'] ?? $_POST['id'] ?? 0);
        $usuario = Usuario::find($this->db, $id);

        if (!$usuario) {
            $_SESSION['flash'] = 'Usuario no encontrado.';
            $this->redirect('usuarios/list');
        }

        $errores = [];
        $mensaje = $this->consumeFlash();
        $roles = Rol::all($this->db);
        $rolesSeleccionados = array_map(
            fn($rol) => (int) $rol['id'],
            $usuario->roles($this->db)
        );

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            [$usuario, $errores, $rolIds] = $this->buildUsuarioFromRequest($id, $usuario->passwordHash);

            if (empty($errores)) {
                $usuarioExistente = Usuario::findByCorreo($this->db, $usuario->correo);
                if ($usuarioExistente && $usuarioExistente->id !== $usuario->id) {
                    $errores['correo'] = 'El correo ya está registrado.';
                }
            }

            if (empty($errores)) {
                $usuario->update($this->db);
                $usuario->setRoles($this->db, $rolIds);
                $_SESSION['flash'] = 'Usuario actualizado correctamente.';
                $this->redirect('usuarios/list');
            }

            $rolesSeleccionados = $rolIds;
        }

        $this->view('usuarios/create', [
            'usuario' => $usuario,
            'errores' => $errores,
            'mensaje' => $mensaje,
            'roles' => $roles,
            'rolesSeleccionados' => $rolesSeleccionados,
            'modoEdicion' => true
        ]);
    }

    public function delete(): void
    {
        Auth::requirePermission($this->db, 'usuarios.manage', $this->baseUrl());
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                Usuario::deleteById($this->db, $id);
                $_SESSION['flash'] = 'Usuario eliminado correctamente.';
            }
        }

        $this->redirect('usuarios/list');
    }

    private function buildUsuarioFromRequest(?int $id = null, ?string $passwordHashActual = null): array
    {
        $errores = [];
        $nombre = trim((string) ($_POST['nombre'] ?? ''));
        $correo = trim((string) ($_POST['correo'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $activo = isset($_POST['activo']);
        $roles = array_map('intval', $_POST['roles'] ?? []);

        if ($nombre === '') {
            $errores['nombre'] = 'Ingrese el nombre.';
        }

        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = 'Ingrese un correo válido.';
        }

        if ($id === null && $password === '') {
            $errores['password'] = 'Ingrese una contraseña.';
        }

        $passwordHash = $passwordHashActual ?? '';
        if ($password !== '') {
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        }

        if ($passwordHash === '') {
            $errores['password'] = 'Debe definir una contraseña.';
        }

        $usuario = new Usuario(
            nombre: $nombre,
            correo: mb_strtolower($correo),
            passwordHash: $passwordHash,
            activo: $activo,
            id: $id
        );

        return [$usuario, $errores, $roles];
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