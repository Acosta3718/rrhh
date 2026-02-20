<?php

namespace App\Models;

use App\Core\Database;
use DateTime;
use PDO;

class Turno
{
    private const DIAS_SEMANA = [
        1 => 'lunes',
        2 => 'martes',
        3 => 'miercoles',
        4 => 'jueves',
        5 => 'viernes',
        6 => 'sabado',
        0 => 'domingo'
    ];

    public function __construct(
        public string $nombre,
        public ?DateTime $fechaInicio,
        public ?DateTime $fechaFin,
        public string $horaEntrada,
        public string $horaSalidaAlmuerzo,
        public string $horaRetornoAlmuerzo,
        public string $horaSalida,
        public array $horariosPorDia = [],
        public ?int $id = null
    ) {
        $this->horariosPorDia = self::normalizarHorariosPorDia(
            $this->horariosPorDia,
            [
                'hora_entrada' => $this->horaEntrada,
                'hora_salida_almuerzo' => $this->horaSalidaAlmuerzo,
                'hora_retorno_almuerzo' => $this->horaRetornoAlmuerzo,
                'hora_salida' => $this->horaSalida
            ]
        );
    }

    public function validate(): array
    {
        $errores = [];

        if (trim($this->nombre) === '') {
            $errores['nombre'] = 'El nombre es obligatorio';
        }
        if (!$this->fechaInicio instanceof DateTime) {
            $errores['fecha_inicio'] = 'La fecha de inicio es obligatoria';
        }
        if (!$this->fechaFin instanceof DateTime) {
            $errores['fecha_fin'] = 'La fecha de fin es obligatoria';
        }
        if ($this->fechaInicio instanceof DateTime && $this->fechaFin instanceof DateTime
            && $this->fechaFin < $this->fechaInicio) {
            $errores['fecha_fin'] = 'La fecha de fin no puede ser menor a la fecha de inicio';
        }

        foreach ($this->horariosPorDia as $dia => $horario) {
            $activo = (bool) ($horario['activo'] ?? true);
            if (!$activo) {
                continue;
            }

            foreach (['hora_entrada', 'hora_salida_almuerzo', 'hora_retorno_almuerzo', 'hora_salida'] as $campo) {
                if (!self::esHoraValida($horario[$campo] ?? null)) {
                    $errores[$dia . '_' . $campo] = 'Ingrese una hora válida en formato HH:MM';
                }
            }
        }

        return $errores;
    }

    public function save(Database $db): bool
    {
        $statement = $db->pdo()->prepare(
            'INSERT INTO turnos (nombre, fecha_inicio, fecha_fin, hora_entrada, hora_salida_almuerzo, hora_retorno_almuerzo, hora_salida, horarios_por_dia) '
            . 'VALUES (:nombre, :fecha_inicio, :fecha_fin, :hora_entrada, :hora_salida_almuerzo, :hora_retorno_almuerzo, :hora_salida, :horarios_por_dia)'
        );

        $resultado = $statement->execute([
            ':nombre' => $this->nombre,
            ':fecha_inicio' => $this->fechaInicio?->format('Y-m-d'),
            ':fecha_fin' => $this->fechaFin?->format('Y-m-d'),
            ':hora_entrada' => $this->horaEntrada,
            ':hora_salida_almuerzo' => $this->horaSalidaAlmuerzo,
            ':hora_retorno_almuerzo' => $this->horaRetornoAlmuerzo,
            ':hora_salida' => $this->horaSalida,
            ':horarios_por_dia' => json_encode($this->horariosPorDia)
        ]);

        if ($resultado) {
            $this->id = (int) $db->pdo()->lastInsertId();
        }

        return $resultado;
    }

    public function update(Database $db): bool
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException('No se puede actualizar un turno sin identificador');
        }

        $statement = $db->pdo()->prepare(
            'UPDATE turnos SET nombre = :nombre, fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin, '
            . 'hora_entrada = :hora_entrada, hora_salida_almuerzo = :hora_salida_almuerzo, '
            . 'hora_retorno_almuerzo = :hora_retorno_almuerzo, hora_salida = :hora_salida, '
            . 'horarios_por_dia = :horarios_por_dia WHERE id = :id'
        );

        return $statement->execute([
            ':nombre' => $this->nombre,
            ':fecha_inicio' => $this->fechaInicio?->format('Y-m-d'),
            ':fecha_fin' => $this->fechaFin?->format('Y-m-d'),
            ':hora_entrada' => $this->horaEntrada,
            ':hora_salida_almuerzo' => $this->horaSalidaAlmuerzo,
            ':hora_retorno_almuerzo' => $this->horaRetornoAlmuerzo,
            ':hora_salida' => $this->horaSalida,
            ':horarios_por_dia' => json_encode($this->horariosPorDia),
            ':id' => $this->id
        ]);
    }

    public static function all(Database $db): array
    {
        $statement = $db->pdo()->query(
            'SELECT id, nombre, fecha_inicio, fecha_fin, hora_entrada, hora_salida_almuerzo, hora_retorno_almuerzo, hora_salida, horarios_por_dia '
            . 'FROM turnos ORDER BY nombre ASC'
        );
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => self::fromRow($row), $rows);
    }

    public static function paginate(Database $db, int $limit, int $offset): array
    {
        $statement = $db->pdo()->prepare(
            'SELECT id, nombre, fecha_inicio, fecha_fin, hora_entrada, hora_salida_almuerzo, hora_retorno_almuerzo, hora_salida, horarios_por_dia '
            . 'FROM turnos ORDER BY nombre ASC LIMIT :limit OFFSET :offset'
        );
        $statement->bindValue(':limit', $limit, PDO::PARAM_INT);
        $statement->bindValue(':offset', $offset, PDO::PARAM_INT);
        $statement->execute();
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => self::fromRow($row), $rows);
    }

    public static function countAll(Database $db): int
    {
        $statement = $db->pdo()->query('SELECT COUNT(*) FROM turnos');
        return (int) $statement->fetchColumn();
    }

    public static function find(Database $db, int $id): ?self
    {
        $statement = $db->pdo()->prepare(
            'SELECT id, nombre, fecha_inicio, fecha_fin, hora_entrada, hora_salida_almuerzo, hora_retorno_almuerzo, hora_salida, horarios_por_dia '
            . 'FROM turnos WHERE id = :id'
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
        $statement = $db->pdo()->prepare('DELETE FROM turnos WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    private static function fromRow(array $row): self
    {
        $horariosJson = isset($row['horarios_por_dia']) ? (string) $row['horarios_por_dia'] : '';
        $horarios = json_decode($horariosJson, true);
        if (!is_array($horarios)) {
            $horarios = [];
        }

        return new self(
            nombre: $row['nombre'] ?? '',
            fechaInicio: isset($row['fecha_inicio']) && $row['fecha_inicio'] ? new DateTime($row['fecha_inicio']) : null,
            fechaFin: isset($row['fecha_fin']) && $row['fecha_fin'] ? new DateTime($row['fecha_fin']) : null,
            horaEntrada: $row['hora_entrada'] ?? '',
            horaSalidaAlmuerzo: $row['hora_salida_almuerzo'] ?? '',
            horaRetornoAlmuerzo: $row['hora_retorno_almuerzo'] ?? '',
            horaSalida: $row['hora_salida'] ?? '',
            horariosPorDia: $horarios,
            id: isset($row['id']) ? (int) $row['id'] : null
        );
    }

    public static function diasSemana(): array
    {
        return self::DIAS_SEMANA;
    }

    public function obtenerHorarioParaFecha(DateTime $fecha): array
    {
        $indice = (int) $fecha->format('w');
        $dia = self::DIAS_SEMANA[$indice] ?? 'lunes';
        $horario = $this->horariosPorDia[$dia] ?? [
            'activo' => true,
            'hora_entrada' => $this->horaEntrada,
            'hora_salida_almuerzo' => $this->horaSalidaAlmuerzo,
            'hora_retorno_almuerzo' => $this->horaRetornoAlmuerzo,
            'hora_salida' => $this->horaSalida
        ];

        if (!(bool) ($horario['activo'] ?? true)) {
            return self::horarioCero(false);
        }

        return $horario;
    }

    public function obtenerMinutosJornadaPromedio(): int
    {
        $total = 0;
        $cantidad = 0;
        foreach ($this->horariosPorDia as $horario) {
            if (!(bool) ($horario['activo'] ?? true)) {
                continue;
            }

            $minutos = self::calcularMinutosDesdeHorario($horario);
            if ($minutos > 0) {
                $total += $minutos;
                $cantidad++;
            }
        }

        if ($cantidad === 0) {
            return self::calcularMinutosDesdeHorario([
                'hora_entrada' => $this->horaEntrada,
                'hora_salida_almuerzo' => $this->horaSalidaAlmuerzo,
                'hora_retorno_almuerzo' => $this->horaRetornoAlmuerzo,
                'hora_salida' => $this->horaSalida
            ]);
        }

        return (int) round($total / $cantidad);
    }

    public function resumenHorario(): string
    {
        $diasActivos = array_filter(
            $this->horariosPorDia,
            static fn(array $horario): bool => (bool) ($horario['activo'] ?? true)
        );
        if (empty($diasActivos)) {
            return 'Sin días laborables';
        }

        $lunes = $this->horariosPorDia['lunes'] ?? null;
        $sabado = $this->horariosPorDia['sabado'] ?? null;
        if (!$lunes || !$sabado || !(bool) ($lunes['activo'] ?? true) || !(bool) ($sabado['activo'] ?? true)) {
            return $this->horaEntrada . ' - ' . $this->horaSalida;
        }

        return 'L-V ' . $lunes['hora_entrada'] . '-' . $lunes['hora_salida'] . ' / S ' . $sabado['hora_entrada'] . '-' . $sabado['hora_salida'];
    }

    private static function normalizarHorariosPorDia(array $horariosPorDia, array $default): array
    {
        $normalizado = [];
        foreach (self::DIAS_SEMANA as $dia) {
            $origen = is_array($horariosPorDia[$dia] ?? null) ? $horariosPorDia[$dia] : [];
            $activo = isset($origen['activo']) ? (bool) $origen['activo'] : true;
            $horarioDia = [
                'activo' => $activo,
                'hora_entrada' => self::normalizarHora($origen['hora_entrada'] ?? $default['hora_entrada'] ?? ''),
                'hora_salida_almuerzo' => self::normalizarHora($origen['hora_salida_almuerzo'] ?? $default['hora_salida_almuerzo'] ?? ''),
                'hora_retorno_almuerzo' => self::normalizarHora($origen['hora_retorno_almuerzo'] ?? $default['hora_retorno_almuerzo'] ?? ''),
                'hora_salida' => self::normalizarHora($origen['hora_salida'] ?? $default['hora_salida'] ?? '')
            ];

            if (!$activo) {
                $horarioDia = self::horarioCero(false);
            }

            $normalizado[$dia] = $horarioDia;
        }

        return $normalizado;
    }

    private static function calcularMinutosDesdeHorario(array $horario): int
    {
        $fechaBase = '2000-01-01 ';
        $entrada = new DateTime($fechaBase . ($horario['hora_entrada'] ?? '00:00'));
        $salida = new DateTime($fechaBase . ($horario['hora_salida'] ?? '00:00'));
        $salidaAlmuerzo = new DateTime($fechaBase . ($horario['hora_salida_almuerzo'] ?? '00:00'));
        $retornoAlmuerzo = new DateTime($fechaBase . ($horario['hora_retorno_almuerzo'] ?? '00:00'));

        $minutosTotales = (int) round(($salida->getTimestamp() - $entrada->getTimestamp()) / 60);
        $minutosAlmuerzo = (int) round(($retornoAlmuerzo->getTimestamp() - $salidaAlmuerzo->getTimestamp()) / 60);

        return max(0, $minutosTotales - $minutosAlmuerzo);
    }

    private static function normalizarHora(mixed $valor): string
    {
        $hora = trim((string) $valor);
        if ($hora === '') {
            return '00:00';
        }

        return self::esHoraValida($hora) ? $hora : '00:00';
    }

    private static function esHoraValida(mixed $hora): bool
    {
        if (!is_string($hora)) {
            return false;
        }

        return (bool) preg_match('/^([01]\\d|2[0-3]):([0-5]\\d)$/', trim($hora));
    }

    private static function horarioCero(bool $activo): array
    {
        return [
            'activo' => $activo,
            'hora_entrada' => '00:00',
            'hora_salida_almuerzo' => '00:00',
            'hora_retorno_almuerzo' => '00:00',
            'hora_salida' => '00:00'
        ];
    }
}