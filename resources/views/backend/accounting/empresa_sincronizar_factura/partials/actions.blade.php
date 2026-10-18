<div class="d-flex justify-content-center align-items-center">
    @if($data->trashed())
        <!-- ACCIONES PARA REGISTROS EN LA PAPELERA (SOFT DELETED) -->
        
        <!-- Botón para Restaurar -->
        <form action="{{ route('empresa_sincronizar_factura.restore', $data->id) }}" method="POST" class="m-1">
            @csrf
            @method('PATCH')
            <button type="submit" class="btn btn-xs btn-outline-success text-nowrap py-1 px-2" style="font-size: 11px;" data-toggle="tooltip" title="{{ _lang('Restaurar Registro') }}" onclick="return confirm('{{ _lang('¿Deseas restaurar esta configuración de sincronización?') }}');">
                <i class="fa fa-undo-alt text-xs"></i> {{ _lang('Restaurar') }}
            </button>
        </form>

        <!-- Botón para Eliminar Permanentemente (Force Delete) -->
       <!-- <form action="{{ route('empresa_sincronizar_factura.force_delete', $data->id) }}" method="POST" class="m-1" onsubmit="return confirm('{{ _lang('¿Estás absolutamente seguro de eliminar este registro de forma permanente? Esta acción no se puede deshacer.') }}');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-xs btn-outline-danger text-nowrap py-1 px-2" style="font-size: 11px;" data-toggle="tooltip" title="{{ _lang('Eliminar Permanentemente') }}">
                <i class="fa fa-fire text-xs"></i> {{ _lang('Destruir') }}
            </button>
        </form>-->

    @else
        <!-- ACCIONES PARA REGISTROS ACTIVOS (MÁS PEQUEÑOS) -->
		{{-- @can('ver-trasladomercancia') --}}
        <!-- Botón Ver Detalle -->
        <a href="{{ route('empresa_sincronizar_factura.show', $data->id) }}" class="btn btn-xs btn-info m-1 text-white p-0 d-flex justify-content-center align-items-center" style="width: 28px; height: 28px; min-width: 28px; border-radius: 4px;" data-toggle="tooltip" title="{{ _lang('Ver Detalle') }}">
            <i class="fa fa-eye" style="font-size: 12px;"></i>
        </a>
		{{-- @endcan --}}

        <!-- Botón Editar -->
        <a href="{{ route('empresa_sincronizar_factura.edit', $data->id) }}" class="btn btn-xs btn-warning m-1 text-white p-0 d-flex justify-content-center align-items-center" style="width: 28px; height: 28px; min-width: 28px; border-radius: 4px;" data-toggle="tooltip" title="{{ _lang('Editar') }}">
            <i class="fa fa-edit" style="font-size: 12px;"></i>
        </a>

        <!-- Botón Mover a la Papelera (Soft Delete) -->
        <form action="{{ route('empresa_sincronizar_factura.destroy', $data->id) }}" method="POST" class="m-1" onsubmit="return confirm('{{ _lang('¿Seguro que deseas enviar esta configuración a la papelera de reciclaje?') }}');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-xs btn-danger p-0 d-flex justify-content-center align-items-center" style="width: 28px; height: 28px; min-width: 28px; border-radius: 4px;" data-toggle="tooltip" title="{{ _lang('Mover a Papelera') }}">
                <i class="fa fa-trash" style="font-size: 12px;"></i>
            </button>
        </form>
    @endif
</div>