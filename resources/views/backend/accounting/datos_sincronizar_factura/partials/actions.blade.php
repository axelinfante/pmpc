@php
    // Definimos la condición de bloqueo en una sola línea al principio
    $estaBloqueado = (!isset($data->invoice_company_id) || $data->invoice_company_id === null);
    $claseBloqueo  = $estaBloqueado ? 'disabled bg-secondary border-secondary text-white' : '';
    $styleBloqueo  = $estaBloqueado ? 'cursor: not-allowed; opacity: 0.55;' : '';
@endphp

<div class="d-flex justify-content-center align-items-center gap-1" role="group" aria-label="Acciones de Facturación">
    @if($data->id == 0 || $data->estado === 'pendiente')
		@can('crear-sincronizacion-facturacion')       
		<a href="{{ $estaBloqueado ? '#' : route('datos_sincronizar_factura.generar', ['datos_sincronizar_factura' => 0, 'invoice_id' => $data->id_factura_original]) }}" 
           class="btn btn-xs btn-success {{ $claseBloqueo }}" 
           style="padding: .15rem .4rem; font-size: .75rem; {{ $styleBloqueo }}"
           title="{{ $estaBloqueado ? 'Sin configuración de empresa asociada' : 'Generar Sincronización' }}"
           {{ $estaBloqueado ? 'disabled' : '' }}>
            <i class="fa {{ $estaBloqueado ? 'fa-ban' : 'fa-plus' }}"></i> Generar
        </a>
		@endcan 
    @else
       <!--<a href="{{ $estaBloqueado ? '#' : route('datos_sincronizar_factura.consultar', ['datos_sincronizar_factura' => $data->id]) }}" 
           class="btn btn-xs btn-info {{ $claseBloqueo }}" 
           style="padding: .15rem .4rem; font-size: .75rem; {{ $styleBloqueo }}"
           title="{{ $estaBloqueado ? 'Sin configuración de empresa asociada' : 'Consultar Integración' }}"
           {{ $estaBloqueado ? 'disabled' : '' }}>
            <i class="fa {{ $estaBloqueado ? 'fa-ban' : 'fa-search' }}"></i> Consultar
        </a>-->

		@can('editar-sincronizacion-facturacion')       
        <a href="{{ $estaBloqueado ? '#' : route('datos_sincronizar_factura.edit', ['datos_sincronizar_factura' => $data->id]) }}" 
           class="btn btn-xs btn-warning {{ $claseBloqueo }}" 
           style="padding: .15rem .4rem; font-size: .75rem; {{ $styleBloqueo }}"
           title="{{ $estaBloqueado ? 'Sin configuración de empresa asociada' : 'Modificar' }}"
           {{ $estaBloqueado ? 'disabled' : '' }}>
            <i class="fa {{ $estaBloqueado ? 'fa-ban' : 'fa-edit' }}"></i> Modificar
        </a>
		@endcan 
    @endif
</div>
