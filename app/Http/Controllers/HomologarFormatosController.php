<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\Input;
use \Excel;
use \Validator,\Hash, \Response, \DB;

use XBase\Table;

class HomologarFormatosController extends Controller{

    /**
     * Se carga un archivo DBF para homologar al formato usado para generar los layouts
     */
    public function cargarDBF(Request $request){
        ini_set('memory_limit', '-1');

        try{
            
            $identificadores = [
                'FEDERAL' => [
                    'clave'=>'FEDERAL',
                    'columnas_dbf'=>[
                        'NOMBRE_NOMINA'     => 'nomprod',
                        'RFC'               => 'rfc',
                        'CURP'              => 'curp',
                        'NOMBRE'            => ['ACTION-CONCAT'=>['apepat',' ','apemat',' ','nombres']],
                        'TURNO'             => '',
                        'FECHA_INGRESO'     => 'fingreso',
                        'PUESTO'            => 'puestodir',
                        'CLUES'             => 'clues',
                        'NUM_EMP'           => 'num',
                        'NSS'               => 'nss',
                        'ORIGEN_RECURSO'    => 'financiami',
                        'PARTE_ESTATAL'     => '',
                        'P0200'             => 'porpen01',
                        'P0700'             => 'porpen05',
                        'P24BA'             => 'importe',
                        'P24GA'             => 'aga55',
                        'P30AR'             => 'liquido',
                        'P30BR'             => 'total',
                        'P30MR'             => '',
                        'P32PD'             => '',
                        'P32PV'             => '',
                        'P32VD'             => '',
                        'P06CG'             => '',
                        'P37TP'             => '',
                        'P4200'             => '',
                        'P55AG'             => '',
                        'P45AL'             => '',
                        'P56MD'             => '',
                        'P56EN'             => '',
                        'P56TS'             => '',
                        'P56AF'             => '',
                        'P56ON'             => '',
                        'P56PS'             => '',
                        'P56QM'             => '',
                        'P56OD'             => '',
                        'P57LM'             => '',
                        'P59DT'             => '',
                        'P69AN'             => '',
                        'P69TR'             => '',
                        'P75AP'             => '',
                        'P73DM'             => '',
                        'P73DR'             => '',
                        'PAX00'             => '',
                        'PE400'             => '',
                        'P19TE'             => '',
                        'P3800'             => '',
                        'P4400'             => '',
                        'P4600'             => '',
                        'P37MF'             => '',
                        'PER_GRAVADA'       => '',
                        'P24GG'             => '',
                        'P32VV'             => '',
                        'P45AA'             => '',
                        'P37TT'             => '',
                        'P57LL'             => '',
                        'P59TT'             => '',
                        'P73MM'             => '',
                        'P73RR'             => '',
                        'PER_NOGRAVA'       => '',
                        'TOT_PERCEPCION'    => 'isr',
                        'OTROS_PAGOS'       => '',
                        'D0100'             => '',
                        'D0200'             => '',
                        'D0400'             => '',
                        'D0300'             => '',
                        'D0800'             => '',
                        'D1000'             => '',
                        'D1700'             => '',
                        'D1800'             => '',
                        'D2024'             => '',
                        'D21FA'             => '',
                        'D2121'             => '',
                        'D2900'             => '',
                        'D3400'             => '',
                        'D4600'             => '',
                        'D46CA'             => '',
                        'D46CR'             => '',
                        'D5000'             => '',
                        'D5100'             => '',
                        'D5600'             => '',
                        'D5700'             => '',
                        'D5800'             => '',
                        'D6200'             => '',
                        'D6400'             => '',
                        'D6500'             => '',
                        'D7000'             => '',
                        'D7700'             => '',
                        'DAS10'             => '',
                        'DAS20'             => '',
                        'D0101'             => '',
                        'OTRAS_DEDUCCIONES' => '',
                        'TOT_DEDUCCION'     => ['ACTION-SUMA'=>['ss02','sr02','si02']],
                        'LIQUIDO'           => '',
                        'TIPO_NOMINA'       => '',
                        'DEL'               => '',
                        'AL'                => '',
                        'FECHA_PAGO'        => '',
                        'QNA'               => '',
                        'PERIODICIDAD'      => '',
                        //'mmFolio'           => '', //Autoincrement
                        //'RAMA'              => '', //RAMA MEDICA
                    ]
                ]
            ];

            
            
            if(!isset($identificadores[$request->input('identificador_nomina')])){
                return response()->json(['error'=>'Identificador de nomina no encontrado'], HttpResponse::HTTP_CONFLICT);
            }
            
            $identificadores_nomina = $identificadores[$request->input('identificador_nomina')];
            
            //var_dump($this->array_values_recursive($identificadores_nomina['columnas_dbf']));

            //return response()->json(['prueba'], HttpResponse::HTTP_CONFLICT);

            $datos_carga_dbf = [];

            $archivo_dbf = $request->file('archivo_dbf');

            //var_dump($archivo_dbf);

            if ($archivo_dbf && $archivo_dbf->isValid()){
                $datos_carga_dbf = $this->cargarDatosDBF($archivo_dbf,$identificadores_nomina);

                if(!$datos_carga_dbf['status']){
                    return response()->json($datos_carga_dbf, HttpResponse::HTTP_CONFLICT);
                }
            }else{
                return response()->json(['error'=>'Archivo DBF no valido'], HttpResponse::HTTP_CONFLICT);
            }

            //return response()->json(['data' => $datos_carga_dbf], HttpResponse::HTTP_OK);
            return self::generarExcelHomologado($datos_carga_dbf['reistros_dbf'],$identificadores_nomina,'Archivo Prueba');
        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage(),'line'=>$e->getLine()], HttpResponse::HTTP_CONFLICT);
        }
    }

    public function cargarDatosDBF($archivo,$identificadores){
        try{
            $datos_dbf = [];
            $columnas_dbf = $identificadores['columnas_dbf'];

            $campos = array_values($identificadores['columnas_dbf']);
            $columnas_dbf = [];
            foreach ($campos as $value) {
                if(is_array($value)){
                    foreach ($value as $val) {
                        if(is_array($val)){
                            foreach ($val as $v) {
                                if(trim($v) != ''){
                                    $columnas_dbf[] = $v;
                                }
                            }
                        }else{
                            if(trim($val) != ''){
                                $columnas_dbf[] = $val;
                            }
                        }
                    }
                }else{
                    if(trim($value) != ''){
                        $columnas_dbf[] = $value;
                    }
                }
            }
            //var_dump($columnas_dbf);

            $table = new Table($archivo,$columnas_dbf);
            
            //DB::beginTransaction();
            //$incremento = 1;
            while($record = $table->nextRecord()){
                $registro_dbf = [];
                
                foreach ($identificadores['columnas_dbf'] as $campo_homologado => $campo_dbf) {
                    if(!is_array($campo_dbf) && trim($campo_dbf) != ''){
                        $registro_dbf[$campo_homologado] = $record->getString($campo_dbf);
                    }elseif(is_array($campo_dbf)){
                        $accion = array_keys($campo_dbf);
                        switch ($accion[0]) {
                            case 'ACTION-CONCAT':
                                $valor = '';
                                foreach ($campo_dbf[$accion[0]] as $campo) {
                                    if($campo == ' '){
                                        $valor .= ' ';
                                    }else{
                                        //$valor .= iconv('ISO-8859-1','utf-8',$record->getString($campo));
                                        //$valor .= mb_convert_encoding($record->getString($campo),'utf-8');
                                        $valor .= $record->getString($campo);
                                    }
                                }
                                $registro_dbf[$campo_homologado] = $valor;
                                break;
                            
                            default:
                                # code...
                                break;
                        }
                    }
                }
                //$registro_dbf['mmFolio'] = $incremento;
                //$registro_dbf['RAMA'] = 'RAMA MEDICA';

                //$incremento++;

                $datos_dbf[] = $registro_dbf;
            }
            //\App\DBF::insert($datos_dbf);
            //DB::commit();
            //$registros_tabla = \App\DBF::where('nomina',$identificador_nomina)->count();

            return ['status'=>true, 'reistros_dbf'=>$datos_dbf];
        }catch(\Exception $e){
            //DB::rollback();
            return ['status'=>false, 'error' => $e->getMessage(), 'linea'=>$e->getLine()];
        }
    }

    public function generarExcelHomologado($datos,$identificadores,$nombre_archivo){
        Excel::create($nombre_archivo, function($excel) use ($identificadores,$datos,$nombre_archivo){
            $datos_generados = [];

            /*
            $programas = DB::select("SELECT programa, acreditado, count(rfc) 
                                    FROM nomina_reportes.dbf 
                                    where nomina = '$identificador_nomina' group by programa, acreditado;"); //nom_prod LIKE 'PRDO%' and
            */
            /*
            if(count($programas) > 0){
                foreach($programas as $index => $programa){
                    $datos_raw = DB::select("SELECT tipo_concepto, cl, partida, sum(importe) as importe, pa, tipo_nomina
                                        FROM nomina_reportes.concentrado 
                                        WHERE  programa = '$programa->programa' and acreditado = '$programa->acreditado' and nomina = '$identificador_nomina'
                                        GROUP BY tipo_concepto, cl, pa, partida, tipo_nomina
                                        ORDER BY tipo_concepto asc;");
    
                    if(count($datos_raw) <= 0){
                        continue;
                    }
    
                    $totales_raw = DB::select("SELECT tipo_concepto, cl, partida, sum(importe) as importe, pa
                                        FROM nomina_reportes.concentrado 
                                        WHERE  programa = '$programa->programa' and acreditado = '$programa->acreditado' and nomina = '$identificador_nomina'
                                        GROUP BY tipo_concepto, cl, pa, partida
                                        ORDER BY tipo_concepto asc;");
                    
                    $datos_nomina = [
                        'ORDINARIO' => [
                            'PERCEPCIONES'=>[],
                            'DEDUCCIONES'=>[]
                        ],
                        'EXTRAORDINARIO' => [
                            'PERCEPCIONES'=>[],
                            'DEDUCCIONES'=>[]
                        ],
                        'TOTAL' => [
                            'PERCEPCIONES'=>[],
                            'DEDUCCIONES'=>[]
                        ]
                    ];
    
                    foreach ($datos_raw as $dato) {
                        if($dato->tipo_nomina == 'ordinaria'){
                            $tipo_nomina = 'ORDINARIO';
                        }else if($dato->tipo_nomina == 'extraordinaria'){
                            $tipo_nomina = 'EXTRAORDINARIO';
                        }else{
                            $tipo_nomina = $dato->tipo_nomina;
                        }
                        if($dato->tipo_concepto == 1){
                            $datos_nomina[$tipo_nomina]['PERCEPCIONES'][] = ['CL'=> $dato->cl, 'PTDA'=> $dato->partida, 'IMPORTE'=> $dato->importe, 'PA' => $dato->pa];
                        }else{
                            $datos_nomina[$tipo_nomina]['DEDUCCIONES'][] = ['CL'=> $dato->cl, 'IMPORTE'=> $dato->importe, 'PA' => $dato->pa];
                        }
                    }
    
                    foreach ($totales_raw as $dato) {
                        if($dato->tipo_concepto == 1){
                            $datos_nomina['TOTAL']['PERCEPCIONES'][] = ['CL'=> $dato->cl, 'PTDA'=> $dato->partida, 'IMPORTE'=> $dato->importe, 'PA' => $dato->pa];
                        }else{
                            $datos_nomina['TOTAL']['DEDUCCIONES'][] = ['CL'=> $dato->cl, 'IMPORTE'=> $dato->importe, 'PA' => $dato->pa];
                        }
                    }
                    
                    if($programa->acreditado != ''){
                        $nombre_programa = $programa->programa . ' (' . $programa->acreditado . ')';
                    }else{
                        $nombre_programa = $programa->programa;
                    }
                    
                    $datos_generados[$nombre_programa] = $datos_nomina;
                }
            }
            */

            $excel->sheet($nombre_archivo, function($sheet) use ($identificadores,$datos){
                $columnas = array_keys($identificadores['columnas_dbf']);
                
                $sheet->row(1, $columnas);
                
                for($i = 0; $i < count($datos); $i++){
                    $datos_linea = $datos[$i];
                    $sheet->appendRow($datos_linea);
                }
            });
        })->export('xls');
    }
}