@extends('layouts.app')

@section('content')
    <!--Start Card-->
    <div class="row">
	 @can('bton-nueva_cotizacion')
		<div class="col-lg-3 mb-3">
			<div class="card">
				<a href="{{ route('buscador_de_piezas') }}" class="seo-fact sbg1" style="text-decoration: none; color: inherit;">
					<div class="p-4">
						<div class="seofct-icon">
							{{-- <span>{{ _lang('Total Invoice') }}</span> --}}
						</div>
						<h2>NUEVA COTIZACIÓN</h2>
					</div>
				</a>
			</div>
		</div>
	@endcan
 	@can('bton-clientes')
        <div class="col-lg-3 mb-3">
			<div class="card">
				<a href="{{ route('contacts.index') }}" class="seo-fact sbg2" style="text-decoration: none; color: inherit;">
					<div class="p-4">
						{{-- <div class="seofct-icon">
							<span>{{ _lang('Unpaid Invoice') }}</span>
						</div> --}}
						<h2>CLIENTES</h2>
					</div>
				</a>
			</div>
		</div>
	 @endcan
 @can('bton-orden_desarme') 
        <div class="col-lg-3 mb-3">
            <div class="card">
				<a href="{{ route('orden-desarme.index') }}" class="seo-fact sbg3" style="text-decoration: none; color: inherit;">
					<div class="p-4">
						{{-- <div class="seofct-icon">
							<span>{{ _lang('Unpaid Invoice') }}</span>
						</div> --}}
						<h2>ORDEN DESARME</h2>
					</div>
				</a>
			</div>
        </div>
 @endcan  
	 @can('bton-vehiculos') 
        <div class="col-lg-3 mb-3">
            <div class="card">
				<a href="{{ route('vehiculo.index') }}" class="seo-fact sbg4" style="text-decoration: none; color: inherit;">
					<div class="p-4">
						{{-- <div class="seofct-icon">
							<span>{{ _lang('Unpaid Invoice') }}</span>
						</div> --}}
						<h2>VEHICULOS</h2>
					</div>
				</a>
            </div>
        </div>
	 @endcan  
    </div><!--end row-->
    <!--End Card-->
@endsection


