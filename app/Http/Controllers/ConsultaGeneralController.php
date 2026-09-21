<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ConsultaGeneralController extends Controller
{
    /**
     * Muestra la vista principal del formulario con las fechas de actualización.
     */
    public function index()
    {
        // 1. Obtener fecha de Anexo IV (layoutp_gral)
        $dateAnexo = 'N/D';
        $anexoRow = DB::table('layoutp_gral')
            ->select('qna_afec')
            ->distinct()
            ->orderBy('qna_afec', 'desc')
            ->first();

        if ($anexoRow && !empty($anexoRow->qna_afec)) {
            $anio = substr($anexoRow->qna_afec, 0, 4);
            $qna = substr($anexoRow->qna_afec, 4, 2);
            $dateAnexo = $anio . "/" . $qna;
        }

        // 2. Obtener fecha de Analítico
        $dateAnalitico = 'N/D';
        $analiticoRow = DB::table('actualizaciones')
            ->where('TIPO', 'ANALITICO')
            ->first();

        if ($analiticoRow && !empty($analiticoRow->tabla)) {
            $tabla = $analiticoRow->tabla;
            $anio = substr($tabla, 9, 4);
            $qna = substr($tabla, 13, 2);
            $dateAnalitico = $anio . "/" . $qna;
        }

        // 3. Obtener fecha de MDP (FONE)
        $dateMDP = 'N/D';
        $mdpRow = DB::table('actualizaciones')
            ->where('TIPO', 'MDP')
            ->first();

        if ($mdpRow && !empty($mdpRow->fecha)) {
            $dateMDP = Carbon::parse($mdpRow->fecha)->format('d/m/Y');
        }

        return view('consulta_general.index', compact('dateAnexo', 'dateAnalitico', 'dateMDP'));
    }

    /**
     * Muestra la pantalla de resultados DataTables con validación previa.
     */
    public function resultados(Request $request)
    {
        // Obtener la opción enviada sin importar si la vista envía 'optTipo' o 'campo'
        $tipoCampo = $request->input('optTipo') ?? $request->input('campo');
        $datoInput = strtoupper(trim($request->input('dato', '')));

        // Validaciones permisivas: se permite búsqueda parcial (mínimo 4 caracteres)
        // en vez de exigir el CURP/RFC/CVEPRE completo.
        $validator = Validator::make([
            'optTipo' => $tipoCampo,
            'dato' => $datoInput,
        ], [
            'optTipo' => 'required|in:CURP,RFC,CVEPRE,CCT,NOMBRE',
            'dato' => [
                'required',
                'string',
                'min:4',
                'max:100',
                function ($attribute, $value, $fail) {
                    if (preg_match('/^(.)\1+$/', $value)) {
                        $fail('El término ingresado contiene solo caracteres repetidos. Por favor ingresa un dato válido.');
                    }
                },
                // Límites máximos por tipo de dato (ya no se exige la longitud
                // completa ni la estructura exacta, para permitir búsqueda parcial)
                Rule::when($tipoCampo === 'CURP', ['max:18']),
                Rule::when($tipoCampo === 'RFC', ['max:13']),
                Rule::when($tipoCampo === 'CVEPRE', ['max:25']),
                Rule::when($tipoCampo === 'CCT', ['max:10']),
            ],
        ], [
            'optTipo.required' => 'Debe seleccionar un tipo de parámetro para la búsqueda.',
            'optTipo.in' => 'El tipo de parámetro seleccionado no es válido.',
            'dato.required' => 'Por favor ingresa un dato para realizar la búsqueda.',
            'dato.min' => 'Ingresa al menos 4 caracteres para realizar la búsqueda.',
            'dato.max' => 'El valor ingresado supera el límite permitido.',
        ]);

        // Si falla la validación, redirigir al formulario notificando el error
        if ($validator->fails()) {
            return redirect()->route('consulta.general')
                ->withErrors($validator)
                ->withInput();
        }

        $campo = $tipoCampo;
        $dato = $datoInput;

        return view('consulta_general.resultados', compact('campo', 'dato'));
    }

    /**
     * Endpoint AJAX que procesa las tablas temporales y retorna el JSON para DataTables.
     */
    public function data(Request $request)
    {
        $campo = $request->query('campo');
        $dato = strtoupper(trim($request->query('dato')));

        // Validar columnas permitidas para evitar SQL Injection dinámica
        $columnasPermitidas = ['CURP', 'RFC', 'CVEPRE', 'CCT', 'NOMBRE'];
        if (!in_array($campo, $columnasPermitidas)) {
            return response()->json(['data' => []]);
        }

        // 1. Obtener nombres de tablas dinámicas desde `actualizaciones`
        $tablaMDP = DB::table('actualizaciones')->where('TIPO', 'MDP')->value('tabla');
        $tablaA = DB::table('actualizaciones')->where('TIPO', 'ANALITICO')->value('tabla');
        $tablaMAP = DB::table('actualizaciones')->where('TIPO', 'MAP')->value('tabla');

        // 2. Preparar/Limpiar la tabla temporal consultagral
        DB::statement("DROP TABLE IF EXISTS consultagral");
        DB::statement("CREATE TABLE consultagral LIKE consultagral_struct");

        // Array de tablas históricas de layout
        $tablasLayout = [
            'layoutp_gral',
            'layoutp_2019',
            'layoutp_2018',
            'layoutp_2017',
            'layoutp_2016',
            'layoutp_2015'
        ];

        // 3. Insertar registros desde cada tabla Layout con límite dinámico
        foreach ($tablasLayout as $idx => $tabla) {
            $numRows = DB::table('consultagral')->count();
            $regs = (41 - $numRows > 0) ? (41 - $numRows) : 0;

            if ($idx > 0 && $regs <= 0) {
                break; // Si ya se completó el cupo de registros, detener barrido
            }

            $limit = ($idx === 0) ? 40 : $regs;

            DB::statement("
                INSERT INTO consultagral 
                (SELECT 0, 'Layout IV' AS FUENTE, QNA_AFEC, OPERACION, COALESCE(COD_SEP, '') AS COD_SEP, CURP, CVEPRE, NS, CCT, RFC, 
                PRIMER_AP AS AP_PAT, SEGUNDO_AP AS AP_MAT, NOMBRE, FECHA_INI, FECHA_FIN, CPZA 
                FROM {$tabla} 
                WHERE {$campo} LIKE ? AND OPERACION NOT IN ('08-98','08','8') 
                ORDER BY QNA_AFEC DESC LIMIT {$limit})
            ", ["%{$dato}%"]);
        }

        // 4. Insertar desde la tabla de ANALITICO ($tablaA)
        if (!empty($tablaA)) {
            DB::statement("
                INSERT INTO consultagral 
                (SELECT 0, 'Analitico' AS FUENTE, '' AS QNA_AFEC, CONCAT(st, '-', mot) AS OPERACION, CONCAT(SEP_1, '-', SEP_2, '-', SEP_3) AS COD_SEP, CURP, CVEPRE, NS, ct AS CCT, RFC, 
                AP_PAT, AP_MAT, NOMBRE, FECHA_INI, FECHA_FIN, '' AS CPZA 
                FROM {$tablaA} 
                WHERE {$campo} LIKE ? 
                ORDER BY HASTA DESC LIMIT 40)
            ", ["%{$dato}%"]);
        }

        // 5. Insertar desde MDP y MAP (FONE)
        if (!empty($tablaMDP)) {
            $mapJoin = !empty($tablaMAP) ? "LEFT JOIN (SELECT * FROM {$tablaMAP} WHERE NOT STATUS='C') MAP ON MDP.cpza=MAP.cpza" : "";
            $mapSelectNS = !empty($tablaMAP) ? "MAP.nivel" : "''";
            $mapSelectCCT = !empty($tablaMAP) ? "MAP.cct" : "''";

            $sqlFone = "
                INSERT INTO consultagral 
                SELECT * FROM ( 
                    ( SELECT 0, 'FONE' AS FUENTE, MDP.fec_op AS QNA_AFEC, OPERACION, operacion AS COD_SEP, CURP, MDP.CVEPRE, {$mapSelectNS} AS NS, {$mapSelectCCT} AS CCT, RFC, 
                    PRIMER_AP AS AP_PAT, SEGUNDO_AP AS AP_MAT, NOMBRE, FEC_INI, FEC_FIN, MDP.CPZA 
                    FROM (
                        SELECT * FROM {$tablaMDP} MDP 
                        WHERE MDP.{$campo} LIKE ? AND OPERACION NOT IN ('08-98','08','8') AND MDP.FEC_FIN >= NOW() AND MDP.FECHA_BAJA = '' 
                        ORDER BY FEC_OP_B DESC
                    ) MDP 
                    {$mapJoin} 
                    LIMIT 80 
                    ) 
                    UNION 
                    ( SELECT 0, 'FONE' AS FUENTE, MDP.fec_op AS QNA_AFEC, OPERACION, operacion AS COD_SEP, CURP, MDP.CVEPRE, '' AS NS, '' AS CCT, RFC, 
                    PRIMER_AP AS AP_PAT, SEGUNDO_AP AS AP_MAT, NOMBRE, FEC_INI, FEC_FIN, MDP.CPZA 
                    FROM {$tablaMDP} MDP 
                    WHERE MDP.{$campo} LIKE ? AND (MDP.FEC_FIN < NOW() OR MDP.FECHA_BAJA != '') AND OPERACION NOT IN ('08-98','08','8') 
                    ORDER BY CPZA, FEC_OP_B DESC LIMIT 80 
                    ) 
                ) A ORDER BY CPZA, fec_ini DESC LIMIT 60
            ";

            DB::statement($sqlFone, ["%{$dato}%", "%{$dato}%"]);
        }

        // 6. Consultar los resultados consolidados de `consultagral`
        $resultados = DB::table('consultagral')->orderBy('id', 'asc')->get();

        return response()->json([
            'data' => $resultados
        ]);
    }
}