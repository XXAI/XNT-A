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

$NumSemanasLaboradas = calcular_semanas($FechaFinal, $row_srcSQL["FECHA_INGRESO"]);


$FechaIngreso = substr($row_srcSQL["FECHA_INGRESO"],0,4)."-".substr($row_srcSQL["FECHA_INGRESO"],4,2)."-".substr($row_srcSQL["FECHA_INGRESO"],6,2);



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

if($TipoHonorario == 'E023'){
	$serie = 'RHN';
}else{
	$serie = 'NOM';
}

$Dato="DC|3.3|".$serie.$TipoNomina."|".$Folio."|".$FechaHoraGeneracion."|99|".number_format($TotalPercepciones+$TotalOtrosPagos,2,'.','')."|".number_format($TotalDeducciones,2,".","")."|MXN||".number_format(($TotalPercepciones+$TotalOtrosPagos)-$TotalDeducciones,2,".","")."|N|PUE|29010||||"."\r";
	fwrite($fh,$Dato.PHP_EOL);

	$Dato="EM|ISA961203QN5|INSTITUTO DE SALUD"."\r";
	fwrite($fh,$Dato.PHP_EOL);  

	if($row_srcSQL["PARTE_ESTATAL"]>0)
	{
		$Dato="CNE|603||||".$row_srcSQL["OrigenRecurso"]."|".number_format($row_srcSQL["PARTE_ESTATAL"],2,'.','')."\r";   //IF Federal, IP Propios, IM Mixtos
		fwrite($fh,$Dato.PHP_EOL);	

	}else
	{
		$Dato="CNE|603||||".$row_srcSQL["OrigenRecurso"]."|"."\r";   //IF Federal, IP Propios, IM Mixtos
		fwrite($fh,$Dato.PHP_EOL);	
	}
    
    

	$Dato="RC|".$row_srcSQL["RFC"]."|".$Nombre."|P01\r";
	fwrite($fh,$Dato.PHP_EOL);

	if($TipoHonorario == 'E023'){
		$departamento = $row_srcSQL["CLUES"];
	}else{
		$departamento = '';
	}
	
	if($TipoHonorario == 'E023' || $TotalOtrosPagos > 0){
		$total_otros_pagos_formateado = number_format($TotalOtrosPagos,2,".","");
	}else{
		$total_otros_pagos_formateado = '';
	}
	
	$Dato="CNR|".$row_srcSQL["TipoE"]."|".$FechaPago."|".$FechaInicio."|".$FechaFinal."|".$Dias."|".($TotalPercepciones ? number_format($TotalPercepciones,2,'.','') : '')."|".number_format($TotalDeduccionesEtiqueta,2,".","")."|".$total_otros_pagos_formateado."|".$row_srcSQL["CURP"]."||||09||".$Turno."|09|".$row_srcSQL["NUMCHE"]."|".$departamento."|".$Puesto."||".$Periodicidad."|||||CHP"."\r";
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
        $Dato="OP|".(trim($row_srcSQL["Observaciones"]))."\r";
        fwrite($fh,$Dato.PHP_EOL);
    }
    
    if(($PercepcionGravado+$PercepcionNoGravado) >0)
    {
        $Dato="CNP|".number_format($PercepcionGravado+$PercepcionNoGravado,2,'.','')."|||".number_format($PercepcionGravado,2,'.','')."|".number_format($PercepcionNoGravado,2,'.','')."|||||||||||\r";  //Pendiente
        fwrite($fh,$Dato.PHP_EOL);
    }

	$NPD=1;

	if($row_srcSQL["P24GA"] > 0){//Harima:Trabajando con aguinaldos
		$Dato="NPD|".($NPD++)."|002|P24GA|Gratificación de Fin de Año|".number_format($row_srcSQL["P24GA"],2,'.','')."|0\r";
		fwrite($fh,$Dato.PHP_EOL);
	}

	if($row_srcSQL["P24GG"] > 0){//Harima:Trabajando con aguinaldos
		$Dato="NPD|".($NPD++)."|046|P24GG|Exento por Gratificación de Fin de Año|0|".number_format($row_srcSQL["P24GG"],2,'.','')."\r";
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


	if($TipoHonorario == 'E023'){
		$clave = 'P0500';
		$concepto = 'Ingresos Asimilados a Salarios';
	}else{
		$clave = 'P0200';
		$concepto = 'Honorarios';
	}

    //---
    if($row_srcSQL["P0200"]){
    	$Dato="NPD|".($NPD++)."|046|".$clave."|".$concepto."|".number_format($row_srcSQL["P0200"],2,".","")."|0"."\r";
    	fwrite($fh,$Dato.PHP_EOL);
    }
    

	
	if($TotalDeducciones>0)
		$TotalDeduccionesMenosISR = number_format($TotalDeducciones-$ISR,2,'.','');
	else
		$TotalDeduccionesMenosISR="";

	if(is_numeric($TotalDeduccionesMenosISR))
	{
		//$Dato="NPD|040|00000|Ingresos Propios|0|0"."\r";
		//fwrite($fh,$Dato.PHP_EOL);
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

	if($row_srcSQL["D1700"]){
        $Dato="NDD|020|D1700|FALTAS|".number_format($row_srcSQL["D1700"],2,".","")."\r";
        fwrite($fh,$Dato.PHP_EOL);
	}

	if($row_srcSQL["D6200"]){
        $Dato="NDD|007|D6200|PENSIÓN ALIMENTICIA|".number_format($row_srcSQL["D6200"],2,".","")."\r";
        fwrite($fh,$Dato.PHP_EOL);
	}

    /*
	if((floor(($TotalDeducciones-$ISR-$row_srcSQL["AportacionRetiro"]-$row_srcSQL["D5800"]-$row_srcSQL["SegSocial"]) * 100) / 100)>0.00)
	{
		$Dato="NDD|004|00000|Otros|".number_format($TotalDeducciones-$ISR-$row_srcSQL["AportacionRetiro"]-$row_srcSQL["D5800"]-$row_srcSQL["SegSocial"],2,'.','')."\r";
		fwrite($fh,$Dato.PHP_EOL);
	}
	*/
    
    //DEducciones

	if($TipoHonorario == 'E023'){
		$concepto_isr = 'IMPUESTO SOBRE LA RENTA';
	}else{
		$concepto_isr = 'ISR';
	}

    if($row_srcSQL["ISR"]){
        $Dato="NDD|002|D0100|".$concepto_isr."|".number_format($row_srcSQL["ISR"],2,".","")."\r";
        fwrite($fh,$Dato.PHP_EOL);
	}
	
	if($row_srcSQL["OTRAS_DEDUCCIONES"])
	{
		$Dato="NDD|004|0004|OTRAS DEDUCCIONES|".number_format($row_srcSQL["OTRAS_DEDUCCIONES"],2,".","")."\r";
        fwrite($fh,$Dato.PHP_EOL);
	}

	/*if($row_srcSQL["OTROS_PAGOS"] == 0.01){
		//$Dato="NOP|999|999|S. PARA EL EMPLEO|0.01||||\r";
        fwrite($fh,$Dato.PHP_EOL);
	}else */

	/*if($row_srcSQL["OTROS_PAGOS"] > 0){ //Harima:Trabajando con aguinaldos
        $Dato="NOP|999|P24GA|GRATIFICACIÓN DE FIN DE AÑO|".number_format($row_srcSQL["OTROS_PAGOS"],2,".","")."||||\r";
        fwrite($fh,$Dato.PHP_EOL);
	}*/

	if($TipoHonorario == 'E023'){
		$Dato="NOP|999|999|Subsidio para el empleo (efectivamente entregado al trabajador)|0.00||||\r";
		fwrite($fh,$Dato.PHP_EOL);
	}
	
    $Dato="ADO|74|ISA961203QN5|0|Chiapas\r";
    fwrite($fh,$Dato.PHP_EOL);

    $Dato="AEN|".$Adenda."\r";
    fwrite($fh,$Dato.PHP_EOL);

?>