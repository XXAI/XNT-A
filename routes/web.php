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
    return view('dividir_xmls');
});