@extends('layouts.app')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <!-- Cabecera de la Tarjeta -->
            <div class="card-header d-flex justify-content-between align-items-center bg-white py-3">
                <div>
                    <h5 class="mb-0 text-primary">
                        <i class="fa fa-edit"></i> Modificar Registro de Sincronización
                    </h5>
                    <small class="text-muted">Inovice Interna Original Nro: <strong>{{ $sincronizacion->invoice_id ?? '0'}}</strong></small>
                </div>
                <a href="{{ route('datos_sincronizar_factura.index') }}" class="btn btn-sm btn-secondary">
                    <i class="fa fa-arrow-left"></i> Volver al listado
                </a>
            </div>

            <!-- Cuerpo del Formulario -->
            <div class="card-body">
                <form action="{{ route('datos_sincronizar_factura.update', $sincronizacion->id) }}" method="POST">
                    @csrf
                    @method('PUT') <!-- Requerido por Laravel para procesar el método UPDATE -->

                    <!-- Sección 1: Información Informativa (Solo Lectura) -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label font-weight-bold">Cotizacion</label>
                                <input type="text" class="form-control bg-light" value="{{ $sincronizacion->invoice_id }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label font-weight-bold">Fecha de Creación</label>
                                <input type="text" class="form-control bg-light" value="{{ \Carbon\Carbon::parse($sincronizacion->created_at)->format('d-m-Y H:i:s') }}" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label font-weight-bold">Última Modificación</label>
                                <input type="text" class="form-control bg-light" value="{{ \Carbon\Carbon::parse($sincronizacion->updated_at)->format('d-m-Y H:i:s') }}" readonly>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- Sección 2: Campos Editables -->
                    <!-- Control del Estado -->
                    <div class="form-group row mb-4">
                        <label for="estado" class="col-md-2 col-form-label font-weight-bold">Estado de Sincronización</label>
                        <div class="col-md-4">
                            <select name="estado" id="estado" class="form-control custom-select">
                                <option value="pendiente" {{ $sincronizacion->estado == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                                <option value="procesando" {{ $sincronizacion->estado == 'procesando' ? 'selected' : '' }}>Procesando</option>
                                <option value="completado" {{ $sincronizacion->estado == 'completado' ? 'selected' : '' }}>Completado</option>
                                <option value="error" {{ $sincronizacion->estado == 'error' ? 'selected' : '' }}>Con Error</option>
                            </select>
                        </div>
                    </div>
                   
<div class="form-group row mb-4">
    <label for="json_pmpc" class="col-md-2 col-form-label font-weight-bold">Contenido del Invoice (json_pmpc)</label>
    <div class="col-md-10">
        <div class="alert alert-warning py-2 mb-2" style="font-size: 0.85rem;">
            <i class="fa fa-info-circle"></i> <strong>Modo Vista:</strong> El contenido estructural del JSON es de solo lectura para preservar la consistencia de la integración.
        </div>
       <textarea name="json_pmpc" id="json_pmpc" rows="12" class="form-control font-monospace text-dark bg-light" style="font-size: 0.85rem; font-family: 'Courier New', Courier, monospace; line-height: 1.4;" readonly>{{ json_encode($sincronizacion->json_pmpc, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea>
    </div>
</div>

<!-- Mensaje de Error (AHORA SOLO LECTURA) -->
<div class="form-group row mb-4">
    <label for="mensaje_error" class="col-md-2 col-form-label font-weight-bold">Mensaje de Error Externo</label>
    <div class="col-md-10">
        <!-- Se agrega 'readonly' y 'bg-light' -->
        <textarea name="mensaje_error" id="mensaje_error" rows="3" class="form-control bg-light" placeholder="Ningún error reportado de forma externa actualmente..." readonly>{{ $sincronizacion->mensaje_error }}</textarea>
    </div>
</div>


                    <div class="form-group row mt-5">
                        <div class="col-md-10 offset-md-2">
							@can('editar-sincronizacion-facturacion')       
                            <button type="submit" class="btn btn-primary px-4 shadow-sm" disabled>
                                <i class="fa fa-save"></i> Guardar Cambios Realizados
                            </button>
							@endcan 
                            <a href="{{ route('datos_sincronizar_factura.index') }}" class="btn btn-outline-secondary px-4 ms-2">
                                Cancelar y Salir
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
