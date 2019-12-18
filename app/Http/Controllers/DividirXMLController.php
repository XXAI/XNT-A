<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;

use App\Http\Controllers\Controller;
use App\Http\Requests;
use Illuminate\Support\Facades\Input;
use \Excel;
use \Validator,\Hash, \Response, \DB, \Storage;
use \ZipArchive;

use XBase\Table;

class DividirXMLController extends Controller{
    
    public function dividirXML(Request $request){
        try{
            

            //$ruta_principal = env('PATH_DIVIDIR_XMLS'); //'C:/pruebas/';
            $ruta_principal = storage_path().'/division_xmls/';
            $tabla_nomina = $request->get('nombre_tabla');
            $Carpeta = '';
            $contador = 0;
            $nombre_archivo = '';

            $check = \DB::select('select * from '.$tabla_nomina.' limit 1');
            if(!property_exists($check[0],'archivo_xml')){
                \DB::statement("ALTER TABLE ".$tabla_nomina." ADD COLUMN archivo_xml VARCHAR(255);");
            }

            /*
                La división de las carpetas se puede especificar como:
                    C-N-X: En carpetas por Clues -> Nombre de Nomina -> archivos Xml
                    N-C-X: En carpetas por Nombre de Nomina -> Clues -> archivos Xml 
                    C-X:   En carpetas por Clues -> archivos Xml
                    N-X:   En carpetas por Nombre de Nomina -> archivos Xml
            */
            $orden_carpetas = $request->get('orden_carpetas');

            $ruta_nomina = $ruta_principal.$tabla_nomina.'/';

            if(is_dir($ruta_nomina)){
                $this->delete_files($ruta_nomina);
            }
            mkdir($ruta_nomina, 0777, true);
            
            $archivo_zip = $request->file('archivo_zip');
            $upload_success = $archivo_zip->move($ruta_nomina, $tabla_nomina."-xmls.zip");
            $zip = $ruta_nomina . $tabla_nomina."-xmls.zip";

            chdir($ruta_nomina);
            //exec("zip -P sat2015 -r ".$zipname." \"".$carpeta."/\"");
            exec("unzip ".$tabla_nomina."-xmls.zip");

            $rfc_clues = \DB::table($tabla_nomina)->selectRaw("CONCAT(NOMBRE_NOMINA,'_',RFC) as LLAVE, CLUES")->pluck('CLUES','LLAVE');

            $rfc_encontrado = [];
            //$files = glob( $ruta_principal.'xmls/*' . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
            $nominas = glob( $ruta_nomina.'xmls/*' . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
            
            foreach($nominas as $nomina){
                $nombre_nomina = str_replace( $ruta_nomina.'xmls/','',$nomina);
                $nombre_nomina = str_replace('\\','/',$nombre_nomina);

                $files = glob( $nomina.'*.xml', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned

                foreach( $files as $file ){
                    $nombre_archivo = str_replace( $nomina,'',$file);
                    
                    $xml = new \SimpleXMLElement($file,null,true);
                    $xml_hijos = $xml->children('cfdi',TRUE);
                    $receptor = $xml_hijos->Receptor->attributes();
                    $rfc = $receptor->Rfc . '';
                    
                    $llave = str_replace('/','_',$nombre_nomina) . $rfc;
                    if(isset($rfc_clues[$llave])){
                        $clues = $rfc_clues[$llave];
                        $rfc_encontrado[str_replace('/','|',$nombre_nomina).$rfc] = $nombre_archivo;

                        switch ($orden_carpetas) {
                            case 'C-N-X':
                                $carpeta_clues = $ruta_nomina.'division/' . $clues . '/' . $nombre_nomina;
                                break;
                            case 'N-C-X':
                                $carpeta_clues = $ruta_nomina.'division/' . $nombre_nomina . $clues;
                                break;
                            case 'C-X':
                                $carpeta_clues = $ruta_nomina.'division/' . $clues;
                                break;
                            case 'N-X':
                                $carpeta_clues = $ruta_nomina.'division/' . $nombre_nomina;
                                break;
                            default:
                                echo "Error: No se especificó el orden de las carpetas."; die;
                                break;
                        }
                        
                        if($Carpeta != $carpeta_clues){
                            $Carpeta = $carpeta_clues;
                        }
                        
                        if(!is_dir($Carpeta)){
                            if(!mkdir($Carpeta, 0777, true)) {
                                //die('Failed to create folders...'); exit();
                            }
                        }
                        
                        copy($file, $Carpeta . '/' . $nombre_archivo);
                        //rename($file, $Carpeta . '/' . $nombre_archivo);
                        $contador++;
                    }else{
                        return response()->json(['error' => 'Error: RFC no encontrado ' . $rfc . ' en el archivo: ' . $nombre_archivo], HttpResponse::HTTP_CONFLICT);
                    }
                }
            }
			
			$update_query = "UPDATE " . $tabla_nomina . " SET archivo_xml = NULL;";
			DB::statement($update_query);
			
            if(count($rfc_encontrado)){
                $update_query = "";
                foreach($rfc_encontrado as $llave => $nombre_archivo){
                    $datos_id = explode('|',$llave);
                    $update_query .= "UPDATE " . $tabla_nomina . " SET archivo_xml = '" . $nombre_archivo . "' WHERE NOMBRE_NOMINA = '" . $datos_id[0] . "' AND RFC = '" . $datos_id[1] . "';";
                }
                DB::statement($update_query);
            }

            $listado_archivos = \DB::table($tabla_nomina)->selectRaw("mmFolio, NOMBRE_NOMINA, RFC, archivo_xml")->get();
            
			$filepath = $ruta_nomina.'division/'.$tabla_nomina.'-listado-registros.csv';
			$fh = fopen($filepath,"w");
			
            foreach($listado_archivos as $registro){
                $linea = $registro->mmFolio.','.$registro->NOMBRE_NOMINA.','.$registro->RFC.','.$registro->archivo_xml."\r";
				fwrite($fh,$linea.PHP_EOL);
            }
			fclose($fh);

            $zip = new ZipArchive();
            $zippath = $ruta_nomina; //.'division/';
            $zipname = "XMLs.".$tabla_nomina.".zip";
            
            chdir($zippath . 'division/');
            exec("zip -P ssa2015 -r ".$zipname." ./*");

            //movemos el archivo un directorio arriba
            rename($zippath . 'division/' . $zipname, $zippath . $zipname);
            //eliminamos todos los layouts generados (ya estan en el zip)
            //$this->delete_files($zippath . 'division/');
            //$this->delete_files($zippath . 'xmls/');
            
            header("Content-Type: application/zip");
            header("Content-Disposition: attachment; filename=$zipname");
            header("Content-Length: " . filesize($zippath.$zipname));
            readfile($zippath.$zipname);

            //$this->delete_files($ruta_nomina);
            
            return response()->json(['data' => "Carpetas: " . $orden_carpetas . " | Total archivos: " . $contador], HttpResponse::HTTP_OK);
        }catch(\Exception $e){
            return response()->json(['error' => $e->getMessage(), 'line'=>$e->getLine(),'carpeta'=>$Carpeta,'nombre_archivo'=>$nombre_archivo], HttpResponse::HTTP_CONFLICT);
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
