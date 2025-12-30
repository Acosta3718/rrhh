<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Empresa;

class EmpresasController extends Controller
{
    public function __construct(private Database $db)
    {
    }

    public function create(): void
    {
        $empresa = null;
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $empresa = new Empresa(
                razonSocial: $_POST['razon_social'] ?? '',
                ruc: $_POST['ruc'] ?? '',
                correo: $_POST['correo'] ?? '',
                telefono: $_POST['telefono'] ?? ''
            );

            $errores = $empresa->validate();

            if (empty($errores)) {
                $_SESSION['empresas'][] = $empresa;
            }
        }

        $this->view('empresas/create', [
            'empresa' => $empresa,
            'errores' => $errores,
            'empresas' => $_SESSION['empresas'] ?? []
        ]);
    }
}