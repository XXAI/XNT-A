<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\Input;
use \Excel;
use \Validator,\Hash, \Response, \DB;
use TRA, DBF;

use XBase\Table;

class ImportarController extends Controller
{
    /**
     * Parsea la nomina enviada por el cliente, y devuelve un archivo excel con diferentes pestañas
     */
    public function parseNomina(Request $request){
        ini_set('memory_limit', '-1');

        try{
            
            $identificadores = [
                'lisandro' => [
                    'clave'=>'LISANDRO',
                    'usa_dbf'=>true,
                    'columnas_dbf'=>[
                        'rfc'=>'rfc',
                        'programa'=>'programa',
                        'acreditado'=>'sinfuente',
                        'nom_prod'=>'nomprod'
                    ],
                    'identificadores_acreditado'=>[
                        'ACREASF'=>'ACREDITADO_FEDERAL', 
                        'ACREASE'=>'ACREDITADO_ESTATAL',
                        'NOACRED'=>'NO_ACREDITADO',
                    ]
                ],
                'walter' =>   [
                    'clave'=>'WALTER', 
                    'usa_dbf'=>true,
                    'columnas_dbf'=>[
                        'rfc'=>'rfc',
                        'programa'=>'programa',
                        'acreditado'=>'ban',      
                        'nom_prod'=>'nomprod'
                    ],
                    'identificadores_acreditado'=>[
                        'RFACRE'=>'ACREDITADO',
                        'ATMPPAC' => 'ACREDITADO',
                        'ATMPPNA'=> 'NO_ACREDITADO',
                        'FORUNIAC' => 'ACREDITADO',
                        'FORUNINA' => 'NO_ACREDITADO',
                        'REFASENA' => 'NO_ACREDITADO'
                    ]
                ],
                'bety' =>   [
                    'clave'=>'BETY',  
                    'usa_dbf'=>true,
                    'columnas_dbf'=>[
                        'rfc'=>'rfc',
                        'programa'=>'programa',
                        'acreditado'=>'num',      
                        'nom_prod'=>'nomprod'
                    ],
                    'identificadores_acreditado'=>[]
                ],
                'homologados' =>   [
                    'clave'=>'HOMOLOGADOS', 
                    'usa_dbf'=>false,
                    'titulo' =>'HOMOLOGADOS'
                ],
                'mandos_medios' =>   [
                    'clave'=>'MANDOS_MEDIOS', 
                    'usa_dbf'=>false,
                    'titulo' =>'MANDOS MEDIOS'
                ],
                'pac' =>   [
                    'clave'=>'PAC', 
                    'usa_dbf'=>false,
                    'titulo' =>'PAC'
                ],
                'san_agustin' =>   [
                    'clave'=>'SAN_AGUSTIN', 
                    'usa_dbf'=>false,
                    'titulo' =>'UNIDAD DE ATENCIÓN A LA SALUD MENTAL SAN AGUSTÍN'
                ],
                'caravanas' =>   [
                    'clave'=>'CARAVANAS', 
                    'usa_dbf'=>false,
                    'titulo' =>'CARAVANAS'
                ]
            ];

            //return response()->json(['error' => "llega aqui",'line'=>'x'], 500);
            
            if(!isset($identificadores[$request->input('identificador_nomina')])){
                return response()->json(['error'=>'Identificador de nomina no encontrado'], HttpResponse::HTTP_CONFLICT);
            }
            
            $identificadores_nomina = $identificadores[$request->input('identificador_nomina')];

            $datos_archivo = [
                'titulo' => ($identificadores_nomina['usa_dbf'])?'':$identificadores_nomina['titulo'],
                'tipo_anio' => $request->input('tipo_anio'),
                'quincena' => $request->input('quincena'),
                'anio' => $request->input('anio'),
                'nombre_archivo' => $request->input('nombre_archivo'),
                'unidad_responsable' => $request->input('unidad_responsable')
            ];

            $datos_carga_dbf = [];
            $datos_carga_tra = [];

            \App\DBF::where('nomina',$identificadores_nomina['clave'])->delete();
            if($identificadores_nomina['usa_dbf']){
                $archivo_dbf = $request->file('archivo_dbf');
                if ($archivo_dbf && $archivo_dbf->isValid()){
                    $datos_carga_dbf = $this->cargarDatosDBF($archivo_dbf,$identificadores_nomina['columnas_dbf'],$identificadores_nomina['clave'],$identificadores_nomina['identificadores_acreditado']);

                    if(!$datos_carga_dbf['status']){
                        return response()->json($datos_carga_dbf, HttpResponse::HTTP_CONFLICT);
                    }
                }else{
                    return response()->json(['error'=>'Archivo DBF no valido'], HttpResponse::HTTP_CONFLICT);
                }
            }

            \App\TRA::where('nomina',$identificadores_nomina['clave'])->delete();
            $input_archivos_tra = ['archivo_tra','archivo_tra_ex'];
            for($i = 0; $i < count($input_archivos_tra); $i++){
                $archivo_tra = $request->file($input_archivos_tra[$i]);
                if($archivo_tra){
                    if ($archivo_tra->isValid()){
                        $tipo_nomina = 'ordinaria';
                        if($input_archivos_tra[$i] == 'archivo_tra_ex'){
                            $tipo_nomina = 'extraordinaria';
                        }
                        $datos_carga_tra[$input_archivos_tra[$i]] = $this->cargarDatosTRA($archivo_tra,$identificadores_nomina['clave'],$tipo_nomina);
    
                        if(!$datos_carga_tra[$input_archivos_tra[$i]]['status']){
                            return response()->json($datos_carga_tra, HttpResponse::HTTP_CONFLICT);
                        }
                    }else{
                        return response()->json(['error'=>'Archivo TRA no valido'], HttpResponse::HTTP_CONFLICT);
                    }
                }
            }
            
            //return response()->json(['data' => 'onegaishimasu'], HttpResponse::HTTP_OK);
            return self::generarExcel($identificadores_nomina['clave'],$datos_archivo);
        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage(),'line'=>$e->getLine()], HttpResponse::HTTP_CONFLICT);
        }
    }

    public function cargarDatosDBF($archivo,$columnas_dbf,$identificador_nomina,$identificadores_acreditado){
        //ini_set('memory_limit', '-1');
        try{
            $datos_dbf = [];

            $table = new Table($archivo,array_values($columnas_dbf),'utf-8');

            DB::beginTransaction();
            while($record = $table->nextRecord()){
                $registro_dbf = [];
                foreach ($columnas_dbf as $key => $value) {
                    if($key == 'acreditado'){
                        if(isset($identificadores_acreditado[$record->getString($value)])){
                            $registro_dbf[$key] = $identificadores_acreditado[$record->getString($value)];
                        }else{
                            $registro_dbf[$key] = '';
                        }
                    }else{
                        $registro_dbf[$key] = $record->getString($value);
                    }
                }
                $registro_dbf['nomina'] = $identificador_nomina;

                $datos_dbf[] = $registro_dbf;
            }

            \App\DBF::insert($datos_dbf);

            DB::commit();

            $registros_tabla = \App\DBF::where('nomina',$identificador_nomina)->count();

            return ['status'=>true, 'total_reistros_dbf'=>$table->getRecordCount(), 'total_regitros_tabla'=>$registros_tabla];
        }catch(\Exception $e){
            DB::rollback();
            return ['status'=>false, 'error' => $e->getMessage(), 'linea'=>$e->getLine()];
        }
    }

    public function cargarDatosTRA($archivo,$identificador_nomina,$tipo_nomina){
        try{
            $finfo = finfo_open(FILEINFO_MIME_TYPE); 
            
            $type = finfo_file($finfo, $archivo); 

            $fechahora = date("d").date("m").date("Y").date("H").date("i").date("s");

            $nombreArchivo = 'ARCHIVOTRA'.$identificador_nomina.$fechahora;

            $idInsertado ='';
            $numeroRegistros = '';

            if($type == "text/plain"){//Si el Mime coincide con CSV
                $destinationPath = storage_path().'/archivostra/';
                $upload_success = $archivo->move($destinationPath, $nombreArchivo.".tra");
                $tra = $destinationPath . $nombreArchivo.".tra";

                DB::connection()->getPdo()->beginTransaction();

                $query = sprintf("
                    LOAD DATA local INFILE '%s' 
                    INTO TABLE tra 
                    CHARACTER SET utf8 
                    FIELDS TERMINATED BY '|' 
                    OPTIONALLY ENCLOSED BY '\"' 
                    ESCAPED BY '\"' 
                    LINES TERMINATED BY '\\n' 
                    SET nomina='%s', tipo_nomina='%s'
                    ", addslashes($tra), $identificador_nomina, $tipo_nomina);
                DB::connection()->getPdo()->exec($query);
                DB::connection()->getPdo()->commit();

                $registros_tabla = \App\TRA::where('nomina',$identificador_nomina)->count();

                return ['status'=>true, 'total_regitros_tabla'=>$registros_tabla];
            }
        }catch(\Exception $e){
            DB::connection()->getPdo()->rollback();
            return ['status'=>false, 'error' => $e->getMessage(), 'linea'=>$e->getLine()];
        }
    }

    public function generarExcel($identificador_nomina,$datos_archivo){
        Excel::create($datos_archivo['nombre_archivo'], function($excel) use ($identificador_nomina,$datos_archivo){
            $estilo = [
                'font' => [
                    'size' => 11,
                    'name' => 'Courier New'
                ]
            ];

            $datos_generados = [];

            $excel->getDefaultStyle()->applyFromArray($estilo);

            $programas = DB::select("SELECT programa, acreditado, count(rfc) 
                                    FROM nomina_reportes.dbf 
                                    where nomina = '$identificador_nomina' group by programa, acreditado;"); //nom_prod LIKE 'PRDO%' and

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
            }else{
                $datos_raw = DB::select("SELECT tipo_concepto, cl, partida, sum(importe) as importe, pa, tipo_nomina
                                        FROM nomina_reportes.concentrado 
                                        WHERE nomina = '$identificador_nomina'
                                        GROUP BY tipo_concepto, cl, pa, partida, tipo_nomina
                                        ORDER BY tipo_concepto asc;");

                $totales_raw = DB::select("SELECT tipo_concepto, cl, partida, sum(importe) as importe, pa
                                    FROM nomina_reportes.concentrado 
                                    WHERE  nomina = '$identificador_nomina'
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

                $nombre_programa = $datos_archivo['titulo'];

                $datos_generados[$nombre_programa] = $datos_nomina;
            }

            foreach ($datos_generados as $nombre_programa => $datos_nomina) {
                $excel->sheet(substr($nombre_programa,0,29), function($sheet) use ($datos_nomina,$nombre_programa,$datos_archivo){
                    $sheet->mergeCells('A1:U1');
                    $sheet->mergeCells('A2:U2');
                    $sheet->mergeCells('A3:U3');

                    $sheet->mergeCells('A4:G4');
                    $sheet->mergeCells('H4:N4');
                    $sheet->mergeCells('O4:U4');

                    $sheet->mergeCells('A5:D5');
                    $sheet->mergeCells('E5:G5');
                    $sheet->mergeCells('H5:K5');
                    $sheet->mergeCells('L5:N5');
                    $sheet->mergeCells('O5:R5');
                    $sheet->mergeCells('S5:U5');
                    
                    $sheet->row(1, array('RESUMEN DE  NOMINA CORRESPONDIENTE A LA QUINCENA '.$datos_archivo['tipo_anio'].' '.$datos_archivo['quincena'].'/'.$datos_archivo['anio']));
                    $sheet->row(2, array('UNIDAD RESPONSABLE: '.$datos_archivo['unidad_responsable'].'      PROGRAMA:  '.$nombre_programa));
                    
                    $sheet->row(4, array('ORDINARIO','','','','','','','EXTRAORDINARIO','','','','','','','TOTAL'));
                    $sheet->row(5, array('PERCEPCIONES','','','','DEDUCCIONES','','','PERCEPCIONES','','','','DEDUCCIONES','','','PERCEPCIONES','','','','DEDUCCIONES'));
                    $sheet->row(6, array('CL','PTDA','IMPORTE','PA','CL','IMPORTE','PA','CL','PTDA','IMPORTE','PA','CL','IMPORTE','PA','CL','PTDA','IMPORTE','PA','CL','IMPORTE','PA'));
                    
                    $sheet->cells("A1:U6", function($cells) {
                        $cells->setAlignment('center');
                    });
                    
                    //$linea = 6;
                    $lineas_maximas = 0;
                    if( count($datos_nomina['TOTAL']['DEDUCCIONES']) > count($datos_nomina['TOTAL']['PERCEPCIONES'])){
                        $lineas_maximas = count($datos_nomina['TOTAL']['DEDUCCIONES']);
                    }else{
                        $lineas_maximas = count($datos_nomina['TOTAL']['PERCEPCIONES']);
                    }
                    
                    for($i = 0; $i <= $lineas_maximas; $i++){
                        $linea_datos = [];
                        if(isset($datos_nomina['ORDINARIO']['PERCEPCIONES'][$i])){
                            array_push($linea_datos,$datos_nomina['ORDINARIO']['PERCEPCIONES'][$i]['CL'],$datos_nomina['ORDINARIO']['PERCEPCIONES'][$i]['PTDA'],$datos_nomina['ORDINARIO']['PERCEPCIONES'][$i]['IMPORTE'],$datos_nomina['ORDINARIO']['PERCEPCIONES'][$i]['PA']);
                        }else{
                            array_push($linea_datos,'','','','');
                        }

                        if(isset($datos_nomina['ORDINARIO']['DEDUCCIONES'][$i])){
                            array_push($linea_datos,$datos_nomina['ORDINARIO']['DEDUCCIONES'][$i]['CL'],$datos_nomina['ORDINARIO']['DEDUCCIONES'][$i]['IMPORTE'],$datos_nomina['ORDINARIO']['DEDUCCIONES'][$i]['PA']);
                        }else{
                            array_push($linea_datos,'','','');
                        }

                        if(isset($datos_nomina['EXTRAORDINARIO']['PERCEPCIONES'][$i])){
                            array_push($linea_datos,$datos_nomina['EXTRAORDINARIO']['PERCEPCIONES'][$i]['CL'],$datos_nomina['EXTRAORDINARIO']['PERCEPCIONES'][$i]['PTDA'],$datos_nomina['EXTRAORDINARIO']['PERCEPCIONES'][$i]['IMPORTE'],$datos_nomina['EXTRAORDINARIO']['PERCEPCIONES'][$i]['PA']);
                        }else{
                            array_push($linea_datos,'','','','');
                        }

                        if(isset($datos_nomina['EXTRAORDINARIO']['DEDUCCIONES'][$i])){
                            array_push($linea_datos,$datos_nomina['EXTRAORDINARIO']['DEDUCCIONES'][$i]['CL'],$datos_nomina['EXTRAORDINARIO']['DEDUCCIONES'][$i]['IMPORTE'],$datos_nomina['EXTRAORDINARIO']['DEDUCCIONES'][$i]['PA']);
                        }else{
                            array_push($linea_datos,'','','');
                        }

                        if(isset($datos_nomina['TOTAL']['PERCEPCIONES'][$i])){
                            array_push($linea_datos,$datos_nomina['TOTAL']['PERCEPCIONES'][$i]['CL'],$datos_nomina['TOTAL']['PERCEPCIONES'][$i]['PTDA'],$datos_nomina['TOTAL']['PERCEPCIONES'][$i]['IMPORTE'],$datos_nomina['TOTAL']['PERCEPCIONES'][$i]['PA']);
                        }else{
                            array_push($linea_datos,'','','','');
                        }

                        if(isset($datos_nomina['TOTAL']['DEDUCCIONES'][$i])){
                            array_push($linea_datos,$datos_nomina['TOTAL']['DEDUCCIONES'][$i]['CL'],$datos_nomina['TOTAL']['DEDUCCIONES'][$i]['IMPORTE'],$datos_nomina['TOTAL']['DEDUCCIONES'][$i]['PA']);
                        }else{
                            array_push($linea_datos,'','','');
                        }

                        $sheet->row(7+$i,$linea_datos);
                    }

                    $contador_filas = $i + 8;
                    
                    $sheet->cells("A7:U".$contador_filas, function($cells) {
                        $cells->setAlignment('center');
                    });

                    $sheet->mergeCells('A'.$contador_filas.':B'.$contador_filas);
                    $sheet->row($contador_filas, array('TOTALES','','=SUM(C7:C'.($contador_filas-1).')','','','=SUM(F7:F'.($contador_filas-1).')','','','','=SUM(J7:J'.($contador_filas-1).')','','','=SUM(M7:M'.($contador_filas-1).')','','','','=SUM(Q7:Q'.($contador_filas-1).')','','','=SUM(T7:T'.($contador_filas-1).')'));

                    $contador_filas++;
                    $sheet->mergeCells('A'.$contador_filas.':B'.$contador_filas);
                    $sheet->row($contador_filas, array('NETOS','','','','','=C'.($contador_filas-1).'-F'.($contador_filas-1),'','','','','','','=J'.($contador_filas-1).'-M'.($contador_filas-1),'','','','','','','=Q'.($contador_filas-1).'-T'.($contador_filas-1)));
                    
                    $sheet->cells("C7:C".$contador_filas, function($cells) { $cells->setAlignment('right'); });
                    $sheet->cells("F7:F".$contador_filas, function($cells) { $cells->setAlignment('right'); });
                    $sheet->cells("J7:J".$contador_filas, function($cells) { $cells->setAlignment('right'); });
                    $sheet->cells("M7:M".$contador_filas, function($cells) { $cells->setAlignment('right'); });
                    $sheet->cells("Q7:Q".$contador_filas, function($cells) { $cells->setAlignment('right'); });
                    $sheet->cells("T7:T".$contador_filas, function($cells) { $cells->setAlignment('right'); });

                    $sheet->setColumnFormat(array(
                        "C7:C".$contador_filas => '#,##0.00',
                        "F7:F".$contador_filas => '#,##0.00',
                        "J7:J".$contador_filas => '#,##0.00',
                        "M7:M".$contador_filas => '#,##0.00',
                        "Q7:Q".$contador_filas => '#,##0.00',
                        "T7:T".$contador_filas => '#,##0.00'
                    ));

                    $bordes = [
                        'borders' =>[
                            'top' => [
                                'style' => \PHPExcel_Style_Border::BORDER_DASHED
                            ],
                            'bottom' => [
                                'style' => \PHPExcel_Style_Border::BORDER_DASHED
                            ]
                        ]
                    ];

                    $sheet->getStyle('A4:U6')->applyFromArray($bordes);
                    $sheet->getStyle('A'.($contador_filas-1).':U'.$contador_filas)->applyFromArray($bordes);
                    //$sheet->setBorder("A1:K$contador_filas", 'thin');
                });
                //$excel->getActiveSheet()->setAutoSize(false);
                //$excel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
                
                //Columna Clave
                //$excel->getActiveSheet()->getColumnDimension('B')->setAutoSize(false);
                //$excel->getActiveSheet()->getColumnDimension('B')->setWidth(18);
                
                $excel->getActiveSheet()->getPageSetup()->setPaperSize(\PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER);
                $excel->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

                //$excel->getActiveSheet()->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(7,9);

                $excel->getActiveSheet()->getPageSetup()->setFitToPage(true);
                $excel->getActiveSheet()->getPageSetup()->setFitToWidth(1);
                $excel->getActiveSheet()->getPageSetup()->setFitToHeight(0);
                
                $excel->getActiveSheet()->getPageMargins()->setTop(0.3543307);
                $excel->getActiveSheet()->getPageMargins()->setBottom(0.3543307);

                $excel->getActiveSheet()->getPageMargins()->setRight(0.1968504);
                $excel->getActiveSheet()->getPageMargins()->setLeft(0.2755906);
                
            }

            //return response()->json(['data' => $datos_generados], HttpResponse::HTTP_OK);
        })->export('xls');
    }
}