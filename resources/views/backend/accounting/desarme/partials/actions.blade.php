

<div class="btn-group dropleft">
  <button type="button" class="btn" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" 
          style="background-color: transparent; color: #6c757d; width: 38px; height: 38px; border-radius: 50%; border: none; padding: 0; display: flex; align-items: center; justify-content: center; transition: 0.3s;"
          onmouseover="this.style.backgroundColor='#e2e6ea'; this.style.color='#343a40';" 
          onmouseout="this.style.backgroundColor='transparent'; this.style.color='#6c757d';">
    <i class="fas fa-ellipsis-v"></i> 
  </button>
  
  <div class="dropdown-menu">
    <a href="{{ route('orden-desarme-one.generar-pdf', $orden->id) }}" class="dropdown-item ajax-modal">
      <i class="ti-printer"></i> Imprimir Orden
    </a>
    
    <a href="{{ action('OrdenDesarmeController@edit', $orden['id']) }}" class="dropdown-item ajax-modal">
      <i class="ti-pencil"></i> {{ _lang('Update Vehicle') }}
    </a>
    
    <button class="dropdown-item" onclick="if(confirm('¿Seguro?')) document.getElementById('destroy{{ $orden->id }}').submit()">
      <i class="ti-eraser"></i> Eliminar
    </button>
		  @include('backend.accounting.desarme.partials.actions_invoice')
  </div>
</div>
