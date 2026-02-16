<?php

namespace App\Core;

use App\Models\Usuario;

class Auth
{
    private const SUPER_USUARIO_ROLES = [
        'super usuario',
        'superusuario',
        'super_usuario',
        'administrador',
        'admin'
    ];
    private const SUPER_USUARIO_PERMISOS = [
        'super.usuario',
        'usuarios.list',
        'roles.list',
        'permisos.list'
    ];

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
            'correo' => $usuario->correo,
            'es_super_usuario' => self::isSuperUser($db, (int) $usuario->id)
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

    public static function hasRole(Database $db, int $userId, string $nombre): bool
    {
        $statement = $db->pdo()->prepare(
            'SELECT 1 FROM roles r '
            . 'INNER JOIN usuarios_roles ur ON ur.rol_id = r.id '
            . 'WHERE ur.usuario_id = :usuario_id AND LOWER(r.nombre) = LOWER(:nombre) '
            . 'LIMIT 1'
        );
        $statement->execute([
            ':usuario_id' => $userId,
            ':nombre' => trim($nombre)
        ]);

        return (bool) $statement->fetchColumn();
    }

    public static function isSuperUser(Database $db, int $userId): bool
    {
        $rolesUsuario = self::normalizedValues(self::userRoles($db, $userId));
        $rolesSuperUsuario = self::normalizedValues(self::SUPER_USUARIO_ROLES);
        if (!empty(array_intersect($rolesUsuario, $rolesSuperUsuario))) {
            return true;
        }

        $permisosUsuario = self::normalizedValues(self::userPermissions($db, $userId));
        $permisosSuperUsuario = self::normalizedValues(self::SUPER_USUARIO_PERMISOS);

        return !empty(array_intersect($permisosUsuario, $permisosSuperUsuario));
    }

    private static function userRoles(Database $db, int $userId): array
    {
        $statement = $db->pdo()->prepare(
            'SELECT r.nombre FROM roles r '
            . 'INNER JOIN usuarios_roles ur ON ur.rol_id = r.id '
            . 'WHERE ur.usuario_id = :usuario_id'
        );
        $statement->execute([':usuario_id' => $userId]);

        return $statement->fetchAll(\PDO::FETCH_COLUMN) ?: [];
    }

    private static function userPermissions(Database $db, int $userId): array
    {
        $statement = $db->pdo()->prepare(
            'SELECT p.clave FROM permisos p '
            . 'INNER JOIN roles_permisos rp ON rp.permiso_id = p.id '
            . 'INNER JOIN usuarios_roles ur ON ur.rol_id = rp.rol_id '
            . 'WHERE ur.usuario_id = :usuario_id'
        );
        $statement->execute([':usuario_id' => $userId]);

        return $statement->fetchAll(\PDO::FETCH_COLUMN) ?: [];
    }

    private static function normalizedValues(array $values): array
    {
        $normalized = array_map(
            static function ($value): string {
                $value = trim((string) $value);
                if ($value === '') {
                    return '';
                }

                return strtolower((string) preg_replace('/[^a-z0-9]/', '', (string) iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value)));
            },
            $values
        );

        return array_values(array_filter(array_unique($normalized)));
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

    public static function requireSuperUser(Database $db, string $baseUrl): void
    {
        $user = self::user();
        if (!$user || empty($user['id']) || !self::isSuperUser($db, (int) $user['id'])) {
            $_SESSION['flash'] = 'No tiene permisos para acceder a esta sección.';
            header('Location: ' . $baseUrl . '/index.php');
            exit;
        }
    }
}