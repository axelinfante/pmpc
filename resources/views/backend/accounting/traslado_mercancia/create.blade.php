@extends('layouts.app')


@section('content')
<form name="formulario_create" id="formulario_create" class="validated" action="{{ route('transfers.store') }}" method="post">
<div class="card">
        @csrf
        <div class="card-body ">
            <div class="row">
                <div class="col-md-12">
                    <!-----Compra---->
                    <div class="col-12">
                        <div class="text-white bg-info p-1 text-center">
                            Datos generales
                        </div>
                        <div class="p-3 border border-3 border-info">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="reference">Reference </label>
                                    <input type="text" class="form-control" name="reference" required readonly value="TRA">
                                </div>

                                <div class="col-md-6">
                                    <label for="fecha_traslado">Fecha traslado </label>
                                    <input type="date" class="form-control" name="fecha_traslado" required value="{{ now()->format('Y-m-d') }}">
                                    @error('fecha_traslado')
                                    <small class="text-danger">{{'*'.$message}}</small>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="almacen_origen_id">Almacen Origen </label>
                                    <select class="form-select" name="almacen_origen_id" id="almacen_origen_id" style="width:100%">
                                        @foreach ($lugar_entregas as $item)
                                            <option value="{{$item->id}}" {{ old('almacen_origen_id') == $item->id ? 'selected' : '' }}>
                                                {{$item->nombre}}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('almacen_origen_id')
                                    <small class="text-danger">{{'*'.$message}}</small>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="almacen_destino_id">Almacen Destino</label>
                                    <select class="form-select" name="almacen_destino_id" id="almacen_destino_id" style="width:100%">
                                        @foreach ($lugar_entregas as $item)
                                            <option value="{{$item->id}}" {{ old('almacen_destino_id') == $item->id ? 'selected' : '' }}>
                                                {{$item->nombre}}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('almacen_destino_id')
                                    <small class="text-danger">{{'*'.$message}}</small>
                                    @enderror
                                </div>
                            
                                <div class="col-12 m-2">
								<textarea class="form-control" name="detalles">{{ old('detalles') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
	<div class="col-12 mt-4">
	 <div class="p-2 text-white bg-primary d-flex justify-content-between align-items-center fw-bold px-3">
        <span>Detalles del Traslado</span>
        
        <!-- ESTE ES EL ELEMENTO QUE BUSCA EL SCRIPT (Aparece como un óvalo a la derecha) -->
        <span id="contador_agregados" class="badge bg-secondary text-white fs-6 px-3 py-1">0</span>
    </div>
	
    <div class="p-3 border border-3">
        <div class="row g-4">
            
            <div class="col-12">
                <div class="table-responsive">
                    <table id="tabla_detalle_busqueda" class="table table-bordered data-table w-100"> 
                        <thead>
                            <tr>
								<th><input type="checkbox" id="seleccionar-todos"></th>
                                <th>ID de producto</th>
                                <th>{{ _lang('Nro Interno') }}</th>
                                <th>{{ _lang('Dominio') }}</th>
                                <th>{{ _lang('Product') }}</th>
                                <th>{{ _lang('Marca') }}</th>
                                <th>{{ _lang('Modelo') }}</th>
                                <th>{{ _lang('nº motor') }}</th>
                                <th>{{ _lang('nº oblea') }}</th>
                                <th style="width: 200px; min-width: 200px;">{{ _lang('Deposito') }}</th>
                                <th>{{ _lang('Fecha último giro') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- DataTables ServerSide inyecta las filas dinámicamente -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Acciones de Traspaso -->
            <div class="col-12 my-3 text-end">
                <button id="btn_agregar" class="btn btn-primary px-4 fw-bold" type="button">
                    <i class="cil-plus me-1"></i> Agregar
                </button>
            </div>
            
            <!-- Tabla Inferior: Detalle del Traslado (Destino) -->
            <div class="col-12">
                <div class="table-responsive">
                    <table id="tabla_detalle" class="table table-hover align-middle w-100">
                        <thead class="table-light border-bottom border-2">
                            <tr>
							    <th></th>
                                <th>Id Producto</th>
                                <th>Producto</th>
                                <th>Nro Interno</th>
                                <th>Nro Oblea</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Las filas se agregan dinámicamente mediante JQuery desde la tabla de búsqueda -->
                        </tbody>
                    </table>
                </div>
            </div>
            
            <!-- Botón para cancelar el flujo actual -->
            <div class="col-12 mt-3">
                <button id="cancelar"
                    type="button"
                    class="btn btn-danger px-3"
                    data-toggle="modal"
                    data-target="#exampleModal">
                    Cancelar Traslado
                </button>
            </div>

        </div>
    </div>
</div>

<!-- Footer de la Tarjeta con Envío de Formulario -->
<div class="card-footer text-center bg-light border-top mt-4">
    @can('crear-trasladomercancia') 
        <button id="submitupdate" type="submit" class="btn btn-success px-5 fw-bold">Guardar Traslado</button>
    @endcan 
</div>

<!-- Modal de Advertencia para Cancelación -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title id="exampleModalLabel">
                    <i class="cil-warning me-2"></i>Advertencia
                </h5>
                <button type="button" class="btn-close btn-close-white" data-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-4">
                <p class="fs-5 mb-0">¿Estás seguro de que quieres cancelar el traslado actual?</p>
                <small class="text-muted">Se perderán todos los productos agregados a la lista de detalles.</small>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                <button id="btnCancelarCompra" type="button" class="btn btn-danger px-4" data-dismiss="modal">Confirmar</button>
            </div>
        </div>
    </div>
</div>
</div>
 </form>
 @endsection
@section('js-script')
   	<script>
	
var arrayIdProductos = [];
var cont = 0;

$(function() {
    $(".form-select").select2();

    let table = $('#tabla_detalle_busqueda').appTable({
        title: "Traslado de Mercancia",
        ajax: ({
            url: '{{ route('transfers.table_detalle') }}',
            method: "POST",
            data: function (d) {
                d._token = "{{ csrf_token() }}";	
                if($('select[name=almacen_origen_id]').val() != ''){
                    d.almacen_id = $('select[name=almacen_origen_id]').val();
                }
            },
            error: function (request, status, error) {
                //console.error("Error en la consulta de la tabla:", error);
            }
        }),
        visibleButtonsFilter: false,
        searchDelay: null, 
        visibleButtons: {
            reset: true,
            excel: true,
            print: false
        },
        columns: [
            { data: 'selection', name: 'selection', orderable: false },
            { data: 'id', name: 'id'},
            { data: 'interno', name: 'nro_interno' },
            { data: 'dominio', name: 'dominio' },
            { data: 'productItem', name: 'productItem' },
            { data: 'marca', name: 'marca' },
            { data: 'modelo', name: 'modelo' },
            { data: 'motor_nro', name: 'motor_nro' },
            { data: 'nro_oblea', name: 'nro_oblea' },
            { data: 'deposito', name: 'deposito' },
            { data: 'fecha_ultimogiro', name: 'fecha_ultimogiro' }
        ],
    });

  
//    $('.dataTables_filter input, .filtros').off('keyup.DT search.DT input.DT paste.DT');

   
    $('#almacen_origen_id').on('change', function() {
         table.ajax.reload(null, false); 
    });	

    $('#seleccionar-todos').on('click', function() {
        $('.fila-seleccionada, input[name="bank_check"]').prop('checked', $(this).prop('checked'));
    });	
    
    inicio();
    
    function inicio() {
        @php 
            $productos_str = json_encode(old('product_ids'));
            $product_datos_str = json_encode(old('product_datos',''));
        @endphp
        
        const productos_ = JSON.parse('{!! $productos_str !!}');
        const row_ = JSON.parse('{!! $product_datos_str !!}');     
        
        if (productos_ != null && productos_.length > 0) {
            cont = 0;
            arrayIdProductos = [];

            $.each(productos_, function(index, value) {
                if(!row_[index]) return;
                let row = JSON.parse(decodeURIComponent(row_[index]));
                arrayIdProductos.push(row.id);
        
                let idProd = row.id || '';
                let producto = row.productItem || row.product_name || 'Sin descripción';
                let nroInterno = row.interno || row.nro_interno || 'N/A';
                let nroOblea = row.nro_oblea || 'N/A';

                let fila = '<tr id="fila' + cont + '">' +
                    '<td>' +
                        '<button class="btn btn-sm btn-danger" type="button" onClick="eliminarProducto('+ cont +',\'' + idProd + '\')"><i class="ti-eraser"></i></button>' +
                        '<input type="hidden" name="product_datos[]" value="' + encodeURIComponent(JSON.stringify(row)) + '">' +
                        '<input type="hidden" name="product_ids[]" value="' + idProd + '">' +
                    '</td>' +
                    '<td><strong>' + idProd + '</strong></td>' +
                    '<td>' + producto + '</td>' + 
                    '<td>' + nroInterno + '</td>' + 
                    '<td>' + nroOblea + '</td>' +
                    '</tr>';
                    
                $('#tabla_detalle tbody').append(fila);
                cont++;
            });
            actualizarContadorProductos();
        }
    }		

    $('#btn_agregar').on('click', function() {
        let dtInstance = $('#tabla_detalle_busqueda').DataTable();
        let checkboxes = $('#tabla_detalle_busqueda tbody input[name="bank_check"]:checked, #tabla_detalle_busqueda tbody input.fila-seleccionada:checked');

        if (checkboxes.length === 0) {
            if (typeof $.toast !== 'undefined') {
                $.toast({ position: 'top-right', text: 'Seleccione al menos un producto', icon: 'warning' });
            }
            return;
        }

        checkboxes.each(function () {
            var $checkbox = $(this);
            var row = dtInstance.row($checkbox.closest('tr')).data();
            
            if (!row) return;

            if (arrayIdProductos.includes(row.id)) {
                if (typeof $.toast !== 'undefined') {
                    $.toast({ position: 'top-right', text: 'El producto ID ' + row.id + ' ya está agregado', icon: 'error' });
                }
                return; 
            }
            
            arrayIdProductos.push(row.id);
            
            let idProd = row.id || '';
            let producto = row.productItem || 'Sin descripción';
            let nroInterno = row.interno || 'N/A';
            let nroOblea = row.nro_oblea || 'N/A';

            let fila = '<tr id="fila' + cont + '">' +
                '<td>' +
                    '<button class="btn btn-sm btn-danger" type="button" onClick="eliminarProducto('+ cont +',\'' + idProd + '\')"><i class="ti-eraser"></i></button>' +
                    '<input type="hidden" name="product_datos[]" value="' + encodeURIComponent(JSON.stringify(row)) + '">' +
                    '<input type="hidden" name="product_ids[]" value="' + idProd + '">' +
                '</td>' +
                '<td><strong>' + idProd + '</strong></td>' +
                '<td>' + producto + '</td>' + 
                '<td>' + nroInterno + '</td>' + 
                '<td>' + nroOblea + '</td>' +
                '</tr>';
                
            $('#tabla_detalle tbody').append(fila);
            cont++;
            $checkbox.prop('checked', false);
        });

        $('#seleccionar-todos').prop('checked', false);
        actualizarContadorProductos(); 
    });
    

    $('#btnCancelarCompra').click(function() {
        cancelarCompra();
    });
    
    function cancelarCompra() {
        $('#tabla_detalle tbody').empty();
        cont = 0;
        arrayIdProductos = [];
        actualizarContadorProductos(); 
    }				

    $('#submitupdate').click(function(e) {
		 // e.preventDefault(); 
		
	if (!arrayIdProductos || arrayIdProductos.length === 0) {
        e.preventDefault();
        if (typeof $.toast !== 'undefined') {
            $.toast({ position: 'top-right', text: 'Debe agregar al menos un producto', icon: 'warning' });
        }
        return false;
    }
	
    const duplicates = arrayIdProductos.filter((item, index) => arrayIdProductos.indexOf(item) !== index);
    
    if (duplicates.length > 0) {
        e.preventDefault();
        if (typeof $.toast !== 'undefined') {
            $.toast({ position: 'top-right', text: 'Hay productos Duplicados en la lista', icon: 'error' });
        }
        return false;
    }
	
	  //$('#formulario_create').submit(); 
	
    });				

    $('form#formulario_create').bind('keypress keydown keyup', function(e) {
        if (e.keyCode == 13) {
            e.preventDefault();
        }
    });
});

function eliminarProducto(indice, idProducto) {
    $('#fila' + indice).remove();
    arrayIdProductos = arrayIdProductos.filter(function(id) {
        return id != idProducto;
    });
    actualizarContadorProductos(); 
}

function actualizarContadorProductos() {
    var total = arrayIdProductos.length;
    $('#contador_agregados').text(total);
    
    if (total > 0) {
        $('#contador_agregados').removeClass('bg-secondary').addClass('bg-warning text-dark');
    } else {
        $('#contador_agregados').removeClass('bg-warning text-dark').addClass('bg-secondary');
    }
}

	
		
	</script>
@endsection

