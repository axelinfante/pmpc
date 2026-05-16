@props([
    'id',                                          // ID único del dropzone
    'name' => 'archivo',                           // Nombre del campo para Laravel
    'label' => 'archivos y documentos',            // Texto de la etiqueta superior
    'url',                                         // Ruta de Laravel para la carga asíncrona temporal
    'type' => 'images',                             // 'images', 'video', 'documents' o 'mixed'
    'message' => 'arrastra tus archivos aquí o haz clic',
    'maxfiles' => 20,                              // Límite máximo de archivos
    'maxsize' => 20,                               // Tamaño máximo en MB
    'serverfiles' => []                            // Archivos precargados desde el servidor
])

{{-- Inyección única del CSS de Dropzone --}}
@once
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css" />
<!--<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.css" integrity="sha512-jU/7UFiaW5UbGODEopEqNBiaHOI8fc6t99m7tsMQS2GkDUJbyjFKcBBFPsN4wQLG9TGNS7C74w3g7qv2811UA==" crossorigin="anonymous" referrerpolicy="no-referrer" />-->
@endonce
{{-- Estilos personalizados avanzados --}}
@once
<style>
    .dropzone-drag-area {
        width: 100%;
        position: relative;
        border-radius: 10px;
        border: 3px dashed #dbdeea;
        background-color: #f8f9fa;
        padding: 20px;
        display: flex;
        overflow: hidden;
        transition: border-color 0.2s ease, background-color 0.2s ease;
        box-sizing: border-box;
    }
    .dropzone-drag-area.dz-drag-hover {
        border-color: #0d6efd;
        background-color: #e9ecef;
    }
    .dropzone-drag-area.zone-single-video {
        height: auto;
        aspect-ratio: 21 / 9;
        align-items: center;
        justify-content: center;
    }
    .dropzone-drag-area.zone-gallery,
    .dropzone-drag-area.zone-documents,
    .dropzone-drag-area.zone-mixed {
        min-height: 160px;
        height: auto;
        flex-wrap: wrap;
        gap: 12px;
    }
    .dropzone-drag-area .dz-message {
        text-align: center;
        color: #6c757d;
        font-weight: 500;
        width: 100%;
        margin: auto 0;
    }
    .dropzone-drag-area .dz-preview {
        position: relative;
        width: 120px;
        height: 120px;
        border-radius: 8px;
        overflow: hidden;
        border: 1px solid #dee2e6;
        background: #fff;
        margin: 0 !important;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .dropzone-drag-area .dz-preview img, 
    .dropzone-drag-area .dz-preview video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .dropzone-drag-area .dz-preview .dz-image {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        height: 100%;
        font-size: 32px;
        font-weight: bold;
        transition: background 0.2s;
    }
    
    /* Variaciones de color para extensiones de documentos */
    .dropzone-drag-area .dz-preview.doc-pdf .dz-image { background-color: #fde8e8 !important; }
    .dropzone-drag-area .dz-preview.doc-pdf .dz-image::after { content: "PDF"; color: #e02424; font-size: 16px; border: 2px solid #e02424; padding: 2px 6px; border-radius: 4px; font-family: sans-serif; }

    .dropzone-drag-area .dz-preview.doc-word .dz-image { background-color: #e1effe !important; }
    .dropzone-drag-area .dz-preview.doc-word .dz-image::after { content: "DOC"; color: #1c64f2; font-size: 16px; border: 2px solid #1c64f2; padding: 2px 6px; border-radius: 4px; font-family: sans-serif; }

    .dropzone-drag-area .dz-preview.doc-excel .dz-image { background-color: #edfcf2 !important; }
    .dropzone-drag-area .dz-preview.doc-excel .dz-image::after { content: "XLS"; color: #0e9f6e; font-size: 16px; border: 2px solid #0e9f6e; padding: 2px 6px; border-radius: 4px; font-family: sans-serif; }

    .dropzone-drag-area .dz-preview.doc-generic .dz-image { background-color: #f3f4f6 !important; }
    .dropzone-drag-area .dz-preview.doc-generic .dz-image::after { content: "📄"; }

    .dropzone-drag-area .dz-preview .dz-details {
        position: absolute;
        bottom: 0;
        left: 0;
        width: 100%;
        background: rgba(0, 0, 0, 0.75);
        color: #fff;
        font-size: 10px;
        padding: 4px 6px;
        box-sizing: border-box;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
        opacity: 1 !important;
    }
    .dropzone-drag-area .dz-preview .dz-details .dz-size {
        display: none;
    }
    .dropzone-drag-area .dz-remove {
        position: absolute;
        top: 5px;
        right: 5px;
        background: rgba(220, 53, 69, 0.9) !important;
        color: #fff !important;
        border: none !important;
        border-radius: 4px;
        padding: 2px 6px;
        font-size: 11px;
        cursor: pointer;
        text-decoration: none;
        z-index: 15;
    }
    .dropzone-drag-area .dz-remove:hover {
        background: #bb2d3b !important;
    }
    .dropzone-drag-area .dz-success-mark, 
    .dropzone-drag-area .dz-error-mark {
        display: none !important;
    }
</style>
@endonce

<div class="mb-3">
    @if($label)
        <label class="form-label text-capitalize fw-bold mb-2 d-block" for="{{ $id }}">
            {{ $label }}
        </label>
    @endif

    <div id="{{ $id }}" class="dropzone-drag-area zone-{{ $type }}">
        <div class="dz-message">
            <span class="d-block mb-1 text-capitalize">{{ $message }}</span>
            <small class="text-muted d-block">
                @if($type === 'mixed')
                    Formatos: Imágenes, PDF, Word, Excel (Máx: {{ $maxfiles }} archivos, {{ $maxsize }}MB c/u)
                @else
                    Máx: {{ $maxfiles }} archivos ({{ $maxsize }}MB c/u)
                @endif
            </small>
        </div>
    </div>

    {{-- Contenedor donde se inyectarán los inputs ocultos de eliminados --}}
    <div id="removed-container-{{ $id }}"></div>
</div>

{{-- Inyección única del JS de Dropzone --}}
@once
<!--<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js" integrity="sha512-U2ddg3uiOM70vG6U8vS78T5NOfVwzH+X8EEFM6zoonFpuA8y9F5vG64z5A5bN7I69P57dD0Z8gA7A3mI5Cg2gA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.3/dropzone.min.js"></script>
<script>
    Dropzone.autoDiscover = false;
</script>
@endonce

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const selector = "#{{ $id }}";
        const container = document.querySelector(selector);
        
        if (!container || container.dropzone) return;

        let removedFiles = [];
        let acceptedFilesOption = "image/*";

        if ("{{ $type }}" === "video") {
            acceptedFilesOption = "video/*";
        } else if ("{{ $type }}" === "documents" || "{{ $type }}" === "mixed") {
            acceptedFilesOption = "image/*,.pdf,application/pdf,.doc,.docx,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,.xls,.xlsx,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
        }

        function assignDocumentClass(file, element) {
            if (!element) return;
            const name = file.name.toLowerCase();
            if (name.endsWith('.pdf')) {
                element.classList.add('doc-pdf');
            } else if (name.endsWith('.doc') || name.endsWith('.docx')) {
                element.classList.add('doc-word');
            } else if (name.endsWith('.xls') || name.endsWith('.xlsx')) {
                element.classList.add('doc-excel');
            } else if (!/\.(jpeg|jpg|gif|png|svg|webp)$/i.test(name) && !/\.(mp4|avi|mov|mkv)$/i.test(name)) {
                element.classList.add('doc-generic');
            }
        }

        const zone = new Dropzone(selector, {
            url: "{{ $url }}",
            paramName: "{{ $name }}",
            maxFilesize: {{ $maxsize }},
            maxFiles: {{ $maxfiles }},
            acceptedFiles: acceptedFilesOption,
			uploadMultiple: true,
            addRemoveLinks: true,
            dictRemoveFile: "Eliminar",
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || "{{ csrf_token() }}"
            },
            init: function() {
                const instance = this;

                // Evento al añadir archivos
                instance.on("addedfile", function(file) {
                    setTimeout(() => {
                        assignDocumentClass(file, file.previewElement);
                    }, 10);
                });

                // Lógica de acumulación diferida al remover un archivo
                instance.on("removedfile", function(file) {
                    if (typeof file.status === 'undefined' || file.status === 'success') {
                        
                        // Si viene del servidor tiene ID numérico/UUID, si es nuevo usamos su nombre o ruta temporal
                        const fileIdentifier = file.id ? file.id : (file.serverId || file.name);
                        
                        removedFiles.push(fileIdentifier);
                        
                        let htmlInputs = '';
                        const isArray = "{{ $type }}" === 'video' ? '' : '[]';
                        const hiddenContainer = document.getElementById("removed-container-{{ $id }}");

                        removedFiles.forEach(val => {
                            htmlInputs += `<input type="hidden" name="removed_{{ $name }}${isArray}" value="${val}" />`;
                        });

                        if (hiddenContainer) {
                            hiddenContainer.innerHTML = htmlInputs;
                        }
                    }
                });

                // Capturar la respuesta de cargas nuevas para asociar rutas/IDs temporales
                instance.on("success", function(file, response) {
                    if (response && response.path) {
                        file.serverId = response.path; // Guardamos la referencia temporal del archivo recién subido
                    }
                });

                // Carga adaptativa de elementos previos del servidor
                const filesFromServer = @json($serverfiles);
                if (filesFromServer && filesFromServer.length > 0) {
                    filesFromServer.forEach(file => {
                        const mockFile = { 
                            name: file.name || "Archivo guardado", 
                            size: file.size || 1024, 
                            id: file.id, 
                            accepted: true 
                        };
                        
                        const isImage = /\.(jpeg|jpg|gif|png|svg|webp)$/i.test(mockFile.name);
                        instance.displayExistingFile(mockFile, isImage ? file.url : null);
                        
                        if (!isImage && mockFile.previewElement) {
                            mockFile.previewElement.classList.add('dz-file-preview');
                        }
                        
                        instance.files.push(mockFile);
                    });
                }

                instance.on("maxfilesexceeded", function(file) {
                    instance.removeFile(file);
                    alert("Límite de archivos superado para este campo.");
                });
            }
        });
    });
</script>