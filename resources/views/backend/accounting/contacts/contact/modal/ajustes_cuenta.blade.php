<form method="post" class="ajax-submit" autocomplete="off" action="{{route('income.agregar_ajuste')}}"
	  enctype="multipart/form-data">
{{ csrf_field() }}
	
	<div class="col-12">
		<div class="row">
			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Date') }}</label>
				<input type="text" class="form-control datepicker" name="trans_date" value="{{ old('trans_date') }}" required>
			 
			<input type="hidden" name="payer_payee_id" id="payer_payee_id" value="{{$id}}">
			<input type="hidden" name="company_id" id="company_id" value="{{$company_id}}">
			</div>
			</div>

			<div class="col-md-6">
				<div class="form-group">
				  <label class="control-label">{{ _lang('Tipo de ajuste') }}</label>						
				  <select class="form-control select2 " 
					  name="tipo_ajuste" id="tipo_ajuste" required>
					 <option value="si+">Carga inicial (+)</option>
					 <option value="si-">Carga inicial (-)</option>
					 <option value="co+">Por Conciliación (+)</option>
					 <option value="co-">Por Conciliación (-)</option>
				  </select>
				</div>
			  </div>
			
			
			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Amount') }}</label>
				<input type="text" class="form-control float-field" name="amount" value="{{ old('amount') }}" required>
			  </div>
			</div>

			<div class="col-md-12">
				<div class="form-group">
						<label class="control-label">{{ _lang('Observación') }}</label>
					<textarea   class="form-control" placeholder="{{ _lang('Detalle') }}" name="detalle" required >{{ old('detalle') }}</textarea>
					
				</div> 
			</div>
			
		

			<div class="col-md-12">
			  <div class="form-group">
				{{-- <button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button> --}}
				<button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
			  </div>
			</div>
		</div>
	</div>
</form>

<script>
	$(document).ready(function () {


        function tasa () {
            let isCheck = $(this).is(':checked');
            if(isCheck){
                let html = "<input class='form-control' type='number' step='0.01' name='tasa' placeholder='Tasa' required>"
                $('#tasaCont').html(html);
            }else{
                $('#tasaCont').html('');
            }


        }
        tasa()
        $('#usd').click(tasa);
	})

</script>