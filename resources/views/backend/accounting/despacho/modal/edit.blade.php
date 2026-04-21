		<link href="{{ asset('public/backend/plugins/jquery-toast-plugin/jquery.toast.min.css') }}" rel="stylesheet" />
        <style>
          .dropzone-drag-area {
    height: 300px; width: 300px;
    position: relative; border-radius: 10px;
    border: 3px dashed #dbdeea;
}

.dz-preview {
    width: 100%; height: 100%; padding: 15px;
    position: absolute !important; top: 0; left: 0;
}

.dz-photo {
    height: 100%; width: 100%; overflow: hidden;
    border-radius: 12px; background: #eae7e2;
}

.dz-thumbnail {
    width: 100%; height: 100%; object-fit: cover;
}

/* --- ESTILOS PARA EL MENSAJE DE ERROR --- */
.dz-error-message {
    position: absolute;
    top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    background: rgba(255, 0, 0, 0.8);
    color: white;
    padding: 10px;
    border-radius: 5px;
    font-size: 13px;
    text-align: center;
    z-index: 10000;
    opacity: 0; /* Oculto por defecto */
    transition: opacity 0.3s ease;
    pointer-events: none;
    width: 80%;
}
/* Solo se muestra cuando Dropzone añade la clase .dz-error */
.dz-preview.dz-error .dz-error-message {
    opacity: 1;
    display: flex; /* Añade esto para centrar el texto */
    align-items: center;
    justify-content: center;
}



.dz-delete {
    width: 24px; height: 24px;
    background: rgba(0, 0, 0, 0.57);
    position: absolute; opacity: 0;
    top: 30px; right: 30px;
    border-radius: 100px; z-index: 9999;
    display: flex; align-items: center; justify-content: center;
}

.dz-preview:hover .dz-delete { opacity: 1; }


        </style>
	
	<form method="post" id="expense" class="validate ajax-submit" data-table_reload="orden-despacho-table" autocomplete="off"
    action="{{ action('OrdenDespachoController@update', $id) }}" enctype="multipart/form-data">
    {{ csrf_field() }}
    <input name="_method" type="hidden" value="PATCH">



    <div class="col-12">
        <div class="row">

            <div class="col-md-3 ">
                <div class="form-group">
                    <label class="control-label">Orden No. </label>
                    <input disabled type="text" class="form-control" name="id"
                        value="{{ old('id', $o->id ?? '') }}">
                </div>
            </div>

            <div class="col-md-9 ">
                <div class="form-group">
                    <label class="control-label">Pieza</label>
                    <input type="text" class="form-control" name="detallle"
                        value="{{ old('detallle', ($o->itemInvoice->item->item_name ?? '') . ' ' . ($o->itemInvoice->product->marcaModelo->marca->marca ?? '') . ' ' . ($o->itemInvoice->product->marcaModelo->modelo->modelo ?? '')) }}">
                </div>
            </div>

            <div class="col-md-6 ">
                <div class="form-group">
                    <label class="control-label">F. Envio otro Depósito</label>
                    <input type="date" class="form-control" name="fecha_envio_otro_dep"
                        value="{{ old('fecha_envio_otro_dep', isset($o->f_otro_deposito) ? \Carbon\Carbon::parse($o->f_otro_deposito)->format('Y-m-d') : '') }}">
                </div>
            </div>



            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">{{ _lang('F. Envio Depósito') }}</label>
                    <input type="date" class="form-control" name="fecha_envio_dep"
                        value="{{ old('fecha_envio_dep', isset($o->f_deposito) ? \Carbon\Carbon::parse($o->f_deposito)->format('Y-m-d') : '') }}">
                </div>
            </div>





            {{--<div class="col-md-6 ">
                <div class="form-group">
                    <label class="control-label">Fecha embalado</label>
                    <input type="date" class="form-control" name="embalado_el"
                        value="{{ old('embalado_el', isset($o->f_embalado) ? \Carbon\Carbon::parse($o->f_embalado)->format('Y-m-d') : '') }}">
                </div>
            </div>

            <div class="col-md-6 ">
                <div class="form-group">
                    <label class="control-label">Lugar embalado</label>
                    <input type="text" class="form-control" name="lugar_embalado"
                        value="{{ old('lugar_embalado', $o->lugar_embalado ?? '') }}">
                </div>
            </div> --}}

            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">{{ _lang('F. Entrega') }}</label>
                    <input type="date" class="form-control" name="fecha_entrega"
                        value="{{ old('fecha_entrega', isset($o->f_entrega) ? \Carbon\Carbon::parse($o->f_entrega)->format('Y-m-d') : '') }}">
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Forma de Entrega') }}</label>
                    <select name="forma_entrega" id="forma_entrega" class="form-control">
                        <option value="">-- Seleccionar --</option>
                       <!-- <option value="retira cliente"
                            {{ old('forma_entrega', $o->forma_entrega ?? '') == 'retira cliente' ? 'selected' : '' }}>
                            Retira cliente</option> -->
                        <option value="despacho"
                            {{ old('forma_entrega', $o->forma_entrega ?? '') == 'despacho' ? 'selected' : '' }}>
                            Despacho</option>
                        <option value="flete"
                            {{ old('forma_entrega', $o->forma_entrega ?? '') == 'flete' ? 'selected' : '' }}>Flete
                        </option>
                        <option value="Mostrador Colectora"
                            {{ old('forma_entrega', $o->forma_entrega ?? '') == 'Mostrador Colectora' ? 'selected' : '' }}>
                            Mostrador Colectora</option>
                        <option value="Mostrador ventanita"
                            {{ old('forma_entrega', $o->forma_entrega ?? '') == 'Mostrador ventanita' ? 'selected' : '' }}>
                            Mostrador ventanita</option>
                        <option value="Mostrador constituyentes"
                            {{ old('forma_entrega', $o->forma_entrega ?? '') == 'Mostrador constituyentes' ? 'selected' : '' }}>
                            Mostrador constituyentes</option>
                        <option value="Mostrador Octubre"
                            {{ old('forma_entrega', $o->forma_entrega ?? '') == 'Mostrador Octubre' ? 'selected' : '' }}>
                            Mostrador Octubre</option>
                    </select>
                </div>
            </div>




            <div class="col-md-6 ">
                <div class="form-group">
                    <label class="control-label">Despachado por</label>
                    <input type="text" class="form-control" name="despachado_por"
                        value="{{ old('despachado_por', $o->despachado_por ?? '') }}">
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">{{ _lang('Observaciones') }} </label>
                    <textarea type="text" class="form-control" name="observaciones">{{ old('observaciones', $o->observaciones) }}</textarea>
                </div>
            </div>


            <!--<div class="col-md-6">
                <div class="form-group">
                    <label class="control-label">Guia / Imagen </label>
                    <input type="file" class="form-control" name="imagen">

                    @if (!empty($o->foto_guia) && file_exists(public_path('uploads/ordenes/' . $o->foto_guia)))
                        <div class="mt-2" id="guia_preview">
                            <img  src="{{ asset('public/uploads/ordenes/' . $o->foto_guia) }}" alt="Imagen cargada"
                                style="max-width: 100px; max-height: 100px;">
                            <button type="button" class="btn btn-danger btn-sm" onclick="eliminarImagen()">X</button>
                            <input type="hidden" name="eliminar_imagen" id="eliminar_imagen" value="0">
                        </div>
                    @endif
                </div>
            </div>-->

			
			<input type="hidden" name="removed_files" />
			<!--<div class="form-group mb-4">
                        <label class="form-label text-muted opacity-75 fw-medium" for="formImage">Guia / Imagen </label>
                        <div class="dropzone-drag-area form-control" id="previews">
                            <div class="dz-message text-muted opacity-50" data-dz-message>
                                <span>Arrastra los archivos aquí para subirlos.</span>
                            </div>    
                            <div class="d-none" id="dzPreviewContainer">
                                <div class="dz-preview dz-file-preview">
                                    <div class="dz-photo">
                                        <img class="dz-thumbnail" data-dz-thumbnail>
                                    </div>
                                    <button class="dz-delete border-0 p-0" type="button" data-dz-remove>
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" id="times"><path fill="#FFFFFF" d="M13.41,12l4.3-4.29a1,1,0,1,0-1.42-1.42L12,10.59,7.71,6.29A1,1,0,0,0,6.29,7.71L10.59,12l-4.3,4.29a1,1,0,0,0,0,1.42,1,1,0,0,0,1.42,0L12,13.41l4.29,4.3a1,1,0,0,0,1.42,0,1,1,0,0,0,0-1.42Z"></path></svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="invalid-feedback fw-bold">Por favor sube una imagen.</div>
                    </div>-->
					
					<div class="form-group mb-4">		
									<label class="form-label text-muted opacity-75 fw-medium" for="formImage">Guia / Imagen </label>	
									<div id="mi-dropzone" class="dropzone-drag-area">
									<div class="dz-message">Arrastra una imagen o haz clic aquí</div>
								</div>
					</div>

            <div class="col-md-12">
                <div class="form-group">
                    <!--<input type="submit" id="formSubmit" class="btn btn-primary" value="Actualizar">-->
					<button type="button" id="formSubmit" class="btn btn-primary">Actualizar Orden</
                </div>
            </div>



        </div>
    </div>
</form>
<script src="{{ asset('public/backend/plugins/jquery-toast-plugin/jquery.toast.min.js') }}"></script>
<script>
/*function eliminarImagen() {
    if (confirm('¿Estás seguro que deseas eliminar la imagen actual?')) {
        document.getElementById('eliminar_imagen').value = 1;
       document.getElementById('guia_preview').style.display = 'none';
    }
}*/
</script>      

 <script>
 
 Dropzone.autoDiscover = false;
let removedFiles = [];

const myDropzone = new Dropzone("#mi-dropzone", {
    paramName: "images_zona",
    url: "{{ action('OrdenDespachoController@update', $id) }}",
	method: "post", // <--- CAMBIA PATCH POR POST AQUÍ
    //method: "patch", // IMPORTANTE: Para que coincida con tu ruta Laravel
    acceptedFiles: ".jpeg, .jpg, .png, .gif, .webp, .jfif",
    maxFilesize: 5,
    autoProcessQueue: false, // El formulario se envía manualmente
    uploadMultiple: false,
    parallelUploads: 1,
    maxFiles: 1,
    headers: {
        'X-CSRF-TOKEN': "{{ csrf_token() }}" // Token de seguridad
    },
    previewTemplate: `
        <div class="dz-preview dz-file-preview">
            <div class="dz-photo">
                <img data-dz-thumbnail class="dz-thumbnail" />
            </div>
            <div class="dz-delete" data-dz-remove>
                <svg width="16" height="16" fill="white" viewBox="0 0 24 24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/></svg>
            </div>
            <div class="dz-error-message"><span data-dz-errormessage></span></div>
        </div>
    `,
    init: function() {
        const self = this;

        // Si ya hay una imagen cargada desde el servidor
        @if(isset($dropzoneFiles))
            var files = {!! json_encode($dropzoneFiles) !!};
            $.each(files, function(key, value) {
                var mockFile = { name: value.name, size: value.filesize, accepted: true };
                self.emit("addedfile", mockFile);
                self.emit("thumbnail", mockFile, value.path);
                self.emit("complete", mockFile);
            });
        @endif

        this.on('addedfile', function(file) {
            // Eliminar archivos anteriores si subes uno nuevo (solo permite 1)
            if (this.files.length > 1) { this.removeFile(this.files[0]); }
            $('.dropzone-drag-area').removeClass('is-invalid');
        });
		
		this.on("sending", function(file, xhr, formData) {
			// 1. Añadimos los campos normales del form
			const data = $('#expense').serializeArray();
			$.each(data, function(key, el) {
				formData.append(el.name, el.value);
			});

			// 2. Forzamos campos críticos de Laravel si no estuvieran
			//if (!formData.has("_token")) formData.append("_token", "{{ csrf_token() }}");
			if (!formData.has("_method")) formData.append("_method", "PATCH");
		});
       
    },
    error: function(file, message) {
        // Esto asegura que el mensaje se escriba en el span y se muestre
        $(file.previewElement).addClass("dz-error");
        $(file.previewElement).find('.dz-error-message span').text(message);
    },
    success: function(file, response) {
		 // hide form and show success message
                    $('#expense').fadeOut(600);
                    setTimeout(function() {
                        //$('#successMessage').removeClass('d-none');
						$('#main_modal').modal('hide');
                    }, 600);
//		alert("Guardado correctamente");
        //$('#main_modal').modal('hide');
        //location.reload(); // O tu lógica de éxito
    },
		removedfile: function (file) {
				if (typeof file.status == 'undefined') { 
					removedFiles.push(file.name)
					$("input[name=removed_files]").val(removedFiles);
				}
                file.previewElement.remove();
				}
});
			
			$('#formSubmit').on('click', function(e) {
					e.preventDefault();
				// Verifica si hay archivos nuevos esperando ser subidos
				// (Los archivos que ya estaban no cuentan como Queued)
				if (myDropzone.getQueuedFiles().length > 0) {
					myDropzone.processQueue(); 
				} else {
								$('#expense').submit(); 
			}
			});
			
 




        
    </script>