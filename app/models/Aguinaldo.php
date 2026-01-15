<?php

namespace App\Models;

use App\Core\Database;
use App\Models\Funcionario;
use DateTime;
use PDO;

class Aguinaldo
{
    public function __construct(
        public int $funcionarioId,
        public int $empresaId,
        public float $monto,
        public int $anio,
        public ?int $id = null,
        public ?DateTime $creadoEn = null,
        public ?string $funcionarioNombre = null,
        public ?string $funcionarioDocumento = null,
        public ?string $empresaNombre = null,
        public ?string $empresaRuc = null,
        public ?string $empresaDireccion = null
    ) {
    }

    public function validate(): array
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

        $anioActual = (int) date('Y');
        if ($this->anio < 2000) {
            $errores['anio'] = 'Ingrese un año válido';
        } elseif ($this->anio > $anioActual) {
            $errores['anio'] = 'El año no puede ser mayor al actual';
        }

        return $errores;
    }

    public function save(Database $db): bool
    {
        $statement = $db->pdo()->prepare(
            'INSERT INTO aguinaldos (funcionario_id, empresa_id, monto, anio, creado_en) VALUES (:funcionario_id, :empresa_id, :monto, :anio, :creado_en)'
        );

        $resultado = $statement->execute([
            ':funcionario_id' => $this->funcionarioId,
            ':empresa_id' => $this->empresaId,
            ':monto' => $this->monto,
            ':anio' => $this->anio,
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
            throw new \InvalidArgumentException('No se puede actualizar un aguinaldo sin identificador');
        }

        $statement = $db->pdo()->prepare(
            'UPDATE aguinaldos SET monto = :monto, anio = :anio WHERE id = :id'
        );

        return $statement->execute([
            ':monto' => $this->monto,
            ':anio' => $this->anio,
            ':id' => $this->id
        ]);
    }

    public static function find(Database $db, int $id): ?self
    {
        $statement = $db->pdo()->prepare(
            'SELECT a.*, f.nombre AS funcionario_nombre, f.nro_documento AS funcionario_documento, '
            . 'e.razon_social AS empresa_nombre, e.ruc AS empresa_ruc, e.direccion AS empresa_direccion FROM aguinaldos a '
            . 'LEFT JOIN funcionarios f ON f.id = a.funcionario_id '
            . 'LEFT JOIN empresas e ON e.id = a.empresa_id WHERE a.id = :id'
        );
        $statement->execute([':id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? self::fromRow($row) : null;
    }

    public static function deleteById(Database $db, int $id): bool
    {
        $statement = $db->pdo()->prepare('DELETE FROM aguinaldos WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    public static function search(
        Database $db,
        ?int $empresaId = null,
        ?int $anio = null,
        ?string $nombre = null
    ): array {
        $sql = 'SELECT a.*, f.nombre AS funcionario_nombre, f.nro_documento AS funcionario_documento, '
            . 'e.razon_social AS empresa_nombre, e.ruc AS empresa_ruc, e.direccion AS empresa_direccion FROM aguinaldos a '
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
        if ($nombre) {
            $sql .= ' AND f.nombre LIKE :nombre';
            $params[':nombre'] = '%' . $nombre . '%';
        }

        $sql .= ' ORDER BY a.id DESC';

        $statement = $db->pdo()->prepare($sql);
        $statement->execute($params);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => self::fromRow($row), $rows);
    }

    public static function generarParaEmpresa(Database $db, int $empresaId, int $anio): array
    {
        $creados = 0;
        $omitidos = [];

        $funcionarios = Funcionario::activosPorEmpresa($db, $empresaId);

        foreach ($funcionarios as $funcionario) {
            $totalCobrado = self::totalCobradoAnual($db, $funcionario->id ?? 0, $anio);

            if ($totalCobrado <= 0) {
                $omitidos[] = "{$funcionario->nombre} (sin salarios en {$anio})";
                continue;
            }

            if (self::existsForPeriod($db, $funcionario->id ?? 0, $anio)) {
                $omitidos[] = "{$funcionario->nombre} (ya tiene aguinaldo en {$anio})";
                continue;
            }

            $aguinaldo = new self(
                funcionarioId: $funcionario->id ?? 0,
                empresaId: $empresaId,
                monto: self::calcularMontoDesdeTotal($totalCobrado),
                anio: $anio
            );

            if ($aguinaldo->save($db)) {
                $creados++;
            }
        }

        return ['creados' => $creados, 'omitidos' => $omitidos];
    }

    public static function existsForPeriod(Database $db, int $funcionarioId, int $anio): bool
    {
        $statement = $db->pdo()->prepare(
            'SELECT 1 FROM aguinaldos WHERE funcionario_id = :funcionario_id AND anio = :anio LIMIT 1'
        );
        $statement->execute([
            ':funcionario_id' => $funcionarioId,
            ':anio' => $anio
        ]);

        return (bool) $statement->fetchColumn();
    }

    public static function totalCobradoAnual(Database $db, int $funcionarioId, int $anio): float
    {
        $statement = $db->pdo()->prepare(
            'SELECT COALESCE(SUM(salario_neto), 0) FROM salarios WHERE funcionario_id = :funcionario_id AND anio = :anio'
        );
        $statement->execute([
            ':funcionario_id' => $funcionarioId,
            ':anio' => $anio
        ]);

        return (float) $statement->fetchColumn();
    }

    public static function calcularMontoDesdeTotal(float $totalCobrado): float
    {
        return $totalCobrado / 12;
    }

    private static function fromRow(array $row): self
    {
        return new self(
            funcionarioId: (int) $row['funcionario_id'],
            empresaId: (int) $row['empresa_id'],
            monto: (float) $row['monto'],
            anio: (int) $row['anio'],
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