<?php

//$Folio = $row_srcSQL["mmFolio"];
//
$dato_periodicidad = trim($row_srcSQL["Periodicidad"]);
$Periodicidad = (strlen($dato_periodicidad)>0) ? $dato_periodicidad :"04";

if($row_srcSQL["TURNO"]=="VESPERTINO")
	$Turno = "02"; //Nocturno
else if($row_srcSQL["TURNO"]=="MATUTINO")
    $Turno = "01"; //Nocturno
else if($row_srcSQL["TURNO"]=="OTRO TURNO")
	$Turno = "99"; //Nocturno
else 
	$Turno = "03"; //Mixto 	             



$FechaIngreso = substr($row_srcSQL["FECHA_INGRESO"],0,4)."-".substr($row_srcSQL["FECHA_INGRESO"],4,2)."-".substr($row_srcSQL["FECHA_INGRESO"],6,2);

$NumSemanasLaboradas = calcular_semanas($FechaFinal, $FechaIngreso);
//$NumSemanasLaboradas = calcular_semanas($FechaFinal, $row_srcSQL["FECHA_INGRESO"]);

$TotalPercepciones = $row_srcSQL["TotalPercepciones"];
$TotalDeducciones = $row_srcSQL["TotalDeducciones"];
$ISR = $row_srcSQL["ISR"];
$PercepcionGravado = $row_srcSQL["PercepcionGravado"];
$PercepcionNoGravado = $row_srcSQL["PercepcionNoGravado"];
// ISR, RFC, CURP, NOMBRE, NUMCHE, PUESTO, NivelRiesgo, FechaIngreso, P3000,P0700, D5800, AportacionRetiro, SegSocial

$TotalDeduccionesEtiqueta = "";

if($TotalDeducciones<=0)
{
	$TotalDeduccionesEtiqueta = "";
	
}else
{
	$TotalDeduccionesEtiqueta = $TotalDeducciones;
}

$Puesto = $row_srcSQL["PUESTO"];

$Puesto = str_replace("'", "", $Puesto);
$Puesto = str_replace('"', "", $Puesto);
$Puesto = str_replace("/", "", $Puesto);
$Puesto = str_replace(".", "", $Puesto);
$Puesto = str_replace(",", "", $Puesto);
$Puesto = str_replace("#", "", $Puesto);
$Puesto = str_replace("$", "", $Puesto);

$Nombre = $row_srcSQL["NOMBRE"];

$Nombre = str_replace("'", "", $Nombre);
$Nombre = str_replace('"', "", $Nombre);
$Nombre = str_replace("/", "", $Nombre);
$Nombre = str_replace(".", "", $Nombre);
$Nombre = str_replace(",", "", $Nombre);
$Nombre = str_replace("#", "", $Nombre);
$Nombre = str_replace("$", "", $Nombre);

$TotalOtrosPagos=$row_srcSQL["OTROS_PAGOS"] ? $row_srcSQL["OTROS_PAGOS"] : 0;

$Dato="DC|3.3|NOM".$TipoNomina."|".$Folio."|".$FechaHoraGeneracion."|99|".number_format($TotalPercepciones+$TotalOtrosPagos,2,'.','')."|".number_format($TotalDeducciones,2,".","")."|MXN||".number_format(($TotalPercepciones+$TotalOtrosPagos)-$TotalDeducciones,2,".","")."|N|PUE|29010"."\r";
	fwrite($fh,$Dato.PHP_EOL);

	$Dato="EM|ISA961203QN5|INSTITUTO DE SALUD"."\r";
	fwrite($fh,$Dato.PHP_EOL);  

	if($row_srcSQL["PARTE_ESTATAL"]>0)
	{
		$Dato="CNE|603||0||".$row_srcSQL["OrigenRecurso"]."|".number_format($row_srcSQL["PARTE_ESTATAL"],2,'.','')."\r";   //IF Federal, IP Propios, IM Mixtos
		fwrite($fh,$Dato.PHP_EOL);	

	}else
	{
		$Dato="CNE|603||0||".$row_srcSQL["OrigenRecurso"]."|"."\r";   //IF Federal, IP Propios, IM Mixtos
		fwrite($fh,$Dato.PHP_EOL);	
	}
    
    

	$Dato="RC|".$row_srcSQL["RFC"]."|".$Nombre."|P01\r";
	fwrite($fh,$Dato.PHP_EOL);

	$Dato="CNR|".$row_srcSQL["TipoE"]."|".$FechaPago."|".$FechaInicio."|".$FechaFinal."|".$Dias."|".($TotalPercepciones ? number_format($TotalPercepciones,2,'.','') : '')."|".number_format($TotalDeduccionesEtiqueta,2,".","")."|".number_format($TotalOtrosPagos,2,".","")."|".$row_srcSQL["CURP"]."|".$row_srcSQL["NSS"]."|".$FechaIngreso."|".$NumSemanasLaboradas."|01||".$Turno."|02|".$row_srcSQL["NUMCHE"]."||".$Puesto."|".$row_srcSQL["NivelRiesgo"]."|".$Periodicidad."||||".number_format($TotalPercepciones/15,2,'.','')."|CHP"."\r";
	fwrite($fh,$Dato.PHP_EOL);

    if($TotalDeducciones>0)
    {
        $Dato="CN|84111505|1|ACT|Pago de nómina|".number_format($TotalPercepciones+$TotalOtrosPagos,2,'.','')."|".number_format($TotalPercepciones+$TotalOtrosPagos,2,'.','')."|".number_format($TotalDeducciones,2,".","")."\r";
        fwrite($fh,$Dato.PHP_EOL);
    }else
    {
        $Dato="CN|84111505|1|ACT|Pago de nómina|".number_format($TotalPercepciones+$TotalOtrosPagos,2,'.','')."|".number_format($TotalPercepciones+$TotalOtrosPagos,2,'.','')."|\r";
        fwrite($fh,$Dato.PHP_EOL);
    }
    
    if($row_srcSQL["Observaciones"] && trim($row_srcSQL["Observaciones"]) != '' ){
        $Dato="OP|".$row_srcSQL["Observaciones"]."\r";
        fwrite($fh,$Dato.PHP_EOL);
    }
    
    if(($PercepcionGravado+$PercepcionNoGravado) >0)
    {
        $Dato="CNP|".number_format($PercepcionGravado+$PercepcionNoGravado,2,'.','')."|||".number_format($PercepcionGravado,2,'.','')."|".number_format($PercepcionNoGravado,2,'.','')."|||||||||||\r";  //Pendiente
        fwrite($fh,$Dato.PHP_EOL);
    }

    /*
	if($PercepcionNoGravado>0)
	{
		$Dato="NPD|".($NPD++)."|001|P0700|Sueldos, Salarios Rayas y Jornales|".number_format($PercepcionGravado,2,'.','')."|".number_format($PercepcionNoGravado,2,'.','')."\r";
		fwrite($fh,$Dato.PHP_EOL);
	}else
	{
		$Dato="NPD|".($NPD++)."|001|P0700|Sueldos, Salarios Rayas y Jornales|".number_format($TotalPercepciones,2,'.','')."|0"."\r";
		fwrite($fh,$Dato.PHP_EOL);
    }
    */

    $NPD=1;
    //---
    if($row_srcSQL["P0200"]){
    $Dato="NPD|".($NPD++)."|001|P0200|Sueldos, Salarios Rayas y Jornales|".number_format($row_srcSQL["P0200"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P0700"]){
    $Dato="NPD|".($NPD++)."|001|P0700|Sueldos, Salarios Rayas y Jornales|".number_format($row_srcSQL["P0700"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P24BA"]){
    $Dato="NPD|".($NPD++)."|002|P24BA|Gratificación Anual (Aguinaldo)|".number_format($row_srcSQL["P24BA"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P24GA"]){
    $Dato="NPD|".($NPD++)."|002|P24GA|Gratificación Anual (Aguinaldo)|".number_format($row_srcSQL["P24GA"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P32PD"]){
    $Dato="NPD|".($NPD++)."|020|P32PD|Prima dominical|".number_format($row_srcSQL["P32PD"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P32PV"]){
    $Dato="NPD|".($NPD++)."|021|P32PV|Prima vacacional|".number_format($row_srcSQL["P32PV"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P32VD"]){
    $Dato="NPD|".($NPD++)."|021|P32VD|Prima vacacional|".number_format($row_srcSQL["P32VD"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P06CG"]){
    $Dato="NPD|".($NPD++)."|038|P06CG|Compensación Garantizada|".number_format($row_srcSQL["P06CG"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P37TP"]){
    $Dato="NPD|".($NPD++)."|038|P37TP|Ayuda de Tesís|".number_format($row_srcSQL["P37TP"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P4200"]){
    $Dato="NPD|".($NPD++)."|038|P4200|Asignación Neta|".number_format($row_srcSQL["P4200"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P55AG"]){
    $Dato="NPD|".($NPD++)."|038|P55AG|Ayuda Para Gastos de Actualización|".number_format($row_srcSQL["P55AG"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P45AL"]){
    $Dato="NPD|".($NPD++)."|035|P45AL|Ayuda para anteojos|".number_format($row_srcSQL["P45AL"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P56MD"]){
    $Dato="NPD|".($NPD++)."|038|P56MD|Estimulos a la Productividad y la Calidad Personal Medico|".number_format($row_srcSQL["P56MD"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P56EN"]){
    $Dato="NPD|".($NPD++)."|038|P56EN|Estimulos a la Productividad y la Calidad Personal de Enfermeria|".number_format($row_srcSQL["P56EN"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P56TS"]){
    $Dato="NPD|".($NPD++)."|038|P56TS|Estimulos a la Productividad y la Calidad Personal de Trabajo Social|".number_format($row_srcSQL["P56TS"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P56AF"]){
    $Dato="NPD|".($NPD++)."|038|P56AF|Estimulos a la Productividad y la Calidad Personal de Rama Afin|".number_format($row_srcSQL["P56AF"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P56ON"]){
    $Dato="NPD|".($NPD++)."|038|P56ON|Estimulos a la Productividad y la Calidad Personal de Odontologia|".number_format($row_srcSQL["P56ON"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P56PS"]){
    $Dato="NPD|".($NPD++)."|038|P56PS|Estimulos a la Productividad y la Calidad Personal Psicologos|".number_format($row_srcSQL["P56PS"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P56QM"]){
    $Dato="NPD|".($NPD++)."|038|P56QM|Estimulos a la Productividad y la Calidad Personal Quimico|".number_format($row_srcSQL["P56QM"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P56OD"]){
    $Dato="NPD|".($NPD++)."|038|P56OD|Estimulos a la Productividad y la Calidad Personal de Otras Disciplinas|".number_format($row_srcSQL["P56OD"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P57LM"]){
    $Dato="NPD|".($NPD++)."|038|P57LM|Licencia de Manejo|".number_format($row_srcSQL["P57LM"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P59DT"]){
    $Dato="NPD|".($NPD++)."|038|P59DT|Día del Trabajador de la Salud|".number_format($row_srcSQL["P59DT"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P69AN"]){
    $Dato="NPD|".($NPD++)."|010|P69AN|Premios por puntualidad|".number_format($row_srcSQL["P69AN"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P69TR"]){
    $Dato="NPD|".($NPD++)."|010|P69TR|Premios por puntualidad|".number_format($row_srcSQL["P69TR"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P75AP"]){
    $Dato="NPD|".($NPD++)."|010|P75AP|Premios por puntualidad|".number_format($row_srcSQL["P75AP"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P73DM"]){
    $Dato="NPD|".($NPD++)."|038|P73DM|Día de las Madres|".number_format($row_srcSQL["P73DM"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P73DR"]){
    $Dato="NPD|".($NPD++)."|038|P73DR|Día de Reyes|".number_format($row_srcSQL["P73DR"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["PAX00"]){
    $Dato="NPD|".($NPD++)."|038|PAX00|Prima quinquenal|".number_format($row_srcSQL["PAX00"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["PE400"]){
    $Dato="NPD|".($NPD++)."|038|PE400|Compensación por Laborar en Comunidades de Bajo Desarrollo|".number_format($row_srcSQL["PE400"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P19TE"]){
    $Dato="NPD|".($NPD++)."|038|P19TE|Tiempo extra|".number_format($row_srcSQL["P19TE"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P3800"]){
    $Dato="NPD|".($NPD++)."|029|P3800|Vales de despensa|".number_format($row_srcSQL["P3800"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P4400"]){
    $Dato="NPD|".($NPD++)."|038|P4400|Previsión Social Múltiple|".number_format($row_srcSQL["P4400"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P4600"]){
    $Dato="NPD|".($NPD++)."|036|P4600|Ayuda para transporte|".number_format($row_srcSQL["P4600"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P37MF"]){
    $Dato="NPD|".($NPD++)."|029|P37MF|Vales de despensa|".number_format($row_srcSQL["P37MF"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P30AR"]){
    $Dato="NPD|".($NPD++)."|038|P30AR|Alto Riesgo|".number_format($row_srcSQL["P30AR"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P30BR"]){
    $Dato="NPD|".($NPD++)."|038|P30BR|Bajo Riesgo|".number_format($row_srcSQL["P30BR"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P30MR"]){
    $Dato="NPD|".($NPD++)."|038|P30MR|Mediano Riesgo|".number_format($row_srcSQL["P30MR"],2,".","")."|0"."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    //Percepciones no gravadas

    if($row_srcSQL["P24GG"]){
    $Dato="NPD|".($NPD++)."|038|P24GG|Compensación de ISR Por Aguinaldo o Gratificación de Fin de Año|0|".number_format($row_srcSQL["P24GG"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P32VV"]){
    $Dato="NPD|".($NPD++)."|038|P32VV|Compensación de ISR Por Prima Vacacional|0|".number_format($row_srcSQL["P32VV"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P45AA"]){
    $Dato="NPD|".($NPD++)."|038|P45AA|Compensación de ISR Por Concepto de Anteojos|0|".number_format($row_srcSQL["P45AA"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P37TT"]){
    $Dato="NPD|".($NPD++)."|038|P37TT|Compensación de ISR Tesís|0|".number_format($row_srcSQL["P37TT"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P57LL"]){
    $Dato="NPD|".($NPD++)."|038|P57LL|Compensación de ISR Por Licencia de Manejo|0|".number_format($row_srcSQL["P57LL"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P59TT"]){
    $Dato="NPD|".($NPD++)."|038|P59TT|Compensación de ISR Por Día del Trabajador de la Salud|0|".number_format($row_srcSQL["P59TT"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P73MM"]){
    $Dato="NPD|".($NPD++)."|038|P73MM|Compensación de ISR Día de las Madres|0|".number_format($row_srcSQL["P73MM"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["P73RR"]){
    $Dato="NPD|".($NPD++)."|038|P73RR|Compensación de ISR Día de Reyes|0|".number_format($row_srcSQL["P73RR"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    

    //---
    
    

	
	if($TotalDeducciones>0)
		$TotalDeduccionesMenosISR = number_format($TotalDeducciones-$ISR,2,'.','');
	else
		$TotalDeduccionesMenosISR="";


	if(is_numeric($TotalDeduccionesMenosISR))
	{



		/*$Dato="NPD|040|00000|Ingresos Propios|0|0"."\r";
		fwrite($fh,$Dato.PHP_EOL);*/
		if($ISR>0)
		{
			$Dato="CND|".$TotalDeduccionesMenosISR."|".number_format($ISR,2,'.','')."\r";
		fwrite($fh,$Dato.PHP_EOL);
		}else
		{
			$Dato="CND|".$TotalDeduccionesMenosISR."|\r";
		fwrite($fh,$Dato.PHP_EOL);
		}
	
	}


    /*
	if((floor(($TotalDeducciones-$ISR-$row_srcSQL["AportacionRetiro"]-$row_srcSQL["D5800"]-$row_srcSQL["SegSocial"]) * 100) / 100)>0.00)
	{
		$Dato="NDD|004|00000|Otros|".number_format($TotalDeducciones-$ISR-$row_srcSQL["AportacionRetiro"]-$row_srcSQL["D5800"]-$row_srcSQL["SegSocial"],2,'.','')."\r";
		fwrite($fh,$Dato.PHP_EOL);
	}
	*/
    
    //DEducciones

    if($row_srcSQL["OTRAS_DEDUCCIONES"]){
    $Dato="NDD|004|00000|Otros|".number_format($row_srcSQL["OTRAS_DEDUCCIONES"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["ISR"]){
    $Dato="NDD|002|D0100|ISR|".number_format($row_srcSQL["ISR"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["AportacionRetiro"]){
    $Dato="NDD|003|D0200|Aportaciones a retiro, cesantía en edad avanzada y vejez.|".number_format($row_srcSQL["AportacionRetiro"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["SegSocial"]){
    $Dato="NDD|001|D0400|Seguridad social|".number_format($row_srcSQL["SegSocial"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D0300"]){
    $Dato="NDD|004|D0300|Préstamo a corto plazo del ISSSTE|".number_format($row_srcSQL["D0300"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D0800"]){
    $Dato="NDD|004|D0800|Préstamo adicionales ISSSTE|".number_format($row_srcSQL["D0800"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D1000"]){
    $Dato="NDD|004|D1000|Rentas ISSSTE|".number_format($row_srcSQL["D1000"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D1700"]){
    $Dato="NDD|020|D1700|Ausencia (Ausentismo)|".number_format($row_srcSQL["D1700"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D1800"]){
    $Dato="NDD|020|D1800|Ausencia (Ausentismo)|".number_format($row_srcSQL["D1800"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D2024"]){
    $Dato="NDD|004|D2024|Reintegros a Partidas Presupuestales Año Anterior|".number_format($row_srcSQL["D2024"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D21FA"]){
    $Dato="NDD|004|D21FA|Fondo de Ahorro Capitalizable|".number_format($row_srcSQL["D21FA"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D2121"]){
    $Dato="NDD|004|D2121|Responsabilidad FONAC ciclo actual|".number_format($row_srcSQL["D2121"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D2900"]){
    $Dato="NDD|004|D2900|Responsabilidades|".number_format($row_srcSQL["D2900"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D3400"]){
    $Dato="NDD|004|D3400|Seguro de Responsabilidad Profesional para Personal Médicos y de Enfermeria|".number_format($row_srcSQL["D3400"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D4600"]){
    $Dato="NDD|017|D4600|Adquisición de artículos producidos por la empresa o establecimiento|".number_format($row_srcSQL["D4600"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D46CA"]){
    $Dato="NDD|018|D46CA|Cuotas para la constitución y fomento de sociedades cooperativas y de cajas de ahorro|".number_format($row_srcSQL["D46CA"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D46CR"]){
    $Dato="NDD|018|D46CR|Cuotas para la constitución y fomento de sociedades cooperativas y de cajas de ahorro|".number_format($row_srcSQL["D46CR"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D5000"]){
    $Dato="NDD|004|D5000|Seguro Institucional de Vida METLIFE México|".number_format($row_srcSQL["D5000"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D5100"]){
    $Dato="NDD|004|D5100|Seguro de Vida Individual METLIFE México|".number_format($row_srcSQL["D5100"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D5600"]){
    $Dato="NDD|004|D5600|Amortización FOVISSSTE sobre préstamos especiales|".number_format($row_srcSQL["D5600"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D5700"]){
    $Dato="NDD|004|D5700|Seguro de Vida Adicional METLIFE México|".number_format($row_srcSQL["D5700"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D5800"]){
    $Dato="NDD|019|D5800|Cuotas sindicales|".number_format($row_srcSQL["D5800"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D6200"]){
    $Dato="NDD|007|D6200|Pensión alimenticia|".number_format($row_srcSQL["D6200"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D6400"]){
    $Dato="NDD|010|D6400|Pago por crédito de vivienda|".number_format($row_srcSQL["D6400"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D6500"]){
    $Dato="NDD|010|D6500|Pago por crédito de vivienda|".number_format($row_srcSQL["D6500"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D7000"]){
    $Dato="NDD|004|D7000|Fondo de Ahorro para Auxilio de Defunción|".number_format($row_srcSQL["D7000"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D7700"]){
    $Dato="NDD|004|D7700|Seguro Colectivo de Retiro|".number_format($row_srcSQL["D7700"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["DAS10"]){
    $Dato="NDD|023|DAS10|Aportaciones voluntarias|".number_format($row_srcSQL["DAS10"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["DAS20"]){
    $Dato="NDD|023|DAS20|Aportaciones voluntarias|".number_format($row_srcSQL["DAS20"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

    if($row_srcSQL["D0101"]){
    $Dato="NDD|101|D0101|ISR Retenido de ejercicio anterior|".number_format($row_srcSQL["D0101"],2,".","")."\r";
    fwrite($fh,$Dato.PHP_EOL);
    }

	
	if($row_srcSQL["OTROS_PAGOS"]){
    $Dato="NOP|999|19999|Otros Pagos|".number_format($row_srcSQL["OTROS_PAGOS"],2,".","")."||||\r";
    fwrite($fh,$Dato.PHP_EOL);
    }
	

?>