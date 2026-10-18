@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Sincronización de Facturas</h3>
                    <a href="{{ route('empresa_sincronizar_factura.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> Nueva Sincronización
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Filtros de Control superiores -->
                    <div class="row mb-4">
                        <div class="col-md-8">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-outline-warning active">
                                    <input type="radio" name="filtro_estado" value="pendiente" checked> Pendientes
                                </label>
                                <label class="btn btn-outline-info">
                                    <input type="radio" name="filtro_estado" value="procesando"> Procesando
                                </label>
                                <label class="btn btn-outline-success">
                                    <input type="radio" name="filtro_estado" value="completado"> Completados
                                </label>
                                <label class="btn btn-outline-danger">
                                    <input type="radio" name="filtro_estado" value="error"> Con Error
                                </label>
                            </div>
                        </div>
                        <div class="col-md-4 text-right">
                            <!-- Checkbox para incluir eliminados lógicamente -->
                            <div class="custom-control custom-checkbox custom-control-inline pt-2">
                                <input type="checkbox" class="custom-control-input" id="incluir_eliminados" value="true">
                                <label class="custom-control-label font-weight-bold text-muted" id="label_eliminados" for="incluir_eliminados">
                                    <i class="fa fa-trash-restore text-danger"></i> Incluir Eliminados
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla Principal -->
                    <div class="table-responsive">
                        <table id="tabla_sincronizaciones" class="table table-bordered table-striped w-100">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Token / URL</th>
                                    <th>Company ID</th>
                                    <th>Estructura JSON</th>
                                    <th>Factura Fiscal</th>
                                    <th>Estado</th>
                                    <th>Mensaje Error</th>
                                    <th>Acciones</th>
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

@section('js')
<script>
$(document).ready(function() {
    // Inicialización de la Tabla con DataTables
    var table = $('#tabla_sincronizaciones').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "{{ route('empresa_sincronizar_factura.index') }}",
            data: function (d) {
                // Pasamos dinámicamente las variables elegidas en los filtros superiores
                d.filtrado = $('input[name="filtro_estado"]:checked').val();
                d.trashed = $('#incluir_eliminados').is(':checked') ? 'true' : 'false';
            }
        },
        columns: [
            { data: 'id', name: 'id' },
            { 
                data: null, 
                name: 'token',
                render: function(data) {
                    return '<strong>URL:</strong> <span class="text-muted">' + data.url + '</span><br>' +
                           '<strong>Token:</strong> <small class="text-monospace">' + data.token + '</small>';
                }
            },
            { data: 'company_id', name: 'company_id', className: 'text-center' },
            { data: 'info_json', name: 'info_json', orderable: false, searchable: false },
            { data: 'factura_fiscal', name: 'factura_fiscal', defaultContent: '<span class="text-muted">N/A</span>' },
            { data: 'status_badge', name: 'estado', className: 'text-center' },
            { 
                data: 'mensaje_error', 
                name: 'mensaje_error',
                render: function(data) {
                    return data ? '<span class="text-danger small">' + data + '</span>' : '<span class="text-muted small">-</span>';
                }
            },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[0, 'desc']], // Ordenar por ID desc por defecto
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json" // Idioma español
        }
    });

    // Recargar la tabla automáticamente cuando cambie el estado del botón de radio
    $('input[name="filtro_estado"]').change(function() {
        table.draw();
    });

    // Recargar la tabla automáticamente cuando se alterne el checkbox de eliminados
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