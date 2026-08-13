<?php

namespace App\Http\Controllers;

use App\Estado;
use App\Lugar_entregas;
use App\Cars;
use App\Categoria;
use App\Categoria_product;
use App\Company;
use App\Historial_product;
use App\Imagen;
use App\Utilities\Imagenes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Product;
use App\Item;
use App\Stock;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Validator;
use Illuminate\Validation\Rule;
use App\Imports\ProductsImport;
use App\Marca;
use Maatwebsite\Excel\Facades\Excel;
//use DataTables;
use DB;
use OwenIt\Auditing\Models\Audit;
use Yajra\DataTables\Facades\DataTables;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use App\Orden_desarme;
use App\Puesto;
use Illuminate\Support\Facades\Event;
use OwenIt\Auditing\Events\AuditCustom;
use Illuminate\Support\Facades\Cache;
use ZipArchive;
use File;

class ProductController extends Controller
{
    use Imagenes;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
		$lugar_entregas = Lugar_entregas::all();
         if (request()->ajax()) {
           
		   $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
            // Iniciamos la consulta base
            $products = Product::select('products.*', 'cars.tipo_vehiculo', 'cars.dominio','cars.motor_nro')
                ->leftJoin('cars', 'cars.id', '=', 'products.nro_interno')
                ->whereNull('car_id')
                ->where('stock', '>=', 1)
                ->where(function ($query) {
                    $query->whereNotIn('products.estado', ['desarme', 'desarme-stock'])
                          ->orWhereNull('products.estado');
                })
				//->whereNotIn('estado', ['desarme','desarme-stock'])
                ->whereIn('products.company_id', $company_id)
				
				->with([
				'category', 
				'devoluciones', // <-- Tu nueva relación añadida al modelo Product
				'item' => function ($query) {
					$query->where("item_type", "product");
					}
				]);
				
             
            //$products->orderBy('products.nro_interno', 'asc');
            if ($request->columns['3']['search']['value'] != "") {
                $products->orderBy('products.nro_interno', 'asc');
            } else {
                $products->orderBy('products.id', 'desc');
            }
			

            return DataTables::eloquent($products)
                ->filterColumn('id', function ($query, $keyword) {
                    $query->where('products.id', 'like', "%{$keyword}%");
                    //$query->where('products.id11', 'like', "%{$keyword}");
                })
                ->filterColumn('created_at', function ($query, $keyword) {
                    if ($keyword != '') {
                        $date_range = explode(' - ', $keyword);
                        if (count($date_range) == 2) {
                            $query->whereDate('products.created_at', '>=', $date_range[0])
                                ->whereDate('products.created_at', '<=', $date_range[1]);
                        } else {
                            $query->whereDate('products.created_at', '=', $keyword);
                        }
                    }
                })
                ->filterColumn('fecha_ingreso_a_stock', function ($query, $keyword) {
					if ($keyword == "todos") {
                        $query->where('products.fecha_ingreso_a_stock', '=', "")
                            ->orWhereNull('products.fecha_ingreso_a_stock');
                    } elseif ($keyword != "") {
						 $date_range = explode(' - ', $keyword);
                    if (count($date_range) == 2) {
                        $query->whereDate('products.fecha_ingreso_a_stock', '>=', $date_range[0])
                            ->whereDate('products.fecha_ingreso_a_stock', '<=', $date_range[1]);
                    } else {
                        $query->whereDate('products.fecha_ingreso_a_stock', '=', $keyword);
                    }
                    }
                })
                ->filterColumn('fecha_ultimogiro', function ($query, $keyword) {
                    if ($keyword != "") {
                        $date_range = explode(' - ', $keyword);
                        if (count($date_range) == 2) {
                            $query->whereDate('products.fecha_ultimogiro', '>=', $date_range[0])
                                ->whereDate('products.fecha_ultimogiro', '<=', $date_range[1]);
                        } else {
                            $query->whereDate('products.fecha_ultimogiro', '=', $keyword);
                        }
                    }
                })
                ->filterColumn('nro_interno', function ($query, $keyword) {
                    $query->where('products.nro_interno', 'like', "%{$keyword}");
                    //$query->orderBy('products.nro_interno', 'asc');
                })
                ->filterColumn('dominio', function ($query, $keyword) {
                    if ($keyword == "todos") {
                        $query->where('cars.dominio', '=', "")
                            ->orWhereNull('cars.dominio');
                    } elseif ($keyword != "") {
                        $query->where('cars.dominio', 'like', "%{$keyword}%");
                    }
                    //$query->where('cars.dominio', 'like', "%{$keyword}%");
                })
                ->filterColumn('productItem', function ($query, $keyword) {
                    $query->orWhereHas('item', function ($subQuery) use ($keyword) {
                        $subQuery->where('item_name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('marca', function ($query, $keyword) {
                    $query->orWhereHas('marcamodelo', function ($subQuery) use ($keyword) {
                        $subQuery->whereHas('marca', function ($str) use ($keyword) {
                            //$str->where('marca', 'like', "%{$keyword}%");

                            if ($keyword == "todos") {
                                $str->where('marca', '=', "")
                                    ->orWhereNull('marca');
                            } elseif ($keyword != "") {
                                $str->where('marca', 'like', "%{$keyword}%");
                            }
                        });
                    });
                })
                ->filterColumn('modelo', function ($query, $keyword) {
                    $query->orWhereHas('marcamodelo', function ($subQuery) use ($keyword) {
                        $subQuery->whereHas('modelo', function ($str) use ($keyword) {
                            $str->where('modelo', 'like', "%{$keyword}%");
                        });
                    });
                })
                ->filterColumn('motor_nro', function ($query, $keyword) {
                    //$query->where('products.motor', 'like', "%{$keyword}");
                    if ($keyword == "todos") {
                        $query->where('cars.motor_nro', '=', "")
                            ->orWhereNull('cars.motor_nro');
                    } elseif ($keyword != "") {
                        $query->where('cars.motor_nro', 'like', "%{$keyword}%");
                    }

            
                })
                ->filterColumn('mercado_libre', function ($query, $keyword) {
                    if ($keyword == "Si") {
                        $query->where('mercado_libre', 1);
                    } elseif ($keyword == "No") {
                        $query->where('mercado_libre', 0)->orwherenull('mercado_libre');
                    }
                })
                ->filterColumn('deposito', function ($query, $keyword) {
                    $query->orWhereHas('deposito', function ($str) use ($keyword) {

                        if ($keyword != "") {
                            $ids = explode(",", $keyword);


                            if (in_array("-1", $ids)) {
                                $str->where('idDeposito', '=', "")
                                    ->orWhereNull('idDeposito');
                            } else {
                                $str->wherein('idDeposito', $ids);
                            }
                        }

   
                    });
                })

                ->addColumn('id', function ($data) {

                   /*if ($data->company_id == 1) {
                        $in = 'PM-';
                    } else if ($data->company_id == 2) {
                        $in = 'PC-';
                    }
                    return $in . $data->id;*/
					return $data->id;
                })


                ->addColumn('created_at', function ($data) {
                    return formatDate($data->created_at);
                })
                ->addColumn('fecha_ingreso_a_stock', function ($data) {
                    return formatDate($data->fecha_ingreso_a_stock);
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
				->addColumn('reparaciones', function ($data) {
					
    if ($data->devoluciones->isEmpty()) {
        return '';
    }

    $htmlResult = '<div class="d-flex flex-column" style="gap: 12px; font-size: 0.85rem; max-width: 350px; text-align: left; color: #2c3e50;">';

    foreach ($data->devoluciones as $devolucion) {
        $notaLimpia = str_replace('undefined', '', $devolucion->note);
        $notaLimpia = trim($notaLimpia);

        if (empty($notaLimpia)) {
            continue; 
        }

        $lineas = preg_split('/\R+/', $notaLimpia);

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
                // Texto plano
                $textoPlano = htmlentities($linea, ENT_QUOTES, 'UTF-8');
                $htmlResult .= '<div class="pb-2" style="border-bottom: 1px dashed #ced4da;">
                                    <span class="d-block" style="line-height: 1.4; color: #1a252f; font-weight: 400;">' . $textoPlano . '</span>
                                </div>';
            }
        }
    }

    $htmlResult .= '</div>';

    return $htmlResult;
					
					
					
                    //return ($data->marcaModelo->modelo->modelo ?? '');
                })
                ->addColumn('dominio', function ($data) {
                    return $data->dominio ?? '';
                })
				->addColumn('motor_nro', function ($data) {
                    return $data->motor_nro ?? '';
                })
				->editColumn('deposito', function ($data) use ($request,$lugar_entregas) {
                    if (!isset($request->exportar)){
                        return view('backend.accounting.product.include.product-deposito', ['data' => $data,'lugar_entregas'=> $lugar_entregas]);
                    }
					return $data->deposito->nombre ?? '';
                })
                ->editColumn('mercado_libre', function ($data) use ($request) {
					
					if (!isset($request->exportar)){
                        return view('backend.accounting.product.include.product-mercadolibre', ['data' => $data]);
                    }
					 
                    return ($data->mercado_libre==1) ? 'Si' : 'No';
                })->addColumn('action', function ($data) {

                    //$result =  "<form id='form-delete-" . $data->id . "' action='" . action('ProductController@destroy', $data->id) . "' method='post'>";
					$result = "<form id='form-delete-" . $data->id . "' action='" . action('ProductController@destroy', $data->id) . "' method='post' class='form-delete-inline'>";
					$result .= csrf_field();
					$result .= "<input name='_method' type='hidden' value='DELETE'>";
					$result .= "<input type='hidden' name='observacion' class='input-observacion'>";
                    
					$result .= "<a href='" . action('ProductController@edit', $data->id) . "' class='btn btn-warning btn-xs " . ((!empty($data->car_id)) ? 'ajax-modal' : '') . "'><i class='ti-pencil'></i></a>";

                    $result .= "<a href='" . action('ProductController@show', $data->id) . "' class='btn btn-primary btn-xs ajax-modal'><i class='ti-eye'></i></a>";

                    $result .= "<a href='" . action('ProductController@printQR', $data->id) . "' class='btn btn-success btn-xs ajax-modal'><i class='fa fa-qrcode' aria-hidden='true'></i></a>";

                    $result .= "<a href='" . action('ProductController@printsinQR', $data->id) . "' class='btn btn-success btn-xs ajax-modal'><i class='fa fa-barcode' aria-hidden='true'></i></a>";
					
					$result .= "<button type='button' class='btn btn-danger btn-xs btn-remove-product-item' data-id='" . $data->id . "'><i class='ti-eraser'></i></button>";
						

/*                    $result .= "<input name='_method' type='hidden' value='DELETE'><button class='btn btn-danger btn-xs btn-remove-product' type='submit'><i
                    class='ti-eraser'></i></button>";*/

					$result .= '<a href="' .  route('auditoriaHistorial', $data->id) . '" 
					data-title="' . _lang('Historial de Productos') . '" data-fullscreen="true" class="btn btn-warning btn-xs ajax-modal"><i class="ti-list"></i></a>&nbsp;';
                    return $result;
                })
				->editColumn('nro_oblea', function ($data) use ($request) {
                    if (!isset($request->exportar)){
                        return view('backend.accounting.product.include.product-oblea', ['data' => $data]);
                    }
                     return $data->nro_oblea;
                })
					->setRowClass(function ($product) {
						return $product->estado === 'Defectuoso Comercializable' ? 'table-danger' : '';
				})
				->rawColumns(['mercado_libre','action','deposito','reparaciones'])
				->tojson();
        } 

        $estados = Estado::select('*')->where('Activo', "Si")->orderBy('estado', 'asc')->get();

        return view('backend.accounting.product.list', compact(['lugar_entregas', 'estados']));
    }

    
    public function historial(Request $request)
    {
        if (request()->ajax()) {
            $idProduct = $request->query('idProduct', null);

            $products = Historial_product::select('historial_products.*', 'cars.tipo_vehiculo')
                ->leftJoin('cars', 'cars.id', '=', 'historial_products.nro_interno')
                ->whereHas('item', function ($query) {
                    $query->where("item_type", "product");
                })
                ->orderBy('historial_products.id', 'desc');
            if ($idProduct) {
                $products->where('historial_products.product_id', $idProduct);
            }
            return DataTables::eloquent($products)
                ->filterColumn('created_at', function ($query, $keyword) {
                    $date_range = ($keyword != '') ? explode(" - ", $keyword) : array();
                    if (count($date_range) == 2) {
                        $query->whereBetween('historial_products.created_at', [$date_range[0], $date_range[1]]);
                    }
                })
                ->filterColumn('productItem', function ($query, $keyword) {
                    $query->whereHas('item', function ($query) use ($keyword) {
                        $query->where('item_name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('informe', function ($query, $keyword) {
                    $query->where('historial_products.informe', 'like', '%' . $keyword . '%');
                })
                ->filterColumn('id', function ($query, $keyword) {
                    $query->where('historial_products.product_id', 'like', '%' . $keyword . '%');
                })
                ->filterColumn('interno', function ($query, $keyword) {
                    $query->where('historial_products.nro_interno', 'like', '%' . $keyword . '%');
                })
                ->filterColumn('deposito', function ($query, $keyword) {
                    $query->orwhereHas('deposito', function ($str) use ($keyword) {
                        //$str->where('nombre', 'like', "%{$keyword}%");
                        if ($keyword == "todos") {
                            $str->where('nombre', '=', "")
                                ->orWhereNull('nombre');
                        } elseif ($keyword != "") {
                            $str->where('nombre', 'like', "%{$keyword}%");
                        }
                    });
                })
                ->filterColumn('ubicacion', function ($query, $keyword) {
                    $query->where('historial_products.ubicacion', 'like', '%' . $keyword . '%');
                })
                ->filterColumn('usuario', function ($query, $keyword) {
                    $query->whereHas('user', function ($str) use ($keyword) {
                        $str->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->addColumn('id', function ($data) {
                    if ($data->company_id == 1) {
                        $in = 'PM-';
                    } else if ($data->company_id == 2) {
                        $in = 'PC-';
                    }
                    return $in . $data->product_id;
                })
                ->addColumn('created_at', function ($data) {
                    return formatDate($data->created_at);
                })
                ->addColumn('interno', function ($data) {
                    return nroInternoAlias($data->company_id, $data->tipo_vehiculo, $data->nro_interno);
                })
                ->addColumn('productItem', function ($data) {
                    return $data->item->item_name ?? null;
                })
                ->addColumn('marcamodelo', function ($data) {
                    return ($data->marcaModelo->marca->marca ?? '') . ' ' .
                        ($data->marcaModelo->modelo->modelo ?? '');
                })
                ->addColumn('deposito', function ($data) {
                    return $data->deposito->nombre ?? '';
                })
                ->addColumn('ubicacion', function ($data) {
                    return $data->ubicacion ?? '';
                })
                ->addColumn('description', function ($data) {
                    return $data->description ?? '';
                })
                ->addColumn('usuario', function ($data) {
                    return $data->user->name ?? '';
                })
                ->addColumn('informe', function ($data) {
                      return strip_tags(clean($data->informe)) ?? '';
                })
                ->addColumn('action', function ($data) {
                    $result = "<a href='" . action('ProductController@historyProduct', $data->id) . "' class='btn btn-primary btn-xs ajax-modal'><i class='ti-eye'></i></a>";
                    $result .= csrf_field();
                    return $result;
                })->tojson();
        }



        return view('backend.accounting.product.historial');
    }

    public function productos_comunes(Request $request)
    {
		
		if ($request->ajax()) {
			$data = Item::where("item_type", "product")
					->orderBy("item_name", "asc");//->get();			
			 if ($request->has('filtrado')) {
						 switch ($request->get('filtrado')) {
							case 'predefinido':
								$data->where("allCar", 1);
								$data->where("activo", 'Si');
								break;
							case 'activos':
								$data->where("activo", 'Si');
								break;
							case 'inactivos':
								$data->where("activo", 'No');
								break;
						}
                }
            return Datatables::eloquent($data)
                ->addIndexColumn()
				->addColumn('action', function ($data) {
								$edit ="<a data-reload='false' href='" . action('ProductController@edit_item', $data->id) . "' data-title='" . _lang
								('Update Product') . "' class='btn btn-warning btn-xs ajax-modal'><i class='ti-pencil'></i></a>";
								$delete='<button class="btn btn-danger btn-xs button-delete" type="button"><i class="ti-eraser"></i></button>';
							return $edit.$delete;
				})
				->editColumn('allCar', function ($car) {
				
					if (!isset($request->exportar)){
                        return view('backend.accounting.item.include.predefinido', ['data' => $car]);
                    }

					return	($car->allCar == 1) ? 'Si':'No';
				})				
				->editColumn('activo', function ($row) use ($request) {
					
					if (!isset($request->exportar)){
                        return view('backend.accounting.item.include.activo', ['data' => $row]);
                    }
                    return $row->activo ?? "";
				})
				
				->rawColumns(['action','allCar','activo'])
                ->make(true);
		}	
		
        return view('backend.accounting.product.listComunes');
    }

    public function edit_item($id)
    {
        $item = Item::find($id);

        return view('backend.accounting.product.modal.edit_item', compact('item', 'id'));
    }
    public function update_item(Request $request, $id)
    {
		
		/*$validator = Validator::make($request->all(), [
          //  'item_name' => 'required|max:150|unique:items,item_name,' .$id
		  'item_name' => [
            'required',
            'item_name',
				Rule::unique('items')->where(function ($query) {
                return $query->where('activo', 'Si'); // ...solo entre los activos
            })->ignore($id),
        ],
        ]);*/
		
		$validator = Validator::make($request->all(), [
    'item_name' => [
        'required',
        'max:150', // Límite de caracteres
        Rule::unique('items', 'item_name')
            ->ignore($id)
            ->where('activo', 'Si')
    ],
]);

        if ($validator->fails()) {
            if($request->ajax()){
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('item.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }
		
        $item = Item::find($id);
        $item->item_name = $request->item_name;
        $item->allCar = $request->allcar ?? 0;
        $item->activo = $request->activo;

        if ($request->item_name) {
            $item->save();

            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated sucessfully'), 'data' => $item]);
        }
    }
    public function importXls()
    {

        //cargar archivo storage  public disk
        //$file = storage_path(). '/app/public/estados.xlsx' ;
        $file = storage_path() . '/app/public/product3.xlsx';

        $spreadsheet = IOFactory::load($file);
        $totalDeHojas = $spreadsheet->getSheetCount();

        $datos = [];
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {

            # Obtener hoja en el índice que vaya del ciclo
            $hojaActual = $spreadsheet->getSheet($indiceHoja);

            $hojaActual->getRowIterator();
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $res = $celda->getValue();
                    $resSinNumeros = preg_replace('/[0-9]+/', '', $res);
                    if (trim($resSinNumeros) != '') {
                        $datos[$resSinNumeros] = $resSinNumeros;
                    }
                }
            }


            # Imprimir
            //echo "En <strong>$coordenadas</strong> tenemos el valor <strong>$valorRaw</strong>. ";
            //            echo "Formateado es: <strong>$valorFormateado</strong>. ";
            //            echo "Calculado es: <strong>$valorCalculado</strong><br><br>";

        }
        foreach ($datos as $dat => $value):
            //$estados = new Estado();
            $estados = new item();
            //$estados-> estado = $dat;
            $estados->item_name = $dat;
            $estados->item_type = 'product';
            $estados->company_id = company_id();
            $estados->allCar = 1;
            $estados->save();
        endforeach;
        dd($estados);
    }
    public function actualizarProductAutos()
    {
		abort(404, 'Sin Configurar actualmente');
		dd();
        ini_set('max_execution_time', 0);

        $items = Item::where('allCar', 1)->get('id');
        $vehiculos = Cars::where('idEstado', '!=', 1)->get(); //where('idEstado', '!=', 5)->orwhere('idEstado', '!=', 6)->or
        $arr = [];
        $arrItem = [];
        foreach ($items as $item) {
            $arrItem[] = $item->id;
        }

        foreach ($vehiculos as $v) {
            $product = Product::where('car_id', $v->id)->get('item_id');

            $arrProduct = [];
            foreach ($product as $product) {
                $arrProduct[] = $product->item_id;
            }
            $dif = array_diff($arrItem, $arrProduct);

            if (!empty($dif)) {
                foreach ($dif as $d) {
                    // $arr[] = [
                    //     'item_id' =>$d,
                    //     'car_id' => $v->id,
                    // 'marca_modelo' => $v->idMarca_modelo,
                    //     //'product_cost' => $request->input('product_cost'),
                    //     'product_price' => 0,
                    //     //'product_unit' => $request->input('product_unit'),
                    //     'tax_method' => 'exclusive',
                    //     //'tax_id' => $request->input('tax_id'),
                    //     //'description' => $request->input('description'),
                    // 'stock' => 1,
                    //     'nro_interno' => $v->id,
                    //     'company_id' => $v->company_id,
                    //     'allCar' => 1,

                    //     'created_at' => date('Y-m-d H:i:s'),
                    //     'updated_at' => date('Y-m-d H:i:s'),
                    // ];
                    $product = new Product();
                    $product->item_id = $d;
                    $product->car_id = $v->id;
                    $product->marca_modelo = $v->idMarca_modelo;
                    //$product->product_cost = $request->input('product_cost');
                    $product->product_price = 0;
                    //$product->product_unit = $request->input('product_unit');
                    $product->tax_method = 'exclusive';
                    //$product->tax_id = $request->input('tax_id');
                    //$product->description = $request->input('description');
                    $product->stock = 1;
                    $product->nro_interno = $v->id;
                    $product->company_id = $v->company_id;
                    $product->allCar = 1;

                    $product->save();
                }
            }
            // dd($arr);
        }





        // foreach ($items as $item) {
        //     foreach($vehiculos as $v) {
        //         $product = Product::where('item_id', $item->id)->where('car_id', $v->id)->first();

        // }
        // }





        return back()->with('success', _lang('Updated Sucessfully'));
    }
    /** Excel Import**/
    public function import(Request $request)
    {
        if ($request->isMethod('get')) {
            return view('backend.accounting.product.import');
        } else {
            @ini_set('max_execution_time', 0);
            @set_time_limit(0);

            $validator = Validator::make($request->all(), [
                'file' => 'required|mimes:xlsx',
            ]);

            if ($validator->fails()) {
                if ($request->ajax()) {
                    return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
                } else {
                    return redirect('products/import')->withErrors($validator)
                        ->withInput();
                }
            }

            $new_rows = 0;

            DB::beginTransaction();

            $previous_rows = Item::where('company_id', company_id())->count();

            $import = Excel::import(new ProductsImport, request()->file('file'));

            $current_rows = Item::where('company_id', company_id())->count();

            $new_rows = $current_rows - $previous_rows;

            DB::commit();

            return back()->with('success', $new_rows . ' ' . _lang('Rows Imported Sucessfully'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $idCar = $request->get('idCar', false);
        $predefinido = $request->get('predefinido', false);
        $modalInStock = $request->get('modalInStock', false);


        $comp = Company::all();
        $cias = $comp;
        $auto = Cars::where('id', $idCar)->with('marca_modelo')->first();
        //$cars = Cars::All()->whereIn('company_id', $company_id);

        $company_id =  (auth()->user()->id==2) ? company_id_arr() : company_id();//empty(session('cia')) ? company_id_arr() : company_id_arr();
//        $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
        $nro_interno_datos = Cars::All()->whereIn('company_id', $company_id);
        $items = Item::where('activo', "Si")->orderBy('item_name', 'ASC')->get();

        $cars = Cars::All()->whereIn('company_id', $company_id);

        $interno = $this->get_last_interno();

        if ($predefinido) {
			
		/*	$predefinidos = Item::where('activo', "Si")->where('allCar', 1)->orderBy('item_name', 'ASC')->pluck('item_name','id')->toArray();
			$predefinidosfiltros = Item::where('activo', "Si")->where('allCar', 1)->orderBy('item_name', 'ASC')->pluck('item_name')->toArray();
			
			$products = Product::select('products.id','products.item_id', 'products.stock', 'products.nro_oblea','items.item_name','items.allCar','items.activo')
						->join('items', 'items.id', '=', 'products.item_id') 
						->where('products.nro_interno','1321')
						->whereIn('items.item_name', $predefinidosfiltros)
						->get();	
			//$results = array();
			foreach ($products as $row) {
				///$results[] = $row->toArray();
				if (in_array($row->item_name, $predefinidos)) {
					$clave = array_search($row->item_name, $predefinidos);
					$predefinidos[$clave] = $row->toArray();
				}
				
			}*/
            return view('backend.accounting.product.createPredefinidos', compact('nro_interno_datos'));
        }

        if ($modalInStock) {
            return view('backend.accounting.product.modal.createMo', compact('interno', 'cias', 'cars', 'items','company_id'));
        }


        if (!$request->ajax()) {
			//$items = Item::where('activo', "Si")->where('allCar', 1)->orderBy('item_name', 'ASC')->get();
            return view('backend.accounting.product.create', compact('interno', 'cias', 'nro_interno_datos', 'items'));
        } else {

            //            return Redirect::back()->withErrors(compact('auto','interno'));
            return view(
                'backend.accounting.product.modal.create',
                compact('auto')
            );
        }
    }




public function store(Request $request)
{
    $lockKey = 'create_product_lock_' . auth()->user()->id;
    $lock = Cache::lock($lockKey, 5);

    if (!$lock->get()) {
        if ($request->ajax()) {
            return response()->json([
                'result' => 'error', 
                'message' => ['El producto ya fue solicitado, por favor espere unos segundos...']
            ]);
        }
        return redirect()->back()->withErrors(['error' => 'El producto ya fue solicitado, por favor espere.']);
    }

    try {
         $validator = Validator::make($request->all(), [
            'nro_oblea' => 'nullable|unique:products',
            'item_name' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value != "") {
						  $item = Item::where('item_name', $value)->where('activo', "Si")->first();
                        if ($item) {
                            $fail('Item ya se encuentra creado.');
                            return;
                        }
                    };
                },
            ],
            'item_id' => [
                function ($attribute, $value, $fail) use ($request) {
					if (procesarSolicitud() == true) {
						$fail("</br><strong>El producto ya fue solicitado, debe esperar unos segundos.....</strong>");
						return;
					 }
					 
                    if ($request->has('item_id') && $request->has('nro_interno')) {

                        if ($request->input('nro_interno') > 0) {
                            $hasPiezaSavedForUser = Product::query()
                                ->where('item_id', $value)
                                ->where('nro_interno', $request->input('nro_interno'))
                                ->where('car_id', null)
                                ->exists();

                            if ($hasPiezaSavedForUser) {
                                $fail('Item ya se encuentra asignado al nro interno.');
                                return;
                            }
                        }
                    }
                },
            ],
            'imagen.*'          => ['mimes:jpg,jpeg,png,gif,svg']
        ]);



        if ($validator->fails()) {
            $lock->release(); 
			 if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect('products/create')
                    ->withErrors($validator)
                    ->withInput();
            }

        }

        DB::beginTransaction();
          $allCar = $request->input('car_or_stock', false);

        // $es_carga_rapida = $request->input('carga_rapida', false);

        if (!empty($request->input('item_name')) && empty($request->input('item_id'))) {
            //Create Item
            $item = new Item();
            $item->item_name = $request->input('item_name');
            $item->item_type = 'product';
            $item->company_id = $request->input('company') ?? company_id();
            $item->activo = 'Si';

            if ($allCar == 1) {
                $item->allCar = 1;
            }
            $item->save();
        } else if ($request->input('item_id')) {
            $item = Item::find($request->input('item_id'));
        }



        //Create pieza 
        if ($allCar != 1) {

			//$car_id=$request->input('nro_interno') ?? 0;
			$nro_interno= $request->input('nro_interno',null);
            $car_id = $request->input('car_id', $nro_interno);
			
			//$car = Cars::find($nro_interno);


            $product = new Product();
            $product->item_id = $item->id;
            $product->car_id =  null;
            //$product->car_id = $car_id ?? null;
            $product->marca_modelo = $request->input('marca_modelo');
            //$product->product_cost = $request->input('product_cost');
            $product->product_price = 0;
            $product->nro_motor = $request->input('nro_motor') ?? null;
            $product->nro_oblea = $request->input('nro_oblea') ?? null;
            //$product->product_unit = $request->input('product_unit');
            $product->tax_method = 'exclusive';
            //$product->tax_id = $request->input('tax_id');
            $product->description = $request->input('description');
            $product->stock = 1;
            $product->anio = $request->input('anio');

            $product->estado = $request->input('estado_prod') ?? "desarme";

			//$car_id=$request->input('nro_interno') ?? 0;
            $car = Cars::find($car_id);

            if (isset($car)) {
                $product->nro_interno = $car_id ?? null;
                $product->company_id = $car->company_id ?? company_id();
                $product->marca_modelo = $car->idMarca_modelo ?? null;

            } else {
                $product->nro_interno = $request->input('nro_interno') ?? 0;
                $product->company_id = $request->input('company') ?? company_id();
				$product->marca_modelo = $request->input('marca_modelo');
            }




            $product->estado = $request->input('estado_prod') ?? null;

            $product->idDeposito = $request->input('idDeposito') ?? null;
            $product->ubicacion = $request->input('ubicacion') ?? null;

            $product->mercado_libre = $request->input('mercado_libre') ?? 0;

            $product->carga_rapida = $request->input('carga_rapida') ?? 0;

            $product->user_id = auth()->user()->id;

            if ($product->ubicacion != ""  && (is_null($product->fecha_ingreso_a_stock))) {
                $product->fecha_ingreso_a_stock = date('Y-m-d H:i:s');
            };
			
			
			if ($item->id == "1612"  || strtoupper($item->item_name)=="MOTOR SEMIARMADO") {
                $product->nro_motor = $car->motor_nro ?? '';
            };
			

            $product->save();
            if (!empty($request->file())) {
				$path = public_path('uploads/products');
				if(!file_exists($path) && !is_dir($path)) mkdir($path, 0755, true);
                $this->uploadImg($request, ['dir' => 'products', 'idProduct' => $product->id]);
            }
			
			
        } else {
        }

        $cate = $request->input('categoria');

        if (isset($product) && !empty($cate[0])) {
            foreach ($cate as $ca):
                $cateProd = new Categoria_product;
                $cateProd->product_id = $product->id;
                $cateProd->categoria_id = $ca;
                $cateProd->save();

            endforeach;
        }

	    $request['informe'] = "Creacion de producto " . json_encode($product);
		$this->grabarHistorial($request, $product);
		DB::commit();
        $lock->release();
        if (!$request->ajax()) {
            return redirect()->back()->with(['success' => _lang('Saved successfully'), 'product' => $product])->withInput();
        } else {
            $product->{"products.id"} = $product->id;
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved successfully'), 'data' => $product]);
        }
		//$table->unique(['item_id', 'nro_interno']);

    } catch (\Exception $e) {
        DB::rollBack();
        $lock->release(); 
        throw $e;
    }
}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store_old(Request $request)
    {
		//dd(procesarSolicitud());
        $validator = Validator::make($request->all(), [
            'nro_oblea' => 'nullable|unique:products',
            'item_name' => [
                'nullable',
                function ($attribute, $value, $fail) use ($request) {
                    if ($value != "") {
//                        $item = Item::find($value);
						  $item = Item::where('item_name', $value)->where('activo', "Si")->first();
                        if ($item) {
                            $fail('Item ya se encuentra creado.');
                            return;
                        }
                    };
                },
            ],
            'item_id' => [
                function ($attribute, $value, $fail) use ($request) {
					if (procesarSolicitud() == true) {
						$fail("</br><strong>El producto ya fue solicitado, debe esperar unos segundos.....</strong>");
						return;
					 }
					 
                    if ($request->has('item_id') && $request->has('nro_interno')) {

                        if ($request->input('nro_interno') > 0) {
                            $hasPiezaSavedForUser = Product::query()
                                ->where('item_id', $value)
                                ->where('nro_interno', $request->input('nro_interno'))
                                ->where('car_id', null)
                                ->exists();

                            if ($hasPiezaSavedForUser) {
                                $fail('Item ya se encuentra asignado al nro interno.');
                                return;
                            }
                        }
                    }
                },
            ],
            //  'item_name' => 'required|unique:items',
            //'product_cost' => 'required|numeric',
            //'product_price' => 'required|numeric',
            //'product_unit' => 'required',
            'imagen.*'          => ['mimes:jpg,jpeg,png,gif,svg']
        ]);


        //dd($request->input());

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect('products/create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }
			
        DB::beginTransaction();
        $allCar = $request->input('car_or_stock', false);

        // $es_carga_rapida = $request->input('carga_rapida', false);

        if (!empty($request->input('item_name')) && empty($request->input('item_id'))) {
            //Create Item
            $item = new Item();
            $item->item_name = $request->input('item_name');
            $item->item_type = 'product';
            $item->company_id = $request->input('company') ?? company_id();
            $item->activo = 'Si';

            if ($allCar == 1) {
                $item->allCar = 1;
            }
            $item->save();
        } else if ($request->input('item_id')) {
            $item = Item::find($request->input('item_id'));
        }



        //Create pieza 
        if ($allCar != 1) {

			//$car_id=$request->input('nro_interno') ?? 0;
			$nro_interno= $request->input('nro_interno',null);
            $car_id = $request->input('car_id', $nro_interno);
			
			//$car = Cars::find($nro_interno);


            $product = new Product();
            $product->item_id = $item->id;
            $product->car_id =  null;
            //$product->car_id = $car_id ?? null;
            $product->marca_modelo = $request->input('marca_modelo');
            //$product->product_cost = $request->input('product_cost');
            $product->product_price = 0;
            $product->nro_motor = $request->input('nro_motor') ?? null;
            $product->nro_oblea = $request->input('nro_oblea') ?? null;
            //$product->product_unit = $request->input('product_unit');
            $product->tax_method = 'exclusive';
            //$product->tax_id = $request->input('tax_id');
            $product->description = $request->input('description');
            $product->stock = 1;
            $product->anio = $request->input('anio');

            $product->estado = $request->input('estado_prod') ?? "desarme";

			//$car_id=$request->input('nro_interno') ?? 0;
            $car = Cars::find($car_id);

            if (isset($car)) {
                $product->nro_interno = $car_id ?? null;
                $product->company_id = $car->company_id ?? company_id();
                $product->marca_modelo = $car->idMarca_modelo ?? null;

                // $px = Product::where('item_id', $product->item_id)->where('car_id', $car->car_id)->first();
                // if($px){

                //     return response()->json(['result' => 'error', 'action' => 'store', 'message' => _lang('El vehículo ya tiene una pieza asociada'), 'data' => $product]);
                // }

            } else {
                //  $product->car_id = $request->input('car_id') ?? $request->input('nro_interno') ?? null;
                $product->nro_interno = $request->input('nro_interno') ?? 0;
                $product->company_id = $request->input('company') ?? company_id();
				$product->marca_modelo = $request->input('marca_modelo');
            }




            $product->estado = $request->input('estado_prod') ?? null;

            $product->idDeposito = $request->input('idDeposito') ?? null;
            $product->ubicacion = $request->input('ubicacion') ?? null;

            $product->mercado_libre = $request->input('mercado_libre') ?? 0;

            $product->carga_rapida = $request->input('carga_rapida') ?? 0;

            $product->user_id = auth()->user()->id;

            if ($product->ubicacion != ""  && (is_null($product->fecha_ingreso_a_stock))) {
                $product->fecha_ingreso_a_stock = date('Y-m-d H:i:s');
            };

            $product->save();
            if (!empty($request->file())) {
				$path = public_path('uploads/products');
				if(!file_exists($path) && !is_dir($path)) mkdir($path, 0755, true);
                $this->uploadImg($request, ['dir' => 'products', 'idProduct' => $product->id]);
            }
			
			/*if ($request->hasFile('imagen')) {
            foreach ($request->file('imagen') as $file) {
                // Guarda en storage/app/public/vehiculos
                $path = $file->store('vehiculos', 'public');
                $imagePaths[] = $path;
				}
			}*/
			
			
			
        } else {
            //Create pieza para todos los autos
            /*$car = Cars::where('idEstado', '!=', 1)->get();
            foreach ($car as $c) {
                $product = new Product();
                $product->item_id = $item->id;
                $product->car_id = $c->id;
                $product->marca_modelo = $c->idMarca_modelo;
                //$product->product_cost = $request->input('product_cost');
                $product->product_price = 0;
                //$product->product_unit = $request->input('product_unit');
                $product->tax_method = 'exclusive';
                //$product->tax_id = $request->input('tax_id');
                $product->description = $request->input('description');
                $product->stock = 1;
                $product->nro_interno = $request->input('nro_interno') ?? $c->id;
                $product->company_id = $c->company_id;
                $product->allCar = 1;
                $product->user_id= auth()->user()->id;

                $product->save();
            }*/
        }

        $cate = $request->input('categoria');

        if (isset($product) && !empty($cate[0])) {
            foreach ($cate as $ca):
                $cateProd = new Categoria_product;
                $cateProd->product_id = $product->id;
                $cateProd->categoria_id = $ca;
                $cateProd->save();

            endforeach;
        }

	    $request['informe'] = "Creacion de producto " . json_encode($product);
		$this->grabarHistorial($request, $product);

        //Create Stock Row
        //        $stock = new Stock();
        //        $stock->product_id = $item->id;
        //        $stock->quantity = 1;
        //        $stock->company_id = company_id();
        //        $stock->save();

        DB::commit();

        if (!$request->ajax()) {
            return redirect()->back()->with(['success' => _lang('Saved sucessfully '), 'product' => $product])->withInput();
        } else {
            $product->{"products.id"} = $product->id;
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved sucessfully'), 'data' => $product]);
        }
    }

    public function historialProducto(Request $request, $idProduct)
    {

        $product = Historial_product::where('product_id', $idProduct)->get();

        $item = Item::where("id", $product[0]->item_id)->first();
        $id = $idProduct;
        // dd($idProduct);
        // dd($item);

        return view('backend.accounting.product.historialProducto', compact('item', 'id', 'product'));
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $product = Product::find($id);
        $item = Item::where("id", $product->item_id)->first(); // ->where("company_id",company_id())

        if (!$request->ajax()) {
            return view('backend.accounting.product.view', compact('item', 'id', 'product'));
        } else {
            return view('backend.accounting.product.modal.view', compact('item', 'id', 'product'));
        }
    }

    public function historyProduct(Request $request, $id)
    {
        $product = Historial_product::find($id);
        $item = Item::where("id", $product->item_id)->first(); // ->where("company_id",company_id())

        if (!$request->ajax()) {
            return view('backend.accounting.product.view', compact('item', 'id', 'product'));
        } else {
            return view('backend.accounting.product.modal.view', compact('item', 'id', 'product'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $product = Product::where('id', $id)->with(['category', 'marcaModelo', 'img'])->first();

        $item = Item::where("id", $product->item->id)->first(); // ->where("company_id",company_id())
        $categorias = Categoria::all();
        $comp = Company::all();
        $cias = $comp;
        $marcas = Marca::all();
        $items = Item::where('activo', "Si")->orderBy('item_name', 'ASC')->get();
        //$items = Item::orderBy('item_name', 'ASC')->get();

        //$auto = [];
        //if($idCar) {

        $nro_interno_datos = Cars::All();

        if (auth()->user()->role->name != 'Gerencial') {
            $nro_interno_datos->where('company_id', company_id());
        }

        $interno = $this->get_last_interno();

        $idCar = $auto->car_id ?? false;
        $auto = Cars::where('id', $product->car_id ?? null)->with('marca_modelo')->first();
		
		$galeriaFiles = [];	
		if ($product && $product->img) {
			$galeriaFiles = $product->img->where('img')->map(function ($file) {
				$path = 'uploads/products/';
				$datos_imagen=buscarImagen($path.$file->img, true);
				return [
					//'id'       => $file->id,
					'name'     => $file->img,
					'filesize' => $datos_imagen['size'], //file_exists($path) ? filesize($path) : ($file->peso ?? 0), 
					'path'      => $datos_imagen['url'] //asset('storage/' . $file->path)
				];
			})->values()->toArray();
		}

        if (!$request->ajax() || empty($auto->id)) {
            return view('backend.accounting.product.edit', compact('item', 'id', 'product', 'categorias', 'interno', 'cias', 'marcas', 'nro_interno_datos', 'items','galeriaFiles'));
        } else {
            return view('backend.accounting.product.modal.edit', compact('item', 'id', 'auto', 'product', 'categorias', 'interno', 'marcas', 'nro_interno_datos', 'items','galeriaFiles'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function bulkUpdateFechaUltimogiro(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required',
            'fecha_ultimogiro' => 'nullable|date',
            'ubicacion' => 'nullable|string|max:255',
            'update_fecha_ultimogiro' => 'nullable',
            'update_ubicacion' => 'nullable'
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'message' => $validator->errors()->all()], 422);
        }

        $idsInput = $request->input('ids', '');
        $ids = array_values(array_filter(array_map(function ($value) {
            $value = trim((string) $value);
            if ($value === '') {
                return null;
            }

            if (preg_match('/(\d+)/', $value, $matches)) {
                return (int) $matches[1];
            }

            return is_numeric($value) ? (int) $value : null;
        }, explode(',', $idsInput))));

        if (empty($ids)) {
            return response()->json(['result' => 'error', 'message' => 'No se seleccionaron productos.'], 422);
        }

        $updateFecha = $request->has('update_fecha_ultimogiro')
            ? filter_var($request->input('update_fecha_ultimogiro', false), FILTER_VALIDATE_BOOLEAN)
            : $request->has('fecha_ultimogiro');

        $updateUbicacion = $request->has('update_ubicacion')
            ? filter_var($request->input('update_ubicacion', false), FILTER_VALIDATE_BOOLEAN)
            : $request->has('ubicacion');

        if (!$updateFecha && !$updateUbicacion) {
            return response()->json(['result' => 'error', 'message' => 'Seleccione al menos un campo para actualizar.'], 422);
        }

        $data = [];

        if ($updateFecha) {
            $data['fecha_ultimogiro'] = $request->filled('fecha_ultimogiro') ? $request->input('fecha_ultimogiro') : null;
        }

        if ($updateUbicacion) {
            $ubicacion = $request->exists('ubicacion') ? trim((string) $request->input('ubicacion')) : null;
            $data['ubicacion'] = $ubicacion === '' ? null : $ubicacion;
        }

        Product::whereIn('id', $ids)
            ->whereIn('company_id', company_id_arr())
            ->update($data);

        return response()->json(['result' => 'success', 'updated' => count($ids), 'updated_fields' => array_keys($data)]);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            // 'item_name' => 'required',
            // 'nro_interno' => 'unique:products,id,'.$id,
            //'product_cost' => 'required|numeric',
            //'product_price' => 'required|numeric',
            //'product_unit' => 'required',
            'imagen.*'          => ['mimes:jpg,jpeg,png,gif,svg']
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('products.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

		//dd($request);
        //Update item
        DB::beginTransaction();

        $product = Product::where("id", $id)->first();

        $originalProduct = collect($product->getOriginal());


        if (!isset($originalProduct['id'])) {
            $originalProduct['id'] = $product->id; // Asegurar que el ID esté presente
        }


        $item = Item::where("id", $product->item_id)->first(); //->where("company_id",company_id())

        // $this->grabarHistorial($request, $product);

        if ($item) {

            // $item->item_name = $request->input('item_name');
            // $item->item_type = 'product';
            // $item->company_id = $request->input('company') ?? company_id();
            $allCar = $request->input('car_or_stock', false);
            // if($allCar == 1) {
            //     $item->allCar = 1;
            // }
            // $item->save();


            if (!empty($request->input('item_name')) && empty($request->input('item_id'))) {
                $item->item_name = $request->input('item_name');
                $item->item_type = 'product';
                $item->company_id = $request->input('company') ?? company_id();
                $allCar = $request->input('car_or_stock', false);
                if ($allCar == 1) {
                    $item->allCar = 1;
                }
                $item->save();
            } else if ($request->input('item_id')) {
                $item = Item::find($request->input('item_id'));
            }
            if ($allCar != 1) {

                //dd($request->input('marca_modelo'));
                $product->item_id = $item->id;
                $product->supplier_id = $request->input('supplier_id');
                $product->product_cost = $request->input('product_cost');
                $product->product_price = $request->input('product_price');
                $product->product_unit = $request->input('product_unit');
                $product->tax_method = 'exclusive';
                //$product->tax_id = $request->input('tax_id');
                $product->marca_modelo = $request->input('marca_modelo');
                $product->description = $request->input('description');
                $product->anio = $request->input('anio');
                $product->nro_interno = $request->input('nro_interno');
                $product->estado = $request->input('estado_prod') ?? null;
                $product->nro_motor = $request->input('nro_motor') ?? null;
                $product->nro_oblea = $request->input('nro_oblea') ?? null;
                $product->idDeposito = $request->input('idDeposito') ?? null;
                $product->ubicacion = $request->input('ubicacion') ?? null;
                $product->fecha_ultimogiro = $request->filled('fecha_ultimogiro') ? $request->input('fecha_ultimogiro') : null;

                if ($product->ubicacion != ""  && (is_null($product->fecha_ingreso_a_stock))) {
                    $product->fecha_ingreso_a_stock = date('Y-m-d H:i:s');
                };

                $product->mercado_libre = $request->input('mercado_libre') ?? 0;



                $dirtyFields = $product->getDirty();

                if (!empty($dirtyFields)) {

                		$cambios="";
                    foreach ($dirtyFields as $field => $newValue) {
                        $oldValue = $originalProduct->get($field);
						
						$cambios.= (($cambios!="") ? "/":"") ." '{$field}': '{$oldValue}' a '{$newValue}'";
                        // Crea un informe
                        //$request['informe'] = "Actualización del campo '{$field}': '{$oldValue}' a '{$newValue}'";
                        // Simula un producto con los valores originales
                    }
					 if ($cambios!=""){
						 $request['informe']="actualizacion de campos:".$cambios;
						  $this->grabarHistorial($request, (object) $originalProduct->toArray());
					  }

                   /* foreach ($dirtyFields as $field => $newValue) {
                        $oldValue = $originalProduct->get($field);

                        // Crea un informe
                        $request['informe'] = "Actualización del campo '{$field}': '{$oldValue}' a '{$newValue}'";

                        // Simula un producto con los valores originales
                        $this->grabarHistorial($request, (object) $originalProduct->toArray());
                    }*/
                }
                $product->save();

                //dd($product);
            }

			if ($request->has('removed_imagen')) {
				$imagenes = Imagen::whereIn('img', $request->removed_imagen)->get();
				foreach ($imagenes as $imagen) {
					if (file_exists(public_path('uploads/products/' . $imagen->img))) {
						unlink(public_path('uploads/products/' . $imagen->img));
					}
					$imagen->delete();
				}
			}
			
			if (!empty($request->file('imagen'))) {
                $this->uploadImg($request, ['dir' => 'products', 'idProduct' => $product->id]);
            }
			
		
		
            //eliminar las imagenes seleccionadas
            /*$arrImgDelete = $request->input('imgDelete', false);
            if ($arrImgDelete && isset($arrImgDelete[0])) {
                foreach ($arrImgDelete as $imgdelete) {
                    $img = Imagen::where('id', $imgdelete)->first();
				if (file_exists(public_path('uploads/products/' . $img->img))) {
                    unlink(public_path('uploads/products/' . $img->img));
				}	
                    Imagen::where('id', $imgdelete)->delete();
                }
            }


            if (!empty($request->file('imagen'))) {
                //                $this->deleteImgsByIdCarOridProd(['idProduct' => $product->id]);
                $this->uploadImg($request, ['dir' => 'products', 'idProduct' => $product->id]);
            }*/

            $cate = $request->input('categoria');

            if (isset($product) && !empty($cate[0])) {
                foreach ($cate as $ca):
                    Categoria_product::where('product_id', $product->id)->where('categoria_id', $ca)->delete();
                    $cateProd = new Categoria_product;
                    $cateProd->product_id = $product->id;
                    $cateProd->categoria_id = $ca;
                    $cateProd->save();

                endforeach;
            }

            DB::commit();
        } else {
            if (!$request->ajax()) {
                return redirect('products')->with('error', _lang('Update Failed !'));
            } else {
                return response()->json(['result' => 'error', 'message' => _lang('Update Failed !')]);
            }
        }


        if (!$request->ajax()) {
            return redirect('products')->with('success', _lang('Updated sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated sucessfully'), 'data' => $product]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /* public function destroy($id, Request $request)
    {
        DB::beginTransaction();
        $product = Product::where("id", $id)->first();

        $item = Item::where("id", $product->item_id); //->where("company_id",company_id())

        $this->grabarHistorial($request, $product);

        $product->estado = "Anulado";
        $product->stock = 0;
        $product->save();

        //$item->delete();
        //$product->delete();



        DB::commit();

        return redirect('products')->with('success', _lang('Deleted sucessfully'));
    } */
	
	public function destroy($id, Request $request)
{
     $request->validate([
        'informe' => 'required|string|max:255',
    ]);

    DB::beginTransaction();

    try {
        $product = Product::where("id", $id)->firstOrFail();
        $this->grabarHistorial($request, $product);
        $product->estado = "Anulado";
        $product->stock = 0;
        $product->save();
        DB::commit();
        if ($request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => _lang('Deleted sucessfully')
            ]);
        }

        return redirect('products')->with('success', _lang('Deleted sucessfully'));

    } catch (\Exception $e) {
        DB::rollBack();
        if ($request->ajax()) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
        return redirect()->back()->with('error', $e->getMessage());
    }
}

    public function destroy_comunes($id)
    {
        DB::beginTransaction();


        $item = Item::where("id", $id); //->where("company_id",company_id())
        $product = Product::where("item_id", $id);
        $item->delete();
        $product->delete();



        DB::commit();

        return redirect(route('productos_comunes'))->with('success', _lang('Deleted sucessfully'));
    }

    public function getItem($id)
    {
        $item = Item::where("id", $id)->first(); //->where("company_id",company_id())

        if (!empty($item)) {
            return response()->json(['item' => $item]);
        }
    }



    public function get_product(Request $request, $id)
    {
        //        $item = Item::where("id",$id)->where("company_id",company_id())->first();
        //
        //      if($item->item_type == 'product'){
        //          echo json_encode(array("item"=>$item,"product"=>$item->product,"tax"=>$item->product->tax,"available_quantity"=>$item->product_stock->quantity));
        //        }else if($item->item_type == 'service'){
        //          echo json_encode(array("item"=>$item,"product"=>$item->service,"tax"=>$item->service->tax));
        //      }

        $product = Product::where('id', $id)->with('marcaModelo', function ($q) {
            $q->with('marca');
            $q->with('modelo');
        })->first();

        if ($product->item->item_type == 'product') {
            $product->item->product_price = $product->item->product_price ?? 1;
            $product->item->product_unit = $product->item->product_unit ?? 1;
            $product->product_price = $product->product_price ?? 1;
            $product->product_unit = $product->product_unit ?? 1;
            echo json_encode(array("item" => $product->item, "product" => $product, "tax" => $product->tax, "available_quantity" => $product->stock));
        }
        //        else if($item->item_type == 'service'){
        //          echo json_encode(array("item"=>$item,"product"=>$item->service,"tax"=>$item->service->tax));
        //      }
    }
	
	public function productosLote($ids)
	{
		//$input = $ids;//$request->all();
		
		if (!empty($ids)) {
			 $ids=explode(",",$ids);
			$product = Product::whereIn('id', $ids)->with('item')->with('marcaModelo', function ($q) {
            $q->with('marca');
            $q->with('modelo');
        })->get();
		return response()->json($product);
		}
	}


    private function get_last_interno()
    {
        $interno = Product::select('id')->orderBy('id', 'desc')->first();

        return $interno->nro_interno ?? 2999;
    }
    public function companyByProduct($id)
    {
        $company = Product::find($id);

        $company = ['company' => $company->company_id];

        return response()->json($company);
    }

    public function cambiarEstado($id, $estado)
    {
        $product = Product::find($id);

        if ($estado == 'descompuesto') {
            $product->estado = $estado;
            $product->save();
        }
    }

    //productos anulados (se coloca stock 0)
    public function anulados(Request $request)
    {

		$lugar_entregas = Lugar_entregas::select('nombre')->get()->map(function ($item) {
			return [
				'id'   => $item->nombre,
				'text' => $item->nombre,
			];
		});

if ($request->ajax()) {

     $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();

    // Aseguramos que solo seleccione campos necesarios y precargue relaciones
    $products = Product::query()
        ->select('products.*', 'cars.tipo_vehiculo', 'cars.dominio')
        ->leftJoin('cars', 'cars.id', '=', 'products.nro_interno')
        ->with([
            'category', 
            'item', 
            'deposito', 
            'marcaModelo.marca', 
            'marcaModelo.modelo'
        ])
        ->whereIn('products.company_id', (array)$company_id)
        ->whereNull('products.car_id')
        ->where('products.stock', 0)
		->where('products.estado', 'Anulado')
		// ->whereDoesntHave('invoiceItems')
        ->whereHas('item', function ($query) {
            $query->where('item_type', 'product');
        })
        ->orderByDesc('products.id');

    return DataTables::eloquent($products)
        ->filterColumn('productsid', function ($query, $keyword) {
            $query->where('products.id', 'like', "%{$keyword}%");
        })
        ->filterColumn('created_at', function ($query, $keyword) {
            if (!empty($keyword)) {
                $date_range = explode(" - ", $keyword);
                if (count($date_range) === 2) {
                    $query->whereBetween('products.created_at', [$date_range[0], $date_range[1]]);
                }
            }
        })
        ->filterColumn('fecha_ingreso_a_stock', function ($query, $keyword) {
            $query->where('products.fecha_ingreso_a_stock', 'like', "%{$keyword}%");
        })
        ->filterColumn('nro_interno', function ($query, $keyword) {
            $query->where('products.nro_interno', 'like', "%{$keyword}%");
        })
        ->filterColumn('dominio', function ($query, $keyword) {
            $query->where('cars.dominio', 'like', "%{$keyword}%");
        })
        ->filterColumn('productItem', function ($query, $keyword) {
            $query->whereHas('item', function ($subQuery) use ($keyword) {
                $subQuery->where('item_name', 'like', "%{$keyword}%");
            });
        })
        ->filterColumn('marca', function ($query, $keyword) {
            $query->whereHas('marcaModelo.marca', function ($subQuery) use ($keyword) {
                $subQuery->where('marca', 'like', "%{$keyword}%");
            });
        })
        ->filterColumn('modelo', function ($query, $keyword) {
            $query->whereHas('marcaModelo.modelo', function ($subQuery) use ($keyword) {
                $subQuery->where('modelo', 'like', "%{$keyword}%");
            });
        })
        ->filterColumn('motor', function ($query, $keyword) {
            $query->where('products.motor', 'like', "%{$keyword}%");
        })
        ->filterColumn('deposito', function ($query, $keyword) {
            $query->whereHas('deposito', function ($subQuery) use ($keyword) {
                if ($keyword === "") {
                    $subQuery->whereNull('nombre')->orWhere('nombre', '');
                } else {
                    $subQuery->where('nombre', 'like', "%{$keyword}%");
                }
            });
        })
        ->addColumn('productsid', function ($data) {
            $prefixes = [1 => 'PM-', 2 => 'PC-'];
            return ($prefixes[$data->company_id] ?? '') . $data->id;
        })
        ->addColumn('created_at', fn($data) => formatDate($data->created_at))
        ->addColumn('fecha_ingreso_a_stock', fn($data) => formatDate($data->fecha_ingreso_a_stock))
        ->addColumn('interno', fn($data) => nroInternoAlias($data->company_id, $data->tipo_vehiculo, $data->nro_interno))
        ->addColumn('productItem', fn($data) => $data->item->item_name ?? null)
        ->addColumn('marca', fn($data) => $data->marcaModelo->marca->marca ?? '')
        ->addColumn('modelo', fn($data) => $data->marcaModelo->modelo->modelo ?? '')
        ->addColumn('deposito', fn($data) => $data->deposito->nombre ?? '')
        ->addColumn('dominio', fn($data) => $data->dominio ?? '')
        ->addColumn('action', function ($data) {
            return "<button class='btn btn-success' data-id='{$data->id}' onClick='toggleStock(this)'>Habilitar</button>";
        })
		->make(true);
        //->toJson();
}
		
		
		
        // $productosNoVendidosSinStock = Product::query()
        //     // 1. Condición: El stock debe ser 0
        //     ->where('stock', 0)

        //     // 2. Condición: El producto NO debe tener registros en la relación 'invoiceItems'
        //     // (Es decir, no se ha vendido nunca)
        //     ->whereDoesntHave('invoiceItems')

        //     // 3. Obtener la colección de resultados
        //     ->get();
		/*$lugar_entregas = Lugar_entregas::all()->map(function ($item) {
					return [
						'id'   => $item->nombre,    
						'text' => $item->nombre,
					];
				});

        if ($request->ajax()) {

            $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
			

            $products = Product::select('products.*', 'cars.tipo_vehiculo', 'cars.dominio')
                ->leftJoin('cars', 'cars.id', '=', 'products.nro_interno')
                ->whereIn('products.company_id', $company_id)
                ->where('car_id', null)
                ->with('category')
                ->whereHas('item', function ($query) {
                    $query->where("item_type", "product");
                });


            $products->where('products.stock', 0)
                ->whereDoesntHave('invoiceItems');

            $products->orderBy('products.id', 'desc');

            return DataTables::eloquent($products)
                ->filterColumn('productsid', function ($query, $keyword) {
                    $query->where('products.id', 'like', "%{$keyword}%");
                })
                ->filterColumn('created_at', function ($query, $keyword) {
                    $date_range = ($keyword != '') ? explode(" - ", $keyword) : array();
                    if (count($date_range) == 2) {
                        $query->whereDate('products.created_at', '>=', $date_range[0])
                            ->whereDate('products.created_at', '<=', $date_range[1]);
                    }
                })
                ->filterColumn('fecha_ingreso_a_stock', function ($query, $keyword) {
                    $query->whereRaw("DATE_FORMAT(fecha_ingreso_a_stock,'%d/%m/%Y') LIKE ?", ["%$keyword%"]);
                })
                ->filterColumn('nro_interno', function ($query, $keyword) {
                    $query->where('products.nro_interno', 'like', "%{$keyword}");
                })
                ->filterColumn('dominio', function ($query, $keyword) {
                    $query->where('cars.dominio', 'like', "%{$keyword}%");
                })
                ->filterColumn('productItem', function ($query, $keyword) {
                    $query->orWhereHas('item', function ($subQuery) use ($keyword) {
                        $subQuery->where('item_name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('marca', function ($query, $keyword) {
                    $query->orWhereHas('marcamodelo', function ($subQuery) use ($keyword) {
                        $subQuery->whereHas('marca', function ($str) use ($keyword) {
                            $str->where('marca', 'like', "%{$keyword}%");
                        });
                    });
                })
                ->filterColumn('modelo', function ($query, $keyword) {
                    $query->orWhereHas('marcamodelo', function ($subQuery) use ($keyword) {
                        $subQuery->whereHas('modelo', function ($str) use ($keyword) {
                            $str->where('modelo', 'like', "%{$keyword}%");
                        });
                    });
                })
                ->filterColumn('motor', function ($query, $keyword) {
                    $query->where('products.motor', 'like', "%{$keyword}");
                })
                ->filterColumn('deposito', function ($query, $keyword) {
                    $query->orWhereHas('deposito', function ($str) use ($keyword) {
                        if ($keyword == "") {
                            $str->where('nombre', '=', "")
                                ->orWhereNull('nombre');
                        } elseif ($keyword != "") {
                            $str->where('nombre', 'like', "%{$keyword}%");
                        }
                    });
                })
                ->addColumn('productsid', function ($data) {
                    if ($data->company_id == 1) {
                        $in = 'PM-';
                    } else if ($data->company_id == 2) {
                        $in = 'PC-';
                    }
                    return $in . $data->id;
                })
                ->addColumn('created_at', function ($data) {
                    return formatDate($data->created_at);
                })
                ->addColumn('fecha_ingreso_a_stock', function ($data) {
                    return formatDate($data->fecha_ingreso_a_stock);
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
                ->addColumn('deposito', function ($data) {
                    return $data->deposito->nombre ?? '';
                })
                ->addColumn('dominio', function ($data) {
                    return $data->dominio ?? '';
                })
                ->addColumn('action', function ($data) {
                    // $result=  "<form action='". action('ProductController@destroy', $data->id) ."' method='post'>";
                    // $result .= "<a href='" . action('ProductController@edit', $data->id) . "' class='btn btn-warning btn-xs ". ((!empty($data->car_id)) ? 'ajax-modal' : '') . "'><i class='ti-pencil'></i></a>";
                    // $result .= "<a href='" . action('ProductController@show', $data->id) . "' class='btn btn-primary btn-xs ajax-modal'><i class='ti-eye'></i></a>";
                    // $result .= "<a href='" . action('ProductController@printQR', $data->id) . "' class='btn btn-success btn-xs ajax-modal'><i class='fa fa-qrcode' aria-hidden='true'></i></a>";
                    // $result .= "<a href='" . action('ProductController@printsinQR', $data->id) . "' class='btn btn-success btn-xs ajax-modal'><i class='fa fa-barcode' aria-hidden='true'></i></a>";
                    // $result .= csrf_field();
                    // $result .= "<input name='_method' type='hidden' value='DELETE'><button class='btn btn-danger btn-xs btn-remove-product' type='submit'><i class='ti-eraser'></i></button>";
                    // $result .= "</form>";
                    $result = "<button class='btn btn-success' data-id='$data->id' onClick='toggleStock(this)' >Habilitar</button> ";
                    return $result;
                })->tojson();
        }*/

        return view('backend.accounting.product.anulados', compact("lugar_entregas"));
    }

    //anular modifica el stock a 0 de lo controario modifica a 1
    public function toggleStock(Request $request)
    {
        $id = $request->id;

        $product = Product::find($id);
        if ($product->stock <= 0) {
            $product->estado = "habilitado";
            $product->stock = 1;
        } else {
			$product->estado = "Anulado";
            $product->stock = 0;
        }
        $product->save();


        return response()->json(['result' => $id]);
    }

    public function cargaRapida(Request $request)
    {
        $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
        $items = Item::where('activo', "Si")->orderBy('item_name', 'ASC')->get();
        //$items = Item::where('activo', "Si")->where('allCar', 1)->orderBy('item_name', 'ASC')->get();
        $contenidoEtiqueta = "";
        $producto = null;
        $cars = Cars::All()->whereIn('company_id', $company_id);
		
		/*$products = Product::where('car_id', null)
            ->where('stock', '>=', 1)
            ->where('carga_rapida', 1)
            ->with('category')
            ->whereHas('item', function ($query) {
                $query->where("item_type", "product");
            })->get();*/

		if ($request->ajax()) {
			
			 $data = Product::select("*")
			 ->where('car_id', null)
            ->where('stock', '>=', 1)
            ->where('carga_rapida', 1)
			->whereIn('company_id', $company_id)
            ->with('category')
            ->whereHas('item', function ($query) {
                $query->where("item_type", "product");
            });
			//$key=0;
            return Datatables::of($data)
                    ->addIndexColumn()
					->setRowId(function ($data) {
						return 'row_' . $data->id; 
					})
					//->setRowId('id') // Sets the tr id attribute to the value of the 'id' column
					->addColumn('producto_id', function ($data) {
						$in ="";
						   if ($data->company_id == 1) {
                                        $in = 'PM-';
                                    } elseif ($data->company_id == 2) {
                                        $in = 'PC-';
                                }
                        return  $in . $data->id; 
                    })
					->addColumn('marcamodelo', function ($data) {
						return ($data->marcaModelo->marca->marca ?? '') . ' ' . ($data->marcaModelo->modelo->modelo ?? ''); 
                    })
					->editColumn('nro_interno', function ($data) {
						$in ="";
						   if ($data->company_id == 1) {
                                        $in = 'PM-';
                                    } elseif ($data->company_id == 2) {
                                        $in = 'PC-';
                                }
								
                        return  $data->description . $in . $data->nro_interno; 
                    })
					->addColumn('deposito', function ($data) {
						return $data->deposito->nombre ?? '';
                    })
					  ->addColumn('item_name', function ($data) {
						return $data->item->item_name;
					})
					->addColumn('fecha_creacion', function ($data) {
						return $data->created_at->format('d/m/Y H:i');
					})
					->addColumn('usuario', function ($data) {
						return $data->user->name;
					})
					->addColumn('action', function ($data) {
						
						 $result=  "<form action='". action('ProductController@destroy', $data->id) ."' method='post'>";
                                        $result .= "<a href='" . action('ProductController@show', $data->id) . "' class='btn btn-primary btn-xs ajax-modal'><i class='ti-eye'></i></a>";

                                        $result .= "<a href='" . action('ProductController@printQR', $data->id) . "' class='btn btn-success btn-xs ajax-modal'><i class='fa fa-qrcode' aria-hidden='true'></i></a>";

                                        $result .= "<a href='" . action('ProductController@printsinQR', $data->id) . "' class='btn btn-success btn-xs ajax-modal'><i class='fa fa-barcode' aria-hidden='true'></i></a>";
                                        $result .= csrf_field();
                                          $result .= "</form>";
                                          //echo "<td>$result </td>";
						
						return $result;
					})
                    ->rawColumns(['action'])
                    ->make(true);
			
		}

        return view('backend.accounting.product.cargarapida', compact('contenidoEtiqueta', 'producto', 'cars', 'items'));
    }

    public function grabarHistorial($request, $product)
    {
        $historial = new Historial_product();
        $historial->product_id = $product->id;
        $historial->item_id = $product->item_id;
        $historial->car_id = $product->car_id;
        $historial->informe = $request->informe ?? 'Actualizacion';
        $historial->nro_interno = $product->nro_interno;
        $historial->company_id = $product->company_id;
        $historial->user_id = auth()->id();

        $historial->marca_modelo = $product->marca_modelo;
        $historial->description = $product->description;
        $historial->estado = $product->estado;
        $historial->nro_motor = $product->nro_motor;
        $historial->nro_oblea = $product->nro_oblea;
        $historial->idDeposito = $product->idDeposito;
        $historial->ubicacion = $product->ubicacion;

        // dd($request->informe);
        $historial->save();
    }

    /**
     *  Bajar zip de imagenes
     */

    public function pro_imag_zip($id, $tipo = "imagenes")
    {

        $product = Product::find($id);
        $item = Item::where("id", $product->item_id)->first();

        if (!$product) {
            return back()->with('error', _lang('Sorry, Car not found !'));
        }

        $path = public_path("uploads/");
        $carpeta_comprimir = "{$path}pro_img_{$id}_" . date("Y-m-d_His");

        if (!File::isDirectory($carpeta_comprimir)) {
            File::makeDirectory($carpeta_comprimir, 0777, true, true);
        }
        sleep(1);
        //Fotos generales
        if (!empty($product->img) && in_array($tipo, ['all', 'imagenes'])) {
            foreach ($product->img as $v) {
				
				if (file_exists($path . 'products/' . $v->img)) {
					$file= $path . 'products/' . $v->img;
					$valor = File::copy($file, $carpeta_comprimir . "/" . $v->img);
					//GuardarmarcaAgua($path . 'products/' . $v->img, $v->company_id, $carpeta_comprimir);
				}else{
					if (Storage::disk('gcs')->exists('/products/'. $v->img)) {
						$stream = Storage::disk('gcs')->readStream('/products/'. $v->img);
						$destinationStream = fopen($carpeta_comprimir . "/" . $v->img, 'w');
							if ($stream && $destinationStream) {
								stream_copy_to_stream($stream, $destinationStream);
								fclose($destinationStream);
							}
					}
				}
				
                
				
            }
        }

        sleep(1);

        // se comprimime
        $zip = new ZipArchive;
        $fileName = "pro_img_{$id}_" . date("Y-m-d_His") . ".zip";

        if ($zip->open($path . $fileName, ZipArchive::CREATE) === TRUE) {
            $files = File::files($carpeta_comprimir);

            foreach ($files as $key => $value) {
                $relativeNameInZipFile = basename($value);
                $zip->addFile($value, $relativeNameInZipFile);
            }
            $zip->close();
        }
        if (\File::isDirectory($carpeta_comprimir)) \File::deleteDirectory($carpeta_comprimir);
        return response()->download($path . $fileName)->deleteFileAfterSend(true);
    }

    public function printQR($id)
    {
        
        $producto = Product::where('id', $id)->first();
        return view('backend.accounting.product.etiquetaQr', compact('producto'))->render();
    }
    public function printsinQR($id)
    {
        //return view('backend.accounting.product.list', compact('products'));
        $producto = Product::where('id', $id)->first();
        return view('backend.accounting.product.etiqueta', compact('producto'))->render();
    }
	
	
	 //anular modifica el stock a 0 de lo controario modifica a 1
    public function actualizaStockitems(Request $request)
    {
        $id = $request->id;

        $product = Product::find($id);
		 if (!$product) {
            return back()->with('error', _lang('Sorry, Car not found !'));
        }
		
		if (isset($request->nro_oblea)) {
			$product->nro_oblea = $request->nro_oblea ?? null;
		}
		
		if (isset($request->campo)) {
			$campo = $request->campo;
			$product->{$campo} = $request->valor ?? null;
		}
		
        $product->save();
		
        return response()->json(['result' => "sucess"]);
    }
	
	public function auditoriaHistorial(Request $request)
    {
		   $id = $request->id;
		return view('backend.accounting.product.modal.historial', compact('id')); 
    }
	
	public function auditoriaProducto(Request $request)
    {
		   $id = $request->id;
		   
		  if (request()->ajax()) {
            $datosAudit = Audit::where('auditable_type', Product::class)
                ->where('auditable_id', $id)
                ->with('user')
                ->with('auditable');
            return DataTables::eloquent($datosAudit)
			->addIndexColumn()
				->addColumn('model', function ($data) {
					return "$data->auditable_type (id: $data->auditable_id )";
				})
				->addColumn('usuario', function ($data) {
					return $data->user->name ?? '';
				})
				->addColumn('valores_ant', function ($data) {
					$datos='<table>';
                    foreach($data->old_values as $attribute => $value){
                      $datos.='<tr>
                        <td><b>'.$attribute .'</b></td>
                        <td>'. $value .'</td>
                      </tr>';
                    }
                  $datos.= '</table>';
					return $datos;
				})
				->addColumn('valores_nue', function ($data) {
					$datos='<table>';
                    foreach($data->new_values as $attribute => $value){
                      $datos.='<tr>
                        <td><b>'.$attribute .'</b></td>
                        <td>'. $value .'</td>
                      </tr>';
                    }
                  $datos.= '</table>';
					return $datos;
				})
				->rawColumns(['valores_ant','valores_nue'])
                ->make(true);
        }
    }
	
	public function table_detalle(Request $request)
    {
		if ($request->ajax()) {
				$isExport = isset($request->exportar);

			$evaluarMostrar = function ($row) use ($isExport) {
				return !$isExport && (strtoupper((string) $row->estado) !== 'ANULADO') && ((float) ($row->stock ?? 0) > 0);
			};

			$query = Item::select(
					'products.id as product_id', 
					'items.id as item_id',       
					'products.stock', 
					'products.nro_oblea',
					DB::raw("CONCAT(items.item_name, CASE WHEN items.allCar = 0 OR items.allCar IS NULL THEN ' (*)' ELSE '' END) as item_name"),
					'items.allCar',
					'items.activo',
					'products.estado',
					'invoices.invoice_number',
					'users.name as vendedor',
					'lugar_entregas.nombre as deposito',
					DB::raw("GROUP_CONCAT(
    DISTINCT CASE 
        WHEN invoices.invoice_number IS NOT NULL OR users.name IS NOT NULL 
        THEN CONCAT(COALESCE(invoices.invoice_number, ''), '||', COALESCE(users.name, '')) 
        ELSE NULL 
    END 
    SEPARATOR ';;'
) as facturas_vendedores")
					//DB::raw("GROUP_CONCAT(DISTINCT invoices.invoice_number SEPARATOR ', ') as datos_facturas"),
					//DB::raw("GROUP_CONCAT(DISTINCT products.id SEPARATOR ', ') as productos_asociados")
				)
				->leftJoin('products', function($join) use ($request) {
					$join->on('products.item_id', '=', 'items.id')
						 ->where('products.nro_interno', $request->nro_interno); 
				})
				->leftJoin('invoice_items', 'invoice_items.product_id', '=', 'products.id')
				->leftJoin('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
				->leftJoin('users', 'invoices.user_id', '=', 'users.id')
				->leftJoin('lugar_entregas', 'products.idDeposito', '=', 'lugar_entregas.id')
				->where(function($query) {
					$query->where('items.item_type', 'product')
						  ->where('items.activo', 'Si');
					$query->where(function($subQuery) {
						$subQuery->where('items.allCar', 1)
							->orWhere(function($q) {
								$q->where(function($allCarQuery) {
									$allCarQuery->where('items.allCar', 0)
												->orWhereNull('items.allCar');
								})
								->where('products.stock', '>', 0);
							});
					});
				})
				->groupBy('items.id', 'products.id')
				->orderBy('item_name', 'asc');

				$lugar_entregas = Lugar_entregas::all();

    return DataTables::of($query)
        ->addIndexColumn()
        ->addColumn('selection', function ($row) {
            if (!$row->product_id) {
                return '<input name="bank_check" type="checkbox" class="fila-seleccionada" data-id="' . $row->item_id . '">';
            }
            return "";
        })
		->filterColumn('nro_oblea', function ($query, $keyword) {
                    $query->where('products.nro_oblea', 'like', "%{$keyword}%");
                })
		->filterColumn('id_producto', function ($query, $keyword) {
                    $query->where('products.id', 'like', "%{$keyword}%");
              })
		->filterColumn('vendedor', function ($query, $keyword) {
                    $query->where('users.name', 'like', "%{$keyword}%");
              })
		->filterColumn('deposito', function ($query, $keyword) {
                    $query->where('lugar_entregas.nombre', 'like', "%{$keyword}%");
              })
			 ->filterColumn('estado', function ($query, $keyword) {
                    $query->where('products.estado', 'like', "%{$keyword}%");
              })	
		->editColumn('vendedor', function ($row) use ($evaluarMostrar) {
			if (!$row->product_id || empty($row->facturas_vendedores)) {
					return ""; 
				}
			$registros = array_filter(explode(';;', $row->facturas_vendedores));

			if (empty($registros)) {
				return "";
			}

			$html = '<div style="display: flex; flex-direction: column; gap: 3px;">';
			
			foreach ($registros as $registro) {
				$partes = explode('||', $registro);
				$factura = trim($partes[0] ?? '');
				$vendedor = trim($partes[1] ?? '');
				if (empty($factura) && empty($vendedor)) {
					continue;
				}
				$html .= '<div style="line-height: 1.2;">';
				if (!empty($vendedor)) {
					$html .= '<strong>' . e($vendedor) . '</strong> ';
				}
				if (!empty($factura)) {
					$html .= '<span class="badge badge-info"><i class="fa fa-file-text-o"></i> ' . e($factura) . '</span>';
				}
				$html .= '</div>';
			}
			
			$html .= '</div>';

			return $html;
			
          
        })			  
        ->editColumn('nro_oblea', function ($row) use ($evaluarMostrar) {
            if (!$row->product_id) {
                return "";
            }   

			 if ($evaluarMostrar($row)) { 
                return '<div class="input-group d-flex justify-content-center">
                    <input id="prod_id-' . $row->product_id . '" style="min-width: 10px; max-width: 200px;" type="text" class="form-control" value="' . e($row->nro_oblea) . '">
                    <div class="input-group-append">
                        <button type="button" onClick="ActualizarOblea(' . $row->product_id . ')" class="btn btn-warning">
                            <i class="ti-check"></i>
                        </button>
                    </div>
                </div>';
            }
            return $row->nro_oblea ?? '';
        })
        ->addColumn('id_producto', function ($row) {
            return ($row->product_id ?? '');
        })
        ->editColumn('estado', function ($row) use ($evaluarMostrar) {
            if (!$row->product_id) {
                return "";
            }   

            if ($evaluarMostrar($row)) {
                return '<div class="input-group d-flex justify-content-center">
                    <input id="estado_id-' . $row->product_id . '" style="min-width: 10px; max-width: 200px;" type="text" class="form-control" value="' . e($row->estado) . '">
                    <div class="input-group-append">
                        <button type="button" onClick="ActualizarCampo(' . $row->product_id . ', \'estado\')" class="btn btn-warning">
                            <i class="ti-check"></i>
                        </button>
                    </div>
                </div>';
            }

            return $row->estado ?? '';
        })
        ->editColumn('deposito', function ($row) use ($evaluarMostrar, $lugar_entregas) {
            if (!$row->product_id) {
                return "";
            }   

            if ($evaluarMostrar($row)) {
                $options = '<option value="">Seleccione...</option>';
                foreach ($lugar_entregas as $lugar) {
                    $selected = ($row->deposito == $lugar->id || $row->deposito == $lugar->nombre) ? 'selected' : '';
                    $options .= '<option value="' . e($lugar->id) . '" ' . $selected . '>' . e($lugar->nombre) . '</option>';
                }
                return '<div class="input-group d-flex justify-content-center">
                    <select id="idDeposito_id-' . $row->product_id . '" style="min-width: 120px; max-width: 200px;" class="form-control">
                        ' . $options . '
                    </select>
                    <div class="input-group-append">
                        <button type="button" onClick="ActualizarCampo(' . $row->product_id . ', \'idDeposito\')" class="btn btn-warning">
                            <i class="ti-check"></i>
                        </button>
                    </div>
                </div>';
            }

            return $row->deposito ?? '';
        })
        ->addColumn('stock', function ($row) {
            return $row->stock ?? '';
        })
        ->addColumn('action', function ($row) use ($evaluarMostrar) {
            if (!$row->product_id) {
                return "";
            }

		//if ($evaluarMostrar($row)) {
            $resultado = "<form id='form-delete-" . $row->product_id . "' action='" . action('ProductController@destroy', $row->product_id) . "' method='post' class='form-delete-inline'>";
            $resultado .= csrf_field();
            $resultado .= "<input name='_method' type='hidden' value='DELETE'>";
            $resultado .= "<input type='hidden' name='observacion' class='input-observacion'>";

            $resultado .= "<a href='" . action('ProductController@printQR', $row->product_id) . "' class='btn btn-success btn-xs ajax-modal'><i class='fa fa-qrcode' aria-hidden='true'></i></a> ";
            $resultado .= "<a href='" . action('ProductController@printsinQR', $row->product_id) . "' class='btn btn-success btn-xs ajax-modal'><i class='fa fa-barcode' aria-hidden='true'></i></a> ";

            if (!$row->invoice_number && $evaluarMostrar($row)) {
                $resultado .= "<button type='button' class='btn btn-danger btn-xs btn-remove-product-item' data-id='" . $row->product_id . "'><i class='ti-eraser'></i></button>";
            }

			$resultado .= "</form>";
		
			/*return '<div class="d-flex align-items-center justify-content-center">' +
                           '<input type="checkbox" class="chk-accion mr-2" value="' + rowId + '">' +
                           '<div>' . $resultado . '</div>' +
                       '</div>';*/
				return $resultado;
		//	}
		//	return "";	
        })
        ->orderColumn('item_name', function ($query, $order) {
            $query->orderBy('items.item_name', $order);
        })
        ->rawColumns(['selection', 'action', 'nro_oblea', 'estado', 'deposito','vendedor'])
        ->make(true);
		}		
	}


	/*public function table_detalle(Request $request)
    {
		
        if ($request->ajax()) {
	
				$products = Item::select(
					'products.id as product_id', 
					'items.id as item_id',       
					'products.stock', 
					'products.nro_oblea',
			        DB::raw("CONCAT(items.item_name, CASE WHEN items.allCar = 0 OR items.allCar IS NULL THEN ' (*)' ELSE '' END) as item_name"),
					//'items.item_name',
					'items.allCar',
					'items.activo',
					'products.estado',
					'invoices.invoice_number',
					'users.name as vendedor',
					'lugar_entregas.nombre as deposito'
				)
				->leftJoin('products', function($join) use ($request) {
					$join->on('products.item_id', '=', 'items.id')
						 ->where('products.nro_interno', $request->nro_interno); 
				})
				 ->leftJoin('invoice_items', function($join) {
					$join->on('invoice_items.product_id', '=', 'products.id');
					})
				->leftJoin('invoices', function($join) {
					$join->on('invoices.id', '=', 'invoice_items.invoice_id');
				})
				->leftJoin('users', function($join) {
					$join->on('invoices.user_id', '=', 'users.id');
				})
				->leftJoin('lugar_entregas', function($join) {
					$join->on('products.idDeposito', '=', 'lugar_entregas.id');
				})
				->where(function($query) {
					$query->where('items.item_type', 'product')
						  ->where('items.activo', 'Si');
					$query->where(function($subQuery) {
						$subQuery->where('items.allCar', 1)
						->orwhere(function($q) {
								$q->where(function($allCarQuery) {
									$allCarQuery->where('items.allCar', 0)
												->orWhereNull('items.allCar');
								})
								->where('products.stock', '>', 0);
							});
					});
				})
				->orderBy('item_name', 'asc')
				->get();
	
				$lugar_entregas = Lugar_entregas::all();
		return DataTables::of($products)
		->addIndexColumn()
		->addColumn('selection', function ($row) {
		$resultado = "";
					if (!$row->product_id){
						$resultado= '<input name="bank_check" type="checkbox" class="fila-seleccionada" data-id="'.$row->item_id.'">';
					}
         return $resultado;
    })
	->editColumn('nro_oblea', function ($row) {
					if (!$row->product_id){
						return "";
					}	
		
					if (!isset($request->exportar)){
							return '<div class="input-group d-flex justify-content-center">
								<input id="prod_id-'. $row->product_id .'" style="min-width: 10px;max-width: 200px;" type="text" class="form-control" value="'.$row->nro_oblea.'">
								<div class="input-group-append">
									<button type="button"  onClick="ActualizarOblea('.$row->product_id.')" class="btn btn-warning">
									   <i class="ti-check"></i>
									</button>
								</div>
							</div>';
							
							
						}
						 return $row->nro_oblea ?? '';
                })
				->addColumn('id_producto', function ($row) {
					$texto="";
					
					if ($row->invoice_number) {
							$texto=" (" . ($row->invoice_number ?? '') . ")";	
					}
                    return ($row->product_id ?? '') . $texto;
                })
				->editColumn('estado', function ($row) use ($request) {
					if (!$row->product_id) {
						return "";
					}   

					if (!isset($request->exportar)) {
						
						if (strtoupper($row->estado)!="ANULADO") {
						
						return '<div class="input-group d-flex justify-content-center">
							<input id="estado_id-' . $row->product_id . '" style="min-width: 10px; max-width: 200px;" type="text" class="form-control" value="' . e($row->estado) . '">
							<div class="input-group-append">
								<button type="button" onClick="ActualizarCampo(' . $row->product_id . ', \'estado\')" class="btn btn-warning">
									<i class="ti-check"></i>
								</button>
							</div>
						</div>';
						}
					}

					return $row->estado ?? '';
				})
				->editColumn('deposito', function ($row) use ($request, $lugar_entregas) {
					if (!$row->product_id) {
						return "";
					}   

					if (!isset($request->exportar)) {
						$options = '<option value="">Seleccione...</option>';
						//$options ='';
					    foreach ($lugar_entregas as $lugar) {
							$selected = ($row->deposito == $lugar->id || $row->deposito == $lugar->nombre) ? 'selected' : '';
							$options .= '<option value="' . e($lugar->id) . '" ' . $selected . '>' . e($lugar->nombre) . '</option>';
						}
						return '<div class="input-group d-flex justify-content-center">
							<select id="idDeposito_id-' . $row->product_id . '" style="min-width: 120px; max-width: 200px;" class="form-control">
								' . $options . '
							</select>
							<div class="input-group-append">
								<button type="button" onClick="ActualizarCampo(' . $row->product_id . ', \'idDeposito\')" class="btn btn-warning">
									<i class="ti-check"></i>
								</button>
							</div>
						</div>';
					}

					return $row->deposito ?? '';
				})
				->addColumn('stock', function ($row)  {
                    return $row->stock ?? '';
                })
			
				->addColumn('action', function ($row) {
					if (!$row->product_id){
						return "";
					}
                     //$resultado="";
						$resultado = "<form id='form-delete-" . $row->product_id . "' action='" . action('ProductController@destroy', $row->product_id) . "' method='post' class='form-delete-inline'>";
						$resultado .= csrf_field();
						$resultado .= "<input name='_method' type='hidden' value='DELETE'>";
						$resultado .= "<input type='hidden' name='observacion' class='input-observacion'>";

						$resultado .= "<a href='" . action('ProductController@printQR', $row->product_id) . "' class='btn btn-success btn-xs ajax-modal'><i class='fa fa-qrcode' aria-hidden='true'></i></a>";
						$resultado .= "<a href='" . action('ProductController@printsinQR', $row->product_id) . "' class='btn btn-success btn-xs ajax-modal'><i class='fa fa-barcode' aria-hidden='true'></i></a>";

						if ((!$row->invoice_number) && (strtoupper($row->estado)!="ANULADO")) {
							$resultado .= "<button type='button' class='btn btn-danger btn-xs btn-remove-product-item' data-id='" . $row->product_id . "'><i class='ti-eraser'></i></button>";
						}
						
						
						//$result = "<button class='btn btn-success' data-id='$data->id' onClick='toggleStock(this)' >Habilitar</button> ";
						
					$resultado .= "</form>";

                    return $resultado;
                })
				
    ->rawColumns(['selection','action','nro_oblea','estado','deposito']) // Obligatorio para que DataTables dibuje el HTML del input
    ->make(true);

		}		
    }*/
	
	  public function table_detalle_post(Request $request)
    {
		$lockKey = 'create_product_lote_lock_' . auth()->user()->id;
		$lock = Cache::lock($lockKey, 5);

    if (!$lock->get()) {
		$lock->release(); 
        if ($request->ajax()) {
            return response()->json([
                'result' => 'error', 
                'message' => ['El proceso ya fue solicitado, por favor espere unos segundos...']
            ]);
        }
        return redirect()->back()->withErrors(['error' => 'El proceso ya fue solicitado, por favor espere.']);
    }

		
		$validator = Validator::make($request->all(), [
			//'nro_interno' => 'required',
			'nro_interno' =>	 [
				'required',
				'exists:cars,id',
				function ($attribute, $value, $fail) use ($request) {
					$car = \DB::table('cars')->where('id', $value)->first();
						// Verificamos el auto en la BD y el valor que viene desde el formulario
						if ($car && $car->idEstado == 1 && $request->input('estado') == 'desarme-stock') {
							$fail('El vehículo seleccionado no está apto (compactado) (estado no permitido).');
						}
					/* if ($car && $car->idEstado == 1) {
						$fail('El vehículo seleccionado no está disponible (estado no permitido).');
					} */
				},
			],
            'idDeposito' => 'required',
            'estado' => 'required',
            'ubicacion' => 'nullable',
            'description' => 'nullable',
            'idsSeleccionados' => 'required'
		]);

        if ($validator->fails()) {
			$lock->release(); 
            if($request->ajax()){
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('item.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }
		
		


  DB::beginTransaction();
        try {
			
			
		$ids = $request->idsSeleccionados;
		$nro_interno = $request->input('nro_interno', 0);
		$car = Cars::find($nro_interno);

		$productosCreadosIds = [];

if (isset($car)) {
    $estado = $request->input('estado', 'despacho');
    $marca_modelo = $car->idMarca_modelo;
    $operario = Puesto::where('predeterminada', '1')->where('company_id', ($car->company_id ?? company_id()))->first();
    
    $idsString = is_array($ids) ? implode(',', $ids) : $ids;
	$nro_motor= $car->motor_nro ?? '';
	

    if ($estado == 'despacho') {
        $marca_modelo_valor = !empty($car->idMarca_modelo) ? $car->idMarca_modelo : 'NULL';
        
     
        $now = now();

        $sql = "INSERT INTO products (item_id, nro_interno, stock, description, product_price, tax_method, estado, company_id, idDeposito, ubicacion, carga_rapida, user_id, mercado_libre, created_at, marca_modelo,nro_motor)
                SELECT items.id, {$nro_interno}, 1, '" . $request->input('description', '') . "', 0, 'exclusive', '" . $estado . "', " . $car->company_id . ", " . $request->input('idDeposito', 'NULL') . ", '" . $request->input('ubicacion', '') . "', " . $request->input('carga_rapida', 0) . ", " . auth()->user()->id . ", 0, '{$now}', {$marca_modelo_valor} AS marcamodelo,
				CASE WHEN items.id = 1612 THEN '{$nro_motor}' ELSE '' END
                FROM items
                WHERE id IN($idsString)";
        
        DB::statement($sql);
//---------------------
				$productosCreados = Product::where('user_id', auth()->id())
				->where('created_at', $now)
				->whereIn('item_id', explode(',', $idsString))
				->get();

    if ($productosCreados->isNotEmpty()) {
        $auditModel = $productosCreados->first();
        $auditModel->auditEvent = 'created';
        $auditModel->isCustomEvent = true;
        $auditModel->auditCustomOld = [];
        $auditModel->auditCustomNew = [
            'bulk_insert'     => true,
            'total_inserted'  => $productosCreados->count(),
            'inserted_ids'    => $productosCreados->pluck('id')->toArray(),
            'item_ids_source' => explode(',', $idsString),
            'description'     => $request->input('description', ''),
            'idDeposito'      => $request->input('idDeposito'),
            'nro_interno'     => $nro_interno,
        ];
        Event::dispatch(AuditCustom::class, [$auditModel]);

	}
//-----------------

        $productosCreadosIds = DB::table('products')
            ->where('nro_interno', $nro_interno)
            ->where('created_at', $now)
            ->whereIn('item_id', is_array($ids) ? $ids : explode(',', $ids))
            ->pluck('id')
            ->toArray();

    } else {

        $idsArray = is_array($ids) ? $ids : explode(",", $ids);
        $items = Item::whereIn('id', $idsArray)->get();

        foreach ($items as $item) {
            $product = new Product();
            $product->item_id = $item->id;
            $product->car_id = null;
            $product->marca_modelo = $marca_modelo;
            $product->product_price = 0;
            $product->nro_interno = $nro_interno;
            $product->tax_method = 'exclusive';
            $product->description = $request->input('description', '');
            $product->stock = 1;
            $product->estado = $request->input('estado') ?? "desarme-stock";
            $product->company_id = $car->company_id ?? company_id();
            $product->mercado_libre = 0;
            $product->idDeposito = $request->input('idDeposito') ?? null;
            $product->nro_motor = $item->id == "1612" ? $nro_motor : '';
            $product->ubicacion = $request->input('ubicacion') ?? '';
            $product->user_id = auth()->user()->id;
            $product->save();

            $productosCreadosIds[] = $product->id;
             
            $orden_desarme = new Orden_desarme();
            $orden_desarme->idCar = $product->idCar ?? $product->nro_interno;
            $orden_desarme->prioridad = "normal";
            $orden_desarme->interno = ($product->idCar ?? $product->nro_interno);
            $orden_desarme->marca_modelo = $product->marca_modelo;
            $orden_desarme->pieza = $item->id;
            $orden_desarme->product_id = $product->id;
            $orden_desarme->procesar = 1;
            $orden_desarme->idCadete_operario = $operario->user_id ?? 0;
            $orden_desarme->save();
        }
    }   
    
    DB::commit();
}
$lock->release(); 
$stringIdsCreados = implode(',', $productosCreadosIds);

return response()->json([
    'result' => 'success', 
    'message' => _lang('Updated successfully'),
    'ids_creados' => $stringIdsCreados 
]);
			
			/* $ids = $request->idsSeleccionados;//is_array($request->idsSeleccionados) ? $request->idsSeleccionados : [$request->input('idsSeleccionados')];
			 
			 //dd($ids);
			 //$idsArray = is_array($ids) ? $ids : explode(',', $ids);
			 $nro_interno= $request->input('nro_interno',0);
			 
			 $car = Cars::find($nro_interno);
			 	
            if (isset($car)) {
				$estado=$request->input('estado','despacho');
				$marca_modelo=$car->idMarca_modelo;
				$operario = Puesto::where('predeterminada', '1')->where('company_id', ($car->company_id ?? company_id()))->first();
						 
			if ($estado=='despacho'){
				$marca_modelo_valor = !empty($car->idMarca_modelo) ? $car->idMarca_modelo : 'NULL';
				$sql="INSERT INTO products (item_id,nro_interno,stock,description,product_price,tax_method,estado,company_id,idDeposito,ubicacion,carga_rapida,user_id,mercado_libre,created_at,marca_modelo)
				SELECT items.id,{$nro_interno},1,'".$request->input('description','')."',0,'exclusive','".$estado."',".$car->company_id.",".$request->input('idDeposito',null).",'".$request->input('ubicacion','')."',".$request->input('carga_rapida',0).",". auth()->user()->id .",0,NOW(), {$marca_modelo_valor} as marcamodelo FROM items
				WHERE id IN($ids)";
				 DB::statement($sql);
			}else{
				$ids =  explode(",", $ids);
				$items = Item::whereIn('id', $ids)->get();
				foreach ($items as $item) {
					
						 $product = new Product();
						 $product->item_id = $item->id;
						 $product->car_id =  null;
						 $product->marca_modelo = $marca_modelo;
						 $product->product_price = 0;
						 $product->nro_interno = $nro_interno;
						 $product->tax_method = 'exclusive';
						 $product->description = $request->input('description','');
						 $product->stock = 1;
						 $product->estado = $request->input('estado') ?? "desarme-stock";
						 $product->company_id = $car->company_id ?? company_id();
						 $product->mercado_libre = 0;
 						 $product->idDeposito = $request->input('idDeposito') ?? null;
 						 $product->ubicacion = $request->input('ubicacion') ?? '';
						 $product->user_id = auth()->user()->id;
						 $product->save();
						 
						 $orden_desarme = new Orden_desarme();
						 $orden_desarme->idCar = $product->idCar ?? $product->nro_interno;
						 $orden_desarme->prioridad = "normal";
						 $orden_desarme->interno = ($product->idCar ?? $product->nro_interno);
						 $orden_desarme->marca_modelo = $product->marca_modelo;
						 $orden_desarme->pieza = $item->id; //$product->id;
						 $orden_desarme->product_id = $product->id;
						 $orden_desarme->procesar = 1;
						 $orden_desarme->idCadete_operario =  $operario->user_id ?? 0;
 						 $orden_desarme->save();
				}
				
			}	
			  DB::commit();
			
			}
			 
			 
			 
			return response()->json(['result' => 'success', 'message' => _lang('Updated sucessfully')]);*/
			
        } catch (Throwable $e) {
			$lock->release(); 
            DB::rollBack();
             dd($e->getMessage());
			return response()->json(['result' => 'error', 'message' => _lang('Error')]);
            //toast('Error al crear la cotizacione! '.$e->getMessage(), 'error');
        }
    }
	

public function buscar(Request $request): JsonResponse
{
	 $search = $request->input('q');
    $carId = $request->input('nro_interno');
    $currentId = $request->input('current_id');
	
    $query = Item::query()
        ->select('id', 'item_name as text')
        ->where(function ($q) use ($currentId) {
            $q->where('activo', 'Si')
				->where('allCar', 1); 
            $q->when($currentId, fn($query) => $query->orWhere('id', $currentId));
        });
    
    if (empty($search)) {
        $items = $query->orderBy('item_name', 'ASC')
            ->limit(30) 
            ->get(); 
    } else {
        $items = $query->where('item_name', 'LIKE', "%{$search}%")
            ->orderBy('item_name', 'ASC')
            ->get(); 
    }
       
  
    $productsInfo = $this->obtenerInfoProductos($items->pluck('id'), $carId);
    $itemsFormateados = $this->formatearItems($items, $productsInfo);

	return response()->json($itemsFormateados->values()->all());
	//return $itemsFormateados;
	
			/*	$search = $request->input('q');
				$carId = $request->input('nro_interno');
				$currentId = $request->input('current_id'); 


				if (empty($search)) {
					return response()->json(['items' => [], 'more' => false]);
				}
				
				$itemsPaginados = Item::query()
				->select('id', 'item_name as text')
				->where('item_name', 'LIKE', "%{$search}%")
				->where(function ($query) use ($currentId) {
					$query->where('activo', 'Si')
						  ->where('allCar', 1);
					if (!empty($currentId)) {
						$query->orWhere('id', $currentId);
					}
				})
				->orderBy('item_name', 'ASC')
				->paginate(10);
				
							$productsInfo = [];

			if (!empty($carId) && $itemsPaginados->isNotEmpty()) {
				$productsInfo = Product::select('item_id', 'estado', 'id as idproducto') 
					->whereIn('item_id', $itemsPaginados->pluck('id'))
					->where('nro_interno', $carId)
					->get()
					->keyBy('item_id') 
					->toArray();
			}

			$itemsFormateados = $itemsPaginados->getCollection()
				->unique('id') 
				->map(function ($item) use ($productsInfo) {
					$itemId = $item->id;
					
					$product = $productsInfo[$itemId] ?? null; 
					$existeProducto = isset($productsInfo[$itemId]);
					
					$estado = $product['estado'] ?? '';   
					$id_producto = $product['idproducto'] ?? '';
					$mensaje = "";
					$disabledRow = false;

					if ($existeProducto) {
						if ($estado == "Anulado") {
							$mensaje = " ($estado)";
							$disabledRow = true; // Permite volver a seleccionarlo si está Anulado
						} else {
							$mensaje = " ($id_producto)";
							$disabledRow = true;  // Bloqueado para otros estados (ej: Vendido, Activo)
						}
					}	

					return [
						'id' => $itemId,
						'text' => $item->text . $mensaje,
						'disabled' => $disabledRow
					];
				});
				return response()->json([
					'items' => $itemsFormateados->values()->all(),
					'more' => $itemsPaginados->hasMorePages()
				]);*/
}

public function printQR_multi(Request $request)
			{
				$ids = $request->input('idsSeleccionados'); 
				$ids =  explode(",", $ids);
				if (empty($ids) || !is_array($ids)) {
					return response()->json([
						'success' => false,
						'message' => 'No se recibieron identificadores válidos.'
					], 400);
				}

				$productos = Product::whereIn('id', $ids)->get();
				return view('backend.accounting.product.etiquetaQr_mul', compact('productos'))->render();
			}

private function obtenerInfoProductos($itemIds, $carId)
{
    if (empty($carId) || $itemIds->isEmpty()) {
        return collect();
    }

    return Product::select('item_id', 'estado', 'id as idproducto') 
        ->whereIn('item_id', $itemIds)
        ->where('nro_interno', $carId)
        ->get()
        ->keyBy('item_id');
}

/**
 * Formatea los items aplicando las reglas de negocio
 */
private function formatearItems($items, $productsInfo)
{
    return $items->map(function ($item) use ($productsInfo) {
        $mensaje = "";
        $disabledRow = false;
        
        $product = $productsInfo->get($item->id);

        if ($product) {
            $estado = $product->estado ?? '';   
            $id_producto = $product->idproducto ?? '';

            if ($estado === "Anulado") {
                $mensaje = " - $id_producto ($estado)";
                $disabledRow = true; 
            } else {
                $mensaje = " ($id_producto)";
                $disabledRow = true;  
            }
        }	

        return [
            'id'       => $item->id,
            'text'     => $item->text . $mensaje,
            'disabled' => $disabledRow
        ];
    });
}			

}
