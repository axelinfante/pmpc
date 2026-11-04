@extends('layouts.app')

@section('content')
	{{--<style type="text/css">--}}
		{{--#quotation-table td:nth-child(5){--}}
			{{--text-align: center !important;--}}
		{{--}--}}
	{{--</style>--}}
	<div class="row">
		{{--<div class="col-lg-6 mb-2">--}}
			{{--<a class="btn btn-primary btn-xs ajax-modal" data-title="{{ _lang('Add New Car') }}"--}}
			   {{--href="{{ route('orden-desarme.create') }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>--}}
		{{--</div>--}}
		<div class="col-12">
@csrf
			<div class="card mt-2">
				<span class="d-none panel-title">{{ _lang('Orden de desarme') }}</span>

				<div class="card-body">
				<div class="mb-3 d-flex align-items-center gap-2 flex-wrap">
					<span class="text-muted small text-uppercase fw-bold me-1">Estados:</span>
					
					<span class="badge rounded-pill bg-warning text-dark px-2.5 py-1 fw-bold">Pendiente / Sin Estado</span>
					<span class="text-muted small">&rarr;</span>
					
					<span class="badge rounded-pill bg-success text-white px-2.5 py-1 fw-bold">Completado</span>
					<span class="text-muted small">&rarr;</span>
					<span class="badge rounded-pill px-2.5 py-1 fw-bold" style="background-color: #D2B48C; color: white;">Cancelado</span>
				</div>
					@php $currency = currency() @endphp
					<table id="orden-desarme-table" class="table table-bordered">
						<thead>
						<tr>
							<th class="notexport"><input type="checkbox" id="select-all" class="ml-1"></th>
                            <th style="width: 150px;min-width: 150px" class="text-right">{{ _lang('Fecha de venta')
							}}</th>
                            <th style="width: 150px;min-width: 150px" class="text-right">{{ _lang('venta') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Fecha Entrega') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Prioridad') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Datos') }}</th>
							<th style="width: 150px;min-width: 150px" class="text-right">{{ _lang('Interno') }}</th>
							<th style="width: 150px;min-width: 150px" class="text-right">{{ _lang('Reserva') }}</th>
							<th style="width: 150px;min-width: 150px" class="text-right">{{ _lang('Marca y modelo') }}</th>
							<th style="width: 150px;min-width: 150px" class="text-right">{{ _lang('pieza') }}</th>
							<th style="width: 150px;min-width: 150px" class="text-right">{{ _lang('Detalle de pieza') }}</th>
							<th style="width: 150px;min-width: 150px" class="text-right">{{ _lang('Vendedor') }}</th>
							<th style="width: 150px;min-width: 150px" class="text-right">{{ _lang('Estado') }}</th>
							<th style="min-width: 300px" class="text-right">{{ _lang('OBS. Al desarme o
							busqueda') }}</th>
							<th style="width: 150px;min-width: 150px" class="text-right">{{ _lang('Desarmado o anulado') }}</th>
							<th style="width: 150px;min-width: 150px" class="text-center">{{ _lang('Ubicación Vehiculo') }}</th>
							<th style="width: 150px;min-width: 150px" class="text-center">{{ _lang('Ubicación Piezas') }}</th>

							<!--<th class='act notexport text-center' style="width: 150px;min-width: 150px" >{{ _lang('Action') }}</th>-->
							<!--<th class="notexport"><input type="checkbox" id="select-all" class="ml-1"></th>-->
						</tr>
						</thead>
						<tbody>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>

@endsection

@section('js-script')
	<script>
	var lugarentregas_tables = <?php echo json_encode($lugar_entregas); ?>;
	
	$(function() {
			var table = $('#orden-desarme-table').appTable({
					title:"Historico de Orden de desarme",
					ajax: {
						url: "{{ url('orden-desarme/get_table_data_nb') }}", // Substitua pela sua rota
						type: 'POST',
						data: function (d) {
							  d._token = $('meta[name="csrf-token"]').attr('content');
							  d.id = "{{$id ?? null}}";  // ID dinámico que puedes enviar
							  d.filtrado_activo = $('select[name="filtrado_activo"]').val() || "";
						}
					},
					visibleButtons: {
					reset: true,
					excel: true,
					print: false
					},
				   columnFilters: ['none','daterangepicker',,'daterangepicker',{ 
            type: 'select', 
            data: ['alta','normal','baja'] // Array simple
        },'none',,,,,,,,,,{ 
					 type: 'select', 
					 data: @json($lugar_entregas)
					},,'none'],
					columnDefs: [
    {
        "targets": 0, 
        "searchable": false,
        "orderable": false,
        "className": 'text-center' ,
        "render": function (data, type, row, meta) {
            
            if (data && data.trim() !== '') {
                
                var rowId = row.id || row[0] || meta.row;
				
				/*return "<input type='checkbox' class='row-checkbox' data-id='" +
                                data.id + "' data-product_id ='" + data.product_id + "'>";*/
								
					return '<div class="d-flex align-items-center justify-content-center">' +
                           '<input type="checkbox" class="row-checkbox mr-2" value="' + rowId + '" data-product_id ="' + row.product_id + '">' +
                           '<div>' + data + '</div>' +
                       '</div>';								

               /* return '<div class="d-flex align-items-center justify-content-center">' +
                           '<input type="checkbox" class="chk-accion mr-2" value="' + rowId + '">' +
                           '<div>' + data + '</div>' +
                       '</div>';*/
            }
            
            return ''; 
        }
    }
],
					
					customButtons: [
					{
                text: '<i class="fas fa-check-square"></i> Multiples Qr',
                className: 'btn-success', 
                attr: {
                    id: 'btn_obtener_datos'
                },
                action: function (e, dt, node, config) {
						
							var qr_seleccionados = [];
                            $('.row-checkbox:checked').each(function() {
                                qr_seleccionados.push($(this).data('product_id'));
                            });

						if (qr_seleccionados.length === 0) {
							alert('Debe seleccionar al menos un valor');
							return; 
						}
						
						
						
						
					//	console.log("IDs listos para procesar:", qr_seleccionados);
						impresion_multiple(qr_seleccionados);
			  }
			},
					{
							text: 'Filtrar por: ' +
							'<select id="filtrado_activo" name="filtrado_activo"  class="form-control-sm">' +
							'<option value="pendiente" selected >Pendiente</option>' +
							'<option value="completado">Completado</option>' +
							'<option value="anulada">Cancelado</option>' +
							'</select>',
					className: 'botones-custom',
					action: function ( e, dt, node, config ) {
						}
					}
					],
				   columns: [
				   { data: 'action', name: 'action', orderable: false, searchable: false },
				     { data : "fecha_venta", name : "fecha_venta" },
                    { data : "invoice_number", name : "invoice_number" },
					{data: "fecha_entrega_posible", name: "fecha_entrega_posible"},
					{data: "prioridad",name: "prioridad"},
					{data: "datos",name: "datos"}, 
                    { data : "interno", name : "interno" },
                    { data : "cotizacion", name : "cotizacion" },
                    { data : "marca_modelo", name : "marca_modelo" },
                    { data : "pieza", name : "pieza" },
                    { data : "detalle_pieza", name : "detalle_pieza" },
                    { data : "vendedor", name : "vendedor" },
                    { data : "estado", name : "estado" },
                    { data : "obs_desarme_busqueda", name : "obs_desarme_busqueda" },
                    { data : "fecha_desarmado_anulado", name : "fecha_desarmado_anulado" },
					{ data : "ubicacion_vehiculo", name : "ubicacion_vehiculo" },
					{ data : "ubicacion_pieza", name : "ubicacion_pieza" }
					],
					createdRow: function(row, data) {
                    var estado = data.estado;
                    if (estado === 'parcial') {
                        $(row).css('background-color', '#FFFACD');
                    } else if (estado === 'completado') {
                        $(row).css('background-color', '#98FB98');
                    } else if (estado === 'anulada') {
                        $(row).css({
                            'background-color': '#D2B48C',
                            'color': 'white'
                        });
                    } else if (!estado || estado === '') {
                       $(row).css('background-color', '#FFFACD');
                      // $(row).css('background-color', '#F08080');
                    }
                }
				});
				
				$('#filtrado_activo').on('change', function(e) {
					e.preventDefault();
					table.draw();
           			return false; //for old browsers 
			});
				
				$('#filtrado').on('change', function(e) {
					e.preventDefault();
					table.draw();
           			return false; 
			});
			
			
			$('#select-all').on('click', function() {
				$('.row-checkbox').prop('checked', $(this).prop('checked'));
			});
			
        });
		
		function impresion_multiple(ids){
			if (ids.length==0)
								{
									alert('Debe seleccionar un valor');
								 return;
								}

				var formData = new FormData();
					formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
                    formData.append('idsSeleccionados', ids);
					$.ajax({
					url: "{{ route('print-qr-mult') }}",						
					type: "POST",
					data: formData,
					processData: false, 
					contentType: false, 
					beforeSend: function() {
                    if (typeof inicioLoading === 'function') inicioLoading();
					},
					success: function(response) {
                    if (typeof response === 'string' || response.html) {
                        var htmlContenido = response.html ? response.html : response;
						$('#main_modal .modal-body').html(htmlContenido);
                        $('#main_modal').modal('show'); 
                    } 
                },
                error: function(xhr) {
                    alert('Ocurrió un error al intentar procesar los códigos QR.');
                },
                complete: function() {
                    if (typeof closeLoading === 'function') closeLoading();
                }

				});								
								
			//alert(ids)
		}
		
/*function newexportaction(e, dt, button, config) {

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
*/

	</script>
@endsection




