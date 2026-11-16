<?php

namespace App\Http\Controllers;

use App\Models\Configuracion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class MantenimientoController extends Controller
{
    private function getBackupPath(): string
    {
        $path = storage_path('app/backups');
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
        return $path;
    }

    public function index()
    {
        $config = Configuracion::getInfo();
        $backupDir = $this->getBackupPath();

        // 1. Obtener lista de respaldos existentes
        $archivos = File::files($backupDir);
        $backups = [];

        foreach ($archivos as $file) {
            if ($file->getExtension() === 'sql') {
                $tamanoBytes = $file->getSize();
                $tamanoFormateado = $tamanoBytes >= 1048576 
                    ? number_format($tamanoBytes / 1048576, 2) . ' MB' 
                    : number_format($tamanoBytes / 1024, 1) . ' KB';

                $backups[] = [
                    'nombre' => $file->getFilename(),
                    'tamano' => $tamanoFormateado,
                    'bytes' => $tamanoBytes,
                    'fecha' => Carbon::createFromTimestamp($file->getMTime())->format('d/m/Y H:i:s'),
                    'hace_tiempo' => Carbon::createFromTimestamp($file->getMTime())->diffForHumans(),
                    'timestamp' => $file->getMTime(),
                ];
            }
        }

        // Ordenar del más reciente al más antiguo
        usort($backups, fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);

        // 2. Información del Estado de la Base de Datos
        $dbInfo = [
            'motor' => 'PostgreSQL',
            'base_datos' => config('database.connections.pgsql.database', 'cultura_fit'),
            'host' => config('database.connections.pgsql.host', '127.0.0.1'),
            'puerto' => config('database.connections.pgsql.port', '5432'),
            'usuario' => config('database.connections.pgsql.username', 'postgres'),
            'conectado' => false,
            'tamano' => 'N/D',
            'tablas' => 0,
            'version' => 'N/D',
        ];

        try {
            $isPgsql = config('database.default') === 'pgsql';
            if ($isPgsql) {
                $sizeQuery = DB::select("SELECT pg_size_pretty(pg_database_size(current_database())) as size");
                $dbInfo['tamano'] = $sizeQuery[0]->size ?? '0 MB';

                $tableQuery = DB::select("SELECT count(*) as count FROM information_schema.tables WHERE table_schema = 'public'");
                $dbInfo['tablas'] = $tableQuery[0]->count ?? 0;

                $verQuery = DB::select("SELECT version() as ver");
                $dbInfo['version'] = explode(' ', $verQuery[0]->ver ?? 'PostgreSQL')[1] ?? '16.x';
                $dbInfo['conectado'] = true;
            }
        } catch (\Exception $e) {
            $dbInfo['conectado'] = false;
        }

        return view('mantenimiento.index', compact('config', 'backups', 'dbInfo'));
    }

    public function crearBackup(Request $request)
    {
        $backupDir = $this->getBackupPath();
        $fileName = 'backup_cultura_fit_' . date('Y-m-d_H-i-s') . '.sql';
        $fullPath = $backupDir . DIRECTORY_SEPARATOR . $fileName;

        $dbHost = config('database.connections.pgsql.host', '127.0.0.1');
        $dbPort = config('database.connections.pgsql.port', '5432');
        $dbName = config('database.connections.pgsql.database', 'cultura_fit');
        $dbUser = config('database.connections.pgsql.username', 'postgres');
        $dbPass = config('database.connections.pgsql.password', '');

        $pgDumpExes = [
            'C:\\Program Files\\PostgreSQL\\16\\bin\\pg_dump.exe',
            'C:\\Program Files\\PostgreSQL\\15\\bin\\pg_dump.exe',
            'C:\\Program Files\\PostgreSQL\\14\\bin\\pg_dump.exe',
            'pg_dump',
        ];

        $dumpExitoso = false;

        // Intentar con pg_dump nativo si existe
        foreach ($pgDumpExes as $exe) {
            if ($exe === 'pg_dump' || file_exists($exe)) {
                $descriptors = [
                    0 => ['pipe', 'r'],
                    1 => ['file', $fullPath, 'w'],
                    2 => ['pipe', 'w'],
                ];

                // getenv() trae el entorno completo del sistema (PATH, SystemRoot...). Con solo $_ENV,
                // pg_dump no puede abrir la conexión en Windows y el respaldo caía siempre al modo alternativo.
                $env = array_merge(getenv(), [
                    'PGPASSWORD' => (string) $dbPass,
                ]);

                $cmd = "\"{$exe}\" -h " . escapeshellarg((string) $dbHost)
                    . ' -p ' . escapeshellarg((string) $dbPort)
                    . ' -U ' . escapeshellarg((string) $dbUser)
                    . ' -F p --clean --if-exists ' . escapeshellarg((string) $dbName);
                $process = @proc_open($cmd, $descriptors, $pipes, null, $env);

                if (is_resource($process)) {
                    fclose($pipes[0]);
                    $stderr = stream_get_contents($pipes[2]);
                    fclose($pipes[2]);
                    $returnVal = proc_close($process);

                    if ($returnVal === 0 && file_exists($fullPath) && filesize($fullPath) > 0) {
                        $dumpExitoso = true;
                        break;
                    }
                }
            }
        }

        // Si pg_dump no pudo ejecutarse, respaldo mediante motor PHP nativo
        if (!$dumpExitoso) {
            try {
                // Orden que respeta las llaves foráneas al restaurar (padres antes que hijos)
                $ordenBase = [
                    'sucursales',
                    'users',
                    'configuracion',
                    'categorias',
                    'productos',
                    'inventario_sucursales',
                    'socios',
                    'cajas',
                    'ventas',
                    'detalle_ventas',
                    'gastos',
                    'medidas',
                    'rutinas',
                ];

                // Se toman TODAS las tablas reales de la base, para que una tabla nueva nunca quede fuera
                $existentes = collect(DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = 'public' AND table_type = 'BASE TABLE'"))
                    ->pluck('table_name')
                    ->all();
                $omitir = ['migrations', 'failed_jobs', 'password_reset_tokens', 'personal_access_tokens'];

                $tablas = array_values(array_unique(array_merge(
                    array_values(array_intersect($ordenBase, $existentes)),
                    array_values(array_diff($existentes, $ordenBase, $omitir))
                )));

                $sqlContent = "-- =====================================================\n";
                $sqlContent .= "-- CULTURA FIT - Copia de Seguridad de Base de Datos\n";
                $sqlContent .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
                $sqlContent .= "-- Base de Datos: {$dbName}\n";
                $sqlContent .= "-- =====================================================\n\n";
                $sqlContent .= "SET client_min_messages = warning;\n\n";

                foreach ($tablas as $tabla) {
                    $filas = DB::table($tabla)->get();
                    if ($filas->count() > 0) {
                        $sqlContent .= "-- Volcado de datos para la tabla: {$tabla}\n";
                        foreach ($filas as $fila) {
                            $campos = array_keys((array)$fila);
                            $valores = array_map(function ($val) {
                                if (is_null($val)) return 'NULL';
                                if (is_numeric($val) && !is_string($val)) return $val;
                                return "'" . str_replace("'", "''", (string)$val) . "'";
                            }, array_values((array)$fila));

                            $sqlContent .= "INSERT INTO {$tabla} (" . implode(', ', $campos) . ") VALUES (" . implode(', ', $valores) . ") ON CONFLICT DO NOTHING;\n";
                        }
                        $sqlContent .= "\n";
                    }
                }

                // Reajustar los contadores de ID para que, tras restaurar, los nuevos registros no choquen
                $sqlContent .= "-- Reajuste de secuencias\n";
                foreach ($tablas as $tabla) {
                    $sqlContent .= "SELECT setval(pg_get_serial_sequence('{$tabla}', 'id'), COALESCE((SELECT MAX(id) FROM {$tabla}), 1));\n";
                }

                File::put($fullPath, $sqlContent);
                $dumpExitoso = true;

            } catch (\Exception $e) {
                return redirect()->route('mantenimiento.index')
                    ->with('error', 'No se pudo generar la copia de seguridad: ' . $e->getMessage());
            }
        }

        return redirect()->route('mantenimiento.index')
            ->with('success', "¡Copia de seguridad '{$fileName}' creada exitosamente!");
    }

    public function descargarBackup($archivo)
    {
        // Seguridad contra Path Traversal
        $archivoSeguro = basename($archivo);
        $fullPath = $this->getBackupPath() . DIRECTORY_SEPARATOR . $archivoSeguro;

        if (!File::exists($fullPath) || pathinfo($fullPath, PATHINFO_EXTENSION) !== 'sql') {
            return redirect()->route('mantenimiento.index')
                ->with('error', 'El archivo de copia de seguridad no existe o es inválido.');
        }

        return response()->download($fullPath, $archivoSeguro, [
            'Content-Type' => 'application/sql',
        ]);
    }

    public function eliminarBackup(Request $request)
    {
        $request->validate([
            'archivo' => ['required', 'string'],
        ]);

        $archivoSeguro = basename($request->archivo);
        $fullPath = $this->getBackupPath() . DIRECTORY_SEPARATOR . $archivoSeguro;

        if (File::exists($fullPath)) {
            File::delete($fullPath);
            return redirect()->route('mantenimiento.index')
                ->with('success', "Copia de seguridad '{$archivoSeguro}' eliminada con éxito.");
        }

        return redirect()->route('mantenimiento.index')
            ->with('error', 'No se encontró el archivo a eliminar.');
    }
}
