<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use Exception;
use PDO;
use XMLReader;

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

    private function colLetterToIndex(string $colLetter): int
    {
        $index = 0;
        $length = strlen($colLetter);
        for ($i = 0; $i < $length; $i++) {
            $index = $index * 26 + (ord($colLetter[$i]) - ord('A') + 1);
        }
        return $index - 1; // 0-based
    }

    public function store(Request $request)
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $request->validate([
            'lbl_FECHA' => 'required|date',
            'archivo' => 'required|file|mimes:zip|max:1024000',
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
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (preg_match('/\.(csv|txt|xlsx|xls)$/i', $filename)) {
                    $extractedFile = $filename;
                    break;
                }
            }

            // MEJORA: extraer solo el archivo que vamos a usar, no todo el ZIP
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
                'message' => 'No se encontró ningún archivo válido dentro del ZIP.'
            ], 400);
        }

        $txtPath = $tempPath . '/' . $extractedFile;
        $extension = strtolower(pathinfo($txtPath, PATHINFO_EXTENSION));

        // Conversión ultrarrápida usando Streaming XML para evitar saturación de memoria RAM
        if (in_array($extension, ['xlsx'])) {
            $csvPath = $tempPath . '/convertido_' . time() . '.csv';

            $excelZip = new ZipArchive;
            if ($excelZip->open($txtPath) === TRUE) {
                // 1. Cargar el mapa de estilos (colores de relleno)
                $stylesXml = $excelZip->getFromName('xl/styles.xml');
                $fillColors = []; // Mapeo de styleIndex => COLOR

                if ($stylesXml) {
                    $xmlStyles = simplexml_load_string($stylesXml);
                    $fills = [];
                    foreach ($xmlStyles->fills->fill as $fill) {
                        $fgColor = $fill->patternFill->fgColor;
                        $rgb = (string) ($fgColor['rgb'] ?? $fgColor['indexed'] ?? '');
                        $fills[] = strtoupper($rgb);
                    }

                    $xfsIndex = 0;
                    foreach ($xmlStyles->cellXfs->xf as $xf) {
                        $fillId = (int) $xf['fillId'];
                        $colorHex = $fills[$fillId] ?? '';

                        if (preg_match('/(90EE90|00FF00|C6EFCE|A9DFBF|52BE80|27AE60|FF001700)/i', $colorHex)) {
                            $fillColors[$xfsIndex] = 'VERDE';
                        } elseif (preg_match('/(FFFF00|FFEB9C|F9E79F|F1C40F|FFFF99)/i', $colorHex)) {
                            $fillColors[$xfsIndex] = 'AMARILLO';
                        } else {
                            $fillColors[$xfsIndex] = '';
                        }
                        $xfsIndex++;
                    }
                }

                // 2. Cargar diccionario de strings compartidas
                $sharedStringsXml = $excelZip->getFromName('xl/sharedStrings.xml');
                $sharedStrings = [];
                if ($sharedStringsXml) {
                    $xmlStrings = simplexml_load_string($sharedStringsXml);
                    foreach ($xmlStrings->si as $val) {
                        $sharedStrings[] = (string) ($val->t ?? $val->r->t ?? '');
                    }
                }

                // 3. Procesar sheet1.xml en modo Streaming con XMLReader
                $sheetXmlPath = $tempPath . '/sheet1_' . time() . '.xml';
                $excelZip->extractTo($tempPath, 'xl/worksheets/sheet1.xml');
                rename($tempPath . '/xl/worksheets/sheet1.xml', $sheetXmlPath);
                @rmdir($tempPath . '/xl/worksheets');
                @rmdir($tempPath . '/xl');
                $excelZip->close();

                $reader = new XMLReader();
                $reader->open($sheetXmlPath);

                $handle = fopen($csvPath, 'w');
                $currentRow = [];
                $rowColor = '';
                $isFirstRow = true;
                $headerColCount = 0;
                $maxColIndex = -1;

                while ($reader->read()) {
                    if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'row') {
                        $currentRow = [];
                        $rowColor = '';
                        $maxColIndex = -1;
                    }

                    if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'c') {
                        $cellType = $reader->getAttribute('t');
                        $styleIndex = (int) ($reader->getAttribute('s') ?? 0);

                        // FIX: usar la referencia real de la celda (ej. "N2") para saber
                        // en qué columna va el valor, en vez de simplemente ir agregando
                        // al arreglo. Excel omite del XML las celdas vacías, así que sin
                        // esto los valores de las columnas siguientes se recorren de lugar.
                        $cellRef = $reader->getAttribute('r');
                        $colLetters = $cellRef ? preg_replace('/[0-9]/', '', $cellRef) : '';
                        $colIndex = $colLetters !== '' ? $this->colLetterToIndex($colLetters) : ($maxColIndex + 1);

                        if ($colIndex > $maxColIndex) {
                            $maxColIndex = $colIndex;
                        }

                        // Si es la primera columna y aún no definimos color para la fila
                        if (empty($rowColor) && isset($fillColors[$styleIndex])) {
                            $rowColor = $fillColors[$styleIndex];
                        }

                        // Leer valor de la celda
                        $cellValue = '';
                        while ($reader->read()) {
                            if ($reader->nodeType == XMLReader::ELEMENT && $reader->name == 'v') {
                                $rawVal = $reader->readString();
                                if ($cellType === 's' && isset($sharedStrings[$rawVal])) {
                                    $cellValue = $sharedStrings[$rawVal];
                                } else {
                                    $cellValue = $rawVal;
                                }
                                break;
                            }
                            if ($reader->nodeType == XMLReader::END_ELEMENT && $reader->name == 'c') {
                                break;
                            }
                        }
                        $currentRow[$colIndex] = $cellValue;
                    }

                    if ($reader->nodeType == XMLReader::END_ELEMENT && $reader->name == 'row') {
                        if ($isFirstRow) {
                            $headerColCount = $maxColIndex + 1;
                        }

                        // FIX: reconstruir la fila en orden, rellenando con '' las
                        // celdas que Excel omitió por estar vacías
                        $orderedRow = [];
                        for ($c = 0; $c < $headerColCount; $c++) {
                            $orderedRow[] = $currentRow[$c] ?? '';
                        }

                        if ($isFirstRow) {
                            $orderedRow[] = 'COLOR';
                            $isFirstRow = false;
                        } else {
                            $orderedRow[] = $rowColor;
                        }
                        fputcsv($handle, $orderedRow, ',', '"');
                    }
                }

                $reader->close();
                fclose($handle);
                @unlink($sheetXmlPath);

                @unlink($txtPath);
                $txtPath = $csvPath;
            }
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

            // MEJORA: índice para que el DELETE/UPDATE posteriores no hagan table scan completo
            DB::statement("ALTER TABLE {$tablaTarget} ADD INDEX idx_filtro (ST, COLOR)");

            // 2. Conexión PDO e Infile
            $pdo = DB::connection()->getPdo();
            $pdo->setAttribute(PDO::MYSQL_ATTR_LOCAL_INFILE, true);

            // MEJORA/FIX: MySQL 8.x trae STRICT_TRANS_TABLES activo por defecto, lo que convierte
            // los truncamientos de datos en error fatal en vez de advertencia. Se desactiva solo
            // para esta sesión, para conservar el comportamiento previo (truncar y continuar).
            $pdo->exec("SET SESSION sql_mode = REPLACE(REPLACE(@@sql_mode, 'STRICT_TRANS_TABLES', ''), 'STRICT_ALL_TABLES', '')");

            // 3. Carga masiva rápida UTF-8
            // MEJORA: se quita LOCAL porque Laragon corre MySQL en el mismo servidor,
            // así MySQL lee el archivo directo de disco en vez de recibirlo por el cliente PHP.
            // Requiere que $tempPath esté dentro de lo permitido por secure_file_priv (ver nota abajo).
            $sqlLoad = "LOAD DATA INFILE " . $pdo->quote($txtPathMysql) . " 
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
                    COLOR = NULLIF(TRIM(COLOR), '');";

            $pdo->exec($sqlLoad);

            // MEJORA: se agrupan el filtrado y las transformaciones en una sola transacción
            // (mismo orden y mismas sentencias, solo se evita el commit individual de cada una)
            DB::transaction(function () use ($tablaTarget, $fechaInput) {
                // 4. Filtrado automático ETL
                DB::statement("
                    DELETE FROM {$tablaTarget} 
                    WHERE (ST NOT IN ('O', 'C', 'V') OR ST IS NULL) 
                      AND (COLOR != 'VERDE' OR COLOR IS NULL)
                ");

                // 5. Transformaciones finales de negocio
                DB::statement("UPDATE {$tablaTarget} SET cvepre = CONCAT(LEFT(cpza,6), SUBSTR(cpza,8,11), '1', RIGHT(cpza,5)) WHERE LENGTH(cpza)=24");
                DB::statement("UPDATE {$tablaTarget} SET cvepre = CONCAT(LEFT(cpza,17), '1', RIGHT(cpza,5)) WHERE LENGTH(cpza)=23");

                DB::table('actualizaciones')->updateOrInsert(
                    ['tipo' => 'MDP'],
                    ['tabla' => $tablaTarget, 'fecha' => $fechaInput]
                );

                DB::statement("UPDATE {$tablaTarget} SET FEC_FIN='2099-12-31', FECHA_FIN='00/00/0000' WHERE FECHA_FIN='' OR FECHA_FIN IS NULL");
                DB::statement("UPDATE {$tablaTarget} SET FEC_INI=FEC_FIN, FECHA_INI=FECHA_FIN WHERE FECHA_INI='' OR FECHA_INI IS NULL");
                DB::statement("UPDATE {$tablaTarget} SET CATEGORIA=TRIM(SUBSTR(CVEPRE,7,7))");
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