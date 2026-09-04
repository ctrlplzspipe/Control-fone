<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PlantillaAnalizadorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

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

        // Cargar la Fuente de Datos
        $spreadsheetFd = IOFactory::load($request->file('archivoF')->getRealPath());
        $rowsFdRaw = $spreadsheetFd->getActiveSheet()->toArray(null, true, true, false);

        // Cargar la Plantilla Ideal
        $spreadsheetPi = IOFactory::load($request->file('archivoP')->getRealPath());
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
            $colLetter = 'A';
            foreach ($row as $value) {
                $colData[$colLetter] = $value;
                $colLetter++;
            }
            $rowsPiFormatted[] = $colData;
        }

        // Ejecutar el análisis en el Servicio
        $reporte = $this->analizadorService->analizarPlantilla($rowsFd, $rowsPiFormatted);

        return response()->json($reporte, 200, [], JSON_UNESCAPED_UNICODE);
    }
}