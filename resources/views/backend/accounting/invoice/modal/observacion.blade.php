<form method="post" class="ajax-submit" autocomplete="off" action="{{ route('invoices.store_observaciones') }}"
	  enctype="multipart/form-data" novalidate>
	{{ csrf_field() }}

	<div class="col-12">
		<div class="row">

			
			
			<div class="col-md-12">
				<div class="form-group">
					<label class="control-label">{{ _lang('Note') }}</label>						
					<textarea class="form-control" rows="4" name="note">{{ $invoice->note }}</textarea>
				</div>
			</div>
			


			<input type="hidden" name="id_venta" value="{{ $invoice->id }}">
			

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