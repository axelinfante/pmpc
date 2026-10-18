@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row">
          <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">{{ _lang('Detalle de Sincronización') }} #{{ $empresaSincronizarFactura->id }}</h3>
                    <a href="{{ route('empresa_sincronizar_factura.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fa fa-arrow-left"></i> {{ _lang('Volver al Listado') }}
                    </a>
                </div>

                <div class="card-body p-0">
                    <table class="table table-bordered mb-0">
                        <tbody>
                            <tr>
                                <th>{{ _lang('ID de la Empresa (Company ID)') }}</th>
                                <td>{{ $empresaSincronizarFactura->company->business_name ?? '' }}</td>
                            </tr>
                            <tr>
                                <th>{{ _lang('URL de Destino') }}</th>
                                <td><a href="{{ $empresaSincronizarFactura->url }}" target="_blank" class="text-break">{{ $empresaSincronizarFactura->url }}</a></td>
                            </tr>
                            <tr>
                                <th>{{ _lang('Token de Acceso Seguro') }}</th>
                                <td>
                                    <pre class="bg-light p-3 border rounded text-break mb-0" style="white-space: pre-wrap;"><code class="text-dark small">{{ $empresaSincronizarFactura->token }}</code></pre>
                                </td>
                            </tr>
                            <tr>
                                <th>{{ _lang('Fecha de Creación') }}</th>
                                <td>{{ $empresaSincronizarFactura->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>{{ _lang('Última Actualización') }}</th>
                                <td>{{ $empresaSincronizarFactura->updated_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            @if($empresaSincronizarFactura->deleted_at)
                                <tr class="table-danger">
                                    <th>{{ _lang('Fecha de Eliminación') }}</th>
                                    <td class="text-danger font-weight-bold">
                                        <i class="fa fa-calendar-times"></i> {{ $empresaSincronizarFactura->deleted_at->format('d/m/Y H:i:s') }} ({{ _lang('En Papelera de Reciclaje') }})
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
                
                <div class="card-footer text-right">
                    @if(!$empresaSincronizarFactura->deleted_at)
                        <a href="{{ route('empresa_sincronizar_factura.edit', $empresaSincronizarFactura->id) }}" class="btn btn-warning text-white btn-sm">
                            <i class="fa fa-edit"></i> {{ _lang('Editar') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
