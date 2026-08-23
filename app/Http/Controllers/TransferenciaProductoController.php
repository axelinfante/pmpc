<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Lugar_entregas;
use App\Transfer;
use App\TransfersProduct;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\Product;
use Yajra\DataTables\Facades\DataTables;
use PDF;

/*use App\Models\Inventario;


use App\Models\Almacen;
use App\Models\Producto;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\QueryException;
use App\DataTables\TransfersDataTable;*/

class TransferenciaProductoController extends Controller
{
    public function index(Request $request)
    {
		 if (request()->ajax()) {

            //$datos = Transfer::select('*')->withCount('TransfersProduct');
			
			
			$datos = Transfer::select('*')->withCount([
				'TransfersProduct as pendientes_count' => function ($query) {
				$query->whereNull('recibido');
				},
				'TransfersProduct as recibido_count' => function ($query) {
					$query->where('recibido', true);
				}
			]);
			
            return DataTables::eloquent($datos)
				 ->addColumn('transfers_product_count', function ($data) {
						$total = $data->pendientes_count + $data->recibido_count;
				return "<strong>Pendientes:</strong> {$data->pendientes_count}<br>" .
						"<strong>Recibidos:</strong> {$data->recibido_count}<br>" .
							"<strong>Total:</strong> {$total}";
                })
				->addColumn('action', function ($data) {
					return view('backend.accounting.traslado_mercancia.partials.actions', compact('data'));
				})
                /*->addColumn('action', function ($data) {
                    $result = "<a href='" . action('transfers@show', $data->id) . "' class='btn btn-primary btn-xs ajax-modal'><i class='ti-eye'></i></a>";
                    $result .= csrf_field();
                    return $result;
                })*/
				->rawColumns(['transfers_product_count', 'action']) 
				->tojson();
        }
		
		
		return view('backend.accounting.traslado_mercancia.index');
    }
	
	 public function create(Request $request) {
           
		    $lugar_entregas = Lugar_entregas::all();
			$almacen_origen_id=$request->almacen_origen_id ?? 0;
			$almacen_destino_id=$request->almacen_destino_id ?? 0;
			
			return view('backend.accounting.traslado_mercancia.create',compact('lugar_entregas'))->with(['almacen_origen_id' => $almacen_origen_id, 'almacen_destino_id' => $almacen_destino_id]);
    }
	
	public function store(Request $request)
    {
        $request->validate([
			'reference'   => 'required|string|max:255',
			'fecha_traslado' => 'required|date',
			'detalles' => 'nullable',
			'almacen_origen_id' => 'required|exists:lugar_entregas,id',
			'almacen_destino_id' => 'required|exists:lugar_entregas,id',
			'product_datos' => 'required|array',
			'product_ids' => ['required',function ($attribute, $value, $fail) use ($request) {
								if ($request->almacen_origen_id === $request->almacen_destino_id) {
									$fail('Almacen de origen y destino no pueden ser el mismo.');
									return;
								}
						}
					]
        ]);
		
		
		
			DB::transaction(function () use ($request) {
				
			$data = array_merge($request->except(['product_datos', 'product_ids']), [
				'user_id' => auth()->user()->id ?? 1,
				'status' => 'en transito'
			]);
			 
			//$transfer = new Transfer($request->except($data));
			$transfer = new Transfer($data);
			$transfer->save();
			
			$products = $request->input('product_ids');
            $itemsData = [];
            $timestamp = now();
           /* foreach ($request->product_ids as $key => $id) {
                TransfersProduct::create([
                    'transfers_id' => $transfer->id,
                    'product_id'    => $id,
                    'cantidad'      => 1
                ]);
            }*/
			
			foreach ($request->product_ids as $key => $id) {
				$itemsData[] = [
                        'transfers_id' => $transfer->id,
                        'product_id'  => $id,
                        'created_at'  => $timestamp,
                        'updated_at'  => $timestamp
                    ];
            }
          
		  TransfersProduct::insert($itemsData); 
		  
		  Product::whereIn('id',$products)
		     ->update(['estado' => 'en transito']);
               //   ->update(['almacenes_id' => $request->almacen_destino_id]);
			
	
	/*			Inventario::whereIn('id',$products)
                  ->update(['almacenes_id' => $request->almacen_destino_id]);
            */
			});
			//toast('Proceso realizado correctamente!', 'success');
			return redirect()->back()->with('success', '¡Proceso realizado correctamente!');
			//return redirect()->route('transfers.index');
    }
	
	public function show(Transfer $transfer) {
        return view('backend.accounting.traslado_mercancia.show', compact('transfer'));
    }
	
	 public function edit(Request $request, $id)
    {
		
		$transfer = Transfer::with('TransfersProduct.inventario')->findOrFail($id);

		$almacenes = DB::table('lugar_entregas')
			->select('id', 'nombre') 
			->get();

		return view('backend.accounting.traslado_mercancia.edit', compact('transfer', 'almacenes'));
    }
	
	 public function update(Request $request, $id)
	{
		
		$request->validate([
			'fecha_recibido' => 'required|date',
			'detalles' => 'nullable|string',
			'product_datos' => 'required|array',
			'product_ids' =>  'required|array'
        ]);
		

		try {
				return DB::transaction(function () use ($request, $id) {
				$transfer = Transfer::findOrFail($id);
				
				$fechaActual = now()->format('d/m/Y H:i');
				$usuario = auth()->user()->name ?? 'Sistema'; 
				$nuevaEntrada = "[{$fechaActual} - {$usuario}]: " . trim($request->input('detalles'));
				
				 if (!empty($transfer->detalles)) {
					$transfer->detalles = $transfer->detalles . "\n\n" . $nuevaEntrada;
				} else {
					$transfer->detalles = $nuevaEntrada;
				}
				
				
				$products = $request->input('product_ids');
				
								
				Product::whereIn('id',$products)
				->update([
					'idDeposito' => $transfer->almacen_destino_id,
					'estado' => ''
				]);
				
				$transfer->TransfersProduct()->whereIn('product_id', $products)->update([
						'recibido' => true,
						'fecha_recibido' => $request->date('fecha_recibido')->format('Y-m-d'),
						'updated_at' => now()
				]);
				
				$transfer->save();
				
				$itemsFaltantes = TransfersProduct::where('transfers_id', $transfer->id)
				->whereNull('recibido')
				->pluck('product_id')
				->toArray();


				$estadoFinal = empty($itemsFaltantes) ? 'entregado' : 'en transito';
				
				 $transfer->update([
                    'status' => $estadoFinal
                ]);
				// Mover todos los productos al nuevo depósito en una sola query
				/*DB::table('products')->whereIn('id', $productIds)->update([
					'idDeposito' => $transfer->almacen_destino_id,
					'fecha_ultimogiro' => Carbon::now()->format('Y-m-d')
				]);*/

			

				return redirect()->back()->with('success', '¡Proceso realizado correctamente!');
			});
		} catch (\Exception $e) {
			return redirect()->back()->with('error', $e->getMessage());
		}
	}	
	
	
	
	 public function verificarYFinalizarTraslado(Request $request, $id)
    {
        $request->validate([
            // Arreglo de IDs de productos que SÍ llegaron físicamente
            'productos_recibidos'   => 'required|array',
            'productos_recibidos.*' => 'exists:products,id',
            'nota_recepcion'        => 'nullable|string|max:500'
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                // 1. Buscar la cabecera del traslado masivo
                $shipment = Shipment::findOrFail($id);

                if ($shipment->status !== 'in_transit') {
                    throw new \Exception('Este traslado ya ha sido procesado o cerrado.');
                }

                $recibidosIds = $request->productos_recibidos;

                // 2. Obtener todos los ítems que se supone venían en este traslado
                $itemsDelTraslado = ShipmentItem::where('shipment_id', $shipment->id)
                    ->pluck('product_id')
                    ->toArray();

                // 3. Identificar cuáles productos quedaron faltantes (No recibidos)
                $faltantesIds = array_diff($itemsDelTraslado, $recibidosIds);

                // --- PROCESAR PRODUCTOS VERIFICADOS (SÍ RECIBIDOS) ---
                if (!empty($recibidosIds)) {
                    // Actualizar el estado en el detalle del traslado
                    ShipmentItem::where('shipment_id', $shipment->id)
                        ->whereIn('product_id', $recibidosIds)
                        ->update(['recibido' => true]);

                    // Actualizar de forma masiva el idDeposito solo a los aprobados
                    Product::whereIn('id', $recibidosIds)->update([
                        'idDeposito'       => $shipment->destination_warehouse_id,
                        'fecha_ultimogiro' => now()->format('Y-m-d')
                    ]);
                }

                // --- PROCESAR PRODUCTOS FALTANTES (NO RECIBIDOS) ---
                if (!empty($faltantesIds)) {
                    // Se marcan como NO recibidos en el detalle para la auditoría
                    ShipmentItem::where('shipment_id', $shipment->id)
                        ->whereIn('product_id', $faltantesIds)
                        ->update(['recibido' => false]);

                    // IMPORTANTE: Al no actualizar su `idDeposito` en la tabla `products`, 
                    // los productos se mantienen automáticamente bajo la propiedad del depósito origen.
                    // Opcionalmente puedes cambiar su campo `estado` a 'En investigación / Pérdida'
                    Product::whereIn('id', $faltantesIds)->update([
                        'estado' => 'Faltante en Traslado'
                    ]);
                }

                // 4. Cerrar el traslado con un estado que indique si fue perfecto o con novedades
                $estadoFinal = empty($faltantesIds) ? 'delivered' : 'delivered_with_discrepancies';
                
                $shipment->update([
                    'status' => $estadoFinal,
                    'observation' => $shipment->observation . " | Nota Recepción: " . $request->nota_recepcion
                ]);
            });

            return response()->json([
                'status'  => 'success',
                'message' => 'Validación de mercancía procesada correctamente. Inventarios actualizados.'
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }
    }
	
	
	public function table_detalle(Request $request)
    {

        if ($request->ajax()) {
			
			 $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
						$products = Product::query()
						->select([
							'products.id',
							'products.nro_interno',
							'products.item_id', 
							'products.marca_modelo',
							'products.nro_oblea',
							'products.idDeposito',
							'products.ubicacion',
							'products.fecha_ultimogiro',
							'cars.tipo_vehiculo', 
							'cars.dominio', 
							'cars.motor_nro'
						])
						->leftJoin('cars', 'cars.id', '=', 'products.nro_interno')
						->whereNull('products.car_id') 
						->where('products.stock', '>=', 1)
						->whereIn('products.company_id', $company_id)
						->where(function ($query) {
							// Agregamos 'pendiente' y 'en transito' a la lista negra
							$query->whereNotIn('products.estado', [
									'desarme', 
									'desarme-stock', 
									'pendiente', 
									'en transito'
								])
								->orWhereNull('products.estado');
						})
						->when($request->almacen_id, function ($query, $almacenId) {
							return $query->where('products.idDeposito', $almacenId);
						});

					$products->orderBy('products.nro_interno', 'asc');
					

            return DataTables::of($products)
                ->addIndexColumn()
				  ->addColumn('selection', function ($row) {
                    return '<input name="bank_check" type="checkbox" class="fila-seleccionada" data-id="'.$row->id.'">';
                })
				->addColumn('interno', function ($data) {
                    return nroInternoAlias($data->company_id, $data->tipo_vehiculo, $data->nro_interno);
                })
                ->addColumn('productItem', function ($data) {
                    return $data->item->item_name ?? null;
                })
                ->addColumn('marca', function ($data) {
                    return ($data->marcaModelo->marca->marca ?? '');
                })
                ->addColumn('modelo', function ($data) {
                    return ($data->marcaModelo->modelo->modelo ?? '');
                })
				
				 ->addColumn('dominio', function ($data) {
                    return $data->dominio ?? '';
                })
				->addColumn('motor_nro', function ($data) {
                    return $data->motor_nro ?? '';
                })
				->addColumn('deposito', function ($data) use ($request) {
					return $data->deposito->nombre ?? '';
                })
				->addColumn('nro_oblea', function ($data) use ($request) {
					return $data->nro_oblea ?? '';
                })
                ->rawColumns(['selection'])
                ->make(true);
        }
    }
	
	 public function descargarGuiaMasiva($id)
    {
		$datos = Transfer::select('*')
		->findOrFail($id);
        $depositoOrigen = \DB::table('lugar_entregas')->where('id', $datos->almacen_origen_id)->first();
        $depositoDestino = \DB::table('lugar_entregas')->where('id', $datos->almacen_destino_id)->first();
		//return view('backend.accounting.traslado_mercancia.guia_traslado_masivo', compact('datos', 'depositoOrigen','depositoDestino'));
        $pdf = Pdf::loadView('backend.accounting.traslado_mercancia.guia_traslado_masivo', compact('datos', 'depositoOrigen','depositoDestino'));

        // Configuración de tamaño de hoja A4 vertical
        $pdf->setPaper('a4', 'portrait');

        // 4. Descargar o visualizar en el navegador
         //return $pdf->download('Guia_Traslado_' . $datos->reference . '.pdf');
        //return $pdf->stream('Guia_Traslado_' . $datos->reference . '.pdf');
		
		$pdf->setWarnings(false);
		$encodedPdf = base64_encode($pdf->output());

		echo '<iframe
			  src="data:application/pdf;base64,'.$encodedPdf.'"
			  width="100%"
			  height="600px"
			>
			  Your browser does not support iframes.
			</iframe>';
		
    }
	
}
