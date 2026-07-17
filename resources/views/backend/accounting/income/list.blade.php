@extends('layouts.app')
<style>
/*table.dataTable th, table.dataTable td {
    min-width: 80px; /* Cambia 150px por el valor mínimo deseado */
}*/
</style>
@section('content')

<div class="row">
	<div class="col-12">
	    <a class="btn btn-primary btn-xs ajax-modal" data-title="{{ _lang('Add Income') }}" href="{{ route('income.create') }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>

		<div class="card mt-2">
			<span class="d-none panel-title">{{ _lang('List Income') }}</span>
			
			<div class="card-body">
				<table id="income-table" class="table table-bordered">
					<thead>
						<tr>
							<th>{{ _lang('Date') }}</th>
							<th >Se cobro / pago en</th>
							<th >{{ _lang('Income Type') }}</th>
							<th >Detalle de Rubro</th>
							<th  class="text-right">{{ _lang('Amount') }}</th>
							<th >{{ _lang('Method') }}</th>
							<th >Quien Realizó</th>
							<th >Razón Social / Nombre</th>
							<th >Comprobante</th>
							<th >Imputar a</th>
							<th >Banco</th>
							<th >N° Cheque</th>
							<th >Vto Cheque</th>
							<th >Cheque entregado a</th>
							<th >tasa</th>

							<th class="action-col notexport" style="width: 200px;min-width: 200px">{{ _lang('Action') }}</th>
						</tr>
					</thead>
					<tbody>
					</tbody>
			  </table>
			</div>
			<div>
				<div class="d-flex justify-content-around">
					<h4>Monto total: {{$total}}</h4>
					<h4>Monto total USD: {{$totalUsd}}</h4>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection

@section('js-script')
{{-- <script src="{{ asset('public/backend/assets/js/ajax-datatable/income.js') }}"></script> --}}

<script>
     var table; 
     
        $(function() {
              $('#income-table').css('width', '100%');
                table = $('#income-table').appTable({
                    title:"Ingresos",
                    ajax: "{{ url('/income/get_table_data') }}", 
                    visibleButtonsFilter:true, 
                    visibleButtons: {
                        reset: true,
                        excel: true,
                        print: false
                    },
					columnFilters: ['daterangepicker','input',,,,,,,,], 
                    columns: [
                    { data : "trans_date", name : "trans_date" },
					{ data : "account.account_title", name : "account.account_title" },
					{ data : "income_type.name", name : "income_type.name" },
					{ data : "detalle_rubro", name : "detalle_rubro" },
					{ data : "amount", name : "amount" },
					{ data : "payment_method.name", name : "payment_method.name" },
					{ data : "payer.name", name : "payer.name" },
					{ data : "razon_social", name : "razon_social" },
					{ data : "tipo_comprobante.descripcion", name : "tipo_comprobante.descripcion" },
					{ data : "imputar_a", name : "imputar_a" },
					{ data : "banco", name : "banco" },
					{ data : "cheque_nro", name : "cheque_nro" },
					{ data : "cheque_vencimiento", name : "cheque_vencimiento" },
					{ data : "cheque_entregado_a", name : "cheque_entregado_a" },
					{ data : "tasa", name : "tasa" },
			        { data: 'action', name: 'action', searchable: false, orderable: false}
                ],
                });
  });

  async function toggleStock(btn) {
    const itemId = btn.dataset.id;
    const response = await fetch('{{ route("toggleStock") }}',{
        headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': "{{ csrf_token() }}"
            },
        method: 'POST',
        body: JSON.stringify({ 
                id: itemId 
            })
    })

    
    const data = await response.json();
    //console.log(data);

    if (typeof table !== 'undefined' && table !== null) {
            table.ajax.reload(null, false);
        }
  }
    </script>
@endsection


