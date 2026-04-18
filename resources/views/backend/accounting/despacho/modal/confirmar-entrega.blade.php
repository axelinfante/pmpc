<!-- Modal -->
<div class="modal fade" id="modalConfirmarEntrega" tabindex="-1" role="dialog" aria-labelledby="modalLabel">
  <div class="modal-dialog" role="document">
    <form method="POST" action="{{ route('orden-despacho.confirmar-entrega') }}">
      @csrf
      <input type="hidden" name="orden_id" id="modal_orden_id">

      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title">Confirmar Entrega</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          
          <div class="form-group">
            <label>Fecha de Entrega</label>
            <input type="date" class="form-control" name="fecha_entrega" id="modal_fecha_entrega" required>
          </div>

          <div class="form-group">
            <label>Forma de Entrega</label>
            <select name="forma_entrega" id="modal_forma_entrega" class="form-control" required>
              <option value="">-- Seleccionar --</option>
              {{-- <option value="retira cliente">Retira cliente</option> --}}
              <option value="despacho">Despacho</option>
              <option value="flete">Flete</option>
              <option value="Mostrador Colectora">Mostrador Colectora</option>
              <option value="Mostrador ventanita">Mostrador ventanita</option>
              <option value="Mostrador constituyentes">Mostrador constituyentes</option>
              <option value="Mostrador Octubre">Mostrador Octubre</option>
            </select>
          </div>

          <div class="form-group">
            <label>Despachado por</label>
            <input type="text" class="form-control" name="despachado_por" id="modal_despachado_por" required>
          </div>

        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-primary">Guardar</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>
