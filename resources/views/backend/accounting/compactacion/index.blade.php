@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-12">
		<div class="card mt-2">
			<span class="panel-title d-none">{{ _lang('Lista Vehículos Compactados') }}</span>
			<div class="card-body">
				<table id="data-table" class="table table-bordered table-striped w-100">
					<thead>
                        <tr>
                            <th class="text-center">{{ _lang('Numero Interno') }}</th>
                            <th class="text-center">{{ _lang('Marca') }}</th>
                            <th class="text-center">{{ _lang('Modelo') }}</th>
                            <th class="text-center">{{ _lang('Estado') }}</th>
                            <th class="text-center">{{ _lang('Fecha Cambia Estado a Compactadora') }}</th>
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
$(document).ready(function() {
    var table = $("#data-table").appTable({
		title: "compactacion",
        ajax: {
            url: "{{ route('compactacion.data') }}",
        },
		columnFilters: ['input', 'input', 'input', 'input', 'input'],
            columns: [
                {data: 'interno', name: 'interno'},
                {data: 'marca', name: 'marca'},
                {data: 'modelo', name: 'modelo'},
                {data: 'estado', name: 'estado'},
                {data: 'fecha_cambio', name: 'fecha_cambio'},
            ],
    });
});
</script>
@endsection