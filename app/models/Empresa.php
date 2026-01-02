<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Empresa
{
    public function __construct(
        public string $razonSocial,
        public string $ruc,
        public string $correo,
        public string $telefono,
        public string $direccion,
        public ?int $id = null
    ) {
    }

    public function validate(): array
    {
        $errores = [];
        if (empty($this->razonSocial)) {
            $errores['razon_social'] = 'La razón social es obligatoria';
        }
        if (empty($this->ruc)) {
            $errores['ruc'] = 'El RUC es obligatorio';
        }
        if (!filter_var($this->correo, FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = 'Correo electrónico inválido';
        }
        if (empty($this->direccion)) {
            $errores['direccion'] = 'La dirección es obligatoria';
        }
        return $errores;
    }

    public function save(Database $db): bool
    {
        $statement = $db->pdo()->prepare(
            'INSERT INTO empresas (razon_social, ruc, correo, telefono, direccion) VALUES (:razon_social, :ruc, :correo, :telefono, :direccion)'
        );

        $result = $statement->execute([
            ':razon_social' => $this->razonSocial,
            ':ruc' => $this->ruc,
            ':correo' => $this->correo,
            ':telefono' => $this->telefono,
            ':direccion' => $this->direccion
        ]);

        if ($result) {
            $this->id = (int) $db->pdo()->lastInsertId();
        }

        return $result;
    }

    public function update(Database $db): bool
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException('No se puede actualizar una empresa sin identificador');
        }

        $statement = $db->pdo()->prepare(
            'UPDATE empresas SET razon_social = :razon_social, ruc = :ruc, correo = :correo, telefono = :telefono, direccion = :direccion WHERE id = :id'
        );

        return $statement->execute([
            ':razon_social' => $this->razonSocial,
            ':ruc' => $this->ruc,
            ':correo' => $this->correo,
            ':telefono' => $this->telefono,
            ':direccion' => $this->direccion,
            ':id' => $this->id
        ]);
    }

    public static function all(Database $db): array
    {
        $statement = $db->pdo()->query('SELECT id, razon_social, ruc, correo, telefono, direccion FROM empresas ORDER BY id DESC');
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => self::fromRow($row), $rows);
    }

    public static function find(Database $db, int $id): ?self
    {
        $statement = $db->pdo()->prepare('SELECT id, razon_social, ruc, correo, telefono, direccion FROM empresas WHERE id = :id');
        $statement->execute([':id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return self::fromRow($row);
    }

    public static function deleteById(Database $db, int $id): bool
    {
        $statement = $db->pdo()->prepare('DELETE FROM empresas WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    private static function fromRow(array $row): self
    {
        return new self(
            razonSocial: $row['razon_social'],
            ruc: $row['ruc'],
            correo: $row['correo'],
            telefono: $row['telefono'] ?? '',
            direccion: $row['direccion'] ?? '',
            id: (int) $row['id']
        );
    }
}