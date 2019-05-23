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
                        <form id="formulario_nomina" action="{{url('api/exportar_query_excel')}}" target="_blank" method="post" >
                            <div class="form-group"> 
                                <label>Nombre Archivo:</label> 
                                <input type="text" name="nombre_archivo" class="form-control">
                            </div>
                            <div class="form-group"> 
                                <label>Query:</label> 
                                <textarea name="query" class="form-control" rows="10"></textarea>
                            </div>
                            <div class="form-row">
                                <div class="col-10"></div>
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