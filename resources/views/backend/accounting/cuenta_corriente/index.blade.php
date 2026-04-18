@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h4 class="panel-title">
                    <i class="fas fa-file-invoice-dollar"></i> {{ _lang('Cuentas Corrientes') }}
                </h4>
            </div>

            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    {{ _lang('Seleccione un contacto para ver su cuenta corriente.') }}
                </div>

                <div class="table-responsive">
                    <table id="cuentas-corrientes-table" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>{{ _lang('Contacto') }}</th>
                                <th>{{ _lang('Grupo') }}</th>
                                <th class="text-right">{{ _lang('Saldo ARS') }}</th>
                                <th class="text-right">{{ _lang('Saldo USD') }}</th>
                                <th>{{ _lang('Último Movimiento') }}</th>
                                <th>{{ _lang('Total Movimientos') }}</th>
                                <th class="text-center">{{ _lang('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Los datos se cargan via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js-script')
<script>
$(document).ready(function() {
    // Configurar jQuery para enviar token CSRF en todas las solicitudes AJAX
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // Inicializar DataTable
    var table = $('#cuentas-corrientes-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route("cuenta_corriente.get_contactos") }}',
            type: 'GET',
            data: function(d) {
                // Agregar token CSRF a la solicitud
                return $.extend({}, d, {
                    _token: $('meta[name="csrf-token"]').attr('content')
                });
            },
            xhrFields: {
                withCredentials: true
            }
        },
        columns: [
            {
                data: 'contacto',
                name: 'contacto',
                render: function(data, type, row) {
                    return '<strong>' + data + '</strong><br>' +
                           '<small>' + (row.contact_email || '') + '</small>';
                }
            },
            {
                data: 'grupo',
                name: 'grupo',
                render: function(data, type, row) {
                    return data || '';
                }
            },
            {
                data: 'saldo_peso',
                name: 'saldo_peso',
                className: 'text-right',
                render: function(data) {
                    var saldo = parseFloat(data || 0);
                    var clase = saldo >= 0 ? 'text-success' : 'text-danger';
                    return '<span class="' + clase + '">' + formatCurrency(data, 'ARS') + '</span>';
                }
            },
            {
                data: 'saldo_usd',
                name: 'saldo_usd',
                className: 'text-right',
                render: function(data) {
                    var saldo = parseFloat(data || 0);
                    var clase = saldo >= 0 ? 'text-success' : 'text-danger';
                    return '<span class="' + clase + '">' + formatCurrency(data, 'USD') + '</span>';
                }
            },
            {
                data: 'ultimo_movimiento',
                name: 'ultimo_movimiento',
                render: function(data) {
                    if (data) {
                        var date = new Date(data);
                        return date.toLocaleDateString('es-AR');
                    }
                    return 'N/A';
                }
            },
            {
                data: 'total_movimientos',
                name: 'total_movimientos',
                className: 'text-center'
            },
            {
                data: 'acciones',
                name: 'acciones',
                className: 'text-center',
                orderable: false,
                searchable: false,
                render: function(data, type, row) {
                    return data;
                }
            }
        ],
        order: [[0, 'asc']],

        dom: '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"f>>' +
             '<"row"<"col-sm-12"tr>>' +
             '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
        buttons: [
            {
                extend: 'copy',
                text: '<i class="fas fa-copy"></i> {{ _lang("Copiar") }}',
                className: 'btn btn-secondary btn-xs'
            },
            {
                extend: 'excel',
                text: '<i class="fas fa-file-excel"></i> {{ _lang("Excel") }}',
                className: 'btn btn-success btn-xs'
            },
            {
                extend: 'pdf',
                text: '<i class="fas fa-file-pdf"></i> {{ _lang("PDF") }}',
                className: 'btn btn-danger btn-xs'
            },
            {
                extend: 'print',
                text: '<i class="fas fa-print"></i> {{ _lang("Imprimir") }}',
                className: 'btn btn-info btn-xs'
            }
        ]
    });

    // Función para formatear moneda
    function formatCurrency(value, currency) {
        var number = parseFloat(value || 0);
        var symbol = currency === 'USD' ? 'US$' : '$';
        return symbol + ' ' + number.toLocaleString('es-AR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
});
</script>
@endsection

@section('styles')
<style>
    .dataTables_wrapper .dt-buttons {
        float: left;
        margin-bottom: 10px;
    }

    .dataTables_wrapper .dataTables_filter {
        float: right;
    }

    .table th {
        background-color: #f8f9fa;
        font-weight: 600;
    }

    .badge {
        font-size: 85%;
        padding: 0.4em 0.6em;
    }
</style>
@endsection
