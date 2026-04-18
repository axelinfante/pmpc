<form method="post" id="expense" class="ajax-submit" autocomplete="off" action="{{action('OrdenDesarmeController@update', $id)}}" enctype="multipart/form-data">
	{{ csrf_field()}}
	<input name="_method" type="hidden" value="PATCH">				
	
	<div class="col-12">
		{{--<div class="row">--}}
			{{--<div class="col-md-6">--}}
			 {{--<div class="form-group">--}}
				{{--<label class="control-label">{{ _lang('Fecha de venta') }}</label>--}}
				{{--<input disabled type="text" class="form-control datepicker" name="trans_date" value="{{ $o->fecha_venta }}"--}}
					   {{-->--}}
			 {{--</div>--}}
			{{--</div>--}}

			{{--<div class="col-md-6">--}}
				{{--<div class="form-group">--}}
					{{--<label class="control-label">Pedido pasado</label>--}}
					{{--<select class="form-control select2-ajax" data-value="id" data-display="name" data-table="users" data-where="1" name="payer_payee_id">--}}
					{{--<option value="">{{ _lang('Select One') }}</option>--}}
					{{--{{ create_option("users","id","name",old('payer_payee_id'),array("company_id="=>company_id())) }}--}}
					{{--</select>--}}
					{{--<input type="text" class="form-control" name="pedido_pasado" value="{{old('pedido_pasado',$o->pedido_pasado--}}
					{{--?? '')}}">--}}
				{{--</div>--}}
			{{--</div>--}}

			{{--<div class="col-md-6">--}}
				{{--<div class="form-group">--}}
					{{--<label class="control-label">Prioridad</label>--}}
					{{--<input type="text" class="form-control" name="prioridad" value="{{ old('prioridad',$o->prioridad ?? '')--}}
				{{--}}" >--}}
				{{--</div>--}}
			{{--</div>--}}
			<input type="hidden" name="idCar" value="{{$o->idCar}}">

			{{--<div class="col-md-6">--}}
				{{--<div class="form-group">--}}
					{{--<label class="control-label">Interno</label>--}}
					{{--<input type="text" class="form-control" name="interno" value="{{ old('interno',$o->interno ?? '')--}}
				{{--}}" >--}}
				{{--</div>--}}
			{{--</div>--}}

			{{--<div class="col-md-6">--}}
				{{--<div class="form-group">--}}
					{{--<label class="control-label">Lugar de venta</label>--}}
					{{--<input type="text" class="form-control" name="lugar_venta" value="{{ old('lugar_venta',$o->lugar_venta ?? '')--}}
				{{--}}" >--}}
				{{--</div>--}}
			{{--</div>--}}

			{{--<div class="col-md-6">--}}
				{{--<div class="form-group">--}}
					{{--<label class="control-label">Detalle de pieza</label>--}}
					{{--<input type="text" class="form-control" name="detalle_pieza" value="{{ old('detalle_pieza',$o->detalle_pieza ?? '')--}}
				{{--}}" >--}}
				{{--</div>--}}
			{{--</div>--}}

			{{--<div class="col-md-6">--}}
				{{--<div class="form-group">--}}
					{{--<label class="control-label">Detalle anulado</label>--}}
					{{--<input type="text" class="form-control" name="detalle_anulado" value="{{ old('detalle_anulado',$o->detalle_anulado ?? '')--}}
				{{--}}" >--}}
				{{--</div>--}}
			{{--</div>--}}

			{{--<div class="col-md-6">--}}
				{{--<div class="form-group">--}}
					{{--<label class="control-label">Ubicacion</label>--}}
					{{--<input type="text" class="form-control" name="ubicacion" value="{{ old('ubicacion',$o->ubicacion ?? '')--}}
				{{--}}" >--}}
				{{--</div>--}}
			{{--</div>--}}

			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Estado</label>
					{{--<input type="text" class="form-control" name="estado" value="" >--}}

					<select class="form-control" name="estado" id="estado">
						<option value="">Seleccionar</option>
						<option {{ old('estado',$o->estado ?? '') == "completado" ? 'selected' : ''}}
								value="completado">Completado</option>
						<option {{ old('estado',$o->estado ?? '') == "parcial" ? 'selected' : ''}}
								value="parcial">Parcial</option>
						<option {{ old('estado',$o->estado ?? '') == "cancelado" ? 'selected' : ''}}
								value="cancelado">Cancelado</option>
					</select>
				</div>
			</div>

			{{--<div class="col-md-6">--}}
				{{--<div class="form-group">--}}
					{{--<label class="control-label">Autorizo</label>--}}
					{{--<input type="text" class="form-control" name="autorizo" value="{{ old('autorizo',$o->autorizo ?? '')--}}
				{{--}}" >--}}
				{{--</div>--}}
			{{--</div>--}}

			{{--<div class="col-md-6">--}}
				{{--<div class="form-group">--}}
					{{--<label class="control-label">Fecha estimada pieza disponible</label>--}}
					{{--<input type="date" class="form-control" name="fecha_estimada_pieza_disponible" value="{{ old--}}
					{{--('fecha_estimada_pieza_disponible',$o->fecha_estimada_pieza_disponible ?? '')--}}
				{{--}}" >--}}
				{{--</div>--}}
			{{--</div>--}}
			{{--<div class="col-md-6">--}}
				{{--<div class="form-group">--}}
					{{--<label class="control-label">Existe</label>--}}
					{{--<input type="text" class="form-control" name="existe" value="{{ old--}}
					{{--('existe',$o->existe ?? '')--}}
				{{--}}" >--}}
				{{--</div>--}}
			{{--</div>--}}
			{{--<div class="col-md-6">--}}
				{{--<div class="form-group">--}}
					{{--<label class="control-label">Falta</label>--}}
					{{--<input type="text" class="form-control" name="falta" value="{{ old--}}
					{{--('falta',$o->falta ?? '')--}}
				{{--}}" >--}}
				{{--</div>--}}
			{{--</div>--}}

			{{--<div class="col-md-6">--}}
				{{--<div class="form-group">--}}
					{{--<label class="control-label">Informó ausencia</label>--}}
					{{--<input type="text" class="form-control" name="informo_ausencia" value="{{ old--}}
					{{--('informo_ausencia',$o->informo_ausencia ?? '')--}}
				{{--}}" >--}}
				{{--</div>--}}
			{{--</div>--}}
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Obs desarme o busqueda</label>
					<textarea type="text" class="form-control" name="obs_desarme_busqueda" >{{ old
					('obs_desarme_busqueda',$o->obs_desarme_busqueda ?? '')
				}}</textarea>
				</div>
			</div>

			{{--<div class="col-md-6">--}}
				{{--<div class="form-group">--}}
					{{--<label class="control-label">Fecha desarmado anulado</label>--}}
					{{--<input type="date" class="form-control" name="fecha_desarmado_anulado" value="{{ old--}}
					{{--('fecha_desarmado_anulado',$o->fecha_desarmado_anulado ?? '')--}}
				{{--}}" >--}}
				{{--</div>--}}
			{{--</div>--}}

			{{--<div class="col-md-6">--}}
				{{--<div class="form-group">--}}
					{{--<label class="control-label">Cargando camioneta</label>--}}
					{{--<input type="date" class="form-control" name="cargando_camioneta" value="{{ old--}}
					{{--('cargando_camioneta',$o->cargando_camioneta ?? '')--}}
				{{--}}" >--}}
				{{--</div>--}}
			{{--</div>--}}

			{{--<div class="col-md-6">--}}
				{{--<div class="form-group">--}}
					{{--<label class="control-label">Entregado</label>--}}
					{{--<input type="text" class="form-control" name="entregado" value="{{ old--}}
					{{--('entregado',$o->entregado ?? '')--}}
				{{--}}" >--}}
				{{--</div>--}}
			{{--</div>--}}
			{{--<div class="col-md-6">--}}
				{{--<div class="form-group">--}}
					{{--<label class="control-label">Fecha embalado</label>--}}
					{{--<input type="date" class="form-control" name="fecha_embalado" value="{{ old--}}
					{{--('fecha_embalado',$o->fecha_embalado ?? '')--}}
				{{--}}" >--}}
				{{--</div>--}}
			{{--</div>--}}

			{{--<div class="col-md-6">--}}
				{{--<div class="form-group">--}}
					{{--<label class="control-label">Fecha avisado a vendedor</label>--}}
					{{--<input type="date" class="form-control" name="fecha_avisado_vendedor" value="{{ old--}}
					{{--('fecha_avisado_vendedor',$o->fecha_avisado_vendedor ?? '')--}}
				{{--}}" >--}}
				{{--</div>--}}
			{{--</div>--}}
			<div class="col-md-6">
				<div class="form-group">
					<input type="submit" class="btn btn-primary" value="Actualizar">
				</div>
			</div>

		</div>
	</div>
</form>

<script>
(function($) {
    "use strict";

	$(document).on('change','#related_to',function(){
	   if($(this).val() == 'projects'){
	   	 $("#projects").removeClass('d-none');
	   	 $("#contacts").addClass('d-none');
	   }else{
	   	 $("#projects").addClass('d-none');
	   	 $("#contacts").removeClass('d-none');
	   }
	});
	
})(jQuery);
</script>