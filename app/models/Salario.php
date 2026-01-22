<?php

namespace App\Models;

use App\Core\Database;
use App\Models\MarcacionReloj;
use App\Models\SalarioMovimiento;
use DateTime;
use PDO;

class Salario
{
    public function __construct(
        public int $funcionarioId,
        public int $empresaId,
        public float $salarioBase,
        public float $adelanto,
        public float $ips,
        public float $salarioNeto,
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

    public function validate(): array
    {
        $errores = [];

        if ($this->funcionarioId <= 0) {
            $errores['funcionario_id'] = 'Seleccione un funcionario válido';
        }
        if ($this->empresaId <= 0) {
            $errores['empresa_id'] = 'Seleccione una empresa válida';
        }
        if ($this->salarioBase <= 0) {
            $errores['salario_base'] = 'El salario base debe ser mayor a cero';
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
            'INSERT INTO salarios (funcionario_id, empresa_id, salario_base, adelanto, ips, salario_neto, anio, mes, creado_en) '
            . 'VALUES (:funcionario_id, :empresa_id, :salario_base, :adelanto, :ips, :salario_neto, :anio, :mes, :creado_en)'
        );

        $resultado = $statement->execute([
            ':funcionario_id' => $this->funcionarioId,
            ':empresa_id' => $this->empresaId,
            ':salario_base' => $this->salarioBase,
            ':adelanto' => $this->adelanto,
            ':ips' => $this->ips,
            ':salario_neto' => $this->salarioNeto,
            ':anio' => $this->anio,
            ':mes' => $this->mes,
            ':creado_en' => $this->creadoEn?->format('Y-m-d H:i:s') ?? (new DateTime())->format('Y-m-d H:i:s')
        ]);

        if ($resultado) {
            $this->id = (int) $db->pdo()->lastInsertId();
        }

        return $resultado;
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
        $sql = 'SELECT s.*, f.nombre AS funcionario_nombre, f.nro_documento AS funcionario_documento, '
            . 'e.razon_social AS empresa_nombre, e.ruc AS empresa_ruc, e.direccion AS empresa_direccion FROM salarios s '
            . 'LEFT JOIN funcionarios f ON f.id = s.funcionario_id '
            . 'LEFT JOIN empresas e ON e.id = s.empresa_id WHERE 1=1';
        $params = [];

        if ($empresaId) {
            $sql .= ' AND s.empresa_id = :empresa_id';
            $params[':empresa_id'] = $empresaId;
        }
        if ($anio) {
            $sql .= ' AND s.anio = :anio';
            $params[':anio'] = $anio;
        }
        if ($mes) {
            $sql .= ' AND s.mes = :mes';
            $params[':mes'] = $mes;
        }
        if ($nombre) {
            $sql .= ' AND f.nombre LIKE :nombre';
            $params[':nombre'] = '%' . $nombre . '%';
        }

        $sql .= ' ORDER BY s.id DESC';

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
        $sql = 'SELECT COUNT(*) FROM salarios s '
            . 'LEFT JOIN funcionarios f ON f.id = s.funcionario_id WHERE 1=1';
        $params = [];

        if ($empresaId) {
            $sql .= ' AND s.empresa_id = :empresa_id';
            $params[':empresa_id'] = $empresaId;
        }
        if ($anio) {
            $sql .= ' AND s.anio = :anio';
            $params[':anio'] = $anio;
        }
        if ($mes) {
            $sql .= ' AND s.mes = :mes';
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

     public static function find(Database $db, int $id): ?self
    {
        $statement = $db->pdo()->prepare(
            'SELECT s.*, f.nombre AS funcionario_nombre, f.nro_documento AS funcionario_documento, '
            . 'e.razon_social AS empresa_nombre, e.ruc AS empresa_ruc, e.direccion AS empresa_direccion FROM salarios s '
            . 'LEFT JOIN funcionarios f ON f.id = s.funcionario_id '
            . 'LEFT JOIN empresas e ON e.id = s.empresa_id WHERE s.id = :id'
        );
        $statement->execute([':id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return self::fromRow($row);
    }

    public static function existsForPeriod(Database $db, int $funcionarioId, int $anio, int $mes): bool
    {
        $statement = $db->pdo()->prepare(
            'SELECT 1 FROM salarios WHERE funcionario_id = :funcionario_id AND anio = :anio AND mes = :mes LIMIT 1'
        );
        $statement->execute([
            ':funcionario_id' => $funcionarioId,
            ':anio' => $anio,
            ':mes' => $mes
        ]);

        return (bool) $statement->fetchColumn();
    }

    public static function existsForPeriodExcludingId(Database $db, int $funcionarioId, int $anio, int $mes, int $excludeId): bool
    {
        $statement = $db->pdo()->prepare(
            'SELECT 1 FROM salarios WHERE funcionario_id = :funcionario_id AND anio = :anio AND mes = :mes '
            . 'AND id <> :id LIMIT 1'
        );
        $statement->execute([
            ':funcionario_id' => $funcionarioId,
            ':anio' => $anio,
            ':mes' => $mes,
            ':id' => $excludeId
        ]);

        return (bool) $statement->fetchColumn();
    }

    public static function findByFuncionarioPeriodo(Database $db, int $funcionarioId, int $anio, int $mes): ?self
    {
        $statement = $db->pdo()->prepare(
            'SELECT s.*, f.nombre AS funcionario_nombre, f.nro_documento AS funcionario_documento, '
            . 'e.razon_social AS empresa_nombre, e.ruc AS empresa_ruc, e.direccion AS empresa_direccion FROM salarios s '
            . 'LEFT JOIN funcionarios f ON f.id = s.funcionario_id '
            . 'LEFT JOIN empresas e ON e.id = s.empresa_id '
            . 'WHERE s.funcionario_id = :funcionario_id AND s.anio = :anio AND s.mes = :mes LIMIT 1'
        );
        $statement->execute([
            ':funcionario_id' => $funcionarioId,
            ':anio' => $anio,
            ':mes' => $mes
        ]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            return null;
        }

        return self::fromRow($row);
    }

    public static function calcularIps(
        ?Funcionario $funcionario,
        float $aporteObrero,
        float $salarioMinimo,
        float $totalCreditos
    ): float {
        if (!$funcionario?->tieneIps) {
            return 0.0;
        }

        if ($funcionario->calculaIpsTotal) {
            return ($totalCreditos * $aporteObrero)/100;
        }

        if ($funcionario->calculaIpsMinimo) {
            return ($salarioMinimo * $aporteObrero)/100;
        }

        return $funcionario->salario * ($aporteObrero)/100;
    }

    public static function generarParaEmpresa(Database $db, int $empresaId, int $anio, int $mes, float $aporteObrero, float $salarioMinimo): array
    {
        $creados = 0;
        $omitidos = [];

        $funcionarios = Funcionario::activosPorEmpresa($db, $empresaId);

        foreach ($funcionarios as $funcionario) {
            if ($funcionario->salario <= 0) {
                $omitidos[] = "{$funcionario->nombre} (sin salario cargado)";
                continue;
            }

            if (self::existsForPeriod($db, $funcionario->id ?? 0, $anio, $mes)) {
                $omitidos[] = "{$funcionario->nombre} (ya tiene salario en {$mes}/{$anio})";
                continue;
            }

            $adelanto = Adelanto::findByFuncionarioPeriodo($db, $funcionario->id ?? 0, $anio, $mes);
            $montoAdelanto = $adelanto?->monto ?? 0.0;
            $movimientosReloj = MarcacionReloj::calcularMovimientosParaPeriodo($db, $funcionario, $anio, $mes);
            $totalCreditos = $funcionario->salario + ($movimientosReloj['total_creditos'] ?? 0.0);
            $totalDebitosReloj = $movimientosReloj['total_debitos'] ?? 0.0;
            $ips = self::calcularIps($funcionario, $aporteObrero, $salarioMinimo, $totalCreditos);
            $salarioNeto = $totalCreditos - ($montoAdelanto + $ips + $totalDebitosReloj);

            $salario = new self(
                funcionarioId: $funcionario->id ?? 0,
                empresaId: $empresaId,
                salarioBase: $funcionario->salario,
                adelanto: $montoAdelanto,
                ips: $ips,
                salarioNeto: $salarioNeto,
                anio: $anio,
                mes: $mes
            );

            if ($salario->save($db)) {
                if (!empty($movimientosReloj['movimientos'])) {
                    SalarioMovimiento::replaceForSalario($db, $salario->id ?? 0, $movimientosReloj['movimientos']);
                }
                $creados++;
            }
        }

        return ['creados' => $creados, 'omitidos' => $omitidos];
    }

    public function update(Database $db): bool
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException('No se puede actualizar un salario sin identificador');
        }

        $statement = $db->pdo()->prepare(
            'UPDATE salarios SET salario_base = :salario_base, adelanto = :adelanto, ips = :ips, '
            . 'salario_neto = :salario_neto, anio = :anio, mes = :mes WHERE id = :id'
        );

        return $statement->execute([
            ':salario_base' => $this->salarioBase,
            ':adelanto' => $this->adelanto,
            ':ips' => $this->ips,
            ':salario_neto' => $this->salarioNeto,
            ':anio' => $this->anio,
            ':mes' => $this->mes,
            ':id' => $this->id
        ]);
    }

    public static function deleteById(Database $db, int $id): bool
    {
        $statement = $db->pdo()->prepare('DELETE FROM salarios WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    private static function fromRow(array $row): self
    {
        return new self(
            funcionarioId: (int) $row['funcionario_id'],
            empresaId: (int) $row['empresa_id'],
            salarioBase: (float) $row['salario_base'],
            adelanto: (float) $row['adelanto'],
            ips: (float) $row['ips'],
            salarioNeto: (float) $row['salario_neto'],
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