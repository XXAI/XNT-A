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
                <h1 class="display-8">Homologar Formatos de nomina</h1>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col">
                        <form id="formulario_nomina" action="/reportes-nomina/public/api/homologar_formato" target="_blank" method="post" enctype="multipart/form-data" >
                            <div class="form-group"> 
                                <label>Nomina</label> 
                                <select name="identificador_nomina" class="form-control">
                                    <option value='sin_id' selected="selected">Seleccione una opción </option>
                                    <option value='FEDERAL' >       Federal       </option>
                                </select>
                            </div>
                            <div class="form-group"> 
                                <label>Archivo DBF</label>                     <input class="form-control-file" type="file" name="archivo_dbf" accept=".dbf"/>
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