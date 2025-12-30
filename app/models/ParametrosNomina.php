<?php

namespace App\Models;

class ParametrosNomina
{
    public float $ipsWorkerRate = 0.09;
    public float $ipsEmployerRate = 0.165;

    private array $vacationByYears = [
        1 => 12,
        5 => 18,
        10 => 24
    ];

    public function vacationDaysByTenure(int $years): int
    {
        if ($years >= 10) {
            return $this->vacationByYears[10];
        }
        if ($years >= 5) {
            return $this->vacationByYears[5];
        }
        return $this->vacationByYears[1];
    }

    public function ipsRates(): array
    {
        return [
            'aporte_obrero' => $this->ipsWorkerRate,
            'aporte_patronal' => $this->ipsEmployerRate
        ];
    }
}