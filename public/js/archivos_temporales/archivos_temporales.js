$(document).ready(function(){
    
    obtenerListadoArchivos();

});

function obtenerListadoArchivos(){
    $.ajax({ url: url_archivos,
        context: document.body,
        success: function(response){
            if(response.data){
                var html_items = '';
                for (const key in response.data) {
                    var item = response.data[key];
                    
                    html_items += '<tr>';
                    html_items += '<td>'+item.titulo+'</td>';
                    html_items += '<td style="text-align:center;">'+item.total_directorios+'</td>';
                    html_items += '<td style="text-align:center;">'+item.total_archivos+'</td>';
                    html_items += '</tr>';
                }
                $('#lista-archivos-temporales > tbody').html(html_items);
            }
    }});
}

function borrarArchivos(){
    $('#error-message > span').text('');
    $('#error-message').hide();
    $('#success-message > span').text('');
    $('#success-message').hide();

    $('#btn-borrar-archivos').attr('disabled','disabled');
    $('#loading-message > span').text('Borrando Archivos...');
    $('#loading-message').show();
    $.ajax({ url: url_borrar_archivos,
        context: document.body,
        data: {
            'pass':$('#contrasena-permiso').val()
        },
        type: 'GET',
        cache: false,
        success: function(response){
            $('#loading-message > span').text('');
            $('#loading-message').hide();
            $('#btn-borrar-archivos').removeAttr('disabled');

            if(response.data){
                $('#success-message > span').text(response.data);
                $('#success-message').show();
                obtenerListadoArchivos();
            }
        },
        error: function(xhr) {
            $('#loading-message > span').text('');
            $('#loading-message').hide();
            $('#btn-borrar-archivos').removeAttr('disabled');

            var response = xhr.responseJSON;
            $('#error-message > span').text(response.error);
            $('#error-message').show();
        }
    });
}

/*

<li class="list-group-item d-flex justify-content-between align-items-center">
    {{ $row['titulo'] }} <span class="badge badge-success badge-pill">Directorios: {{ $row['total_directorios'] }}</span> <span class="badge badge-primary badge-pill">Archivos: {{ $row['total_archivos'] }}</span>
</li>

*/