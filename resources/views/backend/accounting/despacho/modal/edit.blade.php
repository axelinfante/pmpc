        <style>
            .h1 {
                letter-spacing: -0.02em;
            }
            .dropzone {
                overflow-y: auto;
                border: 0;
				cursor: pointer;
                background: transparent;
            }
            .dz-preview {
                width: 100%;
                margin: 0 !important;
                height: 100%;
                padding: 15px;
                position: absolute !important;
                top: 0;
            }
            .dz-photo {
                height: 100%;
                width: 100%;
                overflow: hidden;
                border-radius: 12px;
                background: #eae7e2;
            }
            .dz-drag-hover .dropzone-drag-area {
                border-style: solid;
                border-color: #86b7fe;
				cursor: copy; /* Muestra un cursor de "copiar" */
            }
            .dz-thumbnail {
                width: 100%;
                height: 100%;
                object-fit: cover;
            }
            .dz-image {
                width: 90px !important;
                height: 90px !important;
                border-radius: 6px !important;
            }
            .dz-remove {
                display: none !important;
            }
            .dz-delete {
                width: 24px;
                height: 24px;
                background: rgba(0, 0, 0, 0.57);
                position: absolute;
                opacity: 0;
                transition: all 0.2s ease;
                top: 30px;
                right: 30px;
                border-radius: 100px;
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .dz-delete > svg {
                transform: scale(0.75);
                cursor: pointer;
            }
            .dz-preview:hover .dz-delete, 
            .dz-preview:hover .dz-remove-image {
                opacity: 1;
            }
            .dz-message {
                height: 100%;
                margin: 0 !important;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .dropzone-drag-area {
                height: 300px;
				width: 300px;
                position: relative;
                padding: 0 !important;
                border-radius: 10px;
                border: 3px dashed #dbdeea;
            }
            .was-validated .form-control:valid {
                border-color: #dee2e6 !important;
                background-image: none;
            }
        </style>
	
	<form method="post"  id="expense" class="ajax-submit" autocomplete="off"
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
			 <div class="form-group mb-4">
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
                    </div>
					

            <div class="col-md-12">
                <div class="form-group">
                    <input type="submit" id="formSubmit" class="btn btn-primary" value="Actualizar">
                </div>
            </div>



        </div>
    </div>
</form>
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

            /**
             * Setup dropzone
             */
            $('#expense').dropzone({
				paramName: "images_zona",
                previewTemplate: $('#dzPreviewContainer').html(),
                url: "{{ action('OrdenDespachoController@update', $id) }}",
                addRemoveLinks: true,
                autoProcessQueue: false,       
                uploadMultiple: false,
                parallelUploads: 1,
                maxFiles: 1,
                acceptedFiles: '.jpeg, .jpg, .png, .gif',
                thumbnailWidth: 900,
                thumbnailHeight: 600,
                previewsContainer: "#previews",
                timeout: 0,
                init: function() 
                {
                    myDropzone = this;

                    // when file is dragged in
                    this.on('addedfile', function(file) { 
                        $('.dropzone-drag-area').removeClass('is-invalid').next('.invalid-feedback').hide();
                    });
					@if(isset($dropzoneFiles))
				var files = {!! json_encode($dropzoneFiles) !!};
				$.each(files, function(key,value) {
						var mockFile = { name: value.name, size: value.filesize};
						myDropzone.emit("addedfile", mockFile);
						myDropzone.emit("thumbnail", mockFile, value.path);
						myDropzone.emit("complete", mockFile);

					});
					@endif
                },
                success: function(file, response) 
                {
                    // hide form and show success message
                    $('#expense').fadeOut(600);
                    setTimeout(function() {
                        //$('#successMessage').removeClass('d-none');
						$('#main_modal').modal('hide');
                    }, 600);
                },
				 error: function (request, status, error) {
					alert(5);
				
			 },
				
				removedfile: function (file) {
				if (typeof file.status == 'undefined') { 
					removedFiles.push(file.name)
					$("input[name=removed_files]").val(removedFiles);
				}
                file.previewElement.remove();
				}
            });
			
			 /**
             * Form on submit
             */
            $('#formSubmit').on('click', function(event) {
                event.preventDefault();
				event.stopPropagation();
                var $this = $(this);
				// if everything is ok, submit the form
					 if (myDropzone.getQueuedFiles().length > 0) {
							myDropzone.processQueue();
							//
					  }
					  else {
						// Upload anyway without files
						  $('#expense').submit(); 
					  }
            });
 


        
    </script>