<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class SalarioMovimiento
{
    public function __construct(
        public int $salarioId,
        public int $tipoMovimientoId,
        public float $monto,
        public ?int $id = null,
        public ?string $tipoDescripcion = null,
        public ?string $tipoEstado = null,
        public ?string $tipo = null
    ) {
    }

    public static function listBySalario(Database $db, int $salarioId): array
    {
        $statement = $db->pdo()->prepare(
            'SELECT sm.id, sm.salario_id, sm.tipo_movimiento_id, sm.monto, '
            . 'tm.descripcion, tm.estado, tm.tipo '
            . 'FROM salario_movimientos sm '
            . 'INNER JOIN tipos_movimientos tm ON tm.id = sm.tipo_movimiento_id '
            . 'WHERE sm.salario_id = :salario_id '
            . 'ORDER BY tm.descripcion ASC'
        );
        $statement->execute([':salario_id' => $salarioId]);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => self::fromRow($row), $rows);
    }

    public static function replaceForSalario(Database $db, int $salarioId, array $movimientos): void
    {
        $db->pdo()->prepare('DELETE FROM salario_movimientos WHERE salario_id = :salario_id')
            ->execute([':salario_id' => $salarioId]);

        if (empty($movimientos)) {
            return;
        }

        $statement = $db->pdo()->prepare(
            'INSERT INTO salario_movimientos (salario_id, tipo_movimiento_id, monto) '
            . 'VALUES (:salario_id, :tipo_movimiento_id, :monto)'
        );

        foreach ($movimientos as $tipoMovimientoId => $monto) {
            $statement->execute([
                ':salario_id' => $salarioId,
                ':tipo_movimiento_id' => $tipoMovimientoId,
                ':monto' => $monto
            ]);
        }
    }

    public static function deleteBySalarioId(Database $db, int $salarioId): void
    {
        $statement = $db->pdo()->prepare('DELETE FROM salario_movimientos WHERE salario_id = :salario_id');
        $statement->execute([':salario_id' => $salarioId]);
    }

    private static function fromRow(array $row): self
    {
        return new self(
            salarioId: (int) $row['salario_id'],
            tipoMovimientoId: (int) $row['tipo_movimiento_id'],
            monto: (float) $row['monto'],
            id: isset($row['id']) ? (int) $row['id'] : null,
            tipoDescripcion: $row['descripcion'] ?? null,
            tipoEstado: $row['estado'] ?? null,
            tipo: $row['tipo'] ?? null
        );
    }
}