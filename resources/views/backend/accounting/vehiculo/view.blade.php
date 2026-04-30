<p>
  <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
    Mostrar Observaciones y/o Datos
  </button>
</p>
<div class="collapse" id="collapseExample">
    <div class="card card-body">
        <div class="row">
            <div class="col-md-12 bg-warning"  >ASIGNACION DE VEHICULO</div>
	            <div class="col-sm-12">
                    <div class="table-responsive">
                                <table class="fl-table">
										<tbody>
											<tr>										
												<td><b>{{ _lang('Company') }} :</b></td>
												<td >{{$cars->company->business_name ?? ''}}</td>
                                                <td><b>Fecha Asignacion :</b></td>		
                                                <td >{{formatDate($cars->fecha_asignacion)}}</td>
											</tr> 
											<tr>										
												<td><b>{{ _lang('Tramitador') }} :</b></td>
												<td >{{$cars->tramitador->name  ?? ''}}</td>
												<td><b>Siniestro :</b></td>
												<td >{{$cars->siniestro  ?? ''}}</td>
											</tr>
                                            <tr>										
												<td><b>{{ _lang('Aseguradora') }} :</b></td>
												<td >{{$cars->aseguradora->nombre}}</td>
												<td><b>{{ _lang('Tramitador de compañia') }} :</b></td>
												<td >{{$cars->tramitador_compania }}</td>
											</tr>
                                            <tr>										
												<td><b>{{ _lang('Tipo Vehiculo') }} :</b></td>
												<td >{{$cars->tipo_vehiculo}}</td>
												<td><b>{{ _lang('Dominio') }} :</b></td>
												<td >{{$cars->dominio }}</td>
											</tr>
                                            <tr>										
												<td><b>{{ _lang('Marca') }} :</b></td>
												<td >{{$cars->marca_modelo->marca->marca ?? ''}}</td>
												<td><b>{{ _lang('Modelo') }} :</b></td>
												<td >{{$cars->marca_modelo->modelo->modelo  ?? '' }}</td>
											</tr>
                                            <tr>										
												<td><b>{{ _lang('Estado') }} :</b></td>
												<td >{{$cars->estado->estado ?? null }}</td>
												<td><b>{{ _lang('Tipo') }} :</b></td>
												<td >{{$cars->tipo }}</td>
											</tr>
                                             <tr>										
												<td><b>{{ _lang('Marca Motor') }} :</b></td>
												<td >{{$cars->marca_motor}}</td>
												<td><b>{{ _lang('Motor') }} :</b></td>
												<td >{{$cars->motor_nro }}</td>
											</tr>
                                            <tr>										
												<td><b>{{ _lang('Marca Chasis') }} :</b></td>
												<td >{{$cars->marca_chasis}}</td>
												<td><b>{{ _lang('Chasis') }} :</b></td>
												<td >{{$cars->chasis }}</td>
											</tr>
                                            <tr>										
												<td><b>{{ _lang('Color') }} :</b></td>
												<td >{{$cars->color}}</td>
												<td><b>{{ _lang('Tipo de baja') }} :</b></td>
												<td >{{$tipo_baja[$cars->tipo_baja] ?? null  }}</td>
											</tr>
										</tbody>
								</table>
	                    </div>
	            </div>	
        </div>

  
            <div class="row">
                <div class="col-md-12 bg-warning">DATOS ASEGURADOS</div>
                    <div class="col-sm-12">
                        <div class="table-responsive">
                                            <table class="fl-table">
                                                        <tbody>
                                                            <tr>										
                                                                <td><b>{{ _lang('Asegurado') }} :</b></td>
                                                                <td >{{$cars->asegurado}}</td>
                                                                <td><b>{{ _lang('Contacto') }} :</b></td>
                                                                <td >{{$cars->contacto}}</td>
                                                            </tr>
                                                            <tr>										
                                                                <td ><b>{{ _lang('Fecha confirmacion') }} :</b></td>
                                                                <td colspan="3">{{formatDate($cars->fecha_confirmacion_contacto)}}</td>
                                                            </tr>
                                                        </tbody>
                                                </table>
                        </div>
	                </div>	
           </div>

           <div class="row">
                <div class="col-md-12 bg-warning">COORDINACION DE RETIRO</div>
                    <div class="col-sm-12">
                        <div class="table-responsive">
                                            <table class="fl-table">
                                                        <tbody>
                                                            <tr>										
                                                                <td><b>{{ _lang('Fecha solicitud de retiro') }} :</b></td>
                                                                <td >{{formatDate($cars->fecha_limite_retiro)}}</td>
                                                                <td><b>{{ _lang('Coordinar retiro') }} :</b></td>
                                                                <td >{{$cars->coordinar_retiro ? 'Si' : 'No'}}</td>
                                                            </tr>
                                                            <tr>										
                                                                <td ><b>{{ _lang('Avisar a tramitador') }} :</b></td>
                                                                <td >{{$cars->avisar_tramitador ? 'Si' : 'No'}}</td>
                                                                <td ><b>{{ _lang('Retiro anticipado') }} :</b></td>
                                                                <td >{{$cars->retiro_anticipado ? 'Si' : 'No'}}</td>
                                                            </tr>
                                                            <tr>										
                                                                <td ><b>{{ _lang('Transportista') }} :</b></td>
                                                                <td >{{$cars->responsable_retiro->name ?? null}}</td>
                                                                <td ><b>{{ _lang('Depósito') }} :</b></td>
                                                                <td >{{$cars->lugar_entrega->nombre ?? null}}</td>
                                                            </tr>
                                                            <tr>										
                                                                <td ><b>{{ _lang('Fecha de retiro') }} :</b></td>
                                                                <td >{{formatDate($cars->fecha_retiro)}}</td>
                                                                <td ><b>{{ _lang('Lugar de retiro') }} :</b></td>
                                                                <td >{{$cars->lugar_retiro}}</td>
                                                            </tr>
                                                            <tr>										
                                                                <td ><b>{{ _lang('Localidad') }} :</b></td>
                                                                <td >{{$cars->localidad}}</td>
                                                                <td ><b>{{ _lang('Provincia') }} :</b></td>
                                                                <td >{{$cars->provincias->provincia ?? null}}</td>
                                                            </tr>
                                                            <tr>										
                                                                <td ><b>{{ _lang('Observaciones retiro') }} :</b></td>
                                                                <td colspan="3" >{{$cars->observacion_retiro}}</td>
                                                            </tr>
                                                        </tbody>
                                                </table>
                        </div>
	                </div>	
           </div>

            <div class="row">
                <div class="col-md-12 bg-warning">DOCUMENTACION</div>
                    <div class="col-sm-12">
                        <div class="table-responsive">
                                            <table class="fl-table">
                                                        <tbody>
                                                            <tr>										
                                                                <td><b>{{ _lang('04 Entregado a') }} :</b></td>
                                                               	<td >{{$responsable_entregas[$cars->entregado_a] ?? null  }}</td>
                                                                <td><b>{{ _lang('Fecha entrega 04') }} :</b></td>
                                                                <td >{{formatDate($cars->fecha_entrega_asegurado_cia)}}</td>
                                                            </tr>
                                                            <tr>										
                                                                <td ><b>{{ _lang('Gestor') }} :</b></td>
                                                                <td >{{$cars->gestor }}</td>
                                                                <td><b>{{ _lang('Observacion administrativas') }} :</b></td>
                                                                <td >{{$cars->observaciones_admin}}</td>
                                                            </tr>
                                                            <tr>										
                                                                <td ><b>{{ _lang('Fecha recepcion de documentacion') }} :</b></td>
                                                                <td >{{formatDate($cars->fecha_recepcion)}}</td>
                                                                <td><b>{{ _lang('Fecha envio doc') }} :</b></td>
                                                                <td >{{formatDate($cars->fecha_envio_doc)}}</td>
                                                            </tr>
                                                            <tr>										
                                                                <td ><b>{{ _lang('No requiere enviar al DRNPA') }} :</b></td>
                                                                <td colspan="3" >{{$cars->coordinar_retiro == 1 ? 'Si' : 'No'}}</td>
                                                            </tr>
                                                        </tbody>
                                                </table>
                        </div>
	                </div>	
           </div>
 
            <div class="row">
                <div class="col-md-12 bg-warning">INGRESO DE VEHICULO</div>
                    <div class="col-sm-12">
                        <div class="table-responsive">
                                            <table class="fl-table">
                                                        <tbody>
                                                            <tr>										
                                                                <td><b>{{ _lang('Fecha de ingreso') }} :</b></td>
                                                               	<td >{{formatDate($cars->fecha_ingreso)}}</td>
                                                                <td ><b>{{ _lang('Piezas en mal estado') }} :</b></td>
                                                                <td >{{$cars->piezas_defectuosas }}</td>
                                                            </tr>
                                                            <tr>										
                                                                <td ><b>{{ _lang('Piezas ausentes') }} :</b></td>
                                                                <td colspan="3" >@php
                                                                        foreach ($cars->pieza_ausente as $p) {
                                                                            echo "$p->name</br>";
                                                                        }
                                                                    @endphp</td>
                                                            </tr>
                                                            <tr>										
                                                                <td><b>{{ _lang('Motor en marcha') }} :</b></td>
                                                                <td >{{$cars->motor_en_marcha}}</td>
                                                                <td ><b>{{ _lang('Kilometraje') }} :</b></td>
                                                                <td colspan="3" >{{$cars->kilometraje }}</td>
                                                            </tr>
                                                        </tbody>
                                                </table>
                        </div>
	                </div>	
           </div>

           
 
    </div> <!-- card body--->
</div>


<div class="row">
    <div class="col-lg-12">
        <div class="card">

            <span class="panel-title d-none">{{ $cars->id }}</span>



            <div class="card-body">


                <div class="tab-content mt-4">
                    <div class="tab-pane active" id="cars_details">
                        <div class="row">



                        </div>
                    </div>


                    <!-- Task tab-->



                    <!--Start invoice tab-->
                    {{-- <div class="tab-pane" id="invoices"> --}}

                    {{-- <a href="{{ route('invoices.create') }}?related_to=projects&project_id={{ $project->id }}" class="btn btn-info btn-xs mb-4"><i class="ti-plus"></i> {{ _lang('Create New') }}</a> --}}

                    {{-- <div class="table-responsive"> --}}
                    {{-- <table id="invoice-table" class="table table-bordered"> --}}
                    {{-- <thead> --}}
                    {{-- <tr> --}}
                    {{-- <th>{{ _lang('Invoice Number') }}</th> --}}
                    {{-- <th>{{ _lang('Due Date') }}</th> --}}
                    {{-- <th class="text-right">{{ _lang('Grand Total') }}</th> --}}
                    {{-- <th class="text-right">{{ _lang('Paid') }}</th> --}}
                    {{-- <th class="text-center">{{ _lang('Status') }}</th> --}}
                    {{-- <th class="text-center">{{ _lang('Action') }}</th> --}}
                    {{-- </tr> --}}
                    {{-- </thead> --}}
                    {{-- <tbody> --}}
                    {{-- @foreach ($invoices as $invoice) --}}
                    {{-- <tr> --}}
                    {{-- <td class='invoice_number'>{{ $invoice->invoice_number }}</td> --}}
                    {{-- <td class='due_date'>{{ date($date_format,strtotime($invoice->due_date)) }}</td> --}}
                    {{-- <td class='grand_total text-right'>{{ decimalPlace($invoice->grand_total, $currency) }}</td> --}}
                    {{-- <td class='paid text-right'>{{ decimalPlace($invoice->paid, $currency) }}</td> --}}
                    {{-- <td class='status text-center'>{!! strip_tags(invoice_status($invoice->status),'<span>') !!}</td> --}}
                    {{-- <td class="text-center"> --}}

                    {{-- <div class="dropdown"> --}}
                    {{-- <button class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown">{{ _lang('Action') }} --}}
                    {{-- <i class="fa fa-angle-down"></i></button> --}}
                    {{-- <ul class="dropdown-menu"> --}}
                    {{-- <a class="dropdown-item" href="{{ action('InvoiceController@edit', $invoice->id) }}"><i class="fas fa-edit"></i> {{ _lang('Edit') }}</a> --}}
                    {{-- <a class="dropdown-item" href="{{ action('InvoiceController@show', $invoice->id) }}" data-title="{{ _lang('View Invoice') }}" data-fullscreen="true"><i class="fas fa-eye"></i> {{ _lang('View') }}</a> --}}
                    {{-- <a class="dropdown-item ajax-modal" href="{{ url('invoices/create_payment/'.$invoice->id) }}" data-title="{{ _lang('Make Payment') }}"><i class="fas fa-credit-card"></i> {{ _lang('Make Payment') }}</a> --}}
                    {{-- <a class="dropdown-item ajax-modal" href="{{ url('invoices/view_payment/'.$invoice->id) }}" data-title="{{ _lang('View Payment') }}" data-fullscreen="true"><i class="fas fa-credit-card"></i> {{ _lang('View Payment') }}</a> --}}

                    {{-- <form action="{{action('InvoiceController@destroy', $invoice['id'])}}" method="post"> --}}
                    {{-- {{ csrf_field() }} --}}
                    {{-- <input name="_method" type="hidden" value="DELETE"> --}}
                    {{-- <button class="button-link btn-remove" type="submit"><i class="fas fa-trash-alt"></i> {{ _lang('Delete') }}</button> --}}
                    {{-- </form> --}}

                    {{-- </ul> --}}
                    {{-- </div> --}}
                    {{-- </td> --}}
                    {{-- </tr> --}}
                    {{-- @endforeach --}}
                    {{-- </tbody> --}}
                    {{-- </table> --}}
                    {{-- </div> --}}
                    {{-- </div> --}}
                    <!--End Invoice Tab-->


                    <!--Start Expense tab-->
                    {{-- <div class="tab-pane" id="expense"> --}}

                    {{-- <a href="{{ route('expense.create') }}?related_to=projects&project_id={{ $project->id }}" data-title="{{ _lang('Add Expense') }}" class="btn btn-info btn-xs ajax-modal mb-4"><i class="ti-plus"></i> {{ _lang('Create New') }}</a> --}}

                    {{-- <div class="table-responsive"> --}}
                    {{-- <table id="expense-table" class="table table-bordered"> --}}
                    {{-- <thead> --}}
                    {{-- <tr> --}}
                    {{-- <th>{{ _lang('Date') }}</th> --}}
                    {{-- <th>{{ _lang('Account') }}</th> --}}
                    {{-- <th>{{ _lang('Expense Type') }}</th> --}}
                    {{-- <th class="text-right">{{ _lang('Amount') }}</th> --}}
                    {{-- <th>{{ _lang('Method') }}</th> --}}
                    {{-- <th class="action-col">{{ _lang('Action') }}</th> --}}
                    {{-- </tr> --}}
                    {{-- </thead> --}}
                    {{-- <tbody> --}}
                    {{-- @foreach ($expenses as $expense) --}}
                    {{-- <tr> --}}
                    {{-- <td class='trans_date'>{{ date("$date_format",strtotime($expense->trans_date)) }}</td> --}}
                    {{-- <td class='account_id'>{{ $expense->account->account_title }}</td> --}}
                    {{-- <td class='chart_id'>{{ $expense->expense_type->name }}</td> --}}
                    {{-- <td class='amount text-righ'>{{ decimalPlace($expense->amount, $currency) }}</td> --}}
                    {{-- <td class='payment_method_id'>{{ $expense->payment_method->name }}</td> --}}
                    {{-- <td class="text-center"> --}}

                    {{-- <div class="dropdown"> --}}
                    {{-- <button class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown">{{ _lang('Action') }} --}}
                    {{-- <i class="fa fa-angle-down"></i></button> --}}
                    {{-- <ul class="dropdown-menu"> --}}
                    {{-- <a class="dropdown-item ajax-modal" data-title="{{ _lang('Update Expense') }}" href="{{ action('ExpenseController@edit', $expense->id) }}"><i class="fas fa-edit"></i> {{ _lang('Edit') }}</a> --}}

                    {{-- <a class="dropdown-item ajax-modal" href="{{ action('ExpenseController@show', $expense->id) }}" data-title="{{ _lang('View Expense') }}"><i class="fas fa-eye"></i> {{ _lang('View') }}</a> --}}

                    {{-- <form action="{{action('ExpenseController@destroy', $expense['id'])}}" method="post"> --}}
                    {{-- {{ csrf_field() }} --}}
                    {{-- <input name="_method" type="hidden" value="DELETE"> --}}
                    {{-- <button class="button-link btn-remove" type="submit"><i class="fas fa-trash-alt"></i> {{ _lang('Delete') }}</button> --}}
                    {{-- </form> --}}

                    {{-- </ul> --}}
                    {{-- </div> --}}
                    {{-- </td> --}}
                    {{-- </tr> --}}
                    {{-- @endforeach --}}
                    {{-- </tbody> --}}
                    {{-- </table> --}}
                    {{-- </div> --}}
                    {{-- </div> --}}
                    <!--End Invoice Tab-->
                    <h3 class="mb-3">Video <a href="{{ route('veh_imag_zip',['id' => $cars->id, 'tipo' =>'videos']) }}" class="btn btn-info btn-xs mb-4"><i class="ti-zip"></i> Descargar Videos</a>  <a href="{{ route('veh_imag_zip',['id' => $cars->id, 'tipo' =>'all']) }}" class="btn btn-warning  btn-xs mb-4"><i class="ti-zip"></i> Descargar todo</a></h3>
                    @if (!empty($cars->video))
                        <div class="" id="files">
                            @php
                                $videos = explode(';', $cars->video);
                            @endphp
                            @forelse ($videos as $v)
								@php
									echo video_lazy('uploads/vehiculos/' . $v);
								@endphp
                            @empty
                            @endforelse


                            {{-- <video width="320" height="240" autoplay> --}}
                            {{-- <source src="movie.mp4" type="video/mp4"> --}}
                            {{-- <source src="movie.ogg" type="video/ogg"> --}}
                            {{-- Your browser does not support the video tag. --}}
                            {{-- </video> --}}
                        </div><!-- End File Tab-->
                    @else
                        <span class=" alert alert-warning">No se ha cargado video</span>
                    @endisset

                    {{-- {{ dd($cars->img) }} --}}
                    <div id="galeria">
                        <h3 class="my-3">Fotos 04D <a href="{{ route('veh_imag_zip',['id' => $cars->id, 'tipo' =>'4d']) }}" class="btn btn-info btn-xs mb-4"><i class="ti-zip"></i> Descargar Imagenes 04D</a></h3>

                        <div class="col-md-12 border rounded">


                            @forelse($cars->img_recepcion as $img)
                                <div class="card mx-3" style="width: 18rem;">
									@php
										echo img_lazy('uploads/vehiculos/' . $img);
									@endphp
                                    <!--<img class="card-img-top img-fluid" src="{{ marcaAgua(asset('public/uploads/vehiculos/'. $img->img),$cars->company_id,'/vehiculos/'.$img->img) }}" alt="Card image cap">-->
                                    <div class="card-body">
                                    </div>
                                </div>
                            @empty
                                <p>No hay imágenes 04D disponibles.</p>
                            @endforelse
                        </div>
                        <h3 class="my-3">Fotos generales <a href="{{ route('veh_imag_zip',$cars->id) }}" class="btn btn-info btn-xs mb-4"><i class="ti-zip"></i> Descargar Imagenes</a></h3>
                        <div class="col-md-12 border rounded ">
                        @forelse($cars->img as $img)
                            <div class="row">
                                <div class="col" style="margin-top: 0.50rem !important;">
									@php
										echo img_lazy('uploads/vehiculos/' . $img->img);
									@endphp
                                	<!--<img class="card-img-top img-fluid" src="{{ marcaAgua(asset('public/uploads/vehiculos/'. $img->img),$cars->company_id,'/vehiculos/'.$img->img) }}" alt="">-->
                                    <!--<img src="{{asset('public/uploads/vehiculos/'.$img->img)}}" alt="">-->
                                </div>
                            </div>
                        @empty
                            <span class=" alert alert-warning">No se cargaron fotos</span>
                        @endforelse
                    </div>
                    </div>


                    <div class="crm-scroll">
                        <table id="activity_log_table" class="table table-bordered">
                            <tbody>
                            </tbody>
                        </table>
                    </div>
            </div> <!-- End activity_log Tab-->

        </div>
    </div>
</div>
</div>
</div>



@section('js-script')
<script>
</script>
@endsection
