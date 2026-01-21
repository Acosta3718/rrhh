<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Nacionalidad
{
    public function __construct(
        public string $nombre,
        public ?int $id = null
    ) {
    }

    public function validate(): array
    {
        $errores = [];

        if (empty($this->nombre)) {
            $errores['nombre'] = 'El nombre es obligatorio';
        }

        return $errores;
    }

    public function save(Database $db): bool
    {
        $statement = $db->pdo()->prepare('INSERT INTO nacionalidades (nombre) VALUES (:nombre)');
        $resultado = $statement->execute([':nombre' => $this->nombre]);

        if ($resultado) {
            $this->id = (int) $db->pdo()->lastInsertId();
        }

        return $resultado;
    }

    public function update(Database $db): bool
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException('No se puede actualizar una nacionalidad sin identificador');
        }

        $statement = $db->pdo()->prepare('UPDATE nacionalidades SET nombre = :nombre WHERE id = :id');

        return $statement->execute([
            ':nombre' => $this->nombre,
            ':id' => $this->id
        ]);
    }

    public static function all(Database $db): array
    {
        $statement = $db->pdo()->query('SELECT id, nombre FROM nacionalidades ORDER BY nombre ASC');
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => self::fromRow($row), $rows);
    }

    public static function paginate(Database $db, int $limit, int $offset): array
    {
        $statement = $db->pdo()->prepare(
            'SELECT id, nombre FROM nacionalidades ORDER BY nombre ASC LIMIT :limit OFFSET :offset'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => self::fromRow($row), $rows);
    }

    public static function countAll(Database $db): int
    {
        $statement = $db->pdo()->query('SELECT COUNT(*) FROM nacionalidades');
        return (int) $statement->fetchColumn();
    }

    public static function find(Database $db, int $id): ?self
    {
        $statement = $db->pdo()->prepare('SELECT id, nombre FROM nacionalidades WHERE id = :id');
        $statement->execute([':id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return self::fromRow($row);
    }

    public static function deleteById(Database $db, int $id): bool
    {
        $statement = $db->pdo()->prepare('DELETE FROM nacionalidades WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    public static function existsByNombre(Database $db, string $nombre, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM nacionalidades WHERE nombre = :nombre';
        $params = [':nombre' => $nombre];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
            $params[':id'] = $excludeId;
        }

        $statement = $db->pdo()->prepare($sql . ' LIMIT 1');
        $statement->execute($params);

        return (bool) $statement->fetchColumn();
    }

    private static function fromRow(array $row): self
    {
        return new self(
            nombre: $row['nombre'] ?? '',
            id: isset($row['id']) ? (int) $row['id'] : null
        );
    }
}