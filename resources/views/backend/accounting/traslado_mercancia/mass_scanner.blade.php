<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Escáner de Inventario en Ráfaga</title>
    <script src="https://jsdelivr.net"></script>
    <!-- Librería oficial para lectura de QR por cámara -->
    <script src="https://unpkg.com"></script>
</head>
<body class="bg-gray-100 p-4">
    <div class="max-w-2xl mx-auto bg-white p-6 rounded-lg shadow">
        <h2 class="text-2xl font-bold mb-4 text-center">Escaneo Masivo en Ráfaga</h2>
        
        <!-- Contenedor de la cámara web -->
        <div id="reader" class="w-full bg-black rounded-lg overflow-hidden mb-4" style="max-height: 350px;"></div>
        
        <!-- Listado de códigos acumulados -->
        <div class="mb-6">
            <h3 class="text-lg font-semibold mb-2">Artículos Escaneados</h3>
            <div class="overflow-x-auto">
                <table class="w-full border-collapse bg-gray-50 rounded">
                    <thead>
                        <tr class="bg-gray-200 text-left">
                            <th class="p-2 border">ID Producto</th>
                            <th class="p-2 border">Cantidad a Procesar</th>
                            <th class="p-2 border text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="scanned-items-list">
                        <!-- Se llena dinámicamente con JS -->
                        <tr id="empty-row"><td colspan="3" class="p-4 text-center text-gray-500">Ningún código escaneado aún.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Acciones del lote -->
        <div class="flex gap-4">
            <button onclick="submitBatch('add')" class="flex-1 bg-green-600 hover:bg-green-700 text-white p-3 rounded font-bold transition">
                ➕ Cargar Todo como Entrada
            </button>
            <button onclick="submitBatch('remove')" class="flex-1 bg-red-600 hover:bg-red-700 text-white p-3 rounded font-bold transition">
                ➖ Cargar Todo como Salida
            </button>
        </div>
    </div>

    <script>
        // Objeto para acumular productos escaneados { id_producto: cantidad }
        let scannedItems = {};
        let lastScannedCode = null;
        let lastScannedTime = 0;

        // Efecto de sonido (Beep) al escanear con éxito
        const audioBeep = new Audio('https://mixkit.co');

        function onScanSuccess(decodedText, decodedResult) {
            const now = Date.now();
            
            // Antirrebote: Evita que el mismo código se escanee 2 veces en menos de 2 segundos seguidos
            if (decodedText === lastScannedCode && (now - lastScannedTime) < 2000) {
                return; 
            }

            lastScannedCode = decodedText;
            lastScannedTime = now;
            audioBeep.play();

            // Asumimos que el QR contiene directamente el ID numérico del producto
            const productId = decodedText.trim();

            if (scannedItems[productId]) {
                scannedItems[productId]++;
            } else {
                scannedItems[productId] = 1;
            }

            renderTable();
        }

        function renderTable() {
            const tbody = document.getElementById('scanned-items-list');
            tbody.innerHTML = '';

            const keys = Object.keys(scannedItems);
            if (keys.length === 0) {
                tbody.innerHTML = `<tr id="empty-row"><td colspan="3" class="p-4 text-center text-gray-500">Ningún código escaneado aún.</td></tr>`;
                return;
            }

            keys.forEach(id => {
                const tr = document.createElement('tr');
                tr.className = "border-b";
                tr.innerHTML = `
                    <td class="p-2 border font-mono">${id}</td>
                    <td class="p-2 border">
                        <input type="number" value="${scannedItems[id]}" min="1" 
                               class="border rounded p-1 w-20" 
                               onchange="updateQuantity('${id}', this.value)">
                    </td>
                    <td class="p-2 border text-center">
                        <button onclick="removeItem('${id}')" class="text-red-600 hover:underline">Eliminar</button>
                    </td>
                `;
                tbody.appendChild(tr);
            });
        }

        function updateQuantity(id, val) {
            scannedItems[id] = parseInt(val) || 1;
        }

        function removeItem(id) {
            delete scannedItems[id];
            renderTable();
        }

        // Envía el lote completo a Laravel por medio de Fetch
        function submitBatch(actionType) {
            const keys = Object.keys(scannedItems);
            if (keys.length === 0) {
                alert("Por favor, escanea al menos un producto antes de enviar.");
                return;
            }

            // Mapear el objeto al formato que espera el controlador
            const itemsArray = keys.map(id => ({
                id: id,
                quantity: scannedItems[id]
            }));

            fetch("{{ route('inventory.process_mass') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSR-Forge": "{{ csrf_token() }}", // Laravel CSRF Token requerido para POST
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({
                    action: actionType,
                    items: itemsArray
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(data.message);
                    scannedItems = {}; // Limpiar la lista tras el éxito
                    renderTable();
                } else {
                    alert("Error al procesar el inventario.");
                }
            })
            .catch(err => {
                console.error(err);
                alert("Ocurrió un error en el servidor.");
            });
        }

        // Inicializar la cámara al cargar la página
        const html5QrcodeScanner = new Html5QrcodeScanner("reader", { 
            fps: 15, // Cuadros por segundo para procesar imágenes
            qrbox: { width: 250, height: 250 },
            rememberLastUsedCamera: true
        });
        html5QrcodeScanner.render(onScanSuccess);
    </script>
</body>
</html>