@extends('layouts.app')
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/lozad/dist/lozad.min.js"></script>
<style>
/*table.dataTable {
            table-layout: fixed !important;
            width: 100% !important;
        }
        table.dataTable td {
            white-space: normal !important;
            overflow-wrap: break-word !important;
            word-wrap: break-word !important;
        }*/
</style>
@section('content')

<div class="row">
	<div class="col-12">
					@can('crear-trasladomercancia') 
							<a href="{{ route('transfers.create') }}" class="btn btn-primary">
                            Agregar Traslado <i class="bi bi-plus"></i>
                        </a>
					@endcan 
	 
		<div class="card mt-2">
			<span class="panel-title d-none">{{ _lang('Traslado de almacen') }}</span>
			
			
			<div class="card-body">
				<table id="table-data" class="table table-bordered">
					<thead>
					  <tr>
						<th>Fecha</th>
						<th>Referencia</th>
						<th>Estado</th>
						<th>Productos</th>
						<th class="text-center notexport">{{ _lang('Action') }}</th> 
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
        $(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


			var table = $('#table-data').appTable({
					title:"Traslado de Mercancia",
					ajax: "{{ route('transfers.index') }}",
					visibleButtonsFilter:false,
					visibleButtons: {
					reset: true,
					excel: true,
					print: false
					},
				   columns: [
                    { data: 'fecha_traslado', name: 'fecha_traslado' },
                    { data: 'reference', name: 'reference' },
                    { data: 'status', name: 'status' },
                    { data: 'transfers_product_count', name: 'transfers_product_count' },
                    { data: 'action', name: 'action', orderable: false}
					],
				});
        });
    </script>
       
@endsection


