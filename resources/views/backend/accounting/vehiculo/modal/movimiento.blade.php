<form method="post" id="expense" class="ajax-submit" autocomplete="off" action="{{ route('storeMovimiento') }}"
    enctype="multipart/form-data">
    {{ csrf_field() }}
    <input type="hidden" name="idCar" value="{{ $idCar }}">
    <div class="col-12">

        <div class="row">

            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">Interno</label>
                    <input type="text" class="form-control" name="interno"
                        value="{{ $interno }}" disabled>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">Dominio</label>
                    <input type="text" class="form-control" name="interno"
                        value="{{ $dominio }}" disabled>
                </div>
            </div>
        </div> 
        
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Date') }}</label>
                    <input type="text" class="form-control datepicker" name="trans_date"
                        value="{{ old('trans_date') }}">
                </div>
            </div>

             <div class="col-md-6">
                <div class="form-group">
                <label class="control-label">Proveedor</label>
                  <input type="text" class="form-control" name="razon_social" value="{{ old('razon_social') }}" >
                </div>
              </div>

            {{-- <div class="col-md-6"> --}}
            {{-- <div class="form-group"> --}}
            {{-- <label class="control-label">{{ _lang('Fecha limite de retiro') }}</label> --}}
            {{-- <input type="text" class="form-control datepicker" name="fecha_limite" value="{{ old('trans_date') --}}
            {{-- }}" --}}
            {{-- > --}}


            {{-- </div> --}}
            {{-- </div> --}}

            <div class="col-md-6 d-none">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Control') }}</label>
                    <select class="form-control" name="control" id="control">
                        @foreach ($control as $key => $c)
                            <option value="{{ $key }}">{{ $c }}</option>
                        @endforeach
                    </select>

                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">¿Quien realizó?</label>
                    <select class="form-control select2-ajax" data-value="id" data-display="name" data-table="users"
                        data-where="1" name="payer_payee_id">
                        <option value="">{{ _lang('Select One') }}</option>
                        {{ create_option('users', 'id', 'name', old('payer_payee_id')) }}
                    </select>
                </div>
            </div>

            {{-- <div class="col-md-6"> --}}
            {{-- <div class="form-group"> --}}
            {{-- <label class="control-label">Razón Social / Nombre</label> --}}
            {{-- <input type="text" class="form-control" name="razon_social" value="{{ old('razon_social') }}" > --}}
            {{-- </div> --}}
            {{-- </div> --}}

            {{-- <div class="col-md-6"> --}}
            {{-- <div class="form-group"> --}}
            {{-- <label class="control-label">Comprobante</label> --}}
            {{-- <select class="form-control select2-ajax" data-value="id" data-display="descripcion" data-table="tipo_comprobante" data-where="1" name="tipo_comprobante_id"> --}}
            {{-- <option value="">{{ _lang('Select One') }}</option> --}}
            {{-- {{ create_option("tipo_comprobante","id","descripcion",old('tipo_comprobante_id'),array("company_id="=>company_id())) }} --}}
            {{-- </select> --}}
            {{-- </div> --}}
            {{-- </div> --}}

            {{-- <div class="col-md-6"> --}}
            {{-- <div class="form-group"> --}}
            {{-- <a href="{{ route('accounts.create') }}" data-reload="false" data-title="{{ _lang('Create Account') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a> --}}
            {{-- <label class="control-label">Se cobró en</label> --}}
            {{-- <select class="form-control select2-ajax" data-value="id" data-display="account_title" data-display2="account_currency" data-table="accounts" data-where="1" name="account_id" id="account_id" > --}}
            {{-- <option value="">{{ _lang('Select One') }}</option> --}}
            {{-- {{ create_option("accounts","id",array("account_title","account_currency"),old('account_id'),array("company_id="=>company_id())) }} --}}
            {{-- </select> --}}
            {{-- </div> --}}
            {{-- </div> --}}

            {{-- <div class="col-md-6"> --}}
            {{-- <div class="form-check"> --}}

            {{-- <input type="checkbox" class="form-check-input" name="usd" value="1" id="usd"> --}}
            {{-- <label class="form-check-label" for="usd">USD</label> --}}
            {{-- </div> --}}
            {{-- <div id="tasaCont" class="form-group"> --}}

            {{-- </div> --}}

            {{-- </div> --}}

            {{-- <div class="col-md-6"> --}}
            {{-- <div class="form-group"> --}}
            {{-- <label class="control-label">Imputar a</label> --}}
            {{-- <select class="form-control select2-ajax" data-value="id" data-display="account_title" data-display2="account_currency" data-table="accounts" data-where="1" name="imputar_a" id="imputar_a" > --}}
            {{-- <option value="0">A distribuir</option> --}}
            {{-- {{ create_option("accounts","id",array("account_title","account_currency"),old('account_id'),array("company_id="=>company_id())) }} --}}
            {{-- </select> --}}
            {{-- </div> --}}
            {{-- </div> --}}

            <div class="col-md-6">
                <div class="form-group">
                    <a href="{{ route('chart_of_accounts.create') }}" data-reload="false"
                        data-title="{{ _lang('Add Income/Expense Type') }}" class="ajax-modal-2 select2-add"><i
                            class="ti-plus"></i> {{ _lang('Add New') }}</a>
                    <label class="control-label">{{ _lang('Income Type') }}</label>
                    <select class="form-control" name="chart_id">
                        <option value="">{{ _lang('Select One') }}</option>
                        {{ create_option(
                            'chart_of_accounts',
                            'id',
                            'name',
                            old('chart_id'),
                            ['type=' => 'expense'],
                            "name = 'Pago de vehiculo' OR name =
                                                'Traslado' OR name =
                                                  'Honorarios Gestoría' OR name =
                                                    'Compañía'",
                        ) }}
                    </select>
                </div>
            </div>

            {{-- <div class="col-md-6"> --}}
            {{-- <div class="form-group"> --}}
            {{-- <label class="control-label">Detalle de Rubro</label> --}}
            {{-- <input type="text"  class="form-control" name="detalle_rubro" value="{{ old --}}
            {{-- ('detalle_rubro') }}"> --}}
            {{-- </div> --}}
            {{-- </div> --}}

            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Priodidad de pago') }}</label>
                    <select class="form-control select2" data-value="id" data-display="name" name="payment_priority">
                        <option value="">{{ _lang('Normal') }}</option>
                        <option value="urgente">{{ _lang('Urgente') }}</option>
                        <option value="muy_urgente">{{ _lang('Muy Urgente') }}</option>
                        <option value="no_pagar">{{ _lang('No Pagar') }}</option>
                    </select>
                </div>
            </div>




            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Amount') }}</label>
                    <input type="text" class="form-control float-field" name="amount" value="{{ old('amount') }}">
                </div>
            </div>

            {{-- <div class="col-md-6"> --}}
            {{-- <div class="form-group"> --}}
            {{-- <a href="{{ route('payment_methods.create') }}" data-reload="false" data-title="{{ _lang('Add Payment Method') }}" class="ajax-modal-2 select2-add"><i class="ti-plus"></i> {{ _lang('Add New') }}</a> --}}
            {{-- <label class="control-label">{{ _lang('Payment Method') }}</label> --}}
            {{-- <select class="form-control select2-ajax" data-value="id" data-display="name" data-table="payment_methods" data-where="1" name="payment_method_id" > --}}
            {{-- <option value="">{{ _lang('Select One') }}</option> --}}
            {{-- {{ create_option("payment_methods","id","name",old('payment_method_id'),array("company_id="=>company_id())) }} --}}
            {{-- </select> --}}
            {{-- </div> --}}
            {{-- </div> --}}

            {{-- <div class="col-md-6"> --}}
            {{-- <div class="form-group"> --}}
            {{-- <label class="control-label">Banco</label> --}}
            {{-- <input type="text" class="form-control" name="banco" value="{{ old('banco') }}"> --}}
            {{-- </div> --}}
            {{-- </div> --}}

            {{-- <div class="col-md-6"> --}}
            {{-- <div class="form-group"> --}}
            {{-- <label class="control-label">Nro. Cheque</label> --}}
            {{-- <input type="text" class="form-control" name="cheque_nro" value="{{ old('cheque_nro') }}"> --}}
            {{-- </div> --}}
            {{-- </div> --}}

            {{-- <div class="col-md-6"> --}}
            {{-- <div class="form-group"> --}}
            {{-- <label class="control-label">Vencimiento Cheque</label> --}}
            {{-- <input type="date" class="form-control datepicker" name="cheque_vencimiento" value="{{ old('cheque_vencimiento') }}"> --}}
            {{-- </div> --}}
            {{-- </div> --}}

            {{-- <div class="col-md-6"> --}}
            {{-- <div class="form-group"> --}}
            {{-- <label class="control-label">Cheque entregado a</label> --}}
            {{-- <input type="text" class="form-control" name="cheque_entregado_a" value="{{ old('cheque_entregado_a') }}"> --}}
            {{-- </div> --}}
            {{-- </div> --}}

            <div class="col-md-12">
                <div class="form-group">
                    <button type="reset" class="btn btn-danger">{{ _lang('Reset') }}</button>
                    <button type="submit" class="btn btn-primary">{{ _lang('Save') }}</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    $(document).ready(function() {


        function tasa() {
            let isCheck = $(this).is(':checked');
            if (isCheck) {
                let html =
                    "<input class='form-control' type='number' step='0.01' name='tasa' placeholder='Tasa' >"
                $('#tasaCont').html(html);
            } else {
                $('#tasaCont').html('');
            }


        }
        tasa()
        $('#usd').click(tasa);
    })
</script>
