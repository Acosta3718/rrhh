<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;

class AguinaldosController extends Controller
{
    public function __construct(private Database $db)
    {
    }

    public function index(): void
    {
        $this->view('aguinaldos/index', []);
    }
}