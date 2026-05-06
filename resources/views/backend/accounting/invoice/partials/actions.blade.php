@if ($invoice->status == 'Canceled')
    Anulada 
@else
    @if (!$aFacturar) 
        @php
            // Simplificamos la lógica de la clase CSS
            $role = auth()->user()->role->name;
            $class = ($role == 'Gerencial' || is_null($role)) ? '' : 'd-none';
        @endphp

        <div class="dropdown text-center">
            <button class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown">
                {{ _lang('Action') }}&nbsp;<i class="fas fa-angle-down"></i>
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="{{ action('InvoiceController@edit', $invoice->id) }}"><i class="fas fa-edit"></i> {{ _lang('Edit') }}</a>
                <a class="dropdown-item ajax-modal {{ $class }}" href="{{ action('InvoiceController@create_comision', $invoice->id) }}"><i class="fas fa-usd"></i> {{ _lang('Comisión') }}</a>
                <a class="dropdown-item ajax-modal {{ $class }}" href="{{ action('InvoiceController@create_observaciones', $invoice->id) }}"><i class="fas fa-usd"></i> {{ _lang('Observaciones') }} </a>
                <a class="dropdown-item" href="{{ action('InvoiceController@show', $invoice->id) }}" data-title="{{ _lang('View Invoice') }}" data-fullscreen="true"><i class="fas fa-eye"></i> {{ _lang('View') }} </a>
                <a href="{{ url('invoices/create_payment/' . $invoice->id) }}" data-title="{{ _lang('Make Payment') }}" class="dropdown-item ajax-modal"><i class="fas fa-credit-card"></i> {{ _lang('Make Payment') }} </a>
                <a href="{{ url('invoices/view_payment/' . $invoice->id) }}" data-title="{{ _lang('View Payment') }}" data-fullscreen="true" class="dropdown-item ajax-modal"><i class="fas fa-credit-card"></i> {{ _lang('View Payment') }} </a>
                
                <form action="{{ action('InvoiceController@destroy', $invoice['id']) }}" method="post">
                    @csrf
                    @method('DELETE')
                    <button class="button-link btn-remove-invoice" type="submit"><i class="fas fa-recycle"></i> {{ _lang('Anular') }}</button>
                </form>
            </div>
        </div> 
    @else 
        <div class="dropdown text-center">
            <button class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown">
                {{ _lang('Action') }}&nbsp;<i class="fas fa-angle-down"></i>
            </button>
            <div class="dropdown-menu">
                <a class="dropdown-item" href="#"><i class="fas fa-money-bill"></i> {{ _lang('Facturar') }} </a>
            </div>
        </div>
    @endif 
@endif
