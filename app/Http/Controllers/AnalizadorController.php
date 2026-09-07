<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PlantillaAnalizadorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class AnalizadorController extends Controller
{
    public function __construct(
        protected PlantillaAnalizadorService $analizadorService
    ) {}

    public function procesar(Request $request): JsonResponse
    {
        $request->validate([
            'archivoF' => 'required|file|mimes:xlsx,xls',
            'archivoP' => 'required|file|mimes:xlsx,xls',
        ]);

        // Cargar la Fuente de Datos (Modo optimizado: solo lectura de datos)
        $readerFd = IOFactory::createReaderForFile($request->file('archivoF')->getRealPath());
        $readerFd->setReadDataOnly(true);
        $spreadsheetFd = $readerFd->load($request->file('archivoF')->getRealPath());
        $rowsFdRaw = $spreadsheetFd->getActiveSheet()->toArray(null, true, true, false);

        // Cargar la Plantilla Ideal (Modo optimizado)
        $readerPi = IOFactory::createReaderForFile($request->file('archivoP')->getRealPath());
        $readerPi->setReadDataOnly(true);
        $spreadsheetPi = $readerPi->load($request->file('archivoP')->getRealPath());
        $rowsPiRaw = $spreadsheetPi->getActiveSheet()->toArray(null, true, true, false);

        // Convertir la fuente de datos a arreglo asociativo usando los encabezados de la primera fila
        $headers = array_shift($rowsFdRaw) ?? [];
        $rowsFd = [];
        foreach ($rowsFdRaw as $row) {
            $assoc = [];
            foreach ($headers as $i => $header) {
                $headerKey = trim((string) $header);
                if ($headerKey !== '') {
                    $assoc[$headerKey] = $row[$i] ?? null;
                }
            }
            $rowsFd[] = $assoc;
        }

        // Mapear la Plantilla Ideal por letras de columna (A, B, C...)
        $rowsPiFormatted = [];
        foreach ($rowsPiRaw as $row) {
            $colData = [];
            $colIndex = 1;
            foreach ($row as $value) {
                // Utiliza la función nativa de PhpSpreadsheet para obtener la letra exacta (A, B... Z, AA, AB)
                $colLetter = Coordinate::stringFromColumnIndex($colIndex);
                $colData[$colLetter] = $value;
                $colIndex++;
            }
            $rowsPiFormatted[] = $colData;
        }

        // Ejecutar el análisis en el Servicio
        $reporte = $this->analizadorService->analizarPlantilla($rowsFd, $rowsPiFormatted);

        return response()->json($reporte, 200, [], JSON_UNESCAPED_UNICODE);
    }
}