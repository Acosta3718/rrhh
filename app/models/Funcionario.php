<?php

namespace App\Models;

use DateTime;

class Funcionario
{
    public function __construct(
        public string $nombre,
        public string $cargo,
        public float $salario,
        public DateTime $fechaIngreso,
        public int $empresaId
    ) {
    }

    public function validate(): array
    {
        $errores = [];
        if (empty($this->nombre)) {
            $errores['nombre'] = 'El nombre es obligatorio';
        }
        if ($this->salario <= 0) {
            $errores['salario'] = 'El salario debe ser mayor a cero';
        }
        if (!$this->fechaIngreso instanceof DateTime) {
            $errores['fecha_ingreso'] = 'Fecha de ingreso invalida';
        }
        if ($this->empresaId <= 0) {
            $errores['empresa_id'] = 'Seleccione una empresa válida';
        }
        return $errores;
    }
}