<form method="post" id="expense" class="ajax-submit" autocomplete="off" action="{{action('ExpenseController@update', $id)}}" enctype="multipart/form-data">
	{{ csrf_field()}}
	<input name="_method" type="hidden" value="PATCH">				
	
	<div class="col-12">

		@if ($transaction->pagos_car->id)

				<div class="row">

					<div class="col-md-6">
						<div class="form-group">
							<label class="control-label">Interno</label>
							<input type="text" class="form-control" name="interno"
								value="{{ isset($transaction->pagos_car->vehiculo->id) ? $transaction->pagos_car->vehiculo->id :'' }}" disabled>
						</div>
					</div>
					<div class="col-md-6">
						<div class="form-group">
							<label class="control-label">Dominio</label>
							<input type="text" class="form-control" name="interno"
								value="{{ isset($transaction->pagos_car->vehiculo->dominio) ? $transaction->pagos_car->vehiculo->dominio :'' }}" disabled>
						</div>
					</div>
				</div>
			@endif

		


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

					<input type="checkbox"  class="form-check-input" name="usd" @if($transaction->usd) checked @endif
					value="1"
						   id="usd">
					<label class="form-check-label" for="usd">USD</label>
				</div>
				<div id="tasaCont" class="form-group">

				</div>

			</div>

			<div class="col-md-6">
				<div class="form-group">
				  <label class="control-label">Imputar a</label>
				  <select class="form-control select2" data-value="id"  name="imputar_a" id="imputar_a" required>
					 <option {{ $transaction->imputar_a == 'distribuir' ? 'selected' : ''}} value="distribuir">A distribuir</option>
					 <option {{ $transaction->imputar_a == 'paternal' ? 'selected' : ''}} value="paternal">Paternal</option>
					 <option {{ $transaction->imputar_a == 'pentacar' ? 'selected' : ''}} value="pentacar">Pentacar</option>
					  <option {{ $transaction->imputar_a == 'triunvirato' ? 'selected' : ''}} value="triunvirato">Triunvirato</option>
					  <option {{ $transaction->imputar_a == 'g.u.t.' ? 'selected' : ''}} value="g.u.t.">G.u.t</option>

					 {{--{{ create_option("accounts","id",array("account_title","account_currency"),old('account_id'),array("company_id="=>company_id())) }}--}}
				  </select>
				</div>
			  </div>
			
			<div class="col-md-6">
			  <div class="form-group">
				<a href="{{ route('chart_of_accounts.create') }}" data-reload="false" data-title="{{ _lang('Add Income/Expense Type') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
				<label class="control-label">{{ _lang('Expense Type') }}</label>						
				<select class="form-control select2-ajax" data-value="id" data-display="name" data-table="chart_of_accounts" data-where="4" name="chart_id" required>
				   <option value="">{{ _lang('Select One') }}</option>
				   {{ create_option("chart_of_accounts","id","name",$transaction->chart_id,array("type="=>"expense")) }}
				</select>
			  </div>
			</div>

			<div class="col-md-6">
			 <div class="form-group">
				<label class="control-label">{{ _lang('Amount')." ".currency() }}</label>						
				<input type="text" class="form-control float-field" name="amount" value="{{ $transaction->amount }}" required>
			 </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Related To') }}</label>						
				<select class="form-control select2" name="related_to" id="related_to">
				   <option value="contacts" {{ $transaction->payer_payee_id != '' ? 'selected' : '' }}>{{ _lang('Customer') }}</option>
				   {{-- <option value="projects" {{ $transaction->project_id != '' ? 'selected' : '' }}>{{ _lang('Project') }}</option> --}}
				</select>
			  </div>
			</div>

			<div class="col-md-6 {{ $transaction->payer_payee_id == '' ? 'd-none' : '' }}"  id="contacts">
			  <div class="form-group">
				<a href="{{ route('contacts.create') }}" data-reload="false" data-title="{{ _lang('Add Client') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
				<label class="control-label">{{ _lang('Customer') }}</label>						
				<select class="form-control select2-ajax" data-value="id" data-display="contact_name" data-table="contacts" data-where="1" name="payer_payee_id">
				   <option value="">{{ _lang('Select One') }}</option>
				   {{ create_option("contacts","id","contact_name",$transaction->payer_payee_id) }}
				</select>
			  </div>
			</div>

			<div class="col-md-6 {{ $transaction->project_id == '' ? 'd-none' : '' }}" id="projects">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Select Project') }}</label>						
				<select class="form-control select2" name="project_id">
				   <option value="">{{ _lang('Select One') }}</option>
				   {{ create_option('projects','id','name', $transaction->project_id) }}
				</select>
			  </div>
			</div>
			
			<div class="col-md-6">
			  <div class="form-group">
				<a href="{{ route('payment_methods.create') }}" data-reload="false" data-title="{{ _lang('Add Payment Method') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
				<label class="control-label">{{ _lang('Payment Method') }}</label>						
				<select class="form-control select2" data-value="id" data-display="name" data-table="payment_methods" data-where="1" name="payment_method_id" required>
				   <option value="">{{ _lang('Select One') }}</option>
				   @forelse(payment_method_list() as $pay)
							<option value="{{ $pay->id }}" {{ old('payment_method_id',$transaction->payment_method_id) ? 'selected=' : ''}} >{{ $pay->name }}</option>
							@empty
							<option value="">No disponible</option>
						@endforelse
				   {{-- create_option("payment_methods","id","name",$transaction->payment_method_id) --}}
				</select>
			  </div>
			</div>

			<div class="col-md-6">
			 <div class="form-group">
				<label class="control-label">{{ _lang('Reference') }}</label>						
				<input type="text" class="form-control" name="reference" value="{{ $transaction->reference }}">
			 </div>
			</div>

			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">{{ _lang('Status') }}</label>
					<select class="form-control " name="status" required>
						<option value="">{{ _lang('Select One') }}</option>
						<option {{$transaction->status != 1 ? 'selected' : ''}} value="0">{{ 'Pendiente' }}</option>
						<option {{$transaction->status == 1 ? 'selected' : ''}} value="1">{{ 'Resuelto'  }}</option>

					</select>
				</div>
			</div>

			@if ($transaction->status != 1 && ($transaction->pagos_car->id_car ?? '') != '')

				<div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Priodidad de pago') }}</label>
                    <select class="form-control select2" data-value="id" data-display="name" name="payment_priority">
                        <option @if(!$transaction->payment_priority) {{'selected'}} @endif value="">{{ _lang('Normal') }}</option>
                        <option @if($transaction->payment_priority=='urgente') {{'selected'}} @endif value="urgente">{{ _lang('Urgente') }}</option>
                        <option  @if($transaction->payment_priority=='muy_urgente') {{'selected'}} @endif  value="muy_urgente">{{ _lang('Muy Urgente') }}</option>
                        <option @if($transaction->payment_priority=='no_pagar') {{'selected'}} @endif value="no_pagar">{{ _lang('No Pagar') }}</option>
                    </select>
                </div>
            </div>
			
			@endif

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

<script>
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

</script>

<script>
(function($) {
    "use strict";

	$(document).on('change','#related_to',function(){
	   if($(this).val() == 'projects'){
	   	 $("#projects").removeClass('d-none');
	   	 $("#contacts").addClass('d-none');
	   }else{
	   	 $("#projects").addClass('d-none');
	   	 $("#contacts").removeClass('d-none');
	   }
	});
	
})(jQuery);
</script>

