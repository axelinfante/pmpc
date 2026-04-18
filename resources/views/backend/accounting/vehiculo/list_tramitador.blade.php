@extends('layouts.app')

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
                     	 <a class="btn btn-primary btn-xs ajax-modal" data-title="{{ _lang('Add New Car') }}"
							href="{{ route('vehiculo.create') }}"><i class="ti-plus"></i> {{ _lang('Add New') }}</a>
                     </div>
                     {{--<div class="col-lg-3 mb-2">--}}
                     	 {{--<select class="form-control select2 select-filter" name="client_id">--}}
                             {{--<option value="">{{ _lang('All Customer') }}</option>--}}
                             {{--{{ create_option('contacts','id','contact_name','',array('company_id=' => company_id())) }}--}}
                     	 {{--</select>--}}
                     {{--</div>--}}

                     <div class="col-lg-3">
                     	 <select class="form-control select2 select-filter" data-placeholder="{{ _lang('All Status') }}" name="status"
                     	 multiple="true">

                     	 	@forelse($estados as $estado)
                                 <option value="{{$estado->id}}"> {{ $estado->estado }}</option>
                                @empty


                            @endforelse
                     	 </select>
                     </div>
                </div>

                <hr>


				<table id="vehiculos_table" class="table table-bordered">
					<thead>
					    <tr>
							<th  class="text-center">{{ _lang('Action') }}</th>
							<th >{{ _lang('Nro interno') }}</th>
							<th >{{ _lang('Compañia') }}</th>
							<th >{{ _lang('Tramitador') }}</th>
							<th >{{ _lang('Siniestro') }}</th>
                            <th >{{ _lang('Dominio') }}</th>
                            <th >{{ _lang('Marca y modelo') }}</th>

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
	</script>
	<script src="{{ asset('public/backend/assets/js/ajax-datatable/vehiculo-tramitador.js') }}"></script>

@endsection