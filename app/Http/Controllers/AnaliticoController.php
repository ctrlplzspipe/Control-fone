<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use DateTime;
use ZipArchive;
use Exception;

class AnaliticoController extends Controller
{
    /**
     * Muestra la vista principal de Carga de Analítico.
     */
    public function index()
    {
        $date = "SIN INFORMACION";

        $actualizacion = DB::table('actualizaciones')
            ->where('tipo', 'ANALITICO')
            ->first();

        if ($actualizacion && !empty($actualizacion->tabla)) {
            $tabla = $actualizacion->tabla;
            if (strlen($tabla) >= 15) {
                $anio = substr($tabla, 9, 4);
                $qna = substr($tabla, 13, 2);
                $date = $anio . "/" . $qna;
            }
        }

        return view('cargas.analitico', compact('date'));
    }

    /**
     * Procesa la carga masiva del archivo ZIP de Analítico SEIEM.
     */
    public function store(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');
        ini_set('auto_detect_line_endings', true);

        $request->validate([
            'quincena' => ['required', 'regex:/^20[0-9]{2}(0[1-9]|1[0-9]|2[0-4])$/'],
            'archivo'  => ['required', 'file', 'mimes:zip', 'max:512000'],
        ]);

        $qna = $request->input('quincena');
        $tabla = "analitico" . $qna;
        $lbl_error = '';
        $lineas_error = '';
        $fecha_actualizacion = date('Y-m-d');

        // Directorios dentro de storage/app/temp
        $tempPath = storage_path('app/temp');
        if (!file_exists($tempPath)) {
            mkdir($tempPath, 0777, true);
        }

        $fileZip = $request->file('archivo');
        $zipPath = $fileZip->storeAs('temp', $fileZip->getClientOriginalName());
        $fullZipPath = storage_path('app/' . $zipPath);

        $zip = new ZipArchive;
        $target_r = '';
        $target_w = '';
        $errores = '';

        if ($zip->open($fullZipPath) === true) {
            $extractedFileName = $zip->getNameIndex(0);
            $zip->extractTo($tempPath);
            $zip->close();

            $target_r = $tempPath . '/' . $extractedFileName;
            $target_w = $tempPath . '/w_' . $extractedFileName;
            $errores  = $tempPath . '/err_analitico_' . $qna . '.txt';

            unlink($fullZipPath); // Borramos el ZIP subido
        } else {
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al descomprimir el archivo ZIP.',
                'lineas_error' => ''
            ], 422);
        }

        if (!file_exists($target_r)) {
            return response()->json([
                'success' => false,
                'mensaje' => "El archivo extraído ($extractedFileName) no existe.",
                'lineas_error' => ''
            ], 422);
        }

        // PROCESO DE LECTURA Y FORMATO
        try {
            // 1. Preparación de Tablas
            DB::statement("DROP TABLE IF EXISTS {$tabla}");
            DB::statement("CREATE TABLE {$tabla} LIKE analitico_struct");

            // 2. Apertura de Archivos
            $handle  = fopen($target_r, "r");
            $file    = fopen($target_w, "w");
            $fileErr = fopen($errores, "w");

            $i = 0;
            $errCount = 0;

            while (!feof($handle)) {
                $buffer = fgets($handle, 4096);
                if ($buffer === false || trim($buffer) === '') {
                    continue;
                }

                $i++;
                $dat = strtoupper($buffer);

                // Validaciones de estructura según lógica original
                $cpza = substr($dat, 121, 23);
                if (substr($cpza, 15, 1) !== '.') {
                    fwrite($fileErr, "lin: " . $i . " " . $dat);
                    $lineas_error .= $i . ', ';
                    $errCount++;
                    continue;
                }

                $ct = substr($dat, 181, 10);
                if (substr($ct, 0, 2) !== '15' && substr($ct, 0, 5) !== '00   ' && substr($ct, 0, 5) !== '000  ') {
                    fwrite($fileErr, "lin: " . $i . " " . $dat);
                    $lineas_error .= $i . ', ';
                    $errCount++;
                    continue;
                }

                // Construcción de la línea CSV
                $desde = substr($dat, 144, 6);
                $hasta = substr($dat, 150, 6);
                $fecha_ini = $this->qnaToFechaIni($desde);
                $fecha_fin = $this->qnaToFechaFin($hasta);

                $lineaData = [
                    trim(substr($dat, 0, 13)),                                // RFC
                    trim(str_replace(",", " ", substr($dat, 13, 30))),       // AP_PAT
                    trim(str_replace(",", " ", substr($dat, 43, 30))),       // AP_MAT
                    trim(str_replace(",", " ", substr($dat, 73, 30))),       // NOMBRE
                    trim(str_replace(",", " ", substr($dat, 103, 18))),      // CURP
                    $cpza,                                                    // CVEPRE / CPZA
                    $desde,                                                   // DESDE
                    $hasta,                                                   // HASTA
                    trim(substr($dat, 156, 2)),                               // ST
                    trim(substr($dat, 158, 2)),                               // MOT
                    trim(substr($dat, 160, 6)),                               // ING_SEP
                    trim(substr($dat, 166, 6)),                               // ING_SUB
                    trim(substr($dat, 172, 6)),                               // FRI
                    trim(substr($dat, 178, 1)),                               // NS
                    trim(substr($dat, 179, 2)),                               // NP
                    trim(substr($dat, 181, 10)),                              // CT
                    trim(substr($dat, 191, 3)),                               // UD
                    trim(substr($dat, 194, 3)),                               // MUN
                    trim(substr($dat, 197, 2)),                               // NIVEL_MAX_EST
                    trim(substr($dat, 199, 6)),                               // NO_SS
                    trim(str_replace(",", " ", substr($dat, 205, 59))),      // DIRECCION
                    trim(str_replace(",", " ", substr($dat, 264, 59))),      // COLONIA
                    trim(str_replace(",", " ", substr($dat, 323, 30))),      // LOCALIDAD
                    trim(str_replace(",", " ", substr($dat, 353, 13))),      // BASURA / EXTRA
                    trim(substr($dat, 367, 2)),                               // SEP_1
                    trim(substr($dat, 370, 2)),                               // SEP_2
                    trim(substr($dat, 373, 2)),                               // SEP_3
                    $fecha_ini->format("Y-m-d"),                              // FECHA_INI
                    $fecha_fin->format("Y-m-d"),                              // FECHA_FIN
                    substr($cpza, 0, 4),                                      // CODPAGOUNIDAD
                    substr($cpza, 4, 2),                                      // SUBUNIDAD
                    trim(substr($cpza, 6, 7)),                                // CATEGORIA
                    substr($cpza, 13, 4),                                     // HORAS
                    substr($cpza, 17, 6),                                     // CONSPLAZA
                    substr($cpza, 2, 21),                                     // CVEPRE21
                    "",                                                       // CPZA
                ];

                fwrite($file, implode(",", $lineaData) . PHP_EOL);
            }

            fclose($handle);
            fclose($file);
            fclose($fileErr);

            // 3. Carga Masiva a MySQL via LOAD DATA LOCAL INFILE (o Inserts por Lotes)
            $rutaFormatted = str_replace('\\', '/', $target_w);

            $sql_inserta = "LOAD DATA LOCAL INFILE '" . $rutaFormatted . "' INTO TABLE {$tabla} "
                . "CHARACTER SET latin1 "
                . "FIELDS TERMINATED BY ',' LINES TERMINATED BY '\n' "
                . "(RFC, AP_PAT, AP_MAT, NOMBRE, CURP, CVEPRE, DESDE, HASTA, ST, MOT, "
                . "ING_SEP, ING_SUB, FRI, NS, NP, CT, UD, MUN, NIVEL_MAX_EST, NO_SS, "
                . "DIRECCION, COLONIA, LOCALIDAD, BASURA, SEP_1, SEP_2, SEP_3, FECHA_INI, FECHA_FIN, CODPAGOUNIDAD, "
                . "SUBUNIDAD, CATEGORIA, HORAS, CONSPLAZA, CVEPRE21, CPZA)";

            try {
                DB::connection()->getPdo()->exec($sql_inserta);
            } catch (Exception $e) {
                // Fallback en caso de que LOAD DATA no esté permitido en la conf de PDO/Server:
                $this->insertarPorLotes($target_w, $tabla);
            }

            // 4. Actualizar registro en la tabla de actualizaciones
            DB::table('actualizaciones')
                ->where('tipo', 'ANALITICO')
                ->update([
                    'tabla' => $tabla,
                    'fecha' => $fecha_actualizacion
                ]);

            // Limpieza de archivos temporales
            @unlink($target_r);
            @unlink($target_w);

            $urlErrorDownload = null;
            if ($errCount > 0) {
                $lineas_error = "Lineas con error detectadas: " . $lineas_error;
                $urlErrorDownload = route('analitico.descargar-errores', ['qna' => $qna]);
            } else {
                @unlink($errores);
            }

            return response()->json([
                'success' => true,
                'mensaje' => "Proceso de Carga completado para la quincena {$qna}.",
                'lineas_error' => $lineas_error,
                'url_error' => $urlErrorDownload
            ]);

        } catch (Exception $ex) {
            @unlink($target_r);
            @unlink($target_w);

            return response()->json([
                'success' => false,
                'mensaje' => 'Error durante el procesamiento: ' . $ex->getMessage(),
                'lineas_error' => ''
            ], 500);
        }
    }

    /**
     * Descarga del reporte de errores si existieron líneas con falla.
     */
    public function descargarErrores($qna)
    {
        $file = storage_path('app/temp/err_analitico_' . $qna . '.txt');
        if (file_exists($file)) {
            return response()->download($file)->deleteFileAfterSend(true);
        }
        return redirect()->back()->with('error', 'El archivo de errores ya no está disponible.');
    }

    /**
     * Helper: Convierte Quincena a Fecha Inicio
     */
    private function qnaToFechaIni($qna_ini)
    {
        $fechdia = ["00", "01", "16", "01", "16", "01", "16", "01", "16", "01", "16", "01", "16", "01", "16", "01", "16", "01", "16", "01", "16", "01", "16", "01", "16"];
        $fechmes = ["00", "01", "01", "02", "02", "03", "03", "04", "04", "05", "05", "06", "06", "07", "07", "08", "08", "09", "09", "10", "10", "11", "11", "12", "12"];

        $qna = (int) substr($qna_ini, 4, 2);
        if ($qna > 24) { $qna = 24; }

        $anio = substr($qna_ini, 0, 4);
        return new DateTime("{$fechmes[$qna]}/{$fechdia[$qna]}/{$anio}");
    }

    /**
     * Helper: Convierte Quincena a Fecha Fin
     */
    private function qnaToFechaFin($qna_ini)
    {
        if ($qna_ini == '999999') {
            return new DateTime("12/31/2099");
        }

        $fechdia = ["00", "15", "31", "15", "28", "15", "31", "15", "30", "15", "31", "15", "30", "15", "31", "15", "31", "15", "30", "15", "31", "15", "30", "15", "31"];
        $fechmes = ["00", "01", "01", "02", "02", "03", "03", "04", "04", "05", "05", "06", "06", "07", "07", "08", "08", "09", "09", "10", "10", "11", "11", "12", "12"];

        $qna = (int) substr($qna_ini, 4, 2);
        if ($qna > 24) { $qna = 24; }

        $anio = (int) substr($qna_ini, 0, 4);
        if (($anio % 400 == 0) || ($anio % 4 == 0 && $anio % 100 != 0)) {
            $fechdia[4] = 29; // Año bisiesto
        }

        return new DateTime("{$fechmes[$qna]}/{$fechdia[$qna]}/{$anio}");
    }

    /**
     * Fallback de inserción por lotes en caso de restricción con LOAD DATA
     */
    private function insertarPorLotes($fileCsvPath, $tabla)
    {
        $handle = fopen($fileCsvPath, 'r');
        $batch = [];
        $columns = [
            'RFC', 'AP_PAT', 'AP_MAT', 'NOMBRE', 'CURP', 'CVEPRE', 'DESDE', 'HASTA', 'ST', 'MOT',
            'ING_SEP', 'ING_SUB', 'FRI', 'NS', 'NP', 'CT', 'UD', 'MUN', 'NIVEL_MAX_EST', 'NO_SS',
            'DIRECCION', 'COLONIA', 'LOCALIDAD', 'BASURA', 'SEP_1', 'SEP_2', 'SEP_3', 'FECHA_INI', 'FECHA_FIN',
            'CODPAGOUNIDAD', 'SUBUNIDAD', 'CATEGORIA', 'HORAS', 'CONSPLAZA', 'CVEPRE21', 'CPZA'
        ];

        DB::beginTransaction();

        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) >= count($columns)) {
                $batch[] = array_combine($columns, array_slice($row, 0, count($columns)));
            }

            if (count($batch) >= 1000) {
                DB::table($tabla)->insert($batch);
                $batch = [];
            }
        }

        if (count($batch) > 0) {
            DB::table($tabla)->insert($batch);
        }

        fclose($handle);
        DB::commit();
    }
}