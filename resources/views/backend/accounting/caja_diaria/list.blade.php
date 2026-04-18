@extends('layouts.app')

@section('content')

<div class="row">
	<div class="col-12">
	    {{--<a class="btn btn-primary btn-xs ajax-modal" data-title="{{ _lang('Add Expense') }}" href="{{ route('expense.create') }}"><i class="ti-plus"></i>  {{ _lang('Add New') }}</a>--}}
			
		<div class="card mt-2">
			<span class="d-none panel-title">{{ _lang('Movimientos') .' '. date('d-m-Y') }}</span>
			
			<div class="card-body">
				<table id="expense-table" class="table table-bordered">
					<thead>
						<tr>
							<th style="width: 120px;min-width: 120px">{{ _lang('Date') }}</th>
							<th style="width: 200px;min-width: 200px">Quien Realizó</th>
							<th style="width: 200px;min-width: 200px">Razón Social / Nombre</th>
							<th style="width: 200px;min-width: 200px">Comprobante</th>
							<th style="width: 200px;min-width: 200px">Se cobro / pago en</th>
							<th style="width: 200px;min-width: 200px">Imputar a</th>
							<th style="width: 200px;min-width: 200px">{{ _lang('Income Type') }}</th>
							<th style="width: 200px;min-width: 200px">Detalle de Rubro</th>
							<th style="width: 200px;min-width: 200px" class="text-right">{{ _lang('Amount') }}</th>
							<th style="width: 200px;min-width: 200px">{{ _lang('Method') }}</th>
							<th style="width: 200px;min-width: 200px">Banco</th>
							<th style="width: 200px;min-width: 200px">N° Cheque</th>
							<th style="width: 200px;min-width: 200px">Vto Cheque</th>
							<th style="width: 200px;min-width: 200px">Cheque entregado a</th>
							
							<th class="action-col" style="width: 200px;min-width: 200px">{{ _lang('Action') }}</th>
						</tr>
					</thead>
					<tbody>
			
					</tbody>
				</table>

				<table class="table table-striped table">
					<thead>
						<tr>
							<th>Ingresos</th>
							<th>Gastos</th>
							<th>Dif</th>
						</tr>
					</thead>
					<tbody>
					<tr>
						<td>{{$ingresos}}</td>
						<td>{{$gastos}}</td>
						<td>{{$ingresos - $gastos}}</td>
					</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>

@endsection

@section('js-script')
<script src="{{ asset('public/backend/assets/js/ajax-datatable/caja_diaria.js') }}"></script>
@endsection
