@extends('layouts.app')
<style>

</style>
@section('content')
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="card-title mb-0">Filtros Avanzados</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <!-- Filtro Estado Activo -->
            <div class="col-md-4">
                <div class="form-group">
                    <label for="filtro_activo">Estado de la Marca</label>
                    <select id="filtro_activo" class="form-control filtro-dt">
                        <option value="">Todos</option>
                        <option value="Si">Activas</option>
                        <option value="No">Inactivas</option>
                    </select>
                </div>
            </div>

            <!-- Filtro Avanzado de Modelos (Búsqueda Masiva por AJAX) -->
            <div class="col-md-8">
                <div class="form-group">
                    <label for="filtro_modelo">Filtrar por Modelo Específico</label>
                    <select id="filtro_modelo" class="form-control filtro-dt" style="width: 100%;">
                        <option value="">Buscar modelo para filtrar...</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-12 text-right">
                <button type="button" id="btn_limpiar_filtros" class="btn btn-secondary btn-sm">Limpiar Filtros</button>
            </div>
        </div>
    </div>
</div>

<div class="row">
	<div class="col-lg-12">
		@can('marcas.create')
		<a class="btn btn-primary btn-xs ajax-modal" data-reload="false" data-title="{{ _lang('Crear Marca') }}" href="{{ route('marcas.create')
		}}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
		@endcan
		<div class="card mt-2">
			<span class="panel-title d-none">{{ _lang('Lista Marcas') }}</span>
			<div class="card-body">
				<table id="data-table" class="table table-bordered table-striped w-100">
					<thead>
					    <tr>
						    <th width="30%" class="text-center">{{ _lang('Marca') }}</th>
						    <th >{{ _lang('Modelos Asociados') }}</th>
						    <th width="10%" class="text-center">{{ _lang('Activo') }}</th>
							<th width="10%" class="text-center notexport">{{ _lang('Action') }}</th>
					    </tr>
					</thead>
					<tbody>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
@endsection

@section('js-script')
    <script>
$(document).ready(function() {
  var table = $("#data-table").appTable({
		title:"marcas",
        ajax: {
            url: "{{ route('marcas.index') }}",
            data: function (d) {
                d.activo = $('#filtro_activo').val();
                d.modelo_id = $('#filtro_modelo').val();
				d._token = $('meta[name="csrf-token"]').attr('content');
            }
        },
		columnFilters: ['input',{ defaultText: 'N/A' }, { 
            type: 'select', 
            data: ['Si','No'] // Array simple
        }], 
        columns: [
            {data: 'marca', name: 'marca'},
            {data: 'modelo', name: 'modelo'},
            {data: 'activo', name: 'activo'},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
    });

/*$('.dataTables_filter input')
    .unbind('keypress keyup input')
    .bind('keyup input', function (e) {
		 var code = e.keyCode || e.which;
		 if ($(this).val().length >= 3 && code === 13) {
			_table.search(this.value).draw();
		}
		
    });*/

	$( '#filtro_modelo' ).select2({
    width: '100%', 
    allowClear: true,
    placeholder: 'Escribe el modelo...',
    minimumInputLength: 2,
    ajax: {
        url: '{{ route("modelos.buscar.ajax") }}',
        dataType: 'json',
        delay: 250,
        data: function (params) {
            return {
                q: params.term,
                page: params.page || 1 
            };
        },
        processResults: function (data, params) {
            params.page = params.page || 1;
            return {
                results: data.data,
                pagination: {
                    more: data.current_page < data.last_page 
                }
            };
        },
        cache: true
    }
});


    $('.filtro-dt').on('change', function() {
		if (table) {
			table.ajax.reload(null, false);
		}
    });

    $('#btn_limpiar_filtros').on('click', function() {
        $('#filtro_activo').val('');
        $('#filtro_modelo').val(null).trigger('change');
		if (table) {
			table.ajax.reload(null, false);
		}
    });
	
});
 </script>
@endsection







