<?php

set_time_limit(0);

function calcular_semanas($FechaPago, $FechaIngreso)
{

	$datetime1 = new DateTime($FechaIngreso);
	$datetime2 = new DateTime($FechaPago);
	$interval = $datetime1->diff($datetime2);
	return "P".floor(($interval->format('%a') / 7)) . 'W';

}

function delete_files($target) {
    if(is_dir($target)){
        $files = glob( $target . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned

        foreach( $files as $file ){
            delete_files( $file );      
        }

        rmdir( $target );
    } elseif(is_file($target)) {
        unlink( $target );  
    }
}

function GenerarNominaSAT($TipoNomina, $TablaName, $FechaGeneracion, $FechaInicio, $FechaFinal, $Dias, $Periodicidad, $mysqli)
{
        $query_srcSQL ="SELECT N.NOMBRE_NOMINA as NombreNomina, N.RFC, N.CURP, N.NOMBRE, N.TURNO, N.FECHA_INGRESO, N.PUESTO, N.CLUES, N.NUM_EMP as NUMCHE, N.NSS, N.ORIGEN_RECURSO as OrigenRecurso, N.PARTE_ESTATAL, N.P0200, N.P0700, N.P24BA, N.P24GA, if(N.P30AR>0, 4, if(N.P30MR>0, 3, if(N.P30BR>0, 1, 99))) NivelRiesgo, N.P30AR, N.P30BR, N.P30MR, N.P32PD, N.P32PV, N.P32VD, N.P06CG, N.P37TP, N.P4200, N.P55AG, N.P45AL, N.P56MD, N.P56EN, N.P56TS, N.P56AF, N.P56ON, N.P56PS, N.P56QM, N.P56OD, N.P57LM, N.P59DT, N.P69AN, N.P69TR, N.P75AP, N.P73DM, N.P73DR, N.PAX00, N.PE400, N.P19TE, N.P3800, N.P4400, N.P4600, N.P37MF, N.PER_GRAVADA as PercepcionGravado, N.P24GG, N.P32VV, N.P45AA, N.P37TT, N.P57LL, N.P59TT, N.P73MM, N.P73RR, N.PER_NOGRAVA as PercepcionNoGravado, (N.TOT_PERCEPCION) as TotalPercepciones, N.OTROS_PAGOS, N.D0100 as ISR, N.D0200 as AportacionRetiro, N.D0400 as SegSocial, N.D0300, N.D0800, N.D1000, N.D1700, N.D1800, N.D2024, N.D21FA, N.D2121, N.D2900, N.D3400, N.D4600, N.D46CA, N.D46CR, N.D5000, N.D5100, N.D5600, N.D5700, N.D5800, N.D6200, N.D6400, N.D6500, N.D7000, N.D7700, N.DAS10, N.DAS20, N.D0101, N.OTRAS_DEDUCCIONES, N.TOT_DEDUCCION as TotalDeducciones, N.LIQUIDO as Liquido, substring(N.TIPO_NOMINA,1,1) TipoE, N.DEL, N.AL, N.FECHA_PAGO as FechaPago, N.QNA, N.PERIODICIDAD as Periodicidad, OBSERVACIONES as Observaciones, mmFolio FROM ".$TablaName." as N where length(RFC)>9  order by NOMBRE_NOMINA"; 

		//echo  $query_srcSQL."<br>"; exit();

        /*

		$query_srcSQL = "SELECT DEL, AL, mmFolio, NOMBRE_NOMINA NombreNomina, ORIGE_RECURSO OrigenRecurso, NSS, RFC, CURP, NOMBRE, TURNO, if(if(P3000>0,100*(P3000/P0700),'0')=30,'4', if(if(P3000>0,100*(P3000/P0700),'0')=10,'3',if(if(P3000>0,100*(P3000/P0700),'0')=7,'2','1'))) NivelRiesgo, FECHA_INGRESO, PUESTO, CLUES, NUMCHE, P0700, P3000, PercepcionGravado, PercepcionNoGravado, TotalPercepciones, ISR, AportacionRetiro, SegSocial, D5800, TotalDeducciones, Liquido, OTROS_PAGOS, FechaPago, substring(TIPO_NOMINA,1,1) TipoE, Periodicidad, PARTE_ESTATAL FROM ".$TablaName." where length(RFC)>9 order by NOMBRE_NOMINA"; 
		
		*/
		
			$srcSQL = $mysqli->query($query_srcSQL) or die($mysqli->error.__LINE__);
			$row_srcSQL = $srcSQL->fetch_assoc();
			$totalRows_srcSQL = $srcSQL->num_rows;

			//print_r($row_srcSQL); exit();

			$TotalArchivos=0;
			
			$UpdateQry ="";

			$Segundo=1;
			
			//echo "Folio: ".$Folio."<br>";
			//
			//$TipoNomina = 9;

			$Carpeta = "";

			$carpeta_grupo = $TablaName;

			delete_files('archivos-layouts/'. $carpeta_grupo);

			$FechaHoraGeneracion = date("Y-m-d")."T".date("H:i:s");
	
			do{

				$Folio = $row_srcSQL["mmFolio"];

				$FechaGeneracion = substr($row_srcSQL["AL"],0,4)."-".substr($row_srcSQL["AL"],4,2)."-".substr($row_srcSQL["AL"],6,2);  

				$FechaInicio = substr($row_srcSQL["DEL"],0,4)."-".substr($row_srcSQL["DEL"],4,2)."-".substr($row_srcSQL["DEL"],6,2);

				$FechaFinal = substr($row_srcSQL["AL"],0,4)."-".substr($row_srcSQL["AL"],4,2)."-".substr($row_srcSQL["AL"],6,2); 

				$FechaPago = substr($row_srcSQL["FechaPago"],0,4)."-".substr($row_srcSQL["FechaPago"],4,2)."-".substr($row_srcSQL["FechaPago"],6,2); 

				if($Carpeta != 'archivos-layouts/'. $carpeta_grupo . '/' . $row_srcSQL["NombreNomina"])
				{
					$Carpeta = 'archivos-layouts/'. $carpeta_grupo . '/' . $row_srcSQL["NombreNomina"];
					$TipoNomina++;
					
					echo $Carpeta."<br>";
				}

				if(strlen($TipoNomina)==1)
				{
					$TipoNomina ="0".$TipoNomina;
				}


				if (!mkdir($Carpeta, 0777, true)) {
			    //die('Failed to create folder..'); exit();
				}

				//echo "aqui va: <br>"; exit();

				$filepath = $Carpeta."/".$row_srcSQL["mmFolio"]."_".$row_srcSQL["CURP"].".txt";
				$fh = fopen($filepath,"w");

				$HoraGeneracion = date("H:i:s", mktime(0, 0, $Segundo, substr($FechaGeneracion,5,2), substr($FechaGeneracion,8,2), substr($FechaGeneracion,0,4)));
				
               
				include("funciones_sat3.3v2_layoutHonorarios_v3.php");

				$Segundo += 1;
				

				$TotalArchivos++;

				fclose($fh);

			

			}while($row_srcSQL = $srcSQL->fetch_assoc());
			

			echo "Listo: ".$Carpeta." [".$TotalArchivos."]<br>";

			echo "Folio: " . $Folio;

			return $carpeta_grupo;

}




?>