<?php

namespace App\Models;

use App\Core\Database;
use DateTime;
use PDO;

class MarcacionReloj
{
    public function __construct(
        public string $nroIdReloj,
        public DateTime $checkTime,
        public ?int $funcionarioId = null,
        public ?int $id = null
    ) {
    }

    public static function insertarSiNoExiste(Database $db, string $nroIdReloj, DateTime $checkTime, ?int $funcionarioId): bool
    {
        $statement = $db->pdo()->prepare(
            'SELECT 1 FROM marcaciones_reloj WHERE nro_id_reloj = :nro_id_reloj AND check_time = :check_time LIMIT 1'
        );
        $statement->execute([
            ':nro_id_reloj' => $nroIdReloj,
            ':check_time' => $checkTime->format('Y-m-d H:i:s')
        ]);

        if ($statement->fetchColumn()) {
            return false;
        }

        $insert = $db->pdo()->prepare(
            'INSERT INTO marcaciones_reloj (nro_id_reloj, funcionario_id, check_time, creado_en) '
            . 'VALUES (:nro_id_reloj, :funcionario_id, :check_time, :creado_en)'
        );

        return $insert->execute([
            ':nro_id_reloj' => $nroIdReloj,
            ':funcionario_id' => $funcionarioId,
            ':check_time' => $checkTime->format('Y-m-d H:i:s'),
            ':creado_en' => (new DateTime())->format('Y-m-d H:i:s')
        ]);
    }

    public static function calcularMovimientosParaPeriodo(
        Database $db,
        Funcionario $funcionario,
        int $anio,
        int $mes
    ): array {
        $nroIdReloj = trim((string) ($funcionario->nroIdReloj ?? ''));
        if ($nroIdReloj === '' || !$funcionario->turnoId) {
            return ['movimientos' => [], 'total_creditos' => 0.0, 'total_debitos' => 0.0];
        }

        $turno = Turno::find($db, $funcionario->turnoId);
        if (!$turno) {
            return ['movimientos' => [], 'total_creditos' => 0.0, 'total_debitos' => 0.0];
        }

        $inicioPeriodo = new DateTime(sprintf('%04d-%02d-01 00:00:00', $anio, $mes));
        $finPeriodo = (clone $inicioPeriodo)->modify('first day of next month');

        $statement = $db->pdo()->prepare(
            'SELECT DATE(check_time) AS fecha, MIN(check_time) AS primera, MAX(check_time) AS ultima '
            . 'FROM marcaciones_reloj '
            . 'WHERE nro_id_reloj = :nro_id_reloj AND check_time >= :inicio AND check_time < :fin '
            . 'GROUP BY DATE(check_time) '
            . 'ORDER BY fecha ASC'
        );
        $statement->execute([
            ':nro_id_reloj' => $nroIdReloj,
            ':inicio' => $inicioPeriodo->format('Y-m-d H:i:s'),
            ':fin' => $finPeriodo->format('Y-m-d H:i:s')
        ]);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        if (empty($rows)) {
            return ['movimientos' => [], 'total_creditos' => 0.0, 'total_debitos' => 0.0];
        }

        $minutosTarde = 0;
        $minutosSalidaAnticipada = 0;
        $minutosExtra = 0;

        foreach ($rows as $row) {
            $fecha = new DateTime($row['fecha'] ?? 'now');

            if ($turno->fechaInicio && $fecha < $turno->fechaInicio) {
                continue;
            }
            if ($turno->fechaFin && $fecha > $turno->fechaFin) {
                continue;
            }

            $primera = isset($row['primera']) ? new DateTime($row['primera']) : null;
            $ultima = isset($row['ultima']) ? new DateTime($row['ultima']) : null;
            if (!$primera || !$ultima) {
                continue;
            }

            $entrada = new DateTime($fecha->format('Y-m-d') . ' ' . $turno->horaEntrada);
            $salida = new DateTime($fecha->format('Y-m-d') . ' ' . $turno->horaSalida);

            if ($primera > $entrada) {
                $minutosTarde += (int) round(($primera->getTimestamp() - $entrada->getTimestamp()) / 60);
            }

            if ($ultima < $salida) {
                $minutosSalidaAnticipada += (int) round(($salida->getTimestamp() - $ultima->getTimestamp()) / 60);
            }

            if ($ultima > $salida) {
                $minutosExtra += (int) round(($ultima->getTimestamp() - $salida->getTimestamp()) / 60);
            }
        }

        $minutosJornada = self::calcularMinutosJornada($turno);
        if ($minutosJornada <= 0) {
            return ['movimientos' => [], 'total_creditos' => 0.0, 'total_debitos' => 0.0];
        }

        $tarifaHora = ($funcionario->salario / 30) / ($minutosJornada / 60);
        if ($tarifaHora <= 0) {
            return ['movimientos' => [], 'total_creditos' => 0.0, 'total_debitos' => 0.0];
        }

        $movimientos = [];
        $totalCreditos = 0.0;
        $totalDebitos = 0.0;

        $montoTarde = round(($minutosTarde / 60) * $tarifaHora, 2);
        $montoSalidaAnticipada = round(($minutosSalidaAnticipada / 60) * $tarifaHora, 2);
        $montoExtra = round(($minutosExtra / 60) * $tarifaHora, 2);

        if ($montoTarde > 0) {
            $tipo = TipoMovimiento::findByDescripcion($db, 'Llegada tardía');
            if ($tipo && $tipo->id) {
                $movimientos[$tipo->id] = $montoTarde;
                $totalDebitos += $montoTarde;
            }
        }

        if ($montoSalidaAnticipada > 0) {
            $tipo = TipoMovimiento::findByDescripcion($db, 'Salida anticipada');
            if ($tipo && $tipo->id) {
                $movimientos[$tipo->id] = $montoSalidaAnticipada;
                $totalDebitos += $montoSalidaAnticipada;
            }
        }

        if ($montoExtra > 0) {
            $tipo = TipoMovimiento::findByDescripcion($db, 'Horas extras');
            if ($tipo && $tipo->id) {
                $movimientos[$tipo->id] = $montoExtra;
                $totalCreditos += $montoExtra;
            }
        }

        return [
            'movimientos' => $movimientos,
            'total_creditos' => $totalCreditos,
            'total_debitos' => $totalDebitos
        ];
    }

    private static function calcularMinutosJornada(Turno $turno): int
    {
        $fechaBase = '2000-01-01 ';
        $entrada = new DateTime($fechaBase . $turno->horaEntrada);
        $salida = new DateTime($fechaBase . $turno->horaSalida);
        $salidaAlmuerzo = new DateTime($fechaBase . $turno->horaSalidaAlmuerzo);
        $retornoAlmuerzo = new DateTime($fechaBase . $turno->horaRetornoAlmuerzo);

        $minutosTotales = (int) round(($salida->getTimestamp() - $entrada->getTimestamp()) / 60);
        $minutosAlmuerzo = (int) round(($retornoAlmuerzo->getTimestamp() - $salidaAlmuerzo->getTimestamp()) / 60);

        return max(0, $minutosTotales - $minutosAlmuerzo);
    }
}