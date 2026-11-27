@extends('layouts.app')
@section('content')
    <style type="text/css">
        #invoice-table td:nth-child(5),
        #invoice-table td:nth-child(6) {
            text-align: center !important;
        }
    </style>

    <div class="row">
        <div class="col-12">

            <div class="card mt-2">
                <span class="panel-title d-none">{{ _lang('Invoice List') }}</span>

                <div class="card-body">
                    @php $currency = currency() @endphp
                    <div class="row">
                        <div class="col-lg-3 mb-2">
                            <label>{{ _lang('Invoice Number') }}</label>
                            <input type="text" class="form-control select-filter" name="invoice_number"
                                id="invoice-number">
                        </div>

                        <div class="col-lg-3 mb-2">
                            <label>{{ _lang('Vendedores') }}</label>
                            <select class="form-control select2 select-filter" name="vendedor">
                                <option value="">{{ _lang('Vendedores') }}</option>
                                {{ create_option('users', 'id', 'name', '', ['role_id=' => $rol]) }}
                            </select>
                        </div>

                        <div class="col-lg-3 mb-2">
                            <label>{{ _lang('Revendedores') }}</label>
                            <select class="form-control select2 select-filter" name="revendedor">
                                <option value="">{{ _lang('Revendedores') }}</option>
                                {{ create_option('users', 'id', ['name', 'email'], '', ['role_id=' => $rol_revendedor]) }}
                            </select>
                        </div>

                        <div class="col-lg-3 mb-2">
                            <label>{{ _lang('Empresa') }}</label>
                            <select class="form-control select2 select-filter" name="company_id">
                                <option value="">{{ _lang('Empresa') }}</option>
                                {{ create_option('companies', 'id', 'business_name', '', ['id=' => 1, ' or id =' => 2]) }}
                                {{-- {{ list_company_entrar() }} --}}
                            </select>
                        </div>

                       <!--<div class="col-lg-3 mb-2">
                            <label>{{ _lang('Status') }}</label>
                            <select class="form-control select2 select-filter"
                                data-placeholder="{{ _lang('Invoice Status') }}" name="status" multiple="true">
                                <option value="Unpaid">{{ _lang('Unpaid') }}</option>
                                <option value="Paid">{{ _lang('Paid') }}</option>
                                <option value="Partially_Paid">{{ _lang('Partially Paid') }}</option>
                                <option value="Canceled">{{ _lang('Canceled') }}</option>
                            </select>
                        </div>-->
						
							<div class="col-lg-3 mb-2">
								<label>{{ _lang('Status') }}</label>
								<select id="filtro_status" class="form-control select2"
									data-placeholder="{{ _lang('Invoice Status') }}" 
									data-close-on-select="false"
									name="status[]" multiple="multiple">
									<option value="Unpaid">{{ _lang('Unpaid') }}</option>
									<option value="Paid">{{ _lang('Paid') }}</option>
									<option value="Partially_Paid">{{ _lang('Partially Paid') }}</option>
									<option value="Canceled">{{ _lang('Canceled') }}</option>
								</select>
							</div>

                        <div class="col-lg-3">
                            <label>{{ _lang('Date Range') }}</label>
                            <input type="text" class="form-control select-filter" id="date_range" autocomplete="off"
                                name="date_range">
                        </div>

                    </div>

                    <hr>
                    <form action="{{ route('invoices.store_comisiones_multiples') }}" method="post">
                        @if (auth()->user()->role->name == 'Gerencial' || auth()->user()->role->name == null)
                            <!--<input class="btn btn-primary my-3" type="submit" value="Pagar Comisiones">-->
                        @endif

                        @csrf
						<div style="width: 100%; padding-left: -10px;">
							<div class="table-responsive dt-responsive"> 
                        <table id="invoice-table" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>
                                        <input type="checkbox" class id="allComi">
                                    </th>
                                    <th>{{ _lang('Invoice Number') }}</th>
                                    <th>{{ _lang('Invoice To') }}</th>
                                    <th>{{ _lang('Invoice Date') }}</th>
                                    <th>{{ _lang('Fecha de entrega') }}</th>
                                    <th>{{ _lang('Fecha del ultimo pago') }}</th>
                                    <th>{{ _lang('Nro Interno') }}</th>
                                    <th>{{ _lang('Marca / Modelo') }}</th>
									<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Pieza') }}</th>
                                    <!--<th>{{ _lang('Productos') }}</th>
                                    <th>{{ _lang('id_Productos') }}</th>-->
                                    <th>{{ _lang('Vendedor') }}</th>
									{{-- <th>{{ _lang('Porcentaje de comision') }}</th>
									<th>{{ _lang('Comision') }}</th> --}}
                                    <th class="text-right">{{ _lang('Grand Total') }}</th>
									<th>{{ _lang('Monto Cobrado') }}</th>
									<th>{{ _lang('Saldo') }}</th>
									<th>{{ _lang('Entregado') }}</th>
                                    <th class="text-center">{{ _lang('Status') }}</th>
                                    <th class="text-center">{{ _lang('Observacion') }}</th>
                                    <th class="text-center act">{{ _lang('Action') }}</th>
                                </tr>
                            </thead>
                            <tbody>

                            </tbody>

                            <tfoot>
                                <tr>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                    <th></th>

                                </tr>
                            </tfoot>
                        </table>
						</div>
						</div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPieza" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel" style="font-family: 'Poppins'; font-weight: 600; color: #333;">
                    Detalles de la Pieza - Factura <span id="modal-nro-factura"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th style="font-family: 'Poppins'; color: #666; font-weight: 600;">Descripción de Información</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-piezas-cuerpo">
                        </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal" style="font-family: 'Poppins';">Cerrar</button>
            </div>
        </div>
    </div>
</div>


@endsection


@section('js-script')

<script>
(function($) {
    // 1. Clonar cabecera SIN clonar eventos previos (clone(false))
    $('#invoice-table thead tr').clone(false).appendTo('#invoice-table thead');
    
    $('#invoice-table thead tr:eq(1) th').each(function(i) {
        var title = $.trim($(this).text());

        if (i != 0 && i != 16 && i != 3 && i != 4 && i != 5 && i != 7 && i != 13) {
            $(this).html(
                '<input style="width:80px;" type="text" value="" class="form-control filtros" placeholder="Search...' + title + '" />'
            );

            $(this).find('input').off('change').on('change', function() {
                if (invoice_table.column(i).search() !== this.value) {
                    invoice_table.column(i).search(this.value).draw();
                }
            });

        } else if (i == 3 || i == 4 || i == 5) {
            // Inputs de fecha (3: invoice_date, 4: fecha_entrega, 5: fecha_pago)
            $(this).html('<input style="width:100%;" type="date" value="" class="form-control filtro-fecha" />');

            $(this).find('input').off('change').on('change', function() {
                let val = $(this).val();
                if (invoice_table.column(i).search() !== val) {
                    invoice_table.column(i).search(val ? val : '', true, false).draw();
                }
            });

        } else if (i == 13) {
            // Filtro Entregado
            $(this).html(
                '<select style="width:100%;" class="form-control filtro-entregado">' +
                    '<option value="">{{ _lang("All") }}</option>' +
                    '<option value="entregado">{{ _lang("Entregado") }}</option>' +
                    '<option value="pendiente">{{ _lang("Pendiente") }}</option>' +
                '</select>'
            );

            $(this).find('select')
                .off('click change')
                .on('click', function(e) {
                    e.stopPropagation();
                })
                .on('change', function(e) {
                    e.stopPropagation();
                    let val = $(this).val();
                    if (invoice_table.column(i).search() !== val) {
                        invoice_table.column(i).search(val ? val : '', false, false).draw();
                    }
                });
        } else {
            $(this).html('');
        }
    });

    var xhrInvoice     = null;
	var idsExportar    = [];
	var exportandoTodo = false;

   var invoice_table = $('#invoice-table').DataTable({
    processing: true,
    serverSide: true,
    orderCellsTop: true,
    fixedHeader: false,
    lengthChange: true,
    lengthMenu: [[10, 25, 50, 200], [10, 25, 50, 200]],
    dom: '<"row mb-2"<"col-sm-12 col-md-4"l><"col-sm-12 col-md-4 text-center"B><"col-sm-12 col-md-4"f>>rtip',
    language: {
        "processing": "Procesando...",
        "lengthMenu": "Mostrar _MENU_ registros",
        "zeroRecords": "No se encontraron resultados",
        "emptyTable": "Ningún dato disponible en esta tabla",
        "info": "Mostrando _START_ a _END_ de _TOTAL_ registros",
        "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
        "infoFiltered": "(filtrado de un total de _MAX_ registros)",
        "search": "Buscar:",
        "loadingRecords": "Cargando...",
        "paginate": {
            "first": "Primero",
            "last": "Último",
            "next": "Siguiente",
            "previous": "Anterior"
        }
    },
    ajax: {
        url: '{{ url("invoices/get_table_data") }}',
        method: "POST",
        beforeSend: function(jqXHR) {
            if (!exportandoTodo && xhrInvoice && xhrInvoice.readyState !== 4) {
                xhrInvoice.abort();
            }
            xhrInvoice = jqXHR;
        },
        data: function(d) {
            d._token = $('meta[name="csrf-token"]').attr('content');

            if (exportandoTodo) {
                d.exportar = 1;
                d.start = 0;
                d.length = -1;
                if (idsExportar.length > 0) {
                    d.ids_seleccionados = idsExportar;
                }
            }

            if ($('input[name=invoice_number]').val() != '') {
                d.invoice_number = $('input[name=invoice_number]').val();
            }
            if ($('select[name=vendedor]').val() != '') {
                d.vendedor = $('select[name=vendedor]').val();
            }
            if ($('select[name=revendedor]').val() != '') {
                d.revendedor = $('select[name=revendedor]').val();
            }
            if ($('select[name=company_id]').val() != '') {
                d.company_id = $('select[name=company_id]').val();
            }

            let statusSeleccionados = $('#filtro_status').val();
            if (statusSeleccionados && statusSeleccionados.length > 0) {
                d.status = JSON.stringify(statusSeleccionados);
            }

            if ($('input[name=date_range]').val() != '') {
                d.date_range = $('input[name=date_range]').val();
            }
        },
        error: function(request, status, error) {
            if (status !== 'abort') {
                console.log(request.responseText);
            }
        }
    },
    columns: [
        { data: "checkbox", name: "checkbox", orderable: false, searchable: false },
        { data: "invoice_number", name: "invoice_number" },
        { data: "contact_name", name: "contact_name" },
        { data: "invoice_date", name: "invoice_date" },
        { data: "fecha_entrega", name: "fecha_entrega" },
        { data: "fecha_pago", name: "fecha_pago" },
        { data: "nro_interno", name: "nro_interno" },
        { data: "marca_modelo", name: "marca_modelo", orderable: false, searchable: false },
        { data: "pieza", name: "pieza" },
        { data: "vendedor", name: "vendedor" },
        { data: "grand_total", name: "grand_total" },
        { data: "paid", name: "paid" },
        { data: "monto_adeudado", name: "monto_adeudado", orderable: false, searchable: false },
        { data: "entregado", name: "entregado", orderable: false, searchable: true },
        { data: "status", name: "status", visible: false },
        { data: "note", name: "note" },
        { data: "action", name: "action", orderable: false, searchable: false }
    ],
    buttons: [
        {
            text: 'Reset Filter',
            className: 'btn btn-secondary btn-sm',
            action: function(e, dt, node, config) {
                $('.filtros, .filtro-fecha, .filtro-entregado').val('');
                $('#filtro_status').val(null).trigger('change');
                statusAnterior = '[]';
                idsExportar = [];
                exportandoTodo = false;
                invoice_table.search('').columns().search('').draw();
            }
        },
        {
            extend: 'excelHtml5',
            name: 'excelNativo',
            className: 'd-none',
            title: 'Cotizaciones_' + new Date().toISOString().slice(0, 10),
            exportOptions: {
                columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15]
            }
        },
        {
            text: '<i class="fa fa-file-excel"></i> Exportar Excel',
            className: 'btn btn-success btn-sm',
            action: function(e, dt, node, config) {
                idsExportar = [];
                $('#invoice-table tbody input[name="paidComi[]"]:checked').each(function() {
                    idsExportar.push($(this).val());
                });

                exportandoTodo = true;

                dt.one('draw.dt', function() {
                    dt.button('excelNativo:name').trigger();

                    setTimeout(function() {
                        idsExportar = [];
                        exportandoTodo = false;
                        dt.ajax.reload(null, false);
                    }, 150);
                });

                dt.ajax.reload();
            }
        }
    ]
});

	
	$('#invoice-table')
    .on('preXhr.dt', function () {
         	inicioLoading();
    })
    .on('xhr.dt', function () {
        closeLoading();
    });
	
	$('#invoice-number').off('change').on('change', function(e) {
    invoice_table.draw();
});

$('.select-filter').off('change').on('change', function(e) {
    invoice_table.draw();
});

$('#date_range').daterangepicker({
    autoUpdateInput: false,
    locale: {
        format: 'DD-MM-YYYY',
        cancelLabel: 'Clear'
    }
});

$('#date_range').off('apply.daterangepicker cancel.daterangepicker')
    .on('apply.daterangepicker', function(ev, picker) {
        $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format('DD-MM-YYYY'));
        invoice_table.draw();
    })
    .on('cancel.daterangepicker', function(ev, picker) {
        $(this).val('');
        invoice_table.draw();
    });
	
// Marcar / desmarcar solo los checkboxes habilitados de comisión dentro de la tabla
$('#allComi').off('change').on('change', function() {
    let isChecked = $(this).is(':checked');
    $('#invoice-table tbody input[name="paidComi[]"]:not(:disabled)').prop('checked', isChecked);
});

// Sincronizar #allComi si se desmarca una fila individual o si cambia la página de DataTables
$('#invoice-table').on('change', 'tbody input[name="paidComi[]"]', function() {
    let totalHabilitados = $('#invoice-table tbody input[name="paidComi[]"]:not(:disabled)').length;
    let totalMarcados    = $('#invoice-table tbody input[name="paidComi[]"]:not(:disabled):checked').length;

    $('#allComi').prop('checked', totalHabilitados > 0 && totalHabilitados === totalMarcados);
});

$('#invoice-table').on('draw.dt', function() {
    $('#allComi').prop('checked', false);
});	


$('#filtro_status').select2({
    placeholder: "{{ _lang('Invoice Status') }}",
    closeOnSelect: false,
    allowClear: true
});

let statusAnterior = JSON.stringify($('#filtro_status').val() || []);

// 1. Ejecutar búsqueda UNA SOLA VEZ al cerrar el desplegable (si hubo cambios)
$('#filtro_status').on('select2:close', function () {
    let statusActual = JSON.stringify($(this).val() || []);
    if (statusActual !== statusAnterior) {
        statusAnterior = statusActual;
        invoice_table.draw();
    }
});

// 2. Si el usuario elimina una etiqueta (chip) con la "x" o limpia todo con "clear"
$('#filtro_status').on('select2:unselect select2:clear', function () {
    // Si el menú desplegable NO está abierto, filtrar de inmediato; si está abierto, esperará a select2:close
    if (!$('.select2-container--open').length) {
        setTimeout(function () {
            let statusActual = JSON.stringify($('#filtro_status').val() || []);
            if (statusActual !== statusAnterior) {
                statusAnterior = statusActual;
                invoice_table.draw();
            }
        }, 50);
    }
});
	
})(jQuery);

  
    </script>
@endsection
