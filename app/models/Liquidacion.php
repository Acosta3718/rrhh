<?php

namespace App\Models;

use App\Core\Database;
use DateTime;
use PDO;

class Liquidacion
{
    public const TIPOS_SALIDA = [
        'Renuncia',
        'Renuncia Justificada',
        'Despido Justificado',
        'Despido Injustificado'
    ];

    public function __construct(
        public int $funcionarioId,
        public int $empresaId,
        public DateTime $fechaSalida,
        public string $tipoSalida,
        public int $diasTrabajados,
        public float $descuentos,
        public float $salarioDiario,
        public float $salarioMes,
        public int $aniosServicio,
        public int $preavisoDias,
        public float $preavisoMonto,
        public int $vacacionesDias,
        public float $vacacionesMonto,
        public float $indemnizacion,
        public float $aguinaldo,
        public float $total,
        public ?int $id = null,
        public ?DateTime $creadoEn = null,
        public ?string $funcionarioNombre = null,
        public ?string $empresaNombre = null
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
        if (!in_array($this->tipoSalida, self::TIPOS_SALIDA, true)) {
            $errores['tipo_salida'] = 'Seleccione un tipo de salida válido';
        }
        if ($this->diasTrabajados < 1 || $this->diasTrabajados > 30) {
            $errores['dias_trabajados'] = 'Los días trabajados deben estar entre 1 y 30';
        }
        if ($this->descuentos < 0) {
            $errores['descuentos'] = 'Los descuentos no pueden ser negativos';
        }

        return $errores;
    }

    public function save(Database $db): bool
    {
        $statement = $db->pdo()->prepare(
            'INSERT INTO liquidaciones (funcionario_id, empresa_id, fecha_salida, tipo_salida, dias_trabajados, descuentos, salario_diario, salario_mes, anios_servicio, preaviso_dias, preaviso_monto, vacaciones_dias, vacaciones_monto, indemnizacion, aguinaldo, total, creado_en) '
            . 'VALUES (:funcionario_id, :empresa_id, :fecha_salida, :tipo_salida, :dias_trabajados, :descuentos, :salario_diario, :salario_mes, :anios_servicio, :preaviso_dias, :preaviso_monto, :vacaciones_dias, :vacaciones_monto, :indemnizacion, :aguinaldo, :total, :creado_en)'
        );

        $resultado = $statement->execute([
            ':funcionario_id' => $this->funcionarioId,
            ':empresa_id' => $this->empresaId,
            ':fecha_salida' => $this->fechaSalida->format('Y-m-d'),
            ':tipo_salida' => $this->tipoSalida,
            ':dias_trabajados' => $this->diasTrabajados,
            ':descuentos' => $this->descuentos,
            ':salario_diario' => $this->salarioDiario,
            ':salario_mes' => $this->salarioMes,
            ':anios_servicio' => $this->aniosServicio,
            ':preaviso_dias' => $this->preavisoDias,
            ':preaviso_monto' => $this->preavisoMonto,
            ':vacaciones_dias' => $this->vacacionesDias,
            ':vacaciones_monto' => $this->vacacionesMonto,
            ':indemnizacion' => $this->indemnizacion,
            ':aguinaldo' => $this->aguinaldo,
            ':total' => $this->total,
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
            throw new \InvalidArgumentException('No se puede actualizar una liquidación sin identificador');
        }

        $statement = $db->pdo()->prepare(
            'UPDATE liquidaciones SET fecha_salida = :fecha_salida, tipo_salida = :tipo_salida, dias_trabajados = :dias_trabajados, descuentos = :descuentos, salario_diario = :salario_diario, salario_mes = :salario_mes, anios_servicio = :anios_servicio, preaviso_dias = :preaviso_dias, preaviso_monto = :preaviso_monto, vacaciones_dias = :vacaciones_dias, vacaciones_monto = :vacaciones_monto, indemnizacion = :indemnizacion, aguinaldo = :aguinaldo, total = :total WHERE id = :id'
        );

        return $statement->execute([
            ':fecha_salida' => $this->fechaSalida->format('Y-m-d'),
            ':tipo_salida' => $this->tipoSalida,
            ':dias_trabajados' => $this->diasTrabajados,
            ':descuentos' => $this->descuentos,
            ':salario_diario' => $this->salarioDiario,
            ':salario_mes' => $this->salarioMes,
            ':anios_servicio' => $this->aniosServicio,
            ':preaviso_dias' => $this->preavisoDias,
            ':preaviso_monto' => $this->preavisoMonto,
            ':vacaciones_dias' => $this->vacacionesDias,
            ':vacaciones_monto' => $this->vacacionesMonto,
            ':indemnizacion' => $this->indemnizacion,
            ':aguinaldo' => $this->aguinaldo,
            ':total' => $this->total,
            ':id' => $this->id
        ]);
    }

    public static function deleteById(Database $db, int $id): bool
    {
        $statement = $db->pdo()->prepare('DELETE FROM liquidaciones WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    public static function find(Database $db, int $id): ?self
    {
        $statement = $db->pdo()->prepare(
            'SELECT l.*, f.nombre AS funcionario_nombre, e.razon_social AS empresa_nombre FROM liquidaciones l '
            . 'LEFT JOIN funcionarios f ON f.id = l.funcionario_id '
            . 'LEFT JOIN empresas e ON e.id = l.empresa_id WHERE l.id = :id'
        );
        $statement->execute([':id' => $id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? self::fromRow($row) : null;
    }

    public static function existsByFuncionario(Database $db, int $funcionarioId, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM liquidaciones WHERE funcionario_id = :funcionario_id';
        $params = [':funcionario_id' => $funcionarioId];

        if ($excludeId !== null) {
            $sql .= ' AND id <> :id';
            $params[':id'] = $excludeId;
        }

        $statement = $db->pdo()->prepare($sql . ' LIMIT 1');
        $statement->execute($params);

        return (bool) $statement->fetchColumn();
    }

    public static function search(
        Database $db,
        ?int $empresaId = null,
        ?string $nombre = null,
        ?string $tipoSalida = null,
        ?int $limit = null,
        ?int $offset = null
    ): array {
        $sql = 'SELECT l.*, f.nombre AS funcionario_nombre, e.razon_social AS empresa_nombre FROM liquidaciones l '
            . 'LEFT JOIN funcionarios f ON f.id = l.funcionario_id '
            . 'LEFT JOIN empresas e ON e.id = l.empresa_id WHERE 1=1';
        $params = [];

        if ($empresaId) {
            $sql .= ' AND l.empresa_id = :empresa_id';
            $params[':empresa_id'] = $empresaId;
        }
        if ($nombre) {
            $sql .= ' AND f.nombre LIKE :nombre';
            $params[':nombre'] = '%' . $nombre . '%';
        }
        if ($tipoSalida) {
            $sql .= ' AND l.tipo_salida = :tipo_salida';
            $params[':tipo_salida'] = $tipoSalida;
        }

        $sql .= ' ORDER BY l.id DESC';

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

    public static function countSearch(Database $db, ?int $empresaId = null, ?string $nombre = null, ?string $tipoSalida = null): int
    {
        $sql = 'SELECT COUNT(*) FROM liquidaciones l '
            . 'LEFT JOIN funcionarios f ON f.id = l.funcionario_id WHERE 1=1';
        $params = [];

        if ($empresaId) {
            $sql .= ' AND l.empresa_id = :empresa_id';
            $params[':empresa_id'] = $empresaId;
        }
        if ($nombre) {
            $sql .= ' AND f.nombre LIKE :nombre';
            $params[':nombre'] = '%' . $nombre . '%';
        }
        if ($tipoSalida) {
            $sql .= ' AND l.tipo_salida = :tipo_salida';
            $params[':tipo_salida'] = $tipoSalida;
        }

        $statement = $db->pdo()->prepare($sql);
        $statement->execute($params);

        return (int) $statement->fetchColumn();
    }

    public static function findLatestByFuncionario(Database $db, int $funcionarioId): ?self
    {
        $statement = $db->pdo()->prepare(
            'SELECT l.*, f.nombre AS funcionario_nombre, e.razon_social AS empresa_nombre FROM liquidaciones l '
            . 'LEFT JOIN funcionarios f ON f.id = l.funcionario_id '
            . 'LEFT JOIN empresas e ON e.id = l.empresa_id '
            . 'WHERE l.funcionario_id = :funcionario_id '
            . 'ORDER BY l.fecha_salida DESC, l.id DESC LIMIT 1'
        );
        $statement->execute([':funcionario_id' => $funcionarioId]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? self::fromRow($row) : null;
    }

    public static function calcularDetalle(Funcionario $funcionario, DateTime $fechaSalida, string $tipoSalida, int $diasTrabajados, float $descuentos): array
    {
        $salarioDiario = $funcionario->salario / 30;
        $salarioMes = $salarioDiario * $diasTrabajados;
        $antiguedad = self::calcularAntiguedad($funcionario->fechaIngreso, $fechaSalida);
        $aniosServicio = self::calcularAniosServicio($funcionario->fechaIngreso, $fechaSalida);
        $preavisoDias = self::calcularPreavisoDias($antiguedad);
        $vacacionesDias = self::calcularVacacionesDias($antiguedad);
        $aguinaldo = self::calcularAguinaldo($funcionario->salario, $funcionario->fechaIngreso, $fechaSalida);

        $preavisoMonto = 0.0;
        $indemnizacion = 0.0;
        $aplicaIndemnizacion = in_array($tipoSalida, ['Renuncia Justificada', 'Despido Injustificado'], true);
        $aplicaPreaviso = in_array($tipoSalida, ['Renuncia Justificada', 'Despido Injustificado'], true);

        if ($aplicaIndemnizacion) {
            $indemnizacion = $salarioDiario * 15 * $aniosServicio;
        }
        if ($aplicaPreaviso) {
            $preavisoMonto = $salarioDiario * $preavisoDias;
        }

        $vacacionesMonto = $salarioDiario * $vacacionesDias;
        $total = $salarioMes + $aguinaldo + $vacacionesMonto + $preavisoMonto + $indemnizacion - $descuentos;

        return [
            'salario_diario' => $salarioDiario,
            'salario_mes' => $salarioMes,
            'antiguedad' => $antiguedad,
            'anios_servicio' => $aniosServicio,
            'preaviso_dias' => $preavisoDias,
            'preaviso_monto' => $preavisoMonto,
            'vacaciones_dias' => $vacacionesDias,
            'vacaciones_monto' => $vacacionesMonto,
            'aguinaldo' => $aguinaldo,
            'indemnizacion' => $indemnizacion,
            'total' => $total
        ];
    }

    private static function calcularAntiguedad(?DateTime $fechaIngreso, DateTime $fechaSalida): float
    {
        if (!$fechaIngreso) {
            return 0.0;
        }

        $diff = $fechaIngreso->diff($fechaSalida);
        return $diff->y + ($diff->m / 12);
    }

    private static function calcularAniosServicio(?DateTime $fechaIngreso, DateTime $fechaSalida): int
    {
        if (!$fechaIngreso) {
            return 0;
        }

        $diff = $fechaIngreso->diff($fechaSalida);
        $anios = $diff->y;
        if ($diff->m >= 6 || ($diff->m === 5 && $diff->d > 0)) {
            $anios++;
        }

        return max($anios, 0);
    }

    private static function calcularPreavisoDias(float $antiguedad): int
    {
        if ($antiguedad <= 1) {
            return 30;
        }
        if ($antiguedad <= 5) {
            return 45;
        }
        if ($antiguedad <= 10) {
            return 60;
        }

        return 90;
    }

    private static function calcularVacacionesDias(float $antiguedad): int
    {
        if ($antiguedad < 1) {
            return 0;
        }
        if ($antiguedad <= 5) {
            return 12;
        }
        if ($antiguedad <= 10) {
            return 18;
        }

        return 30;
    }

    private static function calcularAguinaldo(float $salario, ?DateTime $fechaIngreso, DateTime $fechaSalida): float
    {
        if (!$fechaIngreso) {
            return 0.0;
        }

        $inicioAnio = new DateTime($fechaSalida->format('Y-01-01'));
        $inicio = $fechaIngreso > $inicioAnio ? $fechaIngreso : $inicioAnio;
        $diff = $inicio->diff($fechaSalida);
        $meses = ($diff->y * 12) + $diff->m + 1;
        $meses = max(0, min(12, $meses));

        return ($salario * $meses) / 12;
    }

    private static function fromRow(array $row): self
    {
        return new self(
            funcionarioId: (int) $row['funcionario_id'],
            empresaId: (int) $row['empresa_id'],
            fechaSalida: new DateTime($row['fecha_salida']),
            tipoSalida: $row['tipo_salida'],
            diasTrabajados: (int) $row['dias_trabajados'],
            descuentos: (float) $row['descuentos'],
            salarioDiario: (float) $row['salario_diario'],
            salarioMes: (float) $row['salario_mes'],
            aniosServicio: (int) $row['anios_servicio'],
            preavisoDias: (int) $row['preaviso_dias'],
            preavisoMonto: (float) $row['preaviso_monto'],
            vacacionesDias: (int) $row['vacaciones_dias'],
            vacacionesMonto: (float) $row['vacaciones_monto'],
            indemnizacion: (float) $row['indemnizacion'],
            aguinaldo: (float) $row['aguinaldo'],
            total: (float) $row['total'],
            id: (int) $row['id'],
            creadoEn: isset($row['creado_en']) ? new DateTime($row['creado_en']) : null,
            funcionarioNombre: $row['funcionario_nombre'] ?? null,
            empresaNombre: $row['empresa_nombre'] ?? null
        );
    }
}