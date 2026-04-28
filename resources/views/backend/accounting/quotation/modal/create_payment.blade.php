<form method="post" class="ajax-submit" autocomplete="off" action="{{ url('reservas/store_payment') }}" enctype="multipart/form-data" novalidate>
	{{ csrf_field() }}

	<div class="col-12">
		<div class="row">
			{{-- {{ dd($invoice->company_id) }}; --}}
			@if($invoice->is_usd == 1  ) 
				<div class="col-12 alert alert-primary text-center">
					Factura en usd
				</div>
			@endif
			<div class="col-md-6">
			  <div class="form-group">
				<a href="{{ route('accounts.create') }}" data-reload="false" data-title="{{ _lang('Create Account') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
				<label class="control-label">{{ _lang('Credit Account') }}</label>						
				<select class="form-control select2" data-value="id" data-display="account_title" data-display2="account_currency" data-table="accounts" data-where="1" name="account_id" id="account_id" required>
				   <option value="">{{ _lang('Select One') }}</option>
				   {{ create_option("accounts","id",array("account_title","account_currency"),old('account_id'),array("company_id="=>$invoice->company_id)) }}
				</select>
			  </div>
			</div>
@php $rubro = get_table('chart_of_accounts',array("type="=>"income",
                         'AND name =' => 'Venta'
                        ));
                        $idRubroVenta = null;
                        if(!empty($rubro[0])) {
                        	$idRubroVenta =$rubro[0]->id;
                        }
                        
		$userPre = null;
			if(auth()->user()->role->name == 'Cajera' || auth()->user()->role->name == 'Gerencial') {
				$userPre = auth()->id();
			}

		$rol = get_table('staff_roles',['name=' => 'Cajera', 'or name=' => 'Gerencial']);
		$cont = 0;

		foreach ($rol as $r) {
		if(!$cont):
		$where['role_id='] = $r->id;
		$cont = 1;
		else:
		$where['or role_id='] = $r->id;
		endif;



		}


	@endphp

		{{-- <div class="col-md-6">
		  <div class="form-group">
			<label class="control-label">¿Quien realizó?</label>
			<select class="form-control select2" data-value="id" data-display="name" data-table="users" data-where="1" name="payer_payee_id">
			   <option value="">{{ _lang('Select One') }}</option>
			   {{ create_option("users","id","name",old('payer_payee_id',$userPre),$where) }}
			</select>
		  </div>
		</div> --}}

		<div class="col-md-6">
		  <div class="form-group">
			<label class="control-label">Razón Social / Nombre</label>
			<input type="text" class="form-control" name="razon_social" value="{{ old('razon_social',$invoice->client->contact_name) }}" required>
		  </div>
		</div>

		<div class="col-md-6">
		  <div class="form-group">
			<label class="control-label">Comprobante</label>
			<select class="form-control select2-ajax" data-value="id" data-display="descripcion" data-table="tipo_comprobante" data-where="1" name="tipo_comprobante_id">
			   <option value="">{{ _lang('Select One') }}</option>
			   {{ create_option("tipo_comprobante","id","descripcion",old('tipo_comprobante_id')) }}
			</select>
		  </div>
		</div>
			<div class="col-md-6 d-none">
			  <div class="form-group">
				<a href="{{ route('chart_of_accounts.create') }}" data-reload="false" data-title="{{ _lang('Add Income/Expense Type') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
				<label class="control-label">{{ _lang('Income Type') }}</label>						
				<select class="form-control select2"  name="chart_id" required>
{{--				   <option value="">{{ _lang('Select One') }}</option>--}}
				   {{--{{ create_option("chart_of_accounts","id","name",old('chart_id'),array("type="=>"income","AND company_id="=>company_id())) }}--}}
					{{ create_option("chart_of_accounts","id","name",old('chart_id',$idRubroVenta),array("type="=>"income",
                         'AND name =' => 'Venta'
                        )) }}
				</select>
			  </div>
			</div>

			<div class="col-md-6">
				<div class="form-check">

					<input type="checkbox" class="form-check-input" value="1"  name ="usd" id="usd">
					<label class="form-check-label" for="usd">USD</label>
					{{-- <input type="checkbox" class="form-check-input" value="1"  name ="ars" id="ars">
					<label class="form-check-label" for="usd">ARS</label> --}}
				</div>
				<div id="tasaCont" class="form-group">
					<input class='form-control' type='number' step='0.01' name='tasa' id='tasa' placeholder='Tasa' >
				</div>

				<div class=" " id="adeudadoPesos">

				</div>

				
				  
			</div>
			@if($invoice->is_usd != 1  ) 
			<div class="col-md-6">
				<div class="form-group">
				  <label class="control-label">{{ _lang('Amount')." USD" }}</label>						
				  <input type="text" class="form-control float-field" id="amount_usd" name="amount_usd" value="{{ old('amount_usd') }}" >
				</div>
			  </div>
			  @endif
			  @if($invoice->is_usd == 1  ) 
				<div class="col-md-6">
					<div class="form-group">
					<label class="control-label">{{ _lang('Amount')." ARS" }}</label>						
					<input type="text" class="form-control float-field" id="amount_pesos" name="amount_pesos" value="{{ old('amount_pesos') }}" >
					</div>
				</div>
				@endif

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Pending Amount') }} (<b><span class="account_currency">{{ currency() }}</span></b>)</label>						
				<input type="text" class="form-control float-field" value="{{ ($invoice->grand_total - $invoice->paid) }}" id="pending_amount" readOnly="true">
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Amount')." a descontar" }}</label>						
				<input type="text" class="form-control float-field" id="amount" name="amount" value="{{ old('amount') }}" required>
			  </div>
			</div>


			<div class="col-md-6">
			  <div class="form-group">
				<a href="{{ route('payment_methods.create') }}" data-reload="false" data-title="{{ _lang('Add Payment Method') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
				<label class="control-label">{{ _lang('Payment Method') }}</label>						
				<select class="form-control select2" {{-- data-value="id" data-display="name" data-table="payment_methods" data-where="1" name="payment_method_id" --}} name="payment_method_id" required>
				   <option value="">{{ _lang('Select One') }}</option>
				   @forelse(payment_method_list(array("name"=>array('Abono cc','Gasto cc'))) as $pay)
							<option value="{{ $pay->id }}">{{ $pay->name }}</option>
							@empty
							<option value="">No disponible</option>
						@endforelse
				   {{-- create_option("payment_methods","id","name",old('payment_method_id'),array("name !="=> 'Abono cc' , " and name !="=> 'Gasto cc')) --}}
				</select>
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Reference') }}</label>						
				<input type="text" class="form-control" name="reference" value="{{ old('reference') }}">
			  </div>
			</div>

			<div class="col-md-12">
				<div class="form-group">
				<label class="control-label">{{ _lang('Attachment') }}</label>						
				<input type="file" class="form-control dropify" name="attachment">
				</div>
			</div>

			<div class="col-md-12">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Note') }}</label>						
				<textarea class="form-control" name="note">{{ old('note') }}</textarea>
			  </div>
			</div>

			<input type="hidden" name="quotation_id" value="{{ $id }}">
			<input type="hidden" name="client_id" value="{{ $invoice->client_id }}">

			<div class="col-md-12">
			  <div class="form-group">
				<button type="submit" class="btn btn-primary">{{ _lang('Make Payment') }}</button>
			  </div>
			</div>
		</div>
	</div>
</form>

<script>
(function($) {
    "use strict";	
	var from_currency = "{{ base_currency() }}";
	
	
	

	$(document).on('change','#account_id', function(){
		var account_currency = $(this).find( "option:selected" ).text().split(" ").pop();
		var amount = $("#pending_amount").val();
		
		
		$.ajax({
			method: "GET",
			url: "{{ url('convert_currency') }}/" + from_currency + "/" + account_currency + "/" + amount,
	        beforeSend: function(){
				$("#preloader").css("display","block"); 
			},success: function(data){
				$("#preloader").css("display","none");
				var json = JSON.parse(data);
				$("#pending_amount").val(parseFloat(json['amount']).toFixed(2));
				$(".account_currency").html(json['currency2_symbol']);
				from_currency = account_currency;
			}		
		});
	});
	
})(jQuery);	
</script>

<script>
	const moneda = "{{ $invoice->is_usd == 1 ? 'usd' : 'ars'}}"
    $(document).ready(function () {
// tasa();
        function tasa () {
            let isCheck = $('#usd').is(':checked');
            if(isCheck){
                // let html = "<input class='form-control' type='number' step='0.01' name='tasa' id='tasa' placeholder='Tasa' required>"
                // $('#tasaCont').html(html);
            }else{
                // $('#tasaCont').html('');
            }


        }

		

		

        tasa()
        $('#usd').click(tasa);

		$('#amount_usd').keyup(function() {
			let tasa = $("#tasa").val();
			let amount = $("#amount_usd").val();
			let calculado = amount;
			if (moneda != 'usd') {
				calculado = montoPesosCalculo(amount,tasa,'usd'); 
			}
			//

			$('#amount').val(calculado);
		})

		$('#amount_pesos').keyup(function() {
			let tasa = $("#tasa").val();
			let amount = $("#amount_pesos").val();
			let calculado = amount;
			if (moneda != 'ars') {
				calculado = montoPesosCalculo(amount,tasa,'ars'); 
			}
			//

			$('#amount').val(calculado);
		})
    })

	// function montoPesos() {
	// 	let amount = $("#pending_amount").val();
	// 	let tasa = $("#tasa").val();
	// 	let valor_pesos = montoPesosCalculo(amount, tasa,)
		
		
	// 	$('#adeudadoPesos').html('Adeudado '+valor_pesos+' ARS');
		
	// };



	function montoPesosCalculo(monto,tasa,moneda) {
		let amount = monto;
		// let tasa = tasa;
		let valor_pesos = 0;
		if(tasa >= 0 && tasa != null && tasa != ''){
			if(moneda == 'ars') {
				valor_pesos =   amount / tasa;
			}else{
				valor_pesos = amount * tasa;
			}
			
		}
		return parseFloat(valor_pesos).toFixed(2);
		//$('#adeudadoPesos').html('Adeudado '+valor_pesos+' ARS');
		
	};

</script>