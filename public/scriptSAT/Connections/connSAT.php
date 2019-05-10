<?php
date_default_timezone_set('America/Mexico_City');
# FileName="Connection_php_mysql.htm"
# Type="MYSQL"
# HTTP="true"
$hostname_connNomina = "5.196.110.172";
#$hostname_connNomina = "localhost";
$database_connNomina = "nomina_reportes"; //uptics_pymegral
$username_connNomina = "nomina"; //uptics_pymegral
$password_connNomina = "n0m1n4.2019"; //d&=qO4ErJhUH
#$password_connNomina = "";

$public_path = '/usr/local/sistemas/apis/nomina/public/';
//$public_path = 'C:/laragon/www/reportes-nomina/public/';

$mysqli = new mysqli($hostname_connNomina, $username_connNomina, $password_connNomina, $database_connNomina);
if ($mysqli->connect_errno) {
    trigger_error('Database connection failed: '  . $mysqli->connect_error, E_USER_ERROR);
    exit();
}

$insertSQL = 'set names utf8';
$Result1 = $mysqli->query($insertSQL) or die($mysqli->error.__LINE__);


?>