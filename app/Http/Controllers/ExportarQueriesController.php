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

class ExportarQueriesController extends Controller{
    
    public function exportarExcel(Request $request){
        ini_set('memory_limit', '-1');

        try{
            $nombre_archivo = $request->get('nombre_archivo');

            $query = $request->get('query');

            if (preg_match('/(DELETE|DROP|TRUNCATE|ALTER|UPDATE)/',strtoupper($query)) != 0){
                return response()->json(['error' => 'Solo se permiten SELECTs'], HttpResponse::HTTP_CONFLICT);
            }

            $resultado = DB::select($query);

            $datos = $resultado;

            $columnas = array_keys(collect($datos[0])->toArray());

            //var_dump($columnas);
            //return response()->json(['data' => '------'], HttpResponse::HTTP_OK);

            Excel::create($nombre_archivo, function($excel) use ($datos,$columnas){
                $excel->sheet('Datos', function($sheet) use ($datos,$columnas){
                    $sheet->row(1, $columnas);
                    
                    for($i = 0; $i < count($datos); $i++){
                        $datos_linea = collect($datos[$i])->toArray();
                        $sheet->appendRow($datos_linea);
                    }
                });
            })->export('xls');
        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage(),'line'=>$e->getLine()], HttpResponse::HTTP_CONFLICT);
        }
    }
}