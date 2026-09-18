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
            $index = $index * 26
                + (ord($colLetter[$i]) - ord('A') + 1);
        }

        return $index - 1;
    }

    private function normalizeExcelColor(?string $color): string
    {
        $color = strtoupper(trim((string) $color));

        if ($color === '') {
            return '';
        }

        if (strlen($color) === 8 && str_starts_with($color, 'FF')) {
            $color = substr($color, 2);
        }

        return strlen($color) === 6 ? $color : '';
    }

    /**
     * Paleta ampliada de colores indexados de Excel (0-63).
     */
    private function indexedColorToHex(?string $indexed): string
    {
        if ($indexed === null || $indexed === '') {
            return '';
        }

        $indexed = (int) $indexed;

        $colors = [
            0 => '000000',
            1 => 'FFFFFF',
            2 => 'FF0000',
            3 => '00FF00',
            4 => '0000FF',
            5 => 'FFFF00',
            6 => 'FF00FF',
            7 => '00FFFF',
            8 => '000000',
            9 => 'FFFFFF',
            10 => 'FF0000',
            11 => '00FF00',
            12 => '0000FF',
            13 => 'FFFF00',
            14 => 'FF00FF',
            15 => '00FFFF',
            16 => '800000',
            17 => '008000',
            18 => '000080',
            19 => '808000',
            20 => '800080',
            21 => '008080',
            22 => 'C0C0C0',
            23 => '808080',
            24 => '9999FF',
            25 => '993366',
            26 => 'FFFFCC',
            27 => 'CCFFFF',
            28 => '660066',
            29 => 'FF8080',
            30 => '0066CC',
            31 => 'CCCCFF',
            32 => '000080',
            33 => 'FF00FF',
            34 => 'FFFF00',
            35 => '00FFFF',
            36 => '800080',
            37 => '800000',
            38 => '008080',
            39 => '0000FF',
            40 => '00CCFF',
            41 => 'CCFFFF',
            42 => 'CCFFCC',
            43 => 'FFFF99',
            44 => '99CCFF',
            45 => 'FF99CC',
            46 => 'CC99FF',
            47 => 'FFCC99',
            48 => '3366FF',
            49 => '33CCCC',
            50 => '99CC00',
            51 => 'FFCC00',
            52 => 'FF9900',
            53 => 'FF6600',
            54 => '666699',
            55 => '969696',
            56 => '003366',
            57 => '339966',
            58 => '003300',
            59 => '333300',
            60 => '993300',
            61 => '993366',
            62 => '333399',
            63 => '333333',
        ];

        return $colors[$indexed] ?? '';
    }

    /**
     * Colores del tema estándar de Excel.
     */
    private function themeColorToHex(?string $theme): string
    {
        if ($theme === null || $theme === '') {
            return '';
        }

        $theme = (int) $theme;

        $themes = [
            0 => 'FFFFFF',
            1 => '000000',
            2 => 'EEECE1',
            3 => '1F497D',
            4 => '4F81BD',
            5 => 'C0504D',
            6 => '9BBB59',
            7 => '8064A2',
            8 => '4BACC6',
            9 => 'F79646',
        ];

        return $themes[$theme] ?? '';
    }

    /**
     * Aplica tint (aclarado/oscurecido) a un color RGB.
     */
    private function applyTint(string $rgb, float $tint): string
    {
        if ($rgb === '' || abs($tint) < 0.001) {
            return $rgb;
        }

        $r = hexdec(substr($rgb, 0, 2));
        $g = hexdec(substr($rgb, 2, 2));
        $b = hexdec(substr($rgb, 4, 2));

        if ($tint > 0) {
            $r = (int) round($r + (255 - $r) * $tint);
            $g = (int) round($g + (255 - $g) * $tint);
            $b = (int) round($b + (255 - $b) * $tint);
        } else {
            $r = (int) round($r * (1 + $tint));
            $g = (int) round($g * (1 + $tint));
            $b = (int) round($b * (1 + $tint));
        }

        return sprintf('%02X%02X%02X', $r, $g, $b);
    }

    /**
     * Detecta VERDE / AMARILLO a partir de rgb, indexed o theme,
     * usando el matiz (hue) del color en vez de una lista fija
     * de códigos exactos.
     */
    private function detectExcelColor($fgColor): string
    {
        if (!$fgColor) {
            return '';
        }

        $rgb = '';

        if (isset($fgColor['rgb']) && $fgColor['rgb'] !== '') {
            $rgb = $this->normalizeExcelColor((string) $fgColor['rgb']);
        }

        if ($rgb === '' && isset($fgColor['indexed']) && $fgColor['indexed'] !== '') {
            $rgb = $this->indexedColorToHex((string) $fgColor['indexed']);
        }

        if ($rgb === '' && isset($fgColor['theme']) && $fgColor['theme'] !== '') {
            $themeRgb = $this->themeColorToHex((string) $fgColor['theme']);
            $tint = isset($fgColor['tint']) ? (float) $fgColor['tint'] : 0.0;
            $rgb = $this->applyTint($themeRgb, $tint);
        }

        if ($rgb === '') {
            return '';
        }

        $rgb = strtoupper($rgb);

        $r = hexdec(substr($rgb, 0, 2));
        $g = hexdec(substr($rgb, 2, 2));
        $b = hexdec(substr($rgb, 4, 2));

        $max = max($r, $g, $b);
        $min = min($r, $g, $b);
        $delta = $max - $min;

        if ($delta < 20) {
            return '';
        }

        if ($max === $r) {
            $hue = 60 * fmod((($g - $b) / $delta), 6);
        } elseif ($max === $g) {
            $hue = 60 * ((($b - $r) / $delta) + 2);
        } else {
            $hue = 60 * ((($r - $g) / $delta) + 4);
        }

        if ($hue < 0) {
            $hue += 360;
        }

        if ($hue >= 40 && $hue < 70) {
            return 'AMARILLO';
        }

        if ($hue >= 70 && $hue <= 170) {
            return 'VERDE';
        }

        return '';
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

        // =========================================================
        // PROCESAMIENTO DEL XLSX
        // =========================================================
        if ($extension === 'xlsx') {

            $csvPath = $tempPath . '/convertido_' . time() . '.csv';
            $excelZip = new ZipArchive;

            if ($excelZip->open($txtPath) === TRUE) {

                // ---------------- styles.xml ----------------
                $stylesXml = $excelZip->getFromName('xl/styles.xml');
                $fillColors = [];

                if ($stylesXml) {
                    $xmlStyles = simplexml_load_string($stylesXml);

                    $fills = [];
                    if (isset($xmlStyles->fills->fill)) {
                        foreach ($xmlStyles->fills->fill as $fill) {
                            $color = '';
                            if (isset($fill->patternFill->fgColor)) {
                                $fgColor = $fill->patternFill->fgColor;

                                $colorData = [];
                                if (isset($fgColor['rgb']))
                                    $colorData['rgb'] = (string) $fgColor['rgb'];
                                if (isset($fgColor['indexed']))
                                    $colorData['indexed'] = (string) $fgColor['indexed'];
                                if (isset($fgColor['theme']))
                                    $colorData['theme'] = (string) $fgColor['theme'];
                                if (isset($fgColor['tint']))
                                    $colorData['tint'] = (string) $fgColor['tint'];

                                $color = $this->detectExcelColor($colorData);
                            }
                            $fills[] = $color;
                        }
                    }

                    if (isset($xmlStyles->cellXfs->xf)) {
                        $styleIndex = 0;
                        foreach ($xmlStyles->cellXfs->xf as $xf) {
                            $fillId = isset($xf['fillId']) ? (int) $xf['fillId'] : 0;
                            $fillColors[$styleIndex] = $fills[$fillId] ?? '';
                            $styleIndex++;
                        }
                    }
                }

                // ---------------- sharedStrings.xml ----------------
                $sharedStringsXml = $excelZip->getFromName('xl/sharedStrings.xml');
                $sharedStrings = [];

                if ($sharedStringsXml) {
                    $xmlStrings = simplexml_load_string($sharedStringsXml);
                    foreach ($xmlStrings->si as $val) {
                        $text = '';
                        if (isset($val->t)) {
                            $text = (string) $val->t;
                        } elseif (isset($val->r)) {
                            foreach ($val->r as $run) {
                                if (isset($run->t)) {
                                    $text .= (string) $run->t;
                                }
                            }
                        }
                        $sharedStrings[] = $text;
                    }
                }

                // ---------------- sheet1.xml ----------------
                $sheetXmlPath = $tempPath . '/sheet1_' . time() . '.xml';
                $excelZip->extractTo($tempPath, 'xl/worksheets/sheet1.xml');

                rename(
                    $tempPath . '/xl/worksheets/sheet1.xml',
                    $sheetXmlPath
                );

                @rmdir($tempPath . '/xl/worksheets');
                @rmdir($tempPath . '/xl');
                $excelZip->close();

                // ---------------- XMLReader ----------------
                $reader = new XMLReader;
                if (!$reader->open($sheetXmlPath)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pudo abrir el XML interno del Excel.'
                    ], 400);
                }

                $handle = fopen($csvPath, 'w');
                if (!$handle) {
                    $reader->close();
                    return response()->json([
                        'success' => false,
                        'message' => 'No se pudo crear el CSV temporal.'
                    ], 400);
                }

                $currentRow = [];
                $rowColor = '';
                $rowStyleColor = '';
                $isFirstRow = true;
                $headerColCount = 0;
                $maxColIndex = -1;

                while ($reader->read()) {

                    if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'row') {
                        $currentRow = [];
                        $rowColor = '';
                        $rowStyleColor = '';
                        $maxColIndex = -1;

                        $rowStyleIndex = $reader->getAttribute('s');
                        if ($rowStyleIndex !== null) {
                            $rowStyleIndex = (int) $rowStyleIndex;
                            $rowStyleColor = $fillColors[$rowStyleIndex] ?? '';
                        }
                    }

                    if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'c') {
                        $cellType = $reader->getAttribute('t');
                        $styleIndex = $reader->getAttribute('s');
                        $styleIndex = $styleIndex !== null ? (int) $styleIndex : 0;

                        $cellColor = $fillColors[$styleIndex] ?? '';

                        // Guarda el último color detectado en la fila
                        // (cada fila trae un único color de resaltado)
                        if ($cellColor !== '') {
                            $rowColor = $cellColor;
                        }

                        $cellRef = $reader->getAttribute('r');
                        $colLetters = $cellRef
                            ? preg_replace('/[0-9]/', '', $cellRef)
                            : '';

                        $colIndex = $colLetters !== ''
                            ? $this->colLetterToIndex($colLetters)
                            : ($maxColIndex + 1);

                        if ($colIndex > $maxColIndex) {
                            $maxColIndex = $colIndex;
                        }

                        $cellValue = '';
                        while ($reader->read()) {
                            if ($reader->nodeType === XMLReader::ELEMENT && $reader->name === 'v') {
                                $rawVal = $reader->readString();

                                if ($cellType === 's' && isset($sharedStrings[(int) $rawVal])) {
                                    $cellValue = $sharedStrings[(int) $rawVal];
                                } else {
                                    $cellValue = $rawVal;
                                }
                                break;
                            }
                            if ($reader->nodeType === XMLReader::END_ELEMENT && $reader->name === 'c') {
                                break;
                            }
                        }

                        $currentRow[$colIndex] = $cellValue;
                    }

                    if ($reader->nodeType === XMLReader::END_ELEMENT && $reader->name === 'row') {

                        if ($isFirstRow) {
                            $headerColCount = $maxColIndex + 1;
                        }

                        $orderedRow = [];
                        for ($c = 0; $c < $headerColCount; $c++) {
                            $orderedRow[] = $currentRow[$c] ?? '';
                        }

                        if ($isFirstRow) {
                            $orderedRow[] = 'COLOR';
                            $isFirstRow = false;
                        } else {
                            if ($rowColor === '' && $rowStyleColor !== '') {
                                $rowColor = $rowStyleColor;
                            }
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

        // =========================================================
        // CARGA A MYSQL
        // =========================================================
        try {
            $txtPathMysql = str_replace('\\', '/', $txtPath);

            DB::statement("DROP TABLE IF EXISTS {$tablaTarget}");
            DB::statement("CREATE TABLE {$tablaTarget} LIKE movssep_struct");
            DB::statement("ALTER TABLE {$tablaTarget} ADD INDEX idx_filtro (ST, COLOR)");

            $pdo = DB::connection()->getPdo();
            $pdo->setAttribute(PDO::MYSQL_ATTR_LOCAL_INFILE, true);

            $pdo->exec(
                "SET SESSION sql_mode = " .
                "REPLACE(" .
                "REPLACE(@@sql_mode, 'STRICT_TRANS_TABLES', ''), " .
                "'STRICT_ALL_TABLES', ''" .
                ")"
            );

            $sqlLoad =
                "LOAD DATA INFILE " .
                $pdo->quote($txtPathMysql) .
                "
                INTO TABLE {$tablaTarget}
                CHARACTER SET utf8mb4
                FIELDS TERMINATED BY ','
                OPTIONALLY ENCLOSED BY '\"'
                LINES TERMINATED BY '\\n'
                IGNORE 1 LINES
                (
                    ID, UR, CURP, RFC, PRIMER_AP, CP, CPZA, ST, @dummy, MOVIMIENTO,
                    OPERACION, INDICADOR_PAGO, @dummy, FECHA_INI, FECHA_FIN,
                    FECHA_BAJA, REGIMEN, FOLIO_TICKET, FOLIO_EXCEPCION, @dummy,
                    FECHA_OP, COLOR
                )
                SET
                    FEC_INI = CONCAT(SUBSTR(FECHA_INI,7,4),'-',SUBSTR(FECHA_INI,4,2),'-',SUBSTR(FECHA_INI,1,2)),
                    FEC_FIN = CONCAT(SUBSTR(FECHA_FIN,7,4),'-',SUBSTR(FECHA_FIN,4,2),'-',SUBSTR(FECHA_FIN,1,2)),
                    FEC_BAJA = CONCAT(SUBSTR(FECHA_BAJA,7,4),'-',SUBSTR(FECHA_BAJA,4,2),'-',SUBSTR(FECHA_BAJA,1,2)),
                    FEC_OP = CONCAT(SUBSTR(FECHA_OP,7,4),'-',SUBSTR(FECHA_OP,4,2),'-',SUBSTR(FECHA_OP,1,2)),
                    FEC_OP_B = CONCAT(SUBSTR(FECHA_OP,7,4),'-',SUBSTR(FECHA_OP,4,2),'-',SUBSTR(FECHA_OP,1,2),' ',SUBSTR(FECHA_OP,12,5)),
                    CPZA_2 = SUBSTR(CPZA,3,21),
                    CVEPRE = CPZA,
                    COLOR = NULLIF(TRIM(COLOR), '')
                ";

            $pdo->exec($sqlLoad);

            DB::transaction(function () use ($tablaTarget, $fechaInput) {

                DB::statement("
                    DELETE FROM {$tablaTarget}
                    WHERE
                        (ST NOT IN ('O', 'C', 'V') OR ST IS NULL)
                        AND
                        (COLOR NOT IN ('VERDE', 'AMARILLO') OR COLOR IS NULL)
                ");

                DB::statement("
                    UPDATE {$tablaTarget}
                    SET cvepre = CONCAT(LEFT(cpza,6), SUBSTR(cpza,8,11), '1', RIGHT(cpza,5))
                    WHERE LENGTH(cpza)=24
                ");

                DB::statement("
                    UPDATE {$tablaTarget}
                    SET cvepre = CONCAT(LEFT(cpza,17), '1', RIGHT(cpza,5))
                    WHERE LENGTH(cpza)=23
                ");

                DB::table('actualizaciones')->updateOrInsert(
                    ['tipo' => 'MDP'],
                    ['tabla' => $tablaTarget, 'fecha' => $fechaInput]
                );

                DB::statement("
                    UPDATE {$tablaTarget}
                    SET FEC_FIN='2099-12-31', FECHA_FIN='00/00/0000'
                    WHERE FECHA_FIN='' OR FECHA_FIN IS NULL
                ");

                DB::statement("
                    UPDATE {$tablaTarget}
                    SET FEC_INI=FEC_FIN, FECHA_INI=FECHA_FIN
                    WHERE FECHA_INI='' OR FECHA_INI IS NULL
                ");

                DB::statement("
                    UPDATE {$tablaTarget}
                    SET CATEGORIA = TRIM(SUBSTR(CVEPRE, 7, 7))
                ");
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