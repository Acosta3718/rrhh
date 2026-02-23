<?php

namespace App\Models;

use App\Core\Database;
use DateTime;
use PDO;

class MarcacionDiaria
{
    public static function obtenerPorRango(Database $db, string $nroIdReloj, DateTime $inicio, DateTime $fin): array
    {
        $statement = $db->pdo()->prepare(
            'SELECT * FROM marcaciones_reloj_diarias '
            . 'WHERE nro_id_reloj = :nro_id_reloj AND fecha >= :inicio AND fecha <= :fin '
            . 'ORDER BY fecha ASC, actualizado_en DESC, creado_en DESC'
        );
        $statement->execute([
            ':nro_id_reloj' => $nroIdReloj,
            ':inicio' => $inicio->format('Y-m-d'),
            ':fin' => $fin->format('Y-m-d')
        ]);

        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        $resultado = [];
        foreach ($rows as $row) {
            if (empty($row['fecha'])) {
                continue;
            }
            $fecha = $row['fecha'];
            if (isset($resultado[$fecha])) {
                continue;
            }
            $resultado[$fecha] = [
                'fecha' => $fecha,
                'entrada' => $row['entrada'] ?? null,
                'salida_almuerzo' => $row['salida_almuerzo'] ?? null,
                'entrada_almuerzo' => $row['entrada_almuerzo'] ?? null,
                'salida' => $row['salida'] ?? null,
                'aplicar' => (bool) ($row['aplicar'] ?? 0)
            ];
        }

        return $resultado;
    }

    public static function upsert(Database $db, array $data): void
    {
        $statementExiste = $db->pdo()->prepare(
            'SELECT id FROM marcaciones_reloj_diarias '
            . 'WHERE nro_id_reloj = :nro_id_reloj AND fecha = :fecha '
            . 'ORDER BY actualizado_en DESC, creado_en DESC, id DESC '
            . 'LIMIT 1'
        );
        $statementExiste->execute([
            ':nro_id_reloj' => $data['nro_id_reloj'],
            ':fecha' => $data['fecha']
        ]);
        $registroId = $statementExiste->fetchColumn();

        if ($registroId) {
            $statement = $db->pdo()->prepare(
                'UPDATE marcaciones_reloj_diarias SET '
                . 'funcionario_id = :funcionario_id, '
                . 'entrada = :entrada, '
                . 'salida_almuerzo = :salida_almuerzo, '
                . 'entrada_almuerzo = :entrada_almuerzo, '
                . 'salida = :salida, '
                . 'aplicar = :aplicar, '
                . 'actualizado_en = :actualizado_en, '
                . 'actualizado_por = :actualizado_por '
                . 'WHERE id = :id'
            );

            $statement->execute([
                ':id' => $registroId,
                ':funcionario_id' => $data['funcionario_id'],
                ':entrada' => $data['entrada'],
                ':salida_almuerzo' => $data['salida_almuerzo'],
                ':entrada_almuerzo' => $data['entrada_almuerzo'],
                ':salida' => $data['salida'],
                ':aplicar' => $data['aplicar'] ? 1 : 0,
                ':actualizado_en' => $data['actualizado_en'],
                ':actualizado_por' => $data['actualizado_por']
            ]);

            return;
        }

        $statement = $db->pdo()->prepare(
            'INSERT INTO marcaciones_reloj_diarias '
            . '(nro_id_reloj, funcionario_id, fecha, entrada, salida_almuerzo, entrada_almuerzo, salida, aplicar, '
            . 'creado_en, actualizado_en, actualizado_por) '
            . 'VALUES (:nro_id_reloj, :funcionario_id, :fecha, :entrada, :salida_almuerzo, :entrada_almuerzo, :salida, '
            . ':aplicar, :creado_en, :actualizado_en, :actualizado_por)'
        );

        $statement->execute([
            ':nro_id_reloj' => $data['nro_id_reloj'],
            ':funcionario_id' => $data['funcionario_id'],
            ':fecha' => $data['fecha'],
            ':entrada' => $data['entrada'],
            ':salida_almuerzo' => $data['salida_almuerzo'],
            ':entrada_almuerzo' => $data['entrada_almuerzo'],
            ':salida' => $data['salida'],
            ':aplicar' => $data['aplicar'] ? 1 : 0,
            ':creado_en' => $data['creado_en'],
            ':actualizado_en' => $data['actualizado_en'],
            ':actualizado_por' => $data['actualizado_por']
        ]);
    }

    public static function sincronizarDesdeReloj(
        Database $db,
        string $nroIdReloj,
        DateTime $fecha,
        ?int $funcionarioId = null
    ): void {
        $statement = $db->pdo()->prepare(
            'SELECT check_time FROM marcaciones_reloj '
            . 'WHERE nro_id_reloj = :nro_id_reloj AND DATE(check_time) = :fecha '
            . 'ORDER BY check_time ASC'
        );
        $statement->execute([
            ':nro_id_reloj' => $nroIdReloj,
            ':fecha' => $fecha->format('Y-m-d')
        ]);

        $marcas = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            if (!empty($row['check_time'])) {
                $marcas[] = (new DateTime($row['check_time']))->format('H:i');
            }
        }

        $horas = self::mapearHorasSegunMarcaciones($marcas);

        self::upsert($db, [
            'nro_id_reloj' => $nroIdReloj,
            'funcionario_id' => $funcionarioId,
            'fecha' => $fecha->format('Y-m-d'),
            'entrada' => $horas['entrada'],
            'salida_almuerzo' => $horas['salida_almuerzo'],
            'entrada_almuerzo' => $horas['entrada_almuerzo'],
            'salida' => $horas['salida'],
            'aplicar' => true,
            'creado_en' => date('Y-m-d H:i:s'),
            'actualizado_en' => date('Y-m-d H:i:s'),
            'actualizado_por' => null
        ]);
    }

    public static function mapearHorasSegunMarcaciones(array $marcas): array
    {
        if (count($marcas) === 2) {
            return [
                'entrada' => $marcas[0] ?? null,
                'salida_almuerzo' => null,
                'entrada_almuerzo' => null,
                'salida' => $marcas[1] ?? null,
            ];
        }

        return [
            'entrada' => $marcas[0] ?? null,
            'salida_almuerzo' => $marcas[1] ?? null,
            'entrada_almuerzo' => $marcas[2] ?? null,
            'salida' => $marcas[3] ?? null,
        ];
    }
}