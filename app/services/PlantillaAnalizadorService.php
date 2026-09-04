<?php

declare(strict_types=1);

namespace App\Services;

class PlantillaAnalizadorService
{
    /**
     * Procesa la hoja de estructura ideal enviada como arreglo por columnas (A, B, C...).
     */
    public function parsePlantilla(array $rowsPiRaw): array
    {
        // Retirar la fila de encabezados si existe
        if (!empty($rowsPiRaw)) {
            array_shift($rowsPiRaw);
        }

        $idealCategorias = [];
        $limitePorCategoria = [];
        $categoriasPorFuncionGeneral = [];
        $limitePorFuncionGeneral = [];

        foreach ($rowsPiRaw as $row) {
            $tipoCt = trim((string) ($row['A'] ?? ''));
            $categoria = trim((string) ($row['B'] ?? ''));
            $funcionGeneral = trim((string) ($row['E'] ?? ''));
            $limiteCt = is_numeric($row['G'] ?? null) ? (int) $row['G'] : null;
            $limiteFuncionGeneral = is_numeric($row['H'] ?? null) ? (int) $row['H'] : null;

            if ($tipoCt === '' || $categoria === '' || strtoupper($categoria) === 'N/A') {
                continue;
            }

            $idealCategorias[$tipoCt][$categoria] = true;
            $limitePorCategoria[$tipoCt][$categoria] = $limiteCt ?? 1;

            if ($funcionGeneral !== '') {
                $categoriasPorFuncionGeneral[$tipoCt][$funcionGeneral][] = $categoria;
                if ($limiteFuncionGeneral !== null) {
                    $actual = $limitePorFuncionGeneral[$tipoCt][$funcionGeneral] ?? 0;
                    $limitePorFuncionGeneral[$tipoCt][$funcionGeneral] = max($actual, $limiteFuncionGeneral);
                }
            }
        }

        return [
            'idealCategorias' => $idealCategorias,
            'limitePorCategoria' => $limitePorCategoria,
            'categoriasPorFuncionGeneral' => $categoriasPorFuncionGeneral,
            'limitePorFuncionGeneral' => $limitePorFuncionGeneral,
        ];
    }

    /**
     * Analiza las coincidencias y discrepancias entre la Fuente de Datos y la Plantilla Ideal.
     * 
     * @param array $rowsFd Arreglo asociativo de la fuente de datos.
     * @param array $rowsPiRaw Arreglo por columnas de la plantilla ideal.
     */
    public function analizarPlantilla(array $rowsFd, array $rowsPiRaw): array
    {
        $plantilla = $this->parsePlantilla($rowsPiRaw);
        $idealCategorias = $plantilla['idealCategorias'];
        $limitePorCategoria = $plantilla['limitePorCategoria'];
        $categoriasPorFuncionGeneral = $plantilla['categoriasPorFuncionGeneral'];
        $limitePorFuncionGeneral = $plantilla['limitePorFuncionGeneral'];

        // Agrupar filas de la fuente de datos por Centro de Trabajo (CT)
        $gruposCt = [];
        foreach ($rowsFd as $row) {
            $rowValues = array_values($row);
            $ct = trim((string) ($rowValues[11] ?? ''));
            if ($ct === '') {
                continue;
            }

            if (!isset($gruposCt[$ct])) {
                $gruposCt[$ct] = [
                    'tipo_cct' => trim((string) ($rowValues[10] ?? '')),
                    'categorias' => []
                ];
            }

            $cat = trim((string) ($rowValues[4] ?? ''));
            if ($cat !== '') {
                $gruposCt[$ct]['categorias'][$cat] = ($gruposCt[$ct]['categorias'][$cat] ?? 0) + 1;
            }
        }

        $resultados = [];
        foreach ($gruposCt as $ct => $datos) {
            $tipoCct = $datos['tipo_cct'];
            $conteo = $datos['categorias'];
            $catsReales = array_keys($conteo);
            $catsIdeales = isset($idealCategorias[$tipoCct]) ? array_keys($idealCategorias[$tipoCct]) : [];

            $existentes = array_values(array_intersect($catsReales, $catsIdeales));
            $faltantes = array_values(array_diff($catsIdeales, $catsReales));
            $noDeberian = array_values(array_diff($catsReales, $catsIdeales));

            sort($existentes);
            sort($faltantes);
            sort($noDeberian);

            // --- Excedencias ---
            $excedencias = [];

            // 1) Límite por categoría individual
            foreach ($conteo as $cat => $veces) {
                $limite = $limitePorCategoria[$tipoCct][$cat] ?? 1;
                if ($veces > $limite) {
                    $excedencias[] = "{$cat} repetida {$veces} veces (límite: {$limite})";
                }
            }

            // 2) Límite por función general
            if (isset($categoriasPorFuncionGeneral[$tipoCct])) {
                foreach ($categoriasPorFuncionGeneral[$tipoCct] as $funcionGeneral => $categoriasDelGrupo) {
                    $totalGrupo = 0;
                    $detalleGrupo = [];
                    foreach (array_unique($categoriasDelGrupo) as $cat) {
                        if (isset($conteo[$cat])) {
                            $totalGrupo += $conteo[$cat];
                            $detalleGrupo[] = $conteo[$cat] > 1 ? "{$cat} (x{$conteo[$cat]})" : $cat;
                        }
                    }
                    $limiteGrupo = $limitePorFuncionGeneral[$tipoCct][$funcionGeneral] ?? null;
                    if ($limiteGrupo !== null && $totalGrupo > $limiteGrupo) {
                        $excedencias[] = "{$funcionGeneral} excede el límite ({$totalGrupo} de {$limiteGrupo} permitidos): " . implode(' + ', $detalleGrupo);
                    }
                }
            }

            $estado = (count($faltantes) === 0 && count($noDeberian) === 0 && count($excedencias) === 0)
                ? 'OK'
                : 'CON_PROBLEMAS';

            $resultados[] = [
                'CT' => $ct,
                'TIPO_CCT' => $tipoCct,
                'Cant_Existentes' => count($existentes),
                'Categorias_Existentes' => implode(', ', $existentes),
                'Cant_Faltantes' => count($faltantes),
                'Categorias_Faltantes' => implode(', ', $faltantes),
                'Cant_No_Deberian_Existir' => count($noDeberian),
                'Categorias_No_Deberian_Existir' => implode(', ', $noDeberian),
                'Cant_Excedencias' => count($excedencias),
                'Excedencias' => implode(' | ', $excedencias),
                'Estado' => $estado,
            ];
        }

        return $resultados;
    }
}