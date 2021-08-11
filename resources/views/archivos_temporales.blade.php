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
        <script src="{{asset('js/archivos_temporales/archivos_temporales.js')}}"></script>
    </head>
    <body>
        <div>
            <div class="jumbotron">
                <h1 class="display-8">Archivos Temporales</h1>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col-3"></div>
                    <div class="col">
                        <table class="table table-bordered table-sm" id="lista-archivos-temporales">
                            <thead>
                                <tr>
                                    <th scope="col">Titulo</th>
                                    <th scope="col" width="1">Directorios</th>
                                    <th scope="col" width="1">Archivos</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col"><input type="password" class="form-control" id="contrasena-permiso" placeholder="Contraseña"></div>
                            <div class="col"><button type="button" class="btn btn-danger" id="btn-borrar-archivos" onClick="borrarArchivos()">Borrar Archivos</button></div>
                            <div class="col-3"></div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-12 alert alert-danger" style="display:none;" id="error-message"><span></span></div>
                            <div class="col-12 alert alert-success" style="display:none;" id="success-message"><span></span></div>
                            <div class="col-12 alert alert-info" style="display:none;" id="loading-message"><span></span></div>
                        </div>
                    </div>
                    <div class="col-3"></div>
                </div>
            </div>
            <br>
        </div>
        <script type="text/javascript">
            var url_archivos = "{{ url('api/obtener_lista_archivos_temporales') }}";
            var url_borrar_archivos = "{{ url('api/eliminar_archivos_temporales') }}";
        </script>
    </body>
</html>