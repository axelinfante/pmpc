<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Nueva Marca</h3>
        </div>
        
        <form class="validate ajax-submit" action="{{ route('marcas.store') }}" method="POST">
            @csrf
            <div class="card-body">
                <div class="row">
                    <!-- Nombre de la Marca -->
                    <div class="col-md-6 form-group">
                        <label>Nombre de la Marca <span class="text-danger">*</span></label>
                        <input type="text" name="marca" class="form-control @error('marca') is-invalid @enderror" 
                               value="{{ old('marca') }}" placeholder="Ej: Toyota" required>
                        @error('marca') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <!-- Estado Activo -->
                    <div class="col-md-6 form-group">
                        <label>Estado</label>
                        <select name="activo" class="form-control">
                            <option value="Si" {{ old('activo') == 'Si' ? 'selected' : '' }}>Si</option>
                            <option value="No" {{ old('activo') == 'No' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <!-- Selección de Modelos (Búsqueda AJAX) -->
                    <div class="col-md-12 form-group">
                        <label>Asociar Modelos <span class="text-danger">*</span></label>
                        <select name="modelos[]" id="select-modelos" class="form-control @error('modelos') is-invalid @enderror" 
                                multiple="multiple" style="width: 100%;" required>
                            @if(old('modelos'))
                                @foreach(\App\Models\Modelo::whereIn('id', old('modelos'))->get() as $m)
                                    <option value="{{ $m->id }}" selected>{{ $m->modelo }}</option>
                                @endforeach
                            @endif
                        </select>
                        <small class="form-text text-muted">Escribe el nombre del modelo para buscar (mínimo 2 letras).</small>
                        @error('modelos') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('marcas.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-success">Guardar Marca</button>
            </div>
        </form>
    </div>
</div>

    <script>
        $(document).ready(function() {
            $('#select-modelos').select2({
              //  theme: 'bootstrap4',
                placeholder: "Buscar modelos...",
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route('modelos.buscar.ajax') }}",
                    dataType: 'json',
                    delay: 250, // Espera para no saturar el servidor
                    data: function (params) {
                        return { q: params.term };
                    },
                    processResults: function (data) {
                        return { results: data };
                    },
                    cache: true
                }
            });
        });


