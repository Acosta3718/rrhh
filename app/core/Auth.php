<?php

namespace App\Core;

use App\Models\Usuario;

class Auth
{
    public static function check(): bool
    {
        return !empty($_SESSION['auth_user']);
    }

    public static function user(): ?array
    {
        return $_SESSION['auth_user'] ?? null;
    }

    public static function login(Database $db, string $correo, string $password): bool
    {
        $correo = trim(mb_strtolower($correo));
        $usuario = Usuario::findByCorreo($db, $correo);

        if (!$usuario || !$usuario->activo) {
            return false;
        }

        if (!password_verify($password, $usuario->passwordHash)) {
            return false;
        }

        $_SESSION['auth_user'] = [
            'id' => $usuario->id,
            'nombre' => $usuario->nombre,
            'correo' => $usuario->correo
        ];

        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION['auth_user']);
    }

    public static function requireAuth(string $baseUrl): void
    {
        if (!self::check()) {
            $_SESSION['flash'] = 'Debe iniciar sesión para acceder.';
            header('Location: ' . $baseUrl . '/index.php?route=auth/login');
            exit;
        }
    }

    public static function hasPermission(Database $db, int $userId, string $clave): bool
    {
        $statement = $db->pdo()->prepare(
            'SELECT 1 FROM permisos p '
            . 'INNER JOIN roles_permisos rp ON rp.permiso_id = p.id '
            . 'INNER JOIN usuarios_roles ur ON ur.rol_id = rp.rol_id '
            . 'WHERE ur.usuario_id = :usuario_id AND p.clave = :clave '
            . 'LIMIT 1'
        );
        $statement->execute([
            ':usuario_id' => $userId,
            ':clave' => $clave
        ]);

        return (bool) $statement->fetchColumn();
    }

    public static function requirePermission(Database $db, string $clave, string $baseUrl): void
    {
        $user = self::user();
        if (!$user || empty($user['id']) || !self::hasPermission($db, (int) $user['id'], $clave)) {
            $_SESSION['flash'] = 'No tiene permisos para acceder a esta sección.';
            header('Location: ' . $baseUrl . '/index.php');
            exit;
        }
    }
}