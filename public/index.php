<?php

use App\Controllers\EmpresasController;
use App\Controllers\FuncionariosController;
use App\Controllers\InicioController;
use App\Controllers\NominaController;
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
    case 'nomina/overview':
        (new NominaController($db))->overview();
        break;
    default:
        (new InicioController($db))->index();
}