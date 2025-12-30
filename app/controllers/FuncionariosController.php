<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\CalculadoraNomina;
use App\Models\Funcionario;
use DateTime;

class FuncionariosController extends Controller
{
    public function __construct(private Database $db)
    {
    }

    public function create(): void
    {
        $funcionario = null;
        $errores = [];
        $resumen = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $funcionario = new Funcionario(
                nombre: $_POST['nombre'] ?? '',
                cargo: $_POST['cargo'] ?? '',
                salario: (float) ($_POST['salario'] ?? 0),
                fechaIngreso: new DateTime($_POST['fecha_ingreso'] ?? 'now'),
                empresaId: (int) ($_POST['empresa_id'] ?? 0)
            );

            $errores = $funcionario->validate();
            if (empty($errores)) {
                $_SESSION['funcionarios'][] = $funcionario;
                $calculadora = new CalculadoraNomina();
                $resumen = $calculadora->calculateNetSalary(
                    baseSalary: $funcionario->salario,
                    bonus: (float) ($_POST['bonificacion'] ?? 0),
                    overtime: (float) ($_POST['horas_extra'] ?? 0),
                    ipsWorkerRate: (float) ($_POST['tasa_ips_obrero'] ?? 0.09)
                );
            }
        }

        $this->view('funcionarios/create', [
            'funcionario' => $funcionario,
            'errores' => $errores,
            'funcionarios' => $_SESSION['funcionarios'] ?? [],
            'resumen' => $resumen
        ]);
    }
}