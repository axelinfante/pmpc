@props([
    'id',                                   // ID único del Dropzone (ej: dropzone-video)
    'name' => 'imagen',                     // Nombre del campo que recibirá Laravel (ej: video_principal)
    'label' => 'Galeria de Imagenes',       // Texto de la etiqueta superior
    'url',                                  // Ruta para procesar el dropzone
    'type' => 'images',                     // Tipo de carga: 'video' o 'images'
    'message' => 'Arrastra tus archivos aquí o haz clic', // Mensaje de la zona
    'maxFiles' => 20,                       // Límite de archivos. Si es 1, actúa como "Simple Video"
    'maxSize' => 20,                         // Tamaño máximo en MB
    'serverFiles' => []                     // Archivos precargados desde el servidor (Edición)
])

{{-- El bloque @once asegura que el CSS se inyecte una sola vez --}}
@once
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.2/dropzone.min.css" integrity="sha512-jU/7UFiaW5UBGODEopEqnbIAHOI8fO6T99m7Tsmqs2gkdujByJfkCbbfPSN4Wlqlb9TGnsuC0YgUgWkRBK7B9A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
@endonce

{{-- Estilos Personalizados del Componente (Se inyectan una sola vez) --}}
@once
<style>
    .dropzone-drag-area {
        width: 100%; 
        position: relative; 
        border-radius: 10px;
        border: 3px dashed #dbdeea; 
        background-color: #f8f9fa; 
        padding: 15px; 
        display: flex; 
        overflow: hidden;
        transition: border-color 0.2s ease, background-color 0.2s ease;
    }
    .dropzone-drag-area.dz-drag-hover {
        border-color: #0d6efd;
        background-color: #e9ecef;
    }
    /* Estilo para Un Solo Video (Formato horizontal extendido) */
    .dropzone-drag-area.zone-single-video { 
        height: auto; 
        aspect-ratio: 21 / 9; 
        align-items: center; 
        justify-content: center; 
    }
    /* Estilo para Múltiples elementos (Formato Cuadrícula/Galería) */
    .dropzone-drag-area.zone-gallery { 
        min-height: 140px; 
        height: auto; 
        flex-wrap: wrap; 
        gap: 12px; 
    }
    .dropzone-drag-area .dz-message { 
        text-align: center; 
        color: #6c757d; 
        font-weight: 500; 
        width: 100%; 
        margin: 0; 
    }
    .dropzone-drag-area.dz-started .dz-message { 
        display: none; 
    }

    /* Contenedor de miniaturas */
    .dz-preview { 
        width: 105px; 
        height: 105px; 
        position: relative; 
        z-index: 10; 
        border-radius: 8px; 
        overflow: hidden; 
    }
    /* Si es video único, la miniatura cubre toda la zona de arrastre */
    .zone-single-video .dz-preview { 
        width: 100%; 
        height: 100%; 
        position: absolute !important; 
        top: 0; 
        left: 0; 
    }
    .dz-photo { 
        width: 100%; 
        height: 100%; 
        background: #000; 
    }
    .dz-thumbnail, .dz-video-thumbnail { 
        width: 100%; 
        height: 100%; 
        object-fit: cover; 
    }

    /* Botón de Eliminación Flotante */
    .dz-delete {
        width: 26px; 
        height: 26px; 
        background: rgba(0, 0, 0, 0.7); 
        position: absolute; 
        opacity: 0; 
        top: 8px; 
        right: 8px;
        border-radius: 50%; 
        z-index: 30; 
        display: flex; 
        align-items: center; 
        justify-content: center; 
        cursor: pointer; 
        transition: opacity 0.2s ease, background-color 0.2s ease;
    }
    .dz-preview:hover .dz-delete { 
        opacity: 1; 
    }
    .dz-delete:hover { 
        background-color: #dc3545; 
    }

    /* Mensajes de Error Internos */
    .dz-error-message {
        position: absolute; 
        top: 0; 
        left: 0; 
        width: 100%; 
        height: 100%; 
        background: rgba(220, 53, 69, 0.9); 
        color: white;
        padding: 10px; 
        font-size: 11px; 
        text-align: center; 
        z-index: 20; 
        opacity: 0; 
        visibility: hidden; 
        display: flex; 
        align-items: center; 
        justify-content: center;
        transition: opacity 0.2s ease, visibility 0.2s ease;
    }
    .dz-preview.dz-error .dz-error-message { 
        opacity: 1; 
        visibility: visible; 
    }
</style>
@endonce

<div class="form-group mb-4">
    <label class="form-label text-muted opacity-75 fw-medium">{{ $label }}</label>
    
    <div id="{{ $id }}" 
         class="dropzone-drag-area {{ ($type === 'video' && (int)$maxFiles === 1) ? 'zone-single-video' : 'zone-gallery' }}"
         data-name="{{ $name }}"
         data-type="{{ $type }}"
         data-max-files="{{ $maxFiles }}"
         data-max-size="{{ $maxSize }}">
         
        <div class="dz-message">
            @if($type === 'video')
                <svg width="32" height="32" class="mb-2 text-muted opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
            @else
                <svg width="32" height="32" class="mb-2 text-muted opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
            @endif
            <br><span>{{ $message }}</span>
        </div>
    </div>

    <div id="removed-container-{{ $id }}"></div>
</div>

{{-- El bloque @once para Scripts asegura que el JS de la CDN se cargue una sola vez --}}
@once
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dropzone/5.9.2/min/dropzone.min.js" integrity="sha512-VQQXLthlZQO00P+uEu4mJ4G4OAgqTtKG1hri56kQY1DtdLeIqhKUp9W/lllDDu3uN3SnUNawpW7lBda8+dSi7w==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
@endonce

<script>
(function() {
    function initDropzone_{{ Str::slug($id, '_') }}() {
        if (typeof Dropzone === 'undefined') {
            setTimeout(initDropzone_{{ Str::slug($id, '_') }}, 50);
            return;
        }

        const el = document.getElementById("{{ $id }}");
        if (!el) return;
        if (el.dropzoneInstance) return;

        let removedFiles = [];
        const componentType = @js($type ?? 'images');
        const maxFilesAllowed = parseInt("{{ $maxFiles }}");
        
        const previewTemplate = (componentType === 'video') ? `
            <div class="dz-preview dz-file-preview">
                <div class="dz-photo"><video class="dz-video-thumbnail" muted autoplay loop playsinline></video></div>
                <div class="dz-delete" data-dz-remove><svg width="14" height="14" fill="white" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></div>
                <div class="dz-error-message"><span data-dz-errormessage></span></div>
            </div>` : `
            <div class="dz-preview dz-file-preview">
                <div class="dz-photo"><img data-dz-thumbnail class="dz-thumbnail" /></div>
                <div class="dz-delete" data-dz-remove><svg width="14" height="14" fill="white" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg></div>
                <div class="dz-error-message"><span data-dz-errormessage></span></div>
            </div>`;

        const dzInstance = new Dropzone("#{{ $id }}", {
            url: "{{ $url }}", 
            paramName: "{{ $name }}",
            acceptedFiles: componentType === 'video' ? "video/mp4, video/webm, video/quicktime" : "image/jpeg, image/png, image/webp",
            maxFilesize: {{ $maxSize }},
            maxFiles: {{ $maxFiles }},
            uploadMultiple: maxFilesAllowed === 1 ? false : true, 
            parallelUploads: {{ $maxFiles }},
            autoProcessQueue: false,
            previewTemplate: previewTemplate,
            headers: { 'X-CSRF-TOKEN': "{{ csrf_token() }}" },
            init: function() {
                const self = this;

                this.on('addedfile', function(file) {
                    // MANTENER SIMPLE VIDEO: Si está configurado para un solo archivo, limpia el anterior
                    if (maxFilesAllowed === 1 && self.files.length > 1) { 
                        self.removeFile(self.files[0]); 
                    }
                    
                    // Renderizar miniatura local si es video subido por usuario
                    if (componentType === 'video' && file.type && file.type.match('video.*') && !file.fromServer) {
                        const reader = new FileReader();
                        reader.onload = (e) => { 
                            if (file.previewElement) {
                                $(file.previewElement).find('.dz-video-thumbnail').attr('src', e.target.result); 
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                    $('#{{ $id }}').removeClass('is-invalid');
                });

                this.on('removedfile', function(file) {
                    if (typeof file.status == 'undefined' || file.status === 'success') {
                        removedFiles.push(file.name);
                        let htmlInputs = '';
                        removedFiles.forEach(name => {
                            const isArray = maxFilesAllowed === 1 ? '' : '[]';
                            htmlInputs += `<input type="hidden" name="removed_{{ $name }}${isArray}" value="${name}" />`;
                        });
                        $("#removed-container-{{ $id }}").html(htmlInputs);
                    }
                });

                // Cargar archivos del servidor si existen (Soporta $serverFiles y $dropzoneFiles de forma cruzada)
                const sFiles = {!! json_encode($serverFiles ?? $dropzoneFiles ?? []) !!};
                if(sFiles && sFiles.length > 0) {
                    sFiles.forEach(v => {
                        var mock = { name: v.name, size: v.filesize, accepted: true, fromServer: true };
                        
                        self.emit("addedfile", mock);
                        
                        if(componentType === 'video') {
                            if (mock.previewElement) {
                                const videoElement = mock.previewElement.querySelector('.dz-video-thumbnail');
                                if (videoElement) {
                                    videoElement.src = v.path;
                                    videoElement.load(); // Activa el buffering de streams pesados o locales de Laravel
                                }
                            }
                        } else {
                            self.emit("thumbnail", mock, v.path);
                        }
                        
                        self.emit("complete", mock);
                        self.files.push(mock);
                    });
                }
            }
        });

        el.dropzoneInstance = dzInstance;
    }

    // Disparadores de eventos
    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", initDropzone_{{ Str::slug($id, '_') }});
    } else {
        initDropzone_{{ Str::slug($id, '_') }}();
    }

    $(document).on('shown.bs.modal', function () {
        initDropzone_{{ Str::slug($id, '_') }}();
    });

    if (window.Livewire) {
        document.addEventListener("livewire:navigated", () => { initDropzone_{{ Str::slug($id, '_') }}(); });
        Livewire.hook('morph.updated', () => { initDropzone_{{ Str::slug($id, '_') }}(); });
    }
})();
</script>