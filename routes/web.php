<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/importar', function () {
    return view('importardbf');
});

Route::get('/homologar_formatos', function () {
    return view('homologar_formatos');
});

Route::get('/exportar_query', function(){
    return view('exportar_query');
});

Route::get('/importar_archivo_csv', function(){
    return view('importar_archivo_csv');
});

Route::get('/dividir_timbrado', function () {
    $query = sprintf("SELECT DISTINCT TABLE_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE COLUMN_NAME IN ('NOMBRE_NOMINA','PER_GRAVADA','PERIODICIDAD','mmFolio') AND TABLE_SCHEMA='%s';", env('DB_DATABASE'));
    $tablas = DB::select($query);
    
    return view('dividir_xmls',['datos'=>['tablas'=>$tablas]]);
});