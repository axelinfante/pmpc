@extends('layouts.app')

@section('content')
<div class="row">
	<div class="col-lg-12">
		
		<div class="card mt-2">
			<span class="panel-title d-none">{{ _lang('Lista Piezas a Destruir') }}</span>
			<div class="card-body">
				<table id="data-table" class="table table-bordered table-striped w-100">
					<thead>
					    <tr>
						    <th class="text-center">{{ _lang('Fecha Devolución') }}</th>
						    <th class="text-center">{{ _lang('Pieza / Componente') }}</th>
							<th class="text-center">{{ _lang('N° Factura') }}</th>
							<th class="text-center">{{ _lang('Cliente') }}</th>
							<th class="text-center">{{ _lang('Estado') }}</th>
							<th class="text-center">{{ _lang('Observación / Nota') }}</th>
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
//columnFilters: ['input','daterangepicker',,,,,,'none'],
$(document).ready(function() {
    var table = $("#data-table").appTable({
		title: "piezas_destruir",
        ajax: {
            url: "{{ route('piezas_destruir.data') }}",
        },
		columnFilters: ['daterangepicker', 'input', 'input', 'input', { defaultText: 'N/A' }], 
        columns: [
            {data: 'return_date', name: 'return_date'},
            {data: 'product_name', name: 'product_name'},
            {data: 'invoice_id', name: 'invoice_id'},
            {data: 'client', name: 'client'},
			{data: 'status', name: 'status'},
            {data: 'note', name: 'note'},
        ],
    });
});
</script>
@endsection