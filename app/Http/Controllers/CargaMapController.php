<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use Exception;
use PDO;

class CargaMapController extends Controller
{
    public function index()
    {
        $fecha = 'SIN INFORMACION';

        $registro = DB::table('actualizaciones')
            ->where('tipo', 'MAP')
            ->first();

        if ($registro && $registro->fecha) {
            $fecha = $registro->fecha;
        }

        return view('cargas.map', compact('fecha'));
    }

    public function store(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $request->validate([
            'lbl_FECHA' => 'required|date',
            'archivo' => 'required|file|mimes:zip|max:1024000',
        ]);

        // El input viejo era <input type="date">, formato YYYY-MM-DD.
        // Igual que en MAP viejo: asep_ + fecha sin guiones.
        $fechaInput = $request->input('lbl_FECHA');
        $tablaTarget = 'asep_' . str_replace('-', '', $fechaInput);

        $file = $request->file('archivo');
        $tempPath = storage_path('app/temp');

        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0777, true);
        }

        $zipName = time() . '_' . $file->getClientOriginalName();
        $file->move($tempPath, $zipName);
        $fullZipPath = $tempPath . '/' . $zipName;

        $zip = new ZipArchive;
        $extractedFile = null;

        if ($zip->open($fullZipPath) === TRUE) {
            // Buscar un CSV/TXT dentro del ZIP
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (preg_match('/\.(csv|txt)$/i', $filename)) {
                    $extractedFile = $filename;
                    break;
                }
            }

            // Respaldo: si no hay ninguno con esa extensión, se toma el
            // primer archivo del ZIP (comportamiento del sistema viejo,
            // que usaba siempre getNameIndex(0) sin filtrar)
            if (!$extractedFile && $zip->numFiles > 0) {
                $extractedFile = $zip->getNameIndex(0);
            }

            if ($extractedFile) {
                $zip->extractTo($tempPath, $extractedFile);
            }

            $zip->close();
            @unlink($fullZipPath);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo descomprimir el archivo ZIP.'
            ], 400);
        }

        if (!$extractedFile) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró ningún archivo dentro del ZIP.'
            ], 400);
        }

        $txtPath = $tempPath . '/' . $extractedFile;

        if (!file_exists($txtPath)) {
            return response()->json([
                'success' => false,
                'message' => 'El archivo extraído no fue localizado en el servidor.'
            ], 400);
        }

        try {
            $txtPathMysql = str_replace('\\', '/', $txtPath);

            // 1. Recrear la estructura de la tabla
            DB::statement("DROP TABLE IF EXISTS {$tablaTarget}");
            DB::statement("CREATE TABLE {$tablaTarget} LIKE asep_struct");

            // 2. Conexión PDO
            $pdo = DB::connection()->getPdo();
            $pdo->setAttribute(PDO::MYSQL_ATTR_LOCAL_INFILE, true);

            // Igual que en MDP: relajar el modo estricto de MySQL 8.x para
            // que un posible truncamiento no aborte toda la carga.
            $pdo->exec(
                "SET SESSION sql_mode = " .
                "REPLACE(" .
                "REPLACE(@@sql_mode, 'STRICT_TRANS_TABLES', ''), " .
                "'STRICT_ALL_TABLES', ''" .
                ")"
            );

            // 3. Carga masiva (mismas columnas, mismo encoding y mismo
            //    terminador de línea que el sistema viejo)
            $sqlLoad = "LOAD DATA INFILE " . $pdo->quote($txtPathMysql) . " 
                INTO TABLE {$tablaTarget}
                CHARACTER SET latin1
                FIELDS TERMINATED BY ',' 
                LINES TERMINATED BY '\\r\\n' 
                IGNORE 1 LINES
                (RAMO, UNI_RES, CATEGORIA, MODELO, NIVEL, CPZA, CCT, ZONA, CONTRATACION, TIPO_PLAZA, CANTIDAD, TIPO_TRANS, STATUS, FECHA, EFECTOS_DESDE)
                SET FEC_OP = CONCAT(SUBSTR(FECHA,7,4),'-',SUBSTR(FECHA,4,2),'-',SUBSTR(FECHA,1,2)),
                    CVEPRE = CPZA;";

            $pdo->exec($sqlLoad);

            // 4. Transformaciones de cvepre y actualización del registro de
            //    control, agrupadas en una transacción (igual que en MDP)
            DB::transaction(function () use ($tablaTarget, $fechaInput) {

                DB::statement("UPDATE {$tablaTarget} SET cvepre = CONCAT(LEFT(cpza,6), SUBSTR(cpza,8,11), '1', RIGHT(cpza,5)) WHERE LENGTH(cpza)=24");
                DB::statement("UPDATE {$tablaTarget} SET cvepre = CONCAT(LEFT(cpza,17), '1', RIGHT(cpza,5)) WHERE LENGTH(cpza)=23");

                // Igual que el sistema viejo: solo UPDATE, asume que ya
                // existe una fila con tipo='MAP' en 'actualizaciones'.
                DB::table('actualizaciones')
                    ->where('tipo', 'MAP')
                    ->update([
                        'tabla' => $tablaTarget,
                        'fecha' => $fechaInput,
                    ]);
            });

            @unlink($txtPath);

            return response()->json([
                'success' => true,
                'message' => 'Proceso completado exitosamente.'
            ]);

        } catch (Exception $e) {
            if (file_exists($txtPath)) {
                @unlink($txtPath);
            }

            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }
}