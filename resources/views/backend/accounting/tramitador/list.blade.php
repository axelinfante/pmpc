@extends('layouts.app')
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/lozad/dist/lozad.min.js"></script>
<style>
 .lozad.is-loaded {
    opacity: 1 !important;
}
 </style>
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
                                <!--<option value=""> Filtrar por estado del trámite</option>-->
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
                                    <th class="text-center notexport" style="width:30px;">{{ _lang('Action') }}</th>
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
		    $(function() {
			
		var table = $("#vehiculos_table").appTable({
		title:"Lista Tramitadores",
		visibleGlobal: true,
        ajax: {
            url: _url + '/tramitador/get_table_data',
            method: "POST",
            data: function (d) {
                d._token = $('meta[name="csrf-token"]').attr('content');
                if ($('select[name=client_id]').val() != '') {
                    d.client_id = $('select[name=client_id]').val();
                }
                if ($('select[name=status]').val() != null) {
                    d.status = JSON.stringify($('select[name=status]').val());
                }
                if ($('select[name=estado_tramite]').val() != null) {
                    d.estado_tramite = JSON.stringify($('select[name=estado_tramite]').val());
                }

                
            },
            error: function (request, status, error) {  
                //console.log(request.responseText);
            }
        },
		columnFilters: [null,'input'], 
        columns: [
			{ data: "action", name: "action", orderable: false, searchable: false },
            { data: 'dominio', name: 'dominio', searchable: true },
            { data: 'id', name: 'id', searchable: true },
            { data: 'fecha_asignacion', name: 'fecha_asignacion', searchable: true },
            { data: 'tramitador', name: 'tramitador', searchable: true },
            { data: 'aseguradora', name: 'aseguradora', searchable: true },
            { data: 'company', name: 'company', searchable: true },
            { data: 'siniestro', name: 'siniestro', searchable: true },
            { data: 'marca_modelo', name: 'marca_modelo', searchable: true },
            { data: 'fecha_inicio', name: 'fecha_inicio', searchable: true },
            { data: 'fecha_finalizacion', name: 'fecha_finalizacion', searchable: true },
            { data: 'estado_tramite', name: 'estado_tramite', searchable: true, visible: false },
        ],
		 rowCallback: function(row, data) {
            // Obtener el valor de estado_tramite
            var estadoTramite = data.estado_tramite;
            
            // Aplicar color de fondo basado en estado_tramite
            if (estadoTramite === 'En Proceso') {
                $(row).css('background-color', '#33FFAC'); // Verde
            } else if (estadoTramite === 'En Gestoria') {
                $(row).css('background-color', '#33A8FF'); // Azul
            } else if (estadoTramite === 'Finalizado') {
                $(row).css('background-color', '#FFC433'); // Naranja
            } else {
                $(row).css('background-color', '#FFFFFF'); // Color por defecto
            }
        },
        createdRow: function(row, data, dataIndex) {
            $('td', row).eq(5).css('font-size', '12px'); 
            $('td', row).eq(8).css('font-size', '10px'); 
            $('td', row).eq(3).css('font-size', '12px'); 
            $('td', row).eq(9).css('font-size', '12px'); 
            $('td', row).eq(10).css('font-size', '12px'); 
        }
    }).on('init.dt', function () {
        $('[data-toggle="tooltip"]').tooltip();
    });
	
	  $('.page-container').addClass('sbar_collapsed');
	
	$('.select-filter').on('change', function (e) {
		 if (window.isResettingFilters) {
				return; 
			}
        table.draw();
    });

    $(window).resize(function () {
     //   table.columns.adjust().draw();
    });
	
	
	    /*			$('.filtros').val('');
                    $('.select-filter').val(null).trigger('change');
					//$('.select-filter').val('');
					vehiculo_table.search('').columns().search('').draw();
					*/
	
   	
		/*$('#data-table tbody').on('click', '.button-delete', function() {
			var row = $(this).closest('tr');
			var data = table.row(row).data();
			var recordId = data.id;
			
			if (confirm('¿Deseas eliminar esta fila?')) {
				
				$.ajax({
				//url: "/item/" + recordId,
				url: "{{ route('item.store') }}"+'/'+recordId,
				type: 'DELETE',
				data: {
					_token: $('meta[name="csrf-token"]').attr('content')
					},
				success: function(response) {
				if (table) {
					table.ajax.reload(null, false);
				}
            }
        });

		}
			
	});*/
		
		/*$('#filtrado').on('change', function(e) {
					e.preventDefault();
					table.draw();
           			return false; //for old browsers 
			});*/
	});	
	
	const observer = lozad('.lozad', {
    rootMargin: '10px 0px', // margin around the root
    threshold: 0.1,         // ratio of element visibility before loading
    load: function(el) {
        //console.log('Loading element:', el);
        // Custom loading logic here
      
		
		if (el.nodeName.toLowerCase() === 'video') {
            // Si tiene data-src directo
            if (el.dataset.src) {
                el.src = el.dataset.src;
            }
            // Si tiene fuentes internas
            const sources = el.querySelectorAll('source');
            if (sources.length > 0) {
                sources.forEach(source => {
                    source.src = source.dataset.src;
                });
            }
			  el.load(); // ¡Importante! Esto fuerza al navegador a leer el nuevo src
		}else{
			  el.src = el.dataset.src;
		}
    },
    loaded: function(el) {
        // Run after element is loaded
        el.classList.add('fade-in');
		
		
    }
});
	
	/*const observer = lozad('.lozad', {
    loaded: function(el) {
        //console.log('Elemento cargado:', el.src);
    }
	});*/
	
	$("#main_modal").on('show.bs.modal', function () {
			observer.observe(); 
	 });
	    
	
    </script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script src="https://cdn.datatables.net/plug-ins/1.10.24/sorting/datetime-moment.js"></script>
    <script src="{{-- asset('public/backend/assets/js/ajax-datatable/tramitador.js') --}}"></script>
@endsection
