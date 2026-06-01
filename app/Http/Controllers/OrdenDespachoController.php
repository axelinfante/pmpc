<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Yajra\DataTables\Facades\DataTables;
use App\OrdenDespacho;
use App\Lugar_entregas;
use Illuminate\Support\Facades\View;
use PDF;
use Validator;

			
class OrdenDespachoController extends Controller
{
    //
    public function index()
    {

        // if (strtolower(auth()->user()->role->name) == 'vendedor') {
        //     return view('backend.accounting.desarme.listVendedor');
        // }
		$lugar_entregas = Lugar_entregas::all();
        return view('backend.accounting.despacho.list',compact('lugar_entregas'));
    }

public function show()
{
}
    public function get_table_data(Request $request)
    {
		 $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();	
		 
		 
	 	 $ordenes = OrdenDespacho::select('*')->whereIn('company_id', $company_id)
		 //->where('invoice_id', '6850')
		 //->whereIn('company_id', $company_id)
		 ->orderBy('created_at', 'desc')
		 ->selectSub(fn ($query) => $query
        ->selectRaw("GROUP_CONCAT(CONCAT('(',products.id, ') ', items.item_name))")
        ->from('ordenes_desarme')
        ->join('products', 'products.id', '=', 'ordenes_desarme.product_id')
		->join('items', 'items.id', '=', 'products.item_id')
		->whereColumn('ordenes_desarme.id_venta', 'ordenes_despacho.invoice_id')
		//->where('ordenes_desarme.id_venta', 'ordenes_despacho.invoice_id')
		->whereRaw("(ordenes_desarme.`estado` != 'completado' OR ordenes_desarme.`estado` is NULL)")
		->limit(1), 'items_pendientes');
		 
		return DataTables::eloquent($ordenes)
		  ->addColumn('checkbox', function ($orden) {
                return '<input type="checkbox" class="row-checkbox" data-id="' . $orden->id . '" value="' . $orden->id . '">';
            })
            ->editColumn('nro', function ($orden) {
                return $orden->id;
            })
            ->addColumn('actions', function ($orden) {
                $editarBtn = '<a href="' . action('OrdenDespachoController@edit', $orden->id) . '" data-reload="false" data-title="Editar" class="btn btn-warning btn-xs ajax-modal" title="Editar">
                    <i class="ti-pencil"></i>
                  </a>';

                $url = route('orden-despacho.entrega.pdf', $orden->id);

                $ordenEntregaBtn = '<a href="' . $url . '" target="_blank" data-title="Orden de Entrega" id="ordenEntregaBtn" class="btn btn-info btn-xs ajax-modal" title="Orden de Entrega" data-fullscreen="true">
                      <i class="ti-truck"></i>
                    </a>';



                $imprimirBtn = '<a href="' . action('OrdenDespachoController@edit', $orden->id) . '" target="_blank" class="btn btn-success btn-xs" title="Imprimir">
                      <i class="ti-printer"></i>
                    </a>';


                $f_entrega = isset($orden->f_entrega) ? \Carbon\Carbon::parse($orden->f_entrega)->format('Y-m-d') : '';


                $confirmarEntrega = '<button class="btn btn-primary btn-xs" title="Confirmar entrega"
                                data-toggle="modal"
                                data-target="#modalConfirmarEntrega"
                                data-id="' . $orden->id . '"
                                data-fecha="' . $f_entrega . '"
                                data-forma="' . $orden->forma_entrega . '"
                                data-despachado="' . $orden->despachado_por . '">
                            <i class="fa fa-check"></i>
                        </button>';
						
				 $botonqr = "<a href='" . action('ProductController@printQR', $orden->id) . "' class='btn btn-success btn-xs ajax-modal'><i class='fa fa-qrcode' aria-hidden='true'></i></a>";
				 
				/* $botondesarme = '<a href="' . action('OrdenDesarmeController@show',$orden->invoice_id ) . '" class="btn btn-warning btn-xs view-details"><i class="fa fa-info-circle" aria-hidden="true"></i></i></a>';*/
				 $botondesarme = '<a class="btn btn-warning btn-xs  view-details" href="javascript:void(0)" data-body=\'<table class="table table-bordered">
  <thead>
    <tr class="table-primary">
      <th scope="col">Productos pendientes de desarme para esta cotización</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>'.$orden->items_pendientes.'</td>
    </tr>
  </tbody>
</table>\' data-toggle="modal" data-target="#detailsModal"><i class="fa fa-info-circle" aria-hidden="true"></i></a>';

			if 	($orden->items_pendientes){
				return $imprimirBtn . ' '.$botonqr. ' '.$botondesarme;
			}else{
                return $editarBtn . ' ' . $ordenEntregaBtn . ' ' . $imprimirBtn . ' ' . $confirmarEntrega.' '.$botonqr;
				}
            })
			 ->addColumn('fecha_venta', function ($orden) {
                if (!isset($orden->cotizacion->invoice_date)) {
                    return '';
                }

                $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->cotizacion->invoice_date) ? date($date_format, strtotime($orden->cotizacion->invoice_date)) : null;
            })
			 ->addColumn('cotizacion', function ($orden) {

                $in = 'VEN-';
                if (!isset($orden->cotizacion)) {
                    return '';
                }
                if ($orden->cotizacion->company_id == 1) {
                    $in .= 'PM-';
                } elseif ($orden->cotizacion->company_id == 2) {
                    $in .= 'PC-';
                }
                $text = $in . ($orden->cotizacion->invoice_number ?? '');
                $ruta = action('InvoiceController@show', $orden->cotizacion->id);
                $a = "<a href='$ruta' target='_blank' rel='noopener noreferrer'>$text</a>";
                return $a;
            })
			
			 /*if (!in_array($item->product->nro_interno, $ingresado)) {
                            array_push($ingresado, $item->product->nro_interno);
                            $html .= nroInternoAlias($item->product->company_id, $item->product->tipo_vehiculo, $item->product->nro_interno) . '<br>';*/
			
			/*->addColumn('interno', function ($orden) {
				 dd($orden->itemInvoice->product);
				   return  nroInternoAlias(($orden->itemInvoice), ($orden->itemInvoice->product->vehiculo->tipo_vehiculo ?? ''), $orden->itemInvoice->product->nro_interno ?? ''); 
				   //dd($orden->cotizacion->invoice_items);
				  // return $orden->itemInvoice->product->vehiculo->tipo_vehiculo;
            })*/
			->addColumn('interno', function ($orden) {
				   return  nroInternoAlias(($orden->itemInvoice->product->company_id ?? ''), ($orden->itemInvoice->product->vehiculo->tipo_vehiculo ?? ''), $orden->itemInvoice->product->nro_interno ?? ''); 
				   //nroInternoAlias($item->product->company_id, $item->product->tipo_vehiculo, $item->product->nro_interno--$orden->itemInvoice->item->product->nro_interno
				   //return ;
            })
			 ->addColumn('ubicacion', function ($orden) {
                return $orden->itemInvoice->product->ubicacion ?? '';
            })
			    ->addColumn('marca', function ($orden) {
                return $orden->itemInvoice->product->marcaModelo->marca->marca ?? '';
            })
            ->addColumn('modelo', function ($orden) {
                return $orden->itemInvoice->product->marcaModelo->modelo->modelo ?? '';
            })
            ->addColumn('pieza', function ($orden) {
				// $in = ($orden->itemInvoice->product->company_id == 1) ? 'PM-' : 'PC-';
				 //$html="";
                 //$html .= $in . $orden->itemInvoice->product->product_id . (($html != "") ? ',' : '') . ($orden->itemInvoice->item->item_name ?? ''). '<br>';
				//return "({($orden->itemInvoice->product->id ?? '')}) ". ($orden->itemInvoice->item->item_name ?? '');
				
				$producto_completo=$orden->itemInvoice->item->item_name ?? '';
				$producto_id=$orden->itemInvoice->product->id ?? ''; ($orden->itemInvoice->item->item_name ?? '');
				return ($producto_completo) ? "($producto_id) $producto_completo":"";
            })
            ->addColumn('cliente', function ($orden) {
                $nombre = $orden->cotizacion->client->contact_name ?? '';

                if ($nombre == '') {
                    return '<span class="text-muted">Sin datos de cliente</span>';
                }

                $email     = $orden->cotizacion->client->contact_email ?? '';
                $telefono  = $orden->cotizacion->client->contact_phone ?? '';
                $direccion = $orden->cotizacion->client->address ?? '';
                $cuit_dni  = $orden->cotizacion->client->dni_cuit ?? '';

               
                $tabla_cliente = '<table class="table table-striped" style="min-width: 320px; margin: 0;">
                  <thead>
                     <tr>
                       <th colspan="2">
                            <h5>General Information</h5>
                       </th>
                     </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>Nombre</td>
                      <td><b>'.$nombre.'</b></td>
                    </tr>
                    <tr>
                      <td>Email</td>
                      <td><b>'.$email.'</b></td>
                    </tr>   
                    <tr>
                      <td>Teléfono</td>
                      <td><b>'.$telefono.'</b></td>
                    </tr>   
                    <tr>
                      <td>Dirección</td>
                      <td><b>'.$direccion.'</b></td>
                    </tr>   
                    <tr>
                      <td>CUIT - DNI</td>
                      <td><b>'.$cuit_dni.'</b></td>
                    </tr>   
                  </tbody>
                </table>';

                return $tabla_cliente;
            })
			
			 ->addColumn('vendedor', function ($orden) {
                return $orden->cotizacion->vendedor->name ?? '';
            })
            ->addColumn('estado_cotizacion', function ($orden) {
                return $orden->cotizacion->status ?? '';
            })
            ->addColumn('fecha_desarme', function ($orden) {
				if (!isset($orden->created_at)) {
                    return '';
                }

                $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->created_at) ? date($date_format, strtotime($orden->created_at)) : null;
				
                //return $orden->created_at ? \Carbon\Carbon::parse($orden->created_at)->format('d-m-Y') : '';
            })
			 ->addColumn('estado_pieza', function ($orden) {
                return $orden->itemInvoice->product->estado ?? '';
            })
            ->addColumn('envio_otro_deposito', function ($orden) {
              //  return $orden->f_otro_deposito ? \Carbon\Carbon::parse($orden->f_otro_deposito)->format('d-m-Y') : '';
			   $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->f_otro_deposito) ? date($date_format, strtotime($orden->f_otro_deposito)) : '';
            })
            ->addColumn('envio_deposito', function ($orden) {
				   $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->f_deposito) ? date($date_format, strtotime($orden->f_deposito)) : '';
                ///return $orden->f_deposito ? \Carbon\Carbon::parse($orden->f_deposito)->format('d-m-Y') : '';
			})	
            /*->addColumn('embalado_el', function ($orden) {
				   $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->f_embalado) ? date($date_format, strtotime($orden->f_embalado)) : '';
               // return $orden->f_embalado ? \Carbon\Carbon::parse($orden->f_embalado)->format('d-m-Y') : '';
            })
            ->addColumn('lugar_embalado', function ($orden) {
                return $orden->lugar_embalado ?? '';
            })*/
            ->addColumn('fecha_entrega', function ($orden) {
				
                //return $orden->f_entrega ? \Carbon\Carbon::parse($orden->f_entrega)->format('d-m-Y') : '';
				$date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->f_entrega) ? date($date_format, strtotime($orden->f_entrega)) : '';
            })
            ->addColumn('forma_entrega', function ($orden) {
                return $orden->forma_entrega ?? '';
            })
            ->addColumn('despachado_por', function ($orden) {
                return $orden->despachado_por ?? '';
            })
            ->addColumn('observaciones', function ($orden) {
                return $orden->observaciones ?? '';
            })
            ->addColumn('acciones_cotizacion', function ($orden) {
                return $orden->cotizacion->acciones ?? 'Sin información';
            })
            ->addColumn('guia', function ($orden) {
                if (!empty($orden->foto_guia) && file_exists(public_path('uploads/ordenes/' . $orden->foto_guia))) {
					$url = buscarImagen('uploads/ordenes/' . $orden->foto_guia);
                    //$url = asset('public/uploads/ordenes/' . $orden->foto_guia);
					//<img src="{{ public_path('images/modern-invoice-bg.jpg') }}" class="wp-300">
					$botonurl = '<a class="btn btn-sm btn-primary  view-details" href="javascript:void(0)" data-body=\'<table class="table table-bordered">
  <thead>
    <tr class="table-primary">
      <th scope="col">Foto Guia</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td><img src="'.$url.'" class="wp-400"></td>
    </tr>
  </tbody>
</table>\' data-toggle="modal" data-target="#detailsModal">Ver Guía</a>';
					
					
					
					
                    //return '<a href="' . $url . '" target="_blank" class="btn btn-sm btn-primary">Ver Guía</a>';
					return $botonurl;
                } else {
                    return '<span class="text-muted"></span>';
                }
            })
			->addColumn('extra_info', function ($orden) {
            // Add the extra data to the main object
				return $orden->items_pendientes ?? ''; 
			})
			->addColumn('deposito', function ($orden) {
            // Add the extra data to the main object
					//dd($orden->itemInvoice->product->vehiculo->deposito);
				return $orden->itemInvoice->product->vehiculo->lugar_entrega->nombre ?? ''; 
			})
			->filterColumn('deposito', function ($query, $keyword) {
				 if ($keyword != "") {
				$query->whereHas('itemInvoice.product.vehiculo.lugar_entrega', function ($q) use ($keyword) {
					
                    $ids = explode(",", $keyword);
                    if (in_array("-1", $ids)) {
                        $q->where('idLugar_entrega', '=', "")
                            ->orWhereNull('idLugar_entrega');
                    } else {
                        $q->wherein('idLugar_entrega', $ids);
                    }
					
					
					});
				 }	
            })
			->filterColumn('nro', function ($query, $keyword) {
                   $query->where('ordenes_despacho.id', 'LIKE', '%' . $keyword . '%');

            })
			->filterColumn('fecha_venta', function ($query, $keyword) {
                //$keyword = preg_replace('/[^\d\-]/', '', $keyword);
                $query->whereHas('cotizacion', function ($q) use ($keyword) {
                    $q->whereDate('invoice_date', $keyword);
                });
            })
			->filterColumn('cotizacion', function ($query, $keyword) {
                $query->whereHas('cotizacion', function ($q) use ($keyword) {
                    $q->where(function ($q2) use ($keyword) {
                        // Filtrar por invoice_number si el usuario busca el número directamente
                        $q2->where('invoice_number', 'LIKE', "%{$keyword}%");
                    });
                });
            })
			->filterColumn('interno', function ($query, $keyword) {
                $query->whereHas('itemInvoice.product', function ($q) use ($keyword) {
                    $q->where('nro_interno', 'LIKE', "%{$keyword}%");
                });
            })->filterColumn('ubicacion', function ($query, $keyword) {
                $query->whereHas('itemInvoice.product', function ($q) use ($keyword) {
                    $q->where('ubicacion', 'LIKE', "%{$keyword}%");
                });
            })
			->filterColumn('marca', function ($query, $keyword) {
                $query->whereHas('itemInvoice', function ($q) use ($keyword) {
                    $q->whereHas('product', function ($q) use ($keyword) {
                        $q->whereHas('marcaModelo', function ($q) use ($keyword) {
                            $q->whereHas('marca', function ($q) use ($keyword) {
                                $q->whereRaw('LOWER(marca) LIKE ?', ['%' . strtolower($keyword) . '%']);
                            });
                        });
                    });
                });
            })
            ->filterColumn('modelo', function ($query, $keyword) {
                  $query->whereHas('itemInvoice', function ($q) use ($keyword) {
                    $q->whereHas('product', function ($q) use ($keyword) {
                        $q->whereHas('marcaModelo', function ($q) use ($keyword) {
                            $q->whereHas('modelo', function ($q) use ($keyword) {
                                $q->whereRaw('LOWER(modelo) LIKE ?', ['%' . strtolower($keyword) . '%']);
                            });
                        });
                    });
                });
            })
            ->filterColumn('pieza', function ($query, $keyword) {
                  $query->whereHas('itemInvoice', function ($q) use ($keyword) {
					   $q->whereHas('product', function ($q) use ($keyword) {
                                $q->whereRaw('products.id LIKE ?', ['%' . strtolower($keyword) . '%']);
                       });
                    
						$q->orwhereHas('item', function ($q) use ($keyword) {
							$q->whereRaw('LOWER(item_name) LIKE ?', ['%' . strtolower($keyword) . '%']);
						});
					
					
                });
            })
            ->filterColumn('cliente', function ($query, $keyword) {
                $query->whereHas('cotizacion', function ($q) use ($keyword) {
                    $q->whereHas('client', function ($q) use ($keyword) {
                        $q->whereRaw('LOWER(contact_name) LIKE ?', ['%' . strtolower($keyword) . '%']);
                    });
                });
            })
            ->filterColumn('vendedor', function ($query, $keyword) {
                $query->whereHas('cotizacion', function ($q) use ($keyword) {
                    $q->whereHas('vendedor', function ($q) use ($keyword) {
                        $q->whereRaw('LOWER(name) LIKE ?', ['%' . strtolower($keyword) . '%']);
                    });
                });
            })
            ->filterColumn('estado_cotizacion', function ($query, $keyword) {
                $query->whereHas('cotizacion', function ($q) use ($keyword) {
                    $q->whereRaw('LOWER(status) LIKE ?', ['%' . strtolower($keyword) . '%']);
                });
            })
			 ->filterColumn('fecha_desarme', function ($query, $keyword) {
                $query->whereDate('created_at', $keyword);
            })
            ->filterColumn('estado_pieza', function ($query, $keyword) {
                $query->whereHas('itemInvoice', function ($q) use ($keyword) {
                    $q->whereHas('product', function ($q) use ($keyword) {
                        $q->whereRaw('LOWER(estado) LIKE ?', ['%' . strtolower($keyword) . '%']);
                    });
                });
            })->filterColumn('envio_otro_deposito', function ($query, $keyword) {
                $query->whereDate('f_otro_deposito', $keyword);
            })
            ->filterColumn('envio_deposito', function ($query, $keyword) {
                $query->whereDate('f_deposito', $keyword);
            })
            /*->filterColumn('embalado_el', function ($query, $keyword) {
                $query->whereDate('f_embalado', $keyword);
            })
			->filterColumn('lugar_embalado', function ($query, $keyword) {
				$query->where('ordenes_despacho.lugar_embalado', 'LIKE', '%' . $keyword . '%');
            })*/
            ->filterColumn('fecha_entrega', function ($query, $keyword) {
                $query->whereDate('f_entrega', $keyword);
            })
            ->filterColumn('forma_entrega', function ($query, $keyword) {
                $query->where('forma_entrega', $keyword);
            })
            ->filterColumn('despachado_por', function ($query, $keyword) {
                $query->where('despachado_por', 'like', "%{$keyword}%");
            })
            ->filterColumn('observaciones', function ($query, $keyword) {
                $query->where('observaciones', 'like', "%{$keyword}%");
            })
			
		 ->rawColumns(['cotizacion', 'interno','checkbox', 'guia','pieza', 'actions','cliente', 'acciones_cotizacion'])
         ->make(true);
		
    }

    public function edit($id)
    {
        //
        $lugar_entregas = Lugar_entregas::all();
        $orden = OrdenDespacho::with(['cotizacion'])->find($id);
		$dropzoneFiles=null;	
		if ($orden->foto_guia!="") {
			$path = public_path('uploads/ordenes/'.$orden->foto_guia);
			if (file_exists($path)) {
					$dropzoneFiles[] = [
						'name' => $orden->foto_guia,
						'path' => asset('public/uploads/ordenes/'. $orden->foto_guia),
						'filesize' => filesize($path)
					];
			};
        }
        $data = ['o' => $orden, 'id' => $id, 'lugar_entregas' => $lugar_entregas,'dropzoneFiles'=> $dropzoneFiles];
        return view('backend.accounting.despacho.modal.edit', $data);
    }

    public function update(Request $request, $id)
    {
		 $validator = Validator::make($request->all(), [
			'images_zona' => 'nullable|file|mimes:png,jpeg,jpg,pdf,webp,gif,jfif|max:5120'
        ]);

		if ($validator->fails()) {
			if ($request->ajax()) {
				return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
			} else {
				return back()->withErrors($validator)
					->withInput();
			}
		}
		
        $orden = OrdenDespacho::findOrFail($id);

        $orden->f_otro_deposito = $request->fecha_envio_otro_dep;
        $orden->f_deposito = $request->fecha_envio_dep;
        //$orden->f_embalado = $request->embalado_el;
        //$orden->lugar_embalado = $request->lugar_embalado;
        $orden->f_entrega = $request->fecha_entrega;
        $orden->forma_entrega = $request->forma_entrega;
        $orden->despachado_por = $request->despachado_por;
        $orden->observaciones = $request->observaciones;

        // Ruta de la imagen
        $path = public_path('uploads/ordenes');
		if(!file_exists($path) && !is_dir($path)) mkdir($path, 0755, true);
		
		//$request->filled('removed_files')
		if (request("removed_files")) {
            $filePath = $path . '/' . request("removed_files");
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $orden->foto_guia = null;
        }


        // Subida de nueva imagen
        if ($request->hasFile('images_zona')) {
			$orden->foto_guia = filepondUpload('images_zona',$path);
        }

        // Guardar otros cambios como siempre
        $orden->save();


        return response()->json([
            'result' => 'success',
            'message' => 'Orden de despacho actualizada correctamente.',
            'reload' => true
        ]);
    }

    public function generarPDF($id)
    {
        $orden = OrdenDespacho::with([
            'cotizacion.client',
            'cotizacion.vendedor',
            'cotizacion',
            'itemInvoice.item',
            'itemInvoice.product.marcaModelo.marca',
            'itemInvoice.product.marcaModelo.modelo'
        ])->findOrFail($id);

        // Verifica si tiene fecha de embalado
        /*if (empty($orden->f_embalado)) {
            return redirect()->back()->with('error', 'La orden no fue embalada aún.');
       }*/
		
/*	if ($orden->cotizacion->status != "paid"){
		return redirect()->back()->with('error', 'La cotizacion no ha sido pagada.');
	}*/

        // Actualiza estado a "por entregar"
        $orden->estatus = 'listo para entrega';
        $orden->save();

        // Datos para el PDF
        $data = [
            'orden' => $orden,
            'fecha_impresion' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('backend.accounting.despacho.pdf.orden-entrega', $data);
        $pdf->setWarnings(false);
		$encodedPdf = base64_encode($pdf->output());

		echo '<iframe
			  src="data:application/pdf;base64,'.$encodedPdf.'"
			  width="100%"
			  height="600px"
			>
			  Your browser does not support iframes.
			</iframe>';
		

        //return $pdf->stream('orden_entrega_' . $orden->id . '.pdf');
    }

    public function confirmarEntrega(Request $request)
    {
        $request->validate([
            'orden_id' => 'required|exists:ordenes_despacho,id',
            'fecha_entrega' => 'required|date',
            'forma_entrega' => 'required|string',
            'despachado_por' => 'required|string',
        ]);

        $orden = OrdenDespacho::find($request->orden_id);

        // Opcional: validar si está embalada antes de entregar
        /*if (empty($orden->f_embalado)) {
            return back()->with('error', 'La orden debe estar embalada antes de confirmar la entrega.');
        }*/

        $orden->f_entrega = $request->fecha_entrega;
        $orden->forma_entrega = $request->forma_entrega;
        $orden->despachado_por = $request->despachado_por;
        $orden->estatus = 'despachado';
        $orden->save();

        return back()->with('success', 'Entrega confirmada correctamente.');
    }
	
	
	public function confirmacionesMAX(Request $request)
    {
		 $validator = Validator::make($request->all(), [
			 'orden_id_max' => 'required|exists:ordenes_despacho,id',
            'fecha_entrega_max' => 'required|date',
            'forma_entrega_max' => 'required|string',
            'despachado_por_max' => 'required|string',
        ]);

		if ($validator->fails()) {
			if ($request->ajax()) {
				return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
			} else {
				return back()->withErrors($validator)
					->withInput();
			}
		}
			$ids = explode(',', $request->input('orden_id_max'));

			OrdenDespacho::whereIn('id', $ids)->update(['f_entrega' => $request->fecha_entrega_max,'forma_entrega' => $request->forma_entrega_max,'despachado_por' => $request->despachado_por_max,'estatus' => 'despachado']);	
			
		return response()->json([
            'result' => 'success',
            'message' => 'Orden de despacho actualizada correctamente.',
            'reload' => true
        ]);	

	}
	
	
}
