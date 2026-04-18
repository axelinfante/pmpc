@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">

            <div class="card mt-2">
                <span class="panel-title d-none">{{ _lang('Tramitadores') }}</span>
                <div class="card-body">
                    <div class="row" style="opacity: 0.5;pointer-events: none;">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Company') }}</label>
                                <select id="company" name="company" class="form-control">
                                    <option value="">Seleccionar</option>
                                    @foreach ($cias as $cia)
                                        @if ($cia->business_name == 'Pentacar' || $cia->business_name == 'Paternal')
                                            <option {{ $car->company_id == $cia->id ? 'selected' : '' }}
                                                value="{{ $cia->id }}">
                                                {{ $cia->business_name }}</option>
                                        @endif
                                    @endforeach

                                </select>
                                <input id="vehiculo_id" type="hidden" value="{{ $car->id }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                @php
                                    $nro_interno = $car->company_id == 1 ? 'PM-' : 'PC-';
                                @endphp

                                <label class="control-label">{{ _lang('Nº interno') }}</label>
                                <input type="text" class="form-control" name="nro_interno"
                                    value={{ $nro_interno . $car->id }}>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Fecha Asignacion') }}</label>
                                <input type="date" class="form-control" name="fecha_asignacion"
                                    value="{{ old('fecha_asignacion', $car->fecha_asignacion) }}">
                            </div>

                        </div>
                        <div class="col-md-2" style="opacity: 0.5;pointer-events: none;">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Tramitador') }}</label>
                                <select class="form-control select2" data-value="users.id" data-display="users.name"
                                    data-table="users" data-where="7" name="idTramitador">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    @forelse($tramitadores as $tramit)
                                        <option {{ $tramit->id == ($car->tramitador->id ?? '') ? 'selected' : '' }}
                                            value="{{ $tramit->id }}">{{ $tramit->name }}</option>
                                    @empty
                                    @endforelse
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2" style="opacity: 0.5;pointer-events: none;">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Tramitador de compañia') }} </label>
                                <input type="text" class="form-control" name="tramitador_compania"
                                    value="{{ $car->tramitador_compania }}">
                            </div>
                        </div>
                        <div class="col-md-2 position-relative">
                            <div id="alertEstado" class="alert text-center" role="alert" style="position: absolute; top: 0; left: 0; width: 100%; font-size: 20px; background-color: rgba(255, 255, 255, 0.9); z-index: 999;">
                                {{ explode(':',$estadofinal)[1] }}
                            </div>
                        </div>
                        

                    </div>
                    <div class="row">


                        <div class="col-md-2" style="opacity: 0.5;pointer-events: none;">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Dominio') }} </label>
                                <input type="text" class="form-control" name="dominio" value="{{ $car->dominio }}">
                            </div>
                        </div>
                        <div class="col-md-2" style="opacity: 0.5;pointer-events: none;">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Siniestro') }} </label>
                                <input type="text" class="form-control" name="siniestro" value="{{ $car->siniestro }}">
                            </div>
                        </div>
                        <div class="col-md-2" style="opacity: 0.5;pointer-events: none;">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Aseguradora') }} </label>
                                <input type="text" class="form-control" name="aseguradora"
                                    value="{{ $car->aseguradora->nombre ?? ''}}">
                            </div>
                        </div>

                        <div class="col-md-2" style="opacity: 0.5;pointer-events: none;">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Titular') }} </label>
                                <input type="text" class="form-control" name="asegurado"
                                    value="{{ old('asegurado', $car->asegurado) }}">
                            </div>
                        </div>

                        <div class="col-md-2" style="opacity: 0.5;pointer-events: none;">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Contacto') }} </label>
                                <input type="text" class="form-control" name="contacto"
                                    value="{{ old('contacto', $car->contacto) }}">
                            </div>
                        </div>

                    </div>
                    <div class="row" style="opacity: 0.5;pointer-events: none;">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Marca') }}</label>
                                <select class="form-control select2-ajax" data-value="id" data-display="marca"
                                    data-table="marcas" data-where="" id="marca" readonly>
                                    <option value="">{{ _lang('Select One') }}</option>
                                    @forelse($marcas as $marca)
                                        <option {{ $marca->id == ($car->marca_modelo->marca->id ?? '') ? 'selected' : '' }}
                                            value="{{ $marca->id }}">{{ $marca->marca }}
                                        </option>
                                    @empty
                                    @endforelse
                                </select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Modelo') }}</label>
                                <select class="form-control select2" id="modelo">
                                    <option value="">{{ _lang('Select One') }}</option>

                                </select>
                                <input id="modelo_id" type="hidden" value="{{ $car->marca_modelo->modelo->id ?? '' }}">
                                <input type="hidden" name="marca_modelo" id="marca_modelo"
                                    value="{{ $car->idMarca_modelo }}">

                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('No. Motor') }} </label>
                                <input type="text" class="form-control" name="motor_nro"
                                    value="{{ $car->motor_nro }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('No.Chasis') }} </label>
                                <input type="text" class="form-control" name="chasis" value="{{ $car->chasis }}">
                            </div>
                        </div>
                        <div class="col-md-2 ">
                            <div class="form-group">
                                <label class="control-label">{{ _lang('Tipo de baja') }} </label>

                                <select class="form-control" name="tipo_baja">
                                    <option value="">{{ _lang('Select One') }}</option>
                                    @forelse($tipo_baja as $key => $value)
                                        <option {{ old('tipo_baja', $car->tipo_baja) == $key ? 'selected' : '' }}
                                            value="{{ $key }}">{{ $value }}</option>
                                    @empty
                                    @endforelse
                                </select>
                            </div>

                        </div>
                        <div class="col-md-2">
                            <div class="form-group pt-3">

                                <input {{ $car->no_drnpa == 1 ? 'checked' : '' }} type="checkbox" name="no_drnpa"
                                    value="1">
                                <label class="control-label">No requiere enviar al DRNPA </label>
                            </div>
                        </div>

                    </div>

                    <div class="row">
                        {{--  <div class="col-md-3 ">
                            <a class="btn btn-primary btn-xs ajax-modal" data-title="{{ _lang('Agregar Checkpoints') }}"
                                href="{{ route('checkpoints.index') }}"><i class="ti-plus"></i>
                                {{ _lang('Agregar Checkpoints') }}</a>
                        </div> --}}
                        <div class="col-md-2 ">
                            <a class="btn btn-primary btn-xs" data-title="{{ _lang('Volver') }}"
                                href="{{ route('tramitadores.index') }}"><i class="ti-back"></i>
                                {{ _lang('Volver') }}</a>
                        </div>

                        <div class="col-md-3 ">
                            {!! $filemanager !!}
                        </div>
                        <div class="col-md-3">
                            {!! $movimientos !!}
                        </div>
                        <div class="col-md-3">
                            {!! $multimedia !!}
                        </div>

                    </div>
                    <br>
                    <div class="row">


                        <div class="col-md-12 ">
                            <table id="checkpoints_vehiculos_table" class="table table-bordered w-100">
                                <thead>
                                    <tr>
                                        <th>No.</th>
                                        <th>{{ _lang('Nombre') }}</th>
                                        {{-- <th>{{ _lang('Fecha Inicio') }}</th> --}}
                                        <th>{{ _lang('Estado Actual') }}</th>
                                        {{-- <th>{{ _lang('Observaciones') }}</th> --}}
                                        <th>{{ _lang('Usuario') }}</th>
                                        <th>{{ _lang('Actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js-script')
    {{-- <script src="https://code.jquery.com/jquery-3.5.1.js"></script> --}}
    {{-- <script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script> --}}
    {{-- <script src="https://cdn.datatables.net/fixedheader/3.2.3/js/dataTables.fixedHeader.min.js"></script> --}}
    {{-- <script src="{{ asset('public/backend/assets/js/ajax-datatable/tramitador.js') }}"></script> --}}
    <script>
        let ejecuting = false;
        let checkpoint_list_table = false;
        let vehiculo_id = $('#vehiculo_id').val();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $(document).ready(function() {
            let marca = $('#marca');
            let modelo = $('#modelo');
            let modelo_id = $('#modelo_id').val();
            let marca_modelo = $('#marca_modelo');

            marca.change(function() {
                modelo.html(`<option value="">{{ _lang('Select One') }}</option>`);
                $.ajax({
                    url: "{{ route('modelosByMarca') . '/' }}" + marca.val(),
                    dataType: 'json',
                    success: function(res) {
                        console.log(res);
                        let html = `<option value="">{{ _lang('Select One') }}</option>`;
                        res.map(r => {
                            if (modelo_id == r.idModelo) {

                                html +=
                                    `<option selected value="${r.idModelo}">${r.modelo.modelo}</option>`;
                            } else {
                                html +=
                                    `<option value="${r.idModelo}">${r.modelo.modelo}</option>`;
                            }

                        })
                        modelo.html(html);
                    }

                })
            });




            $('#checkpoints thead tr')
                .clone(true)
                .addClass('filters')
                .appendTo('#checkpointss thead');


            $('#checkpoints thead tr:eq(1) th').each(function(i) {
                var title = $(this).text();
                $(this).html('<input type="text" placeholder="Search...' + title + '" />');
                $('input', this).on('change', function() {
                    if (checkpoints_table.column(i).search() !== this.value) {
                        checkpoints_table
                            .column(i)
                            .search(this.value)
                            .draw();
                    }
                });
            });



            var checkpoints_table = $('#checkpoints').DataTable({
                // dom: 'Bfrtip',
                orderCellsTop: true,
                fixedHeader: true,
                scrollX: true,

            });



            marca.change();

            $(document).on('click', '#add_to_list', function(e) {
                e.preventDefault();
                let checkpoint = $('#checkpoint').val();

                $.ajax({
                    url: "{{ route('checkpoints.store') }}",
                    method: "POST",
                    data: {
                        'checkpoint': checkpoint,
                    },
                    success: function(res) {

                        var currentPage = checkpoint_list_table.page();
                        checkpoint_list_table.ajax.reload(function() {
                            checkpoint_list_table.page(currentPage).draw(false);
                        });
                    }

                })

            });


            $(document).on('click', '.marca_checkpoint', function(e) {
                e.preventDefault();
                if (!ejecuting) {
                    ejecuting = true;

                    let checkpoint_id = $(this).attr('data-id');
                    let vehiculo_id = $(this).attr('data-vehiculo_id');
                    let valor = $(this).is(':checked') ? 1 : 0;

                    $.ajax({
                        url: "{{ route('tramitadores.store-checkpoint') }}",
                        method: "POST",
                        data: {
                            'checkpoint_id': checkpoint_id,
                            'vehiculo_id': vehiculo_id,
                            'valor': valor
                        },
                        success: function(res) {
                            ejecuting = false;
                            //  checkpoint_list_table.ajax.reload();
                            var currentPage = checkpoint_list_table.page();
                            checkpoint_list_table.ajax.reload(function() {
                                checkpoint_list_table.page(currentPage).draw(false);
                            });


                        },
                        complete: function(jqXHR, textStatus) {
                            ejecuting = false;
                        }

                    })
                }

            });

            let checkpoints_vehiculos_table = $('#checkpoints_vehiculos_table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('checkpoints_vehiculos.get_table_data') }}",
                    type: 'POST',
                    data: {
                        'vehiculo_id': vehiculo_id
                    },
                },
                pageLength: 25,
                lengthMenu: [25, 30, 50, 100],
                columns: [{
                        data: 'numero',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'nombre',
                        orderable: false,
                        searchable: false
                    },
                    // {
                    //     data: 'fecha_inicio',
                    //     orderable: false,
                    //     searchable: false
                    // },
                    {
                        data: 'status',
                        orderable: false,
                        searchable: false
                    },
                    // {
                    //     data: 'observaciones',
                    //     orderable: false,
                    //     searchable: false
                    // },
                    {
                        data: 'user',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false
                    },
                ],
                responsive: true,
                bStateSave: true,
                autoWidth: false, // Importante para evitar el ancho automático
                ordering: false,
                language: {
                    decimal: "",
                    emptyTable: $lang_no_data_found,
                    info: $lang_showing + " _START_ " + $lang_to + " _END_ " + $lang_of + " _TOTAL_ " +
                        $lang_entries,
                    infoEmpty: $lang_showing_0_to_0_of_0_entries,
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    thousands: ",",
                    lengthMenu: $lang_show + " _MENU_ " + $lang_entries,
                    loadingRecords: $lang_loading,
                    processing: $lang_processing,
                    search: $lang_search,
                    zeroRecords: $lang_no_matching_records_found,
                    paginate: {
                        first: $lang_first,
                        last: $lang_last,
                        next: $lang_next,
                        previous: $lang_previous
                    }
                },
                initComplete: function(settings, json) {
                    // Ajusta el ancho de las columnas después de la inicialización
                    $(this.api().columns().header()).css('min-width', '0');
                    $(this.api().table().container()).css('width', '100%');
                }
            });


            $(document).on('click', '#save04d', function(e) {
                e.preventDefault();

                // Crear un nuevo objeto FormData para enviar los archivos
                var formData = new FormData();

                // Obtener los archivos del input file
                var files = $('#imagen_recepcion')[0].files;

                // Verificar si se han seleccionado archivos
                // if (files.length === 0) {
                //     alert('Por favor, selecciona al menos un archivo.');
                //     return;
                // }


                $('input[name="imgDeleteRecepcion[]"]:checked').each(function() {
                    formData.append('imgDeleteRecepcion[]', $(this).val());
                });


                // Agregar cada archivo a formData
                for (var i = 0; i < files.length; i++) {
                    formData.append('imagen_recepcion[]', files[i]);

                }
                formData.append('id', vehiculo_id);

                // Realizar la solicitud AJAX
                $.ajax({
                    url: "{{ route('tramitadores.update04D') }}",
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr(
                            'content') // Para Laravel, añade el token CSRF
                    },
                    success: function(response) {
                        alert('Imágenes actualizadas exitosamente.');
                        $('#main_modal').modal('hide');

                    },
                    error: function(xhr, status, error) {
                        console.error(xhr.responseText);
                        alert('Ocurrió un error al subir las imágenes.');
                    }
                });
            });
            var estadoFinal = "{{ $estadofinal }}"; 
            var estado = estadoFinal.split(':')[0].trim(); 

            var alerta = $('#alertEstado');

            switch (estado.toLowerCase()) {
                case 'finalizado':
                    alerta.css('background-color', 'rgb(255, 196, 51)');
                    alerta.css('color', '#000'); 
                    break;
                case 'en gestoria':
                    alerta.css('background-color', 'rgb(51, 168, 255)');
                    alerta.css('color', '#fff'); 
                    break;
                case 'en proceso':
                    alerta.css('background-color', 'rgb(51, 255, 172)');
                    alerta.css('color', '#000'); 
                    break;
                case 'pendiente':
                alerta.css('background-color', 'rgb(255, 182, 193)'); // Color rojo pastel
                 alerta.css('color', '#000'); // Texto negro
            break;
                default:
                    alerta.css('background-color', 'rgb(240, 240, 240)'); // Color gris por defecto
                    alerta.css('color', '#000'); 
                    break;
            }

        });
    </script>
@endsection
