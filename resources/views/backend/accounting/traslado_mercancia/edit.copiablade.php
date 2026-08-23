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
 
 <!-- MODAL DEL ESCÁNER QR (BOOTSTRAP) -->
<div class="modal fade" id="scannerModal" tabindex="-1" aria-labelledby="scannerModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg">
            
            <!-- Cabecera -->
            <div class="modal-header bg-dark text-white py-2">
                <h5 class="modal-title d-flex align-items-center gap-2" id="scannerModalLabel">
                    <span class="spinner-grow spinner-grow-sm text-success" role="status"></span>
                    Escáner Activo (Ráfaga)
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="closeScannerModal()" aria-label="Close"></button>
            </div>

            <!-- Cuerpo del Modal -->
            <div class="modal-body bg-light text-center">
                <!-- SELECTOR DE CÁMARAS (FRONTAL / TRASERA) -->
                <div class="mb-3 text-start">
                    <label for="cameraSelection" class="form-label small fw-bold text-muted mb-1">Seleccionar Cámara:</label>
                    <select id="cameraSelection" class="form-select form-select-sm" onchange="changeCamera(this.value)">
                        <option value="">Cargando cámaras disponibles...</option>
                    </select>
                </div>

                <!-- Visor de la Cámara -->
                <div id="reader" class="w-100 bg-black rounded border border-secondary mx-auto overflow-hidden" style="max-width: 100%; max-height: 280px;"></div>
                
                <!-- Notificador de lectura rápida -->
                <div id="scan-feedback" class="mt-2 small fw-bold text-success" style="opacity: 0; transition: opacity 0.3s; height: 15px;">
                    ¡Código detectado! +1
                </div>
            </div>

            <!-- Pie del Modal -->
            <div class="modal-footer bg-light justify-content-between py-2">
                <span class="text-muted text-start" style="font-size: 0.75rem;">Las lecturas se acumulan automáticamente de fondo.</span>
                <button type="button" class="btn btn-secondary btn-sm fw-bold" onclick="closeScannerModal()">Listo (Ver Lista)</button>
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
	
	<script>
    // Las funciones deben declararse de forma global (fuera de cualquier listener) 
    // para que el 'onclick' del HTML pueda encontrarlas.
	
	let scannedItems = {}; // Estructura: { id_producto: quantity }
let lastScannedCode = null;
let lastScannedTime = 0;

// Instancia global del escáner y estado de la cámara actual
let html5Qrcode = null;
let currentCameraId = null;
const audioBeep = new Audio('https://mixkit.co');

// Instancia del modal de Bootstrap de forma manual
let bootstrapModalInstance = null;

/*function openScannerModal() {
    // Inicializar el modal si no existe
    if (!bootstrapModalInstance) {
        bootstrapModalInstance = new bootstrapModal(document.getElementById('scannerModal'));
    }
    bootstrapModalInstance.show();

    // Solicitar permisos y listar cámaras disponibles
    Html5Qrcode.getCameras().then(devices => {
        const selectContainer = document.getElementById('cameraSelection');
        selectContainer.innerHTML = ''; // Limpiar opciones previas

        if (devices && devices.length > 0) {
            // Priorizar la cámara trasera en dispositivos móviles buscando palabras clave
            let defaultDevice = devices[0];
            devices.forEach(device => {
                const label = device.label.toLowerCase();
                if (label.includes('back') || label.includes('trasera') || label.includes('environment')) {
                    defaultDevice = device;
                }
            });

            // Llenar el selector dinámico
            devices.forEach(device => {
                const option = document.createElement('option');
                option.value = device.id;
                option.text = device.label || `Cámara ${selectContainer.options.length + 1}`;
                if (device.id === defaultDevice.id) {
                    option.selected = true;
                }
                selectContainer.appendChild(option);
            });

            // Instanciar el lector de códigos sobre el contenedor "reader"
            html5Qrcode = new Html5Qrcode("reader");
            
            // Iniciar directamente con la cámara seleccionada por defecto
            startScanning(defaultDevice.id);
        } else {
            selectContainer.innerHTML = '<option value="">No se encontraron cámaras disponibles</option>';
        }
    }).catch(err => {
        console.error("Error al obtener acceso a las cámaras:", err);
        alert("Asegúrate de otorgar permisos de cámara en tu navegador.");
    });
}*/
  

    function openScannerModal() {
        if (!bootstrapModalInstance) {
            bootstrapModalInstance = new bootstrap.Modal(document.getElementById('scannerModal'));
        }
        bootstrapModalInstance.show();
       
	      // 2. Crear la instancia del lector sobre el contenedor 'reader'
    if (!html5Qrcode) {
        html5Qrcode = new Html5Qrcode("reader");
    }

    // 3. Forzar la solicitud de permisos interactuando directamente con el hardware
    Html5Qrcode.getCameras().then(devices => {
        const selectContainer = document.getElementById('cameraSelection');
        selectContainer.innerHTML = ''; 

        if (devices && devices.length > 0) {
            let defaultDevice = devices[0]; // Por defecto la primera
            
            // Intentar buscar la cámara trasera automáticamente
            devices.forEach(device => {
                const label = device.label.toLowerCase();
                if (label.includes('back') || label.includes('trasera') || label.includes('environment')) {
                    defaultDevice = device;
                }
            });

            // Llenar el select de Bootstrap
            devices.forEach(device => {
                const option = document.createElement('option');
                option.value = device.id;
                option.text = device.label || `Cámara ${selectContainer.options.length + 1}`;
                if (device.id === defaultDevice.id) {
                    option.selected = true;
                }
                selectContainer.appendChild(option);
            });

            // Arrancar el escaneo masivo
            startScanning(defaultDevice.id);
        } else {
            selectContainer.innerHTML = '<option value="">No se detectaron cámaras</option>';
        }
    }).catch(err => {
        console.error("Error detallado de hardware:", err);
        // Alerta amigable al usuario
        alert("No se pudo acceder a la cámara. Verifica que estés usando HTTPS (o localhost) y que hayas concedido los permisos en tu navegador.");
    });
      
    }
	
	
function startScanning(cameraId) {
    currentCameraId = cameraId;
    
    html5Qrcode.start(
        cameraId, 
        {
            fps: 15,
            qrbox: { width: 220, height: 220 }
        },
        onScanSuccess,
        (errorMessage) => {
            // Callback opcional de errores de escaneo continuos (se ignora para no saturar la consola)
        }
    ).catch(err => {
        console.error("No se pudo iniciar el stream de video de la cámara:", err);
    });
}

// Cambiar de cámara dinámicamente desde el menú desplegable
function changeCamera(newCameraId) {
    if (!html5Qrcode || !newCameraId || newCameraId === currentCameraId) return;

    // Detener la cámara actual y arrancar la nueva de manera consecutiva
    html5Qrcode.stop().then(() => {
        startScanning(newCameraId);
    }).catch(err => console.error("Error al detener cámara previa:", err));
}

function closeScannerModal() {
    if (bootstrapModalInstance) {
        bootstrapModalInstance.hide();
    }

    // Apagar la cámara web de forma segura para apagar el LED físico del dispositivo
    if (html5Qrcode && html5Qrcode.isScanning) {
        html5Qrcode.stop().then(() => {
            html5Qrcode = null;
        }).catch(err => console.error("Error al apagar el visor de la cámara:", err));
    }
}

// --- CAPTURA DE CÓDIGO QR ---
function onScanSuccess(decodedText, decodedResult) {
    const now = Date.now();
    
    // Antirrebote: Filtro de 2 segundos para evitar lecturas duplicadas accidentales
    if (decodedText === lastScannedCode && (now - lastScannedTime) < 2000) {
        return; 
    }

    lastScannedCode = decodedText;
    lastScannedTime = now;

    // Ejecutar pitido acústico y destello visual en pantalla
    audioBeep.play().catch(e => console.log("Sonido silenciado por políticas de navegador"));
    showFeedbackVisual();

    const productId = decodedText.trim();

    if (scannedItems[productId]) {
        scannedItems[productId]++;
    } else {
        scannedItems[productId] = 1;
    }

    // Refrescar la tabla que está debajo en la página principal
    renderMainTable();
}

function showFeedbackVisual() {
    const feedback = document.getElementById('scan-feedback');
    feedback.style.opacity = '1';
    setTimeout(() => {
        feedback.style.opacity = '0';
    }, 1000);
}
	
    
    /*let scannedItems = {}; 
    let html5Qrcode = null;
    let bootstrapModalInstance = null;

    function openScannerModal() {
        console.log("Abriendo modal..."); // Línea de prueba para verificar en consola
        if (!bootstrapModalInstance) {
            // Nota: Si usas Bootstrap 5 es 'new bootstrap.Modal(...)'. Si usas versión vieja puede variar.
            bootstrapModalInstance = new bootstrap.Modal(document.getElementById('scannerModal'));
        }
        bootstrapModalInstance.show();
        
      
    }

    function closeScannerModal() {
        if (bootstrapModalInstance) {
            bootstrapModalInstance.hide();
        }
        if (html5Qrcode && html5Qrcode.isScanning) {
            html5Qrcode.stop();
        }
    }*/
</script>
@endsection

