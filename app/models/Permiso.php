<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Permiso
{
    public function __construct(
        public string $clave,
        public string $descripcion,
        public ?int $id = null
    ) {
    }

    public static function find(Database $db, int $id): ?Permiso
    {
        $statement = $db->pdo()->prepare('SELECT * FROM permisos WHERE id = :id LIMIT 1');
        $statement->execute([':id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? self::fromRow($row) : null;
    }

    public static function all(Database $db): array
    {
        $statement = $db->pdo()->query('SELECT * FROM permisos ORDER BY clave ASC');
        return array_map([self::class, 'fromRow'], $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function paginate(Database $db, int $limit, int $offset): array
    {
        $statement = $db->pdo()->prepare(
            'SELECT * FROM permisos ORDER BY clave ASC LIMIT :limit OFFSET :offset'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        return array_map([self::class, 'fromRow'], $statement->fetchAll(PDO::FETCH_ASSOC));
    }

    public static function countAll(Database $db): int
    {
        return (int) $db->pdo()->query('SELECT COUNT(*) FROM permisos')->fetchColumn();
    }

    public function save(Database $db): bool
    {
        $statement = $db->pdo()->prepare(
            'INSERT INTO permisos (clave, descripcion, creado_en, actualizado_en) '
            . 'VALUES (:clave, :descripcion, :creado_en, :actualizado_en)'
        );

        $ok = $statement->execute([
            ':clave' => $this->clave,
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
            'UPDATE permisos SET clave = :clave, descripcion = :descripcion, actualizado_en = :actualizado_en '
            . 'WHERE id = :id'
        );

        return $statement->execute([
            ':clave' => $this->clave,
            ':descripcion' => $this->descripcion,
            ':actualizado_en' => date('Y-m-d H:i:s'),
            ':id' => $this->id
        ]);
    }

    public static function deleteById(Database $db, int $id): bool
    {
        $statement = $db->pdo()->prepare('DELETE FROM permisos WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    public static function fromRow(array $row): Permiso
    {
        return new self(
            clave: $row['clave'] ?? '',
            descripcion: $row['descripcion'] ?? '',
            id: isset($row['id']) ? (int) $row['id'] : null
        );
    }
}