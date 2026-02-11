<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;

class AuthController extends Controller
{
    public function __construct(private Database $db)
    {
    }

    public function login(): void
    {
        $errores = [];
        $mensaje = $this->consumeFlash();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $correo = trim((string) ($_POST['correo'] ?? ''));
            $password = (string) ($_POST['password'] ?? '');

            if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $errores['correo'] = 'Ingrese un correo válido.';
            }
            if ($password === '') {
                $errores['password'] = 'Ingrese su contraseña.';
            }

            if (empty($errores)) {
                if (Auth::login($this->db, $correo, $password)) {
                    $this->redirect('inicio');
                }
                $errores['general'] = 'Credenciales inválidas o usuario inactivo.';
            }
        }

        $this->view('auth/login', [
            'errores' => $errores,
            'mensaje' => $mensaje
        ]);
    }

    public function logout(): void
    {
        Auth::logout();
        $this->redirect('auth/login');
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