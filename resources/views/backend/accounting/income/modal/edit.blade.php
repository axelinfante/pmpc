<form method="post" class="ajax-submit" autocomplete="off" action="{{action('IncomeController@update', $id)}}" enctype="multipart/form-data">
	{{ csrf_field()}}
	<input name="_method" type="hidden" value="PATCH">				
	
	<div class="col-12">
		<div class="row">
			<div class="col-md-6">
			 <div class="form-group">
				<label class="control-label">{{ _lang('Date') }}</label>						
				<input type="text" class="form-control datepicker" name="trans_date" value="{{ $transaction->trans_date }}" required>
			 </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<a href="{{ route('accounts.create') }}" data-reload="false" data-title="{{ _lang('Create Account') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
				<label class="control-label">{{ _lang('Account') }}</label>						
				<select class="form-control select2-ajax" data-value="id" data-display="account_title" data-display2="account_currency" data-table="accounts" data-where="1" name="account_id" required>
				   <option value="">{{ _lang('Select One') }}</option>
				   {{ create_option("accounts","id",array("account_title","account_currency"),$transaction->account_id) }}
				</select>
			  </div>
			</div>
			<div class="col-md-6">
				<div class="form-check">

					<input type="checkbox" class="form-check-input" value="1"  name ="usd" id="usd" @if($transaction->usd) checked @endif>
					<label class="form-check-label" for="usd">USD</label>
					{{-- <input type="checkbox" class="form-check-input" value="1"  name ="ars" id="ars">
					<label class="form-check-label" for="usd">ARS</label> --}}
				</div>
				<div id="tasaCont" class="form-group">
					<input class='form-control' type='number' step='0.01' name='tasa' id='tasa' placeholder='Tasa' value="{{ $transaction->tasa }}" >
				</div>

				<div class=" " id="adeudadoPesos">

				</div>

				
				  
			</div>
			@if(($transaction->invoice->is_usd ?? null) != 1  ) 
			<div class="col-md-6">
				<div class="form-group">
				  <label class="control-label">{{ _lang('Amount')." USD" }}</label>						
				  <input type="text" class="form-control float-field" id="amount_usd" name="amount_usd" value="{{ old('amount_usd',$transaction->amount_usd) }}" >
				</div>
			  </div>
			  @endif
			  @if(($transaction->invoice->is_usd ?? null) == 1  ) 
				<div class="col-md-6">
					<div class="form-group">
					<label class="control-label">{{ _lang('Amount')." ARS" }}</label>						
					<input type="text" class="form-control float-field" id="amount_pesos" name="amount_pesos" value="{{ old('amount_pesos',$transaction->amount_pesos) }}" >
					</div>
				</div>
				@endif

			<div class="col-md-6">
			  <div class="form-group">
				<a href="{{ route('chart_of_accounts.create') }}" data-reload="false" data-title="{{ _lang('Add Income/Expense Type') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
				<label class="control-label">{{ _lang('Income Type') }}</label>						
				<select class="form-control select2" data-value="id" data-display="name" data-table="chart_of_accounts" data-where="3" name="chart_id" required>
				   <option value="">{{ _lang('Select One') }}</option>
				   @forelse(chart_of_account_list(null,array("type"=>array('income'))) as $pay)
							<option value="{{ $pay->id }}">{{ $pay->name }}</option>
							@empty
							<option value="">No disponible</option>
						@endforelse
				</select>
			  </div>
			</div>

			<div class="col-md-6">
			 <div class="form-group">
				<label class="control-label">{{ _lang('Amount') }}</label>						
				<input type="text" class="form-control float-field" name="amount" id="amount" value="{{ $transaction->amount }}" required>
			 </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<a href="{{ route('contacts.create') }}" data-reload="false" data-title="{{ _lang('Add Client') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
				<label class="control-label">{{ _lang('Payer') }}</label>						
				<select class="form-control select2-ajax" data-value="id" data-display="contact_name" data-table="contacts" data-where="1" name="payer_payee_id">
					 <option value="">{{ _lang('Select One') }}</option>
					 {{ create_option("contacts","id","contact_name",$transaction->payer_payee_id) }}
				</select>
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<a href="{{ route('payment_methods.create') }}" data-reload="false" data-title="{{ _lang('Add Payment Method') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
				<label class="control-label">{{ _lang('Payment Method') }}</label>						
				<select class="form-control select2" data-value="id" data-display="name" data-table="payment_methods" data-where="1" name="payment_method_id" required>
				   <option value="">{{ _lang('Select One') }}</option>
						@forelse(payment_method_list(array("name"=>array('Abono cc','Gasto cc'))) as $pay)
							<option value="{{ $pay->id }}" {{ old('payment_method_id',$transaction->payment_method_id) ? 'selected=' : ''}} >{{ $pay->name }}</option>
							@empty
							<option value="">No disponible</option>
						@endforelse
				   {{-- create_option("payment_methods","id","name",$transaction->payment_method_id) --}}
				</select>
			  </div>
			</div>

			<div class="col-md-12">
			 <div class="form-group">
				<label class="control-label">{{ _lang('Reference') }}</label>						
				<input type="text" class="form-control" name="reference" value="{{ $transaction->reference }}">
			 </div>
			</div>

			<div class="col-md-12">
				<div class="form-group">
				<label class="control-label">{{ _lang('Attachment') }}</label>						
				<input type="file" class="form-control dropify" name="attachment" data-default-file="{{ $transaction->attachment != "" ? asset('public/uploads/transactions/'.$transaction->attachment) : "" }}">
				</div>
			</div>

			<div class="col-md-12">
			 <div class="form-group">
				<label class="control-label">{{ _lang('Note') }}</label>						
				<textarea class="form-control" name="note">{{ $transaction->note }}</textarea>
			 </div>
			</div>

			<div class="col-md-12">
			  <div class="form-group">
				<button type="submit" class="btn btn-primary">{{ _lang('Update') }}</button>
			  </div>
			</div>
		</div>
	</div>
</form>

{{-- <script>
    $(document).ready(function () {


        function tasa () {
            let isCheck = $('#usd').is(':checked');
            if(isCheck){
                let html = "<input class='form-control' type='number' step='0.01' name='tasa' " +
					"value='{{$transaction->tasa ?? null}}' " +
					"placeholder='Tasa' required>"
                $('#tasaCont').html(html);
            }else{
                $('#tasaCont').html('');
            }


        }

        $('#usd').click(tasa);
        tasa();
    })

</script> --}}

<script>
	const moneda = "{{ ($transaction->invoice->is_usd ?? null) == 1 ? 'usd' : 'ars'}}"
    $(document).ready(function () {
	
		
		console.log(moneda)
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


		$('#tasa').keyup(function() {
			let tasa = $("#tasa").val();
			let amount = 0;
			if($("#amount_pesos")[0] != undefined) { amount
				amount = $("#amount_pesos").val();
				// console.log($("#amount_pesos")[0])
			}

			if($("#amount_usd")[0] != undefined) {
				amount = $("#amount_usd").val();
				// console.log($("#amount_usd")[0])
			}
			
			// let amount = $("#amount_usd").val();
			let calculado = amount;
			if (moneda != 'ars') {
				calculado = montoPesosCalculo(amount,tasa,'ars'); 
			}

			if (moneda != 'usd') {
				calculado = montoPesosCalculo(amount,tasa,'usd'); 
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
		console.log('a')
		return parseFloat(valor_pesos).toFixed(2);
		//$('#adeudadoPesos').html('Adeudado '+valor_pesos+' ARS');
		
	};

</script>