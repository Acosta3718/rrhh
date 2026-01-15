<?php

use App\Controllers\EmpresasController;
use App\Controllers\FuncionariosController;
use App\Controllers\InicioController;
use App\Controllers\NacionalidadesController;
use App\Controllers\NominaController;
use App\Controllers\ParametrosController;
use App\Controllers\AdelantosController;
use App\Controllers\SalariosController;
use App\Controllers\AguinaldosController;
use App\Controllers\TiposMovimientosController;
use App\Core\Database;

session_start();

$configFile = __DIR__ . '/../config/config.php';
if (!file_exists($configFile)) {
    $configFile = __DIR__ . '/../config/config.example.php';
}
$config = require $configFile;
$GLOBALS['app_config'] = $config;

spl_autoload_register(function (string $class) {
    $prefix = 'App\\';
    if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
        return;
    }

    $relative = substr($class, strlen($prefix));
    $segments = explode('/', str_replace('\\', '/', $relative));
    $segments[0] = strtolower($segments[0]);
    $path = __DIR__ . '/../app/' . implode('/', $segments) . '.php';
    if (file_exists($path)) {
        require $path;
    }
});

$db = new Database($config['db']);

$route = $_GET['route'] ?? 'inicio';

switch ($route) {
    case 'empresas':
        (new EmpresasController($db))->index();
        break;
    case 'empresas/create':
        (new EmpresasController($db))->create();
        break;
    case 'empresas/list':
        (new EmpresasController($db))->index();
        break;
    case 'empresas/edit':
        (new EmpresasController($db))->edit();
        break;
    case 'empresas/delete':
        (new EmpresasController($db))->delete();
        break;
    case 'funcionarios/create':
        (new FuncionariosController($db))->create();
        break;
    case 'funcionarios/list':
        (new FuncionariosController($db))->index();
        break;
    case 'funcionarios/edit':
        (new FuncionariosController($db))->edit();
        break;
    case 'funcionarios/delete':
        (new FuncionariosController($db))->delete();
        break;
    case 'adelantos/create':
        (new AdelantosController($db))->create();
        break;
    case 'adelantos/list':
        (new AdelantosController($db))->index();
        break;
    case 'adelantos/edit':
        (new AdelantosController($db))->edit();
        break;
    case 'adelantos/delete':
        (new AdelantosController($db))->delete();
        break;
    case 'adelantos/print':
        (new AdelantosController($db))->print();
        break;
    case 'adelantos/prints':
        (new AdelantosController($db))->prints();
        break;
    case 'adelantos/print-company':
        (new AdelantosController($db))->printCompany();
        break;
    case 'adelantos/print-individual':
        (new AdelantosController($db))->printIndividual();
        break;
    case 'nacionalidades/create':
        (new NacionalidadesController($db))->create();
        break;
    case 'nacionalidades/list':
        (new NacionalidadesController($db))->index();
        break;
    case 'nacionalidades/edit':
        (new NacionalidadesController($db))->edit();
        break;
    case 'nacionalidades/delete':
        (new NacionalidadesController($db))->delete();
        break;
    case 'nomina/overview':
        (new NominaController($db))->overview();
        break;
    case 'parametros':
        (new ParametrosController($db))->index();
        break;
    case 'salarios/list':
        (new SalariosController($db))->index();
        break;
    case 'salarios/prints':
        (new SalariosController($db))->prints();
        break;
    case 'salarios/print-company':
        (new SalariosController($db))->printCompany();
        break;
    case 'salarios/print-individual':
        (new SalariosController($db))->printIndividual();
        break;
    case 'salarios/create':
        (new SalariosController($db))->create();
        break;
    case 'salarios/edit':
        (new SalariosController($db))->edit();
        break;
    case 'salarios/delete':
        (new SalariosController($db))->delete();
        break;
    case 'aguinaldos/list':
        (new AguinaldosController($db))->index();
        break;
    case 'aguinaldos/create':
        (new AguinaldosController($db))->create();
        break;
    case 'aguinaldos/edit':
        (new AguinaldosController($db))->edit();
        break;
    case 'aguinaldos/delete':
        (new AguinaldosController($db))->delete();
        break;
    case 'tipos-movimientos/create':
        (new TiposMovimientosController($db))->create();
        break;
    case 'tipos-movimientos/list':
        (new TiposMovimientosController($db))->index();
        break;
    case 'tipos-movimientos/edit':
        (new TiposMovimientosController($db))->edit();
        break;
    case 'tipos-movimientos/delete':
        (new TiposMovimientosController($db))->delete();
        break;
    default:
        (new InicioController($db))->index();
}