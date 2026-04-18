<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('invoices.store_comision') }}"
	  enctype="multipart/form-data" novalidate>
	{{ csrf_field() }}

	<div class="col-12">
		<div class="row">

			@if($invoice->is_usd == 1  ) 
				<div class="col-12 alert alert-primary text-center">
					Factura en usd
				</div>
			@endif
			
			<div class="col-md-6">
				<div class="form-group">
					<label for="">Porcentaje</label>
					<input type="number" step="0.01" name="porcentaje" id="porcentaje" class="form-control" value="{{old
					('porcentaje',$comision->porcentaje ?? $comisionDefault)}}">
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-group">
					<label for="">Monto total de comisión</label>
					<input disabled type="number" step="0.01"  id="montoCalculado" class="form-control" value="{{old('monto',
					$comision->monto ?? null)}}">
					<input type="hidden" step="0.01" name="monto" id="monto" value="{{old('monto',$comision->monto ??
					null)}}">
				</div>
			</div>

			<div class="col-md-6">
				<div class="form-check">
					<input type="checkbox" class="form-check-input" {{isset($comision->isPaid) ? 'checked' : ''}} name="isPaid" value="1" id="isPaid">
					<label class="form-check-label"
					for="isPaid">Pagado</label>
				</div>
			</div>
			


			<input type="hidden" name="id_venta" value="{{ $id }}">
			<input type="hidden" name="id_vendedor" value="{{ $invoice->user_id }}">
			<input type="hidden" name="id_comision" value="{{ $comision->id ?? null}}">

			<div class="col-md-12">
			  <div class="form-group">
				<button type="submit" class="btn btn-primary">{{ _lang('Guardar') }}</button>
			  </div>
			</div>
		</div>
	</div>
</form>

<script>
(function($) {
    $('#porcentaje').keyup(function (e) {
        porcentaje($('#porcentaje'));
    });

    $(document).ready(function() {
        if($('#porcentaje').val() > 0) {
            porcentaje($('#porcentaje'))
        }
	})



	function porcentaje(per) {
        let valPercen = per.val();
        let montoVenta = "{{$invoice->grand_total}}";
        console.log(valPercen);
        if(valPercen > 0) {
            //calcular comision
            let percent = (montoVenta * valPercen) / 100;

            $('#montoCalculado').val(percent);
            $('#monto').val(percent);

        }else {
            $('#montoCalculado').val(0);
            $('#monto').val(0);
        }
	}

	
})(jQuery);	
</script>