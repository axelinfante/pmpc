 @php
 $aFacturar = ($orden->venta->facturar==1 && $orden->venta->facturado==null) ? true:false;
 @endphp
 
@if($orden->venta!=null)
<div class="dropdown-divider"></div>
 <a class="dropdown-item" href="#" style="background-color: #f0ad4e; color: white;">Cotizaciones</a>
 @if ($orden->venta->status == 'Canceled')
    Anulada 
@else
    @if (!$aFacturar) 
        @php
            // Simplificamos la lógica de la clase CSS
            $role = auth()->user()->role->name;
            $class = ($role == 'Gerencial' || is_null($role)) ? '' : 'd-none';
        @endphp

                <a class="dropdown-item" href="{{ action('InvoiceController@edit', $orden->venta->id) }}"><i class="fas fa-edit"></i> {{ _lang('Edit') }}</a>
                <a class="dropdown-item ajax-modal {{ $class }}" href="{{ action('InvoiceController@create_comision', $orden->venta->id) }}"><i class="fas fa-usd"></i> {{ _lang('Comisión') }}</a>
                <a class="dropdown-item ajax-modal {{ $class }}" href="{{ action('InvoiceController@create_observaciones', $orden->venta->id) }}"><i class="fas fa-usd"></i> {{ _lang('Observaciones') }} </a>
                <a class="dropdown-item" href="{{ action('InvoiceController@show', $orden->venta->id) }}" data-title="{{ _lang('View Invoice') }}" data-fullscreen="true"><i class="fas fa-eye"></i> {{ _lang('View') }} </a>
                <a href="{{ url('invoices/create_payment/' . $orden->venta->id) }}" data-title="{{ _lang('Make Payment') }}" class="dropdown-item ajax-modal"><i class="fas fa-credit-card"></i> {{ _lang('Make Payment') }} </a>
                <a href="{{ url('invoices/view_payment/' . $orden->venta->id) }}" data-title="{{ _lang('View Payment') }}" data-fullscreen="true" class="dropdown-item ajax-modal"><i class="fas fa-credit-card"></i> {{ _lang('View Payment') }} </a>
                
                <form action="{{ action('InvoiceController@destroy', $orden->venta->id) }}" method="post">
                    @csrf
                    @method('DELETE')
                    <button class="button-link btn-remove-invoice" type="submit"><i class="fas fa-recycle"></i> {{ _lang('Anular') }}</button>
                </form>
    @else 
                <a class="dropdown-item" href="#"><i class="fas fa-money-bill"></i> {{ _lang('Facturar') }} </a>
    @endif 
@endif




@endif