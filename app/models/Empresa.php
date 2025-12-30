<?php

namespace App\Models;

class Empresa
{
    public function __construct(
        public string $razonSocial,
        public string $ruc,
        public string $correo,
        public string $telefono
    ) {
    }

    public function validate(): array
    {
        $errores = [];
        if (empty($this->razonSocial)) {
            $errores['razon_social'] = 'La razón social es obligatoria';
        }
        if (empty($this->ruc)) {
            $errores['ruc'] = 'El RUC es obligatorio';
        }
        if (!filter_var($this->correo, FILTER_VALIDATE_EMAIL)) {
            $errores['correo'] = 'Correo electrónico inválido';
        }
        return $errores;
    }
}