@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ _lang('Nueva Configuración Sincronización') }}</h3>
                    <a href="{{ route('empresa_sincronizar_factura.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> {{ _lang('Volver') }}
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('empresa_sincronizar_factura.store') }}" method="POST">
                        @csrf

                        <!-- Campo URL de Sincronización -->
                        <div class="form-group mb-3">
                            <label for="url" class="form-label font-weight-bold">{{ _lang('URL de Sincronización') }}</label>
                            <input type="url" name="url" id="url" class="form-control" value="{{ old('url') }}" placeholder="https://ejemplo.com" required>
                            <small class="text-muted">{{ _lang('Dirección URL externa del endpoint adonde se enviarán los datos.') }}</small>
                        </div>

                        <!-- Campo Token -->
                        <div class="form-group mb-3">
                            <label for="token" class="form-label font-weight-bold">{{ _lang('Token de Autenticación') }}</label>
                            <textarea name="token" id="token" class="form-control" rows="4" placeholder="{{ _lang('Introduce el token o API Key seguro...') }}" required>{{ old('token') }}</textarea>
                            <small class="text-muted">{{ _lang('Token o llave de acceso requerida por el servicio remoto para validar la identidad.') }}</small>
                        </div>

                        <!-- Campo Company ID -->
                        <div class="form-group mb-4">
                            <label for="company_id" class="form-label font-weight-bold">{{ _lang('ID de la Empresa (Company ID)') }}</label>
							<select class="form-control select2 select-filter" name="company_id" required>
							<option value=""></option>
							{{ create_option('companies','id','business_name',old('company_id'),array('business_name=' => 'Paternal','or business_name=' => 'Pentacar')) }}
						</select>
                            <small class="text-muted">{{ _lang('Identificador numérico de la empresa asociada a este flujo de sincronización.') }}</small>
                        </div>

                        <!-- Botones de Acción -->
                        <div class="form-group text-right">
                            <button type="reset" class="btn btn-outline-secondary">{{ _lang('Limpiar') }}</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save"></i> {{ _lang('Guardar Configuración') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
