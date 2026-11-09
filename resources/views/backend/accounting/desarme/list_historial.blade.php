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
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Fecha enviado puesto') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right" >{{ _lang('Puesto') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Puesto final') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Interno') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Reserva') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('venta') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Fecha de venta')
							}}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Lugar de venta') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Marca y modelo') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('pieza') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Vendedor') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Ubicación') }}</th>
							<th style="width: 100px;min-width: 100px" class="text-right">{{ _lang('Estado') }}</th>
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
            /*$.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });*/


			var table = $('#orden-desarme-table').appTable({
					title:"Historico de Orden de desarme",
					ajax: {
						url: "{{ url('orden-desarme/get_table_data') }}", // Substitua pela sua rota
						type: 'POST',
						data: function (d) {
							// Aqui você envia o valor do select/input como o parâmetro 'filtrado'
							//d.filtrado = $('#filtrado').val();
							  d._token = $('meta[name="csrf-token"]').attr('content');
							  d.id = "{{$id ?? null}}";  // ID dinámico que puedes enviar
							  d.estado = "completado";    // Estado enviado
							  d.isHistorial = 1;          // Historial
						}
					},
					//ajax: "{{ route('transfers.index') }}",
					//visibleButtonsFilter:false,
					//,,,,,'daterangepicker','input'],
					visibleButtons: {
					reset: true,
					excel: true,
					print: false
					},
				   columnFilters: ['daterangepicker', { 
					 type: 'select', 
					 data: @json($puestos)
					},{ 
					 type: 'select', 
					 data: @json($puestos)
					}],
				   columns: [
                    { data : "f_ingreso_puesto", name : "f_ingreso_puesto" },
                    { data : "puesto", name : "puesto" },
                    { data : "puesto_final", name : "puesto_final" },
                    { data : "interno", name : "interno" },
                    { data : "cotizacion", name : "cotizacion" },
                    { data : "venta", name : "venta" },
                    { data : "fecha_venta", name : "fecha_venta" },
                    { data : "lugar_venta", name : "lugar_venta" },
                    { data : "marca_modelo", name : "marca_modelo" },
                    { data : "pieza", name : "pieza" },
					{ data : "vendedor", name : "vendedor" },
                    { data : "ubicacion", name : "ubicacion" },
                    { data : "estado", name : "estado" }
                    //{ data: 'action', name: 'action', orderable: false}
					],
				});
				
				$('#filtrado').on('change', function(e) {
					e.preventDefault();
					table.draw();
           			return false; //for old browsers 
			});
        });
	</script>
@endsection




