<?php

namespace App\Models;

use App\Core\Database;
use DateTime;
use PDO;

class Funcionario
{
    public function __construct(
        public string $nombre,
        public string $cargo,
        public string $nroDocumento,
        public string $direccion,
        public string $celular,
        public float $salario,
        public ?DateTime $fechaIngreso,
        public int $empresaId,
        public ?DateTime $fechaNacimiento = null,
        public ?int $nacionalidadId = null,
        public ?string $nacionalidadNombre = null,
        public string $estadoCivil = 'soltero',
        public string $estado = 'activo',
        public float $adelanto = 0.0,
        public bool $tieneIps = false,
        public ?DateTime $fechaSalida = null,
        public ?string $empresaNombre = null,
        public ?int $id = null
    ) {
    }

    public function validate(): array
    {
        $errores = [];
        $estadoCivilPermitidos = ['casado', 'soltero', 'divorciado', 'separado'];

        if (empty($this->nombre)) {
            $errores['nombre'] = 'El nombre es obligatorio';
        }
        if (empty($this->nroDocumento)) {
            $errores['nro_documento'] = 'El número de documento es obligatorio';
        }
        if (empty($this->direccion)) {
            $errores['direccion'] = 'La dirección es obligatoria';
        }
        if (empty($this->celular)) {
            $errores['celular'] = 'El celular es obligatorio';
        }
        if ($this->salario <= 0) {
            $errores['salario'] = 'El salario debe ser mayor a cero';
        }
        if (!$this->fechaIngreso instanceof DateTime) {
            $errores['fecha_ingreso'] = 'La fecha de ingreso es obligatoria';
        }
        if ($this->empresaId <= 0) {
            $errores['empresa_id'] = 'Seleccione una empresa válida';
        }
        if ($this->fechaNacimiento instanceof DateTime) {
            $hoy = new DateTime('today');
            if ($this->fechaNacimiento > $hoy) {
                $errores['fecha_nacimiento'] = 'La fecha de nacimiento no puede ser futura';
            }
        } else {
            $errores['fecha_nacimiento'] = 'La fecha de nacimiento es obligatoria';
        }
        if ($this->nacionalidadId === null || $this->nacionalidadId <= 0) {
            $errores['nacionalidad_id'] = 'Seleccione una nacionalidad';
        }
        if (!in_array($this->estadoCivil, $estadoCivilPermitidos, true)) {
            $errores['estado_civil'] = 'Estado civil inválido';
        }
        if (!in_array($this->estado, ['activo', 'inactivo'], true)) {
            $errores['estado'] = 'El estado debe ser activo o inactivo';
        }
        if ($this->adelanto < 0) {
            $errores['adelanto'] = 'El adelanto no puede ser negativo';
        }

        if ($this->fechaSalida && !$this->fechaSalida instanceof DateTime) {
            $errores['fecha_salida'] = 'Fecha de salida inválida';
        }

        return $errores;
    }

    public function save(Database $db): bool
    {
        $statement = $db->pdo()->prepare(
            'INSERT INTO funcionarios (nombre, cargo, nro_documento, direccion, celular, salario, fecha_ingreso, empresa_id, fecha_nacimiento, nacionalidad_id, estado_civil, estado, adelanto, tiene_ips, fecha_salida) '
            . 'VALUES (:nombre, :cargo, :nro_documento, :direccion, :celular, :salario, :fecha_ingreso, :empresa_id, :fecha_nacimiento, :nacionalidad_id, :estado_civil, :estado, :adelanto, :tiene_ips, :fecha_salida)'
        );

        $resultado = $statement->execute([
            ':nombre' => $this->nombre,
            ':cargo' => $this->cargo,
            ':nro_documento' => $this->nroDocumento,
            ':direccion' => $this->direccion,
            ':celular' => $this->celular,
            ':salario' => $this->salario,
            ':fecha_ingreso' => $this->fechaIngreso?->format('Y-m-d'),
            ':empresa_id' => $this->empresaId,
            ':fecha_nacimiento' => $this->fechaNacimiento?->format('Y-m-d'),
            ':nacionalidad_id' => $this->nacionalidadId,
            ':estado_civil' => $this->estadoCivil,
            ':estado' => $this->estado,
            ':adelanto' => $this->adelanto,
            ':tiene_ips' => $this->tieneIps ? 1 : 0,
            ':fecha_salida' => $this->fechaSalida?->format('Y-m-d')
        ]);

        if ($resultado) {
            $this->id = (int) $db->pdo()->lastInsertId();
        }

        return $resultado;
    }

    public function update(Database $db): bool
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException('No se puede actualizar un funcionario sin identificador');
        }

        $statement = $db->pdo()->prepare(
            'UPDATE funcionarios SET nombre = :nombre, cargo = :cargo, nro_documento = :nro_documento, direccion = :direccion, celular = :celular, salario = :salario, fecha_ingreso = :fecha_ingreso, empresa_id = :empresa_id, fecha_nacimiento = :fecha_nacimiento, nacionalidad_id = :nacionalidad_id, estado_civil = :estado_civil, estado = :estado, adelanto = :adelanto, tiene_ips = :tiene_ips, fecha_salida = :fecha_salida WHERE id = :id'
        );

        return $statement->execute([
            ':nombre' => $this->nombre,
            ':cargo' => $this->cargo,
            ':nro_documento' => $this->nroDocumento,
            ':direccion' => $this->direccion,
            ':celular' => $this->celular,
            ':salario' => $this->salario,
            ':fecha_ingreso' => $this->fechaIngreso?->format('Y-m-d'),
            ':empresa_id' => $this->empresaId,
            ':fecha_nacimiento' => $this->fechaNacimiento?->format('Y-m-d'),
            ':nacionalidad_id' => $this->nacionalidadId,
            ':estado_civil' => $this->estadoCivil,
            ':estado' => $this->estado,
            ':adelanto' => $this->adelanto,
            ':tiene_ips' => $this->tieneIps ? 1 : 0,
            ':fecha_salida' => $this->fechaSalida?->format('Y-m-d'),
            ':id' => $this->id
        ]);
    }

    public static function all(Database $db): array
    {
        return self::search($db);
    }

    public static function find(Database $db, int $id): ?self
    {
        $statement = $db->pdo()->prepare(
            'SELECT f.*, n.nombre AS nacionalidad_nombre, e.razon_social AS empresa_nombre FROM funcionarios f '
            . 'LEFT JOIN nacionalidades n ON f.nacionalidad_id = n.id '
            . 'LEFT JOIN empresas e ON f.empresa_id = e.id WHERE f.id = :id'
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
        $statement = $db->pdo()->prepare('DELETE FROM funcionarios WHERE id = :id');
        return $statement->execute([':id' => $id]);
    }

    public static function activosPorEmpresa(Database $db, int $empresaId): array
    {
        return self::search($db, $empresaId, null, 'activo');
    }

    public static function search(Database $db, ?int $empresaId = null, ?string $nombre = null, ?string $estado = null): array
    {
        $sql = 'SELECT f.*, n.nombre AS nacionalidad_nombre, e.razon_social AS empresa_nombre FROM funcionarios f '
            . 'LEFT JOIN nacionalidades n ON f.nacionalidad_id = n.id '
            . 'LEFT JOIN empresas e ON f.empresa_id = e.id WHERE 1=1';

        $params = [];
        if ($empresaId) {
            $sql .= ' AND f.empresa_id = :empresa_id';
            $params[':empresa_id'] = $empresaId;
        }
        if ($nombre) {
            $sql .= ' AND f.nombre LIKE :nombre';
            $params[':nombre'] = '%' . $nombre . '%';
        }
        if ($estado) {
            $sql .= ' AND f.estado = :estado';
            $params[':estado'] = $estado;
        }

        $sql .= ' ORDER BY f.id DESC';

        $statement = $db->pdo()->prepare($sql);
        $statement->execute($params);
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

        return array_map(fn(array $row) => self::fromRow($row), $rows);
    }

    public static function existsByDocumento(Database $db, string $documento, ?int $excludeId = null): bool
    {
        $sql = 'SELECT 1 FROM funcionarios WHERE nro_documento = :nro_documento';
        $params = [':nro_documento' => $documento];

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
            nombre: $row['nombre'] ?? '',
            cargo: $row['cargo'] ?? '',
            nroDocumento: $row['nro_documento'] ?? '',
            direccion: $row['direccion'] ?? '',
            celular: $row['celular'] ?? '',
            salario: isset($row['salario']) ? (float) $row['salario'] : 0.0,
            fechaIngreso: new DateTime($row['fecha_ingreso'] ?? 'now'),
            empresaId: isset($row['empresa_id']) ? (int) $row['empresa_id'] : 0,
            fechaNacimiento: isset($row['fecha_nacimiento']) && $row['fecha_nacimiento'] ? new DateTime($row['fecha_nacimiento']) : null,
            nacionalidadId: isset($row['nacionalidad_id']) ? (int) $row['nacionalidad_id'] : null,
            nacionalidadNombre: $row['nacionalidad_nombre'] ?? null,
            estadoCivil: $row['estado_civil'] ?? 'soltero',
            estado: $row['estado'] ?? 'activo',
            adelanto: isset($row['adelanto']) ? (float) $row['adelanto'] : 0.0,
            tieneIps: isset($row['tiene_ips']) ? (bool) $row['tiene_ips'] : false,
            fechaSalida: isset($row['fecha_salida']) && $row['fecha_salida'] ? new DateTime($row['fecha_salida']) : null,
            empresaNombre: $row['empresa_nombre'] ?? null,
            id: isset($row['id']) ? (int) $row['id'] : null
        );
    }
}