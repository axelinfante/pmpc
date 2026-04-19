<form method="post" class="ajax-submit" action="{{ route('certificado') }}">
    @csrf
    <div class="card">
        <div class="card-body">
            <table class="table table-bordered">
                <tr>
                    <td>{{ _lang('Dominio') }}</td>
                    <td><input disabled class="form-control" type="text" name="dominio" id="dominio"
                            value="{{ $car->dominio ?? '' }}"></td>
                </tr>
                <tr>
                    <td>{{ _lang('Marca') }}</td>
                    <td><input disabled class="form-control" type="text" name="marca" id="marca"
                            value="{{ $car->marca_modelo->marca->marca ?? '' }}"></td>
                </tr>
                <tr>
                    <td>{{ _lang('Modelo') }}</td>
                    <td><input disabled class="form-control" type="text" name="modelo"
                            id="modelo"value="{{ $car->marca_modelo->modelo->modelo ?? '' }}"></td>
                </tr>
                <tr>
                    <td>{{ _lang('Tipo') }}</td>
                    <td><input disabled class="form-control" type="text" name="tipo" id="tipo"
                            value="{{ $car->tipo ?? '' }}"></td>
                </tr>
                <tr>
                    <td>{{ _lang('Marca Motor') }}</td>
                    <td><input disabled class="form-control" type="text" name="marca_motor" id="marca_motor"
                            value="{{ $car->marca_motor ?? '' }}"></td>
                </tr>
                <tr>
                    <td>{{ _lang('Numero Motor') }}</td>
                    <td><input disabled class="form-control" type="text" name="motor_nro" id="motor_nro"
                            value="{{ $car->motor_nro ?? '' }}"></td>
                </tr>
                <tr>
                    <td>{{ _lang('Marca Chasis') }}</td>
                    <td><input disabled class="form-control" type="text" name="marca_chasis" id="marca_chasis"
                            value="{{ $car->marca_chasis ?? '' }}"></td>
                </tr>
                <tr>
                    <td>{{ _lang('Numero Chasis') }}</td>
                    <td><input disabled class="form-control" type="text" name="chasis" id="chasis"
                            value="{{ $car->chasis ?? '' }}"></td>
                </tr>

                @if ($car->cc_impresa == 1)
                <tr>
                    <td>{{ _lang('Numero Chasis') }}</td>
                    <td><span class='badge badge-warning' style="display: block;">Esta certificación ya ha sido impresa</span></td>
                    <input type="hidden" name="impresa" id="impresa" value="1">
                </tr>
              
                @endif


            </table>

                <button id='print_certificate' class="btn btn-primary">Imprimir</button>
            
        </div>
    </div>
</form>
<script>
    $(document).ready(function() {

        $('#print_certificate').on('click', function(e) {
            e.preventDefault();
            let impresa = $('#impresa').val();
            const formData = {
                dominio: $('#dominio').val(),
                marca: $('#marca').val(),
                tipo: $('#tipo').val(),
                modelo: $('#modelo').val(),
                marca_motor: $('#marca_motor').val(),
                numero_motor: $('#motor_nro').val(),
                marca_chasis: $('#marca_chasis').val(),
                numero_chasis: $('#chasis').val(),
                _token: $('meta[name="csrf-token"]').attr('content')
            };

            if (impresa) {
                const reimprimir = confirm(
                    "La certificación ya fue confeccionada. ¿ Desea imprimir una nueva ?");
                   
                if (!reimprimir) {
                    //console.log("Proceso cancelado por el usuario.");
                    return;
                }
            }

            $.ajax({
                url: "{{ route('certificado') }}",
                type: 'POST',
                data: JSON.stringify(formData),
                contentType: 'application/json',
                xhrFields: {
                    responseType: 'blob'
                },
                success: function(data, status, xhr) {
                    const disposition = xhr.getResponseHeader('Content-Disposition');
                    const filename = disposition && disposition.match(/filename="(.+)"/) ?
                        disposition.match(/filename="(.+)"/)[1] : 'certificado.pdf';

                    const blob = new Blob([data], {
                        type: 'application/pdf'
                    });
                    const url = window.URL.createObjectURL(blob);

                    const a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = filename;
                    document.body.appendChild(a);
                    a.click();

                    window.URL.revokeObjectURL(url);
                },
                error: function(xhr, status, error) {
                    console.error('Error al generar el certificado:', error);
                    alert('Ocurrió un error al intentar generar el certificado.');
                }
            });
        });


    });
</script>
