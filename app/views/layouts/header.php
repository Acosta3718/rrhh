<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gestión RRHH</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container-fluid">
        <a class="navbar-brand" href="<?php echo $baseUrl; ?>/index.php">RRHH</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?php echo $baseUrl; ?>/index.php">Inicio</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownEmpresas" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Empresas
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownEmpresas">
                        <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/index.php?route=empresas/create">Crear empresa</a></li>
                        <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/index.php?route=empresas/list">Listado de empresas</a></li>
                    </ul>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownFuncionarios" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Funcionarios
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdownFuncionarios">
                        <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/index.php?route=funcionarios/create">Crear funcionario</a></li>
                        <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/index.php?route=funcionarios/list">Listado de funcionarios</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="<?php echo $baseUrl; ?>/index.php?route=nacionalidades/list">Nacionalidades</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="<?php echo $baseUrl; ?>/index.php?route=nomina/overview">Nómina</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container">