<form method="post" class="ajax-submit" autocomplete="off" action="{{ action('LugarEntregaController@update',
$id) }}" enctype="multipart/form-data">
	{{ csrf_field()}}
	<input name="_method" type="hidden" value="PATCH">				
	
	<div class="col-md-8">
		<div class="form-group">
		   <label class="control-label">{{ _lang('Deposito') }}</label>
		   <input type="text" class="form-control" name="nombre" value="{{ $lugar_entrega->nombre }}" required>
		</div>
	</div>

<div class="col-md-4">                    
    <div class="form-group">
        <label for="activo">Activo <span class="text-danger"></span></label>
        <select class="form-control" name="activo" id="activo" required>
            <option value="Si" {{ (old('activo', $modelo->activo ?? '') == 'Si') ? 'selected' : '' }}>Activo</option>
            <option value="No" {{ (old('activo', $modelo->activo ?? '') == 'No') ? 'selected' : '' }}>Desactivo</option>
        </select>
    </div>
</div>



	
	<div class="form-group">
	    <div class="col-md-12">
		    <button type="submit" class="btn btn-primary">{{ _lang('Update') }}</button>
	    </div>
	</div>
</form>

