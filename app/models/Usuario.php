<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Usuario
{
    public function __construct(
        public string $nombre,
        public string $correo,
        public string $passwordHash,
        public bool $activo = true,
        public ?int $id = null
    ) {
    }

    public static function find(Database $db, int $id): ?Usuario
    {
        $statement = $db->pdo()->prepare('SELECT * FROM usuarios WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? self::fromRow($row) : null;
    }

    public static function findByCorreo(Database $db, string $correo): ?Usuario
    {
        $statement = $db->pdo()->prepare('SELECT * FROM usuarios WHERE correo = :correo LIMIT 1');
        $statement->execute([':correo' => $correo]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? self::fromRow($row) : null;
    }

    public static function paginate(Database $db, int $limit, int $offset): array
    {
        $statement = $db->pdo()->prepare(
            'SELECT * FROM usuarios ORDER BY nombre ASC LIMIT :limit OFFSET :offset'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return array_map([self::class, 'fromRow'], $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function countAll(Database $db): int
    {
        return (int) $db->pdo()->query('SELECT COUNT(*) FROM usuarios')->fetchColumn();
    }

    public function save(Database $db): bool
    {
        $statement = $db->pdo()->prepare(
            'INSERT INTO usuarios (nombre, correo, password_hash, activo, creado_en, actualizado_en) '
            . 'VALUES (:nombre, :correo, :password_hash, :activo, :creado_en, :actualizado_en)'
        );

        $ok = $statement->execute([
            ':nombre' => $this->nombre,
            ':correo' => $this->correo,
            ':password_hash' => $this->passwordHash,
            ':activo' => $this->activo ? 1 : 0,
            ':creado_en' => date('Y-m-d H:i:s'),
            ':actualizado_en' => date('Y-m-d H:i:s')
        ]);

        if ($ok) {
            $this->id = (int) $db->pdo()->lastInsertId();
        }

        return $ok;
    }

    public function update(Database $db): bool
    {
        if (!$this->id) {
            return false;
        }

        $statement = $db->pdo()->prepare(
            'UPDATE usuarios SET nombre = :nombre, correo = :correo, password_hash = :password_hash, '
            . 'activo = :activo, actualizado_en = :actualizado_en WHERE id = :id'
        );

        return $statement->execute([
            ':nombre' => $this->nombre,
            ':correo' => $this->correo,
            ':password_hash' => $this->passwordHash,
            ':activo' => $this->activo ? 1 : 0,
            ':actualizado_en' => date('Y-m-d H:i:s'),
            ':id' => $this->id
        ]);
    }

    public static function deleteById(Database $db, int $id): bool
    {
        $statement = $db->pdo()->prepare('DELETE FROM usuarios WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    public static function all(Database $db): array
    {
        $statement = $db->pdo()->query('SELECT * FROM usuarios ORDER BY nombre ASC');
        return array_map([self::class, 'fromRow'], $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public function setRoles(Database $db, array $rolIds): void
    {
        if (!$this->id) {
            return;
        }

        $db->pdo()->prepare('DELETE FROM usuarios_roles WHERE usuario_id = :usuario_id')
            ->execute([':usuario_id' => $this->id]);

        $insert = $db->pdo()->prepare(
            'INSERT INTO usuarios_roles (usuario_id, rol_id) VALUES (:usuario_id, :rol_id)'
        );
        foreach ($rolIds as $rolId) {
            $insert->execute([
                ':usuario_id' => $this->id,
                ':rol_id' => $rolId
            ]);
        }
    }

    public function roles(Database $db): array
    {
        if (!$this->id) {
            return [];
        }

        $statement = $db->pdo()->prepare(
            'SELECT r.* FROM roles r '
            . 'INNER JOIN usuarios_roles ur ON ur.rol_id = r.id '
            . 'WHERE ur.usuario_id = :usuario_id '
            . 'ORDER BY r.nombre ASC'
        );
        $statement->execute([':usuario_id' => $this->id]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function fromRow(array $row): Usuario
    {
        return new self(
            nombre: $row['nombre'] ?? '',
            correo: $row['correo'] ?? '',
            passwordHash: $row['password_hash'] ?? '',
            activo: (bool) ($row['activo'] ?? 0),
            id: isset($row['id']) ? (int) $row['id'] : null
        );
    }
}