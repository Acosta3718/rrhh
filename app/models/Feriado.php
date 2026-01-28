<?php

namespace App\Models;

use App\Core\Database;
use DateTime;
use PDO;

class Feriado
{
    public function __construct(
        public string $descripcion,
        public ?DateTime $fecha,
        public ?int $id = null
    ) {
    }

    public function validate(): array
    {
        $errores = [];

        if (trim($this->descripcion) === '') {
            $errores['descripcion'] = 'La descripción es obligatoria.';
        }
        if (!$this->fecha instanceof DateTime) {
            $errores['fecha'] = 'La fecha es obligatoria.';
        }

        return $errores;
    }

    public function save(Database $db): bool
    {
        $statement = $db->pdo()->prepare(
            'INSERT INTO feriados (descripcion, fecha) VALUES (:descripcion, :fecha)'
        );

        $resultado = $statement->execute([
            ':descripcion' => $this->descripcion,
            ':fecha' => $this->fecha?->format('Y-m-d')
        ]);

        if ($resultado) {
            $this->id = (int) $db->pdo()->lastInsertId();
        }

        return $resultado;
    }

    public function update(Database $db): bool
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException('No se puede actualizar un feriado sin identificador');
        }

        $statement = $db->pdo()->prepare(
            'UPDATE feriados SET descripcion = :descripcion, fecha = :fecha WHERE id = :id'
        );

        return $statement->execute([
            ':descripcion' => $this->descripcion,
            ':fecha' => $this->fecha?->format('Y-m-d'),
            ':id' => $this->id
        ]);
    }

    public static function find(Database $db, int $id): ?self
    {
        $statement = $db->pdo()->prepare('SELECT * FROM feriados WHERE id = :id');
        $statement->execute([':id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return self::fromRow($row);
    }

    public static function deleteById(Database $db, int $id): bool
    {
        $statement = $db->pdo()->prepare('DELETE FROM feriados WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    public static function paginate(Database $db, int $limit, int $offset): array
    {
        $statement = $db->pdo()->prepare(
            'SELECT * FROM feriados ORDER BY fecha DESC LIMIT :limit OFFSET :offset'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn(array $row) => self::fromRow($row), $rows);
    }

    public static function countAll(Database $db): int
    {
        $statement = $db->pdo()->query('SELECT COUNT(*) FROM feriados');
        return (int) $statement->fetchColumn();
    }

    public static function existsByFecha(Database $db, DateTime $fecha, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM feriados WHERE fecha = :fecha';
        $params = [':fecha' => $fecha->format('Y-m-d')];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
            $params[':id'] = $excludeId;
        }

        $statement = $db->pdo()->prepare($sql . ' LIMIT 1');
        $statement->execute($params);

        return (bool) $statement->fetchColumn();
    }

    public static function listarPorRango(Database $db, DateTime $inicio, DateTime $fin): array
    {
        $statement = $db->pdo()->prepare(
            'SELECT * FROM feriados WHERE fecha >= :inicio AND fecha <= :fin ORDER BY fecha ASC'
        );
        $statement->execute([
            ':inicio' => $inicio->format('Y-m-d'),
            ':fin' => $fin->format('Y-m-d')
        ]);

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $resultado = [];
        foreach ($rows as $row) {
            $feriado = self::fromRow($row);
            $resultado[$feriado->fecha->format('Y-m-d')] = $feriado;
        }

        return $resultado;
    }

    private static function fromRow(array $row): self
    {
        return new self(
            descripcion: $row['descripcion'] ?? '',
            fecha: new DateTime($row['fecha'] ?? 'now'),
            id: isset($row['id']) ? (int) $row['id'] : null
        );
    }
}