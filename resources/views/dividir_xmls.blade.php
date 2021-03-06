<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laravel</title>
        <!-- Fonts -->
        <link rel="stylesheet" href="{{asset('bootstrap/css/bootstrap.min.css')}}">
        <script src="{{asset('js/jquery-3.4.1.min.js')}}"></script>
        <script src="{{asset('bootstrap/js/bootstrap.min.js')}}"></script>
    </head>
    <body>
        <div>
            <div class="jumbotron">
                <h1 class="display-8">Dividir XMLs por clues</h1>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col">
                        <form id="formulario_nomina" action="{{url('api/dividir_xml')}}" target="_blank" method="post" enctype="multipart/form-data" >
                            <div class="form-group"> 
                                <label>Dividir por:</label> 
                                <select name="orden_carpetas" class="form-control">
                                    <option value='C-N-X' selected="selected">Clues->Nomina->XMLs</option>
                                    <option value='N-C-X'>  Nomina->Clues->XMLs </option>
                                    <option value='C-X'>    Clues->XMLs         </option>
                                    <option value='N-X'>    Nomina->XMLs        </option>
                                </select>
                            </div>
                            <div class="form-group"> 
                                <label>Nombre de la Tabla:</label> 
                                <select name="nombre_tabla" class="form-control" >
                                    @foreach($datos['tablas'] as $tabla)
                                    <option value='{{$tabla->TABLE_NAME}}'>{{$tabla->TABLE_NAME}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <!--div class="form-group">
                                <label>Nombre de Tabla</label>
                                <input type="text" class="form-control" name="nombre_tabla">
                            </div-->
                            <div class="form-group"> 
                                <label>Archivo ZIP</label>
                                <input class="form-control-file" type="file" name="archivo_zip" accept=".zip"/>
                            </div>
                            <hr/>
                            <div class="form-row">
                                <div class="col-8"></div>
                                <div class="col-2">
                                    <button class="btn btn-default" type="reset">Limpiar Formulario</button>
                                </div>
                                <div class="col-2">
                                    <button class="btn btn-primary" type="submit">Enviar</button>
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