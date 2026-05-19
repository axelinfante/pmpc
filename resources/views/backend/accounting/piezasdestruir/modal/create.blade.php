<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Nuevo registro</h3>
        </div>
        
        <form class="validate ajax-submit" action="{{ route('modelos.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <!-- Nombre de la Modelo -->
                    <div class="col-md-6 form-group">
                        <label>Nombre del Modelo <span class="text-danger">*</span></label>
                        <input type="text" name="modelo" class="form-control @error('modelo') is-invalid @enderror" 
                               value="{{ old('modelo') }}" placeholder="Ej: SIENA HL 4P" required>
                        @error('modelo') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <!-- Estado Activo -->
                    <div class="col-md-6 form-group">
                        <label>Estado</label>
                        <select name="activo" class="form-control">
                            <option value="Si" {{ old('activo') == 'Si' ? 'selected' : '' }}>Si</option>
                            <option value="No" {{ old('activo') == 'No' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('modelos.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success">Guardar Modelo</button>
            </div>
        </form>
    </div>
</div>

