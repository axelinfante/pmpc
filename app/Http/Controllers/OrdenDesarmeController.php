<?php

namespace App\Http\Controllers;

use App\Historial_ordenes_desarme;
use App\Invoice;
use App\Lugar_entregas;
use App\Mail\AlertNotificationMail;
use App\Mail\OrdenDesarmeNotificacion;
use App\Notifications\OrdenUpdated;
use App\Orden_desarme;
use App\Quotation;
use App\Role;
use App\User;
use App\Item;
use App\Product;
use App\Puesto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Yajra\DataTables\Facades\DataTables;
use App\Cars;
use App\CompanySetting;
use PDF;
use Illuminate\Support\Facades\Validator;


use ZipArchive;
use Illuminate\Support\Facades\Storage;

use App\OrdenDespacho;
use App\InvoiceItem;



class OrdenDesarmeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
	 protected $opciones_puestos = ['1C', '1P', '2C', '2P', '3', '4C', '4P','GENERAL']; 
	 
    public function index()
    {
        if (strtolower(auth()->user()->role->name) == 'operario' || strtolower(auth()->user()->role->name) == 'cadete') {
            return redirect(route('list_operarios'));
        }
        if (strtolower(auth()->user()->role->name) == 'vendedor') {
            return view('backend.accounting.desarme.listVendedor');
        }
		$company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
		//$company = \App\Company::where('business_name', 'Pentacar')->orwhere('business_name', 'Paternal')->get();
		$company_id=count($company_id) > 1 ? array(1,2):$company_id;
		$company = \App\Company::whereIn('id', $company_id)->get();
		$usuario = \App\User::whereIn('company_id', $company_id)->get();
        return view('backend.accounting.desarme.list',compact('company_id','company','usuario'));
    }

    public function index_operario()
    {
        $lugar_entregas = Lugar_entregas::all();
        return view('backend.accounting.desarme.list_operario',["lugar_entregas" => $lugar_entregas]);
    }


    public function historial()
    {
        //

        return view('backend.accounting.desarme.list_historial');
    }

    public function crear($id = false, $tipo = false)
    {
        if ($tipo == 1) {
            //cotizacion
            $q = Quotation::where(['id' => $id])->first();
        } else {
            //venta
            $q = Invoice::where(['id' => $id])->first();
        }
        return view('backend.accounting.desarme.modal.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //

        $orden_desarme = new Orden_desarme();
        $orden_desarme->pedido_pasado =  $request->input('pedido_pasado');
        $orden_desarme->prioridad = $request->input('prioridad');
        $orden_desarme->interno =  $request->input('interno');
        $orden_desarme->id_cotizacion =  $request->input('id_cotizacion');
        $orden_desarme->id_venta =  $request->input('id_venta');
        $orden_desarme->fecha_venta =  $request->input('fecha_venta');
        $orden_desarme->lugar_venta =  $request->input('lugar_venta');
        $orden_desarme->marca_modelo =  $request->input('marca_modelo');
        $orden_desarme->pieza =  $request->input('pieza');
        $orden_desarme->detalle_pieza =  $request->input('detalle_pieza');
        $orden_desarme->detalle_anulado =  $request->input('detalle_anulado');
        $orden_desarme->ubicacion =  $request->input('ubicacion');
        $orden_desarme->estado =  $request->input('estado');
        $orden_desarme->autorizo =  $request->input('autorizo');
        $orden_desarme->fecha_estimada_pieza_disponible =  $request->input('fecha_estimada_pieza_disponible');
        $orden_desarme->existe =  $request->input('existe');
        $orden_desarme->falta =  $request->input('falta');
        $orden_desarme->informo_ausencia =  $request->input('informo_ausencia');
        $orden_desarme->obs_desarme_busqueda =  $request->input('obs_desarme_busqueda');
        $orden_desarme->fecha_desarmado_anulado =  $request->input('fecha_desarmado_anulado');
        $orden_desarme->cargando_camioneta =  $request->input('cargando_camioneta');
        $orden_desarme->entregado =  $request->input('entregado');
        $orden_desarme->fecha_embalado =  $request->input('fecha_embalado');
        $orden_desarme->fecha_avisado_vendedor =  $request->input('fecha_avisado_vendedor');
        $orden_desarme->f_ingreso_puesto =  $request->input('f_ingreso_puesto');
        $orden_desarme->idCar =  $request->input('idCar');

        $orden_desarme->save();
    }

    public function get_table_data_operario(Request $request)
    {
        $ordenes = Orden_desarme::select('ordenes_desarme.*')
            ->with('venta')
            //->with('aseguradoras')
            ->with('cotizacion')
            ->whereHas('car', function ($str) {
                if (strtolower(auth()->user()->role->name) == 'operario' || strtolower(auth()->user()->role->name) == 'cadete' || strtolower(auth()->user()->role->name) == 'administrativo de desarme') { //
                    //dd(auth()->user()->company_id);
                    $str->where('company_id', auth()->user()->company_id);
                }
                $str->where(function ($row) {
                    // $row-> where('idEstado',6)->orwhere('idEstado',5)->orwhere('idEstado',8);
                    //$row->where('idEstado', '!=', 1);
                });
            });
        //dd(strtolower(auth()->user()->role->name) == 'vendedor');
        if (strtolower(auth()->user()->role->name) == 'vendedor') {

            $ordenes->whereHas('venta', function ($str) {
                $str->where('user_id', '=', auth()->id());
            });
        }
        //dd(auth()->user()->location);
        $ocultar = '';
        if (strtolower(auth()->user()->role->name) == 'operario' || strtolower(auth()->user()->role->name) == 'cadete') {

            $ordenes->where('ubicacion', auth()->user()->location);
            $ordenes->where('procesar', 1);
            $ocultar = 'd-none';
        }




        //->where('company_id', company_id());
        //->orderBy("projects.id","desc");


        return DataTables::eloquent($ordenes)
            ->filter(function ($query) use ($request) {
                //                            if ($request->has('cliente')) {
                //                                $query->where('cliente', 'like', "%{$request->post('cliente')}%");
                //                            }
                //
                //                if ($request->has('status')) {
                //                    $query->whereHas('estado',function($s) use ($request) {
                //                        $s->whereIn('id', json_decode($request->post('status')));
                //                    });
                //                }

            })

            ->filterColumn('ubicacion', function ($query, $keyword) {

                $query->orwhereHas('lugares', function ($str) use ($keyword) {
                    $str->where('nombre', 'like', "%{$keyword}%");
                });
            })

            //            ->editColumn('pedido_pasado', function ($orden) {
            //                return $orden->pedido_pasado;
            //            })
            //            ->editColumn('prioridad', function ($orden) {
            //
            //                return $orden->prioridad;
            //            })
            ->editColumn('interno', function ($orden) {

                return $orden->interno ?? null;
            })
            //            ->editColumn('cotizacion', function ($orden) {
            //
            //                return $orden->cotizacion->quotation_number ?? null;
            //            })
            ->editColumn('venta', function ($orden) {
                return $orden->venta->invoice_number ?? null;
            })
            ->editColumn('fecha_venta', function ($orden) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->fecha_venta) ? date($date_format, strtotime($orden->fecha_venta)) : null;
            })
            //            ->editColumn('lugar_venta', function ($orden) {
            //                return $orden->lugar_venta;
            //            })
            ->editColumn('marca_modelo', function ($orden) {
                //dd($orden->producto->marcaModelo);
                return ($orden->producto->marcaModelo->marca->marca ?? '') . ' ' .
                    ($orden->producto->marcaModelo->modelo->modelo ?? '');
            })
            ->editColumn('pieza', function ($orden) {
                return $orden->producto->item->item_name ?? null;
            })
            ->editColumn('detalle_pieza', function ($orden) {
                return $orden->detalle_pieza;
            })
            ->editColumn('detalle_anulado', function ($orden) {
                return $orden->detalle_anulado;
            })
            //            ->editColumn('cliente', function ($orden) {
            ////                if ($orden->cotizacion->id == 27)
            ////                {
            ////                    dd($orden->cotizacion);
            ////                }
            //                //
            //                return $orden->cotizacion->client->contact_name ?? $orden->venta->client->contact_name;
            //            })
            ->editColumn('vendedor', function ($orden) {
                // dd($orden->venta->vendedor->name);
                return ($orden->cotizacion->vendedor->name ?? null) ?? ($orden->venta->vendedor->name ?? '');
            })
            //            ->editColumn('ubicacion', function ($orden) {
            //                return $orden->lugares->nombre;
            //            })
            ->editColumn('estado', function ($orden) {
                return $orden->estado;
            })
            //            ->editColumn('autorizo', function ($orden) {
            //                return $orden->autorizo;
            //            })
            //            ->editColumn('fecha_estimada_pieza_disponible', function ($orden)  {
            //
            //                $date_format = get_company_option('date_format','Y-m-d');
            //                return isset($orden->fecha_estimada_pieza_disponible) ? date($date_format, strtotime
            //                ($orden->fecha_estimada_pieza_disponible)) : null;
            //            })
            //            ->editColumn('existe', function ($orden) {
            //                return $orden->existe;
            //            })
            //            ->editColumn('falta', function ($orden) {
            //                return $orden->falta;
            //            })
            ->editColumn('informo_ausencia', function ($orden) {
                return $orden->informo_ausencia;
            })->editColumn('obs_desarme_busqueda', function ($orden) {
                return $orden->obs_desarme_busqueda;
            })
            //            ->editColumn('fecha_desarmado_anulado', function ($orden) {
            //                $date_format = get_company_option('date_format','Y-m-d');
            //                return isset($orden->fecha_desarmado_anulado) ? date($date_format, strtotime
            //                ($orden->fecha_desarmado_anulado)) : null;
            //
            //            })
            //            ->editColumn('cargando_camioneta', function ($orden) {
            //                return $orden->cargando_camioneta;
            //            })->editColumn('entregado', function ($orden) {
            //                return $orden->entregado ;
            //            })
            //            ->editColumn('fecha_embalado', function ($orden) {
            //                $date_format = get_company_option('date_format','Y-m-d');
            //                return isset($orden->fecha_embalado) ? date($date_format, strtotime
            //                ($orden->fecha_embalado)) : null;
            //
            //            })
            //
            //            ->editColumn('fecha_avisado_vendedor', function ($orden) {
            //                $date_format = get_company_option('date_format','Y-m-d');
            //                return isset($orden->fecha_avisado_vendedor) ? date($date_format, strtotime
            //                ($orden->fecha_avisado_vendedor)) : null;
            //
            //            })
            ->addColumn('action', function ($orden) use ($ocultar) {
                return '<form action="' . action('OrdenDesarmeController@destroy', $orden['id']) . '" class="text-center" method="post">'

                    . '<a href="' . action('OrdenDesarmeController@edit', $orden['id']) . '"
data-title="' . _lang('Update Vehicle') . '" class="btn btn-warning btn-xs ajax-modal"><i class="ti-pencil"></i></a>&nbsp;'

                    . csrf_field()
                    . '<input name="_method" type="hidden" value="DELETE">'
                    . '<button
class="btn btn-danger btn-xs btn-remove ' . $ocultar . '" type="submit"><i class="ti-eraser"></i></button>'
                    . '</form>';
            })
            ->setRowId(function ($orden) {
                return "row_" . $orden->id;
            })
            ->rawColumns(['action', 'members.name', 'status', 'id'])
            ->make(true);
    }


  /*  public function get_table_data_old(Request $request)
    {
        //dd(session('cia'));
        //$company_id = empty(session('cia')) ? company_id() : company_id(session('cia'));
        //$user_type = Auth::user()->user_type;
        //$datos = $this->datos();

        $estEnv = $request->input('estado');       // Parámetro 'estado'
        $isHistorial = $request->input('isHistorial'); // Parámetro 'isHistorial'


        $ordenes = Orden_desarme::select('ordenes_desarme.*')
            ->with('venta')
            //->with('aseguradoras')
            ->with('cotizacion')
            ->whereHas('car', function ($str) use ($isHistorial) {

                if (strtolower(auth()->user()->role->name) == 'operario' || strtolower(auth()->user()->role->name) == 'cadete' || strtolower(auth()->user()->role->name) == 'administrativo de desarme') { //|| strtolower(auth()->user()->role->name) == 'gerente de operarios'
                    //dd(auth()->user()->company_id);
                    if (!$isHistorial)
                        $str->where('company_id', auth()->user()->company_id);
                }
                $str->where(function ($row) use ($isHistorial) {
                    // $row-> where('idEstado',6)->orwhere('idEstado',5)->orwhere('idEstado',8);

                    if (!$isHistorial)
                        $row->where('idEstado', '!=', 1);
                });
            });
        // dd(strtolower(auth()->user()->role->name));
        if (strtolower(auth()->user()->role->name) == 'vendedor') {

            $ordenes->whereHas('venta', function ($str) {
                $str->where('user_id', '=', auth()->id());
            });
        }
        //dd(auth()->user()->location);
        $ocultar = '';
        if ((strtolower(auth()->user()->role->name) == 'operario' || strtolower(auth()->user()->role->name) == 'cadete') && (!$isHistorial)) {

            // $ordenes->where('ubicacion', auth()->user()->location);
            $ordenes->where('procesar', 1);
            $ordenes->where('idCadete_operario', auth()->id());
            //$ocultar = 'd-none';
        }

        $ordenes->orderBy('created_at', 'desc');

        $estEnv = $request->estado;

        if (!$estEnv) {



            if (!$isHistorial)
                $ordenes->where(function ($query) {
                    $query->where('estado', '!=', 'completado')
                        ->orWhere('estado', null);
                });

            // $ordenes->where('estado', '!=', 'completado')->orwhere('estado', null);
        }
        //else {
        //     // dd(!$estEnv);

        //     $ordenes->where('estado', 'completado');
        // }




        //->where('company_id', company_id());
        //->orderBy("projects.id","desc");


        return DataTables::eloquent($ordenes)
            ->filter(function ($query) use ($request) {
                //                            if ($request->has('cliente')) {
                //                                $query->where('cliente', 'like', "%{$request->post('cliente')}%");
                //                            }
                //
                if ($request->has('id')) {
                    if ($request->post('id'))
                        $query->where('id', $request->post('id'));
                }
            })

            ->filterColumn('ubicacion', function ($query, $keyword) {

                $query->orwhereHas('lugares', function ($str) use ($keyword) {
                    $str->where('nombre', 'like', "%{$keyword}%");
                });
            })

            ->editColumn('procesar', function ($orden) {
                // dd($orden->procesar);
                $selected = $orden->procesar == 1 ? 'selected' : '';
                $disable = '';
                if ($orden->estado == 'completado')
                    $disable = 'disabled';

                $a = "<select $disable class='form-control' onchange='changeProcesar(this)' data-id='$orden->id' name='procesar[$orden->id]'>
                    <option value = '' > No procesado</option>
                    <option $selected value = '1' > Procesar</option>
                </select>";
                return $a;
            })
            ->editColumn('id', function ($orden) {
                return '<a href="' . action('OrdenDesarmeController@show', $orden->id) . '">' . $orden->id . '</a>';
            })
            ->editColumn('pedido_pasado', function ($orden) {
                return $orden->pedido_pasado;
            })
            ->editColumn('prioridad', function ($orden) {

                return $orden->prioridad;
            })
            ->editColumn('interno', function ($orden) {

                return $orden->interno ?? null;
            })
            ->editColumn('cotizacion', function ($orden) {

                return $orden->cotizacion->quotation_number ?? null;
            })
            ->editColumn('venta', function ($orden) {

                $in = 'VEN-';
                if (!isset($orden->venta)) {
                    return '';
                }
                if ($orden->venta->company_id == 1) {
                    $in .= 'PM-';
                } else if ($orden->venta->company_id == 2) {
                    $in .= 'PC-';
                }
                $text = $in . $orden->venta->invoice_number ?? null;
                $ruta = action('InvoiceController@show', $orden->venta->id);
                $a = "<a href='$ruta'>$text </a>";
                return $a;
            })
            ->editColumn('fecha_venta', function ($orden) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->fecha_venta) ? date($date_format, strtotime($orden->fecha_venta)) : null;
            })
            ->editColumn('lugar_venta', function ($orden) {
                return $orden->lugar_venta;
            })
            ->editColumn('marca_modelo', function ($orden) {
                //dd($orden->producto->marcaModelo);
                return ($orden->producto->marcaModelo->marca->marca ?? '') . ' ' .
                    ($orden->producto->marcaModelo->modelo->modelo ?? '');
            })
            ->editColumn('pieza', function ($orden) {
                return $orden->producto->item->item_name ?? null;
            })
            ->editColumn('detalle_pieza', function ($orden) {
                return $orden->detalle_pieza;
            })
            ->editColumn('detalle_anulado', function ($orden) {
                return $orden->detalle_anulado;
            })
            ->editColumn('cliente', function ($orden) {
                if (!empty($orden->cotizacion) || !empty($orden->venta)) {
                    return $orden->cotizacion->client->contact_name ?? $orden->venta->client->contact_name;
                }

                return '';
            })
            ->editColumn('vendedor', function ($orden) {
                // dd($orden->venta->vendedor->name);
                return ($orden->cotizacion->vendedor->name ?? null) ?? ($orden->venta->vendedor->name ?? '');
            })
            ->editColumn('ubicacion', function ($orden) {
                return $orden->car->lugar_entrega->nombre ?? '';
            })
            ->editColumn('estado', function ($orden) {
                return $orden->estado;
            })
            ->editColumn('autorizo', function ($orden) {
                return $orden->autorizo;
            })
            ->editColumn('fecha_estimada_pieza_disponible', function ($orden) {

                $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->fecha_estimada_pieza_disponible) ? date($date_format, strtotime($orden->fecha_estimada_pieza_disponible)) : null;
            })
            ->editColumn('existe', function ($orden) {
                return $orden->existe;
            })
            ->editColumn('falta', function ($orden) {
                return $orden->falta;
            })
            ->editColumn('informo_ausencia', function ($orden) {
                return $orden->informo_ausencia;
            })->editColumn('obs_desarme_busqueda', function ($orden) {
                return $orden->obs_desarme_busqueda;
            })
            ->editColumn('fecha_desarmado_anulado', function ($orden) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->fecha_desarmado_anulado) ? date($date_format, strtotime($orden->fecha_desarmado_anulado)) : null;
            })
            ->editColumn('cargando_camioneta', function ($orden) {
                return $orden->cargando_camioneta;
            })->editColumn('entregado', function ($orden) {
                return $orden->entregado;
            })
            ->editColumn('fecha_embalado', function ($orden) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->fecha_embalado) ? date($date_format, strtotime($orden->fecha_embalado)) : null;
            })

            ->editColumn('fecha_avisado_vendedor', function ($orden) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->fecha_avisado_vendedor) ? date($date_format, strtotime($orden->fecha_avisado_vendedor)) : null;
            })
            ->addColumn('cliente', function ($orden) {
                return $orden->venta->client->contact_name;
            })
            ->addColumn('action', function ($orden) use ($ocultar) {
                return '<form action="' . action('OrdenDesarmeController@destroy', $orden['id']) . '" class="text-center" method="post">'

                    . '<a href="' . action('OrdenDesarmeController@edit', $orden['id']) . '" 
data-title="' . _lang('Update Vehicle') . '" class="btn btn-warning btn-xs ajax-modal"><i class="ti-pencil"></i></a>&nbsp;'

                    . csrf_field()
                    . '<input name="_method" type="hidden" value="DELETE">'
                    . '<button 
class="btn btn-danger btn-xs btn-remove ' . $ocultar . '" type="submit"><i class="ti-eraser"></i></button>'
                    . '</form>';
            })
            ->setRowId(function ($orden) {
                return "row_" . $orden->id;
            })
            ->rawColumns(['action', 'members.name', 'status', 'id', 'procesar', 'venta'])
            ->make(true);
    }*/

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id,Request $request)
    {
        if (strtolower(auth()->user()->role->name) == 'vendedor') {
            return view('backend.accounting.desarme.listVendedor', ['id' => $id]);
        }
		
		 if ($request->ajax()) {
			$order_desarme = Orden_desarme::with(['venta', 'cotizacion', 'producto.marcaModelo.marca', 'producto.marcaModelo.modelo', 'producto.item', 'car.lugar_entrega', 'car'])
            ->where("id", $id)
            ->orderBy('interno', 'desc')
            ->first();
			$data['order_desarme'] = $order_desarme;
			$data['company'] = CompanySetting::where('company_id',$data['order_desarme']->company_id)->get();

			return view("backend.accounting.desarme.template", $data);
		 }
		
		
		
        return view('backend.accounting.desarme.list', ['id' => $id]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
        $lugar_entregas = Lugar_entregas::all();
		$company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
        $orden = Orden_desarme::find($id);
        $data = ['o' => $orden, 'id' => $id, 'lugar_entregas' => $lugar_entregas];

        $data['roles'] = Role::where('name', 'Operario')->orWhere('name', 'Cadete')->get();
        $data['puestos'] = Puesto::with('asignado')->with('company')
		->whereIn('company_id', $company_id)
		->orderBy("company_id", "asc")
		->get();
		//$this->opciones_puestos;
        // dd($data);

        $nro_interno_datos = Cars::All();

        if (auth()->user()->role->name != 'Gerencial') {
            $nro_interno_datos->where('company_id', company_id());
        };
        $data['nro_interno_datos'] = $nro_interno_datos;
		
		$data['productos'] =$data['productos'] = Product::select('products.*', 'cars.tipo_vehiculo','items.item_name')
                ->Join('items', 'items.id', '=', 'products.item_id')
                ->leftJoin('cars', 'cars.id', '=', 'products.nro_interno')
                ->where('car_id', null)
                ->where('products.nro_interno', $orden->idCar)
                //->where('products.item_id', $orden->pieza)
                ->where('stock', '>=', 0)->get();
				
				//$item = Product::where('id', $idProduct)->with('item')->first();
	   if (strtolower(auth()->user()->role->name) != 'operario' || strtolower(auth()->user()->role->name) != 'cadete') {
            return view('backend.accounting.desarme.modal.edit', $data);
        } else {
            return view('backend.accounting.desarme.modal.editOperarios', $data);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
		
		$validator = Validator::make($request->all(), [
            'producto_id' => 'required'
        ], [
            'product_id.required' => _lang('La pieza de encontrarse disponibles'),
        ]);
		
		 if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect('orden-desarme')
                    ->withErrors($validator)
                    ->withInput();
            }
        }
		//dd($request->producto_id);
        //dd($request->input('fecha_avisado_vendedor'));
        $orden_desarme = Orden_desarme::find($id);
        $cadete_operario_aterior = $orden_desarme->idCadete_operario;

        $estado_anterior = $orden_desarme->estado;

        $orden_desarme->pedido_pasado =  $request->input('pedido_pasado');
        //$orden_desarme->prioridad = $request->input('prioridad');
        $orden_desarme->interno =  $request->input('interno');
        //$orden_desarme->id_cotizacion =  $request->input('id_cotizacion');
        //$orden_desarme->id_venta =  $request->input('id_venta');
        // $orden_desarme->fecha_venta =  $request->input('fecha_venta');
        $orden_desarme->lugar_venta =  $request->input('lugar_venta');
        //$orden_desarme->marca_modelo =  $request->input('marca_modelo');
        //$orden_desarme->pieza =  $request->input('pieza');
        $orden_desarme->detalle_pieza =  $request->input('detalle_pieza');
        $orden_desarme->detalle_anulado =  $request->input('detalle_anulado');
        $orden_desarme->ubicacion =  $request->input('ubicacion');
        $orden_desarme->estado =  $request->input('estado');
        $orden_desarme->autorizo =  $request->input('autorizo');
        $orden_desarme->fecha_estimada_pieza_disponible =  $request->input('fecha_estimada_pieza_disponible');
        $orden_desarme->existe =  $request->input('existe');
        $orden_desarme->falta =  $request->input('falta');
        $orden_desarme->informo_ausencia =  $request->input('informo_ausencia');
        $orden_desarme->obs_desarme_busqueda =  $request->input('obs_desarme_busqueda');
        $orden_desarme->fecha_desarmado_anulado =  $request->input('fecha_desarmado_anulado');
        $orden_desarme->cargando_camioneta =  $request->input('cargando_camioneta');
        $orden_desarme->entregado =  $request->input('entregado');
        $orden_desarme->fecha_embalado =  $request->input('fecha_embalado');
        $orden_desarme->fecha_avisado_vendedor =  $request->input('fecha_avisado_vendedor');
        //$orden_desarme->f_ingreso_puesto =  $request->input('f_ingreso_puesto');
		
        $orden_desarme->idCar =  $request->input('idCar');
        $orden_desarme->product_id =  $request->input('producto_id') ?? 0;

        // if (strtolower(auth()->user()->role->name) != 'operarios' || strtolower(auth()->user()->role->name) != 'cadete') {
        //     $orden_desarme->procesar =  $request->input('procesar');
        //     $orden_desarme->idCadete_operario =  $request->input('idCadete_operario');
        // }
		
		/*if (($orden_desarme->puesto != $request->input('puesto')) {
			$opciones = $this->opciones_puestos;
			$operarios = ['operariocolectora@pmpc.com.ar', 'operariocolectora@pmpc.com.ar', 'operariocolectora@pmpc.com.ar', 'operariocolectora@pmpc.com.ar', 'operarioconstituyentes@pmpc.com.ar', 'operarioventanita@pmpc.com.ar', 'operarioventanita@pmpc.com.ar', 'operariogeneral@pmpc.com.ar'];
		
		}*/
		/*if ($orden_desarme->puesto != $request->input('puesto')) {
			//$operario = Puesto::where('puesto', $request->input('puesto'))->where('company_id',company_id())->first();
			$orden_desarme->puesto = $request->input('puesto');
			$orden_desarme->idCadete_operario=$request->input('idcadete_operario');
		}*/
		
		 $orden_desarme->puesto_final = $request->input('puesto_final') ?? '';
		 
		 if ($orden_desarme->fecha_desarmado_anulado !== null) { 
			$orden_desarme->estado = 'completado'; 
		}
		 
        $orden_desarme->save();
		
        /*if (!empty($orden_desarme->idCadete_operario) && $cadete_operario_aterior != $orden_desarme->idCadete_operario) {
            Notification::send(User::find($orden_desarme->idCadete_operario), new OrdenUpdated($orden_desarme));
        }*/
        //dd($orden_desarme);
        //if (trim($orden_desarme->estado) != '' && $orden_desarme->estado != $estado_anterior) {
        if ($orden_desarme->fecha_desarmado_anulado !== null)
         {       
            $item_invoice = InvoiceItem::where('invoice_id',$orden_desarme->id_venta )->where('item_id',$orden_desarme->pieza)->first();
			$stock = Product::where("id", $orden_desarme->product_id)->first();
			if($item_invoice)
			{
				$item_invoice->product_id=$orden_desarme->product_id;
				$item_invoice->save();
				
				//$vend = Product::where('nro_interno', "$car->id")->where('stock', 0)->get();
				 //$stock = Product::where("id", $orden_desarme->product_id)->first();
				 
				 //if ($stock->estado=="desarme"){
				 if (in_array($stock->estado, array("desarme","desarme-stock"))) {
					$stock->estado = "optimo";
				  }
				 
				 $stock->stock = $stock->stock - $item_invoice->quantity;
			     $stock->save();
				
				$orden_despacho_ = OrdenDespacho::where('invoice_id', '=',  $orden_desarme->venta->id)->where('invoiceitem_id', '=',  $orden_desarme->product_id)->first();

					if(!$orden_despacho_){
						//Notification::send(User::find($orden_desarme->venta->user_id), new OrdenUpdated($orden_desarme));

						$orden_despacho = new OrdenDespacho();

						$orden_despacho->invoice_id = $orden_desarme->venta->id;

						$orden_despacho->invoiceitem_id = $item_invoice->id; // $orden_desarme->product_id;---
						$orden_despacho->description =  $item_invoice->description;
						$orden_despacho->quantity =  $item_invoice->quantity;
						$orden_despacho->company_id =  $item_invoice->company_id;
						$orden_despacho->estatus = 'pendiente';

						$orden_despacho->save();

						//$message = "Cambio de estado en orden de desarme <b><a href='" . route('orden-desarme.show', $orden_desarme->id) . "'>$orden_desarme->id</a></b>";

						//$user = User::find($orden_desarme->venta->user_id);
						//$email = $user->email;
						//Mail::to($email)->send(new OrdenDesarmeNotificacion($message));
					 }


			}		
			/// proceso para desarme-stock
				 if (in_array($stock->estado, array("desarme-stock"))) {
					$stock->estado = "despacho";
					$stock->save();
				  }
			//dd($orden_desarme->pieza);
        }


        return response()->json([
            'result' => 'success',
            'action' => 'store',
            'message' => _lang('Orden Actualizada Correctamente'),
            'data' => $orden_desarme
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $orden = Orden_desarme::find($id)->toArray();
        $orden['id_orden_desarme'] = $orden['id'];
        unset($orden['id']);
        $orden['id_user'] = auth()->id();

        $historial = Historial_ordenes_desarme::create($orden);
        // dd($historial);


        Orden_desarme::where('id', $id)->delete();
        return redirect('orden-desarme')->with('success', _lang('Orden de desarme eliminada'));
    }


    public function changeProcesar($id, $procesar = null)
    {
        $orden_desarme = Orden_desarme::find($id);
        $orden_desarme->procesar = $procesar;
        $orden_desarme->save();
        return response()->json([
            'result' => 'success',
            'action' => 'store',
            'message' => _lang('Orden Actualizada Correctamente'),
            'data' => $orden_desarme
        ]);
    }

    public function consultaOrden(Request $request)
    {
        if ($request->ajax()) {

            $data = Orden_desarme::select('ordenes_desarme.id', 'id_cotizacion', 'interno', 'fecha_venta', 'marca_modelo', 'pieza', 'id_venta', 'idCar', 'estado', 'f_ingreso_puesto', 'falta', 'informo_ausencia', 'obs_desarme_busqueda', 'fecha_desarmado_anulado','puesto','puesto_final','ordenes_desarme.product_id')
                ->with(['marcaModelo', 'venta', 'cotizacion', 'producto'])
                ->whereHas('car', function ($str) {
                    if (strtolower(auth()->user()->role->name) == 'operario' || strtolower(auth()->user()->role->name) == 'cadete') {
                        $str->where('company_id', auth()->user()->company_id);
                    }
                    $str->where(function ($row) {
                       // $row->where('idEstado', '!=', 1);
                    });
                });
            if (strtolower(auth()->user()->role->name) == 'vendedor') {

                $data->whereHas('venta', function ($str) {
                    $str->where('user_id', '=', auth()->id());
                });
            }

            if ((strtolower(auth()->user()->role->name) == 'operario' || strtolower(auth()->user()->role->name) == 'cadete')) {
                $data->where('procesar', 1);
                $data->where('idCadete_operario', auth()->id());
            };

            $data->orderBy('created_at', 'desc');

            return DataTables::eloquent($data)
                ->addColumn('marcamodelo', function ($data) {
                    return ($data->marcaModelo->marca->marca ?? '') . ' ' .
                        ($data->marcaModelo->modelo->modelo ?? '');
                })
                ->addColumn('item_pieza', function ($data) {
					return  "(".($data->producto->id ?? ''). ") " .$data->item->item_name ?? null;
                    //return $data->producto->item->item_name ?? null;
                })
                ->addColumn('cliente', function ($data) {
                    if (!empty($data->cotizacion) || !empty($data->venta)) {
                        return $data->cotizacion->client->contact_name ?? $data->venta->client->contact_name;
                    }
                })
                ->addColumn('vendedor', function ($data) {
                    return ($data->cotizacion->vendedor->name ?? null) ?? ($data->venta->vendedor->name ?? '');
                })
                ->addColumn('ubicacion', function ($data) {
                    return $data->car->lugar_entrega->nombre ?? '';
                })
                ->addColumn('invoice_number', function ($data) {
                    if (!empty($data->venta)) {

                        return '<a href="' . action('InvoiceController@show', $data->venta->id) . '">' .  $data->venta->invoice_number . '</a>';
                        //return $data->venta->invoice_number ?? '';   
                    }
                })
                /* ->addColumn('fecha_desarmado_anulado', function ($data) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($data->fecha_desarmado_anulado) ? date($date_format, strtotime($data->fecha_desarmado_anulado)) : null;
            })*/
                ->filterColumn('marcamodelo', function ($query, $keyword) {
                    $query->orwhereHas('marcamodelo', function ($str) use ($keyword) {
                        $str->whereHas('marca', function ($str) use ($keyword) {
                            $str->where('marca', 'like', "%{$keyword}%");
                        });
                        $str->orwhereHas('modelo', function ($str) use ($keyword) {
                            $str->where('modelo', 'like', "%{$keyword}%");
                        });
                    });
                })
                /*->filterColumn('item_pieza', function ($query, $keyword) {
                    $query->whereHas('producto.item', function ($subQuery) use ($keyword) {
                        $subQuery->where('item_name', 'like', "%{$keyword}%");
                    });
                })*/
				->filterColumn('item_pieza', function ($query, $keyword) {
				$query->orwhereHas('producto', function ($str) use ($keyword) {
                         $str->where('products.id', 'like', "%{$keyword}%");
						$str->orwhereHas('item', function ($str) use ($keyword) {
							$str->where('items.item_name', 'like', "%{$keyword}%");
						});
				});
				})
                ->filterColumn('cliente', function ($query, $keyword) {
                    $query->whereHas('cotizacion.client', function ($subQuery) use ($keyword) {
                        $subQuery->where('contact_name', 'like', "%{$keyword}%");
                    });
                    $query->orwhereHas('venta.client', function ($subQuery) use ($keyword) {
                        $subQuery->where('contact_name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('vendedor', function ($query, $keyword) {
                    $query->whereHas('cotizacion.vendedor', function ($subQuery) use ($keyword) {
                        $subQuery->where('name', 'like', "%{$keyword}%");
                    });
                    $query->orwhereHas('venta.vendedor', function ($subQuery) use ($keyword) {
                        $subQuery->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('ubicacion', function ($query, $keyword) {
                    $query->orwhereHas('car.lugar_entrega', function ($str) use ($keyword) {
                        $str->where('nombre', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('invoice_number', function ($query, $keyword) {
                    $query->orwhereHas('venta', function ($str) use ($keyword) {
                        $str->where('invoice_number', 'like', "%{$keyword}%");
                    });
                })
				->filterColumn('interno', function ($query, $keyword) {
				  $query->where('idCar', 'like', "%$keyword%");
				})
                ->rawColumns(['invoice_number'])
                ->addColumn('interno', function ($data) {
                    return nroInternoAlias($data->car->company_id, $data->car->tipo_vehiculo, $data->car->id);  //$orden->car->id."--".$orden->interno;
                })
                ->make(true);
        }

        return view('backend.accounting.desarme.list_orden_consul');
    }


    public function get_table_data(Request $request)
    {
        $estEnv = $request->input('estado');       // Parámetro 'estado'
        $isHistorial = $request->input('isHistorial'); // Parámetro 'isHistorial'
		//$company_id = empty(session('cia')) ? company_id() : company_id(session('cia'));
		$company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();

    //    $nro_interno_datos = Cars::All();
	
        $opciones = Puesto::with('asignado')->with('company')
		->whereIn('company_id', $company_id)
		->orderBy("company_id", "asc")
		->get();
		
		//$this->opciones_puestos;

        //if (auth()->user()->role->name != 'Gerencial') {
       //     $nro_interno_datos->whereIn('company_id', $company_id);
        //}
        $gerenciales_autorizado = Puesto::where("predeterminada", 1)->pluck('user_id')->toArray();
        /*$ordenes = Orden_desarme::select('ordenes_desarme.*')
    ->with([
        'venta', 
        'cotizacion', 
        'car.estado_relacion' 
    ])
    ->whereHas('car', function ($str) use ($isHistorial, $company_id) {
        if (strtolower(auth()->user()->role->name) == 'operario' || strtolower(auth()->user()->role->name) == 'cadete' || strtolower(auth()->user()->role->name) == 'administrativo de desarme') {
            if (!$isHistorial)
                $str->where('company_id', auth()->user()->company_id);
        } else {
            $str->whereIn('company_id', $company_id);
        }
        
        $str->where(function ($row) use ($isHistorial) {
            if (!$isHistorial)
                $row->where('idEstado', '!=', 1);
        });
    })	
	->whereHas('venta', function ($query) {
        $query->where('status', '!=', 'Canceled');
    });

		// $ordenes->whereIn('company_id', $company_id)
		
        if (strtolower(auth()->user()->role->name) == 'vendedor') {
            $ordenes->whereHas('venta', function ($str) {
                $str->where('user_id', '=', auth()->id());
            });
        }
        $ocultar = '';
        if ((strtolower(auth()->user()->role->name) == 'operario' || strtolower(auth()->user()->role->name) == 'cadete') && (!$isHistorial)) {
            $ordenes->where('procesar', 1);
            $ordenes->where('idCadete_operario', auth()->id());
        }

        $ordenes->orderBy('created_at', 'desc');

        $estEnv = $request->estado;

        if (!$estEnv) {
            if (!$isHistorial)
                $ordenes->where(function ($query) {
                    $query->where('estado', '!=', 'completado')
                        ->orWhere('estado', null);
                });
        }
		*/
		
		
		   $ocultar = '';
		$user = auth()->user();
$role = strtolower($user->role->name);

/*$ordenes = Orden_desarme::select('ordenes_desarme.*')
    ->with([
        'venta', 
        'cotizacion', 
        'car.estado_relacion' 
    ]);

$ordenes->whereHas('car', function ($query) use ($isHistorial, $company_id, $user, $role) {
    if (in_array($role, ['operario', 'cadete', 'administrativo de desarme'])) {
        if (!$isHistorial) {
            $query->where('company_id', $user->company_id);
        }
    } else {
        $query->whereIn('company_id', $company_id);
    }
    
    if (!$isHistorial) {
        $query->where('idEstado', '!=', 1);
    }
});


$ordenes->whereHas('venta', function ($query) use ($role) {
    $query->where('status', '!=', 'Canceled');
    if ($role === 'vendedor') {
        $query->where('user_id', '=', auth()->id());
    }
});


if (in_array($role, ['operario', 'cadete']) && !$isHistorial) {
    $ordenes->where('procesar', 1)
            ->where('idCadete_operario', auth()->id());
}


$estEnv = $request->estado;

if (!$estEnv && !$isHistorial) {
    $ordenes->where(function ($query) {
        $query->where('estado', '!=', 'completado')
              ->orWhereNull('estado'); 
    });
}*/

$ordenes = Orden_desarme::with([
			'venta', 
			'cotizacion', 
			'car.estado_relacion' 
		]);

		$ordenes->whereHas('car', function ($q) use ($isHistorial, $company_id, $user, $role) {
			if (in_array($role, ['operario', 'cadete', 'administrativo de desarme'])) {
				if (!$isHistorial) {
					$q->where('company_id', $user->company_id);
				}
			} else {
				$q->whereIn('company_id', (array)$company_id); // Asegura que sea un array
			}
			
			if (!$isHistorial) {
				//$q->where('idEstado', '!=', 1);
			}
		});

		$ordenes->where(function ($mainQuery) use ($role) {
			$mainQuery->whereHas('venta', function ($q) use ($role) {
				$q->where('status', '!=', 'Canceled');
				
				if ($role === 'vendedor' && auth()->check()) {
					$q->where('user_id', auth()->id());
				}
			})->orDoesntHave('venta'); // <- Esto permite incluir las órdenes que no tienen venta
		});

		if (in_array($role, ['operario', 'cadete']) && !$isHistorial) {
			$ordenes->where('procesar', 1)
				  ->where('idCadete_operario', auth()->id());
		}

		$estEnv = $request->estado;
		if (!$estEnv && !$isHistorial) {
			$ordenes->where(function ($q) {
				$q->where('estado', '!=', 'completado')
				  ->orWhereNull('estado'); 
			});
		}

		$ordenes->orderBy('created_at', 'desc');


        return DataTables::eloquent($ordenes)
            ->filter(function ($query) use ($request) {
                if ($request->has('id')) {
                    if ($request->post('id'))
                        $query->where('id', $request->post('id'));
                }
            })
            ->filterColumn('ubicacion', function ($query, $keyword) {
                $query->orwhereHas('lugares', function ($str) use ($keyword) {
                    $str->where('nombre', 'like', "%{$keyword}%");
                });
            })
			 ->filterColumn('puesto', function ($query, $keyword) {
				  $query->where('puesto', "$keyword");
            })
			->filterColumn('interno', function ($query, $keyword) {
				  $query->where('idCar', "$keyword");
            })
			->filterColumn('lugar_venta', function ($query, $keyword) {
				  $query->where('lugar_venta', 'like', "%{$keyword}%");
            })
			->filterColumn('venta', function ($query, $keyword) {
					$query->whereHas('venta', function ($str) use ($keyword) {
                    $str->where('invoice_number', 'like', "%{$keyword}%");
                });
            })
			 ->filterColumn('fecha_venta', function ($query, $keyword) {
				   $query->whereRaw("DATE_FORMAT(fecha_venta,'%d/%m/%Y') LIKE ?", ["%$keyword%"]);
                   // $query->whereDate('fecha_venta', $keyword);
            })
			 ->filterColumn('cliente', function ($query, $keyword) {
				 $query->whereHas('venta.client', function ($str) use ($keyword) {
						$str->where('contact_name', 'like', "%{$keyword}%");
				  });
            })
			
            ->filterColumn('marca_modelo', function ($query, $keyword) {
              $query->orwhereHas('producto.marcaModelo', function ($str) use ($keyword) {
                    $str->whereHas('marca', function ($str) use ($keyword) {
                        $str->where('marca', 'like', "%{$keyword}%");
                    });
                    $str->orwhereHas('modelo', function ($str) use ($keyword) {
                        $str->where('modelo', 'like', "%{$keyword}%");
                    });
                });
            })
			->filterColumn('pieza', function ($query, $keyword) {
				$query->orwhereHas('producto', function ($str) use ($keyword) {
                         $str->where('products.id', 'like', "%{$keyword}%");
						$str->orwhereHas('item', function ($str) use ($keyword) {
							$str->where('items.item_name', 'like', "%{$keyword}%");
						});
				});
            })
			->filterColumn('vendedor', function ($query, $keyword) {
                $query->orwhereHas('venta.vendedor', function ($str) use ($keyword) {
                        $str->where('name', 'like', "%{$keyword}%");
                    });
            })
			->filterColumn('ubicacion', function ($query, $keyword) {
                $query->orwhereHas('car.lugar_entrega', function ($str) use ($keyword) {
                        $str->where('nombre', 'like', "%{$keyword}%");
                    });
            })
            ->editColumn('procesar', function ($orden) {
                $selected = $orden->procesar == 1 ? 'selected' : '';
                $disable = '';
                if ($orden->estado == 'completado')
                    $disable = 'disabled';

                $a = "<select $disable class='form-control' onchange='changeProcesar(this)' data-id='$orden->id' name='procesar[$orden->id]'>
                    <option value = '' > No procesado</option>
                    <option $selected value = '1' > Procesar</option>
                </select>";
                return $a;
            })

            ->addColumn('id_link', function ($orden) {
                return '<a href="' . action('OrdenDesarmeController@show', $orden->id) . '">' . $orden->id . '</a>';
            })
            ->editColumn('pedido_pasado', function ($orden) {
                return $orden->pedido_pasado;
            })
           /* ->editColumn('prioridad', function ($orden) {

                return $orden->prioridad;
            })*/
            ->editColumn('cotizacion', function ($orden) {

                return $orden->cotizacion->quotation_number ?? null;
            })
            ->editColumn('venta', function ($orden) {

                $in = 'VEN-';
                if (!isset($orden->venta)) {
                    return '';
                }
                if ($orden->venta->company_id == 1) {
                    $in .= 'PM-';
                } elseif ($orden->venta->company_id == 2) {
                    $in .= 'PC-';
                }
                $text = $in . $orden->venta->invoice_number ?? null;
                $ruta = action('InvoiceController@show', $orden->venta->id);
                $a = "<a href='$ruta'>$text </a>";
                return $a;
            })
            ->editColumn('fecha_venta', function ($orden) {
                //$date_format = get_company_option('date_format', 'Y-m-d');
                //return "111";//isset($orden->fecha_venta) ? date($date_format, strtotime($orden->fecha_venta)) : '';
				//$date_format = 'Y-m-d'; //get_company_option('date_format', 'Y-m-d');
                //return isset($orden->fecha_venta) ? date($date_format, strtotime($orden->fecha_venta)) : '';
				return formatDate($orden->fecha_venta);
				
            })
            ->editColumn('lugar_venta', function ($orden) {
                return $orden->lugar_venta;
            })
            ->editColumn('marca_modelo', function ($orden) {
                return ($orden->producto->marcaModelo->marca->marca ?? '') . ' ' .
                    ($orden->producto->marcaModelo->modelo->modelo ?? '');
            })
          /*  ->editColumn('pieza', function ($orden) {
				return  "($orden->producto->id) " . $orden->item->item_name ?? null;
            })*/
            ->editColumn('detalle_pieza', function ($orden) {
                return $orden->detalle_pieza;
            })
            ->editColumn('detalle_anulado', function ($orden) {
                return $orden->detalle_anulado;
            })
            ->editColumn('cliente', function ($orden) {
                if (!empty($orden->cotizacion) || !empty($orden->venta)) {
                    return $orden->cotizacion->client->contact_name ?? $orden->venta->client->contact_name;
                }

                return '';
            })
           ->editColumn('vendedor', function ($orden) {
                return ($orden->cotizacion->vendedor->name ?? null) ?? ($orden->venta->vendedor->name ?? '');
            })
            ->editColumn('ubicacion', function ($orden) {
                return $orden->car->lugar_entrega->nombre ?? '';
            })
            ->editColumn('estado', function ($orden) {
                return $orden->car->estado->estado ?? 'Sin Estado';
            })
           ->editColumn('autorizo', function ($orden) {
                return $orden->autorizo;
            })
            ->editColumn('fecha_estimada_pieza_disponible', function ($orden) {

                $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->fecha_estimada_pieza_disponible) ? date($date_format, strtotime($orden->fecha_estimada_pieza_disponible)) : null;
            })
            ->editColumn('existe', function ($orden) {
                return $orden->existe;
            })
            ->editColumn('falta', function ($orden) {
                return $orden->falta;
            })
            ->editColumn('informo_ausencia', function ($orden) {
                return $orden->informo_ausencia;
            })->editColumn('obs_desarme_busqueda', function ($orden) {
                return $orden->obs_desarme_busqueda;
            })
            ->editColumn('fecha_desarmado_anulado', function ($orden) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->fecha_desarmado_anulado) ? date($date_format, strtotime($orden->fecha_desarmado_anulado)) : null;
            })
            ->editColumn('cargando_camioneta', function ($orden) {
                return $orden->cargando_camioneta;
            })->editColumn('entregado', function ($orden) {
                return $orden->entregado;
            })
            ->editColumn('fecha_embalado', function ($orden) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->fecha_embalado) ? date($date_format, strtotime($orden->fecha_embalado)) : null;
            })

            ->editColumn('fecha_avisado_vendedor', function ($orden) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->fecha_avisado_vendedor) ? date($date_format, strtotime($orden->fecha_avisado_vendedor)) : null;
            })
			 ->editColumn('f_ingreso_puesto', function ($orden) use ($gerenciales_autorizado) {

                if (strTolower(auth()->user()->role->name) == 'administrativo de desarme' || strTolower(auth()->user()->role->name) == 'gerencial' || in_array(auth()->user()->id, $gerenciales_autorizado)) {
                //if (true == true ) {
					$fecha = isset($orden->f_ingreso_puesto) ? date('Y-m-d\TH:i', strtotime($orden->f_ingreso_puesto)) : date('Y-m-d\TH:i');
					/* if (isset($orden->f_ingreso_puesto)){
						return '<span>' . $orden->f_ingreso_puesto . '</span>';
					}*/
						return '<input type="datetime-local" name="f_ingreso_puesto" value="' . $fecha . '" class="f-ingreso-puesto-input form-control">';	
                } else {
                    return '<span>' . $orden->f_ingreso_puesto . '</span>';
                }
            })
			/*->editColumn('f_ingreso_puesto', function ($orden) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->f_ingreso_puesto) ? date($date_format, strtotime($orden->f_ingreso_puesto)) : null;
            })*/
			  ->addColumn('pieza', function ($orden) {
				return  "(".($orden->producto->id ?? ''). ") " .$orden->item->item_name ?? null;
            })
            ->addColumn('interno', function ($orden) {
				//return "";
                return nroInternoAlias($orden->car->company_id ?? null, $orden->car->tipo_vehiculo ?? null, $orden->car->id ?? null);  //$orden->car->id."--".$orden->interno;
            })

            ->addColumn('cliente', function ($orden) {
                return $orden->venta->client->contact_name ?? '';
            })
            ->addColumn('action', function ($orden) use ($ocultar) {
				//return view('backend.accounting.desarme.partials.actions', compact('orden','ocultar'));
				
				 $url = route('orden-desarme-one.generar-pdf', $orden->id);

                $ordenDesarmeBtn = '<a href="' . $url . '" target="_blank" data-title="Orden Desarame '.$orden->id.' " data-fullscreen="true" id="ordenDesarmeBtn" class="btn btn-info ajax-modal btn-xs" title="Orden de Desarme">
                      <i class="ti-printer"></i>
                    </a>';
					
                return '<form action="' . action('OrdenDesarmeController@destroy', $orden['id']) . '" class="text-center" method="post">'

                    . '<a href="' . action('OrdenDesarmeController@edit', $orden['id']) . '" 
data-title="' . _lang('Update Vehicle') . '" class="btn btn-warning btn-xs ajax-modal"><i class="ti-pencil"></i></a>&nbsp;'

                    . csrf_field()
                    . '<input name="_method" type="hidden" value="DELETE">'
                    . '<button 
class="btn btn-danger btn-xs btn-remove ' . $ocultar . '" type="submit"><i class="ti-eraser"></i></button>'
                    . '</form>'.$ordenDesarmeBtn;
					
					
					
            })
            ->editColumn('puesto', function ($orden) use ($opciones, $gerenciales_autorizado) {
                if (strTolower(auth()->user()->role->name) == 'administrativo de desarme' || strTolower(auth()->user()->role->name) == 'gerencial' || in_array(auth()->user()->id, $gerenciales_autorizado)) {
                    //dd($opciones);
                    //$opciones = ['1C', '1P', '2C', '2P', '3', '4C', '4P'];
                    //$opciones = $this->opciones_puestos;
				//	if (true == true ) {
                    //$operarios = ['operariocolectora@pmpc.com.ar', 'operariocolectora@pmpc.com.ar', 'operariocolectora@pmpc.com.ar', 'operariocolectora@pmpc.com.ar', 'operarioconstituyentes@pmpc.com.ar', 'operarioventanita@pmpc.com.ar', 'operarioventanita@pmpc.com.ar', 'operariogeneral@pmpc.com.ar'];
					
					
					/*if (isset($orden->puesto)){
						 return '<span>' . $orden->puesto . '</span>';
					}*/
					
					
					$filteredCompany = $opciones->filter(function ($opcion) use ($orden) {
						return $opcion->company_id == $orden->venta->company_id;
					});
                    $select = '<select name="puesto" class="puesto-select form-control" data-id=' . $orden->id . '>';
                    $select .= '<option value=""> </option>';
                    //$i = 0;
                    foreach ($filteredCompany as $opcion) {
                        $selected = ($orden->puesto == $opcion->puesto) ? 'selected' : '';
                        $select .= '<option value="' . $opcion->puesto . '" ' . $selected . ' data-operario ="' . $opcion->asignado->email . 
						'" data-compania ="'. $orden->venta->company_id .'">' . $opcion->puesto . '</option>';
                      //  $i++;
                    }

                    $select .= '</select>';
                    return $select;
                } else {
                    return '<span>' . $orden->puesto . '</span>';
                }
            })
            ->setRowId(function ($orden) {
                return "row_" . $orden->id;
            })
            ->rawColumns(['action', 'members.name', 'status', 'id', 'procesar', 'venta', 'interno', 'puesto', 'f_ingreso_puesto','pieza', 'estado'])
            ->make(true);
    }

    public function updatePuesto(Request $request)
    {
        /*$request->validate([
            'ordenId' => 'required|integer',
            'puesto' => 'required|string|max:255',
            //'f_ingreso_puesto' => 'nullable|date_format:Y-m-d H:i:s',
            'f_ingreso_puesto' => 'required|date_format:Y-m-d\TH:i',
        ]);*/
        $validator = Validator::make($request->all(), [
            'ordenId' => 'required|integer',
            'puesto' => 'required|string|max:255',
            'f_ingreso_puesto' => 'required|date_format:Y-m-d\TH:i',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $orden = Orden_desarme::find($request->ordenId);

        if (!$orden) {
            return response()->json(['error' => 'Registro no encontrado.'], 404);
        }

        $operario = User::where('email', $request->operario)->first();
		$compania = $request->compania ?? 0;
        $orden->puesto = $request->puesto;
        $orden->f_ingreso_puesto = $request->f_ingreso_puesto;
        $orden->idCadete_operario = $operario->id;
		
		/*if (in_array($compania, array("2"))) {		
			$orden->puesto_final = '';
			$orden->fecha_desarmado_anulado = $request->f_ingreso_puesto;
			$orden->estado = 'completado'; 
		
            $item_invoice = InvoiceItem::where('invoice_id',$orden->id_venta )->where('item_id',$orden->pieza)->first();
			if($item_invoice)
			{
				$item_invoice->product_id=$orden->product_id;
				$item_invoice->save();
				
				//$vend = Product::where('nro_interno', "$car->id")->where('stock', 0)->get();
				 $stock = Product::where("id", $orden->product_id)->first();
				 $stock->stock = $stock->stock - $item_invoice->quantity;
			     $stock->save();
				
				$orden_despacho_ = OrdenDespacho::where('invoice_id', '=',  $orden->venta->id)->where('invoiceitem_id', '=',  $orden->product_id)->first();

					if(!$orden_despacho_){
					//	Notification::send(User::find($orden->venta->user_id), new OrdenUpdated($orden));

						$orden_despacho = new OrdenDespacho();

						$orden_despacho->invoice_id = $orden->venta->id;

						$orden_despacho->invoiceitem_id =  $item_invoice->id; //$orden->product_id; --
						$orden_despacho->description =  $item_invoice->description;
						$orden_despacho->quantity =  $item_invoice->quantity;
						$orden_despacho->company_id =  $item_invoice->company_id;
						$orden_despacho->estatus = 'pendiente';

						$orden_despacho->save();

						//$message = "Cambio de estado en orden de desarme <b><a href='" . route('orden-desarme.show', $orden->id) . "'>$orden->id</a></b>";

						//$user = User::find($orden->venta->user_id);
						//$email = $user->email;
						//Mail::to($email)->send(new OrdenDesarmeNotificacion($message));
					 }


			}		
			
		 }//
		 */

        $orden->save();

        // Respuesta de éxito
        return response()->json(['success' => 'Registro actualizado exitosamente.']);
    }



    public function generateOrdenPdf(Request $request, $id)
    {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        //	$id = decrypt($id);

        //$order_desarme = Orden_desarme::where("id", $id)->first();
		
		//$order_desarme = Orden_desarme::with(['venta', 'cotizacion', 'producto.marcaModelo.marca', 'producto.marcaModelo.modelo', 'producto.item', 'car.lugar_entrega', 'car'])
		$order_desarme = Orden_desarme::with(['venta', 'cotizacion', 'producto.marcaModelo.marca', 'producto.marcaModelo.modelo', 'producto.item', 'car.lugar_entrega', 'car'])
            ->where("id", $id)
            ->orderBy('interno', 'desc')
            ->first();
        $data['order_desarme'] = $order_desarme;

        $data['company'] = CompanySetting::where('company_id',$data['order_desarme']->company_id)->get();

		//return view("backend.accounting.desarme.template", $data);
		
		/*return Pdf::loadView("backend.accounting.desarme.template", $data)
        ->name('your-invoice.pdf');*/

        $pdf = PDF::loadView("backend.accounting.desarme.template", $data);
        $pdf->setWarnings(false);
		
		$encodedPdf = base64_encode($pdf->output());

		echo '<iframe
			  src="data:application/pdf;base64,'.$encodedPdf.'"
			  width="100%"
			  height="600px"
			>
			  Your browser does not support iframes.
			</iframe>';
		 //$encodedPdf;

        //return $pdf->stream();
        //return $pdf->stream("invoice_{$order_desarme->id}.pdf");
		
		/*return $pdf->stream("invoice_{$order_desarme->id}.pdf", [
		'Content-Type' => 'application/pdf',
		'Content-Disposition' => 'inline' // Ensures it opens in browser/iframe
		]);*/

    }

    public function generateOrdenPdfLote(Request $request, $ids)
    {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $idsArray = explode(',', $ids);

        $ordenes = Orden_desarme::with(['venta', 'cotizacion', 'producto.marcaModelo.marca', 'producto.marcaModelo.modelo', 'producto.item', 'car.lugar_entrega', 'car'])
            ->whereIn("id", $idsArray)
            ->orderBy('interno', 'desc')
            ->get();

        $ordenesFormateadas = $ordenes->map(function ($orden) {
            $date_format = get_company_option('date_format', 'Y-m-d');

            return [
                'id' => $orden->id,
                'cliente' => $orden->cotizacion->client->contact_name ?? $orden->venta->client->contact_name ?? '',
                'vendedor' => $orden->cotizacion->vendedor->name ?? $orden->venta->vendedor->name ?? '',
                'cotizacion' => $orden->cotizacion->quotation_number ?? null,
                'venta' => isset($orden->venta) ? 'VEN-' . ($orden->venta->company_id == 1 ? 'PM-' : 'PC-') . $orden->venta->invoice_number : '',
                'fecha_venta' => isset($orden->fecha_venta) ? date($date_format, strtotime($orden->fecha_venta)) : null,
                'marca_modelo' => ($orden->producto->marcaModelo->marca->marca ?? '') . ' ' . ($orden->producto->marcaModelo->modelo->modelo ?? ''),
                'pieza' => $orden->producto->item->item_name ?? null,
                'detalle_pieza' => $orden->detalle_pieza,
                'estado' => $orden->estado,
                'ubicacion' => $orden->car->lugar_entrega->nombre ?? '',
                'interno' => nroInternoAlias($orden->car->company_id, $orden->car->tipo_vehiculo, $orden->car->id),
                'puesto' => $orden->puesto ?? '',
                'f_ingreso_puesto' => $orden->f_ingreso_puesto ? date('Y-m-d H:i', strtotime($orden->f_ingreso_puesto)) : '',
                // podés agregar todos los campos formateados que quieras...
            ];
        });

        $ordenesPorInterno = $ordenesFormateadas->groupBy('interno');



        $tempFolder = storage_path('app/pdfs');
        Storage::makeDirectory('pdfs');

        foreach ($ordenesPorInterno as $interno => $ordenesGrupo) {
            $pdf = PDF::loadView("backend.accounting.desarme.template-lote", [
                'ordenes' => $ordenesGrupo,
                'interno' => $interno
            ])->setPaper('a4', 'landscape');

            $pdf->save("$tempFolder/ordenes_$interno.pdf");
        }

        // Crear el ZIP
        $zipPath = storage_path('app/ordenes_pdf.zip');
        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            foreach (Storage::files('pdfs') as $file) {
                $filePath = storage_path("app/{$file}");
                $zip->addFile($filePath, basename($filePath));
            }
            $zip->close();
        }

        // Limpiar la carpeta temporal si quieres
        Storage::deleteDirectory('pdfs');

        // Descargar el ZIP
        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
	
	
	  public function get_table_data_nb(Request $request)
		{
	
	if ($request->ajax()) {	
		$estEnv = $request->input('estado');       // Parámetro 'estado'
        $isHistorial = $request->input('isHistorial'); // Parámetro 'isHistorial'

        $ordenes = Orden_desarme::select('ordenes_desarme.*')
            ->with('venta')
            ->with('marcaModelo')
            //->with('aseguradoras')
            ->with('cotizacion')
            ->whereHas('car', function ($str) use ($isHistorial) {

                if (strtolower(auth()->user()->role->name) == 'operario' || strtolower(auth()->user()->role->name) == 'cadete' || strtolower(auth()->user()->role->name) == 'administrativo de desarme') { //|| strtolower(auth()->user()->role->name) == 'gerente de operarios'
                    //dd(auth()->user()->company_id);
                    if (!$isHistorial)
                        $str->where('company_id', auth()->user()->company_id);
                }
                $str->where(function ($row) use ($isHistorial) {
                    if (!$isHistorial){
                       // $row->where('idEstado', '!=', 1);
					}
                });
            });
        if (strtolower(auth()->user()->role->name) == 'vendedor') {
            $ordenes->whereHas('venta', function ($str) {
                $str->where('user_id', '=', auth()->id());
            });
        }
        $ocultar = '';
        if ((strtolower(auth()->user()->role->name) == 'operario' || strtolower(auth()->user()->role->name) == 'cadete') && (!$isHistorial)) {
            $ordenes->where('procesar', 1);
            $ordenes->where('idCadete_operario', auth()->id());
        }

        $ordenes->orderBy('created_at', 'desc');

        $estEnv = $request->estado;

        if (!$estEnv) {
            if (!$isHistorial)
                $ordenes->where(function ($query) {
                    $query->where('estado', '!=', 'completado')
                        ->orWhere('estado', null);
                });
        }

        return DataTables::eloquent($ordenes)
            /*->filter(function ($query) use ($request) {
                if ($request->has('id')) {
                    if ($request->post('id'))
                        $query->where('id', $request->post('id'));
                }
            })*/
			->filterColumn('invoice_number', function ($query, $keyword) {
                    $query->orwhereHas('venta', function ($str) use ($keyword) {
                        $str->where('invoice_number', 'like', "%{$keyword}%");
                    });
                })
				
				->filterColumn('interno', function ($query, $keyword) {
                    $query->orwhereHas('car', function ($str) use ($keyword) {
                        $str->where('id', 'like', "%{$keyword}%");
                    });
                })	

			->filterColumn('cotizacion', function ($query, $keyword) {
                    $query->orwhereHas('cotizacion', function ($str) use ($keyword) {
                        $str->where('quotation_number', 'like', "%{$keyword}%");
                    });
                })					
			->filterColumn('marca_modelo', function ($query, $keyword) {
                    $query->orwhereHas('marcamodelo', function ($str) use ($keyword) {
                        $str->whereHas('marca', function ($str) use ($keyword) {
                            $str->where('marca', 'like', "%{$keyword}%");
                        });
                        $str->orwhereHas('modelo', function ($str) use ($keyword) {
                            $str->where('modelo', 'like', "%{$keyword}%");
                        });
                    });
                })
		 ->filterColumn('pieza', function ($query, $keyword) {
                    $query->whereHas('item', function ($subQuery) use ($keyword) {
                        $subQuery->where('item_name', 'like', "%{$keyword}%");
                    });
                })
			->filterColumn('vendedor', function ($query, $keyword) {
                    $query->whereHas('cotizacion.vendedor', function ($subQuery) use ($keyword) {
                        $subQuery->where('name', 'like', "%{$keyword}%");
                    });
                    $query->orwhereHas('venta.vendedor', function ($subQuery) use ($keyword) {
                        $subQuery->where('name', 'like', "%{$keyword}%");
                    });
                })
				->filterColumn('ubicacion_vehiculo', function ($query, $keyword) {
				if ($keyword != "") {
					$query->orwhereHas('car.lugar_entrega', function ($str) use ($keyword) {
						$ids = explode(",", $keyword);
						 if (in_array("-1", $ids)) {
                                $str->where('id', '=', "")
                                    ->orWhereNull('id');
                            } else {
                                $str->wherein('id', $ids);
                            }
						});
					}
				})
			->filterColumn('ubicacion_pieza', function ($query, $keyword) {
				$query->orwhereHas('producto', function ($str) use ($keyword) {
                        $str->where('products.ubicacion', 'like', "%{$keyword}%");
				});
            })
				->editColumn('vendedor', function ($orden) {
						return ($orden->cotizacion->vendedor->name ?? null) ?? ($orden->venta->vendedor->name ?? '');
					})
			
			->editColumn('cotizacion', function ($orden) {

                return $orden->cotizacion->quotation_number ?? null;
            })
			
			 ->editColumn('marca_modelo', function ($orden) {
                return ($orden->producto->marcaModelo->marca->marca ?? '') . ' ' .
                    ($orden->producto->marcaModelo->modelo->modelo ?? '');
            })
            ->editColumn('pieza', function ($orden) {
                return $orden->item->item_name ?? null;
            })
            ->editColumn('detalle_pieza', function ($orden) {
                return $orden->detalle_pieza;
            })
			->editColumn('ubicacion_vehiculo', function ($orden) {
                return $orden->car->lugar_entrega->nombre ?? '';
            })			
			->editColumn('estado', function ($orden) {
                return $orden->estado;
            })
			->editColumn('ubicacion_pieza', function ($orden) {
                 return $orden->producto->ubicacion ?? null;
            })
			 ->addColumn('invoice_number', function ($orden) {

                $in = 'VEN-';
                if (!isset($orden->venta)) {
                    return '';
                }
                if ($orden->venta->company_id == 1) {
                    $in .= 'PM-';
                } elseif ($orden->venta->company_id == 2) {
                    $in .= 'PC-';
                }
                $text = $in . $orden->venta->invoice_number ?? null;
                $ruta = action('InvoiceController@show', $orden->venta->id);
                $a = "<a href='$ruta'>$text </a>";
                return $a;
            })
			->addColumn('interno', function ($orden) {
                return nroInternoAlias($orden->car->company_id, $orden->car->tipo_vehiculo, $orden->car->id);  //$orden->car->id."--".$orden->interno;
            })
			->addColumn('action', function ($orden) use ($ocultar) {
                return '<form action="' . action('OrdenDesarmeController@destroy', $orden['id']) . '" class="text-center" method="post">'

                    . '<a href="' . action('OrdenDesarmeController@edit', $orden['id']) . '" 
data-title="' . _lang('Update Vehicle') . '" class="btn btn-warning btn-xs ajax-modal"><i class="ti-pencil"></i></a>&nbsp;'

                    . csrf_field()
                    . '<input name="_method" type="hidden" value="DELETE">'
                    . '<button 
class="btn btn-danger btn-xs btn-remove ' . $ocultar . '" type="submit"><i class="ti-eraser"></i></button>'
                    . '</form>';
            })
			
		
           /* ->editColumn('procesar', function ($orden) {
                $selected = $orden->procesar == 1 ? 'selected' : '';
                $disable = '';
                if ($orden->estado == 'completado')
                    $disable = 'disabled';

                $a = "<select $disable class='form-control' onchange='changeProcesar(this)' data-id='$orden->id' name='procesar[$orden->id]'>
                    <option value = '' > No procesado</option>
                    <option $selected value = '1' > Procesar</option>
                </select>";
                return $a;
            })

            ->addColumn('id_link', function ($orden) {
                return '<a href="' . action('OrdenDesarmeController@show', $orden->id) . '">' . $orden->id . '</a>';
            })
            ->editColumn('pedido_pasado', function ($orden) {
                return $orden->pedido_pasado;
            })
            ->editColumn('prioridad', function ($orden) {

                return $orden->prioridad;
            })*/
            
			
			
			
			
           
            /*->editColumn('fecha_venta', function ($orden) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->fecha_venta) ? date($date_format, strtotime($orden->fecha_venta)) : null;
            })
            ->editColumn('lugar_venta', function ($orden) {
                return $orden->lugar_venta;
            })*/
           
            /*->editColumn('detalle_anulado', function ($orden) {
                return $orden->detalle_anulado;
            })
            ->editColumn('cliente', function ($orden) {
                if (!empty($orden->cotizacion) || !empty($orden->venta)) {
                    return $orden->cotizacion->client->contact_name ?? $orden->venta->client->contact_name;
                }

                return '';
            })*/
			
			
			
            
           /* ->editColumn('ubicacion', function ($orden) {
                return $orden->car->lugar_entrega->nombre ?? '';
            })*/
            
           /* ->editColumn('autorizo', function ($orden) {
                return $orden->autorizo;
            })
            ->editColumn('fecha_estimada_pieza_disponible', function ($orden) {

                $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->fecha_estimada_pieza_disponible) ? date($date_format, strtotime($orden->fecha_estimada_pieza_disponible)) : null;
            })
            ->editColumn('existe', function ($orden) {
                return $orden->existe;
            })
            ->editColumn('falta', function ($orden) {
                return $orden->falta;
            })
            ->editColumn('informo_ausencia', function ($orden) {
                return $orden->informo_ausencia;
            })->editColumn('obs_desarme_busqueda', function ($orden) {
                return $orden->obs_desarme_busqueda;
            })
            ->editColumn('fecha_desarmado_anulado', function ($orden) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->fecha_desarmado_anulado) ? date($date_format, strtotime($orden->fecha_desarmado_anulado)) : null;
            })
            ->editColumn('cargando_camioneta', function ($orden) {
                return $orden->cargando_camioneta;
            })->editColumn('entregado', function ($orden) {
                return $orden->entregado;
            })
            ->editColumn('fecha_embalado', function ($orden) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->fecha_embalado) ? date($date_format, strtotime($orden->fecha_embalado)) : null;
            })

            ->editColumn('fecha_avisado_vendedor', function ($orden) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return isset($orden->fecha_avisado_vendedor) ? date($date_format, strtotime($orden->fecha_avisado_vendedor)) : null;
            })*/
            
			
			
			
/*
            ->addColumn('cliente', function ($orden) {
                return $orden->venta->client->contact_name;
            })*/
            
           /* ->editColumn('puesto', function ($orden) {

                if (strTolower(auth()->user()->role->name) == 'administrativo de desarme' || strTolower(auth()->user()->role->name) == 'gerencial') {

                    $opciones = ['1C', '1P', '2C', '2P', '3', '4C', '4P'];
                    $operarios = ['operariocolectora@pmpc.com.ar', 'operariocolectora@pmpc.com.ar', 'operariocolectora@pmpc.com.ar', 'operariocolectora@pmpc.com.ar', 'operarioconstituyentes@pmpc.com.ar', 'operarioventanita@pmpc.com.ar', 'operarioventanita@pmpc.com.ar'];

                    $select = '<select name="puesto" class="puesto-select form-control" data-id=' . $orden->id . '>';
                    $select .= '<option value=""> </option>';
                    $i = 0;
                    foreach ($opciones as $opcion) {
                        $selected = ($orden->puesto == $opcion) ? 'selected' : '';
                        $select .= '<option value="' . $opcion . '" ' . $selected . ' data-operario ="' . $operarios[$i] . '">' . $opcion . '</option>';
                        $i++;
                    }

                    $select .= '</select>';
                    return $select;
                } else {
                    return '<span>' . $orden->puesto . '</span>';
                }
            })
            ->editColumn('f_ingreso_puesto', function ($orden) {

                if (strTolower(auth()->user()->role->name) == 'administrativo de desarme' || strTolower(auth()->user()->role->name) == 'gerencial') {
                    $fecha = isset($orden->f_ingreso_puesto) ? date('Y-m-d\TH:i', strtotime($orden->f_ingreso_puesto)) : '';
                    return '<input type="datetime-local" name="f_ingreso_puesto" value="' . $fecha . '" class="f-ingreso-puesto-input form-control">';
                } else {
                    return '<span>' . $orden->f_ingreso_puesto . '</span>';
                }
            })*/
            ->setRowId(function ($orden) {
                return "row_" . $orden->id;
            })
            ->rawColumns(['action', 'invoice_number', 'interno'])
            //->rawColumns(['action', 'members.name', 'status', 'id', 'procesar', 'invoice_number', 'interno', 'puesto', 'f_ingreso_puesto'])
            ->make(true);
		}
	}	
	
	
}
