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
        public bool $finSemanaSabado = false,
        public bool $finSemanaDomingo = false,
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
            'INSERT INTO empresas (razon_social, ruc, correo, telefono, direccion, fin_semana_sabado, fin_semana_domingo) '
            . 'VALUES (:razon_social, :ruc, :correo, :telefono, :direccion, :fin_semana_sabado, :fin_semana_domingo)'
        );

        $result = $statement->execute([
            ':razon_social' => $this->razonSocial,
            ':ruc' => $this->ruc,
            ':correo' => $this->correo,
            ':telefono' => $this->telefono,
            ':direccion' => $this->direccion,
            ':fin_semana_sabado' => $this->finSemanaSabado ? 1 : 0,
            ':fin_semana_domingo' => $this->finSemanaDomingo ? 1 : 0
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
            'UPDATE empresas SET razon_social = :razon_social, ruc = :ruc, correo = :correo, telefono = :telefono, direccion = :direccion, '
            . 'fin_semana_sabado = :fin_semana_sabado, fin_semana_domingo = :fin_semana_domingo WHERE id = :id'
        );

        return $statement->execute([
            ':razon_social' => $this->razonSocial,
            ':ruc' => $this->ruc,
            ':correo' => $this->correo,
            ':telefono' => $this->telefono,
            ':direccion' => $this->direccion,
            ':fin_semana_sabado' => $this->finSemanaSabado ? 1 : 0,
            ':fin_semana_domingo' => $this->finSemanaDomingo ? 1 : 0,
            ':id' => $this->id
        ]);
    }

    public static function all(Database $db): array
    {
        $statement = $db->pdo()->query(
            'SELECT id, razon_social, ruc, correo, telefono, direccion, fin_semana_sabado, fin_semana_domingo FROM empresas ORDER BY id DESC'
        );
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => self::fromRow($row), $rows);
    }

    public static function paginate(Database $db, int $limit, int $offset): array
    {
        $statement = $db->pdo()->prepare(
            'SELECT id, razon_social, ruc, correo, telefono, direccion, fin_semana_sabado, fin_semana_domingo '
            . 'FROM empresas ORDER BY id DESC LIMIT :limit OFFSET :offset'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => self::fromRow($row), $rows);
    }

    public static function countAll(Database $db): int
    {
        $statement = $db->pdo()->query('SELECT COUNT(*) FROM empresas');
        return (int) $statement->fetchColumn();
    }

    public static function find(Database $db, int $id): ?self
    {
        $statement = $db->pdo()->prepare(
            'SELECT id, razon_social, ruc, correo, telefono, direccion, fin_semana_sabado, fin_semana_domingo FROM empresas WHERE id = :id'
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
        $statement = $db->pdo()->prepare('DELETE FROM empresas WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    public static function existsByRuc(Database $db, string $ruc, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM empresas WHERE ruc = :ruc';
        $params = [':ruc' => $ruc];

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
            razonSocial: $row['razon_social'],
            ruc: $row['ruc'],
            correo: $row['correo'],
            telefono: $row['telefono'] ?? '',
            direccion: $row['direccion'] ?? '',
            finSemanaSabado: isset($row['fin_semana_sabado']) ? (bool) $row['fin_semana_sabado'] : false,
            finSemanaDomingo: isset($row['fin_semana_domingo']) ? (bool) $row['fin_semana_domingo'] : false,
            id: (int) $row['id']
        );
    }
}