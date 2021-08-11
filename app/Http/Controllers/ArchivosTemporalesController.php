<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Hash;
use \Excel;
use \Validator, \Response, \DB, \Storage;
use \ZipArchive;

use XBase\Table;

class ArchivosTemporalesController extends Controller{
    
    public function obtenerListaDeArchivos(Request $request){
        try{
            $datos = [
                'division'  => ['titulo'=>'División de XMLs',   'total_archivos'=>0, 'total_directorios'=>0],
                'csv'       => ['titulo'=>'Archivos CSV',       'total_archivos'=>0, 'total_directorios'=>0],
                'layouts'   => ['titulo'=>'Layouts Generados',  'total_archivos'=>0, 'total_directorios'=>0],
            ];
        
            $listado = [];
            $listado['division'] = glob( storage_path().'/division_xmls/'.'*' . '*', GLOB_MARK );
            $listado['csv'] = glob( public_path().'/scriptSAT/archivos-csv/'.'*' . '*', GLOB_MARK );
            $listado['layouts'] = glob( public_path().'/scriptSAT/archivos-layouts/'.'*' . '*', GLOB_MARK );
        
            foreach($listado as $clave => $lista_archivos){
                foreach($lista_archivos as $archivo){
                    if(is_dir($archivo)){
                        $datos[$clave]['total_directorios']++;
                    }else{
                        $datos[$clave]['total_archivos']++;
                    }
                }
            }

            return response()->json(['data' => $datos], HttpResponse::HTTP_OK);
        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage(), 'line'=>$e->getLine()], HttpResponse::HTTP_CONFLICT);
        }
    }

    public function eliminarArchivosTemporales(Request $request){
        //
        try{
            $tiene_permiso = false;
            $permiso_pass ='$2y$10$TBXkndl1q0bS0eHD62eVhOK5cEO9DHXNDX1HSHBFI4CRbT8OLLu16';
            $parametros = $request->all();

            if(isset($parametros['pass']) && $parametros['pass']){
                if(Hash::check($parametros['pass'],$permiso_pass)){
                    $tiene_permiso = true;
                }
            }

            if(!$tiene_permiso){
                return response()->json(['error' => 'Contraseña no Valida','data'=>Hash::make($parametros['pass'])], HttpResponse::HTTP_CONFLICT);
            }

            $listado = [];
            $listado['division'] = glob( storage_path().'/division_xmls/'.'*' . '*', GLOB_MARK );
            $listado['csv'] = glob( public_path().'/scriptSAT/archivos-csv/'.'*' . '*', GLOB_MARK );
            $listado['layouts'] = glob( public_path().'/scriptSAT/archivos-layouts/'.'*' . '*', GLOB_MARK );

            foreach($listado as $clave => $lista_archivos){
                foreach($lista_archivos as $archivo){
                    $this->delete_files($archivo);
                }
            }
			
            return response()->json(['data' => 'Archivos eliminados'], HttpResponse::HTTP_OK);
            //return response()->json(['data' => "Carpetas: " . $orden_carpetas . " | Total archivos: " . $contador], HttpResponse::HTTP_OK);
        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage(), 'line'=>$e->getLine()], HttpResponse::HTTP_CONFLICT);
        }
    }

    function delete_files($target) {
        if(is_dir($target)){
            $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
    
            foreach( $files as $file ){
                $this->delete_files( $file );      
            }
            rmdir( $target );
        } elseif(is_file($target)) {
            unlink( $target );  
        }
    }
}
