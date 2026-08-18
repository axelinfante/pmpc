@extends('layouts.app')
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/lozad/dist/lozad.min.js"></script>
@section('content')
<div class="row">
	<div class="col-lg-12">

		<div class="card mt-2">
			<span class="panel-title d-none">{{ _lang('Cars List') }}</span>
			<div class="card-body">
				{{--<div class="row my-3">--}}
					{{--<div class="col-md-4">--}}
						{{--<select id="companySelect" class="form-control">--}}
							{{--@foreach($cias as $cia)--}}
								{{--<option @if(!empty(session('cia')) && session('cia') == $cia->id) selected @endif--}}
										{{--{{  empty(session('cia')) && auth()->user()->company_id == $cia->id ?--}}
										{{--'selected' : ''}}--}}
								{{--value="{{$cia->id}}">{{$cia->business_name}}</option>--}}
							{{--@endforeach--}}
						{{--</select>--}}
					{{--</div>--}}
				{{--</div>--}}

				<div class="row">



					 <!--<div class="col mb-2">
                     	 <a class="btn btn-primary btn-xs ajax-modal" data-title="{{ _lang('Add New Car') }}"
							href="{{ route('vehiculo.create') }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
                     </div>-->
                     {{--<div class="col-lg-3 mb-2">--}}
                     	 {{--<select class="form-control select2 select-filter" name="client_id">--}}
                             {{--<option value="">{{ _lang('All Customer') }}</option>--}}
                             {{--{{ create_option('contacts','id','contact_name','',array('company_id=' => company_id())) }}--}}
                     	 {{--</select>--}}
                     {{--</div>--}}

                     <!--<div class="col-lg-3">
                     	 <select class="form-control select2 select-filter" data-placeholder="{{ _lang('All Status') }}" name="status"
                     	 multiple="true">

                     	 	@forelse($estados as $estado)
                                 <option value="{{$estado->id}}"> {{ $estado->estado }}</option>
                                @empty


                            @endforelse
                     	 </select>
                     </div>-->
                </div>

                <hr>


				<table id="vehiculos_table" class="table table-bordered">
					<thead>
					    <tr>
							<th  class="text-center notexport">{{ _lang('Action') }}</th>
							<th >{{ _lang('Nro interno') }}</th>
							<th >{{ _lang('Dominio')}}</th>
							<th>{{ _lang('Nro Motor')}}</th>
							<th >{{ _lang('Tipo Baja')}}</th>
							<th >{{ _lang('Estado')}}</th>	
							<th>{{ _lang('Estado/Seguimiento') }}</th>
							<th >{{ _lang('Retira')}}</th>
							<th >{{ _lang('Color')}}</th>
							<th >{{ _lang('Marca') }}</th>
							<th >{{ _lang('Modelo') }}</th>
							<th style="width: 200px;min-width: 200px" >{{ _lang('Ubicacion') }}</th>
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
  var table = $("#vehiculos_table").appTable({
		title:"Listado receptores",
        ajax: ({
			url : _url + '/vehiculo/get_table_data',
			method: "POST",
			data: function (d) {
				d._token =  $('meta[name="csrf-token"]').attr('content');

              /*  if($('select[name=client_id]').val() != ''){
	                d.client_id = $('select[name=client_id]').val();
	            }

                if($('select[name=status]').val() != null){
                	d.status = JSON.stringify($('select[name=status]').val());
                }*/

            },
			 error: function (request, status, error) {
				console.log(request.responseText);
			 }
		}),
		columnFilters: [,, , , {type: 'select',data: @json($filterData)}, {type: 'select',data: @json($estados_data)}, , , , , {type: 'select',data: @json($lugar_entregas_data)}],
        "columns" : [
            { data : "action", name : "action", searcheable : false },
			{  data : 'id', name : 'id' },
			{  data : 'dominio', name : 'dominio' },
			{ data : 'motor_nro', name : 'motor_nro' },
			{  data : 'tipo_baja', name : 'tipo_baja' },
			{  data : 'estado', name : 'estado', searcheable : false},
			{ data : 'estado_seguimiento', name : 'estado_seguimiento' }, // Columna 19 (Posición 20)
            {  data : 'responsable_retiro', name : 'responsable_retiro' },
			{  data : 'color', name : 'color' },
			{  data : 'marca', name : 'marca' },
			{  data : 'modelo', name : 'modelo' },
            //{  data : 'kilometraje', name : 'kilometraje' },
            //{  data : 'pieza_no_disponible', name : 'pieza_no_disponible' },
			{  data : 'lugar_entrega', name : 'lugar_entrega', searcheable : false}
		],
    });
	
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


	{{--<script src="https://code.jquery.com/jquery-3.5.1.js"></script>--}}
	{{--<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>--}}
	{{--<script src="https://cdn.datatables.net/fixedheader/3.2.3/js/dataTables.fixedHeader.min.js"></script>--}}

	{{-- <script src="{{ asset('public/backend/assets/js/ajax-datatable/vehiculo-receptor.js') }}"></script>--}}
	
	
 
@endsection