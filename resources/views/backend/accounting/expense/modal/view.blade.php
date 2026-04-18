@php $date_format = get_company_option('date_format','Y-m-d'); @endphp	

<div class="card">
	<div class="card-body">
	    <table class="table table-bordered">
			
			
			@if ($transaction->pagos_car->id)
			<tr><td>{{ _lang('Interno') }}</td><td>{{ isset($transaction->pagos_car->vehiculo->id) ? $transaction->pagos_car->vehiculo->id :'' }}</td></tr>
			<tr><td>{{ _lang('Dominio') }}</td><td>{{ isset($transaction->pagos_car->vehiculo->dominio) ? $transaction->pagos_car->vehiculo->dominio :'' }}</td></tr>

			@endif

			<tr><td>{{ _lang('Trans Date') }}</td><td>{{ date($date_format, strtotime($transaction->trans_date)) }}</td></tr>
			<tr><td>{{ _lang('Account') }}</td><td>{{ $transaction->account->account_title }}</td></tr>
			<tr><td>{{ _lang('Expense Type') }}</td><td>{{ isset($transaction->expense_type->name) ? $transaction->expense_type->name : _lang('Transfer') }}</td></tr>
			<tr><td>{{ _lang('Amount') }}</td><td>{{ decimalPlace($transaction->amount, currency($transaction->account->account_currency)) }}</td></tr>
			{{--<tr><td>{{ _lang('Base Amount') }}</td><td>{{ decimalPlace($transaction->base_amount, currency()) }}</td></tr>--}}
			<tr><td>{{ _lang('Payer') }}</td><td>{{ isset($transaction->payee->contact_name) ? $transaction->payee->contact_name : '' }}</td></tr>
			<tr><td>{{ _lang('Payment Method') }}</td><td>{{ $transaction->payment_method->name }}</td></tr>
			<tr><td>{{ _lang('Tasa') }}</td><td>{{ $transaction->tasa }}</td></tr>
			<tr>
				<td>{{ _lang('Attachment') }}</td>
				<td>
					@if($transaction->attachment != "")
					 <a href="{{ asset('public/uploads/transactions/'.urlencode($transaction->attachment)) }}" target="_blank" class="btn btn-primary">{{ _lang('View Attachment') }}</a>
					@else
						<label class="badge badge-warning">
						<strong>{{ _lang('No Atachment Availabel !') }}</strong>
						</label>
					@endif
				</td>
			</tr>
			<tr><td>{{ _lang('Note') }}</td><td>{{ $transaction->note }}</td></tr>
		</table>
	</div>
</div>
