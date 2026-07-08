<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\Contact;
use App\Product;
use App\Item;


class Select2Controller extends Controller
{

    public function __construct()
    {
        date_default_timezone_set(get_company_option('timezone', get_option('timezone', 'Asia/Dhaka')));
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
	 
	  public function get_table_data(Request $request)
    {
		
		if ($request->get('where')=="101"){
			return self::SearchCliente($request);
		}
		if ($request->get('where')=="9"){
			return self::SearchProducts($request);
		}
		if ($request->get('where')=="100"){
			return self::SearchItems($request);
		}
		
        $companias_global= empty(session('cia')) ? company_id_arr() : company_id_arr(); company_id_arr();
        
        $data_where = array(
            '1' => array(), //general company Data
            '2' => array( 'item_type' => 'product'), //Item Type Product
            '3' => array('company_id' => company_id(), 'type' => 'income'), //Income Category
            '4' => array('company_id' => company_id(), 'type' => 'expense'), //Expense Category
            '5' => array('company_id' => company_id(), 'item_type' => 'service'), //Item Type Service
            '6' => array( 'staff_roles.name' => 'transportista'), //roles.
            '7' => array( 'staff_roles.name' => 'tramitador'), //roles.
            //8, // marca y modelo
            '9' => array('item_type' => 'product'), //Item Type Product Piezas $display_option
            '100' => array('item_type' => 'product','activo' => 'Si'), //Item Type Product Piezas $display_option
        );

        

        $table = $request->get('table');
        $value = $request->get('value');
        $display = $request->get('display');
        $display2 = $request->get('display2');
        $display3 = $request->get('display3') ?? '';
        $where = $request->get('where');
        $option = $request->get('option', false);
        $company = $request->get('company', false);

        $q = $request->get('q');
		

        $display_option = "$display as text";
        if ($display2 != '') {
            $display_option = "CONCAT($display,' - ',$display2) AS text";
        }

        //IF(cars.company_id = 1, CONCAT('PM',products.tipo_vehiculo,'-',LPAD(cars.id, 7, '0')), CONCAT('PC-',tipo_vehiculo,'-',LPAD(cars.id, 7, '0') ))

        if ($display2 != '' && $display3 != '') {
            if ($where == 9) {
                $display_option = "CONCAT('Codigo ',products.id,' - interno : ',IF(cars.company_id = 1, CONCAT('PM',COALESCE(cars.tipo_vehiculo,''),'-',LPAD(cars.id, 7, '0')), CONCAT('PC',COALESCE(cars.tipo_vehiculo,''),'-',LPAD(cars.id, 7, '0') )),' - ', IFNULL(CONCAT('Motor :',nro_motor,' - ') ,''), ' ' ,$display,' - ',$display2,' - ',$display3) AS text";
            } else {
                $display_option = "CONCAT($display,' - ',$display2,' - ',$display3) AS text";
            }

        }

        //    dd($display_option);

        if ($where != '') {
            $result = DB::table($table)
                ->select("$value as id", DB::raw($display_option))
                ->where($display, 'LIKE', "$q%");


            if ($where == 6 || $where == 7) {


                return $result->join('staff_roles', 'users.role_id', '=', 'staff_roles.id')->where($data_where[$where])->get();
            } else if ($where == 8) {
                //dd($company);
                if ($company != 'undefined' && $company) {
                    $display_option .= ", IF(cars.company_id = " . $company . ", false, true) as disabled";
                }
                // $display_option .= ", IF(cars.company_id = 1, CONCAT('PM-',cars.id), CONCAT('PC-',cars.id)) as id";
                $result = DB::table($table)
                    ->select("$value as id", DB::raw($display_option));

                $display3 = $display3 == '' ? 'modelos.modelo' : $display3;
                // devolver vehiculo marca modelo
                return $result->leftJoin('marca_modelos', 'cars.idMarca_modelo', '=', 'marca_modelos.id')->leftJoin(
                    'marcas',
                    'marca_modelos.idMarca',
                    '=',
                    'marcas.id'
                )->leftJoin(
                        'modelos',
                        'marca_modelos.idModelo',
                        '=',
                        'modelos.id'
                    )->where(function ($q) {
                        $q->where('cars.idEstado', 5)->orwhere('cars.idEstado', 1)->orwhere('cars.idEstado', 6)->orwhere('cars.idEstado', 8);
                    })->where(function ($c) use ($display2, $display3, $q) {
                        $c->orWhere('marcas.marca', 'LIKE', "$q%")->orWhere('modelos.modelo', 'LIKE', "$q%")->orWhere('cars.id', 'LIKE', "%$q%");
                    })
                    //     ->where
                    // ($data_where[1])
                    ->get();
            } elseif ($where == 9) {


                if ($option && $option != 'undefined') {


                    return $result->whereRaw($option)->join('items', 'products.item_id', '=', 'items.id')->where('products.stock', 1)->where(function ($q) {
                        $q->where('products.estado', '!=', 'descompuesto')->orWhereNull('products.estado');
                    })->whereRaw('products.id NOT IN (SELECT quotation_items.product_id from quotation_items)')
                    // ->leftJoin('quotations_items', 'products.id', '!=', 'quotations_items.product_id')
                        ->leftJoin('marca_modelos', 'products.marca_modelo', '=', 'marca_modelos.id')->leftJoin(
                            'marcas',
                            'marca_modelos.idMarca',
                            '=',
                            'marcas.id'
                        )->leftJoin(
                            'modelos',
                            'marca_modelos.idModelo',
                            '=',
                            'modelos.id'
                        )->leftJoin('cars', 'cars.id', '=', 'products.nro_interno')    
                        ->where(function ($str) use ($display3, $display2, $q, $display) {
                            $str->orwhere($display, 'LIKE', "$q%");
                            $str->orwhere('products.nro_interno', 'LIKE', "$q%");
                            $str->orwhere('nro_motor', 'LIKE', "$q%");
                            $str->orwhere('products.id', '=', "$q%");
                        })->whereIn("products.company_id",$companias_global)->get();
                }

                if ($company != 'undefined' && $company) {
                   // $display_option .= ", IF(items.company_id = " . $company . ", false, true) as disabled";
                    $display_option .= ", IF(products.company_id = " . $company . ", false, true) as disabled";
                }

                $result = DB::table($table)
                    ->select("$value as id", DB::raw($display_option));
                return $result->where
                ('products.allCar', null)->join('items', 'products.item_id', '=', 'items.id')->where('products.stock', 1)->where(function ($q) {
                    $q->where('products.estado', '!=', 'descompuesto')->orWhereNull('products.estado');
                })
                    // ->where('products.car_id', null)
                    ->leftJoin('marca_modelos', 'products.marca_modelo', '=', 'marca_modelos.id')->leftJoin(
                        'marcas',
                        'marca_modelos.idMarca',
                        '=',
                        'marcas.id'
                    )->leftJoin(
                        'modelos',
                        'marca_modelos.idModelo',
                        '=',
                        'modelos.id'
                    )->leftJoin('cars', 'cars.id', '=', 'products.nro_interno')
                    ->where(function ($str) use ($display3, $display2, $q, $display) {
                        $str->orWhere(
                            'marcas.marca',
                            'LIKE',
                            "$q%"
                        )->orWhere
                            ('modelos.modelo', 'LIKE', "$q%")->orwhere($display, 'LIKE', "$q%");


                        $str->orwhere('products.nro_interno', 'LIKE', "$q%");
                        $str->orwhere('nro_motor', 'LIKE', "$q%");
                        $str->orwhere('products.id', '=', "$q%");
                    })->whereIn("products.company_id",$companias_global)->get();




            } elseif ($where == 10) {

                if ($company != 'undefined' && $company) {
                    $display_option .= ", IF(cars.company_id = " . $company . ", false, true) as disabled";
                }
                // $display_option .= ", IF(cars.company_id = 1, CONCAT('PM-',cars.id), CONCAT('PC-',cars.id)) as id";
                $result = DB::table($table)
                    ->select("$value as id", DB::raw($display_option));

                $display3 = $display3 == '' ? 'modelos.modelo' : $display3;
                // devolver vehiculo marca modelo
                return $result->leftJoin('marca_modelos', 'cars.idMarca_modelo', '=', 'marca_modelos.id')->leftJoin(
                    'marcas',
                    'marca_modelos.idMarca',
                    '=',
                    'marcas.id'
                )->leftJoin(
                        'modelos',
                        'marca_modelos.idModelo',
                        '=',
                        'modelos.id'
                    )->where(function ($q) {
                        $q->where('cars.idEstado', '!=', 5)->orwhere('cars.idEstado', '!=', 6)->orwhere('cars.idEstado', 8);
                    })->where(function ($c) use ($display2, $display3, $q) {
                        $c->orWhere($display2, 'LIKE', "$q%")->orWhere($display3, 'LIKE', "$q%")->orWhere('cars.id', 'LIKE', "%$q%");
                    })
                    //     ->where
                    // ($data_where[1])
                    ->get();


            } elseif ($where == 11) {
                //dd($company);
                if ($company != 'undefined' && $company) {
                    $display_option .= ", IF(cars.company_id = " . $company . ", false, true) as disabled";
                }
                // $display_option .= ", IF(cars.company_id = 1, CONCAT('PM-',cars.id), CONCAT('PC-',cars.id)) as id";
                $result = DB::table($table)
                    ->select("$value as id", DB::raw($display_option));

                $display3 = $display3 == '' ? 'modelos.modelo' : $display3;
                // devolver vehiculo marca modelo
                return $result->leftJoin('marca_modelos', 'cars.idMarca_modelo', '=', 'marca_modelos.id')->leftJoin(
                    'marcas',
                    'marca_modelos.idMarca',
                    '=',
                    'marcas.id'
                )->leftJoin(
                        'modelos',
                        'marca_modelos.idModelo',
                        '=',
                        'modelos.id'
                    )->where(function ($q) use ($companias_global) {
                        $q->whereIn('cars.idEstado', [1,5,6,8,12]);
						$q->whereIn("cars.company_id",$companias_global);
                        //$q->where('cars.idEstado', 5)->orwhere('cars.idEstado', 6)->orwhere('cars.idEstado', 8);
                        //$q->where('cars.idEstado', 5)->orwhere('cars.idEstado', 1)->orwhere('cars.idEstado', 6)->orwhere('cars.idEstado', 8);
                    })->where(function ($c) use ($display2, $display3, $q) {
                        $c->orWhere('marcas.marca', 'LIKE', "$q%")->orWhere('modelos.modelo', 'LIKE', "$q%")->orWhere('cars.id', 'LIKE', "%$q%");
                    })
                    //     ->where
                    // ($data_where[1])
                    ->get();
            } elseif ($where == 101) {
                    return $result->whereIn("company_id",$companias_global)->orderBy("text")->get();
                    }
            else {

                return $result->where($data_where[$where])->orderBy("text")->get();
            }
        } else {
            $result = DB::table($table)
                ->select("$value as id", DB::raw($display_option))
                ->where(function($c) use ($q,$display,$display2) {
                    $c->where($display, 'LIKE', "%$q%");
                    if($display2 != '') {
                        $c->orWhere($display2, 'LIKE', "%$q%");
                    }
                })
                ->get();
        }
		
        return $result;
    }
	
	private function SearchCliente(Request $request)
{
    $search = $request->input('q');
    $companias_global = company_id_arr(); 
    
    if (empty($search)) {
        return Contact::query()
            ->select('id', DB::raw("CONCAT('[', IFNULL(dni_cuit, 'Sin DNI'), '] ', IFNULL(contact_name, 'Sin nombre')) AS text"))
            ->whereIn("company_id", $companias_global)
            ->orderBy('contact_name', 'ASC')
            ->limit(30)
            ->get();
    }
    
    
    $result = Contact::query()
        ->select(
            'id', 
            DB::raw("CONCAT('[', IFNULL(dni_cuit, 'Sin DNI'), '] ', IFNULL(contact_name, 'Sin nombre')) AS text")
        )
        ->where(function ($query) use ($search) {
            $query->where('dni_cuit', 'LIKE', '%' . $search . '%')
                  ->orWhere('contact_name', 'LIKE', '%' . $search . '%');
        })
        ->whereIn("company_id", $companias_global)
        ->orderBy('contact_name', 'ASC')
        ->get();
        
    return $result;
}
	
	
	private function SearchProducts(Request $request)
{
    $search = $request->input('q');
    $company = company_id(); 
    $companias_global = company_id_arr(); 

    $rawText = "
    CONCAT(
        'IdProducto ', products.id, 
        ' - Interno : ', 
        IF(cars.company_id = 1, 
            CONCAT('PM', COALESCE(cars.tipo_vehiculo,''), '-', LPAD(cars.id, 10, '0')), 
            CONCAT('PC', COALESCE(cars.tipo_vehiculo,''), '-', LPAD(cars.id, 10, '0'))
        ), 
        ' - ', 
        items.item_name,
        IFNULL(CONCAT(' - ','Motor : ', cars.motor_nro), ''), 
        IFNULL(CONCAT(' - ',marcas.marca), ''), 
        IFNULL(CONCAT(' / ',modelos.modelo), '')
    )";

    $selectArray = [
        'products.id',
        DB::raw("($rawText) AS text")
    ];

    if (!empty($company) && $company !== 'undefined') {
        $selectArray[] = DB::raw("IF(products.company_id = " . intval($company) . ", false, true) as disabled");
    }
//$query->whereNotIn('products.estado', ['desarme', 'desarme-stock'])
    $query = Product::query()
        ->select($selectArray)
        ->leftJoin('items', 'products.item_id', '=', 'items.id')
        ->leftJoin('marca_modelos', 'products.marca_modelo', '=', 'marca_modelos.id')
        ->leftJoin('marcas', 'marca_modelos.idMarca', '=', 'marcas.id')
        ->leftJoin('modelos', 'marca_modelos.idModelo', '=', 'modelos.id')
        ->leftJoin('cars', 'cars.id', '=', 'products.nro_interno')
        ->whereNull('products.allCar')
        ->where('products.stock', 1)
        ->where(function ($q) {
            //$q->where('products.estado', '!=', 'descompuesto')
				$q->whereNotIn('products.estado', ['desarme', 'desarme-stock','descompuesto'])
              ->orWhereNull('products.estado');
        })
        ->whereIn("products.company_id", $companias_global);

    if (empty($search)) {
        $result = $query->limit(30)
            ->groupBy('products.id')
            ->orderBy('products.id', 'ASC')
            ->get();
    } else {
        $query->where(function ($str) use ($search) {
            $str->orWhere('marcas.marca', 'LIKE', "%$search%")
                ->orWhere('modelos.modelo', 'LIKE', "%$search%")
                ->orWhere('products.nro_interno', 'LIKE', "%$search%")
                ->orWhere('cars.motor_nro', 'LIKE', "%$search%")
                ->orWhere('products.id', 'LIKE', "%$search%");
        });
        
        $result = $query->groupBy('products.id')
            ->orderBy('products.id', 'ASC')
            ->get();
    }

    return $result;
}


public function SearchItems(Request $request)
{
    $search = $request->input('q');
    $carId = $request->input('car_id');
    $currentId = $request->input('current_id');
    
    // 1. Creamos la consulta base
    $query = Item::query()
        ->select('id', 'item_name as text')
        ->where(function ($q) use ($currentId) {
            $q->where('activo', 'Si');
            // ->where('allCar', 1); // (Comentado según tu código)
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

	return $itemsFormateados;
}
/**
 * Extrae la información de productos para evitar N+1
 */
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


	/*
	
    public function get_table_data_old(Request $request)
    {
        $companias_global= empty(session('cia')) ? company_id_arr() : company_id_arr(); company_id_arr();
        
        $data_where = array(
            '1' => array(), //general company Data
            '2' => array( 'item_type' => 'product'), //Item Type Product
            '3' => array('company_id' => company_id(), 'type' => 'income'), //Income Category
            '4' => array('company_id' => company_id(), 'type' => 'expense'), //Expense Category
            '5' => array('company_id' => company_id(), 'item_type' => 'service'), //Item Type Service
            '6' => array( 'staff_roles.name' => 'transportista'), //roles.
            '7' => array( 'staff_roles.name' => 'tramitador'), //roles.
            //8, // marca y modelo
            '9' => array('item_type' => 'product'), //Item Type Product Piezas $display_option
            '100' => array('item_type' => 'product','activo' => 'Si'), //Item Type Product Piezas $display_option
        );

        

        $table = $request->get('table');
        $value = $request->get('value');
        $display = $request->get('display');
        $display2 = $request->get('display2');
        $display3 = $request->get('display3') ?? '';
        $where = $request->get('where');
        $option = $request->get('option', false);
        $company = $request->get('company', false);

        $q = $request->get('q');

        $display_option = "$display as text";
        if ($display2 != '') {
            $display_option = "CONCAT($display,' - ',$display2) AS text";
        }

        //IF(cars.company_id = 1, CONCAT('PM',products.tipo_vehiculo,'-',LPAD(cars.id, 7, '0')), CONCAT('PC-',tipo_vehiculo,'-',LPAD(cars.id, 7, '0') ))

        if ($display2 != '' && $display3 != '') {
            if ($where == 9) {
                $display_option = "CONCAT('Codigo ',products.id,' - interno : ',IF(cars.company_id = 1, CONCAT('PM',COALESCE(cars.tipo_vehiculo,''),'-',LPAD(cars.id, 7, '0')), CONCAT('PC',COALESCE(cars.tipo_vehiculo,''),'-',LPAD(cars.id, 7, '0') )),' - ', IFNULL(CONCAT('Motor :',nro_motor,' - ') ,''), ' ' ,$display,' - ',$display2,' - ',$display3) AS text";
            } else {
                $display_option = "CONCAT($display,' - ',$display2,' - ',$display3) AS text";
            }

        }

        //    dd($display_option);

        if ($where != '') {
            $result = DB::table($table)
                ->select("$value as id", DB::raw($display_option))
                ->where($display, 'LIKE', "$q%");


            if ($where == 6 || $where == 7) {


                return $result->join('staff_roles', 'users.role_id', '=', 'staff_roles.id')->where($data_where[$where])->get();
            } else if ($where == 8) {
                //dd($company);
                if ($company != 'undefined' && $company) {
                    $display_option .= ", IF(cars.company_id = " . $company . ", false, true) as disabled";
                }
                // $display_option .= ", IF(cars.company_id = 1, CONCAT('PM-',cars.id), CONCAT('PC-',cars.id)) as id";
                $result = DB::table($table)
                    ->select("$value as id", DB::raw($display_option));

                $display3 = $display3 == '' ? 'modelos.modelo' : $display3;
                // devolver vehiculo marca modelo
                return $result->leftJoin('marca_modelos', 'cars.idMarca_modelo', '=', 'marca_modelos.id')->leftJoin(
                    'marcas',
                    'marca_modelos.idMarca',
                    '=',
                    'marcas.id'
                )->leftJoin(
                        'modelos',
                        'marca_modelos.idModelo',
                        '=',
                        'modelos.id'
                    )->where(function ($q) {
                        $q->where('cars.idEstado', 5)->orwhere('cars.idEstado', 1)->orwhere('cars.idEstado', 6)->orwhere('cars.idEstado', 8);
                    })->where(function ($c) use ($display2, $display3, $q) {
                        $c->orWhere('marcas.marca', 'LIKE', "$q%")->orWhere('modelos.modelo', 'LIKE', "$q%")->orWhere('cars.id', 'LIKE', "%$q%");
                    })
                    //     ->where
                    // ($data_where[1])
                    ->get();
            } elseif ($where == 9) {


                if ($option && $option != 'undefined') {


                    return $result->whereRaw($option)->join('items', 'products.item_id', '=', 'items.id')->where('products.stock', 1)->where(function ($q) {
                        $q->where('products.estado', '!=', 'descompuesto')->orWhereNull('products.estado');
                    })->whereRaw('products.id NOT IN (SELECT quotation_items.product_id from quotation_items)')
                    // ->leftJoin('quotations_items', 'products.id', '!=', 'quotations_items.product_id')
                        ->leftJoin('marca_modelos', 'products.marca_modelo', '=', 'marca_modelos.id')->leftJoin(
                            'marcas',
                            'marca_modelos.idMarca',
                            '=',
                            'marcas.id'
                        )->leftJoin(
                            'modelos',
                            'marca_modelos.idModelo',
                            '=',
                            'modelos.id'
                        )->leftJoin('cars', 'cars.id', '=', 'products.nro_interno')    
                        ->where(function ($str) use ($display3, $display2, $q, $display) {
                            $str->orwhere($display, 'LIKE', "$q%");
                            $str->orwhere('products.nro_interno', 'LIKE', "$q%");
                            $str->orwhere('nro_motor', 'LIKE', "$q%");
                            $str->orwhere('products.id', '=', "$q%");
                        })->whereIn("products.company_id",$companias_global)->get();
                }

                if ($company != 'undefined' && $company) {
                   // $display_option .= ", IF(items.company_id = " . $company . ", false, true) as disabled";
                    $display_option .= ", IF(products.company_id = " . $company . ", false, true) as disabled";
                }

                $result = DB::table($table)
                    ->select("$value as id", DB::raw($display_option));
                return $result->where
                ('products.allCar', null)->join('items', 'products.item_id', '=', 'items.id')->where('products.stock', 1)->where(function ($q) {
                    $q->where('products.estado', '!=', 'descompuesto')->orWhereNull('products.estado');
                })
                    // ->where('products.car_id', null)
                    ->leftJoin('marca_modelos', 'products.marca_modelo', '=', 'marca_modelos.id')->leftJoin(
                        'marcas',
                        'marca_modelos.idMarca',
                        '=',
                        'marcas.id'
                    )->leftJoin(
                        'modelos',
                        'marca_modelos.idModelo',
                        '=',
                        'modelos.id'
                    )->leftJoin('cars', 'cars.id', '=', 'products.nro_interno')
                    ->where(function ($str) use ($display3, $display2, $q, $display) {
                        $str->orWhere(
                            'marcas.marca',
                            'LIKE',
                            "$q%"
                        )->orWhere
                            ('modelos.modelo', 'LIKE', "$q%")->orwhere($display, 'LIKE', "$q%");


                        $str->orwhere('products.nro_interno', 'LIKE', "$q%");
                        $str->orwhere('nro_motor', 'LIKE', "$q%");
                        $str->orwhere('products.id', '=', "$q%");
                    })->whereIn("products.company_id",$companias_global)->get();




            } elseif ($where == 10) {

                if ($company != 'undefined' && $company) {
                    $display_option .= ", IF(cars.company_id = " . $company . ", false, true) as disabled";
                }
                // $display_option .= ", IF(cars.company_id = 1, CONCAT('PM-',cars.id), CONCAT('PC-',cars.id)) as id";
                $result = DB::table($table)
                    ->select("$value as id", DB::raw($display_option));

                $display3 = $display3 == '' ? 'modelos.modelo' : $display3;
                // devolver vehiculo marca modelo
                return $result->leftJoin('marca_modelos', 'cars.idMarca_modelo', '=', 'marca_modelos.id')->leftJoin(
                    'marcas',
                    'marca_modelos.idMarca',
                    '=',
                    'marcas.id'
                )->leftJoin(
                        'modelos',
                        'marca_modelos.idModelo',
                        '=',
                        'modelos.id'
                    )->where(function ($q) {
                        $q->where('cars.idEstado', '!=', 5)->orwhere('cars.idEstado', '!=', 6)->orwhere('cars.idEstado', 8);
                    })->where(function ($c) use ($display2, $display3, $q) {
                        $c->orWhere($display2, 'LIKE', "$q%")->orWhere($display3, 'LIKE', "$q%")->orWhere('cars.id', 'LIKE', "%$q%");
                    })
                    //     ->where
                    // ($data_where[1])
                    ->get();


            } elseif ($where == 11) {
                //dd($company);
                if ($company != 'undefined' && $company) {
                    $display_option .= ", IF(cars.company_id = " . $company . ", false, true) as disabled";
                }
                // $display_option .= ", IF(cars.company_id = 1, CONCAT('PM-',cars.id), CONCAT('PC-',cars.id)) as id";
                $result = DB::table($table)
                    ->select("$value as id", DB::raw($display_option));

                $display3 = $display3 == '' ? 'modelos.modelo' : $display3;
                // devolver vehiculo marca modelo
                return $result->leftJoin('marca_modelos', 'cars.idMarca_modelo', '=', 'marca_modelos.id')->leftJoin(
                    'marcas',
                    'marca_modelos.idMarca',
                    '=',
                    'marcas.id'
                )->leftJoin(
                        'modelos',
                        'marca_modelos.idModelo',
                        '=',
                        'modelos.id'
                    )->where(function ($q) use ($companias_global) {
                        $q->whereIn('cars.idEstado', [1,5,6,8,12]);
						$q->whereIn("cars.company_id",$companias_global);
                        //$q->where('cars.idEstado', 5)->orwhere('cars.idEstado', 6)->orwhere('cars.idEstado', 8);
                        //$q->where('cars.idEstado', 5)->orwhere('cars.idEstado', 1)->orwhere('cars.idEstado', 6)->orwhere('cars.idEstado', 8);
                    })->where(function ($c) use ($display2, $display3, $q) {
                        $c->orWhere('marcas.marca', 'LIKE', "$q%")->orWhere('modelos.modelo', 'LIKE', "$q%")->orWhere('cars.id', 'LIKE', "%$q%");
                    })
                    //     ->where
                    // ($data_where[1])
                    ->get();
            } elseif ($where == 101) {
                    return $result->whereIn("company_id",$companias_global)->orderBy("text")->get();
                    }
            else {

                return $result->where($data_where[$where])->orderBy("text")->get();
            }
        } else {
            $result = DB::table($table)
                ->select("$value as id", DB::raw($display_option))
                ->where(function($c) use ($q,$display,$display2) {
                    $c->where($display, 'LIKE', "%$q%");
                    if($display2 != '') {
                        $c->orWhere($display2, 'LIKE', "%$q%");
                    }
                })
                ->get();
        }

        return $result;
    }
*/
}
