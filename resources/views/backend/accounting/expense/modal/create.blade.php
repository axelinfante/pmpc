<form method="post" id="expense" class="ajax-submit" autocomplete="off" action="{{route('expense.store')}}" enctype="multipart/form-data">
	{{ csrf_field() }}

	<div class="col-12">
		<div class="row">
		<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Date') }}</label>
				<input type="text" class="form-control datepicker" name="trans_date" value="{{ old('trans_date') }}" required>
			  </div>
			</div>
@php
	$userPre = '';
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

			<div class="col-md-6">
			  <div class="form-group">
			  <label class="control-label">¿Quien realizó?</label>
				<select class="form-control select2" data-value="id" data-display="name" data-table="users" data-where="1" name="payer_payee_id">
				   <option value="">{{ _lang('Select One') }}</option>
				   {{ create_option("users","id","name",old('payer_payee_id',$userPre),$where) }}
				</select>
			  </div>
			</div>

			<div class="col-md-6">
				<div class="form-group">
				<label class="control-label">Cliente</label>
				  <select class="form-control select2" data-value="id" data-display="contact_name" data-table="contacts" data-where="1" name="client_id" id="client_id">
					 <option value="">{{ _lang('Select One') }}</option>
					 {{ create_option("contacts","id",["contact_name", 'dni_cuit'],old('client_id')) }}
				  </select>
				</div>
			  </div>

			<div class="col-md-6">
			  <div class="form-group">
			  <label class="control-label">Proveedor</label>
				<input type="text" class="form-control" name="razon_social" value="{{ old('razon_social') }}" required>
			  </div>
			</div>

			<div class="col-md-6">
				<label class="control-label">{{ _lang('Vehiculo') }}</label>
				<select class="form-control select2-ajax" data-value="cars.id" data-display="IF(cars.company_id = 1, CONCAT('PM-',cars.id), CONCAT('PC-',cars.id))"
						data-display2="marcas.marca" data-display3="modelos.modelo"
						data-table="cars"
						data-where="8" name="idCar" id="idCar">
					<option value="">{{ _lang('- Select Car -') }}</option>

					{{ create_option("cars","id","id",old('idCar')) }}
					{{-- @forelse($vehiculos as $v)
						<option {{old('idCar',$idCar ?? '') == $v->id? 'selected' :''}} value="{{
								$v->id}}">{{ $v->id.' '.
								($v->marca_modelo->marca->marca ??
								 '').' '.
								($v->marca_modelo->modelo->modelo ?? '') .' '. $v->siniestro}}</option>
					@empty
					@endforelse --}}
				</select>
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

			<div class="col-md-6">
			  <div class="form-group">
				<a href="{{ route('accounts.create') }}" data-reload="false" data-title="{{ _lang('Create Account') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
				<label class="control-label">Se pagó en</label>
				<select class="form-control select2-ajax" data-value="id" data-display="account_title" data-display2="account_currency" data-table="accounts" data-where="1" name="account_id" id="account_id" required>
				   <option value="">{{ _lang('Select One') }}</option>
				   {{ create_option("accounts","id",array("account_title","account_currency"),old('account_id')) }}
				</select>
			  </div>
			</div>

			<div class="col-md-6">
				<div class="form-check">

					<input type="checkbox" class="form-check-input" name="usd" value="1" id="usd">
					<label class="form-check-label" for="usd">USD</label>
				</div>
				<div id="tasaCont" class="form-group">

				</div>

			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">Imputar a</label>
				<select class="form-control select2" data-value="id"  name="imputar_a" id="imputar_a" required>
				   <option value="distribuir">A distribuir</option>
				   <option value="paternal">Paternal</option>
				   <option value="pentacar">Pentacar</option>
					<option value="triunvirato">Triunvirato</option>
					<option value="g.u.t.">G.u.t</option>

				   {{--{{ create_option("accounts","id",array("account_title","account_currency"),old('account_id'),array("company_id="=>company_id())) }}--}}
				</select>
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<a href="{{ route('chart_of_accounts.create') }}" data-reload="false" data-title="{{ _lang('Add Income/Expense Type') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
				<label class="control-label">{{ _lang('Income Type') }}</label>
				<select class="form-control select2" data-value="id" data-display="name" data-table="chart_of_accounts" data-where="4" name="chart_id" id="chart_id" required>
				   <option value="">{{ _lang('Select One') }}</option>
				   @forelse(chart_of_account_list(null,array("type"=>array('expense'))) as $pay)
							<option value="{{ $pay->id }}">{{ $pay->name }}</option>
							@empty
							<option value="">No disponible</option>
						@endforelse
				   {{-- create_option("chart_of_accounts","id","name",old('chart_id'),array("type="=>"expense")) --}}
				</select>
			  </div>
			</div>

			<div class="col-md-6" id="contCotizacion">
				<label class="control-label">{{ _lang('Cotización con saldo') }}</label>
				{{-- buscar cotizacion con saldo para retirar por el medio que sea --}}
				<select class="form-control" name="idCotizacionSaldo" id="idCotizacionSaldo">
					<option value="">Elige una cotizacion devolucion</option>
				</select>
				<input type="hidden" name="idCotizacionMontoMax" id="idCotizacionMontoMax" value="0">
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">Detalle de Rubro</label>
				<input type="text" class="form-control" name="detalle_rubro" value="{{ old('detalle_rubro') }}">
			  </div>
			</div>
			
			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Amount') }}</label>
				<input type="text" class="form-control float-field" name="amount" value="{{ old('amount') }}" required>
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<a href="{{ route('payment_methods.create') }}" data-reload="false" data-title="{{ _lang('Add Payment Method') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
				<label class="control-label">{{ _lang('Payment Method') }}</label>
				<select class="form-control select2" data-value="id" data-display="name" data-table="payment_methods" data-where="1" name="payment_method_id" required>
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
				<label class="control-label">Banco</label>
				<input type="text" class="form-control" name="banco" value="{{ old('banco') }}">
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">Nro. Cheque</label>
				<input type="text" class="form-control" name="cheque_nro" value="{{ old('cheque_nro') }}">
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">Vencimiento Cheque</label>
				<input type="date" class="form-control datepicker" name="cheque_vencimiento" value="{{ old('cheque_vencimiento') }}">
			  </div>
			</div>

			{{-- <div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">Cheque entregado a</label>
				<input type="text" class="form-control" name="cheque_entregado_a" value="{{ old('cheque_entregado_a') }}">
			  </div>
			</div> --}}

			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">{{ _lang('Status') }}</label>
					<select class="form-control " name="status" required>
						<option value="0">{{ 'Pendiente' }}</option>
						<option value="1">{{ 'Resuelto'  }}</option>

					</select>
				</div>
			</div>

			<div class="col-md-12">
				<div class="form-group">
				   <label class="control-label">{{ _lang('Note') }}</label>						
				   <textarea class="form-control" name="note"></textarea>
				</div>
			   </div>

			<div class="col-md-12">
			  <div class="form-group">
				<button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button>
				<button id="submit" type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
			  </div>
			</div>
		</div>
	</div>
</form>

<script>
    $(document).ready(function () {

		let btnSubmit = $('#submit');
		let idClient = $('#client_id')
		let cotizacionSaldo = $('#idCotizacionSaldo');
		let contCotizacion = $('#contCotizacion');
		let rubro = $('#chart_id');


		contCotizacion.hide();

		
		function getCotiConSaldo() {
			let id = idClient.val();

			if(id == '' && id <= 0) {
				btnSubmit.prop('disabled',true);
				alert('Elige el cliente');
				
				return;
			}
			

			let url = "{{ url('contacts/cotizacionesConSaldo') }}?id="+id;

			$.ajax({
				url,
				dataType: "json",
				success:function(res) {
					//console.log(res);
					html = `<option value="">Elige una cotizacion devolucion</option>`
					res.cotizaciones.map(t => {
						html += `<option value="${t.idCotizacion}" data-monto="${t.paid_dev}">(${t.referencia}) ${t.paid_dev}</option>`;
		//				html += `<option value="${t.idCotizacion}">(${t.idCotizacion}) ${t.paid_dev}</option>`

					})
					cotizacionSaldo.html(html);
					
				}
			})
		}
		

		idClient.change(function(e) {
			// console.log(idClient);
			if(idClient.val() != '' && idClient.val() > 0 ) {
				getCotiConSaldo();
				$('#idCotizacionMontoMax').val(0) 
			}
		})
		
		cotizacionSaldo.on('change', function() {
			let montoSeleccionado = $(this).find(':selected').data('monto');
    		$('#idCotizacionMontoMax').val(montoSeleccionado || 0);
		});


		rubro.change(function(e) {
			if(rubro.val() == 23) {
				cotizacionSaldo.show();
				contCotizacion.show();
			}else {
				cotizacionSaldo.hide();
				cotizacionSaldo.val('');
				contCotizacion.hide();
			}
		})




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