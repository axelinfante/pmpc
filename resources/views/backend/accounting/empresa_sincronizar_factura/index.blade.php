@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ _lang('Configuración Sincronización') }}</h3>
					@can('crear-configuracion-facturacion') 
                    <a href="{{ route('empresa_sincronizar_factura.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa fa-plus"></i> {{ _lang('Nueva Configuración') }}
                    </a>
					 @endcan 	
                </div>
                
                <div class="card-body">
                    <!-- Filtros de Control -->
                    <div class="row mb-4">
                        <div class="col-12 text-right">
                            <!-- Checkbox para incluir eliminados lógicamente -->
                            <div class="custom-control custom-checkbox custom-control-inline">
                                <input type="checkbox" class="custom-control-input" id="incluir_eliminados" value="true">
                                <label class="custom-control-label font-weight-bold text-muted" id="label_eliminados" for="incluir_eliminados" style="cursor: pointer;">
                                    <i class="fa fa-trash-restore text-danger"></i> {{ _lang('Incluir Eliminados') }}
                                </label>
                            </div>
                        </div>
                    </div>
                    <!-- Estructura de la Tabla -->
                    <div class="table-responsive">
                        <table id="tabla_sincronizaciones" class="table table-bordered table-striped w-100">
                            <thead>
                                <tr>
                                    <th>{{ _lang('URL de Sincronización') }}</th>
                                    <th>{{ _lang('Token') }}</th>
                                    <th>{{ _lang('Company ID') }}</th>
                                    <th>{{ _lang('Estado') }}</th>
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
    var table = $('#tabla_sincronizaciones').appTable({
        processing: true,
        serverSide: true,
        responsive: true,
        ajax: {
            url: "{{ route('empresa_sincronizar_factura.index') }}",
            data: function (d) {
                d.trashed = $('#incluir_eliminados').is(':checked') ? 'true' : 'false';
            }
        },
        columns: [
            { 
                data: 'url', 
                name: 'url',
                render: function(data) {
                    return '<span class="text-primary">' + data + '</span>';
                }
            },
            { 
                data: 'token', 
                name: 'token',
                render: function(data) {
                    return '<small class="text-monospace text-truncate d-inline-block" style="max-width: 250px;" title="' + data + '">' + data + '</small>';
                }
            },
            { data: 'company_id', name: 'company_id', className: 'text-center' },
            { data: 'status_badge', name: 'deleted_at', className: 'text-center', orderable: false, searchable: false },
            { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
        ],
        order: [[0, 'desc']],
        language: {
            url: "//cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
        }
    });

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