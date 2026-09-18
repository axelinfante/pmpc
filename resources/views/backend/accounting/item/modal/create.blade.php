<form method="post" class="validate ajax-submit" autocomplete="off" action="{{route('item.store')}}" >
	{{ csrf_field()}}
    <div class="col-12">
		<div class="row">
			
			<div class="col-md-6">
				<div class="form-group">
					<label class="control-label">{{ _lang('Product Name') }}</label>						
					<input type="text" class="form-control" name="item_name" value="" required>
				</div>
			</div>
			
			<div class="col-md-6">
				<div class="form-group">
                                <label class="control-label" for="categoria">Categoria </label>
								<select class="form-control" name="categoria" id="categoria">
									<option value="">Seleccione una categoría</option>
										<option value="AUTO" {{ old('categoria') == 'AUTO' ? 'selected' : '' }}>AUTO</option>
										<option value="CARROCERIA" {{ old('categoria') == 'CARROCERIA' ? 'selected' : '' }}>CARROCERIA</option>
										<option value="CARROCERIA / MOTOR" {{ old('categoria') == 'CARROCERIA / MOTOR' ? 'selected' : '' }}>CARROCERIA / MOTOR</option>
										<option value="INTERIOR" {{ old('categoria') == 'INTERIOR' ? 'selected' : '' }}>INTERIOR</option>
										<option value="MOTO" {{ old('categoria') == 'MOTO' ? 'selected' : '' }}>MOTO</option>
										<option value="MOTOR" {{ old('categoria') == 'MOTOR' ? 'selected' : '' }}>MOTOR</option>
										<option value="MOTOR / Periferico" {{ old('categoria') == 'MOTOR / Periferico' ? 'selected' : '' }}>MOTOR / Periferico</option>
										<option value="MOTOR /TRANS" {{ old('categoria') == 'MOTOR /TRANS' ? 'selected' : '' }}>MOTOR /TRANS</option>
										<option value="MOTOR - BD" {{ old('categoria') == 'MOTOR - BD' ? 'selected' : '' }}>MOTOR - BD</option>
										<option value="SUSPENSION" {{ old('categoria') == 'SUSPENSION' ? 'selected' : '' }}>SUSPENSION</option>
							</select>
						</div>
			</div>
			
			
			<div class="col-lg-6 mb-3">					
						<div class="form-group">
                                <label for="type">Tipo Estandar-Combo </label>
                                <select class="form-control" name="type" id="type">
								    <option value="" {{ old('type' ?? '') == 'Estandar' ? 'selected' : '' }}>Estandar</option>
                                    <option value="Combo" {{ old('type' ?? '') == 'Combo' ? 'selected' : '' }}>Combo</option>
                                </select>
						</div>
                    </div>	
					
					
					<div class="col-lg-6 mb-3">
                        <label>Productos Asociados </label>
                        <select name="combo_producto[]" id="select-combo_producto" class="form-control @error('combo_producto') is-invalid @enderror" 
                                multiple="multiple" style="width: 100%;">
                           
                        </select>
                        <small class="form-text text-muted">Escribe el nombre del producto para buscar.</small>
                        @error('combo_producto') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
					
					<div class="col-lg-4 mb-3">	
						<div class="form-group">
									<label for="allcar">Primario <span class="text-danger"></span></label>
									<select class="form-control" name="allcar" id="allcar" required>
										<option value="0" {{ old('allcar' ?? '0') == '0' ? 'selected' : '' }} >No</option>
										<option value="1" {{ old('allcar' ?? '') == '1' ? 'selected' : '' }} >Si</option>
									</select>
						</div>
					</div>
			
				<div class="col-lg-4 mb-3">					
						<div class="form-group">
                                <label for="con_oblea">C / N° Oblea <span class="text-danger"></span></label>
                                <select class="form-control" name="con_oblea" id="con_oblea" required>
								    <option value="No" {{ old('con_oblea' ?? '') == 'No' ? 'selected' : '' }}>No</option>
                                    <option value="Si" {{ old('con_oblea' ?? '') == 'Si' ? 'selected' : '' }}>Si</option>
                                </select>
						</div>
                    </div>	

									
				
			<div class="col-lg-4 mb-3">					
						<div class="form-group">
                                <label for="activo">Activo <span class="text-danger"></span></label>
                                <select class="form-control" name="activo" id="activo" required>
                                    <option value="Si" {{ old('activo' ?? '') == 'Si' ? 'selected' : '' }}>Activo</option>
                                    <option value="No" {{ old('activo' ?? '') == 'No' ? 'selected' : '' }}>Desactivo</option>
                                </select>
						</div>
             </div>		
					
			<div class="col-lg-4 mb-3">					
						<div class="form-group">
                                <label for="importado">Importado <span class="text-danger"></span></label>
                                <select class="form-control" name="importado" id="importado" required>
								    <option value="No" {{ old('importado' ?? '') == 'No' ? 'selected' : '' }}>Desactivo</option>
                                    <option value="Si" {{ old('importado' ?? '') == 'Si' ? 'selected' : '' }}>Activo</option>
                                </select>
						</div>
             </div>		
							
					
				<input type="hidden" name="item_type" value="product">
			<div class="col-md-12">
			  <div class="form-group">
				<button type="submit" class="btn btn-primary">{{ _lang('Update') }}</button>
			  </div>
			</div>
		</div>
	</div>
</form>

<script>
    $(document).ready(function() {
    $('#select-combo_producto').select2({
        placeholder: "Buscar modelos...",
        allowClear: true,
        minimumInputLength: 0,
        ajax: {
            //url: "{{ route('modelos.buscar.ajax') }}",
			url: function () {
                    var params = new URLSearchParams({
                        table: 'items',
                        value: 'id',
                        display: 'item_name',
                        where: '',
                        whereraw: "activo='Si' and allcar=1"
                    });
                    return _url + '/ajax/get_table_data?' + params.toString();
                },
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                return { results: data }; // Apuntamos a .data si usas paginate() de Laravel
            },
            cache: true
        }
    });
}); 

</script>
