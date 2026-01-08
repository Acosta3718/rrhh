<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Parametro
{
    public function __construct(
        public float $salarioMinimo,
        public int $mayoriaEdad,
        public float $aporteObrero,
        public float $aportePatronal,
        public int $vacaciones10,
        public int $vacaciones5,
        public int $vacaciones1,
        public ?int $id = null
    ) {
    }

    public function validate(): array
    {
        $errores = [];

        if ($this->salarioMinimo <= 0) {
            $errores['salario_minimo'] = 'El salario mínimo debe ser mayor a 0.';
        }
        if ($this->mayoriaEdad <= 0) {
            $errores['mayoria_edad'] = 'La mayoría de edad debe ser mayor a 0.';
        }
        if ($this->aporteObrero < 0) {
            $errores['aporte_obrero'] = 'El aporte obrero debe ser un valor válido.';
        }
        if ($this->aportePatronal < 0) {
            $errores['aporte_patronal'] = 'El aporte patronal debe ser un valor válido.';
        }
        if ($this->vacaciones10 <= 0) {
            $errores['vacaciones10'] = 'Las vacaciones (10 años) deben ser mayor a 0.';
        }
        if ($this->vacaciones5 <= 0) {
            $errores['vacaciones5'] = 'Las vacaciones (5 años) deben ser mayor a 0.';
        }
        if ($this->vacaciones1 <= 0) {
            $errores['vacaciones1'] = 'Las vacaciones (1 año) deben ser mayor a 0.';
        }

        return $errores;
    }

    public function save(Database $db): bool
    {
        $statement = $db->pdo()->prepare(
            'INSERT INTO parametros (salario_minimo, mayoria_edad, aporte_obrero, aporte_patronal, vacaciones10, vacaciones5, vacaciones1) '
            . 'VALUES (:salario_minimo, :mayoria_edad, :aporte_obrero, :aporte_patronal, :vacaciones10, :vacaciones5, :vacaciones1)'
        );

        $result = $statement->execute([
            ':salario_minimo' => $this->salarioMinimo,
            ':mayoria_edad' => $this->mayoriaEdad,
            ':aporte_obrero' => $this->aporteObrero,
            ':aporte_patronal' => $this->aportePatronal,
            ':vacaciones10' => $this->vacaciones10,
            ':vacaciones5' => $this->vacaciones5,
            ':vacaciones1' => $this->vacaciones1
        ]);

        if ($result) {
            $this->id = (int) $db->pdo()->lastInsertId();
        }

        return $result;
    }

    public function update(Database $db): bool
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException('No se puede actualizar parámetros sin identificador.');
        }

        $statement = $db->pdo()->prepare(
            'UPDATE parametros SET salario_minimo = :salario_minimo, mayoria_edad = :mayoria_edad, aporte_obrero = :aporte_obrero, '
            . 'aporte_patronal = :aporte_patronal, vacaciones10 = :vacaciones10, vacaciones5 = :vacaciones5, vacaciones1 = :vacaciones1 '
            . 'WHERE id = :id'
        );

        return $statement->execute([
            ':salario_minimo' => $this->salarioMinimo,
            ':mayoria_edad' => $this->mayoriaEdad,
            ':aporte_obrero' => $this->aporteObrero,
            ':aporte_patronal' => $this->aportePatronal,
            ':vacaciones10' => $this->vacaciones10,
            ':vacaciones5' => $this->vacaciones5,
            ':vacaciones1' => $this->vacaciones1,
            ':id' => $this->id
        ]);
    }

    public static function getCurrent(Database $db): ?self
    {
        $statement = $db->pdo()->query('SELECT id, salario_minimo, mayoria_edad, aporte_obrero, aporte_patronal, vacaciones10, vacaciones5, vacaciones1 FROM parametros ORDER BY id DESC LIMIT 1');
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return self::fromRow($row);
    }

    private static function fromRow(array $row): self
    {
        return new self(
            salarioMinimo: (float) $row['salario_minimo'],
            mayoriaEdad: (int) $row['mayoria_edad'],
            aporteObrero: (float) $row['aporte_obrero'],
            aportePatronal: (float) $row['aporte_patronal'],
            vacaciones10: (int) $row['vacaciones10'],
            vacaciones5: (int) $row['vacaciones5'],
            vacaciones1: (int) $row['vacaciones1'],
            id: (int) $row['id']
        );
    }
}