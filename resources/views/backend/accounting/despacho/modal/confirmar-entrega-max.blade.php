<!-- Modal -->
<div class="modal fade" id="modalConfirmarEntregaMax" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
  <div class="modal-dialog" role="document">
    <form id = "miFormulario" method="POST" action="{{ route('orden-despacho.confirmaciones') }}">
      @csrf
      <input type="hidden" name="orden_id_max" id="modal_orden_id_max">

      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirmar Entrega</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-danger print-error-msg" style="display:none">
					<ul></ul>
				</div>
          <div class="form-group">
            <label>Fecha de Entrega</label>
            <input type="date" class="form-control" name="fecha_entrega_max" id="modal_fecha_entrega_max" value="{{ now()->format('Y-m-d') }}" required>
          </div>
		  
		  <div class="form-group">
            <label>Forma de Entrega</label>
                   {!! formasEntrega('forma_entrega_max', '', false,true,true,'modal_') !!}
            <div>
          <div class="form-group">
            <label>Despachado por</label>
            <input type="text" class="form-control" name="despachado_por_max" id="modal_despachado_por">
          </div>

        </div>
        <div class="modal-footer">
          <button type="submit" data-reload="false" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>