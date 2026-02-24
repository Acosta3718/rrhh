<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Auth;
use App\Models\Feriado;
use App\Models\Funcionario;
use App\Models\MarcacionDiaria;
use App\Models\MarcacionReloj;
use App\Models\Turno;
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
        $fechaInicio = trim((string) ($_POST['fecha_inicio'] ?? ''));
        $fechaFin = trim((string) ($_POST['fecha_fin'] ?? ''));
        $inicioFiltro = null;
        $finFiltro = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (($fechaInicio && !$fechaFin) || (!$fechaInicio && $fechaFin)) {
                $errores['fecha'] = 'Ingrese la fecha inicial y la fecha final para filtrar la importación.';
            } elseif ($fechaInicio && $fechaFin) {
                try {
                    $inicioFiltro = new DateTime($fechaInicio . ' 00:00:00');
                    $finFiltro = new DateTime($fechaFin . ' 23:59:59');
                    if ($inicioFiltro > $finFiltro) {
                        $errores['fecha'] = 'La fecha inicial no puede ser mayor a la fecha final.';
                    }
                } catch (\Exception $e) {
                    $errores['fecha'] = 'Las fechas ingresadas no son válidas.';
                }
            }

            if (empty($_FILES['archivo_access']['tmp_name'])) {
                $errores['archivo_access'] = 'Seleccione un archivo de Access.';
            }

            if (empty($errores)) {
                $archivo = $_FILES['archivo_access'];
                $nombre = $archivo['name'] ?? 'reloj.access';
                $rutaDestino = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . uniqid('reloj_', true) . '_' . $nombre;

                if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                    $errores['archivo_access'] = 'No se pudo cargar el archivo seleccionado.';
                } else {
                    try {
                        $resultado = $this->importarDesdeAccess($rutaDestino, $inicioFiltro, $finFiltro);
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
            'resultado' => $resultado,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin
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
        $totalMinutosPeriodo = 0;
        $turnoAsignado = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Auth::requirePermission($this->db, 'marcaciones.editar', $this->baseUrl());
            $resultadoActualizacion = $this->actualizarHoras();
            if ($resultadoActualizacion['ok']) {
                $_SESSION['flash'] = 'Marcaciones actualizadas correctamente.';
                $this->redirectWithParams('marcaciones/horas', [
                    'funcionario_id' => $resultadoActualizacion['funcionario_id'],
                    'fecha_inicio' => $resultadoActualizacion['fecha_inicio'],
                    'fecha_fin' => $resultadoActualizacion['fecha_fin']
                ]);
            }
            $errores = array_merge($errores, $resultadoActualizacion['errores']);
            $funcionarioId = $resultadoActualizacion['funcionario_id'];
            $fechaInicio = $resultadoActualizacion['fecha_inicio'];
            $fechaFin = $resultadoActualizacion['fecha_fin'];
        }

        if ($funcionarioId) {
            $funcionarioSeleccionado = Funcionario::find($this->db, $funcionarioId);
            $nroIdReloj = trim((string) ($funcionarioSeleccionado?->nroIdReloj ?? ''));
            if ($nroIdReloj === '') {
                $errores['funcionario_id'] = 'Seleccione un funcionario con ID de reloj.';
            }

            if ($funcionarioSeleccionado?->turnoId) {
                $turnoAsignado = Turno::find($this->db, (int) $funcionarioSeleccionado->turnoId);
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
                        $fechaKey = $dia->format('Y-m-d');
                        $registro = $horasPorDia[$fechaKey] ?? null;
                        if ($registro) {
                            $totalMinutosPeriodo += $this->calcularMinutosTrabajadosDia($registro);
                        }
                    }
                }
            } catch (\Exception $e) {
                $errores['fecha'] = 'Las fechas ingresadas no son válidas.';
            }
        }

        $this->view('marcaciones/horas', [
            'errores' => $errores,
            'mensaje' => $this->consumeFlash(),
            'funcionarios' => $funcionarios,
            'funcionarioSeleccionado' => $funcionarioSeleccionado,
            'fechaInicio' => $fechaInicio,
            'fechaFin' => $fechaFin,
            'horasPorDia' => $horasPorDia,
            'diasPeriodo' => $diasPeriodo,
            'feriados' => $feriados,
            'totalMinutosPeriodo' => $totalMinutosPeriodo,
            'turnoAsignado' => $turnoAsignado
        ]);
    }

    private function importarDesdeAccess(string $rutaArchivo, ?DateTime $inicioFiltro = null, ?DateTime $finFiltro = null): array
    {
        if (!extension_loaded('odbc')) {
            throw new RuntimeException('La extensión ODBC no está disponible en el servidor.');
        }

        $dsn = sprintf('Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq=%s;', $rutaArchivo);
        $conexion = @odbc_connect($dsn, '', '');

        if (!$conexion) {
            throw new RuntimeException('No se pudo abrir el archivo de Access. Verifique el driver ODBC.');
        }

        $query = 'SELECT userid, checktime FROM CHECKINOUT';
        if ($inicioFiltro && $finFiltro) {
            $query .= sprintf(
                " WHERE checktime >= #%s# AND checktime <= #%s#",
                $inicioFiltro->format('Y-m-d H:i:s'),
                $finFiltro->format('Y-m-d H:i:s')
            );
        }
        $resultado = odbc_exec($conexion, $query);
        if (!$resultado) {
            odbc_close($conexion);
            throw new RuntimeException('No se pudo leer la tabla CHECKINOUT.');
        }

        $insertados = 0;
        $omitidos = 0;
        $fechasAActualizar = [];

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
                $fechaKey = $checkTime->format('Y-m-d');
                $fechasAActualizar[$nroIdReloj][$fechaKey] = [
                    'fecha' => $fechaKey,
                    'funcionario_id' => $funcionarioId ? (int) $funcionarioId : null
                ];
            } else {
                $omitidos++;
            }
        }

        foreach ($fechasAActualizar as $nroIdReloj => $fechas) {
            foreach ($fechas as $fechaData) {
                $fecha = new DateTime($fechaData['fecha'] . ' 00:00:00');
                MarcacionDiaria::sincronizarDesdeReloj(
                    $this->db,
                    $nroIdReloj,
                    $fecha,
                    $fechaData['funcionario_id']
                );
            }
        }

        odbc_close($conexion);

        return [
            'insertados' => $insertados,
            'omitidos' => $omitidos
        ];
    }

    private function calcularMinutosTrabajadosDia(array $registro): int
    {
        $minutosManana = $this->calcularMinutosSegmento(
            $registro['entrada'] ?? null,
            $registro['salida_almuerzo'] ?? null
        );

        $minutosTarde = $this->calcularMinutosSegmento(
            $registro['entrada_almuerzo'] ?? null,
            $registro['salida'] ?? null
        );

        $minutosJornadaCompleta = $this->calcularMinutosSegmento(
            $registro['entrada'] ?? null,
            $registro['salida'] ?? null
        );

        if ($minutosManana === 0 && $minutosTarde === 0) {
            return $minutosJornadaCompleta;
        }

        return $minutosManana + $minutosTarde;
    }

    private function calcularMinutosSegmento(?string $inicio, ?string $fin): int
    {
        if (!$inicio || !$fin) {
            return 0;
        }

        $inicioObj = $this->parseHora($inicio);
        $finObj = $this->parseHora($fin);
        if (!$inicioObj || !$finObj) {
            return 0;
        }

        $diferencia = (int) round(($finObj->getTimestamp() - $inicioObj->getTimestamp()) / 60);

        return max(0, $diferencia);
    }

    private function parseHora(?string $hora): ?DateTime
    {
        if (!$hora) {
            return null;
        }

        $hora = trim($hora);
        if ($hora === '') {
            return null;
        }

        $formatos = ['H:i:s', 'H:i'];
        foreach ($formatos as $formato) {
            $horaObj = DateTime::createFromFormat($formato, $hora);
            if ($horaObj instanceof DateTime) {
                return $horaObj;
            }
        }

        return null;
    }

    private function consumeFlash(): ?string
    {
        $mensaje = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        return $mensaje;
    }

    private function actualizarHoras(): array
    {
        $errores = [];
        $funcionarioId = (int) ($_POST['funcionario_id'] ?? 0);
        $fechaInicio = trim((string) ($_POST['fecha_inicio'] ?? ''));
        $fechaFin = trim((string) ($_POST['fecha_fin'] ?? ''));
        $dias = $_POST['dias'] ?? [];
        $entradas = $_POST['entrada'] ?? [];
        $salidasAlmuerzo = $_POST['salida_almuerzo'] ?? [];
        $entradasAlmuerzo = $_POST['entrada_almuerzo'] ?? [];
        $salidas = $_POST['salida'] ?? [];
        $aplicar = $_POST['aplicar'] ?? [];

        if (!$funcionarioId) {
            $errores['funcionario_id'] = 'Seleccione un funcionario válido.';
        }

        if (!$fechaInicio || !$fechaFin) {
            $errores['fecha'] = 'Ingrese la fecha inicial y la fecha final.';
        }

        $funcionario = $funcionarioId ? Funcionario::find($this->db, $funcionarioId) : null;
        if (!$funcionario) {
            $errores['funcionario_id'] = 'Seleccione un funcionario válido.';
        }

        $nroIdReloj = trim((string) ($funcionario?->nroIdReloj ?? ''));
        if ($funcionario && $nroIdReloj === '') {
            $errores['funcionario_id'] = 'El funcionario no tiene ID de reloj.';
        }

        if (empty($dias)) {
            $errores['general'] = 'No hay registros para actualizar.';
        }

        if (!empty($errores)) {
            return [
                'ok' => false,
                'errores' => $errores,
                'funcionario_id' => $funcionarioId,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ];
        }

        $usuario = Auth::user();
        $usuarioId = $usuario['id'] ?? null;
        $erroresHoras = [];

        foreach ($dias as $fecha) {
            $fecha = trim((string) $fecha);
            if ($fecha === '') {
                continue;
            }

            $entrada = trim((string) ($entradas[$fecha] ?? ''));
            $salidaAlmuerzo = trim((string) ($salidasAlmuerzo[$fecha] ?? ''));
            $entradaAlmuerzo = trim((string) ($entradasAlmuerzo[$fecha] ?? ''));
            $salida = trim((string) ($salidas[$fecha] ?? ''));

            if ($entrada !== '' && $this->normalizarHora($entrada) === null) {
                $erroresHoras[$fecha][] = 'Entrada inválida.';
            }
            if ($salidaAlmuerzo !== '' && $this->normalizarHora($salidaAlmuerzo) === null) {
                $erroresHoras[$fecha][] = 'Salida almuerzo inválida.';
            }
            if ($entradaAlmuerzo !== '' && $this->normalizarHora($entradaAlmuerzo) === null) {
                $erroresHoras[$fecha][] = 'Entrada almuerzo inválida.';
            }
            if ($salida !== '' && $this->normalizarHora($salida) === null) {
                $erroresHoras[$fecha][] = 'Salida inválida.';
            }
        }

        if (!empty($erroresHoras)) {
            $errores['horas'] = 'Revise los horarios inválidos y vuelva a intentar.';
            return [
                'ok' => false,
                'errores' => $errores,
                'funcionario_id' => $funcionarioId,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin
            ];
        }

        foreach ($dias as $fecha) {
            $fecha = trim((string) $fecha);
            if ($fecha === '') {
                continue;
            }

            $entrada = trim((string) ($entradas[$fecha] ?? ''));
            $salidaAlmuerzo = trim((string) ($salidasAlmuerzo[$fecha] ?? ''));
            $entradaAlmuerzo = trim((string) ($entradasAlmuerzo[$fecha] ?? ''));
            $salida = trim((string) ($salidas[$fecha] ?? ''));

            $entrada = $entrada !== '' ? $this->normalizarHora($entrada) : null;
            $salidaAlmuerzo = $salidaAlmuerzo !== '' ? $this->normalizarHora($salidaAlmuerzo) : null;
            $entradaAlmuerzo = $entradaAlmuerzo !== '' ? $this->normalizarHora($entradaAlmuerzo) : null;
            $salida = $salida !== '' ? $this->normalizarHora($salida) : null;

            MarcacionDiaria::upsert($this->db, [
                'nro_id_reloj' => $nroIdReloj,
                'funcionario_id' => $funcionarioId,
                'fecha' => $fecha,
                'entrada' => $entrada,
                'salida_almuerzo' => $salidaAlmuerzo,
                'entrada_almuerzo' => $entradaAlmuerzo,
                'salida' => $salida,
                'aplicar' => isset($aplicar[$fecha]),
                'creado_en' => date('Y-m-d H:i:s'),
                'actualizado_en' => date('Y-m-d H:i:s'),
                'actualizado_por' => $usuarioId
            ]);
        }

        return [
            'ok' => true,
            'errores' => [],
            'funcionario_id' => $funcionarioId,
            'fecha_inicio' => $fechaInicio,
            'fecha_fin' => $fechaFin
        ];
    }


    private function normalizarHora(string $hora): ?string
    {
        $hora = trim($hora);
        if ($hora === '') {
            return null;
        }

        $horaObj = DateTime::createFromFormat('H:i', $hora);
        if ($horaObj instanceof DateTime) {
            return $horaObj->format('H:i:s');
        }

        $horaObj = DateTime::createFromFormat('H:i:s', $hora);
        if ($horaObj instanceof DateTime) {
            return $horaObj->format('H:i:s');
        }

        return null;
    }

    private function redirectWithParams(string $route, array $params): void
    {
        $config = $GLOBALS['app_config'] ?? [];
        $baseUrl = rtrim($config['app']['base_url'] ?? '/public', '/');
        $query = http_build_query(array_merge(['route' => $route], $params));
        header('Location: ' . $baseUrl . '/index.php?' . $query);
        exit;
    }

    private function baseUrl(): string
    {
        $config = $GLOBALS['app_config'] ?? [];
        return rtrim($config['app']['base_url'] ?? '/public', '/');
    }
}