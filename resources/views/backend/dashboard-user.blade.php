@extends('layouts.app')

@section('content')

@if (\Session::has('paypal_success'))
  <div class="alert alert-success text-center">
	<b>{{ \Session::get('paypal_success') }}</b>
  </div>
  <br>
@endif

@php
$currency = currency();
$date_format = get_company_option('date_format','Y-m-d');
@endphp

<!--Start Card-->
<div class="row">
	@can('ver-ingresos_mes')
	<div class="col-lg-3 mb-3">
		<div class="card">
			<div class="seo-fact sbg1">
				<div class="p-4">
					<div class="seofct-icon">
						<span>{{ _lang('Current Month Income') }}</span>
					</div>
					<h2>{{ decimalPlace($current_month_income, $currency) }}</h2>
				</div>
			</div>
		</div>
	</div>
	@endcan 
	@can('ver-ingresos_mes_usd')	
			<div class="col-lg-3 mb-3">
				<div class="card">
					<div class="seo-fact sbg1">
						<div class="p-4">
							<div class="seofct-icon">
								<span>{{ _lang('Current Month Income') }} (USD)</span>
							</div>
							<h2>{{ decimalPlace($current_month_income_usd, $currency) }}</h2>
						</div>
					</div>
				</div>
			</div>
	@endcan	
	@can('ver-gastos_mes')	
	<div class="col-lg-3 mb-3">
		<div class="card">
			<div class="seo-fact sbg2">
				<div class="p-4">
					<div class="seofct-icon">
						<span>{{ _lang('Current Month Expense') }}</span>
					</div>
					<h2>{{ decimalPlace($current_month_expense, $currency) }}</h2>
				</div>
			</div>
		</div>
	</div>
	@endcan		
	@can('ver-total_factura')	
	<div class="col-lg-3 mb-3">
		<div class="card">
			<div class="seo-fact sbg1">
				<div class="p-4">
					<div class="seofct-icon">
						<span>{{ _lang('Total Invoice') }}</span>
					</div>
					<h2>{{ $total_invoice_count }}</h2>
				</div>
			</div>
		</div>
	</div>
	@endcan		
	@can('ver-factura_pendiente_pago')	
	<div class="col-lg-3 mb-3">
		<div class="card">
			<div class="seo-fact sbg3">
				<div class="p-4">
					<div class="seofct-icon">
						<span>{{ _lang('Unpaid Invoice') }}</span>
					</div>
					<h2>{{ $unpaid_invoice_count }}</h2>
				</div>
			</div>
		</div>
	</div>
    @endcan		
	@can('ver-importe_factura_vencida')	
	<div class="col-lg-3 mb-3">
		<div class="card">
			<div class="seo-fact sbg4">
				<div class="p-4">
					<div class="seofct-icon">
						<span>{{ _lang('Invoice Due Amount') }}</span>
					</div>
					<h2>{{ decimalPlace($invoice_due_amount->grand_total - $invoice_due_amount->paid, $currency) }}</h2>
				</div>
			</div>
		</div>
	</div>
  @endcan		
	@can('ver-factura_cancelada')	
	<div class="col-lg-3 mb-3">
		<div class="card">
			<div class="seo-fact sbg3">
				<div class="p-4">
					<div class="seofct-icon">
						<span>{{ _lang('Canceled Invoice') }}</span>
					</div>
					<h2>{{ $canceled_invoice_count }}</h2>
				</div>
			</div>
		</div>
	</div>
 @endcan		
</div><!--end row-->
<!--End Card-->
@can('ver-income_vs_expense')		
<div class="row">
  <div class="col-md-12">
	 <div class="card">
		<div class="card-body">
		   <h4 class="header-title mt-0">{{ _lang('Income VS Expense')." - ".date('Y') }}</h4>
		   <div id="yearly_income_expense"></div>
		</div>
	 </div>
  </div>
</div>
@endcan		



<div class="row mt-4 d-flex align-items-stretch">
  <!-- Panel 3 -->
  @can('ver-income_vs_expense_mes')	
  <div class="col-md-6">
	 <div class="card h-100">
		<div class="card-body">
		   <h4 class="header-title mt-0">{{ _lang('Income vs Expense')." - ".date('M, Y') }}</h4>
		   <div id="dn_income_expense"></div>
		</div>
	 </div>
  </div>
  @endcan
  <!-- End Panel 3 -->

  <!-- Panel 4 -->
  @can('ver-financial_balance_status')	
  <div class="col-md-6">
	 <div class="card h-100">
		<div class="card-body">
		  <h4 class="header-title mt-0">{{ _lang('Financial Balance Status') }}</h4>
		  <div class="table-responsive">
			<table class="table table-bordered">
				<thead>
				  <tr>
					<th>{{ _lang('A/C') }}</th>
					<th>{{ _lang('A/C Number') }}</th>
					<th class="text-right">{{ _lang('Balance') }}</th>
				  </tr>
				</thead>
				<tbody>
				  @foreach(get_financial_balance() as $account)
				  <tr id="row_{{ $account->id }}">
					<td class='account_title'>{{ $account->account_title.' ('.$account->account_currency.')' }}</td>
					<td class='account_number'>{{ $account->account_number }}</td>
					<td class='opening_balance text-right'>{{ decimalPlace($account->balance, currency($account->account_currency)) }}</td>
				  </tr>
				  @endforeach
				</tbody>
			  </table>
            </div>
		</div>
	 </div>
  </div>
  <!-- End Panel 4 -->
	  @endcan
</div>
@endsection

@section('js-script')
<script src="{{ asset('public/backend/assets/js/dashboard.js?v=1.1') }}"></script>
@endsection
