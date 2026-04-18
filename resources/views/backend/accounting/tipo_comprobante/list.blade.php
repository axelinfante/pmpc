@extends('layouts.app')

@section('content')

<div class="row">
	<div class="col-12">
	    <a class="btn btn-primary btn-xs ajax-modal" data-title="Agregar tipo de comprobante" href="{{ route('tipocomprobante.create') }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>

		<div class="card mt-2">
			<span class="d-none panel-title">Tipos de comprobantes</span>
			
			<div class="card-body">
				<table id="tipocomprobante-table" class="table table-bordered">
					<thead>
						<tr>
                            <th>Número</th>
							<th>{{ _lang('Description') }}</th>
							<th class="action-col">{{ _lang('Action') }}</th>
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
<script src="{{ asset('public/backend/assets/js/ajax-datatable/tipo-comprobante.js') }}"></script>
@endsection


