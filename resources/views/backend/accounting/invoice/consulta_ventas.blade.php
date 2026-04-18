@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-12">
            @csrf
            <div class="card mt-2">
                <span class="d-none panel-title">{{ _lang('Consulta de Ventas') }}</span>
				     <div class="report-header">
                        <h4> Reporte Consulta de Ventas</h4>
                    </div>
                <div class="card-body">
				<div class=""> 
                    <table id="customers-table" class="table table-bordered" style="width:100%; min-height: 30px;">
                        <thead>
                            <tr>
                                <th>{{ _lang('FECHA COTIZACION') }}</th>
                                <th>{{ _lang('COTIZACION') }}</th>
                                <th>{{ _lang('INTERNO') }}</th>
                                <th>{{ _lang('MARCA MODELO') }}</th>
                                <th>{{ _lang('PRODUCTO') }}</th>
								<th>{{ _lang('ID_PRODUCTO') }}</th>
                                <th>{{ _lang('OBLEA') }}</th>
                                <th>{{ _lang('VENDEDOR') }}</th>
                                <th>{{ _lang('IMPORTE VENDEDOR') }}</th>
                                <th>{{ _lang('FECHA ENTREGA') }}</th>

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
@endsection

@section('js-script')

 <script>
     var _table = $('#customers-table').DataTable({
        processing: true,
        serverSide: true,
//		scrollX: true,
        orderCellsTop: true,
        fixedHeader: true,
        ajax: '{{ route('invoice.consulta_getventas') }}',
        columns: [
          {"width": "5%", data: 'invoice_date', name: 'invoice_date' },
          {"width": "10%", data: 'invoice_number', name: 'invoice_number' },
          {"width": "10%", data: 'nro_interno', name: 'nro_interno' },
          {"width": "10%", data: 'marcamodelo', name: 'marcamodelo' },
          {"width": "10%", data: 'producto', name: 'producto' },
          {"width": "10%", data: 'idproducto', name: 'idproducto' },
          {"width": "10%", data: 'nro_oblea', name: 'nro_oblea' },
          {"width": "10%", data: 'vendedor', name: 'vendedor', },
          {"width": "10%", data: 'comision', name: 'comision', className: "text-right" },
          {"width": "10%", data: 'fecha_entrega', name: 'fecha_entrega' },
        ],
		autoWidth: false,
		  responsive: true,
           //"bStateSave": true,
          "bAutoWidth": false,
           "ordering": false,
        initComplete: function () {
          this.api().columns([1,2,3,4,5,6,7,8,9]).every(function (index) {
            var column = this;
			//console.log(column);
			var title =  $(this.header()).text();  //this.index()
            //if (column.header().className !== 'non_searchable') {
              //var input = document.createElement("input");
			  /*var select = $(
            '<select class="form-control input-sm block margin-top5"><option value=""></option></select>')*/
			
				var input = $('<input type="text" value="" class="form-control filtros" placeholder="Search...' +
                        title + '" />');
              $(input).appendTo($(column.header()))
              .keyup(function (e) {
				 var code = e.keyCode || e.which; 
				 if (code === 13) {
						column.search($(this).val(), false, false, true).draw();
				 }		
              });
            //}
          });
        },
		dom: 'Bfrtip',
                buttons: [{
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
                    },
		  ],
      });
	  
	  $('.dataTables_filter input')
    .unbind('keypress keyup input')
    .bind('keyup input', function (e) {
		//console.log($(this).val());
		 var code = e.keyCode || e.which;
		 if ($(this).val().length >= 3 && code === 13) {
			_table.search(this.value).draw();
		}
		
    });
	
	
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
	  
		  
	  /*
	   this.api()
        .columns([2, 5, 7])
	  initComplete: function () {
                    this.api().columns([2, 5, 7]).every(function () {
                        var column = this;
                        var select = $('<select style="width: 100%" role="combobox"><option value=""></option></select>')
                            .appendTo($(column.header()).empty())
                            .on('change', function () {
                                var val = $.fn.dataTable.util.escapeRegex(
                                    $(this).val()
                                );
                                column.search(val ? '^' + val + '$' : '', true, false).draw();
                            });
                        column.data().unique().sort().each(function (d, j) {
                            select.append('<option value="' + d + '">' + d + '</option>')
                        });
                    });
                }
	  */
	  
    </script>
@endsection
