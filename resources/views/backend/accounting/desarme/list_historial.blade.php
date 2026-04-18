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
					@php $currency = currency() @endphp
					<table id="orden-desarme-table" class="table table-bordered">
						<thead>
						<tr>
                            {{-- <th style="width: 100px;min-width: 100px" class="text-center">{{ _lang('Action') }}</th> --}}
                            {{-- <th style="width: 150px;min-width: 150px" class="text-center">{{ _lang('Procesar') }}</th> --}}
							{{-- <th style="width: 100px;min-width: 100px" >{{ _lang('Id') }}</th> --}}
							{{-- <th style="width: 100px;min-width: 100px" >{{ _lang('Pedido pasado') }}</th> --}}
							<th style="width: 100px;min-width: 100px" >{{ _lang('Fecha enviado puesto') }}</th>
							<th style="width: 100px;min-width: 100px" >{{ _lang('Puesto') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Interno') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Reserva') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('venta') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Fecha de venta')
							}}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Lugar de venta') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Marca y modelo') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('pieza') }}</th>
							{{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Detalle de pieza') }}</th> --}}
							{{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Detalle de anulado') }}</th> --}}
							{{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Cliente') }}</th> --}}
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Vendedor') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Ubicación') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Estado') }}</th>
							{{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Autorizo') }}</th> --}}
							{{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Fecha estimada de pieza disponible') }}</th> --}}
							{{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Esta la pieza') }}</th> --}}
							{{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Que Falta') }}</th> --}}
							{{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Quien informó que no esta') }}</th> --}}
							{{-- <th style="min-width: 300px" class="text-right">{{ _lang('OBS. Al desarme o busqueda') }}</th> --}}
							{{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Desarmado o anulado') }}</th> --}}
							{{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Cargado en camioneta el') }}</th> --}}
							{{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Entregado') }}</th> --}}
							{{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Embalado el') }}</th> --}}
							{{-- <th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Aviso a vendedor') }}</th> --}}
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


    function changeProcesar(s){
        let select = $(s);
        let id = select.data('id');
        let procesar = select.val();
        $.ajax({
            url: '{{url('orden-desarme/changeProcesar')}}/'+id+'/'+procesar,
            method:'GET',
            success:function(data){
                if(data.result == 'success'){
                    select.addClass('border-success')
                }else{
                    select.addClass('border-danger')
                }
            }
        })
    } 



        $(function() {
            $('#orden-desarme-table thead tr')
                .clone(true)
                .addClass('filters')
                .appendTo('#orden-desarme-table thead');
          var table  = $('#orden-desarme-table').DataTable({
                scrollX:true,
                processing: true,
                serverSide: false,
                searching: true,
              orderCellsTop: true,
              fixedHeader: true,

                ajax: ({
                url: '{{url('orden-desarme/get_table_data')}}', 
                method: "POST",
                data: function (d) {
                d._token = $('meta[name="csrf-token"]').attr('content');
                d.id = "{{$id ?? null}}";  // ID dinámico que puedes enviar
                d.estado = "completado";    // Estado enviado
                d.isHistorial = 1;          // Historial
                    },
                            
                }),


                "columns" : [
                    // { data : "action", name : "action" },
                    // { data : "procesar", name : "procesar" },
                    // { data : "id", name : "id" },
                    // { data : "pedido_pasado", name : "pedido_pasado" },
                    { data : "f_ingreso_puesto", name : "f_ingreso_puesto" },
                    { data : "prioridad", name : "prioridad" },
                    { data : "interno", name : "interno" },
                    { data : "cotizacion", name : "cotizacion" },
                    { data : "venta", name : "venta" },
                    { data : "fecha_venta", name : "fecha_venta" },
                    { data : "lugar_venta", name : "lugar_venta" },
                    { data : "marca_modelo", name : "marca_modelo" },
                    { data : "pieza", name : "pieza" },
                    // { data : "detalle_pieza", name : "detalle_pieza" },
                    // { data : "detalle_anulado", name : "detalle_anulado" },
                    // { data : "cliente", name : "cliente" },
                    { data : "vendedor", name : "vendedor" },
                    { data : "ubicacion", name : "ubicacion" },
                    { data : "estado", name : "estado" },
                    // { data : "autorizo", name : "autorizo" },
                    // { data : "fecha_estimada_pieza_disponible", name : "fecha_estimada_pieza_disponible" },
                    // { data : "existe", name : "existe" },
                    // { data : "falta", name : "falta" },
                    // { data : "informo_ausencia", name : "informo_ausencia" },
                    // { data : "obs_desarme_busqueda", name : "obs_desarme_busqueda" },
                    // { data : "fecha_desarmado_anulado", name : "fecha_desarmado_anulado" },
                    // { data : "cargando_camioneta", name : "cargando_camioneta" },
                    // { data : "entregado", name : "entregado" },
                    // { data : "fecha_embalado", name : "fecha_embalado" },
                    // { data : "fecha_avisado_vendedor", name : "fecha_avisado_vendedor" },
                ],
                responsive: false,
                "bStateSave": true,
                "bAutoWidth":false,
                "ordering": false,
                "language": {
                    "decimal":        "",
                    "emptyTable":     "{{ _lang('No Data Found') }}",
                    "info":           "{{ _lang('Showing') }} _START_ {{ _lang('to') }} _END_ {{ _lang('of') }} _TOTAL_ {{ _lang('Entries') }}",
                    "infoEmpty":      "{{ _lang('Showing 0 To 0 Of 0 Entries') }}",
                    "infoFiltered":   "(filtered from _MAX_ total entries)",
                    "infoPostFix":    "",
                    "thousands":      ",",
                    "lengthMenu":     "{{ _lang('Show') }} _MENU_ {{ _lang('Entries') }}",
                    "loadingRecords": "{{ _lang('Loading...') }}",
                    "processing":     "{{ _lang('Processing...') }}",
                    "search":         "{{ _lang('Search') }}",
                    "zeroRecords":    "{{ _lang('No matching records found') }}",
                    "paginate": {
                        "first":      "{{ _lang('First') }}",
                        "last":       "{{ _lang('Last') }}",
                        "next":       "{{ _lang('Next') }}",
                        "previous":   "{{ _lang('Previous') }}"
                    }
                },

              initComplete: function () {
                  var api = this.api();

                  // For each column
                  api
                      .columns()
                      .eq(0)
                      .each(function (colIdx) {
                          // Set the header cell to contain the input element
                          var cell = $('.filters th').eq(
                              $(api.column(colIdx).header()).index()
                          );

                          var title = $(cell).text();
                          //console.log($(api.column(colIdx).header()).index());
                          
						  if (colIdx == 1) {

                                //const opciones = ['1C', '1P', '2C', '2P', '3', '4C', '4P','GENERAL'];
								//let opciones = [];
								
								 $.ajax({
										url : "{{route('puestos.index')}}",
										type: 'GET',
										dataType: 'json',
										success: function (response) {
											let selectString = `<select name="puesto_filter" style="width:100%" class="puesto_filter">`;
											 selectString += '<option value=""> </option>';
                                            var ids = [];
											$.each(response.data, function(index, item) {
                                             if($.inArray(item.puesto, ids) === -1) { // No esta duplicado 
                                                selectString += `<option value="${item.puesto}" >${item.puesto}</option>`;
                                                ids.push(item.puesto);
                                            }
										});
											selectString += '</select>';
											$(cell).html(selectString);	
											 $('.puesto_filter').on('change', function() {
                                var puestoFilterValue = $(this).val();
                                // Usa una función de filtrado personalizada para manejar el select
                                $.fn.dataTable.ext.search.push(
                                    function(settings, data, dataIndex) {
										var puestoSelectValue =data[1];
                                        //var puestoSelectValue = $(table.cell(dataIndex, 1).node()).find('select').val(); // Obtiene el valor del select en la fila
                                        if (puestoFilterValue === "" || puestoSelectValue === puestoFilterValue) {
                                            return true;
                                        }
                                        return false;
                                    }
                                );
                                table.draw(); // Redibuja la tabla para aplicar el filtro
                                $.fn.dataTable.ext.search.pop(); // Remueve la función de filtrado para no interferir con otros filtros
                            });
											
												},error: function (e) {
											//newData.status = 'inactive'
											//row.data(newData);
											}
									});
                            }else{
						  
						  
								$(cell).html('<input style="width:100%" type="text" placeholder="' + title + '" />');

									  // On every keypress in this input
									  $(
										  'input',
										  $('.filters th').eq($(api.column(colIdx).header()).index())
									  )
									  .off('keyup change')
									  .on('change', function (e) {
										  // Get the search value
                                  $(this).attr('title', $(this).val());
                                  var regexr = '({search})'; //$(this).parents('th').find('select').val();

                                  var cursorPosition = this.selectionStart;
                                  // Search the column for that value
                                  api
                                      .column(colIdx)
                                      .search(
                                          this.value != ''
                                              ? regexr.replace('{search}', '(((' + this.value + ')))')
                                              : '',
                                          this.value != '',
                                          this.value == ''
                                      )
                                      .draw();
									})
									  .on('keyup', function (e) {
										  e.stopPropagation();

										  $(this).trigger('change');
										  $(this)
											  .focus()[0]
											  .setSelectionRange(cursorPosition, cursorPosition);
									  });
							}
						});
              },
            });



            table.search('').columns().search('').draw();

        });
	</script>
@endsection




