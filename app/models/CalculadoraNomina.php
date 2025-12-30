<?php

namespace App\Models;

class CalculadoraNomina
{
    public function calculateNetSalary(float $baseSalary, float $bonus, float $overtime, float $ipsWorkerRate): array
    {
        $gross = $baseSalary + $bonus + $overtime;
        $ips = $gross * $ipsWorkerRate;
        $net = $gross - $ips;

        return [
            'gross' => $gross,
            'ips' => $ips,
            'net' => $net
        ];
    }

    public function calculateAguinaldo(float $monthlySalary, int $monthsWorked): float
    {
        return ($monthlySalary * $monthsWorked) / 12;
    }

    public function calculateVacationPay(float $dailySalary, int $days): float
    {
        return $dailySalary * $days;
    }
}