<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('cuenta_corriente.devolucion.store') }}?id={{ $id }}"
	  enctype="multipart/form-data">
{{ csrf_field() }}
	
	<div class="col-12">
		<!-- Información de saldo actual -->
		<div class="alert alert-dark mb-4">
			<h5><i class="fas fa-wallet"></i> Saldo Actual del Cliente</h5>
			<p class="mb-1">
				<strong>Saldo en Pesos:</strong> 
				<span class="{{ $saldo_peso < 0 ? 'text-success' : 'text-danger' }}">
					$ {{ number_format(abs($saldo_peso), 2) }} 
					{{ $saldo_peso < 0 ? '(A favor del cliente)' : '(En contra del cliente)' }}
				</span>
			</p>
			<p class="mb-1">
				<strong>Saldo en USD:</strong> 
				<span class="{{ $saldo_usd < 0 ? 'text-success' : 'text-danger' }}">
					US$ {{ number_format(abs($saldo_usd), 2) }} 
					{{ $saldo_usd < 0 ? '(A favor del cliente)' : '(En contra del cliente)' }}
				</span>
			</p>
			<p class="mb-0"><small><i class="fas fa-info-circle"></i> Un saldo negativo indica dinero que debemos al cliente (saldo a favor). La devolución retira dinero de la cuenta bancaria seleccionada y también reduce el saldo a favor en la cuenta corriente del cliente.</small></p>
		</div>
		
		<div class="row">
			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Date') }}</label>
				<input type="text" class="form-control datepicker" name="trans_date" value="{{ old('trans_date', date('Y-m-d')) }}" required>
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<a href="{{ route('accounts.create') }}" data-reload="false" data-title="{{ _lang('Create Account') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
				<label class="control-label">Cuenta Bancaria (De donde sale el dinero)</label>
				<select class="form-control select2-ajax" data-value="id" data-display="account_title" data-display2="account_currency" data-table="accounts" data-where="1" name="account_id" id="account_id" required>
				   <option value="">{{ _lang('Select One') }}</option>
				   {{ create_option("accounts","id",array("account_title","account_currency"),old('account_id')) }}
				</select>
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<a href="{{ route('chart_of_accounts.create') }}" data-reload="false" data-title="{{ _lang('Add Income/Expense Type') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
				<label class="control-label">{{ _lang('Rubro de Gasto') }} <small class="text-muted">(Opcional)</small></label>
				<select class="form-control select2-ajax" data-value="id" data-display="name" data-table="chart_of_accounts" data-where="3" name="chart_id">
				   <option value="">{{ _lang('Select One - Si no selecciona, se usará "Devolución a Cliente"') }}</option>
				   @foreach($charts as $chart)
				   <option value="{{ $chart->id }}" {{ old('chart_id') == $chart->id ? 'selected' : '' }}>{{ $chart->name }}</option>
				   @endforeach
				</select>
				<small class="form-text text-muted">Si no selecciona un rubro, se usará automáticamente "Devolución a Cliente".</small>
			  </div>
			</div>

			<div class="col-md-6">
				<div class="form-check">
					<input type="checkbox" class="form-check-input" value="1" id="usd" name="usd">
					<label class="form-check-label" for="usd">USD</label>
				</div>
				<div id="tasaCont" class="form-group mt-2">
					<label class="control-label">Tasa de Cambio</label>
					<input type="number" class="form-control" step="0.01" name="tasa" id="tasa" placeholder="Tasa" value="{{ old('tasa', 1) }}" required disabled>
					<small class="form-text text-muted">Solo necesario cuando se retira en USD</small>
				</div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">Razón Social / Nombre</label>
				<input type="text" class="form-control" name="razon_social" value="{{ old('razon_social') }}" placeholder="Razón social o nombre del beneficiario" required>
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">Comprobante</label>
				<select class="form-control select2-ajax" data-value="id" data-display="descripcion" data-table="tipo_comprobante" data-where="1" name="tipo_comprobante_id">
				   <option value="">{{ _lang('Select One') }}</option>
				   {{ create_option("tipo_comprobante","id","descripcion",old('tipo_comprobante_id'),array("company_id="=>company_id())) }}
				</select>
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
					  <option value="g.u.t.">G.U.T.</option>
				  </select>
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">Detalle de Rubro</label>
				<input type="text" class="form-control" name="detalle_rubro" value="{{ old('detalle_rubro') }}" placeholder="Detalle específico del rubro">
			  </div>
			</div>
			
			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">{{ _lang('Amount') }}</label>
				<input type="text" class="form-control float-field" name="amount" value="{{ old('amount') }}" required>
				<small class="form-text text-muted">Monto a devolver al cliente</small>
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<a href="{{ route('payment_methods.create') }}" data-reload="false" data-title="{{ _lang('Add Payment Method') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
				<label class="control-label">{{ _lang('Payment Method') }}</label>
				<select class="form-control select2-ajax" data-value="id" data-display="name" data-table="payment_methods" data-where="1" name="payment_method_id" required>
				   <option value="">{{ _lang('Select One') }}</option>
				   @foreach($payment_methods as $method)
				   <option value="{{ $method->id }}" {{ old('payment_method_id') == $method->id ? 'selected' : '' }}>{{ $method->name }}</option>
				   @endforeach
				</select>
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">Referencia</label>
				<input type="text" class="form-control" name="reference" value="{{ old('reference') }}" placeholder="Número de referencia o comprobante">
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">Banco</label>
				<input type="text" class="form-control" name="banco" value="{{ old('banco') }}" placeholder="Nombre del banco">
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">Nro. Cheque</label>
				<input type="text" class="form-control" name="cheque_nro" value="{{ old('cheque_nro') }}" placeholder="Número de cheque">
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">Vencimiento Cheque</label>
				<input type="date" class="form-control datepicker" name="cheque_vencimiento" value="{{ old('cheque_vencimiento') }}">
			  </div>
			</div>

			<div class="col-md-6">
			  <div class="form-group">
				<label class="control-label">Cheque entregado a</label>
				<input type="text" class="form-control" name="cheque_entregado_a" value="{{ old('cheque_entregado_a') }}" placeholder="Persona que recibió el cheque">
			  </div>
			</div>

			<div class="col-md-12">
			  <div class="form-group">
				<label class="control-label">Nota / Descripción</label>
				<textarea class="form-control" name="note" rows="2" placeholder="Descripción de la devolución">{{ old('note', 'Devolución de saldo a favor') }}</textarea>
			  </div>
			</div>

			<div class="col-md-12">
			  <div class="form-group">
				<label class="control-label">Archivo Adjunto</label>
				<input type="file" class="form-control" name="attachment" accept=".jpeg,.png,.jpg,.doc,.pdf,.docx,.zip">
				<small class="form-text text-muted">Formatos permitidos: jpeg, png, jpg, doc, pdf, docx, zip</small>
			  </div>
			</div>

			<div class="col-md-12">
			  <div class="form-group">
				<button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button>
				<button type="submit" class="btn btn-primary">{{ _lang('Devolver Dinero al Cliente') }}</button>
			  </div>
			</div>
		</div>
	</div>
</form>

<script>
	$(document).ready(function () {
        function toggleTasaField() {
            let isChecked = $('#usd').is(':checked');
            if(isChecked){
                $('#tasa').prop('disabled', false);
                $('#tasa').prop('required', true);
            } else {
                $('#tasa').prop('disabled', true);
                $('#tasa').prop('required', false);
                $('#tasa').val(1);
            }
        }
        
        // Función para detectar moneda de la cuenta seleccionada
        function detectarMonedaCuenta() {
            var selectedOption = $('#account_id option:selected');
            if (selectedOption.length > 0 && selectedOption.val() !== '') {
                var texto = selectedOption.text();
                // Buscar si el texto contiene "USD" (case insensitive)
                if (texto.toUpperCase().indexOf('USD') !== -1) {
                    // Es una cuenta en USD, marcar el checkbox
                    $('#usd').prop('checked', true);
                } else {
                    // Es una cuenta en Pesos, desmarcar el checkbox
                    $('#usd').prop('checked', false);
                }
                // Actualizar estado del campo tasa
                toggleTasaField();
            }
        }
        
        // Initialize on page load
        toggleTasaField();
        
        // Bind click event
        $('#usd').click(toggleTasaField);
        
        // Detectar moneda cuando se cambia la cuenta bancaria
        $('#account_id').change(function() {
            detectarMonedaCuenta();
        });
        
        // Detectar moneda al cargar la página si ya hay una cuenta seleccionada
        detectarMonedaCuenta();
	})
</script>