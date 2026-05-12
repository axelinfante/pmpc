<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editar Registro: <strong>{{ $modelo->modelo }}</strong></h3>
        </div>
        
        <form class="validate ajax-submit" action="{{ route('modelos.update', $modelo->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card-body">
                <div class="row">
                    <!-- Nombre de la modelo -->
                    <div class="col-md-6 form-group">
                        <label>Nombre del Modelo <span class="text-danger">*</span></label>
                        <input type="text" name="modelo" class="form-control @error('modelo') is-invalid @enderror" 
                               value="{{ old('modelo', $modelo->modelo) }}" required>
                        @error('modelo') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <!-- Estado Activo -->
                    <div class="col-md-6 form-group">
                        <label>Estado</label>
                        <select name="activo" class="form-control">
                            <option value="Si" {{ old('activo', $modelo->activo) == 'Si' ? 'selected' : '' }}>Si</option>
                            <option value="No" {{ old('activo', $modelo->activo) == 'No' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('modelos.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar Cambios</button>
            </div>
        </form>
    </div>
</div>
