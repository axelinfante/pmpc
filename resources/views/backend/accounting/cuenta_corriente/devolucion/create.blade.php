@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="panel-title">
                            <i class="fas fa-minus-circle"></i>
                            {{ _lang('Devolución Manual de Cuenta Corriente') }}
                        </h4>
                        <p class="mb-0">
                            {{ _lang('Cliente') }}: <strong>{{ App\Contact::find($id)->contact_name ?? 'N/A' }}</strong>
                        </p>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('cuenta_corriente.show', $id) }}" class="btn btn-primary btn-xs">
                            <i class="fas fa-arrow-left"></i> {{ _lang('Volver a Cuenta Corriente') }}
                        </a>
                        <a href="{{ route('cuenta_corriente.index') }}" class="btn btn-secondary btn-xs">
                            <i class="fas fa-list"></i> {{ _lang('Lista de Cuentas') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <div class="row mb-4">
                    <div class="col-md-12">
                        <div class="alert alert-info">
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
                    </div>
                </div>

                <form method="post" autocomplete="off" action="{{ route('cuenta_corriente.devolucion.store') }}?id={{ $id }}"
                      enctype="multipart/form-data">
                    {{ csrf_field() }}
                    
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
                            <div class="form-group">
                                <label class="control-label">Moneda</label>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" name="usd" id="usd_peso" value="0" checked>
                                    <label class="form-check-label" for="usd_peso">Pesos (ARS)</label>
                                </div>
                                <div class="form-check">
                                    <input type="radio" class="form-check-input" name="usd" id="usd_usd" value="1">
                                    <label class="form-check-label" for="usd_usd">Dólares (USD)</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6" id="tasa_container" style="display: none;">
                            <div class="form-group">
                                <label class="control-label">Tasa de Cambio</label>
                                <input type="number" class="form-control" step="0.01" name="tasa" id="tasa" placeholder="Tasa" value="{{ old('tasa', 1) }}">
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
                                <select class="form-control select2" data-value="id" name="imputar_a" id="imputar_a" required>
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
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        // Inicializar datepicker
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });

        // Inicializar select2
        $('.select2').select2();
        $('.select2-ajax').select2();

        // Mostrar/ocultar campo de tasa según moneda seleccionada
        function toggleTasaField() {
            const isUsd = $('input[name="usd"]:checked').val() == '1';
            if (isUsd) {
                $('#tasa_container').show();
                $('#tasa').prop('required', true);
                $('#tasa').val(''); // Limpiar valor para que el usuario ingrese tasa
            } else {
                $('#tasa_container').hide();
                $('#tasa').prop('required', false);
                $('#tasa').val('1'); // Establecer valor por defecto para Pesos
            }
        }

        // Escuchar cambios en la selección de moneda
        $('input[name="usd"]').change(function() {
            toggleTasaField();
        });

        // Inicializar estado del campo tasa
        toggleTasaField();

        // Validación personalizada para el monto
        $('input[name="amount"]').on('blur', function() {
            const amount = parseFloat($(this).val());
            if (isNaN(amount) || amount <= 0) {
                alert('El monto debe ser un número mayor a 0');
                $(this).val('');
                $(this).focus();
            }
        });

        // Función para detectar moneda de la cuenta seleccionada
        function detectarMonedaCuenta() {
            var selectedOption = $('#account_id option:selected');
            if (selectedOption.length > 0 && selectedOption.val() !== '') {
                var texto = selectedOption.text();
                // Buscar si el texto contiene "USD" (case insensitive)
                if (texto.toUpperCase().indexOf('USD') !== -1) {
                    // Es una cuenta en USD, seleccionar radio button USD
                    $('#usd_usd').prop('checked', true);
                } else {
                    // Es una cuenta en Pesos, seleccionar radio button Pesos
                    $('#usd_peso').prop('checked', true);
                }
                // Actualizar estado del campo tasa
                toggleTasaField();
            }
        }

        // Detectar moneda cuando se cambia la cuenta bancaria
        $('#account_id').change(function() {
            detectarMonedaCuenta();
        });

        // Detectar moneda al cargar la página si ya hay una cuenta seleccionada
        $(document).ready(function() {
            detectarMonedaCuenta();
        });

        // Validación para tasa cuando es USD
        $('#tasa').on('blur', function() {
            const isUsd = $('input[name="usd"]:checked').val() == '1';
            if (isUsd) {
                const tasa = parseFloat($(this).val());
                if (isNaN(tasa) || tasa <= 0) {
                    alert('La tasa de cambio debe ser un número mayor a 0');
                    $(this).val('');
                    $(this).focus();
                }
            }
        });
    });
</script>
@endsection