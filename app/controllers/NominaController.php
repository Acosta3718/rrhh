<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\CalculadoraNomina;
use App\Models\ParametrosNomina;
use DateTime;

class NominaController extends Controller
{
    public function __construct(private Database $db)
    {
    }

    public function overview(): void
    {
        $parametros = new ParametrosNomina();
        $calculadora = new CalculadoraNomina();

        $salarioEjemplo = 5000000; // guaranies
        $mesesTrabajados = 12;
        $fechaIngreso = new DateTime('2020-01-01');
        $hoy = new DateTime();
        $aniosAntiguedad = $fechaIngreso->diff($hoy)->y;

        $salarioNeto = $calculadora->calculateNetSalary(
            baseSalary: $salarioEjemplo,
            bonus: 250000,
            overtime: 150000,
            ipsWorkerRate: $parametros->ipsWorkerRate
        );

        $aguinaldo = $calculadora->calculateAguinaldo($salarioEjemplo, $mesesTrabajados);
        $diasVacaciones = $parametros->vacationDaysByTenure($aniosAntiguedad);

        $this->view('nomina/overview', [
            'salarioNeto' => $salarioNeto,
            'aguinaldo' => $aguinaldo,
            'diasVacaciones' => $diasVacaciones,
            'tasasIps' => $parametros->ipsRates(),
            'aniosAntiguedad' => $aniosAntiguedad
        ]);
    }
}