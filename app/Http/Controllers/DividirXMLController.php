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
            //C:/Users/Harima/Documents/Timbrado Nomina/Seguro Popular/Timbrados QNA01/Archivos Timbrados XML/global
            //C:/Users/Harima/Documents/Timbrado Nomina/Caravanas Cancelacion y Retimbrado/TIMBRADO CARAVANAS/TIMBRADO CORRECTO COMPLETO
            $ruta_principal = 'C:/pruebas/';
            $tabla_nomina = 'QNA01_19_2018_CARAVANAS';

            /*
                La división de las carpetas se puede especificar como:
                    C-N-X: En carpetas por Clues -> Nombre de Nomina -> archivos Xml
                    N-C-X: En carpetas por Nombre de Nomina -> Clues -> archivos Xml 
                    C-X:   En carpetas por Clues -> archivos Xml
                    N-X:   En carpetas por Nombre de Nomina -> archivos Xml
            */
            $orden_carpetas = 'C-X';

            $rfc_clues = \DB::table($tabla_nomina)->selectRaw("CONCAT(NOMBRE_NOMINA,'_',RFC) as LLAVE, CLUES")->pluck('CLUES','LLAVE');

            //$files = glob( $ruta_principal.'xmls/*' . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
            $nominas = glob( $ruta_principal.'xmls/*' . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
            
            $Carpeta = '';
            $contador = 0;

            foreach($nominas as $nomina){
                $nombre_nomina = str_replace( $ruta_principal.'xmls/','',$nomina);
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
                                $carpeta_clues = $ruta_principal.'division/' . $clues . '/' . $nombre_nomina;
                                break;
                            case 'N-C-X':
                                $carpeta_clues = $ruta_principal.'division/' . $nombre_nomina . $clues;
                                break;
                            case 'C-X':
                                $carpeta_clues = $ruta_principal.'division/' . $clues;
                                break;
                            case 'N-X':
                                $carpeta_clues = $ruta_principal.'division/' . $nombre_nomina;
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