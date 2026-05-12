<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Editar Marca: <strong>{{ $marca->marca }}</strong></h3>
        </div>
        
        <form class="validate ajax-submit" action="{{ route('marcas.update', $marca->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="card-body">
                <div class="row">
                    <!-- Nombre de la Marca -->
                    <div class="col-md-6 form-group">
                        <label>Nombre de la Marca <span class="text-danger">*</span></label>
                        <input type="text" name="marca" class="form-control @error('marca') is-invalid @enderror" 
                               value="{{ old('marca', $marca->marca) }}" required>
                        @error('marca') <span class="invalid-feedback">{{ $message }}</span> @enderror
                    </div>

                    <!-- Estado Activo -->
                    <div class="col-md-6 form-group">
                        <label>Estado</label>
                        <select name="activo" class="form-control">
                            <option value="Si" {{ old('activo', $marca->activo) == 'Si' ? 'selected' : '' }}>Si</option>
                            <option value="No" {{ old('activo', $marca->activo) == 'No' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <!-- Selección de Modelos (Precargados + AJAX) -->
                    <div class="col-md-12 form-group">
                        <label>Modelos Asociados <span class="text-danger">*</span></label>
                        <select name="modelos[]" id="select-modelos-edit" class="form-control @error('modelos') is-invalid @enderror" 
                                multiple="multiple" style="width: 100%;" required>
                            
                            {{-- Lógica para mantener selecciones si hay error o cargar las existentes --}}
                            @php
                                $modelosSeleccionados = old('modelos') 
                                    ? \App\Models\Modelo::whereIn('id', old('modelos'))->get() 
                                    : $marca->modelos;
                            @endphp

                            @foreach($modelosSeleccionados as $m)
                                <option value="{{ $m->id }}" selected>{{ $m->modelo }}</option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">Puedes eliminar los actuales o buscar nuevos escribiendo su nombre.</small>
                        @error('modelos') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <div class="card-footer text-right">
                <a href="{{ route('marcas.index') }}" class="btn btn-secondary">Cancelar</a>
                <button type="submit" class="btn btn-primary">Actualizar Cambios</button>
            </div>
        </form>
    </div>
</div>


    <script>
        $(document).ready(function() {
            $('#select-modelos-edit').select2({
                placeholder: "Buscar y agregar más modelos...",
                allowClear: true,
                minimumInputLength: 2,
                ajax: {
                    url: "{{ route('modelos.buscar.ajax') }}",
                    dataType: 'json',
                    delay: 250,
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
    </script>

