@extends('layouts.app')

@section('content')

<div class="row">
	<div class="col-12">
	    <a class="btn btn-primary btn-xs ajax-modal" data-title="{{ _lang('Add Expense') }}" href="{{ action('VehiculoController@movimiento', $idCar) }}"><i class="ti-plus"></i>  {{ _lang('Add New') }}</a>
		<input type="hidden" id="idCar" value="{{$idCar}}">

		<div class="card mt-2">
			<span class="d-none panel-title">{{ _lang('List Expense') }} de vehiculo nº interno  #{{$idCar}} / Dominio {{$dominio}}</span>
			
			<div class="card-body">
				<table id="expense-mov-table" class="table table-bordered">
					<thead>
						<tr>
							<th class="action-col" style="width: 200px;min-width: 200px">{{ _lang('Action') }}</th>
							<th style="width: 120px;min-width: 120px">{{ _lang('Date') }}</th>
							<th style="width: 200px;min-width: 200px">Razón Social / Nombre</th>
							<th style="width: 200px;min-width: 200px" class="text-right">{{ _lang('Amount') }}</th>

							<th style="width: 200px;min-width: 200px">Quien Realizó</th>
							
							<th style="width: 200px;min-width: 200px">Comprobante</th>
							<th style="width: 200px;min-width: 200px">Se cobro / pago en</th>
							<th style="width: 200px;min-width: 200px">Imputar a</th>
							<th style="width: 200px;min-width: 200px">{{ _lang('Income Type') }}</th>
							<th style="width: 200px;min-width: 200px">Detalle de Rubro</th>
							<th style="width: 200px;min-width: 200px">{{ _lang('Method') }}</th>
							<th style="width: 200px;min-width: 200px">Banco</th>
							<th style="width: 200px;min-width: 200px">N° Cheque</th>
							<th style="width: 200px;min-width: 200px">Vto Cheque</th>
							<th style="width: 200px;min-width: 200px">Cheque entregado a</th>
							<th style="width: 200px;min-width: 200px">Tasa</th>

						</tr>
					</thead>
					<tbody>
						@forelse($movimientos as $m)

							@empty
						@endforelse
			
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

@endsection

@section('js-script')

<script>
	const routes = {
		exportExcel: "{{ route('vehiculo-expense.export.excel') }}",
		exportPDF: "{{ route('vehiculo-expense.export.pdf') }}",
		csrfToken: "{{ csrf_token() }}"
	};
</script>


<script src="{{ asset('public/backend/assets/js/ajax-datatable/vehiculo-expense.js') }}"></script>
@endsection
