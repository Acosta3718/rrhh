<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Rol
{
    public function __construct(
        public string $nombre,
        public string $descripcion,
        public ?int $id = null
    ) {
    }

    public static function find(Database $db, int $id): ?Rol
    {
        $statement = $db->pdo()->prepare('SELECT * FROM roles WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? self::fromRow($row) : null;
    }

    public static function all(Database $db): array
    {
        $statement = $db->pdo()->query('SELECT * FROM roles ORDER BY nombre ASC');
        return array_map([self::class, 'fromRow'], $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function paginate(Database $db, int $limit, int $offset): array
    {
        $statement = $db->pdo()->prepare(
            'SELECT * FROM roles ORDER BY nombre ASC LIMIT :limit OFFSET :offset'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return array_map([self::class, 'fromRow'], $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function countAll(Database $db): int
    {
        return (int) $db->pdo()->query('SELECT COUNT(*) FROM roles')->fetchColumn();
    }

    public function save(Database $db): bool
    {
        $statement = $db->pdo()->prepare(
            'INSERT INTO roles (nombre, descripcion, creado_en, actualizado_en) '
            . 'VALUES (:nombre, :descripcion, :creado_en, :actualizado_en)'
        );

        $ok = $statement->execute([
            ':nombre' => $this->nombre,
            ':descripcion' => $this->descripcion,
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
            'UPDATE roles SET nombre = :nombre, descripcion = :descripcion, actualizado_en = :actualizado_en '
            . 'WHERE id = :id'
        );

        return $statement->execute([
            ':nombre' => $this->nombre,
            ':descripcion' => $this->descripcion,
            ':actualizado_en' => date('Y-m-d H:i:s'),
            ':id' => $this->id
        ]);
    }

    public static function deleteById(Database $db, int $id): bool
    {
        $statement = $db->pdo()->prepare('DELETE FROM roles WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    public function setPermissions(Database $db, array $permisoIds): void
    {
        if (!$this->id) {
            return;
        }

        $db->pdo()->prepare('DELETE FROM roles_permisos WHERE rol_id = :rol_id')
            ->execute([':rol_id' => $this->id]);

        $insert = $db->pdo()->prepare(
            'INSERT INTO roles_permisos (rol_id, permiso_id) VALUES (:rol_id, :permiso_id)'
        );
        foreach ($permisoIds as $permisoId) {
            $insert->execute([
                ':rol_id' => $this->id,
                ':permiso_id' => $permisoId
            ]);
        }
    }

    public function permisos(Database $db): array
    {
        if (!$this->id) {
            return [];
        }

        $statement = $db->pdo()->prepare(
            'SELECT p.* FROM permisos p '
            . 'INNER JOIN roles_permisos rp ON rp.permiso_id = p.id '
            . 'WHERE rp.rol_id = :rol_id '
            . 'ORDER BY p.clave ASC'
        );
        $statement->execute([':rol_id' => $this->id]);

        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function fromRow(array $row): Rol
    {
        return new self(
            nombre: $row['nombre'] ?? '',
            descripcion: $row['descripcion'] ?? '',
            id: isset($row['id']) ? (int) $row['id'] : null
        );
    }
}