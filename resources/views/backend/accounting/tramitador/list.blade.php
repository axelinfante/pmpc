@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-lg-12">
            <div class="card mt-2">
                <span class="panel-title d-none">{{ _lang('Tramitadores') }}</span>
                <div class="card-body">
                    <div class="row">
                        <div class="col mb-2">
                            <a class="btn btn-primary btn-xs" data-title="{{ _lang('Add New Car') }}"
                                href="{{ route('tramitadores.create') }}">
                                <i class="ti-plus"></i> {{ _lang('Add New') }}
                            </a>
                        </div>
                        <div class="col-lg-3">
                            <select class="form-control select2 select-filter" data-placeholder="{{ _lang('All Status') }}"
                                name="status" multiple="true">
                                @forelse($estados as $estado)
                                    <option value="{{ $estado->id }}"> {{ $estado->estado }}</option>
                                @empty
                                    <!-- Empty state -->
                                @endforelse
                            </select>
                        </div>
                        <div class="col-lg-3">
                            <select class="form-control select2 select-filter"
                                data-placeholder="{{ _lang('Estado Tramite') }}" name="estado_tramite" id="estado_tramite"
                                multiple="true">
                                <option value=""> Filtrar por estado del trámite</option>
                                <option value="Pendiente" style="background-color: #FFFFFF;">Pendientes</option>
                                <option value="En Proceso" style="background-color: #33FFAC;" #33FFAC>En Proceso</option>
                                <option value="En Gestoria" style="background-color: #33A8FF;">En Gestoría</option>
                                <option value="Finalizado" style="background-color: #FFC433;">Finalizado</option>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <div class="">
                        <table id="vehiculos_table" class="table table-bordered" style="width:100%; min-height: 30px;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width:30px;">{{ _lang('Action') }}</th>
                                    <th style="width: 50px;">{{ _lang('Dominio') }}</th>
                                    <th style="width: 80px;">{{ _lang('No.Interno') }}</th>
                                    <th style="width: 50px;">{{ _lang('F.Asignacion') }}</th>
                                    <th style="width: 100px;">{{ _lang('Tramitador') }}</th>
                                    <th style="width: 60px;">{{ _lang('Cia Aseg') }}</th>
                                    <th style="width: 80px;">{{ _lang('Compañia') }}</th>
                                    <th style="width: 80px;">{{ _lang('Siniestro') }}</th>
                                    <th style="width: 120px;">{{ _lang('Marca y modelo') }}</th>
                                    <th style="width: 50px;">{{ _lang('Fecha Inicio') }}</th>
                                    <th style="width: 50px;">{{ _lang('Fecha Finalizacion') }}</th>
                                    <th style="width: 80px;">{{ _lang('Tramite') }}</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
@push('styles')
    <style>
        .action-buttons {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .action-buttons a,
        .action-buttons form {
            margin: 2px;
            flex: 1 0 30%;
            /* Ajusta el tamaño de los botones para que se distribuyan en 3 por línea */
            text-align: center;
        }

        .dataTables_wrapper .btn-group .dropdown-menu .action {
            position: absolute !important;
            z-index: 9999;
            will-change: transform;
        }

        .dataTables_scrollBody {
            min-height: 400px;
        }
    </style>
@endpush


@section('js-script')
    <script>
        const routes = {
            exportExcel: "{{ route('tramitadores.export.excel') }}",
            exportPDF: "{{ route('tramitadores.export.pdf') }}",
            csrfToken: "{{ csrf_token() }}"
        };
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.datatables.net/plug-ins/1.10.24/sorting/datetime-moment.js"></script>
    <script src="{{ asset('public/backend/assets/js/ajax-datatable/tramitador.js') }}"></script>
@endsection
