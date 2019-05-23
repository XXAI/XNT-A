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

class DatosTimbradoController extends Controller
{
    /**
     * Parsea la nomina enviada por el cliente, y devuelve un archivo excel con diferentes pestañas
     */
    public function cargarArchivo(Request $request){
        ini_set('memory_limit', '-1');

        try{
            $datos_carga = [];

            $archivo_csv = $request->file('archivo_csv');
            if ($archivo_csv && $archivo_csv->isValid()){
                $datos_carga = $this->cargarDatos($archivo_csv);

                if(!$datos_carga['status']){
                    return response()->json($datos_carga_dbf, HttpResponse::HTTP_CONFLICT);
                }
            }else{
                return response()->json(['error'=>'Archivo DBF no valido'], HttpResponse::HTTP_CONFLICT);
            }
            
            return response()->json(['data' => 'onegaishimasu'], HttpResponse::HTTP_OK);
            //return self::generarExcel($identificadores_nomina['clave'],$datos_archivo);
        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage(),'line'=>$e->getLine()], HttpResponse::HTTP_CONFLICT);
        }
    }

    public function cargarDatos($archivo){
        //ini_set('memory_limit', '-1');
        try{
            $finfo = finfo_open(FILEINFO_MIME_TYPE); 
            
            $type = finfo_file($finfo, $archivo); 

            $nombreArchivo = 'archivo_csv';
            
            $numeroRegistros = '';

            if($type == "text/plain"){//Si el Mime coincide con CSV
                $destinationPath = storage_path().'/archivoscsv_timbrado/';
                $upload_success = $archivo->move($destinationPath, $nombreArchivo.".csv");
                $csv = $destinationPath . $nombreArchivo.".csv";

                DB::connection()->getPdo()->beginTransaction();

                //informacion_sat
                $query = sprintf("
                    LOAD DATA local INFILE '%s' 
                    INTO TABLE nomina.sat_2016 
                    CHARACTER SET utf8 
                    FIELDS TERMINATED BY '|' 
                    OPTIONALLY ENCLOSED BY '\"' 
                    ESCAPED BY '\"' 
                    LINES TERMINATED BY '\\n' 
                    IGNORE 1 LINES
                    ", addslashes($csv));
                DB::connection()->getPdo()->exec($query);
                DB::connection()->getPdo()->commit();

                //$registros_tabla = \DB::table('nomina.informacion_salud')->count();
                $registros_tabla = 10000;
                
                return ['status'=>true, 'total_regitros_tabla'=>$registros_tabla];
            }
        }catch(\Exception $e){
            DB::connection()->getPdo()->rollback();
            return ['status'=>false, 'error' => $e->getMessage(), 'linea'=>$e->getLine()];
        }
    }
}