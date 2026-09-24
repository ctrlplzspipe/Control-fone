<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use Exception;
use PDO;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\IReadFilter;




class CargaMdpController extends Controller
{
    public function index()
    {
        $fecha = 'SIN INFORMACION';

        $registro = DB::table('actualizaciones')
            ->where('tipo', 'MDP')
            ->first();

        if ($registro && $registro->fecha) {
            $fecha = $registro->fecha;
        }

        return view('cargas.mdp', compact('fecha'));
    }

    public function store(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $request->validate([
            'lbl_FECHA' => 'required|date',
            'archivo' => 'required|file|mimes:zip|max:1024000', // ZIP proveniente del sistema original
        ]);

        $fechaInput = $request->input('lbl_FECHA');
        $tablaTarget = 'movssep_' . str_replace('-', '', $fechaInput);

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
            // Localiza automáticamente el CSV, TXT o Excel interno sin importar subcarpetas
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (preg_match('/\.(csv|txt|xlsx|xls)$/i', $filename)) {
                    $extractedFile = $filename;
                    break;
                }
            }

            $zip->extractTo($tempPath);
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
                'message' => 'No se encontró ningún archivo válido dentro del ZIP.'
            ], 400);
        }

        $txtPath = $tempPath . '/' . $extractedFile;
        $extension = strtolower(pathinfo($txtPath, PATHINFO_EXTENSION));

        // Si el archivo extraído es un Excel (.xlsx o .xls), procesamos celdas y color
        if (in_array($extension, ['xlsx', 'xls'])) {
            $csvPath = $tempPath . '/convertido_' . time() . '.csv';

            // Cargar el Excel extraído
            $reader = IOFactory::createReaderForFile($txtPath);
            $reader->setReadDataOnly(false); // Necesario para leer estilos y colores
            $reader->setReadEmptyCells(false); // Evitar leer celdas vacías
            $spreadsheet = $reader->load($txtPath);

            // Abrir archivo CSV temporal para escribir filas con la nueva columna de COLOR
            $handle = fopen($csvPath, 'w');

            // --- CAMBIO 1: Variable para controlar el encabezado global ---
            $esPrimeraFilaGlobal = true;

            // --- CAMBIO 2: Recorrer TODAS las hojas del Excel ---
            foreach ($spreadsheet->getAllSheets() as $worksheet) {

                $highestRow = $worksheet->getHighestRow();
                $highestColumn = $worksheet->getHighestColumn();

                // Procesar fila por fila de la hoja actual
                for ($row = 1; $row <= $highestRow; $row++) {
                    // Obtener todos los valores de las celdas de la fila actual
                    $rowData = $worksheet->rangeToArray("A{$row}:{$highestColumn}{$row}", NULL, TRUE, FALSE)[0];

                    // --- CAMBIO 3: Control de encabezados entre hojas ---
                    if ($row === 1) {
                        if ($esPrimeraFilaGlobal) {
                            $rowData[] = 'COLOR';
                            fputcsv($handle, $rowData, ',', '"');
                            $esPrimeraFilaGlobal = false;
                        }
                        continue; // Omite escribir los encabezados repetidos de las hojas 2, 3, etc.
                    }

                    // Filas de datos: evaluamos el color de relleno de la celda en columna A
                    $fill = $worksheet->getStyle("A{$row}")->getFill();
                    $fillType = $fill->getFillType();

                    $colorTexto = '';

                    // Evaluamos solo si la celda efectivamente tiene un relleno configurado
                    if ($fillType !== \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_NONE) {
                        $rgb = strtoupper($fill->getStartColor()->getRGB());
                        $argb = strtoupper($fill->getStartColor()->getARGB());

                        // Detección de Verde
                        if (in_array($rgb, ['90EE90', '00FF00', 'C6EFCE', 'A9DFBF', '52BE80', '27AE60']) || strpos($argb, 'FF00') !== false || strpos($argb, '90EE') !== false) {
                            $colorTexto = 'VERDE';
                        }
                        // Detección de Amarillo
                        elseif (in_array($rgb, ['FFFF00', 'FFEB9C', 'F9E79F', 'F1C40F']) || strpos($argb, 'FFFF') !== false) {
                            $colorTexto = 'AMARILLO';
                        }
                    }

                    // Para celdas sin color o cualquier otra tonalidad, $colorTexto se mantiene como ''
                    $rowData[] = $colorTexto;

                    // Escribir fila formateada en el CSV con UTF-8
                    fputcsv($handle, $rowData, ',', '"');
                }
            }

            fclose($handle);

            // Eliminamos el Excel original y apuntamos a nuestro CSV generado
            @unlink($txtPath);
            $txtPath = $csvPath;
        }

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
            DB::statement("CREATE TABLE {$tablaTarget} LIKE movssep_struct");

            // 2. Obtener conexión PDO y activar soporte LOCAL INFILE
            $pdo = DB::connection()->getPdo();
            $pdo->setAttribute(PDO::MYSQL_ATTR_LOCAL_INFILE, true);

            // 3. Carga masiva rápida con soporte UTF-8 completo y mapeo de COLOR
            $sqlLoad = "LOAD DATA LOCAL INFILE " . $pdo->quote($txtPathMysql) . " 
                INTO TABLE {$tablaTarget}
                CHARACTER SET utf8mb4
                FIELDS TERMINATED BY ',' 
                OPTIONALLY ENCLOSED BY '\"'
                LINES TERMINATED BY '\n' 
                IGNORE 1 LINES
                (ID, UR, CURP, RFC, PRIMER_AP, CP, CPZA, ST, @dummy, MOVIMIENTO, OPERACION, INDICADOR_PAGO, @dummy, FECHA_INI, FECHA_FIN, FECHA_BAJA, REGIMEN, FOLIO_TICKET, FOLIO_EXCEPCION, @dummy, FECHA_OP, COLOR)
                SET FEC_INI = CONCAT(SUBSTR(FECHA_INI,7,4),'-',SUBSTR(FECHA_INI,4,2),'-',SUBSTR(FECHA_INI,1,2)),
                    FEC_FIN = CONCAT(SUBSTR(FECHA_FIN,7,4),'-',SUBSTR(FECHA_FIN,4,2),'-',SUBSTR(FECHA_FIN,1,2)),
                    FEC_BAJA = CONCAT(SUBSTR(FECHA_BAJA,7,4),'-',SUBSTR(FECHA_BAJA,4,2),'-',SUBSTR(FECHA_BAJA,1,2)),
                    FEC_OP = CONCAT(SUBSTR(FECHA_OP,7,4),'-',SUBSTR(FECHA_OP,4,2),'-',SUBSTR(FECHA_OP,1,2)),
                    FEC_OP_B = CONCAT(SUBSTR(FECHA_OP,7,4),'-',SUBSTR(FECHA_OP,4,2),'-',SUBSTR(FECHA_OP,1,2),' ', SUBSTR(FECHA_OP,12,5)),
                    CPZA_2 = SUBSTR(CPZA,3,21), 
                    CVEPRE = CPZA,
                    COLOR = TRIM(COLOR);";

            $pdo->exec($sqlLoad);

            // 4. Transformaciones posteriores
            DB::statement("UPDATE {$tablaTarget} SET cvepre = CONCAT(LEFT(cpza,6), SUBSTR(cpza,8,11), '1', RIGHT(cpza,5)) WHERE LENGTH(cpza)=24");
            DB::statement("UPDATE {$tablaTarget} SET cvepre = CONCAT(LEFT(cpza,17), '1', RIGHT(cpza,5)) WHERE LENGTH(cpza)=23");

            DB::table('actualizaciones')->updateOrInsert(
                ['tipo' => 'MDP'],
                ['tabla' => $tablaTarget, 'fecha' => $fechaInput]
            );

            DB::statement("UPDATE {$tablaTarget} SET FEC_FIN='2099-12-31', FECHA_FIN='00/00/0000' WHERE FECHA_FIN='' OR FECHA_FIN IS NULL");
            DB::statement("UPDATE {$tablaTarget} SET FEC_INI=FEC_FIN, FECHA_INI=FECHA_FIN WHERE FECHA_INI='' OR FECHA_INI IS NULL");
            DB::statement("UPDATE {$tablaTarget} SET CATEGORIA=TRIM(SUBSTR(CVEPRE,7,7))");

            // Limpiar archivo temporal
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