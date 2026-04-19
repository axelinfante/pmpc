<form method="post" id="expense" class="ajax-submit" autocomplete="off"
    action="{{ action('VehiculoController@updateExpense', $id) }}" enctype="multipart/form-data">
    {{ csrf_field() }}

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
                    <input required type="text" class="form-control datepicker" name="trans_date"
                        value="{{ $transaction->trans_date }}" required>
                </div>
            </div>

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
                <label class="control-label">Proveedor</label>
                  <input type="text" class="form-control" name="razon_social" value="{{ $transaction->razon_social }}" required>
                </div>
              </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">¿Quien realizó?</label>
                    <select required class="form-control select2-ajax" data-value="id" data-display="name" data-table="users"
                        data-where="1" name="payer_payee_id">
                        <option value="">{{ _lang('Select One') }}</option>
                        {{ create_option('users', 'id', 'name',  $transaction->payer_payee_id ) }}
                    </select>
                </div>
            </div>


            <div class="col-md-6">
                <div class="form-group">
                    <a href="{{ route('chart_of_accounts.create') }}" data-reload="false"
                        data-title="{{ _lang('Add Income/Expense Type') }}" class="ajax-modal-2 select2-add"><i
                            class="ti-plus"></i> {{ _lang('Add New') }}</a>
                    <label class="control-label">{{ _lang('Income Type') }}</label>
                    <select required class="form-control" name="chart_id">
                        <option value="">{{ _lang('Select One') }}</option>
                        {{ create_option(
                            'chart_of_accounts',
                            'id',
                            'name',
                            $transaction->chart_id,
                            ['type=' => 'expense'],
                            "name = 'Pago de vehiculo' OR name =
                                                                        'Traslado' OR name =
                                                                        'Honorarios Gestoría' OR name =
                                                                        'Compañía'",
                                                                        
                        ) }}
                    </select>
                </div>
            </div>

            
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Priodidad de pago') }}</label>
                    <select class="form-control select2" data-value="id" data-display="name" name="payment_priority">
                        <option @if (!$transaction->payment_priority) {{ 'selected' }} @endif value="">
                            {{ _lang('Normal') }}</option>
                        <option @if ($transaction->payment_priority == 'urgente') {{ 'selected' }} @endif value="urgente">
                            {{ _lang('Urgente') }}</option>
                        <option @if ($transaction->payment_priority == 'muy_urgente') {{ 'selected' }} @endif value="muy_urgente">
                            {{ _lang('Muy Urgente') }}</option>
                        <option @if ($transaction->payment_priority == 'no_pagar') {{ 'selected' }} @endif value="no_pagar">
                            {{ _lang('No Pagar') }}</option>
                    </select>
                </div>
            </div>




            <div class="col-md-6">
                <div class="form-group">
                   <label class="control-label">{{ _lang('Amount')." ".currency() }}</label>						
                   <input type="text" class="form-control float-field" name="amount" value="{{ $transaction->amount }}" required>
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
    $(document).ready(function() {


        function tasa() {
            let isCheck = $('#usd').is(':checked');
            if (isCheck) {
                let html = "<input class='form-control' type='number' step='0.01' name='tasa' " +
                    "value='{{ $transaction->tasa ?? null }}' " +
                    "placeholder='Tasa' required>"
                $('#tasaCont').html(html);
            } else {
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

        $(document).on('change', '#related_to', function() {
            if ($(this).val() == 'projects') {
                $("#projects").removeClass('d-none');
                $("#contacts").addClass('d-none');
            } else {
                $("#projects").addClass('d-none');
                $("#contacts").removeClass('d-none');
            }
        });

    })(jQuery);
</script>
