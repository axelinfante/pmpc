<form method="post" id="expense" class="ajax-submit" autocomplete="off" action="{{action('OrdenDesarmeController@update', $id)}}" enctype="multipart/form-data">
	{{ csrf_field()}}
	<input name="_method" type="hidden" value="PATCH">				
	

	@php
		$class = '';
		if(strtolower(auth()->user()->role->name) == 'gerente de operarios') {
			$class = 'd-none';
		}
	@endphp




	<div class="col-12">
		<div class="row">
			{{-- <div class="col-md-6"> --}}
			 {{-- <div class="form-group"> --}}
				{{-- <label class="control-label">{{ _lang('Fecha de venta') }}</label> --}}
				{{-- <input disabled type="text" class="form-control datepicker" name="trans_date" value="{{ $o->fecha_venta }}" > --}}
			 {{-- </div> --}}
			{{-- </div> --}}
			{{-- <div class="col-md-3 py-4"> --}}
			{{--	<div class="form-check"> --}}
			{{--		<input type="checkbox" id="procesar" {{$o->procesar == 1 ? 'checked' : ''}} name="procesar" class="form-check-input" value="1"> --}}
			{{--		<label class="form-check-label" for="procesar">Procesar</label> --}}
			{{--	</div> --}}
			{{-- </div> --}}

			@if (strTolower(auth()->user()->role->name) == 'administrativo de desarme'|| strTolower(auth()->user()->role->name) == 'gerencial') 

			{{--<div class="col-md-6"> --}}
			{{--	<div class="form-group"> --}}
			{{--		<label for="idCadete_operario">Operario</label> --}}
			{{--		<select class="form-control" name="idCadete_operario" id="idCadete_operario"> --}}
			{{--			<option value="">Seleccione</option> --}}
						{{-- create_option("users","id","name",old('idCadete_operario',$o->idCadete_operario ?? ''),['role_id =' => $roles[0]->id, 'or role_id =' =>$roles[1]->id] ) --}} 
			{{--		</select> --}}
			{{--	</div> --}}
			{{--</div> --}}
			@endif

			{{-- <div class="col-md-6 {{ $class }}"> --}}
				{{-- <div class="form-group"> --}}
					{{-- <label class="control-label">Pedido pasado</label> --}}
					{{--<select class="form-control select2-ajax" data-value="id" data-display="name" data-table="users" data-where="1" name="payer_payee_id">--}}
					{{--<option value="">{{ _lang('Select One') }}</option>--}}
					{{--{{ create_option("users","id","name",old('payer_payee_id'),array("company_id="=>company_id())) }}--}}
					{{--</select>--}}
					{{-- <input type="text" class="form-control" name="pedido_pasado" value="{{old('pedido_pasado',$o->pedido_pasado --}}
					{{-- ?? '')}}"> --}}
				{{-- </div> --}}
			{{-- </div> --}}

			{{-- <div class="col-md-6 "> --}}
			{{--	<div class="form-group"> --}}
			{{--		<label class="control-label">{{ _lang('Prioridad de desarme') }}</label> --}}
			{{--		<select class="form-control "  name="prioridad_desarmar" id="prioridad_desarmar"> --}}
			{{--			<option {{ old('prioridad',$o->prioridad ?? '') == "normal" ? 'selected' : ''}} value="normal">{{ _lang('normal') }}</option> --}}
			{{--			<option {{ old('prioridad',$o->prioridad ?? '') == "alta" ? 'selected' : ''}} value="alta">{{ _lang('alta') }}</option> --}}
			{{--			<option {{ old('prioridad',$o->prioridad ?? '') == "baja" ? 'selected' : ''}} value="baja">{{ _lang('baja') }}</option> --}}
			{{--		</select> --}}
			{{--	</div> --}}
			{{-- </div> --}}
			<input type="hidden" name="interno" value="{{$o->idCar}}">
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Interno</label>
									<select style="pointer-events:none" id="idCar" name="idCar" required class="form-control">
                                    <option value="">Seleccionar</option>
                                        @foreach ($nro_interno_datos as $interno_row)
                                                        <option value="{{ $interno_row->id }}"  {{ $o->idCar == $interno_row->id ? 'selected' : '' }} >{{ nroInternoAlias($interno_row->company_id,$interno_row->tipo_vehiculo,$interno_row->id) }}</option>
                                        @endforeach
                                    </select>

				</div>
			</div>

			{{-- <div class="col-md-6 {{ $class }}"> --}}
			{{--	<div class="form-group"> --}}
			{{--		<label class="control-label">Lugar de venta</label> --}}
			{{--		<input type="text" class="form-control" name="lugar_venta" value="{{ old('lugar_venta',$o->lugar_venta ?? '')
				}}" > --}}
			{{--	</div> --}}
			{{-- </div> --}}
			
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Productos en stock Asociado</label>
									<select  style="pointer-events:none"  id="producto_id" name="producto_id" required class="form-control">
                                    <option value="">Seleccionar</option>
                                        @foreach ($productos as $producto_row)
                                                        <option value="{{ $producto_row->id }}"  {{ $o->pieza == $producto_row->item_id ? 'selected' : '' }} >{{ nroInternoAlias($producto_row->company_id,$producto_row->cars->tipo_vehiculo ?? '',$producto_row->nro_interno) ." ". $producto_row->item_name }}</option>
                                        @endforeach
                                    </select>

				</div>
			</div>

			<!--<div class="col-md-6 {{ $class }}">
				<div class="form-group">
					<label class="control-label">Detalle de pieza</label>
					<input type="text" class="form-control" name="detalle_pieza" value="{{ old('detalle_pieza',$o->detalle_pieza ?? '')
				}}" >
				</div>
			</div>
			
				

			<div class="col-md-6 {{ $class }}">
				<div class="form-group">
					<label class="control-label">Detalle anulado</label>
					<input type="text" class="form-control" name="detalle_anulado" value="{{ old('detalle_anulado',$o->detalle_anulado ?? '')
				}}" >
				</div>
			</div>-->

			{{-- <div id="contLugares" class="col-md-12"> --}}
			{{--	<div class="form-group"> --}}
			{{--		<label class="control-label">{{ _lang('Ubicación') }}</label> --}}
			{{--		<select class="form-control " id="ubicacion" name="ubicacion" > --}}
			{{--			<option value="">{{ _lang('Select One') }}</option> --}}
			{{--			{{ create_option('lugar_entregas','id','nombre', $o->ubicacion) }} --}}
			{{--		</select> --}}
			{{--	</div> --}}
			{{-- </div> --}}

			{{--<div class="col-md-6">--}}
				{{--<div class="form-group">--}}
					{{--<label class="control-label">Ubicacion</label>--}}
					{{--<input type="text" class="form-control" name="ubicacion" value="{{ old('ubicacion',$o->ubicacion ?? '')--}}
				{{--}}" >--}}
				{{--</div>--}}
			{{--</div>--}}

			

			<!--<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">Estado</label>
					{{--<input type="text" class="form-control" name="estado" value="{{ old('estado',$o->estado ?? '')--}}
				{{--}}" >--}}

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

			<div class="col-md-6 {{ $class }}">
				<div class="form-group">
					<label class="control-label">Autorizo</label>
					<input type="text" class="form-control" name="autorizo" value="{{ old('autorizo',$o->autorizo ?? '')
				}}" >
				</div>
			</div>-->

			<!--<div class="col-md-6 {{ $class }}">
				<div class="form-group">
					<label class="control-label">Fecha estimada pieza disponible</label>
					<input type="date" class="form-control" name="fecha_estimada_pieza_disponible" value="{{ old
					('fecha_estimada_pieza_disponible',$o->fecha_estimada_pieza_disponible ?? '')
				}}" >
				</div>
			</div>
			<div class="col-md-6 {{ $class }}">
				<div class="form-group">
					<label class="control-label">Existe</label>
					<input type="text" class="form-control" name="existe" value="{{ old
					('existe',$o->existe ?? '')
				}}" >
				</div>
			</div>
			<div class="col-md-6 {{ $class }}">
				<div class="form-group">
					<label class="control-label">Falta</label>
					<input type="text" class="form-control" name="falta" value="{{ old
					('falta',$o->falta ?? '')
				}}" >
				</div>
			</div>-->

			<div class="col-md-6 {{ $class }}">
				<div class="form-group">
					<label class="control-label">Informó</label>
					<input type="text" class="form-control" name="informo_ausencia" value="{{ old
					('informo_ausencia',$o->informo_ausencia ?? '')
				}}" >
				</div>
			</div>
			<div class="col-md-6 {{ $class }}">
				<div class="form-group">
					<label class="control-label">Obs desarme o busqueda</label>
					<textarea type="text" class="form-control" name="obs_desarme_busqueda" >{{ old
					('obs_desarme_busqueda',$o->obs_desarme_busqueda ?? '')
				}}</textarea>
				</div>
			</div>

			<div class="col-md-6 {{ $class }}">
				<div class="form-group">
					<label class="control-label">Fecha desarmado</label>
					<input type="date" class="form-control" name="fecha_desarmado_anulado" value="{{ old
					('fecha_desarmado_anulado',$o->fecha_desarmado_anulado ?? '')
				}}" >
				</div>
			</div>
			
			

			<!--<div class="col-md-6 {{ $class }}">
				<div class="form-group">
					<label class="control-label">Cargando camioneta</label>
					<input type="date" class="form-control" name="cargando_camioneta" value="{{ old
					('cargando_camioneta',$o->cargando_camioneta ?? '')
				}}" >
				</div>
			</div>

			<div class="col-md-6 {{ $class }}">
				<div class="form-group">
					<label class="control-label">Entregado</label>
					<input type="text" class="form-control" name="entregado" value="{{ old
					('entregado',$o->entregado ?? '')
				}}" >
				</div>
			</div>
			<div class="col-md-6 {{ $class }}">
				<div class="form-group">
					<label class="control-label">Fecha embalado</label>
					<input type="date" class="form-control" name="fecha_embalado" value="{{ old
					('fecha_embalado',$o->fecha_embalado ?? '')
				}}" >
				</div>
			</div>-->

			<!--<div class="col-md-6 {{ $class }}">
				<div class="form-group">
					<label class="control-label">Fecha avisado a vendedor</label>
					<input type="date" class="form-control" name="fecha_avisado_vendedor" value="{{ old
					('fecha_avisado_vendedor',$o->fecha_avisado_vendedor ?? '')
				}}" >
				</div>
			</div>-->
			
			 <div class="col-md-6 "> 
				<div class="form-group"> 
					<label class="control-label">{{ _lang('Puesto desarme final(Opcional)') }}</label> 
					<input type="hidden" name="idcadete_operario" id="idcadete_operario" value="{{$o->idCadete_operario}}">
					<select id="puesto_final"  name="puesto_final" class="form-control">
                                    <option value="">Seleccionar</option>
                                        @foreach ($puestos as $puesto_row)
                                                        <option data-operario="{{ $puesto_row->user_id }}" value="{{ $puesto_row->puesto }}"  {{ $o->puesto_final == $puesto_row->puesto ? 'selected' : '' }} >{{ $puesto_row->puesto }}</option>
                                        @endforeach
                                    </select>
					</select> 
				</div> 
			 </div> 
			
			<!--<div class="col-md-6 {{ $class }}">
				<div class="form-group">
					<label class="control-label">Fecha envio a puesto</label>
					<input type="date" class="form-control" name="f_ingreso_puesto" 
					  value="{{ old('f_ingreso_puesto', isset($o->f_ingreso_puesto) ? \Carbon\Carbon::parse($o->f_ingreso_puesto)->format('Y-m-d') : '') }}">
				</div>
			</div>-->
			
		
			
			<div class="col-md-12">
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
	
	 /*$(document).on('change', '#puesto', function(e) {
                e.stopPropagation();
                var operario = $(this).find('option:selected').data('operario');
				 $("#idcadete_operario").val(operario);
            });*/
	
})(jQuery);
</script>