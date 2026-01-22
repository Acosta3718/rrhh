<?php

namespace App\Models;

use App\Core\Database;
use DateTime;
use PDO;

class Turno
{
    public function __construct(
        public string $nombre,
        public ?DateTime $fechaInicio,
        public ?DateTime $fechaFin,
        public string $horaEntrada,
        public string $horaSalidaAlmuerzo,
        public string $horaRetornoAlmuerzo,
        public string $horaSalida,
        public ?int $id = null
    ) {
    }

    public function validate(): array
    {
        $errores = [];

        if (trim($this->nombre) === '') {
            $errores['nombre'] = 'El nombre es obligatorio';
        }
        if (!$this->fechaInicio instanceof DateTime) {
            $errores['fecha_inicio'] = 'La fecha de inicio es obligatoria';
        }
        if (!$this->fechaFin instanceof DateTime) {
            $errores['fecha_fin'] = 'La fecha de fin es obligatoria';
        }
        if ($this->fechaInicio instanceof DateTime && $this->fechaFin instanceof DateTime
            && $this->fechaFin < $this->fechaInicio) {
            $errores['fecha_fin'] = 'La fecha de fin no puede ser menor a la fecha de inicio';
        }

        foreach ([
            'hora_entrada' => $this->horaEntrada,
            'hora_salida_almuerzo' => $this->horaSalidaAlmuerzo,
            'hora_retorno_almuerzo' => $this->horaRetornoAlmuerzo,
            'hora_salida' => $this->horaSalida
        ] as $key => $value) {
            if (trim($value) === '') {
                $errores[$key] = 'El horario es obligatorio';
            }
        }

        return $errores;
    }

    public function save(Database $db): bool
    {
        $statement = $db->pdo()->prepare(
            'INSERT INTO turnos (nombre, fecha_inicio, fecha_fin, hora_entrada, hora_salida_almuerzo, hora_retorno_almuerzo, hora_salida) '
            . 'VALUES (:nombre, :fecha_inicio, :fecha_fin, :hora_entrada, :hora_salida_almuerzo, :hora_retorno_almuerzo, :hora_salida)'
        );

        $resultado = $statement->execute([
            ':nombre' => $this->nombre,
            ':fecha_inicio' => $this->fechaInicio?->format('Y-m-d'),
            ':fecha_fin' => $this->fechaFin?->format('Y-m-d'),
            ':hora_entrada' => $this->horaEntrada,
            ':hora_salida_almuerzo' => $this->horaSalidaAlmuerzo,
            ':hora_retorno_almuerzo' => $this->horaRetornoAlmuerzo,
            ':hora_salida' => $this->horaSalida
        ]);

        if ($resultado) {
            $this->id = (int) $db->pdo()->lastInsertId();
        }

        return $resultado;
    }

    public function update(Database $db): bool
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException('No se puede actualizar un turno sin identificador');
        }

        $statement = $db->pdo()->prepare(
            'UPDATE turnos SET nombre = :nombre, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, '
            . 'hora_entrada = :hora_entrada, hora_salida_almuerzo = :hora_salida_almuerzo, '
            . 'hora_retorno_almuerzo = :hora_retorno_almuerzo, hora_salida = :hora_salida WHERE id = :id'
        );

        return $statement->execute([
            ':nombre' => $this->nombre,
            ':fecha_inicio' => $this->fechaInicio?->format('Y-m-d'),
            ':fecha_fin' => $this->fechaFin?->format('Y-m-d'),
            ':hora_entrada' => $this->horaEntrada,
            ':hora_salida_almuerzo' => $this->horaSalidaAlmuerzo,
            ':hora_retorno_almuerzo' => $this->horaRetornoAlmuerzo,
            ':hora_salida' => $this->horaSalida,
            ':id' => $this->id
        ]);
    }

    public static function all(Database $db): array
    {
        $statement = $db->pdo()->query(
            'SELECT id, nombre, fecha_inicio, fecha_fin, hora_entrada, hora_salida_almuerzo, hora_retorno_almuerzo, hora_salida '
            . 'FROM turnos ORDER BY nombre ASC'
        );
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => self::fromRow($row), $rows);
    }

    public static function paginate(Database $db, int $limit, int $offset): array
    {
        $statement = $db->pdo()->prepare(
            'SELECT id, nombre, fecha_inicio, fecha_fin, hora_entrada, hora_salida_almuerzo, hora_retorno_almuerzo, hora_salida '
            . 'FROM turnos ORDER BY nombre ASC LIMIT :limit OFFSET :offset'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => self::fromRow($row), $rows);
    }

    public static function countAll(Database $db): int
    {
        $statement = $db->pdo()->query('SELECT COUNT(*) FROM turnos');
        return (int) $statement->fetchColumn();
    }

    public static function find(Database $db, int $id): ?self
    {
        $statement = $db->pdo()->prepare(
            'SELECT id, nombre, fecha_inicio, fecha_fin, hora_entrada, hora_salida_almuerzo, hora_retorno_almuerzo, hora_salida '
            . 'FROM turnos WHERE id = :id'
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
        $statement = $db->pdo()->prepare('DELETE FROM turnos WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    private static function fromRow(array $row): self
    {
        return new self(
            nombre: $row['nombre'] ?? '',
            fechaInicio: isset($row['fecha_inicio']) && $row['fecha_inicio'] ? new DateTime($row['fecha_inicio']) : null,
            fechaFin: isset($row['fecha_fin']) && $row['fecha_fin'] ? new DateTime($row['fecha_fin']) : null,
            horaEntrada: $row['hora_entrada'] ?? '',
            horaSalidaAlmuerzo: $row['hora_salida_almuerzo'] ?? '',
            horaRetornoAlmuerzo: $row['hora_retorno_almuerzo'] ?? '',
            horaSalida: $row['hora_salida'] ?? '',
            id: isset($row['id']) ? (int) $row['id'] : null
        );
    }
}