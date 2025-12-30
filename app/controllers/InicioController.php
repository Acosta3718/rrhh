<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class InicioController extends Controller
{
    public function __construct(private Database $db)
    {
    }

    public function index(): void
    {
        $conexionActiva = $this->db->testConnection();
        $this->view('inicio/index', [
            'conexionActiva' => $conexionActiva
        ]);
    }
}