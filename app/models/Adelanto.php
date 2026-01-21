<?php

namespace App\Models;

use App\Core\Database;
use App\Models\Funcionario;
use DateTime;
use PDO;

class Adelanto
{
    public function __construct(
        public int $funcionarioId,
        public int $empresaId,
        public float $monto,
        public int $anio,
        public int $mes,
        public ?int $id = null,
        public ?DateTime $creadoEn = null,
        public ?string $funcionarioNombre = null,
        public ?string $funcionarioDocumento = null,
        public ?string $empresaNombre = null,
        public ?string $empresaRuc = null,
        public ?string $empresaDireccion = null
    ) {
    }

    public function validate(?float $maxAdelanto = null): array
    {
        $errores = [];
        if ($this->funcionarioId <= 0) {
            $errores['funcionario_id'] = 'Seleccione un funcionario válido';
        }
        if ($this->empresaId <= 0) {
            $errores['empresa_id'] = 'Seleccione una empresa válida';
        }
        if ($this->monto <= 0) {
            $errores['monto'] = 'El monto debe ser mayor a cero';
        }
        if ($maxAdelanto !== null && $this->monto > $maxAdelanto) {
            $errores['monto'] = 'El monto no puede superar el adelanto configurado del funcionario';
        }
        $anioActual = (int) date('Y');
        if ($this->anio < 2000) {
            $errores['anio'] = 'Ingrese un año válido';
        } elseif ($this->anio > $anioActual) {
            $errores['anio'] = 'El año no puede ser mayor al actual';
        }
        if ($this->mes < 1 || $this->mes > 12) {
            $errores['mes'] = 'Ingrese un mes válido';
        }

        return $errores;
    }

    public function save(Database $db): bool
    {
        $statement = $db->pdo()->prepare(
            'INSERT INTO adelantos (funcionario_id, empresa_id, monto, anio, mes, creado_en) VALUES (:funcionario_id, :empresa_id, :monto, :anio, :mes, :creado_en)'
        );

        $resultado = $statement->execute([
            ':funcionario_id' => $this->funcionarioId,
            ':empresa_id' => $this->empresaId,
            ':monto' => $this->monto,
            ':anio' => $this->anio,
            ':mes' => $this->mes,
            ':creado_en' => $this->creadoEn?->format('Y-m-d H:i:s') ?? (new DateTime())->format('Y-m-d H:i:s')
        ]);

        if ($resultado) {
            $this->id = (int) $db->pdo()->lastInsertId();
        }

        return $resultado;
    }

    public function update(Database $db): bool
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException('No se puede actualizar un adelanto sin identificador');
        }

        $statement = $db->pdo()->prepare(
            'UPDATE adelantos SET funcionario_id = :funcionario_id, empresa_id = :empresa_id, monto = :monto, anio = :anio, mes = :mes WHERE id = :id'
        );

        return $statement->execute([
            ':funcionario_id' => $this->funcionarioId,
            ':empresa_id' => $this->empresaId,
            ':monto' => $this->monto,
            ':anio' => $this->anio,
            ':mes' => $this->mes,
            ':id' => $this->id
        ]);
    }

    public static function find(Database $db, int $id): ?self
    {
        $statement = $db->pdo()->prepare(
            'SELECT a.*, f.nombre AS funcionario_nombre, f.nro_documento AS funcionario_documento, '
            . 'e.razon_social AS empresa_nombre, e.ruc AS empresa_ruc, e.direccion AS empresa_direccion FROM adelantos a '
            . 'LEFT JOIN funcionarios f ON f.id = a.funcionario_id '
            . 'LEFT JOIN empresas e ON e.id = a.empresa_id WHERE a.id = :id'
        );
        $statement->execute([':id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? self::fromRow($row) : null;
    }

    public static function findByFuncionarioPeriodo(Database $db, int $funcionarioId, int $anio, int $mes): ?self
    {
        $statement = $db->pdo()->prepare(
            'SELECT a.*, f.nombre AS funcionario_nombre, f.nro_documento AS funcionario_documento, '
            . 'e.razon_social AS empresa_nombre, e.ruc AS empresa_ruc, e.direccion AS empresa_direccion FROM adelantos a '
            . 'LEFT JOIN funcionarios f ON f.id = a.funcionario_id '
            . 'LEFT JOIN empresas e ON e.id = a.empresa_id WHERE a.funcionario_id = :funcionario_id AND a.anio = :anio AND a.mes = :mes '
            . 'ORDER BY a.id DESC LIMIT 1'
        );
        $statement->execute([
            ':funcionario_id' => $funcionarioId,
            ':anio' => $anio,
            ':mes' => $mes
        ]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? self::fromRow($row) : null;
    }
    
    public static function deleteById(Database $db, int $id): bool
    {
        $statement = $db->pdo()->prepare('DELETE FROM adelantos WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    public static function search(
        Database $db,
        ?int $empresaId = null,
        ?int $anio = null,
        ?int $mes = null,
        ?string $nombre = null,
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $sql = 'SELECT a.*, f.nombre AS funcionario_nombre, f.nro_documento AS funcionario_documento, '
            . 'e.razon_social AS empresa_nombre, e.ruc AS empresa_ruc, e.direccion AS empresa_direccion FROM adelantos a '
            . 'LEFT JOIN funcionarios f ON f.id = a.funcionario_id '
            . 'LEFT JOIN empresas e ON e.id = a.empresa_id WHERE 1=1';
        $params = [];

        if ($empresaId) {
            $sql .= ' AND a.empresa_id = :empresa_id';
            $params[':empresa_id'] = $empresaId;
        }
        if ($anio) {
            $sql .= ' AND a.anio = :anio';
            $params[':anio'] = $anio;
        }
        if ($mes) {
            $sql .= ' AND a.mes = :mes';
            $params[':mes'] = $mes;
        }
        if ($nombre) {
            $sql .= ' AND f.nombre LIKE :nombre';
            $params[':nombre'] = '%' . $nombre . '%';
        }

        $sql .= ' ORDER BY a.id DESC';

        $statement = $db->pdo()->prepare($sql);
        if ($limit !== null && $offset !== null) {
            $sql .= ' LIMIT :limit OFFSET :offset';
            $statement = $db->pdo()->prepare($sql);
            $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
            $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        }

        foreach ($params as $key => $value) {
            $statement->bindValue($key, $value);
        }

        $statement->execute();
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => self::fromRow($row), $rows);
    }

    public static function countSearch(
        Database $db,
        ?int $empresaId = null,
        ?int $anio = null,
        ?int $mes = null,
        ?string $nombre = null
    ): int {
        $sql = 'SELECT COUNT(*) FROM adelantos a '
            . 'LEFT JOIN funcionarios f ON f.id = a.funcionario_id WHERE 1=1';
        $params = [];

        if ($empresaId) {
            $sql .= ' AND a.empresa_id = :empresa_id';
            $params[':empresa_id'] = $empresaId;
        }
        if ($anio) {
            $sql .= ' AND a.anio = :anio';
            $params[':anio'] = $anio;
        }
        if ($mes) {
            $sql .= ' AND a.mes = :mes';
            $params[':mes'] = $mes;
        }
        if ($nombre) {
            $sql .= ' AND f.nombre LIKE :nombre';
            $params[':nombre'] = '%' . $nombre . '%';
        }

        $statement = $db->pdo()->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    public static function generarParaEmpresa(Database $db, int $empresaId, int $anio, int $mes): array
    {
        $creados = 0;
        $omitidos = [];

        $funcionarios = Funcionario::activosPorEmpresa($db, $empresaId);

        foreach ($funcionarios as $funcionario) {
            if ($funcionario->adelanto <= 0) {
                $omitidos[] = "{$funcionario->nombre} (sin monto configurado)";
                continue;
            }

            if (self::existsForPeriod($db, $funcionario->id, $anio, $mes)) {
                $omitidos[] = "{$funcionario->nombre} (ya tiene adelanto en {$mes}/{$anio})";
                continue;
            }

            $adelanto = new self(
                funcionarioId: $funcionario->id ?? 0,
                empresaId: $empresaId,
                monto: $funcionario->adelanto,
                anio: $anio,
                mes: $mes
            );

            if ($adelanto->save($db)) {
                $creados++;
            }
        }

        return ['creados' => $creados, 'omitidos' => $omitidos];
    }

    public static function existsForPeriod(Database $db, int $funcionarioId, int $anio, int $mes): bool
    {
        $statement = $db->pdo()->prepare(
            'SELECT 1 FROM adelantos WHERE funcionario_id = :funcionario_id AND anio = :anio AND mes = :mes LIMIT 1'
        );
        $statement->execute([
            ':funcionario_id' => $funcionarioId,
            ':anio' => $anio,
            ':mes' => $mes
        ]);

        return (bool) $statement->fetchColumn();
    }

    private static function fromRow(array $row): self
    {
        return new self(
            funcionarioId: (int) $row['funcionario_id'],
            empresaId: (int) $row['empresa_id'],
            monto: (float) $row['monto'],
            anio: (int) $row['anio'],
            mes: (int) $row['mes'],
            id: isset($row['id']) ? (int) $row['id'] : null,
            creadoEn: isset($row['creado_en']) ? new DateTime($row['creado_en']) : null,
            funcionarioNombre: $row['funcionario_nombre'] ?? null,
            funcionarioDocumento: $row['funcionario_documento'] ?? null,
            empresaNombre: $row['empresa_nombre'] ?? null,
            empresaRuc: $row['empresa_ruc'] ?? null,
            empresaDireccion: $row['empresa_direccion'] ?? null
        );
    }
}