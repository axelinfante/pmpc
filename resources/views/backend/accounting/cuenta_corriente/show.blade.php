@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <h4 class="panel-title">
                            <i class="fas fa-file-invoice-dollar"></i>
                            {{ _lang('Cuenta Corriente') }} - {{ $contact->contact_name }}
                        </h4>
                        <p class="mb-0">
                            {{ _lang('Grupo') }}: <strong>{{ $contact->group->name ?? 'N/A' }}</strong> |
                            {{ _lang('Email') }}: <strong>{{ $contact->contact_email ?? 'N/A' }}</strong>
                        </p>
                    </div>
                    <div class="col-md-6 text-right">
                        <a href="{{ route('contacts.show', $id) }}" class="btn btn-primary btn-xs">
                            <i class="fas fa-arrow-left"></i> {{ _lang('Volver al Contacto') }}
                        </a>
                        <a href="{{ route('cuenta_corriente.index') }}" class="btn btn-secondary btn-xs">
                            <i class="fas fa-list"></i> {{ _lang('Lista de Cuentas') }}
                        </a>
                        <button type="button" class="btn btn-success btn-xs" id="btn-refresh">
                            <i class="fas fa-sync-alt"></i> {{ _lang('Actualizar') }}
                        </button>
                        <a href="{{ route('cuenta_corriente.devolucion.create', ['id' => $id]) }}" class="btn btn-info btn-xs ajax-modal" data-title="{{ _lang('Devolución de Saldo') }}">
                            <i class="fas fa-hand-holding-usd"></i> {{ _lang('Devolución de Saldo') }}
                        </a>
                        <a href="{{ route('cuenta_corriente.ingreso.create', ['id' => $id]) }}" class="btn btn-success btn-xs ajax-modal" data-title="{{ _lang('Ingreso Manual') }}">
                            <i class="fas fa-plus-circle"></i> {{ _lang('Ingreso Manual') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Resumen de Saldos -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-0">{{ _lang('Saldo Actual ARS') }}</h6>
                                        <h3 class="mb-0" id="saldo-actual-peso">0.00</h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-balance-scale fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-0">{{ _lang('Saldo Actual USD') }}</h6>
                                        <h3 class="mb-0" id="saldo-actual-usd">0.00</h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-dollar-sign fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-0">{{ _lang('Total Débitos ARS') }}</h6>
                                        <h3 class="mb-0" id="total-debitos-peso">0.00</h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-arrow-down fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="card bg-warning text-white">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="mb-0">{{ _lang('Total Créditos ARS') }}</h6>
                                        <h3 class="mb-0" id="total-creditos-peso">0.00</h3>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="fas fa-arrow-up fa-2x"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="row mb-3">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-body">
                                <form id="form-filtros">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ _lang('Fecha Desde') }}</label>
                                                <input type="text" class="form-control datepicker" id="fecha-desde" name="fecha_desde" value=""  autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ _lang('Fecha Hasta') }}</label>
                                                <input type="text" class="form-control datepicker" id="fecha-hasta" name="fecha_hasta" value=""  autocomplete="off">
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ _lang('Tipo Comprobante') }}</label>
                                                <select class="form-control select2" id="tipo-comprobante" name="tipo_comprobante" data-placeholder="{{ _lang('Todos') }}">
                                                    <option value=""></option>
                                                    <option value="App\Invoice">{{ _lang('Factura') }}</option>
                                                    <option value="App\Transaction">{{ _lang('Transacción') }}</option>
                                                    <option value="App\ProductReturn">{{ _lang('Devolución') }}</option>
                                                    <option value="App\Quotation">{{ _lang('Cotización') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label>{{ _lang('Moneda') }}</label>
                                                <select class="form-control select2" id="moneda" name="moneda" data-placeholder="{{ _lang('Ambas') }}">
                                                    <option value=""></option>
                                                    <option value="peso">{{ _lang('Pesos (ARS)') }}</option>
                                                    <option value="usd">{{ _lang('Dólares (USD)') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12 text-right">
                                            <button type="button" class="btn btn-primary" id="btn-filtrar">
                                                <i class="fas fa-filter"></i> {{ _lang('Filtrar') }}
                                            </button>
                                            <button type="button" class="btn btn-secondary" id="btn-limpiar">
                                                <i class="fas fa-broom"></i> {{ _lang('Limpiar') }}
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Movimientos -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table id="cuenta-corriente-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>{{ _lang('Fecha') }}</th>
                                        <th>{{ _lang('Tipo') }}</th>
                                        <th>{{ _lang('Referencia') }}</th>
                                        <th>{{ _lang('Descripción') }}</th>
                                        <th class="text-right">{{ _lang('Débito ARS') }}</th>
                                        <th class="text-right">{{ _lang('Crédito ARS') }}</th>
                                        <th class="text-right">{{ _lang('Saldo ARS') }}</th>
                                        <th class="text-right">{{ _lang('Débito USD') }}</th>
                                        <th class="text-right">{{ _lang('Crédito USD') }}</th>
                                        <th class="text-right">{{ _lang('Saldo USD') }}</th>
                                        <th>{{ _lang('Conversión') }}</th>
                                        <th>{{ _lang('Acciones') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Los datos se cargan via AJAX -->
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="4" class="text-right">{{ _lang('Totales') }}:</th>
                                        <th class="text-right" id="footer-debito-peso">0.00</th>
                                        <th class="text-right" id="footer-credito-peso">0.00</th>
                                        <th class="text-right" id="footer-saldo-peso">0.00</th>
                                        <th class="text-right" id="footer-debito-usd">0.00</th>
                                        <th class="text-right" id="footer-credito-usd">0.00</th>
                                        <th class="text-right" id="footer-saldo-usd">0.00</th>
                                        <th class="text-center" id="footer-conversiones">0</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('js-script')
<script>

// Usar jQuery.noConflict() para evitar conflictos
jQuery(document).ready(function($) {
    // Verificar que DataTables esté disponible
    if (typeof $.fn.dataTable === 'undefined') {
        console.error('DataTables no está cargado. Verifica que se incluyan los scripts necesarios.');
        alert('Error: DataTables no está cargado. La tabla no funcionará correctamente.');
        return;
    }

    // El servidor ordena los datos por ID ascendente para cálculo correcto de saldos
    // Desactivamos ordenamiento en DataTables para mantener el orden del servidor

    // Inicializar datepicker - CONFIGURACIÓN PARA FILTROS VACÍOS
    // Verificar que jQuery UI datepicker esté disponible
    if (typeof $.fn.datepicker !== 'undefined') {
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true,
            clearBtn: true
        }).on('show', function() {
            // Prevenir selección automática de fecha
            if (!$(this).val()) {
                $(this).datepicker('setDate', null);
            }
        });
    } else {
        console.warn('jQuery UI Datepicker no está disponible. Los filtros de fecha usarán inputs normales.');
        // Convertir a inputs de tipo date nativos como fallback
        $('.datepicker').attr('type', 'date');
    }

    // Inicializar select2 - CONFIGURACIÓN PARA FILTROS VACÍOS
    $('.select2').select2({
        width: '100%',
        placeholder: function() {
            return $(this).data('placeholder') || 'Seleccionar...';
        },
        allowClear: true
    });

    // FORZAR FILTROS VACÍOS AL CARGAR LA PÁGINA
        // Limpiar datepickers de manera segura
        $('#fecha-desde').val('');
        $('#fecha-hasta').val('');
        
        // Solo usar datepicker('update') si está disponible
        if (typeof $.fn.datepicker !== 'undefined') {
            try {
                $('#fecha-desde').datepicker('update');
                $('#fecha-hasta').datepicker('update');
            } catch (e) {
                console.warn('Error al actualizar datepicker:', e.message);
            }
        }

        // Limpiar select2 - asegurar opción vacía seleccionada
        $('#tipo-comprobante').val('').trigger('change.select2');
        $('#moneda').val('').trigger('change.select2');

        // Limpiar cualquier almacenamiento local específico de filtros
        if (localStorage.getItem('filtros_cuenta_corriente_' + clienteId)) {
            localStorage.removeItem('filtros_cuenta_corriente_' + clienteId);
        }

    // Variables globales
    var table;
    var clienteId = {{ $id }};
    var currencyPeso = '{{ $currency }}';
    var currencyUsd = 'USD';
    
    // Verificar que las librerías necesarias estén cargadas
    function verificarLibrerias() {
        var libreriasCargadas = true;
        
        if (typeof jQuery === 'undefined') {
            console.error('jQuery no está cargado');
            libreriasCargadas = false;
        }
        
        if (typeof $.fn.dataTable === 'undefined') {
            console.error('DataTables no está cargado');
            libreriasCargadas = false;
        }
        
        if (typeof $.fn.select2 === 'undefined') {
            console.warn('Select2 no está cargado. Los selects usarán funcionalidad nativa.');
        }
        
        if (typeof $.fn.datepicker === 'undefined') {
            console.warn('jQuery UI Datepicker no está cargado. Los filtros de fecha usarán inputs nativos.');
        }
        
        return libreriasCargadas;
    }
    
    // Ejecutar verificación
    if (!verificarLibrerias()) {
        alert('Algunas librerías necesarias no están cargadas. La funcionalidad puede estar limitada.');
    }

    // Inicializar DataTable
    function inicializarDataTable() {
        // Verificar que DataTables esté disponible antes de inicializar
        if (typeof $.fn.dataTable === 'undefined') {
            console.error('No se puede inicializar DataTable: la librería no está cargada');
            return null;
        }
        
        table = $('#cuenta-corriente-table').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 100, // Mostrar 100 registros por página
            lengthMenu: [[10, 25, 50, 100, 250, 500, -1], [10, 25, 50, 100, 250, 500, "Todos"]],
            ajax: {
                url: '{{ route("cuenta_corriente.get_movimientos", $id) }}',
                type: 'GET',
                data: function(d) {
                    // Solo enviar filtros si tienen valor explícito
                    var filtros = {};

                    var fechaDesde = $('#fecha-desde').val();
                    var fechaHasta = $('#fecha-hasta').val();
                    var tipoComprobante = $('#tipo-comprobante').val();
                    var moneda = $('#moneda').val();

                    if (fechaDesde) filtros.fecha_desde = fechaDesde;
                    if (fechaHasta) filtros.fecha_hasta = fechaHasta;
                    if (tipoComprobante) filtros.tipo_comprobante = tipoComprobante;
                    if (moneda) filtros.moneda = moneda;

                    // Guardar filtros en localStorage para persistencia (opcional)
                    if (Object.keys(filtros).length > 0) {
                        localStorage.setItem('filtros_cuenta_corriente_' + clienteId, JSON.stringify(filtros));
                    } else {
                        localStorage.removeItem('filtros_cuenta_corriente_' + clienteId);
                    }

                    // Combinar parámetros de DataTables con nuestros filtros
                    // Usar $.extend para no sobreescribir los parámetros de DataTables
                    return $.extend({}, d, filtros);
                },
                dataSrc: function(json) {
                    // Asegurar que DataTables reciba los datos en el formato correcto
                    if (json && json.data) {
                        return json.data;
                    }
                    return [];
                }
            },
            initComplete: function(settings, json) {
                console.log('DataTable inicializada y datos cargados');
                // Cargar filtros guardados si existen
                var savedFilters = localStorage.getItem('filtros_cuenta_corriente_' + clienteId);
                if (savedFilters) {
                    try {
                        var filters = JSON.parse(savedFilters);
                        if (filters.fecha_desde) {
                            $('#fecha-desde').val(filters.fecha_desde);
                            if (typeof $.fn.datepicker !== 'undefined') {
                                $('#fecha-desde').datepicker('update');
                            }
                        }
                        if (filters.fecha_hasta) {
                            $('#fecha-hasta').val(filters.fecha_hasta);
                            if (typeof $.fn.datepicker !== 'undefined') {
                                $('#fecha-hasta').datepicker('update');
                            }
                        }
                        if (filters.tipo_comprobante) {
                            $('#tipo-comprobante').val(filters.tipo_comprobante);
                            if (typeof $.fn.select2 !== 'undefined') {
                                $('#tipo-comprobante').trigger('change.select2');
                            }
                        }
                        if (filters.moneda) {
                            $('#moneda').val(filters.moneda);
                            if (typeof $.fn.select2 !== 'undefined') {
                                $('#moneda').trigger('change.select2');
                            }
                        }
                    } catch (e) {
                        console.error('Error al cargar filtros guardados:', e);
                    }
                }
            },
            drawCallback: function(settings) {
                console.log('DataTable redibujada con ' + settings.json.recordsTotal + ' registros');
            },
            columns: [
                {
                    data: 'created_at',
                    name: 'created_at',

                    render: function(data) {
                        if (data) {
                            try {
                                // Parsear fecha ISO (YYYY-MM-DD HH:mm:ss)
                                var date = new Date(data.replace(' ', 'T'));
                                if (!isNaN(date.getTime())) {
                                    // Formatear fecha en formato local argentino
                                    var formattedDate = date.toLocaleDateString('es-AR') + ' ' +
                                                        date.toLocaleTimeString('es-AR', {hour: '2-digit', minute: '2-digit', second: '2-digit'});
                                    return formattedDate;
                                }
                            } catch (e) {
                                console.error('Error al parsear fecha:', data, e);
                            }
                            // Si hay error, devolver la fecha original
                            return data;
                        }
                        return '';
                    }
                },
                {
                    data: 'comprobable_type',
                    name: 'comprobable_type'
                },
                {
                    data: 'comprobable_id',
                    name: 'comprobable_id'
                },
                {
                    data: 'nota',
                    name: 'nota'
                },
                {
                    data: 'debe_peso',
                    name: 'debe_peso',
                    className: 'text-right',
                    render: function(data) {

                        if (data && parseFloat(data) > 0) {
                            return '<span class="text-danger">' + formatCurrency(data, currencyPeso) + '</span>';
                        }
                        return formatCurrency(data || 0, currencyPeso);
                    }
                },
                {
                    data: 'haber_peso',
                    name: 'haber_peso',
                    className: 'text-right',
                    render: function(data) {
                        if (data && parseFloat(data) > 0) {
                            return '<span class="text-success">' + formatCurrency(data, currencyPeso) + '</span>';
                        }
                        return formatCurrency(data || 0, currencyPeso);
                    }
                },
                {
                    data: 'saldo_peso',
                    name: 'saldo_peso',
                    className: 'text-right',
                    render: function(data) {
                        var saldo = parseFloat(data || 0);
                        var clase = saldo >= 0 ? 'text-success' : 'text-danger';
                        return '<span class="' + clase + '">' + formatCurrency(data, currencyPeso) + '</span>';
                    }
                },
                {
                    data: 'debe_usd',
                    name: 'debe_usd',
                    className: 'text-right',
                    render: function(data) {
                        if (data && parseFloat(data) > 0) {
                            return '<span class="text-danger">' + formatCurrency(data, currencyUsd) + '</span>';
                        }
                        return formatCurrency(data || 0, currencyUsd);
                    }
                },
                {
                    data: 'haber_usd',
                    name: 'haber_usd',
                    className: 'text-right',
                    render: function(data) {
                        if (data && parseFloat(data) > 0) {
                            return '<span class="text-success">' + formatCurrency(data, currencyUsd) + '</span>';
                        }
                        return formatCurrency(data || 0, currencyUsd);
                    }
                },
                {
                    data: 'saldo_usd',
                    name: 'saldo_usd',
                    className: 'text-right',
                    render: function(data) {
                        var saldo = parseFloat(data || 0);
                        var clase = saldo >= 0 ? 'text-success' : 'text-danger';
                        return '<span class="' + clase + '">' + formatCurrency(data, currencyUsd) + '</span>';
                    }
                },
                {
                    data: 'tiene_conversion',
                    name: 'tiene_conversion',
                    className: 'text-center',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        if (data == true || data == 1) {
                            var html = '<div class="conversion-info" style="font-size: 0.85rem;">';

                            // Mostrar información de conversión
                            if (row.monto_original && row.moneda_original && row.monto_convertido && row.moneda_convertida) {
                                html += '<div><strong>' + row.moneda_original + ' ' + parseFloat(row.monto_original).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</strong></div>';
                                html += '<div><i class="fas fa-exchange-alt mx-1"></i> Tasa: ' + parseFloat(row.tasa_aplicada || row.tasa_cambio || 1).toLocaleString('es-AR', {minimumFractionDigits: 4, maximumFractionDigits: 4}) + '</div>';
                                html += '<div><strong>' + row.moneda_convertida + ' ' + parseFloat(row.monto_convertido).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</strong></div>';

                                // Mostrar monto aplicado a factura si existe
                                if (row.monto_aplicado && row.moneda_aplicada) {
                                    html += '<div class="mt-1"><i class="fas fa-check-circle text-success mr-1"></i> Aplicado: ' +
                                            row.moneda_aplicada + ' ' + parseFloat(row.monto_aplicado).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</div>';
                                }

                                // Mostrar sobrante si existe
                                if (row.sobrante && row.moneda_sobrante) {
                                    html += '<div class="mt-1"><i class="fas fa-plus-circle text-info mr-1"></i> Sobrante: ' +
                                            row.moneda_sobrante + ' ' + parseFloat(row.sobrante).toLocaleString('es-AR', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + '</div>';
                                }
                            }

                            if (row.detalle_conversion) {
                                html += '<div class="text-muted mt-1"><small>' + row.detalle_conversion + '</small></div>';
                            }
                            html += '</div>';
                            return html;
                        }
                        return '';
                    }
                },
                {
                    data: 'acciones',
                    name: 'acciones',
                    className: 'text-center',
                    orderable: false,
                    searchable: false
                }
            ],
            order: [[0, 'asc']], // Ordenar por primera columna (Fecha) ascendente por defecto
            ordering: true, // Habilitar ordenamiento
            // language: {
            //     url: '{{ asset("public/assets/datatables/lang/Spanish.json") }}'
            // },
            dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                 '<"row"<"col-sm-12 col-md-6"B><"col-sm-12 col-md-6"p>>' +
                 '<"row"<"col-sm-12"tr>>' +
                 '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
            buttons: [
                {
                    extend: 'copy',
                    text: '<i class="fas fa-copy"></i> {{ _lang("Copiar") }}',
                    className: 'btn btn-secondary btn-xs'
                },
                {
                    extend: 'excel',
                    text: '<i class="fas fa-file-excel"></i> {{ _lang("Excel") }}',
                    className: 'btn btn-success btn-xs'
                },
                {
                    extend: 'pdf',
                    text: '<i class="fas fa-file-pdf"></i> {{ _lang("PDF") }}',
                    className: 'btn btn-danger btn-xs'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> {{ _lang("Imprimir") }}',
                    className: 'btn btn-info btn-xs'
                }
            ],
            footerCallback: function (row, data, start, end, display) {
                var api = this.api();

                // Calcular totales ARS (solo de la página actual)
                var totalDebePeso = api
                    .column(4, { page: 'current' })
                    .data()
                    .reduce(function (a, b) {
                        return parseFloat(a) + parseFloat(b);
                    }, 0);

                var totalHaberPeso = api
                    .column(5, { page: 'current' })
                    .data()
                    .reduce(function (a, b) {
                        return parseFloat(a) + parseFloat(b);
                    }, 0);

                // Calcular totales USD (solo de la página actual)
                var totalDebeUsd = api
                    .column(7, { page: 'current' })
                    .data()
                    .reduce(function (a, b) {
                        return parseFloat(a) + parseFloat(b);
                    }, 0);

                var totalHaberUsd = api
                    .column(8, { page: 'current' })
                    .data()
                    .reduce(function (a, b) {
                        return parseFloat(a) + parseFloat(b);
                    }, 0);

                // Obtener saldos actuales (del último movimiento de toda la cuenta)
                // Necesitamos hacer una solicitud AJAX para obtener los saldos reales
                $.ajax({
                    url: '{{ route("cuenta_corriente.get_resumen", $id) }}',
                    type: 'GET',
                    async: false,
                    success: function(resumenData) {
                        var saldoActualPeso = parseFloat(resumenData.saldo_actual_peso || 0);
                        var saldoActualUsd = parseFloat(resumenData.saldo_actual_usd || 0);
                        var totalDebePesoGeneral = parseFloat(resumenData.total_debe_peso || 0);
                        var totalHaberPesoGeneral = parseFloat(resumenData.total_haber_peso || 0);
                        var totalDebeUsdGeneral = parseFloat(resumenData.total_debe_usd || 0);
                        var totalHaberUsdGeneral = parseFloat(resumenData.total_haber_usd || 0);

                        // Actualizar footer con totales generales
                        $('#footer-debito-peso').html(formatCurrency(totalDebePesoGeneral, currencyPeso));
                        $('#footer-credito-peso').html(formatCurrency(totalHaberPesoGeneral, currencyPeso));
                        $('#footer-saldo-peso').html(formatCurrency(saldoActualPeso, currencyPeso));
                        $('#footer-debito-usd').html(formatCurrency(totalDebeUsdGeneral, currencyUsd));
                        $('#footer-credito-usd').html(formatCurrency(totalHaberUsdGeneral, currencyUsd));
                        $('#footer-saldo-usd').html(formatCurrency(saldoActualUsd, currencyUsd));

                        // Actualizar resumen
                        $('#saldo-actual-peso').html(formatCurrency(saldoActualPeso, currencyPeso));
                        $('#saldo-actual-usd').html(formatCurrency(saldoActualUsd, currencyUsd));
                        $('#total-debitos-peso').html(formatCurrency(totalDebePesoGeneral, currencyPeso));
                        $('#total-creditos-peso').html(formatCurrency(totalHaberPesoGeneral, currencyPeso));
                    },
                    error: function() {
                        // Si falla la solicitud, usar los datos de la página actual
                        var ultimoSaldoPeso = 0;
                        var ultimoSaldoUsd = 0;
                        var pageData = api.rows({ page: 'current' }).data();
                        if (pageData.length > 0) {
                            ultimoSaldoPeso = parseFloat(pageData[pageData.length - 1].saldo_peso || 0);
                            ultimoSaldoUsd = parseFloat(pageData[pageData.length - 1].saldo_usd || 0);
                        }

                        $('#footer-debito-peso').html(formatCurrency(totalDebePeso, currencyPeso));
                        $('#footer-credito-peso').html(formatCurrency(totalHaberPeso, currencyPeso));
                        $('#footer-saldo-peso').html(formatCurrency(ultimoSaldoPeso, currencyPeso));
                        $('#footer-debito-usd').html(formatCurrency(totalDebeUsd, currencyUsd));
                        $('#footer-credito-usd').html(formatCurrency(totalHaberUsd, currencyUsd));
                        $('#footer-saldo-usd').html(formatCurrency(ultimoSaldoUsd, currencyUsd));

                        $('#saldo-actual-peso').html(formatCurrency(ultimoSaldoPeso, currencyPeso));
                        $('#saldo-actual-usd').html(formatCurrency(ultimoSaldoUsd, currencyUsd));
                        $('#total-debitos-peso').html(formatCurrency(totalDebePeso, currencyPeso));
                        $('#total-creditos-peso').html(formatCurrency(totalHaberPeso, currencyPeso));
                    }
                });

                // Calcular total de conversiones en la página actual
                var totalConversiones = 0;
                var pageData = api.rows({ page: 'current' }).data();
                for (var i = 0; i < pageData.length; i++) {
                    if (pageData[i].tiene_conversion == true || pageData[i].tiene_conversion == 1) {
                        totalConversiones++;
                    }
                }
                $('#footer-conversiones').html(totalConversiones);
            }
        });
    }

    // Función para formatear moneda
    function formatCurrency(value, currency) {
        var number = parseFloat(value || 0);
        var symbol = currency === 'USD' ? 'US$' : '$';
        return symbol + ' ' + number.toLocaleString('es-AR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Botón filtrar
    $('#btn-filtrar').click(function() {
        // Recargar DataTable con los filtros actuales
        table.ajax.reload(null, false); // false para mantener la página actual
    });

    // Botón limpiar - LIMPIEZA COMPLETA
    $('#btn-limpiar').click(function() {
        // 1. Limpiar formulario
        $('#form-filtros')[0].reset();

        // 2. Limpiar datepickers específicamente
        $('#fecha-desde').val('');
        $('#fecha-hasta').val('');
        
        // Solo usar datepicker('update') si está disponible
        if (typeof $.fn.datepicker !== 'undefined') {
            try {
                $('#fecha-desde').datepicker('update');
                $('#fecha-hasta').datepicker('update');
            } catch (e) {
                console.warn('Error al actualizar datepicker:', e.message);
            }
        }

        // 3. Limpiar select2 - forzar opción vacía
        $('#tipo-comprobante').val('');
        $('#moneda').val('');
        
        if (typeof $.fn.select2 !== 'undefined') {
            $('#tipo-comprobante').trigger('change.select2');
            $('#moneda').trigger('change.select2');
        }

        // 4. Limpiar almacenamiento local
        localStorage.removeItem('filtros_cuenta_corriente_' + clienteId);

        // 5. Recargar DataTable sin filtros
        if (table && typeof table.ajax === 'function') {
            table.ajax.reload();
        }

        console.log('Filtros limpiados completamente');
    });

    // Aplicar filtros automáticamente cuando cambian (opcional)
    $('#fecha-desde, #fecha-hasta').on('change', function() {
        setTimeout(function() {
            table.ajax.reload();
        }, 100);
    });

    $('#tipo-comprobante, #moneda').on('change', function() {
        setTimeout(function() {
            table.ajax.reload();
        }, 100);
    });

// Botón actualizar
$('#btn-refresh').click(function() {
    // Recargar DataTable
    table.ajax.reload();

    // También actualizar resumen
    $.ajax({
        url: '{{ route("cuenta_corriente.get_resumen", $id) }}',
        type: 'GET',
        success: function(data) {
            $('#saldo-actual-peso').html(formatCurrency(data.saldo_actual_peso, currencyPeso));
            $('#saldo-actual-usd').html(formatCurrency(data.saldo_actual_usd, currencyUsd));
            $('#total-debitos-peso').html(formatCurrency(data.total_debe_peso, currencyPeso));
            $('#total-creditos-peso').html(formatCurrency(data.total_haber_peso, currencyPeso));
        }
    });
});

// Cargar resumen inicial
$.ajax({
    url: '{{ route("cuenta_corriente.get_resumen", $id) }}',
    type: 'GET',
    success: function(data) {
        $('#saldo-actual-peso').html(formatCurrency(data.saldo_actual_peso, currencyPeso));
        $('#saldo-actual-usd').html(formatCurrency(data.saldo_actual_usd, currencyUsd));
        $('#total-debitos-peso').html(formatCurrency(data.total_debe_peso, currencyPeso));
        $('#total-creditos-peso').html(formatCurrency(data.total_haber_peso, currencyPeso));
    }
});

    // Inicializar DataTable después de 500ms
    setTimeout(function() {
        inicializarDataTable();
        
        if (table) {
            console.log('DataTable cargada automáticamente con filtros vacíos');
            
            // Forzar una recarga inicial para asegurar que se carguen los datos
            setTimeout(function() {
                if (table && typeof table.ajax === 'function') {
                    table.ajax.reload();
                    console.log('Carga inicial forzada');
                }
            }, 1000);
        } else {
            console.error('DataTable no se inicializó correctamente');
        }
    }, 500);

    // Verificación adicional después de 2 segundos
    setTimeout(function() {
        if (table && table.rows().count() === 0) {
            console.log('DataTable aún vacía, forzando carga...');
            table.ajax.reload();
        }
    }, 2000);

    // Manejo de errores global para jQuery
    if (typeof jQuery !== 'undefined') {
        jQuery.ajaxSetup({
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Error AJAX:', textStatus, errorThrown);
                if (jqXHR.status === 0) {
                    alert('Error de conexión. Verifica tu conexión a internet.');
                } else if (jqXHR.status === 404) {
                    alert('Recurso no encontrado (404).');
                } else if (jqXHR.status === 500) {
                    alert('Error interno del servidor (500).');
                } else {
                    alert('Error desconocido: ' + textStatus);
                }
            }
        });
    }
});
</script>
@endsection
