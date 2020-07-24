<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>
        <!-- Fonts -->
        <link rel="stylesheet" href="{{asset('bootstrap/css/bootstrap.min.css')}}">
        <script src="{{asset('bootstrap/js/bootstrap.min.js')}}"></script>
    </head>
    <body>
        <div>
            <div class="jumbotron">
                <h1 class="display-8">Generar - Reporte - Nomina</h1>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col">
                        <form id="formulario_nomina" action="api/importar_nomina" target="_blank" method="post" enctype="multipart/form-data" >
                            <div class="form-row">
                                <div class="form-group col-4"> <label>Tipo de año</label> <input class="form-control" type="text" name="tipo_anio" value="(A/C)"/> </div>
                                <div class="form-group col-4"> <label>Quincena</label> <input class="form-control" type="text" name="quincena" value="01"/> </div>
                                <div class="form-group col-4"> <label>Año</label> <input class="form-control" type="text" name="anio" value="2020"/> </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-6"> <label>Unidad Responsable</label> <input class="form-control" type="text" name="unidad_responsable" value="CON"/> </div>
                                <div class="form-group col-6"> <label>Nombre del achivo</label> <input class="form-control" type="text" name="nombre_archivo" value="Reporte Nomina"/> </div>
                            </div>
                            <hr/>
                            <div class="form-group"> 
                                <label>Nomina</label> 
                                <select name="identificador_nomina" class="form-control">
                                    <option value='sin_id' selected="selected">Seleccione una opcion </option>
                                    <option value='lisandro' >       Lisandro       </option>
                                    <option value='walter' >         Walter         </option>
                                    <option value='bety' >           Bety           </option>
                                    <option value='homologados' >    Homologados    </option>
                                    <option value='formalizados' >   Formalizados   </option>
                                    <option value='precarios' >      Precarios      </option>
                                    <option value='mandos_medios' >  Mandos Medios  </option>
                                    <option value='pac' >            PAC            </option>
                                    <option value='san_agustin' >    San Agustin    </option>
                                    <option value='caravanas' >      Caravanas    </option>
                                </select>
                            </div>
                            <div class="form-group"> 
                                <label>Archivo DBF</label>                     <input class="form-control-file" type="file" name="archivo_dbf" accept=".dbf"/>       
                                <small id="dbf_ayuda" class="form-text text-muted">Para las nominas de Homologados, Mandos Medios, PAC y San Agustin no es necesario cargar el archivo DBF</small>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-6"> <label>Archivo TRA (ordinario)</label>         <input class="form-control-file" type="file" name="archivo_tra" accept=".tra"/>      <a href='importar'>Convertir dbf a tra</a> </div>
                                <div class="form-group col-6"> <label>Archivo TRA (extraordinario)</label>    <input class="form-control-file" type="file" name="archivo_tra_ex" accept=".tra"/>    </div>
                            </div>
                            <hr/>
                            <div class="form-row">
                                <div class="col-8"></div>
                                <div class="col-2">
                                    <button class="btn btn-default" type="reset">Limpiar Formulario</button>
                                </div>
                                <div class="col-2">
                                    <button class="btn btn-primary" type="submit">Generar Excel</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <br>
        </div>
        <script type="text/javascript">
            /*var myForm = document.getElementById('formulario_nomina');
            myForm.onsubmit = function() {
                var w = window.open('about:blank','Popup_Window','toolbar=0,scrollbars=0,location=0,statusbar=0,menubar=0,resizable=0,width=400,height=300,left = 312,top = 234');
                this.target = 'Popup_Window';
            };*/
        </script>
    </body>
</html>