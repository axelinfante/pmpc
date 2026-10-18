@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row">
          <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ _lang('Editar Configuración Sincronización') }}</h3>
                    <a href="{{ route('empresa_sincronizar_factura.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> {{ _lang('Volver') }}
                    </a>
                </div>

                <div class="card-body">
                    <form action="{{ route('empresa_sincronizar_factura.update', $empresaSincronizarFactura->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Campo URL -->
                        <div class="form-group mb-3">
                            <label for="url" class="form-label font-weight-bold">{{ _lang('URL de Sincronización') }}</label>
                            <input type="url" name="url" id="url" class="form-control" value="{{ old('url', $empresaSincronizarFactura->url) }}" required>
                        </div>

                        <!-- Campo Token -->
                        <div class="form-group mb-3">
                            <label for="token" class="form-label font-weight-bold">{{ _lang('Token de Autenticación') }}</label>
                            <textarea name="token" id="token" class="form-control" rows="4" required>{{ old('token', $empresaSincronizarFactura->token) }}</textarea>
                        </div>

                        <!-- Campo Company ID -->
                        <div class="form-group mb-4">
                            <label for="company_id" class="form-label font-weight-bold">{{ _lang('Empresa') }}</label>
							<select class="form-control select2 select-filter" name="company_id" required>
							<option value=""></option>
							{{ create_option('companies','id','business_name',old('company_id', $empresaSincronizarFactura->company_id) ,array('business_name=' => 'Paternal','or business_name=' => 'Pentacar')) }}
						</select>
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-sync-alt"></i> {{ _lang('Actualizar Configuración') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
