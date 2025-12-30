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
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="<?php echo $baseUrl; ?>/index.php">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo $baseUrl; ?>/index.php?route=empresas/create">Empresas</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo $baseUrl; ?>/index.php?route=funcionarios/create">Funcionarios</a></li>
                <li class="nav-item"><a class="nav-link" href="<?php echo $baseUrl; ?>/index.php?route=nomina/overview">Nómina</a></li>
            </ul>
        </div>
    </div>
</nav>
<div class="container">