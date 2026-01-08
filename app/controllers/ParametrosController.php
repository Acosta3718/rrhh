<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Parametro;
use PDOException;

class ParametrosController extends Controller
{
    public function __construct(private Database $db)
    {
    }

    public function index(): void
    {
        $parametros = Parametro::getCurrent($this->db);
        $errores = [];
        $mensaje = $this->consumeFlash();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $parametros = $this->buildFromRequest($parametros?->id);
            $errores = $parametros->validate();

            if (empty($errores)) {
                try {
                    if ($parametros->id === null) {
                        $parametros->save($this->db);
                        $_SESSION['flash'] = 'Parámetros guardados correctamente.';
                    } else {
                        $parametros->update($this->db);
                        $_SESSION['flash'] = 'Parámetros actualizados correctamente.';
                    }
                    $this->redirect('parametros');
                } catch (PDOException $e) {
                    $errores['general'] = 'No se pudieron guardar los parámetros.';
                }
            }
        }

        $this->view('parametros/index', [
            'parametros' => $parametros,
            'errores' => $errores,
            'mensaje' => $mensaje
        ]);
    }

    private function buildFromRequest(?int $id = null): Parametro
    {
        return new Parametro(
            salarioMinimo: (float) ($_POST['salario_minimo'] ?? 0),
            mayoriaEdad: (int) ($_POST['mayoria_edad'] ?? 0),
            aporteObrero: (float) ($_POST['aporte_obrero'] ?? 0),
            aportePatronal: (float) ($_POST['aporte_patronal'] ?? 0),
            vacaciones10: (int) ($_POST['vacaciones10'] ?? 0),
            vacaciones5: (int) ($_POST['vacaciones5'] ?? 0),
            vacaciones1: (int) ($_POST['vacaciones1'] ?? 0),
            id: $id
        );
    }

    private function redirect(string $route): void
    {
        $config = $GLOBALS['app_config'] ?? [];
        $baseUrl = rtrim($config['app']['base_url'] ?? '/public', '/');
        header('Location: ' . $baseUrl . '/index.php?route=' . $route);
        exit;
    }

    private function consumeFlash(): ?string
    {
        $mensaje = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        return $mensaje;
    }
}