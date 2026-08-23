@extends('layouts.app')

@section('content')
<!-- Formulario adaptado para actualizar (Acción apuntando a update y método PUT) -->
<form name="formulario_edit" id="formulario_edit" class="validated" action="{{ route('transfers.update', $transfer->id) }}" method="post">
    @csrf
    @method('PUT') {{-- Directiva obligatoria en Laravel para peticiones de edición --}}
    
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="col-12">
                        <div class="text-white bg-info p-1 text-center">
                            Modificar Datos Generales
                        </div>
                        <div class="p-3 border border-3 border-info">
                            <div class="row g-4">
                                <div class="col-md-6">
                                    <label for="reference">Reference</label>
                                    <input type="text" class="form-control" name="reference" required readonly value="{{ old('reference', $transfer->reference) }}">
                                </div>

                                <div class="col-md-6">
                                    <label for="fecha_recibido">Fecha recibido</label>
									<input type="date" class="form-control" name="fecha_recibido" required 
									value="{{ old('fecha_recibido', $transfer->fecha_recibido ? date('Y-m-d', strtotime($transfer->fecha_recibido)) :  now()->format('Y-m-d')) }}">
                                    @error('fecha_recibido')
                                        <small class="text-danger">{{'*'.$message}}</small>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="almacen_origen_id">Almacen Origen</label>
                                    <select class="form-select" name="almacen_origen_id" id="almacen_origen_id"  style="width:100%;">
                                        @foreach ($almacenes as $item)
                                            <option value="{{$item->id}}" {{ old('almacen_origen_id', $transfer->almacen_origen_id) == $item->id ? 'selected' : '' }}>
                                                {{ $item->name ?? $item->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('almacen_origen_id')
                                        <small class="text-danger">{{'*'.$message}}</small>
                                    @enderror
                                </div>
                                
                                <div class="col-md-6">
                                    <label for="almacen_destino_id">Almacen Destino</label>
                                    <select class="form-select" name="almacen_destino_id" id="almacen_destino_id"  style="width:100%;">
                                        @foreach ($almacenes as $item)
                                            <option value="{{$item->id}}" {{ old('almacen_destino_id', $transfer->almacen_destino_id) == $item->id ? 'selected' : '' }}>
                                                {{ $item->name ?? $item->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('almacen_destino_id')
                                        <small class="text-danger">{{'*'.$message}}</small>
                                    @enderror
                                </div>
                            <div class="col-md-12">
    <div class="form-group">
        <label class="control-label text-muted" style="font-weight: 600;">
            <i class="fas fa-history text-primary mr-1"></i> {{ _lang('Observaciones Previas') }}
        </label>
        <div class="p-3 bg-light rounded text-secondary border d-block" 
             style="min-height: 80px; max-height: 180px; overflow-y: auto; white-space: pre-line; font-size: 0.9rem; text-align: left; vertical-align: top; line-height: 1.4;">{{ trim($transfer->detalles) ?: _lang('Sin observaciones registradas.') }}</div>
    </div>
                                <div class="col-12 m-2">
                                    <label for="detalles">Detalles adicionales</label>
                                    <textarea class="form-control" name="detalles" rows="3"></textarea>
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
                <!-- El contador inicializará con el número de ítems ya cargados -->
                <span id="contador_agregados" class="badge bg-secondary text-white fs-6 px-3 py-1">
                    {{ $transfer->TransfersProduct->count() }}
                </span>
            </div>
            
            <div class="p-3 border border-3">
                <div class="row g-4">
                    
                    <!-- Tabla Superior: Buscador ServerSide -->
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="tabla_detalle_busqueda" class="table table-bordered data-table w-100"> 
                                <thead>
                                    <tr>
                                        <th><input type="checkbox" id="seleccionar-todos"></th>
										<th>Id Producto</th>
                                        <th>Producto</th>
                                        <th>Nro Interno</th>
                                        <th>Nro Oblea</th>
										<th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
								@foreach($transfer->TransfersProduct as $items)
                                    <tr>	
                                        <td>
										@if(!$items->recibido)
											<input name="bank_check" type="checkbox" class="fila-seleccionada" data-id="{{ $items->id }}">
										@endif
										</td>
                                        <td>{{ $items->product_id ?? '' }}</td>
                                        <td>{{ $items->inventario->item->item_name  ?? '' }}</td>
										<td>{{ nroInternoAlias($items->inventario->company_id, $items->inventario->tipo_vehiculo, $items->inventario->nro_interno)  }}</td>
										<td>{{ $items->inventario->nro_oblea ?? '' }}</td>
										<td> @if($items->recibido)
											<span class="badge badge-success">Recibido</span>
										@else
											<span class="badge badge-warning">Pendiente</span>
										@endif</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="col-12 my-3 text-end">
						<!-- Tu botón existente -->
						<button id="btn_agregar" class="btn btn-primary px-4 fw-bold" type="button">
							<i class="cil-plus me-1"></i> Agregar Seleccionados
						</button>
						
						<!-- Botón de la cámara QR adaptado a Bootstrap -->
						<button onclick="openScannerModal()" class="btn btn-info px-4 fw-bold text-white" type="button">
							<i class="cil-camera me-1"></i> Abrir Cámara QR
						</button>
					</div>
                    
                    <!-- Tabla Inferior: Detalle del Traslado Actual (Modificable) -->
                    <div class="col-12">
                        <div class="table-responsive">
                            <table id="tabla_detalle" class="table table-hover align-middle w-100">
                                <thead class="table-light border-bottom border-2">
                                    <tr>
                                        <th>Acción</th>
                                        <th>Id Producto</th>
                                        <th>Producto</th>
                                        <th>Nro Interno</th>
                                        <th>Nro Oblea</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Precarga de productos vinculados en el servidor -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="col-12 mt-3">
                        <button id="cancelar" type="button" class="btn btn-danger px-3" data-toggle="modal" data-target="#exampleModal">
                            Cancelar Edición
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
 
 <div class="modal fade" id="scannerModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="scannerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            
            <div class="modal-header bg-success text-white">
    <h5 class="modal-title font-weight-bold" id="scannerModalLabel">Lector de Códigos QR / Barras</h5>
    <!-- Reemplazado por una X tradicional blanca -->
    <button type="button" class="btn text-white p-0 border-0 fs-4 lh-1" data-bs-dismiss="modal" aria-label="Close" onclick="closeScannerModal()">
        &times;
    </button>
</div>

            
            <div class="modal-body p-3 text-center position-relative">
                
                <!-- Indicador Visual de Feedback (Parpadea al escanear con éxito) -->
                <div id="scan-feedback" class="position-absolute top-0 start-0 w-100 h-100 bg-success d-flex align-items-center justify-content-center text-white" 
                     style="opacity: 0; transition: opacity 0.2s ease-in-out; z-index: 10; pointer-events: none;">
                    <div class="display-4 font-weight-bold">✓ LEÍDO</div>
                </div>

                <!-- Contenedor del flujo de Video de la Cámara -->
                <div id="reader" class="overflow-hidden rounded-3 bg-dark border" style="width: 100%; min-height: 280px;"></div>
                
                <p class="text-muted small mt-2 mb-0">
                    Enfoque el código QR dentro del recuadro para registrarlo automáticamente.
                </p>
            </div>
            
            <div class="modal-footer bg-light d-flex justify-content-between">
                <!-- Botón dinámico para conmutar la cámara activa -->
                <button type="button" id="btn-toggle-camera" class="btn btn-outline-secondary btn-sm font-weight-bold" onclick="toggleCameraMode()">
                    🔄 Cambiar a Cámara Trasera
                </button>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal" onclick="closeScannerModal()">
                    Cerrar
                </button>
            </div>

        </div>
    </div>
</div>

 @endsection



@section('js-script')
	<script src="{{ asset('public/backend/assets/js/html5-qrcode.min.js') }}"></script>
   	<script>
	
var arrayIdProductos = [];
var cont = 0;

$(function() {
    
	if ($(".form-select").data('select2')) {
			$(".form-select").select2('destroy');
		}
	$(".form-select").select2();
	$(".form-select").prop("disabled", true);

	let table = $('#tabla_detalle_busqueda').DataTable();


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
            //var row = dtInstance.row($checkbox.closest('tr')).data();
			var row = dtInstance.row($(this).closest('tr')).data();
			let idProducto = parseInt($checkbox.data('id')); 
            //console.log(row);
            if (!row) return;
			
			 let idProd = row[1] || '';
            let producto = row[2] || 'Sin descripción';
            let nroInterno = row[3] || 'N/A';
            let nroOblea = row[4] || 'N/A';

            if (arrayIdProductos.includes(idProd)) {
                if (typeof $.toast !== 'undefined') {
                    $.toast({ position: 'top-right', text: 'El producto ID ' + idProd + ' ya está agregado', icon: 'error' });
                }
                return; 
            }
            
            arrayIdProductos.push(idProd);
            inyectarFilaLocal(idProd, row);
            $checkbox.prop('checked', false);

            /*let fila = '<tr id="fila' + cont + '">' +
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
            $checkbox.prop('checked', false);*/
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
	
	<script>
  
  
 let scannedItems = {}; 
let lastScannedCode = null;
let lastScannedTime = 0;

let html5Qrcode = null;
// 'environment' = Cámara Trasera (Por defecto) | 'user' = Cámara Frontal
let currentFacingMode = "environment"; 

// Audio Beep para confirmación de lectura en el depósito
const audioBeep = new Audio('https://mixkit.co'); // URL con Beep real de almacén
let bootstrapModalInstance = null;


function openScannerModal() {
    if (!bootstrapModalInstance) {
        bootstrapModalInstance = new bootstrap.Modal(document.getElementById('scannerModal'));
    }
    bootstrapModalInstance.show();

    if (!html5Qrcode) {
        html5Qrcode = new Html5Qrcode("reader");
    }

    // Iniciar siempre priorizando la cámara trasera (Modo Despacho/Recepción)
    currentFacingMode = "environment";
    updateToggleButtonText();
    startScanning(currentFacingMode);
}

/**
 * 2. Arrancar el Flujo de Video de la Cámara Seleccionada
 */
function startScanning(facingMode) {
    html5Qrcode.start(
        { facingMode: facingMode }, // Forzar una de las 2 opciones nativas del dispositivo
        {
            fps: 15,
            qrbox: { width: 220, height: 220 }
        },
        onScanSuccess,
        (errorMessage) => { /* Ignorar errores de escaneo continuos por frame */ }
    ).catch(err => {
        //console.error("Error al arrancar la cámara:", err);
        alert("No se pudo acceder a la cámara. Verifica los permisos de tu navegador o que estés usando una conexión segura HTTPS.");
    });
}

/**
 * 3. Alternar entre Cámara Trasera y Frontal
 */
function toggleCameraMode() {
    if (!html5Qrcode || !html5Qrcode.isScanning) return;

    // Cambiar el estado interno
    currentFacingMode = (currentFacingMode === "environment") ? "user" : "environment";
    
    updateToggleButtonText();

    // Detener la cámara actual e iniciar la nueva opción de inmediato
    html5Qrcode.stop().then(() => {
        startScanning(currentFacingMode);
    }).catch(err => console.error("Error al cambiar de cámara:", err));
}

/**
 * 4. Actualizar el Texto del Botón Conmutador
 */
function updateToggleButtonText() {
    const btn = document.getElementById('btn-toggle-camera');
    if (btn) {
        btn.innerHTML = (currentFacingMode === "environment") 
            ? "🔄 Cambiar a Cámara Frontal" 
            : "🔄 Cambiar a Cámara Trasera";
    }
}

/**
 * 5. Cerrar el Modal y Apagar la Cámara (Evita consumo de batería)
 */
function closeScannerModal() {
    if (bootstrapModalInstance) {
        bootstrapModalInstance.hide();
    }

    if (html5Qrcode && html5Qrcode.isScanning) {
        html5Qrcode.stop().then(() => {
            html5Qrcode = null;
        }).catch(err => console.error("Error al detener la cámara:", err));
    }
}

/**
 * 6. Captura y Procesamiento Exclusivo del Código Escaneado
 */
 function onScanSuccess(decodedText, decodedResult) {
    const now = Date.now();
    
    // Antirebote de 2 segundos para la cámara
    if (decodedText === lastScannedCode && (now - lastScannedTime) < 2000) {
        return; 
    }

    lastScannedCode = decodedText;
    lastScannedTime = now;

    // Alerta sonora y visual de éxito
    audioBeep.play().catch(e => console.log("Sonido bloqueado"));
    showFeedbackVisual();

    // Descomponer el QR escaneado (ejemplo: "2038401|9|N/A|N/A|N/A")
    const datosQr = decodedText.trim().split('|');
    let idProdEscaneado = datosQr[0]; // Extrae "2038401"

    if (!idProdEscaneado) return;

    // Verificar si el producto ya está en la lista del traslado usando TU array global
    if (arrayIdProductos.includes(idProdEscaneado)) {
        if (typeof $.toast !== 'undefined') {
            $.toast({ position: 'top-right', text: 'El producto ID ' + idProdEscaneado + ' ya está agregado', icon: 'error' });
        }
        return; 
    }

    // BUSCAR LOS DATOS LOCALMENTE EN TU DATATABLES DE BÚSQUEDA
    let dtInstance = $('#tabla_detalle_busqueda').DataTable();
    let filaEncontrada = null;
    let tieneCheckboxValido = false;

    // Recorrer las filas cargadas localmente en el DataTables
    dtInstance.rows().every(function () {
        let dataRow = this.data();
        
        // Verificamos si coincide el ID del producto (columna índice 1)
        if (dataRow && dataRow[1] == idProdEscaneado) {
            
            // Obtener el nodo HTML (<tr>) de la fila actual en el DOM
            let filaNodo = this.node(); 
            
            if (filaNodo) {
                // Buscamos si dentro de este <tr> existe un checkbox con el nombre "bank_check" o la clase "fila-seleccionada"
                let $checkbox = $(filaNodo).find('input[name="bank_check"], input.fila-seleccionada');
                
                if ($checkbox.length > 0) {
                    tieneCheckboxValido = true;
                    filaEncontrada = dataRow;
                }
            }
            
            return false; // Romper el bucle .every() de DataTables de inmediato
        }
    });

    // Validar el resultado de la inspección de la fila
    if (filaEncontrada && tieneCheckboxValido) {
        // Registrar en tu array de control global
        arrayIdProductos.push(idProdEscaneado);
        
        // Renderizar la fila usando tu misma estructura HTML de inserción
        inyectarFilaLocal(idProdEscaneado, filaEncontrada);
        
        if (typeof $.toast !== 'undefined') {
            $.toast({ position: 'top-right', text: 'Producto ID ' + idProdEscaneado + ' agregado vía QR', icon: 'success' });
        }
    } else {
        // Si el producto existe pero no tiene checkbox (está bloqueado, vendido o pertenece a otro depósito)
        if (typeof $.toast !== 'undefined') {
            $.toast({ 
                position: 'top-right', 
                text: 'El producto está en la lista pero no está disponible para selección (sin selector).', 
                icon: 'warning' 
            });
        }
    }
}


function onScanSuccess_old(decodedText, decodedResult) {
    const now = Date.now();
    
    // Antirebote de 2 segundos para la cámara
    if (decodedText === lastScannedCode && (now - lastScannedTime) < 2000) {
        return; 
    }

    lastScannedCode = decodedText;
    lastScannedTime = now;

    // Alerta sonora y visual de éxito
    audioBeep.play().catch(e => console.log("Sonido bloqueado"));
    showFeedbackVisual();

    // Descomponer el QR escaneado (ejemplo: "2038401|9|N/A|N/A|N/A")
    const datosQr = decodedText.trim().split('|');
    let idProdEscaneado = datosQr[0]; // Extrae "2038401"

    if (!idProdEscaneado) return;

    // Verificar si el producto ya está en la lista del traslado usando TU array global
    if (arrayIdProductos.includes(idProdEscaneado)) {
        if (typeof $.toast !== 'undefined') {
            $.toast({ position: 'top-right', text: 'El producto ID ' + idProdEscaneado + ' ya está agregado', icon: 'error' });
        }
        return; 
    }

    // BUSCAR LOS DATOS LOCALMENTE EN TU DATATABLES DE BÚSQUEDA
    let dtInstance = $('#tabla_detalle_busqueda').DataTable();
    let filaEncontrada = null;

    // Recorrer las filas cargadas localmente en el DataTables para extraer la información
    dtInstance.rows().every(function () {
        let dataRow = this.data();
        if (dataRow && dataRow[1] == idProdEscaneado) {
            filaEncontrada = dataRow;
            return false; // Romper bucle each de DataTables
        }
    });

    if (filaEncontrada) {
        // Registrar en tu array de control global
        arrayIdProductos.push(idProdEscaneado);
        
        // Renderizar la fila usando tu misma estructura HTML de inserción
        inyectarFilaLocal(idProdEscaneado, filaEncontrada);
    } else {
        if (typeof $.toast !== 'undefined') {
            $.toast({ position: 'top-right', text: 'Código QR no coincide con los productos de la búsqueda', icon: 'warning' });
        }
    }
}

/**
 * 7. Feedback de Confirmación Visual en Pantalla
 */
function showFeedbackVisual() {
    const feedback = document.getElementById('scan-feedback');
    if (feedback) {
        feedback.style.opacity = '1';
        setTimeout(() => {
            feedback.style.opacity = '0';
        }, 1000);
    }
}

function inyectarFilaLocal(idProd, rowData) {
    let producto = rowData[2] || 'Sin descripción';
    let nroInterno = rowData[3] || 'N/A';
    let nroOblea = rowData[4] || 'N/A';

    let fila = '<tr id="fila' + cont + '">' +
        '<td>' +
            // Conservamos tu icono ti-eraser de borrado
            '<button class="btn btn-sm btn-danger" type="button" onClick="eliminarProducto('+ cont +',\'' + idProd + '\')"><i class="ti-eraser"></i></button>' +
            '<input type="hidden" name="product_datos[]" value="' + encodeURIComponent(JSON.stringify(rowData)) + '">' +
            '<input type="hidden" name="product_ids[]" value="' + idProd + '">' +
        '</td>' +
        '<td><strong>' + idProd + '</strong></td>' +
        '<td>' + producto + '</td>' + 
        '<td>' + nroInterno + '</td>' + 
        '<td>' + nroOblea + '</td>' +
        '</tr>';
        
    // Insertar en tu tabla de destino
    $('#tabla_detalle tbody').append(fila);
    cont++;

    // Ejecutar tu función de actualización de contadores
    actualizarContadorProductos(); 
}
   
	
   
</script>
@endsection

