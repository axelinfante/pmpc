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

                        <div class="col-lg-3 mb-2">
                            <label>{{ _lang('Status') }}</label>
                            <select class="form-control select2 select-filter"
                                data-placeholder="{{ _lang('Invoice Status') }}" name="status" multiple="true">
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
									<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Pieza') }}</th>
                                    <!--<th>{{ _lang('Productos') }}</th>
                                    <th>{{ _lang('id_Productos') }}</th>-->
                                    <th>{{ _lang('Vendedor') }}</th>
									{{-- <th>{{ _lang('Porcentaje de comision') }}</th>
									<th>{{ _lang('Comision') }}</th> --}}
                                    <th class="text-right">{{ _lang('Grand Total') }}</th>
									<th>{{ _lang('Monto Cobrado') }}</th>
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
                                    <!--<th></th>-->
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
@endsection


@section('js-script')
    <script>
        (function($) {

            $('#invoice-table thead tr').clone(true).appendTo('#invoice-table thead');
            $('#invoice-table thead tr:eq(1) th').each(function(i) {
                var title = $(this).text(); //es el nombre de la columna
                if (i != 0 && i != 13) {
                    $(this).html(
                        '<input style="width:80px;" type="text" value="" class="form-control filtros" placeholder="Search...' +
                        title + '" />');

                    $('.filtros', this).on('change', function() {
                        if (invoice_table.column(i).search() !== this.value) {

                            invoice_table
                                .column(i)
                                .search(this.value)
                                .draw();
                        }

                    });
                } else {
                    $(this).html('');
                }

            });

            var invoice_table = $('#invoice-table').DataTable({
                processing: true,
                serverSide: true,
                //scrollX: true,
                orderCellsTop: true,
                fixedHeader: true,
                ajax: ({
                    url: '{{ url('invoices/get_table_data') }}',
                    method: "POST",
                    data: function(d) {

                        d._token = $('meta[name="csrf-token"]').attr('content');

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

                        if ($('select[name=status]').val() != null) {
                            d.status = JSON.stringify($('select[name=status]').val());
                        }

                        if ($('input[name=date_range]').val() != '') {
                            d.date_range = $('input[name=date_range]').val();
                        }
                    },
                    error: function(request, status, error) {
                        console.log(request.responseText);
                    }
                }),
            
                    "columns": [{
                            data: "checkbox",
                            name: "checkbox"
                        },
                        {
                            data: "invoice_number",
                            name: "invoice_number"
                        },
                        {
                            data: "contact_name",
                            name: "contact_name"
                        },
                        {
                            data: "invoice_date",
                            name: "invoice_date"
                        },
                        {
                            data: "fecha_entrega",
                            name: "fecha_entrega"
                        },
                        {
                            data: "fecha_pago",
                            name: "fecha_pago"
                        },
                         {
                                data: "nro_interno",
                                name: "nro_interno"
                            },
                      /*  {
                            data: "producto",
                            name: "producto"
                        },
						{
                            data: "idproducto",
                            name: "idproducto"
                        },*/
						{
                        data: 'pieza',
                        name: 'pieza'
						}, // Pieza
                        {
                            data: "vendedor",
                            name: "vendedor"
                        },
					/*	{
                                data: "porcentajeComision",
                                name: "porcentajeComision"
                         },
						 {
                            data: "comision",
                            name: "comision"
                        },*/
                        {
                            data: "grand_total",
                            name: "grand_total"
                        },
						{
                            data: "paid",
                            name: "paid"
                        },
                        {
                            data: "status",
                            name: "status",
                            "visible": false
                        },
                        {
                            data: "note",
                            name: "note"
                        },
                        {
                            data: "action",
                            name: "action"
                        },
                    ],

                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.7/i18n/es-ES.json',
                },
                dom: 'Bfrtip',
                buttons: [
				{
                    text: 'Reset Filter',
                    action: function(e, dt, node, config) {
                        $('.filtros').val('');
						$('.select-filter').val(null).trigger('change');
							invoice_table.search('').columns().search('').draw();
                      }
                    },
				{
                        extend: 'excel',
                        text : "<i class='fa fa-file-export'></i> Exportar a Excel",
						title: "invoices",
						filename: "invoices",
                        exportOptions: {
                            columns: [ ':not(.act):visible' ],	
                            modifier: {
                                search: 'applied',
                                order: 'applied',
                                page: 'all'
                            }
						}, action: newexportaction, 
                        /*action: function() {
                            let params = invoice_table.ajax.params(); // Obtén los parámetros del DataTable

                            $.ajax({
                                url: '{{ route('invoices.export.excel') }}', // URL de la ruta
                                type: 'POST', // Cambia el método a POST
                                data: {
                                    ...params, // Incluye los parámetros del DataTable
                                    _token: '{{ csrf_token() }}' // Añade el token CSRF
                                },
                                xhrFields: {
                                    responseType: 'blob' // Importante para manejar archivos como respuesta
                                },
                                success: function(response) {
                                    // Descarga el archivo Excel
                                    let blob = new Blob([response], {
                                        type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                                    });
                                    let link = document.createElement('a');
                                    link.href = window.URL.createObjectURL(blob);
                                    link.download =
                                        'invoices.xlsx'; // Nombre del archivo descargado
                                    link.click();
                                },
                                error: function(xhr) {
                                    // Maneja errores
                                    alert('Hubo un error al exportar el archivo.');
                                }
                            });
                        }*/

                    },
                    {
                        extend: 'pdf',
                        text: 'Exportar a PDF',
						title: 'Lista de Invoices', // Título del archivo PDF
						orientation: 'landscape', // Orientación horizontal
						pageSize: 'A4', // Tamaño del papel
                        exportOptions: {
                            columns: [ ':not(.act):visible' ],	
                            modifier: {
                                search: 'applied',
                                order: 'applied',
                                page: 'all'
                            }
						}, action: newexportaction, 
                        /*action: function() {
                            let params = invoice_table.ajax.params(); // Obtén los parámetros del DataTable

                            $.ajax({
                                url: '{{ route('invoices.export.pdf') }}', // Ruta para generar el PDF
                                type: 'POST', // Usamos POST para enviar los datos
                                data: {
                                    ...params, // Incluye los parámetros del DataTable
                                    _token: '{{ csrf_token() }}' // Agrega el token CSRF
                                },
                                xhrFields: {
                                    responseType: 'blob' // Importante para manejar archivos como respuesta
                                },
                                success: function(response) {
                                    // Descarga el archivo PDF
                                    let blob = new Blob([response], {
                                        type: 'application/pdf'
                                    });
                                    let link = document.createElement('a');
                                    link.href = window.URL.createObjectURL(blob);
                                    link.download =
                                    'invoices.pdf'; // Nombre del archivo descargado
                                    link.click();
                                },
                                error: function(xhr) {
                                    // Manejo de errores
                                    alert('Hubo un error al exportar el archivo.');
                                }
                            });
                        }*/

                    },

                ],

                //responsive: true,
                bStateSave: true,
                bAutoWidth: false,
                ordering: false,
                searching: true,
                "language": {
                    "decimal": "",
                    "emptyTable": "{{ _lang('No Data Found') }}",
                    "info": "{{ _lang('Showing') }} _START_ {{ _lang('to') }} _END_ {{ _lang('of') }} _TOTAL_ {{ _lang('Entries') }}",
                    "infoEmpty": "{{ _lang('Showing 0 To 0 Of 0 Entries') }}",
                    "infoFiltered": "(filtered from _MAX_ total entries)",
                    "infoPostFix": "",
                    "thousands": ",",
                    "lengthMenu": "{{ _lang('Show') }} _MENU_ {{ _lang('Entries') }}",
                    "loadingRecords": "{{ _lang('Loading...') }}",
                    "processing": "{{ _lang('Processing...') }}",
                    "search": "{{ _lang('Search') }}",
                    "zeroRecords": "{{ _lang('No matching records found') }}",
                    "paginate": {
                        "first": "{{ _lang('First') }}",
                        "last": "{{ _lang('Last') }}",
                        "next": "{{ _lang('Next') }}",
                        "previous": "{{ _lang('Previous') }}"
                    }
                }
            });


$('#invoice-table').on('processing.dt', function (e, settings, processing) {
    if (processing) {
       	inicioLoading();
    } else {
        closeLoading();
    }
});

 $('.dataTables_filter input')
    .unbind('keypress keyup input')
    .bind('keyup input', function (e) {
		 var code = e.keyCode || e.which;
		 if ($(this).val().length >= 2 && code === 13) {
			invoice_table.search(this.value).draw();
		}
		
    });


            $('#invoice-number').on('change', function(e) {
				//var code = e.keyCode || e.which;
				//if ($(this).val().length >= 2 && code === 13) {
					invoice_table.draw();
				//}
                
            });

            $('.select-filter').on('change', function(e) {
				//var code = e.keyCode || e.which;
				//if ($(this).val().length >= 2 && code === 13) {
					invoice_table.draw();
				//}	
            });

            $('#date_range').daterangepicker({
                autoUpdateInput: false,
                locale: {
                    format: 'DD-MM-YYYY',
                    cancelLabel: 'Clear'
                }
            });

            $('#date_range').on('apply.daterangepicker', function(ev, picker) {
                $(this).val(picker.startDate.format('DD-MM-YYYY') + ' - ' + picker.endDate.format(
                    'DD-MM-YYYY'));
                invoice_table.draw();
            });

            $('#date_range').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('');
                invoice_table.draw();
            });
          //  invoice_table.search('').columns().search('').draw();

        })(jQuery);

        $(document).ready(function() {
            $('#allComi').click(function(e) {
                let isCheked = $(this).is(':checked');
                if (isCheked) {
                    $('input[type="checkbox"]').prop('checked', 'true');
                } else {
                    $('input[type="checkbox"]').removeAttr('checked');

                }
                // console.log(isCheked);
            });
        })
		
		function newexportaction(e, dt, button, config) {

this.processing( true );
var self = this;
var oldStart = dt.settings()[0]._iDisplayStart;
dt.one('preXhr', function (e, s, data) {
  // Just this once, load all data from the server...
  data.start = 0;
  //data.length = 2147483647;
  data.length = -1;
  dt.one('preDraw', function (e, settings) {
      // Call the original action function
      if (button[0].className.indexOf('buttons-copy') >= 0) {
          $.fn.dataTable.ext.buttons.copyHtml5.action.call(self, e, dt, button, config);
      } else if (button[0].className.indexOf('buttons-excel') >= 0) {
          $.fn.dataTable.ext.buttons.excelHtml5.available(dt, config) ?
              $.fn.dataTable.ext.buttons.excelHtml5.action.call(self, e, dt, button, config) :
              $.fn.dataTable.ext.buttons.excelFlash.action.call(self, e, dt, button, config);
      } else if (button[0].className.indexOf('buttons-csv') >= 0) {
          $.fn.dataTable.ext.buttons.csvHtml5.available(dt, config) ?
              $.fn.dataTable.ext.buttons.csvHtml5.action.call(self, e, dt, button, config) :
              $.fn.dataTable.ext.buttons.csvFlash.action.call(self, e, dt, button, config);
      } else if (button[0].className.indexOf('buttons-pdf') >= 0) {
          $.fn.dataTable.ext.buttons.pdfHtml5.available(dt, config) ?
              $.fn.dataTable.ext.buttons.pdfHtml5.action.call(self, e, dt, button, config) :
              $.fn.dataTable.ext.buttons.pdfFlash.action.call(self, e, dt, button, config);
      } else if (button[0].className.indexOf('buttons-print') >= 0) {
          $.fn.dataTable.ext.buttons.print.action(e, dt, button, config);
      }
      dt.one('preXhr', function (e, s, data) {
          // DataTables thinks the first item displayed is index 0, but we're not drawing that.
          // Set the property to what it was before exporting.
          settings._iDisplayStart = oldStart;
          data.start = oldStart;
      });
      // Reload the grid with the original page. Otherwise, API functions like table.cell(this) don't work properly.
      setTimeout(dt.ajax.reload, 0);
      // Prevent rendering of the full data to the DOM
      return false;
  });
});
// Requery the server with the new one-time export settings
dt.ajax.reload();
this.processing( false );
}
    </script>
@endsection
