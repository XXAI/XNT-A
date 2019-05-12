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
                <h1 class="display-8">Generar - Tra</h1>
            </div>
            <div class="container">
                <div class="row">
                    <div class="col">
                        <form id="formulario_nomina" action="api/importar_dbf" target="_blank" method="post" enctype="multipart/form-data" >
                            <div class="form-group"> 
                                <label>Archivo DBF</label>                     <input class="form-control-file" type="file" name="archivo_dbf" accept=".dbf"/>       
                                <small id="dbf_ayuda" class="form-text text-muted"></small>
                            </div>
                            <hr/>
                            <div class="form-row">
                                <div class="col-6"></div>
                                <div class="col-2">
                                <a href='index.php'><button class="btn btn-info" type="button">Regresar al formulario principal</button></a>
                                </div>
                                <div class="col-2">
                                    <button class="btn btn-default" type="reset">Limpiar Formulario</button>
                                </div>
                                <div class="col-2">
                                    <button class="btn btn-primary" type="submit">Generar Tra</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <br>
        </div>
        
    </body>
</html>