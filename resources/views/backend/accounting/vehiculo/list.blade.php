@extends('layouts.app')
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/lozad/dist/lozad.min.js"></script>
<style>
	.toggleContainer {
   position: relative;
   display: grid;
   grid-template-columns: repeat(2, 1fr);
   width: fit-content;
   border: 3px solid #274331;
   border-radius: 20px;
   background: #274331;
   font-weight: bold;
   color: #274331;
   cursor: pointer;
 }
 .toggleContainer::before {
   content: '';
   position: absolute;
   width: 50%;
   height: 100%;
   left: 0%;
   border-radius:20px;
   background: white;
   transition: all 0.3s;
 }
 .toggleCheckbox:checked + .toggleContainer::before {
    left: 50%;
 }
 .toggleContainer div {
   padding: 6px;
   text-align: center;
   z-index: 1;
 }
 .toggleCheckbox {
   display: none;
 }
 .toggleCheckbox:checked + .toggleContainer div:first-child{
   color: white;
   transition: color 0.3s;
 }
 .toggleCheckbox:checked + .toggleContainer div:last-child{
   color: #343434;
   transition: color 0.3s;
 }
 .toggleCheckbox + .toggleContainer div:first-child{
   color: #343434;
   transition: color 0.3s;
 }
 .toggleCheckbox + .toggleContainer div:last-child{
   color: white;
   transition: color 0.3s;
 }
 
 .lozad.is-loaded {
    opacity: 1 !important;
}
 </style>
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



					 <div class="col mb-2">
                     	 <a class="btn btn-primary btn-xs ajax-modal" data-reload="false" data-title="{{ _lang('Add New Car') }}"
							href="{{ route('vehiculo.create') }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
	                     </div>
						 <input type="checkbox" id="regitro_activo" class="toggleCheckbox" />
						<label for="regitro_activo" class='toggleContainer'>
						<div>Activos</div>   
						<div>Eliminados</div>
						</label>

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
							<th style="width: 100%;min-width: 150px" class="text-center act">{{ _lang('Action') }}</th>
							<th style="width: 100%;min-width: 150px">{{ _lang('Nro interno') }}</th>
							<th style="width: 100%;min-width: 150px">{{ _lang('Dominio') }}</th>
							<th style="width: 100%;min-width: 150px">Anulado</th>
							{{--<th style="width: 100%;min-width: 150px">{{ _lang('Nro interno') }}</th>--}}
							<th style="width: 100%;min-width: 150px">{{ _lang('Fecha asignacion') }}</th>
							{{--<th style="width: 100%;min-width: 150px">{{ _lang('Forma') }}</th>--}}
							<th style="width: 100%;min-width: 150px">{{ _lang('Tramitador') }}</th>
							<th style="width: 100%;min-width: 150px">{{ _lang('Cia Aseg') }}</th>
                            <th style="width: 100%;min-width: 150px">{{ _lang('Tramitador de compañia') }}</th>
                            <th style="width: 100%;min-width: 150px">{{ _lang('Siniestro') }}</th>
                            <th style="width: 100%;min-width: 150px">{{ _lang('Marca') }}</th>
							<th style="width: 100%;min-width: 150px">{{ _lang('Modelo') }}</th>
                            <th style="width: 100%;min-width: 150px">{{ _lang('Motor') }}</th>
                            <th style="width: 100%;min-width: 150px">{{ _lang('Tipo de baja') }}</th>
                            <th style="width: 100%;min-width: 150px">{{ _lang('Asegurado') }}</th>
                            <th style="width: 100%;min-width: 150px">{{ _lang('Contacto') }}</th>
                            <th style="width: 100%;min-width: 150px">{{ _lang('Lugar de retiro') }}</th>
                            <th style="width: 100%;min-width: 150px">{{ _lang('Localidad') }}</th>
							<th style="width: 100%;min-width: 150px">{{ _lang('Provincia') }}</th>
							<th style="width: 100%;min-width: 150px">{{ _lang('Estado') }}</th>
							<th style="width: 100%;min-width: 150px">{{ _lang('Estado/Seguimiento') }}</th>
							<th style="width: 100%;min-width: 150px">{{ _lang('Entregado a') }}</th>
							<th style="width: 100%;min-width: 150px">{{ _lang('Fecha entrega 04') }}</th>
							<th style="width: 100%;min-width: 150px">{{ _lang('Observaciones administrativas') }}</th>
							<th style="width: 100%;min-width: 150px">{{ _lang('Fecha de recepción de documentos')}}</th>
							<th style="width: 100%;min-width: 150px">{{ _lang('Coordinar retiro')}}</th>
							<th style="width: 100%;min-width: 150px">{{ _lang('Fecha de envio de documentos a
							constituyentes')}}</th>
							<th style="width: 100%;min-width: 150px">{{ _lang('Chasis')}}</th>
							<th style="width: 100%;min-width: 150px">{{ _lang('Fecha de confirmacion de contacto')}}
							<th style="width: 100%;min-width: 150px">{{ _lang('Fecha de limite de retiro programado')}}
							<th style="width: 100%;min-width: 150px">{{ _lang('Retira')}}
							<th style="width: 100%;min-width: 150px">{{ _lang('Motor vendido')}}
							<th style="width: 100%;min-width: 150px">{{ _lang('Ubicacion') }}
							<th style="width: 100%;min-width: 150px">{{ _lang('Fecha de retiro')}}
							<th style="width: 100%;min-width: 150px">{{ _lang('Fecha de ingreso')}}
							{{--<th style="width: 100%;min-width: 150px">{{ _lang('Control fuera de programacion')}}--}}
								<th style="width: 100%;min-width: 150px" >{{ _lang('Observaciones de taller') }}</th>
							<th style="width: 100%;min-width: 150px">{{ _lang('Observaciones de retiro')}}
							<th style="width: 100%;min-width: 150px">{{ _lang('Fecha de pago Cia')}}								
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

	{{--<script src="https://code.jquery.com/jquery-3.5.1.js"></script>--}}
	{{--<script src="https://cdn.datatables.net/1.12.1/js/jquery.dataTables.min.js"></script>--}}
	{{--<script src="https://cdn.datatables.net/fixedheader/3.2.3/js/dataTables.fixedHeader.min.js"></script>--}}

	<script>
		const routes = {
			exportExcel: "{{ route('vehiculos.export.excel') }}",
			exportPDF: "{{ route('vehiculos.export.pdf') }}",
			csrfToken: "{{ csrf_token() }}"
		};

	var Estados_tables = <?php echo json_encode($estados); ?>;
	var lugarentregas_tables = <?php echo json_encode($lugar_entregas); ?>;

	/*for (const row_x of Estados_tables) {
 		 console.log(row_x.id+"-"+row_x.estado);
	}*/


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
 	
	<script src="{{ asset('public/backend/assets/js/ajax-datatable/vehiculo.js') }}"></script>

@endsection