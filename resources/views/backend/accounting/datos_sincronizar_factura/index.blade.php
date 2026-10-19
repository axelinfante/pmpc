@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ _lang('Sincronización de Cotizaciones') }}</h3>
                    <!--<a href="{{ route('datos_sincronizar_factura.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> {{ _lang('Nueva Sincronización') }}
                    </a>-->
                </div>
                
                <div class="card-body">
                    <!-- Barra de Filtros Dinámicos -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-outline-warning active">
                                    <input type="radio" name="filtro_estado" value="pendiente" checked> {{ _lang('Pendientes') }}
                                </label>
                                <label class="btn btn-outline-info">
                                    <input type="radio" name="filtro_estado" value="procesando"> {{ _lang('Procesando') }}
                                </label>
                                <label class="btn btn-outline-success">
                                    <input type="radio" name="filtro_estado" value="completado"> {{ _lang('Completados') }}
                                </label>
                                <label class="btn btn-outline-danger">
                                    <input type="radio" name="filtro_estado" value="error"> {{ _lang('Con Error') }}
                                </label>
                            </div>
                        </div>
                        <!--<div class="col-md-4 text-right">
                            <div class="custom-control custom-checkbox custom-control-inline pt-2">
                                <input type="checkbox" class="custom-control-input" id="incluir_eliminados" value="true">
                                <label class="custom-control-label font-weight-bold text-muted" id="label_eliminados" for="incluir_eliminados" style="cursor: pointer;">
                                    <i class="fa fa-trash-restore text-danger"></i> {{ _lang('Incluir Eliminados') }}
                                </label>
                            </div>
                        </div>-->
                    </div>

                    <!-- Estructura de la Tabla -->
                    <div class="table-responsive">
                        <table id="tabla_sincronizaciones" class="table table-bordered table-striped w-100">
                            <thead>
                                <tr>
                                    <th>{{ _lang('Cotizaciones') }}</th>
                                    <th>{{ _lang('Comprobante Afip') }}</th>
                                    <th>{{ _lang('Estado') }}</th>
                                    <th>{{ _lang('Mensaje Sincronización') }}</th>
                                    <th class="text-center">{{ _lang('Acciones') }}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js-script')
<script>
$(document).ready(function() {
    // Configuración y mapeo del servidor con DataTables
    var table = $('#tabla_sincronizaciones').appTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "{{ route('datos_sincronizar_factura.index') }}",
            data: function (d) {
                // Inyectamos las variables dinámicas de los filtros a la petición AJAX
                d.filtrado = $('input[name="filtro_estado"]:checked').val();
                d.trashed = $('#incluir_eliminados').is(':checked') ? 'true' : 'false';
            }
        },
        columns: [
            { data: 'invoice_id', name: 'invoice_id', className: 'text-center font-weight-bold' },
            { 
                data: 'factura_fiscal', 
                name: 'factura_fiscal', 
                defaultContent: '<span class="text-muted small">N/A</span>' 
            },
            { data: 'status_badge', name: 'estado', className: 'text-center' },
            { 
                data: 'mensaje_error', 
                name: 'mensaje_error'
               /*  render: function(data) {
                    return data ? '<span class="text-danger small">' + data + '</span>' : '<span class="text-muted small">-</span>';
                } */
            },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[0, 'desc']], // Ordenar por ID descendente por defecto
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    });

    // Escuchar el cambio en los botones de estado para redibujar la tabla
    $('input[name="filtro_estado"]').change(function() {
        table.draw();
    });

    // Escuchar el cambio en el checkbox de la papelera
    $('#incluir_eliminados').change(function() {
        if ($(this).is(':checked')) {
            $('#label_eliminados').removeClass('text-muted').addClass('text-danger');
        } else {
            $('#label_eliminados').removeClass('text-danger').addClass('text-muted');
        }
        table.draw();
    });
});
</script>
@endsection
