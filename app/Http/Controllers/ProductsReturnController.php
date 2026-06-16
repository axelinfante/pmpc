<?php

namespace App\Http\Controllers;

use App\Invoice;
use App\InvoiceItem;
use App\Product;
use Illuminate\Http\Request;
use App\ProductReturn;
use App\SalesReturn;
use App\SalesReturnItem;
use App\Comision;
use Validator;
use DB;
use DataTables;
use Auth;
use Illuminate\Validation\Rule;

class ProductsReturnController extends Controller
{

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->middleware(function ($request, $next) {
			if (has_membership_system() == 'enabled') {
				if (!has_feature('inventory_module')) {
					if (!$request->ajax()) {
						return redirect('membership/extend')->with('message', _lang('Your Current package not support this feature. You can upgrade your package !'));
					} else {
						return response()->json(['result' => 'error', 'message' => _lang('Sorry, This feature is not available in your current subscription !')]);
					}
				}
			}

			return $next($request);
		});

		date_default_timezone_set(get_company_option('timezone', get_option('timezone', 'Asia/Dhaka')));
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{

		return view('backend.accounting.products_return.list');
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create(Request $request)
	{
		$company_id = company_id();

		//$invoices = \App\Invoice::where('company_id', $company_id)->get();
		//$invoices = \App\Invoice::all();
		
		//dd($invoices);

		$invoices = \App\Invoice::select('invoices.*')
		->join('invoice_items','invoice_items.invoice_id','=','invoices.id')
		->leftJoin('products_returns', function ($join) {
        	$join->on('products_returns.invoice_id', '=', 'invoices.id')
			 ->on('products_returns.product_id', '=', 'invoice_items.product_id') 
             ->on('products_returns.status', '!=', DB::raw("'anulada'")); 
    	})->where('invoices.related_to',  DB::raw("'contacts'"))->whereNull('products_returns.product_id')
		->groupBy('invoices.id')
		->groupBy('invoices.invoice_number')
		->get();

		//dd($invoices);
		$products = \App\Item::where("item_type", "product")->orderBy("item_name", "asc")->get();

		return view('backend.accounting.products_return.create', compact('invoices', 'products'));
	}
	
	public function store(Request $request)
	{
			$validator = Validator::make($request->all(), [
				'return_date' => 'required',
				'invoice_id' => 'required',
				'product_id' => 'required',
				'qty*' => 'required|numeric',
			], [
				'invoice_id.required' => _lang('Usted deberia indicar el numero o factura de venta'),
				'product_id.required' => _lang('Usted debería indicar el producto de devolución'),
				'qty.required' => _lang('Usted debería indicar la cantidad a devolver'),
			]);
		

		if ($validator->fails()) {
			if ($request->ajax()) {
				return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
			} else {
				return redirect()->back()
					->withErrors($validator)
					->withInput();
			}
		}

		
			/*$factura_item = InvoiceItem::whereIn('id', $request->input('product_id'))->first();
			if (!$factura_item) {
				return redirect()->back()->with('error', _lang('La venta no es válida'));
			}

			$cantidad_devoluciones = ProductReturn::where('invoice_id', $request->input('invoice_id'))
				->whereIn('product_id', $request->input('product_id'))
				->where('status', '!=', 'anulada')->sum('quantity');

			$resultado = $cantidad_devoluciones ?? 0;

			if ($resultado >= $factura_item->quantity) { // ya ha sido devueltas todas.

				return redirect()->back()->with('error', _lang('todas las piezas ya se encuentran en devolucion'));
			}*/

			$product_id = $request->input('product_id');
			$invoice_id = $request->input('invoice_id');
			$observacion=$request->input('note');
			$ids=$request->product_id;
			$estatus="Item Devuelto";
	
		DB::beginTransaction();
		$company_id = company_id();
		$invoice = Invoice::where("id", $invoice_id)->first();	
		if ($invoice) {
						$salesReturn = new SalesReturn();
						$salesReturn->return_date = date('Y-m-d');;
						$salesReturn->customer_id = $invoice->client_id;
						$salesReturn->invoice_id = $invoice->id;
						$salesReturn->tax_amount = 0;
						$salesReturn->product_total = 0;
						$salesReturn->grand_total = 0; 
						$salesReturn->converted_total = 0;
						$salesReturn->note = $observacion;
						$salesReturn->company_id = $invoice->company_id;
						$salesReturn->save();
						
						$grand_total= $invoice->grand_total ?? 0;
						$grand_total_dev=0;
						
						//$invoiceItems = InvoiceItem::where('invoice_id',$invoice->id)->whereIn('product_id',$ids)->get();
						$invoiceItems = InvoiceItem::whereIn('id',$ids)->get();
						foreach ($invoiceItems as $p_item) {
									$grand_total_dev+=$p_item->sub_total;
									$salesReturnItem = new SalesReturnItem();
									$salesReturnItem->sales_return_id = $salesReturn->id;
									$salesReturnItem->product_id = $p_item->product_id;
									$salesReturnItem->description = $p_item->description;
									$salesReturnItem->quantity =  $p_item->quantity;
									$salesReturnItem->unit_cost =  $p_item->unit_cost;
									$salesReturnItem->discount =  $p_item->discount;
									$salesReturnItem->tax_amount =  $p_item->product_tax;
									$salesReturnItem->sub_total =  $p_item->sub_total;
									$salesReturnItem->company_id = $p_item->company_id;
									$salesReturnItem->save();
									$estatus_item='pendiente';
									//aumenta stock
									/*if ($estatus=="Item inventario"){
										Product::where('id', $p_item->product_id)->update(['stock' => 1]);
									}else{*/
										Product::where('id', $p_item->product_id)->update(['stock' => 0]);
										//$estatus_item='pendiente';
									//}	
									//
									$productReturn = new ProductReturn();
									$productReturn->return_date = $salesReturn->return_date;
									$productReturn->invoice_id = $salesReturn->invoice_id;
									$productReturn->product_id = $p_item->product_id;;
									$productReturn->quantity =  $p_item->quantity;
									$productReturn->note = $observacion;
									$productReturn->status = $estatus_item;
									$productReturn->company_id = $p_item->company_id;
									$productReturn->return_number =  $salesReturn->id;
									$productReturn->save();
									
									
									DB::insert("INSERT INTO anulados_comisions(invoiceitem_id,invoice_id,item_id,description,quantity,unit_cost,discount,tax_method,tax_id,tax_amount,sub_total,company_id,idCar,product_id,observaciones,estatus,monto_anulado) SELECT id,invoice_id,item_id,description,quantity,unit_cost,discount,tax_method,tax_id,tax_amount,sub_total,company_id,idCar,product_id,'{$observacion}','{$estatus}',sub_total FROM invoice_items where id={$p_item->id}");
									
									
									//eliminar comision
									//--$comision = Comision::where('id_venta', $id)->where('id_vendedor', $invoice->user_id)->delete();
						}
						// se agregan los totales
						
						$salesReturn->product_total = $grand_total_dev;
						$salesReturn->grand_total = $grand_total_dev; 
						$salesReturn->converted_total = $grand_total_dev;
						$salesReturn->save();
						// se calcula comisiones
						
						$comision = Comision::where('id_venta', $invoice->id)->where('id_vendedor', $invoice->user_id)->first();
						$total_comision=((($grand_total-$grand_total_dev) * $comision->porcentaje)/100);
						$comision->monto=$total_comision;
						$comision->save();
						
						/*if ($anular_cotizacion=='si') {
								$invoice->status = 'Canceled';	
								$invoice->note = "Anulados todos los item";
								$invoice->save();
						}*/
		}

		DB::commit();


		if (!$request->ajax()) {
			return redirect('products_returns/')->with('success', _lang('Sales Returned Sucessfully'));
		} else {
			return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Sales Returned Sucessfully'), 'data' => $productReturn]);
		}
	}
	
	
	public function edit(Request $request, $id)
    {
        $product_return = ProductReturn::where('id', $id)->first();
		$status = $request->get('status', '');
        return view('backend.accounting.products_return.modal.edit_observaciones', compact('product_return', 'id','status'));
    }	
	
	public function update(Request $request, $id)
{
	$validator = Validator::make($request->all(), [
        'note' => 'required|string'
	]);
	
	if ($validator->fails()) {
            if($request->ajax()){
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('products_returns.edit')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

    $productReturn = ProductReturn::findOrFail($id);

    $fechaActual = now()->format('d/m/Y H:i');
    $usuario = auth()->user()->name ?? 'Sistema'; 
    $nuevaEntrada = "[{$fechaActual} - {$usuario}]: " . trim($request->input('note'));

    if (!empty($productReturn->note)) {
        $productReturn->note = $productReturn->note . "\n\n" . $nuevaEntrada;
    } else {
        $productReturn->note = $nuevaEntrada;
    }
	$estatus= trim($request->input('status'));
	if ($estatus=="procesada"){
		Product::where('id', $productReturn->product_id)->update(['stock' => 1]);
	}	

    $productReturn->status = $estatus;
    $productReturn->save();
	
	return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated sucessfully'), 'data' => $productReturn]);
}

	public function process(Request $request)
	{

		DB::beginTransaction();
		try {
			$id = $request->id;

			$product_return = ProductReturn::where('id', $id)->first();

			if (!$product_return) {
				return response()->json(['result' => 'error', 'action' => 'products returns process', 'message' => _lang('Un error ha ocurrido'), 'data' => ['Devolución no encontrada']], 404);
			}
			Product::where('id', $product_return->product_id)->update(['stock' => 1]);

			$product_return->note= trim($product_return->note)." ".($request->observacion ?? '');
			$product_return->status='procesada';
			$product_return->save();
			/*ProductReturn::where('invoice_id',  $id)->where('status', 'pendiente')->update([
				'status' => 'procesada'
				]);*/
		
			DB::commit();

			return response()->json(['result' => 'success', 'action' => 'products returns process', 'message' => _lang('Updated Sucessfully'), 'data' => []], 200);
		} catch (\Exception $e) {
			DB::rollback();
			return response()->json(['result' => 'error', 'action' => 'products returns process', 'message' => _lang('Un error ha ocurrido'), 'data' => [$e->getMessage()]], 500);
		}
	}

	public function repair(Request $request)
{
    DB::beginTransaction();
    try {
        $id = $request->id;

        $product_return = ProductReturn::where('id', $id)->first();

        if (!$product_return) {
            return response()->json([
                'result' => 'error', 
                'action' => 'products returns repair', 
                'message' => _lang('Un error ha ocurrido'), 
                'data' => ['Devolución no encontrada']
            ], 404);
        }

        $product_return->note = trim($product_return->note) . " " . ($request->observacion ?? '');
        
        $product_return->status = 'reparar';
        $product_return->save();
    
        DB::commit();

        return response()->json([
            'result' => 'success', 
            'action' => 'products returns repair', 
            'message' => _lang('La devolución ha sido enviada a reparación correctamente.'), 
            'data' => []
        ], 200);

    } catch (\Exception $e) {
        DB::rollback();
        return response()->json([
            'result' => 'error', 
            'action' => 'products returns repair', 
            'message' => _lang('Un error ha ocurrido'), 
            'data' => [$e->getMessage()]
        ], 500);
    }
}


	
	public function cancel(Request $request)
	{
		DB::beginTransaction();
		try {
			$id = $request->id;

			//$product_return = ProductReturn::where('invoice_id', $id)->where('status', 'pendiente')->first();
			$product_return = ProductReturn::where('id', $id)->first();

			if (!$product_return) {
				return response()->json(['result' => 'error', 'action' => 'products returns process', 'message' => _lang('Un error ha ocurrido'), 'data' => ['Devolución no encontrada']], 404);
			}
			
			$product_return->note= trim($product_return->note)." ".($request->observacion ?? '');
			$product_return->status='descompuesto';
			$product_return->save();

			/*ProductReturn::where('invoice_id',  $id)->where('status', 'pendiente')->update([
           	 'status' => 'descompuesto'
        	]);*/

			/*foreach ($product_return as $registro) {
					$registro->status = 'anulada';
					$registro->save();
				}*/

			DB::commit();

			return response()->json(['result' => 'success', 'action' => 'products returns process', 'message' => _lang('Updated Sucessfully'), 'data' => []], 200);
		} catch (\Exception $e) {
			DB::rollback();
			return response()->json(['result' => 'error', 'action' => 'products returns process', 'message' => _lang('Un error ha ocurrido'), 'data' => [$e->getMessage()]], 500);
		}
	}

	public function get_table_data(Request $request)
{
    $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();

    	$ProductReturn = ProductReturn::select('products_returns.*')
		->with(['producto.item', 'company', 'invoice.client'])
		//->groupBy('invoice_id')
		->orderBy('created_at', 'desc'); 
	

    // Aplicar filtros condicionales si están presentes
    if ($request->has('status')) {
        $status = $request->status;
        $ProductReturn->whereIn('status', $status);
    }

    return Datatables::eloquent($ProductReturn)
	->editColumn('return_date', function ($ProductReturn) {
		return $ProductReturn->return_date 
			? \Carbon\Carbon::parse($ProductReturn->return_date)->format('d-m-Y') 
			: null;
	})
        ->editColumn('product_name', function ($ProductReturn) {
			return  "(".($ProductReturn->producto->id ?? ''). ") " .$ProductReturn->producto->item->item_name ?? null;
			/*$html="";
			$product_return_ = ProductReturn::where('invoice_id', $ProductReturn->invoice_id)
			->with(['producto.item'])->get();
                if (isset($product_return_)) {
                    foreach ($product_return_  as $pieza) {
                        $html .= "*.-".$pieza->producto->item->item_name . '<br>';
                    }
                }
 				return $html;*/
          //  return $ProductReturn->producto->item->item_name."--" ?? '';
        })
		->editColumn('internal_reference', function ($ProductReturn) {
		return $ProductReturn->producto->nro_interno ?? ''; 
	})
		->filterColumn('internal_reference', function ($query, $keyword) {
    $query->whereHas('producto', function ($subQuery) use ($keyword) {
        $subQuery->where('nro_interno', 'like', "%{$keyword}%");
    });
})
        ->editColumn('invoice_id', function ($ProductReturn) {
            return $ProductReturn->invoice->invoice_number ?? $ProductReturn->invoice_id;
        })
        ->editColumn('client', function ($ProductReturn) {
            return $ProductReturn->invoice->client->contact_name ?? '';
        })
		->editColumn('status', function ($ProductReturn) {
    $statuses = [
        'pendiente'    => '<span class="badge badge-warning">Pendientes</span>',
        'procesada'    => '<span class="badge badge-success">Procesadas</span>',
        'reparar'      => '<span class="badge badge-info">Defectuoso a reparar</span>',
        'descompuesto' => '<span class="badge badge-danger">Defectuoso a destruir</span>',
    ];

    
    return $statuses[$ProductReturn->status] ?? '<span class="badge badge-secondary">' . e($ProductReturn->status) . '</span>';
})
		->editColumn('note', function ($ProductReturn) {
	    $notaLimpia = str_replace('undefined', '', $ProductReturn->note);
    $notaLimpia = trim($notaLimpia);

    if (empty($notaLimpia)) {
        return '';
       // return '<span class="text-dark small"><i>' . _lang('Sin observaciones') . '</i></span>';
    }

    $lineas = preg_split('/\R+/', $notaLimpia);
    $htmlResult = '<div class="d-flex flex-column" style="gap: 12px; font-size: 0.85rem; max-width: 350px; text-align: left; color: #2c3e50;">';

    foreach ($lineas as $linea) {
        $linea = trim($linea);
        if (empty($linea)) continue;
        if (preg_match('/^(\[.*?\]):(.*)$/', $linea, $matches)) {
            $cabecera = htmlentities(trim($matches[1]), ENT_QUOTES, 'UTF-8');
            $cuerpo = htmlentities(trim($matches[2]), ENT_QUOTES, 'UTF-8');

            $htmlResult .= '<div class="pb-2" style="border-bottom: 1px dashed #ced4da;">
                                <span class="font-weight-bold d-block mb-1" style="font-size: 0.78rem; color: #0056b3;">
                                    <i class="far fa-clock mr-1"></i> ' . $cabecera . '
                                </span>
                                <span class="d-block" style="line-height: 1.4; color: #1a252f; font-weight: 400;">' . $cuerpo . '</span>
                            </div>';
        } else {
            $textoPlano = htmlentities($linea, ENT_QUOTES, 'UTF-8');
            $htmlResult .= '<div class="pb-2" style="border-bottom: 1px dashed #ced4da;">
                                <span class="d-block" style="line-height: 1.4; color: #1a252f; font-weight: 400;">' . $textoPlano . '</span>
                            </div>';
        }
    }

    $htmlResult .= '</div>';

    return $htmlResult;
        })
        ->filterColumn('client', function ($query, $keyword) {
            $query->whereHas('invoice.client', function ($subQuery) use ($keyword) {
                $subQuery->where('contact_name', 'like', "%{$keyword}%");
            });
        })
        ->filterColumn('product_name', function ($query, $keyword) {
            /*$query->whereHas('producto.item', function ($subQuery) use ($keyword) {
                $subQuery->where('item_name', 'like', "%{$keyword}%");
            });*/
				$query->orwhereHas('producto', function ($str) use ($keyword) {
                         $str->where('products.id', 'like', "%{$keyword}%");
						$str->orwhereHas('item', function ($str) use ($keyword) {
							$str->where('items.item_name', 'like', "%{$keyword}%");
						});
				});
        })
        ->filterColumn('invoice_id', function ($query, $keyword) {
			$query->where(function ($query) use ($keyword) {
				$query->whereHas('invoice', function ($subQuery) use ($keyword) {
					$subQuery->where('invoice_number', 'like', "%{$keyword}%");
				})
				->orWhere('invoice_id', 'like', "%{$keyword}%");
			});
		})
		->filterColumn('return_date', function ($query, $keyword) {
					$date_range = ($keyword != '') ? explode(" - ", $keyword) : array();
                    if (count($date_range) == 2) {
                        $query->whereDate('return_date', '>=', $date_range[0])
                            ->whereDate('return_date', '<=', $date_range[1]);
                    }
			//$query->whereRaw("DATE_FORMAT(return_date, '%d-%m-%Y') like ?", ["%{$keyword}%"]);
		})
        ->editColumn('action', function ($ProductReturn) {
            //if (strtolower(auth()->user()->role->name) !== 'despacho' && $ProductReturn->status === 'pendiente') {
				
				 /*<a class="dropdown-item procesar-devolucion" href="#" data-id="' . $ProductReturn->id . '">
                                    <i class="far fa-check-circle"></i> ' . _lang('Devolver a stock') . '
                                </a>
								
								
								<a class="dropdown-item reparar-devolucion" href="#" data-id="' . $ProductReturn->id . '">
                                    <i class="fas fa-tools"></i> ' . _lang('Defectuoso a reparar') . '
                                </a>
								
								
								<a class="dropdown-item anular-devolucion" href="#" data-id="' . $ProductReturn->id . '">
                                    <i class="fas fa-trash-alt"></i> ' . _lang('Defectuoso a destruir') . '
                                </a>*/
				
            if (strtolower(auth()->user()->role->name) !== 'despacho' &&  (in_array($ProductReturn->status, array('pendiente','descompuesto')))) {
				
                return '<div class="dropdown">
                            <button class="btn btn-xs dropdown-toggle" type="button" data-toggle="dropdown" 
                        		style="background-color: white; color: black; border: 1px solid #ced4da; font-family: \'Poppins\', sans-serif;">
                        		' . _lang('Actions') . ' <i class="fa fa-angle-down"></i>
                    		</button>
                            <div class="dropdown-menu" style = "z-index: 10000; position: relative;">
								<a data-reload="false" href="' . action('ProductsReturnController@edit', $ProductReturn->id) . '?status=' . urlencode($ProductReturn->status) . '" class="dropdown-item ajax-modal"><i class="far fa-handshake"></i> ' . _lang('Observaciones Piezas') . '</a>
								
								<a data-reload="false" href="' . action('ProductsReturnController@edit', $ProductReturn->id) . '?status=procesada" class="dropdown-item ajax-modal"><i class="far fa-check-circle"></i> ' . _lang('Devolver a stock') . '</a>
								
								<a data-reload="false" href="' . action('ProductsReturnController@edit', $ProductReturn->id) . '?status=reparar" class="dropdown-item ajax-modal"><i class="fas fa-tools"></i> ' . _lang('Defectuoso a reparar') . '</a>
								
								<a data-reload="false" href="' . action('ProductsReturnController@edit', $ProductReturn->id) . '?status=descompuesto" class="dropdown-item ajax-modal"><i class="fas fa-trash-alt"></i> ' . _lang('Defectuoso a destruir') . '</a>
                               
                            </div>
                        </div>';
            }
            return '';
        })
		 ->rawColumns(['action','product_name','status','note', 'internal_reference'])
        ->make(true);
}

}
