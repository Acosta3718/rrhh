<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class TipoMovimiento
{
    public function __construct(
        public string $descripcion,
        public string $estado,
        public string $tipo,
        public ?int $id = null
    ) {
    }

    public function validate(): array
    {
        $errores = [];

        if (trim($this->descripcion) === '') {
            $errores['descripcion'] = 'La descripción es obligatoria';
        }

        if (!in_array($this->estado, ['activo', 'inactivo'], true)) {
            $errores['estado'] = 'Seleccione un estado válido';
        }

        if (!in_array($this->tipo, ['credito', 'debito'], true)) {
            $errores['tipo'] = 'Seleccione un tipo válido';
        }

        return $errores;
    }

    public function save(Database $db): bool
    {
        $statement = $db->pdo()->prepare(
            'INSERT INTO tipos_movimientos (descripcion, estado, tipo) VALUES (:descripcion, :estado, :tipo)'
        );
        $resultado = $statement->execute([
            ':descripcion' => $this->descripcion,
            ':estado' => $this->estado,
            ':tipo' => $this->tipo
        ]);

        if ($resultado) {
            $this->id = (int) $db->pdo()->lastInsertId();
        }

        return $resultado;
    }

    public function update(Database $db): bool
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException('No se puede actualizar un tipo sin identificador');
        }

        $statement = $db->pdo()->prepare(
            'UPDATE tipos_movimientos SET descripcion = :descripcion, estado = :estado, tipo = :tipo WHERE id = :id'
        );

        return $statement->execute([
            ':descripcion' => $this->descripcion,
            ':estado' => $this->estado,
            ':tipo' => $this->tipo,
            ':id' => $this->id
        ]);
    }

    public static function all(Database $db): array
    {
        $statement = $db->pdo()->query(
            'SELECT id, descripcion, estado, tipo FROM tipos_movimientos ORDER BY descripcion ASC'
        );
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => self::fromRow($row), $rows);
    }

    public static function paginate(Database $db, int $limit, int $offset): array
    {
        $statement = $db->pdo()->prepare(
            'SELECT id, descripcion, estado, tipo FROM tipos_movimientos ORDER BY descripcion ASC LIMIT :limit OFFSET :offset'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => self::fromRow($row), $rows);
    }

    public static function countAll(Database $db): int
    {
        $statement = $db->pdo()->query('SELECT COUNT(*) FROM tipos_movimientos');
        return (int) $statement->fetchColumn();
    }

    public static function find(Database $db, int $id): ?self
    {
        $statement = $db->pdo()->prepare(
            'SELECT id, descripcion, estado, tipo FROM tipos_movimientos WHERE id = :id'
        );
        $statement->execute([':id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return self::fromRow($row);
    }

    public static function deleteById(Database $db, int $id): bool
    {
        $statement = $db->pdo()->prepare('DELETE FROM tipos_movimientos WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    public static function existsByDescripcion(Database $db, string $descripcion, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM tipos_movimientos WHERE descripcion = :descripcion';
        $params = [':descripcion' => $descripcion];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
            $params[':id'] = $excludeId;
        }

        $statement = $db->pdo()->prepare($sql . ' LIMIT 1');
        $statement->execute($params);

        return (bool) $statement->fetchColumn();
    }

    public static function findByDescripcion(Database $db, string $descripcion): ?self
    {
        $statement = $db->pdo()->prepare(
            'SELECT id, descripcion, estado, tipo FROM tipos_movimientos WHERE descripcion = :descripcion LIMIT 1'
        );
        $statement->execute([':descripcion' => $descripcion]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return self::fromRow($row);
    }

    private static function fromRow(array $row): self
    {
        return new self(
            descripcion: $row['descripcion'] ?? '',
            estado: $row['estado'] ?? 'activo',
            tipo: $row['tipo'] ?? 'credito',
            id: isset($row['id']) ? (int) $row['id'] : null
        );
    }
}