@extends('layouts.app')
<style>
</style>
@section('content')
<div class="row">
	<div class="col-lg-12">
		@can('modelos.create')
		<a class="btn btn-primary btn-xs ajax-modal" data-reload="false" data-title="{{ _lang('Crear Modelo') }}" href="{{ route('modelos.create')
		}}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
		@endcan
		<div class="card mt-2">
			<span class="panel-title d-none">{{ _lang('Lista Modelos') }}</span>
			<div class="card-body">
				<table id="data-table" class="table table-bordered table-striped w-100">
					<thead>
					    <tr>
						    <th width="30%" class="text-center">{{ _lang('Modelo') }}</th>
						    <th width="10%" class="text-center">{{ _lang('Activo') }}</th>
							<th width="10%" class="text-center notexport">{{ _lang('Action') }}</th>
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
// Inicialización limpia
$(document).ready(function() {
  var table = $("#data-table").appTable({
		title:"modelos",
        ajax: {
            url: "{{ route('modelos.index') }}",
        },
		columnFilters: ['input',{ 
            type: 'select', 
            data: ['Si','No'] // Array simple
        }], 
        columns: [
            {data: 'modelo', name: 'modelo'},
            {data: 'activo', name: 'activo'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
    });
});
 </script>
@endsection







