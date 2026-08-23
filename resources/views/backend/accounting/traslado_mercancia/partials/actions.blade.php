@can('ver-trasladomercancia')
<!-- Botón para VER (Azul) -->
<a href="{{ route('transfers.show', $data->id) }}" class="btn btn-primary btn-sm ajax-modal" data-fullscreen="true" title="Ver Detalle">
    <i class='ti-eye'></i>
</a>

<!-- Botón para PDF (Rojo o Guinda) -->
<a href="{{ route('traslados.pdf', $data->id) }}" class="btn btn-danger btn-sm ajax-modal" data-fullscreen="true" title="Descargar PDF">
    <i class='ti-printer'></i>
</a>
@if($data->status === 'en transito')
    <a href="{{ route('transfers.edit', $data->id) }}" class="btn btn-success btn-sm" data-fullscreen="true" title="Recibir Traslado">
        <i class='ti-package'></i> <!-- Icono de un paquete/caja -->
    </a>
@else
    <button class="btn btn-secondary btn-sm" disabled title="Traslado ya procesado">
        <i class='ti-lock'></i>
    </button>
@endif
</a>@endcan




