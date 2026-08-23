    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <td colspan="2">
                                        <strong>Fecha :</strong> {{ $transfer->fecha_traslado }}
                                    </td>
                                    <td colspan="3">
                                        <strong> Referencia : </strong>{{ $transfer->reference }}
                                    </td>
                                </tr>
								 <tr>
                                    <td colspan="2">
                                        <strong>Almacen Origen : </strong> {{ $transfer->almacen_origen->nombre }}
                                    </td>
                                    <td colspan="3">
                                        <strong> Almacen Destino : </strong> {{ $transfer->almacen_destino->nombre }}
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        {{ $transfer->fecha_traslado }}
                                    </td>
                                    <td colspan="3">
                                        {{ $transfer->reference }}
                                    </td>
                                </tr>
                                <tr>
                                    <th colspan="2">Observacion</th>
                                    <td colspan="3">{{ $transfer->detalles }}</td>
                                </tr>
                                <tr>
                                    <th >Id Producto</th>
                                    <th >Producto</th>
                                    <th >Nro Interno</th>
                                    <th >Nro Oblea</th>
									<th >Estado</th>
									</tr>
					
                                @foreach($transfer->TransfersProduct as $items)
                                    <tr>	
                                        <td>{{ $items->product_id ?? '' }}</td>
                                        <td>{{ $items->inventario->item->item_name  ?? '' }}</td>
										<td>{{ nroInternoAlias($items->inventario->company_id, $items->inventario->tipo_vehiculo, $items->inventario->nro_interno)  }}</td>
										<td>{{ $items->inventario->nro_oblea ?? '' }}</td>
										<td> @if($items->recibido)
											<span class="badge badge-success">Recibido</span>
										@else
											<span class="badge badge-warning">Pendiente</span>
										@endif</td>
                                    </tr>
                                @endforeach
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>