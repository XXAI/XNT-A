<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/importar_nomina', 'ImportarController@parseNomina');

Route::post('/importar_dbf', 'ImportarDBFController@parseDBF');

Route::post('/homologar_formato', 'HomologarFormatosController@cargarDBF');

Route::post('/exportar_query_excel', 'ExportarQueriesController@exportarExcel');

Route::post('/importar_archivo_csv','DatosTimbradoController@cargarArchivo');

Route::post('/dividir_xml','DividirXMLController@dividirXML');

Route::get('/obtener_lista_archivos_temporales','ArchivosTemporalesController@obtenerListaDeArchivos');
Route::get('/eliminar_archivos_temporales','ArchivosTemporalesController@eliminarArchivosTemporales');