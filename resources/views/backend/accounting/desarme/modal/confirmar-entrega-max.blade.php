<form method="post" id="expense" class="ajax-submit" autocomplete="off" action="{{route('orden-desarme.confirmaciones')}}" enctype="multipart/form-data">
	{{ csrf_field()}}

	<div class="col-12">
		<div class="row">
			 <input type="hidden" name="orden_id_max" id="modal_orden_id_max" value="{{ $ids }}">
			 
			 
			 <div class="col-md-12">
							<div class="table-responsive">
								<table id="order-table" class="table table-bordered">
									<thead>
										<tr>
											<th>Interno</th>
											<th>Producto</th>
										</tr>
									</thead>
									<tbody>
										@foreach($o as $item)
										<tr>
										<td>{{$item->interno ?? ''}}</td>
										<td>{{ ($item->product_id ?? null) . "-" . ($item->producto->item->item_name ?? '')}}</td> 
										<input type="hidden" name="id_desarme[]" value="{{$item->id ?? null}}">
										<input type="hidden" name="product_ids[]" value="{{$item->product_id ?? null}}">
										</tr>
										@endforeach
									</tbody>
									<tfoot class="tfoot active">
										<tr>
										</tr>
									</tfoot>
								</table>
			 
				</div>
				</div>
			<div class="col-md-6 ">
				<div class="form-group">
					<label class="control-label">Informó</label>
					<input type="text" class="form-control" name="informo_ausencia" value="{{ old
					('informo_ausencia','')
				}}" >
				</div>
			</div>
			<div class="col-md-6 ">
				<div class="form-group">
					<label class="control-label">Obs desarme o busqueda</label>
					<textarea type="text" class="form-control" name="obs_desarme_busqueda" >{{ old
					('obs_desarme_busqueda','')
				}}</textarea>
				</div>
			</div>

			<div class="col-md-6 ">
				<div class="form-group">
					<label class="control-label">Fecha desarmado</label>
					<input type="date" class="form-control" name="fecha_desarmado_anulado" value="{{ old
					('fecha_desarmado_anulado')
				}}" >
				</div>
			</div>
			
			<div class="col-md-6 "> 
				<div class="form-group"> 
					<label class="control-label">{{ _lang('Puesto desarme final(Opcional)') }}</label> 
					<select id="puesto_final"  name="puesto_final" class="form-control">
                                    <option value="">Seleccionar</option>
                                        @foreach ($puestos as $puesto_row)
                                                        <option data-operario="{{ $puesto_row->user_id }}" value="{{ $puesto_row->puesto }}"  {{ '' == $puesto_row->puesto ? 'selected' : '' }} >{{ $puesto_row->puesto }}</option>
                                        @endforeach
                                    </select>
					</select> 
				</div> 
			</div> 
			
			<div class="col-md-12">
				<div class="form-group">
					<input type="submit" class="btn btn-primary" value="Actualizar">
				</div>
			</div>

		</div>
	</div>
</form>

<script>
</script>