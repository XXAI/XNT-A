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

class DividirXMLController extends Controller{
    
    public function dividirXML(Request $request){
        try{
            //$ruta_principal = env('PATH_DIVIDIR_XMLS'); //'C:/pruebas/';
            $ruta_principal = storage_path().'/division_xmls/';
            $tabla_nomina = $request->get('nombre_tabla');

            /*
                La división de las carpetas se puede especificar como:
                    C-N-X: En carpetas por Clues -> Nombre de Nomina -> archivos Xml
                    N-C-X: En carpetas por Nombre de Nomina -> Clues -> archivos Xml 
                    C-X:   En carpetas por Clues -> archivos Xml
                    N-X:   En carpetas por Nombre de Nomina -> archivos Xml
            */
            $orden_carpetas = $request->get('orden_carpetas');

            $ruta_nomina = $ruta_principal.'/'.$tabla_nomina.'/';
            mkdir($ruta_nomina, 0777, true);
            
            $archivo_zip = $request->file('archivo_zip');
            $upload_success = $archivo_zip->move($ruta_nomina, $tabla_nomina."-xmls.zip");
            $zip = $ruta_nomina . $tabla_nomina."-xmls.zip";

            chdir($ruta_nomina);
            //exec("zip -P sat2015 -r ".$zipname." \"".$carpeta."/\"");
            exec("unzip ".$tabla_nomina."-xmls.zip");

            $rfc_clues = \DB::table($tabla_nomina)->selectRaw("CONCAT(NOMBRE_NOMINA,'_',RFC) as LLAVE, CLUES")->pluck('CLUES','LLAVE');

            //$files = glob( $ruta_principal.'xmls/*' . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
            $nominas = glob( $ruta_nomina.'xmls/*' . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
            
            $Carpeta = '';
            $contador = 0;

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
                        echo "Error: RFC no encontrado: " . $rfc; die;
                    }
                }
            }

            echo "Carpetas: " . $orden_carpetas . " | Total archivos: " . $contador;
        }catch(\Exception $e){
            echo $e->getMessage() . '<br> ' . $e->getLine() . '<br> ' . $Carpeta . '<br> ' . $nombre_archivo;
        }

    }
}