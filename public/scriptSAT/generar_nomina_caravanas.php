<?php 
require_once("Connections/connSAT.php");
require_once 'funciones_sat3.3v2_Caravanas.php';
set_time_limit(0);


$Dias = $_POST['dias'];
$Periodicidad = $_POST['periodicidad'];
$TablaName = $_POST['nombre_nomina'];
$TipoNomina = "01";
$Adenda = $_POST['adenda'];

echo "Recibida información del formulario.<br>\n";

$archivo = $_FILES['archivo_csv'];

$nombre_archivo = str_replace('.csv','',$archivo['name']);
       
if($nombre_archivo != $TablaName){
    echo 'Error: el nombre del archivo no corresponde con el nombre de la nomina<br>';
    die;
}
echo "Nombre de archivo validado.<br>\n";

$query_srcSQL = "DROP TABLE IF EXISTS $TablaName";

$srcSQL = $mysqli->query($query_srcSQL) or die($mysqli->error.__LINE__);

echo "Eliminando tabla si existe.<br>\n";

//////
$query_srcSQL = "CREATE TABLE `$TablaName` (
    `NOMBRE_NOMINA` text DEFAULT NULL,
    `RFC` text DEFAULT NULL,
    `CURP` text DEFAULT NULL,
    `NOMBRE` text DEFAULT NULL,
    `TURNO` text DEFAULT NULL,
    `FECHA_INGRESO` text DEFAULT NULL,
    `PUESTO` text DEFAULT NULL,
    `CLUES` text DEFAULT NULL,
    `NUM_EMP` text DEFAULT NULL,
    `NSS` bigint(20) DEFAULT NULL,
    `ORIGEN_RECURSO` text DEFAULT NULL,
    `PARTE_ESTATAL` text DEFAULT NULL,
    `P0200` double DEFAULT NULL,
    `P0700` double DEFAULT NULL,
    `P24BA` double DEFAULT NULL,
    `P24GA` double DEFAULT NULL,
    `P30AR` double DEFAULT NULL,
    `P30BR` double DEFAULT NULL,
    `P30MR` double DEFAULT NULL,
    `P32PD` double DEFAULT NULL,
    `P32PV` double DEFAULT NULL,
    `P32VD` double DEFAULT NULL,
    `P06CG` double DEFAULT NULL,
    `P37TP` double DEFAULT NULL,
    `P4200` double DEFAULT NULL,
    `P55AG` double DEFAULT NULL,
    `P45AL` double DEFAULT NULL,
    `P56MD` double DEFAULT NULL,
    `P56EN` double DEFAULT NULL,
    `P56TS` double DEFAULT NULL,
    `P56AF` double DEFAULT NULL,
    `P56ON` double DEFAULT NULL,
    `P56PS` double DEFAULT NULL,
    `P56QM` double DEFAULT NULL,
    `P56OD` double DEFAULT NULL,
    `P57LM` double DEFAULT NULL,
    `P59DT` double DEFAULT NULL,
    `P69AN` double DEFAULT NULL,
    `P69TR` double DEFAULT NULL,
    `P75AP` double DEFAULT NULL,
    `P73DM` double DEFAULT NULL,
    `P73DR` double DEFAULT NULL,
    `PAX00` double DEFAULT NULL,
    `PE400` double DEFAULT NULL,
    `P19TE` double DEFAULT NULL,
    `P3800` double DEFAULT NULL,
    `P4400` double DEFAULT NULL,
    `P4600` double DEFAULT NULL,
    `P37MF` double DEFAULT NULL,
    `PER_GRAVADA` double DEFAULT NULL,
    `P24GG` double DEFAULT NULL,
    `P32VV` double DEFAULT NULL,
    `P45AA` double DEFAULT NULL,
    `P37TT` double DEFAULT NULL,
    `P57LL` double DEFAULT NULL,
    `P59TT` double DEFAULT NULL,
    `P73MM` double DEFAULT NULL,
    `P32DD` double DEFAULT NULL,
    `PER_NOGRAVA` double DEFAULT NULL,
    `TOT_PERCEPCION` double DEFAULT NULL,
    `OTROS_PAGOS` text DEFAULT NULL,
    `D0100` double DEFAULT NULL,
    `D0200` double DEFAULT NULL,
    `D0400` double DEFAULT NULL,
    `D0300` double DEFAULT NULL,
    `D0800` double DEFAULT NULL,
    `D1000` double DEFAULT NULL,
    `D1700` double DEFAULT NULL,
    `D1800` double DEFAULT NULL,
    `D2024` double DEFAULT NULL,
    `D21FA` double DEFAULT NULL,
    `D2121` double DEFAULT NULL,
    `D2900` double DEFAULT NULL,
    `D3400` double DEFAULT NULL,
    `D4600` double DEFAULT NULL,
    `D46CA` double DEFAULT NULL,
    `D46CR` double DEFAULT NULL,
    `D5000` double DEFAULT NULL,
    `D5100` double DEFAULT NULL,
    `D5600` double DEFAULT NULL,
    `D5700` double DEFAULT NULL,
    `D5800` double DEFAULT NULL,
    `D6200` double DEFAULT NULL,
    `D6400` double DEFAULT NULL,
    `D6500` double DEFAULT NULL,
    `D7000` double DEFAULT NULL,
    `D7700` double DEFAULT NULL,
    `DAS10` double DEFAULT NULL,
    `DAS20` double DEFAULT NULL,
    `D0101` double DEFAULT NULL,
    `OTRAS_DEDUCCIONES` double DEFAULT NULL,
    `TOT_DEDUCCION` double DEFAULT NULL,
    `LIQUIDO` double DEFAULT NULL,
    `TIPO_NOMINA` text DEFAULT NULL,
    `DEL` int(11) DEFAULT NULL,
    `AL` int(11) DEFAULT NULL,
    `FECHA_PAGO` int(11) DEFAULT NULL,
    `QNA` text DEFAULT NULL,
    `PERIODICIDAD` text DEFAULT NULL,
    `OBSERVACIONES` varchar(255) DEFAULT NULL,
    `CEDULA_PROF` varchar(255) DEFAULT NULL,
    `mmFolio` int(11) NOT NULL AUTO_INCREMENT,
    `RAMA` varchar(125) DEFAULT NULL,
    `archivo_xml` varchar(255) DEFAULT NULL,
    PRIMARY KEY (`mmFolio`)
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
/////////
    $srcSQL = $mysqli->query($query_srcSQL) or die($mysqli->error.__LINE__);

    echo "Tabla creada con éxito.<br>\n";

    try{
        $finfo = finfo_open(FILEINFO_MIME_TYPE); 

        $type = finfo_file($finfo, $archivo['tmp_name']); 

        $fechahora = date("d").date("m").date("Y").date("H").date("i").date("s");

        $nombreArchivo = $TablaName;

        $idInsertado ='';
        $numeroRegistros = '';

        if($type == "text/plain"){//Si el Mime coincide con CSV
            $destinationPath = $public_path.'scriptSAT/archivos-csv/';
            $csv = $destinationPath . $nombreArchivo.".csv";

            $file_data = file_get_contents($_FILES['archivo_csv']['tmp_name']);
            $encoding = mb_detect_encoding($file_data,"UTF-8",true);
            if($encoding === false){
                echo "Codificando a UTF-8.<br>\n";
                $utf8_file_data = utf8_encode($file_data);
            }else{
                $utf8_file_data = $file_data;
            }
            
            //if (move_uploaded_file($_FILES['archivo_csv']['tmp_name'], $csv)) {
            if(file_put_contents($csv,$utf8_file_data)){
                echo "El fichero es válido y se subió con éxito.<br>\n";
            } else {
                echo "¡Posible ataque de subida de ficheros!<br>\n";
            }

            $batchcount=0;
            /**** Conteo de columnas en el csv */
            while ($line = fgetcsv(fopen($csv,'r'))){
                $numcols = count($line);
                if ($numcols < 99 || $numcols > 100) {
                    echo '===================== ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR ERROR =====================  <br>';
                    echo "Error: el numero de columnas en el csv es incorrecto, $numcols. En linea ".++$batchcount.". <br>";
                    die;
                    break;
                }
                break;
            }
            
            $mysqli->begin_transaction();

            echo "Comienza tranzacción para subir datos del CSV.<br>\n";

            $query = sprintf("
                LOAD DATA local INFILE '%s' 
                INTO TABLE $TablaName 
                CHARACTER SET utf8 
                FIELDS TERMINATED BY ',' 
                OPTIONALLY ENCLOSED BY '\"' 
                ESCAPED BY '\"' 
                LINES TERMINATED BY '\\n'
                IGNORE 1 LINES
                SET mmFolio = null, RAMA = 'SALUD', archivo_xml = null 
                ", addslashes($csv));

            $mysqli->query($query) or die($mysqli->error.__LINE__);

            $mysqli->commit();

            echo "Se termina carga de datos del CSV.<br>\n";
        }
    }catch(\Exception $e){
        $mysqli->rollback();
    }
    echo "Se inicia comienzo de generacion de archivos.<br>\n";
    $FechaGeneracion = $FechaInicio = $FechaFinal = '';
    $carpeta = GenerarNominaSAT($TipoNomina, $TablaName, $FechaGeneracion, $FechaInicio, $FechaFinal, $Dias, $Periodicidad, $Adenda,$mysqli);
    echo "<br>Se termino generacion de archivos.<br>\n";

    $storage_path = $public_path.'scriptSAT/';
    
    $zip = new ZipArchive();
    $zippath = $storage_path."archivos-layouts/".$carpeta."/";
    $zipname = "Layouts.SAT.".$carpeta.".zip";
    
    chdir($zippath);
    echo "Generación del archivo zip.<br>\n";
    exec("zip -P sat2015 -r ".$zipname." ./*");
    
    //movemos el archivo un directorio arriba
    echo "Moviendo zip.<br>\n";
    rename($zippath.$zipname,$storage_path."archivos-layouts/".$zipname);
    //eliminamos todos los layouts generados (ya estan en el zip)
    echo "Eliminando archivos generados.<br>\n";
    delete_files($zippath);
    
    $zippath = $storage_path."archivos-layouts/";

    echo '<br>############################################### ------------------ Archivo ZIP ------------------ ###############################################<br>';
    echo "Archivo zip generado: ".$zippath.$zipname;
    echo "<a href='archivos-layouts/$zipname'>Descargar ZIP</a>";

    header("Content-Type: application/zip");
    header("Content-Disposition: attachment; filename=$zipname");
    header("Content-Length: " . filesize($zippath.$zipname));
    readfile($zippath.$zipname);

    //delete_files($zippath.$zipname);

    //echo "zip -P sat2015 -j -r ".$zippath.$zipname." \"".$zippath.$carpeta."/\"";

    exit;

    /*
    $zip_status = $zip->open($zippath.$zipname);

    if ($zip_status === true) {

        $zip->close();
        //Storage::deleteDirectory("sync");
        ///Then download the zipped file.
        header('Accept-Ranges: bytes');
        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename='.$zipname);
        header('Content-Length: ' . filesize($zippath.$zipname));
        
        readfile($zippath.$zipname);
    }else{
        echo 'Archivo zip no valido';
    }
    */
?>