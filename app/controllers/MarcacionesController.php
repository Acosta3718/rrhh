<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Models\Feriado;
use App\Models\Funcionario;
use App\Models\MarcacionReloj;
use DateTime;
use DatePeriod;
use DateInterval;
use RuntimeException;

class MarcacionesController extends Controller
{
    public function __construct(private Database $db)
    {
    }

    public function importar(): void
    {
        $errores = [];
        $mensaje = $this->consumeFlash();
        $resultado = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (empty($_FILES['archivo_access']['tmp_name'])) {
                $errores['archivo_access'] = 'Seleccione un archivo de Access.';
            } else {
                $archivo = $_FILES['archivo_access'];
                $nombre = $archivo['name'] ?? 'reloj.access';
                $rutaDestino = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . uniqid('reloj_', true) . '_' . $nombre;

                if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                    $errores['archivo_access'] = 'No se pudo cargar el archivo seleccionado.';
                } else {
                    try {
                        $resultado = $this->importarDesdeAccess($rutaDestino);
                        $mensaje = 'Importación finalizada.';
                    } catch (RuntimeException $e) {
                        $errores['archivo_access'] = $e->getMessage();
                    }
                }
            }
        }

        $this->view('marcaciones/import', [
            'errores' => $errores,
            'mensaje' => $mensaje,
            'resultado' => $resultado
        ]);
    }

    public function horas(): void
    {
        $errores = [];
        $funcionarios = Funcionario::conIdReloj($this->db);
        $funcionarioId = isset($_GET['funcionario_id']) ? (int) $_GET['funcionario_id'] : null;
        $fechaInicio = trim((string) ($_GET['fecha_inicio'] ?? ''));
        $fechaFin = trim((string) ($_GET['fecha_fin'] ?? ''));
        $funcionarioSeleccionado = null;
        $horasPorDia = [];
        $diasPeriodo = [];
        $feriados = [];

        if ($funcionarioId) {
            $funcionarioSeleccionado = Funcionario::find($this->db, $funcionarioId);
            $nroIdReloj = trim((string) ($funcionarioSeleccionado?->nroIdReloj ?? ''));
            if ($nroIdReloj === '') {
                $errores['funcionario_id'] = 'Seleccione un funcionario con ID de reloj.';
            }
        } elseif ($fechaInicio || $fechaFin) {
            $errores['funcionario_id'] = 'Seleccione un funcionario válido.';
        }

        if (($fechaInicio && !$fechaFin) || (!$fechaInicio && $fechaFin)) {
            $errores['fecha'] = 'Ingrese la fecha inicial y la fecha final.';
        }

        if (empty($errores) && $funcionarioSeleccionado && $fechaInicio && $fechaFin) {
            try {
                $inicio = new DateTime($fechaInicio . ' 00:00:00');
                $fin = new DateTime($fechaFin . ' 23:59:59');
                if ($inicio > $fin) {
                    $errores['fecha'] = 'La fecha inicial no puede ser mayor a la fecha final.';
                } else {
                    $horasPorDia = MarcacionReloj::obtenerHorasPorDia(
                        $this->db,
                        (string) $funcionarioSeleccionado->nroIdReloj,
                        $inicio,
                        $fin
                    );
                    $feriados = Feriado::listarPorRango($this->db, $inicio, $fin);
                    $periodo = new DatePeriod($inicio, new DateInterval('P1D'), (clone $fin)->modify('+1 day'));
                    foreach ($periodo as $dia) {
                        $diasPeriodo[] = $dia;
                    }
                }
            } catch (\Exception $e) {
                $errores['fecha'] = 'Las fechas ingresadas no son válidas.';
            }
        }

        $this->view('marcaciones/horas', [
            'errores' => $errores,
            'funcionarios' => $funcionarios,
            'funcionarioSeleccionado' => $funcionarioSeleccionado,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'horasPorDia' => $horasPorDia,
            'diasPeriodo' => $diasPeriodo,
            'feriados' => $feriados
        ]);
    }

    private function importarDesdeAccess(string $rutaArchivo): array
    {
        if (!extension_loaded('odbc')) {
            throw new RuntimeException('La extensión ODBC no está disponible en el servidor.');
        }

        $dsn = sprintf('Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=%s;', $rutaArchivo);
        $conexion = @odbc_connect($dsn, '', '');

        if (!$conexion) {
            throw new RuntimeException('No se pudo abrir el archivo de Access. Verifique el driver ODBC.');
        }

        $resultado = odbc_exec($conexion, 'SELECT userid, checktime FROM CHECKINOUT');
        if (!$resultado) {
            odbc_close($conexion);
            throw new RuntimeException('No se pudo leer la tabla CHECKINOUT.');
        }

        $insertados = 0;
        $omitidos = 0;

        $statementFuncionario = $this->db->pdo()->prepare(
            'SELECT id FROM funcionarios WHERE nro_id_reloj = :nro_id_reloj LIMIT 1'
        );

        while ($row = odbc_fetch_array($resultado)) {
            $nroIdReloj = trim((string) ($row['userid'] ?? ''));
            $checkTimeRaw = $row['checktime'] ?? null;

            if ($nroIdReloj === '' || !$checkTimeRaw) {
                $omitidos++;
                continue;
            }

            try {
                $checkTime = new DateTime((string) $checkTimeRaw);
            } catch (\Exception $e) {
                $omitidos++;
                continue;
            }

            $statementFuncionario->execute([':nro_id_reloj' => $nroIdReloj]);
            $funcionarioId = $statementFuncionario->fetchColumn();

            $insertado = MarcacionReloj::insertarSiNoExiste(
                $this->db,
                $nroIdReloj,
                $checkTime,
                $funcionarioId ? (int) $funcionarioId : null
            );

            if ($insertado) {
                $insertados++;
            } else {
                $omitidos++;
            }
        }

        odbc_close($conexion);

        return [
            'insertados' => $insertados,
            'omitidos' => $omitidos
        ];
    }

    private function consumeFlash(): ?string
    {
        $mensaje = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        return $mensaje;
    }
}