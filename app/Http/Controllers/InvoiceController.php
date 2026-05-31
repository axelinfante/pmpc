<?php

namespace App\Http\Controllers;

use App\Lugar_entregas;
use App\Cars;
use App\Comision;
use App\Contact;
use App\Estado;
use App\Invoice;
use App\InvoiceItem;
use App\InvoiceItemTax;
use App\ProductReturn;
use App\Item;
use App\Mail\GeneralMail;
use App\Mail\InvoiceReceiptMail;
use App\Marca;
use App\Notifications\InvoiceCreated;
use App\Notifications\InvoiceProductOctubre;
use App\Notifications\InvoiceUpdated;
use App\Notifications\OrdenCreated;
use App\Notifications\InvoiceUbicationChange;
use App\Notifications\InvoiceProductMercadoLibre;
use App\Orden_desarme;
use App\SalesReturnItem;
use App\PaymentMethod;
use App\Product;
use App\SalesReturn;
use App\Project;
use App\Quotation;
use App\Role;
use App\Stock;
use App\Tax;
use App\Transaction;
use App\User;
use App\Utilities\cc_client;
use App\Utilities\Overrider;
use DataTables;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\InvoicesExport;
use App\Anulados_comision;
use App\OrdenDespacho;
use App\Transaciones_cotizaciones;
use App\Puesto;
use OwenIt\Auditing\Models\Audit;

use function PHPUnit\Framework\isNull;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    use cc_client;
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public $status;

    public function __construct()
    {
        date_default_timezone_set(get_company_option('timezone', get_option('timezone', 'Asia/Dhaka')));

        $this->middleware(function ($request, $next) {
            if (has_membership_system() == 'enabled') {
                if (!has_feature('invoice_limit')) {
                    return redirect('membership/extend')->with('message', _lang('Your Current package not support this feature. You can upgrade your package !'));
                }

                // If request is create/store
                $route_name = \Request::route()->getName();
                if ($route_name == 'invoices.store') {
                    if (!has_feature_limit('invoice_limit')) {
                        if (!$request->ajax()) {
                            return redirect('membership/extend')->with('message', _lang('Your have already reached your usages limit. You can upgrade your package !'));
                        } else {
                            return response()->json(['result' => 'error', 'message' => _lang('Your have already reached your usages limit. You can upgrade your package !')]);
                        }
                    }
                }
            }

            return $next($request);
        });

        $this->status = [
            1 => 'Pendiente',
            2 => 'Procesado'
        ];
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $rol = Role::where('name', 'Vendedor')->first()->id;
        $rol_revendedor = Role::where('name', 'Revendedor')->first()->id ?? null;
        $data['rol'] = $rol;
        $data['rol_revendedor'] = $rol_revendedor;
        return view('backend.accounting.invoice.list', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $companias_global = empty(session('cia')) ? company_id_arr() : company_id_arr();
        company_id_arr();
        $idCar = $request->get('idCar', false);
        $idProduct = $request->get('idProduct', false);

        $rol = Role::where('name', 'Vendedor')->first()->id;
        $rol_revendedor = Role::where('name', 'Revendedor')->first()->id ?? null;

        $vehiculos = Cars::with('marca_modelo')->get(); //where('company_id', company_id())->
        $item = Product::where('id', $idProduct)->with('item')->first();

        $company = \App\Company::where('business_name', 'Pentacar')->orwhere('business_name', 'Paternal')->get();

        $users = User::all()->where('user_type', '!=', 'admin')->where('user_type', '!=', 'user');
        if (!$request->ajax()) {
            return view('backend.accounting.invoice.create', compact('idCar', 'vehiculos', 'idProduct', 'item', 'users', 'rol', 'rol_revendedor', 'company'))->with(['companias_global' => $companias_global]);
        } else {
            return view(
                'backend.accounting.invoice.modal.create',
                compact(
                    'idCar',
                    'vehiculos',
                    'idProduct',
                    'item',
                    'users',
                    'rol',
                    'rol_revendedor',
                    'company'
                )
            )->with(['companias_global' => $companias_global]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
{
    @ini_set('max_execution_time', 0);
    @set_time_limit(0);

    $validator = Validator::make($request->all(), [
        'invoice_number' => 'required|max:191',
        'client_id' => 'required',
        'invoice_date' => 'required',
        'due_date' => 'required',
        'product_id' => 'required|array',       
        /*'product_id' => ['required','array', function ($attribute, $value, $fail) use ($request): void {
            $item = Product::selectRaw('GROUP_CONCAT(products.id) AS productos_observacion')
                ->leftJoin('cars', 'cars.id', '=', 'products.nro_interno')
                ->where('cars.idEstado',1)
                ->where('products.estado',"desarme")
                ->whereIn('products.id', $request->product_id)
                ->first();
            if ($item->productos_observacion) {
                $fail('Usted esta queriendo desarmar un vehiculo ya desarmado, debe agregar una observación. Productos =>'.$item->productos_observacion);
                return;
            }
        },
        ],*/
        'company_id' => 'required',
        'product_total' => 'required|numeric|not_in:NaN',
        //'product_id.*' =>  ['required', 'distinct', Rule::unique('invoice_items', 'product_id')],
    ], [
        'product_id.required' => _lang('You must select at least one product or service'),
//      'product_id.unique' => _lang('Codigo de Producto ya existe en ventas'),
    ]);

    if ($validator->fails()) {
        if ($request->ajax()) {
            return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
        } else {
            return redirect('invoices/create')
                ->withErrors($validator)
                ->withInput();
        }
    }

    $vent = Invoice::where('invoice_number', $request->input('invoice_number'))->first();
    $invoice_number = $request->input('invoice_number');
    if ($vent) {
        for ($i = $invoice_number; $i < 1000000; $i) {
            $i = $i + 1;
            $vent = Invoice::where('invoice_number', $i)->first();

            if (!$vent) {
                $invoice_number = $i;
                break;
            }
        }
    }

    DB::beginTransaction();

    $company_id = $request->input('company_id');

    $facturarOptions = $request->input('acciones', []);
    //dd($facturarOptions);

    $invoice = new Invoice();
    $invoice->invoice_number = $invoice_number;
    $invoice->invoice_date = $request->input('invoice_date');
    $invoice->due_date = $request->input('due_date');
    $invoice->grand_total = $request->product_total + $request->tax_total;
    $invoice->tax_total = $request->tax_total;
    $invoice->paid = 0;
    $invoice->status = 'Unpaid';
    $invoice->template = 'classic';
    $invoice->note = $request->input('note');
    $invoice->revendedor = $request->input('revendedor');
    //$invoice->related_to     = $request->input('related_to');
    $invoice->related_to = 'contacts';
    $vendedor = $request->input('vendedor', false);
    if (!$vendedor) {
        $vendedor = auth()->id();
    }
    $invoice->user_id = $vendedor;

    // ⚡ ASIGNACIÓN AGREGADA AQUÍ (Separado por comas)
    if (empty($facturarOptions)) {
        $invoice->acciones = null;
    } else {
        $invoice->acciones = implode(',', $facturarOptions);
    }

    if (in_array('no_desarmar', $facturarOptions)) {
        $invoice->desarmar = 0;
    } else {
        $invoice->desarmar = 1;
    }

    // $invoice->desarmar = $request->input('desarmar', 1);

    $invoice->is_usd = $request->input('is_usd', 0);
    $invoice->tasa = $request->input('tasa', null);

    /*if ($invoice->related_to == 'contacts') {
        $invoice->related_id      = $request->input('client_id');
        $invoice->client_id       = $request->input('client_id');
        $invoice->converted_total = convert_currency(base_currency(), $invoice->client->currency, $invoice->grand_total);
    } else if ($invoice->related_to == 'projects') {
        $invoice->related_id      = $request->input('project_id');
        $invoice->client_id       = Project::find($invoice->related_id)->client_id;
        $invoice->converted_total = convert_currency(base_currency(), $invoice->project->client->currency, $invoice->grand_total);
    }*/

    $invoice->related_id = $request->input('client_id');
    $invoice->client_id = $request->input('client_id');
    $invoice->converted_total = convert_currency(base_currency(), $invoice->client->currency, $invoice->grand_total);

    $invoice->company_id = $company_id;
    //$invoice->car_id = $request->input('car_id',null);
    $invoice->fecha_entrega = $request->input('fecha_entrega', null);

    if (in_array('retirar', $facturarOptions)) {
        $invoice->retiro = true;
    } else {
        $invoice->retiro = false;
    }
    // $invoice->retiro = $request->input('retiro', null);

    $invoice->entregado_a = $request->input('entregado_a', null);
    $invoice->entregado_por = $request->input('entregado_por', null);
    $invoice->ubicacion = $request->input('ubicacion', null);

    if (in_array('facturar', $facturarOptions)) {
        $invoice->facturar = true;
    } else {
        $invoice->facturar = false;
    }
    //        $invoice->facturar = $request->input('facturar', null);

    $invoice->save();

    $taxes = Tax::where('company_id', $company_id)->get();

    //Save Invoice Items
    for ($i = 0; $i < count($request->product_id); $i++) {
        //dd($request->product_id);
        $product = Product::where('id', $request->product_id[$i])->first();

        //si es del deposito octubre notificar
        if ($product->idDeposito == 4 && !$product->allCar) {
            //notificar venta de producto
            //$user = User::find(58);
            //Notification::send($user, new InvoiceProductOctubre($product));

            $user_all = User::find(['47', '49', '58', '169']);
            foreach ($user_all as $enviar_user) {
                Notification::send($enviar_user, new InvoiceProductOctubre($product));
                sleep(1);
            }
        }

        //si es del deposito octubre notificar
        if ($product->mercado_libre == 1 && !$product->allCar) {
            //notificar venta de producto de mercado libre
            /* $user = User::find(58);
            Notification::send($user, new InvoiceProductMercadoLibre($product));*/

            $user_all = User::find(['47', '49', '58', '169']);
            foreach ($user_all as $enviar_user) {
                Notification::send($enviar_user, new InvoiceProductMercadoLibre($product));
                sleep(1);
            }
        }

        $invoiceItem = new InvoiceItem();
        $invoiceItem->invoice_id = $invoice->id;
        $invoiceItem->item_id = $product->item->id;
        $invoiceItem->description = $request->product_description[$i];
        $invoiceItem->quantity = 1; //$request->quantity[$i];
        $invoiceItem->unit_cost = $request->unit_cost[$i];
        // $invoiceItem->discount = $request->discount[$i];
        //$invoiceItem->tax_method = $request->tax_method[$i];
        //$invoiceItem->tax_id = $request->tax_id[$i];
        $invoiceItem->tax_amount = $request->product_tax[$i];
        $invoiceItem->sub_total = $request->sub_total[$i];
        $invoiceItem->idCar = $request->autos[$i] ?? null;
        $invoiceItem->product_id = $product->id;
        $invoiceItem->company_id = $company_id;
        $invoiceItem->save();
        
        
        $company = '';
        if ($product->company_id  == 1) {
                        $company = 'PM-';
        } else if ($product->company_id  == 2) {
                    $company = 'PC-';
        }
        //*************************
        $desarmarValue = $product->estado ?? '';  //$request->desarmar_id[$i] ?? null;
        $estado_old=$desarmarValue;
        if ($desarmarValue == 'desarme' && $product->nro_interno > 0 ) {
                        $orden_desarme = new Orden_desarme();
                        $orden_desarme->id_venta = $invoice->id;
                        $orden_desarme->fecha_venta = $invoice->invoice_date;
                        //$orden_desarme->idCar = $product->idCar ?? null;
                        $orden_desarme->idCar = $product->idCar ?? $product->nro_interno;
                        $orden_desarme->prioridad = "normal";
                        $orden_desarme->interno = $company . ($product->idCar ?? $product->nro_interno);
                        $orden_desarme->marca_modelo = $product->marca_modelo;
                        $orden_desarme->pieza = $product->item->id; //$product->id;
                        $orden_desarme->product_id = $product->id ?? 0;
                        // Aqui colocae orden procesada y asignarla al operario segun la compañia
                        $orden_desarme->procesar = 1;
                        /*$operario = User::wherehas('role', function ($q) {
                            $q->where('name', 'Operario');
                        })->where('company_id', $product->company_id)->first();
                        $orden_desarme->idCadete_operario =  $operario->id;*/
                        $operario = Puesto::where('predeterminada', '1')->where('company_id', $product->company_id)->first();
                        $orden_desarme->idCadete_operario =  $operario->user_id ?? 0;
                        $orden_desarme->save();
                        // enviar notificacion al operario de creada una orden
                    //  Notification::send($operario, new OrdenCreated($orden_desarme));
        /*}elseif($desarmarValue == 'despacho'){
            
                $orden_despacho = new OrdenDespacho();
                $orden_despacho->invoice_id = $invoice->id;
                $orden_despacho->invoiceitem_id = $invoiceItem->id;
                $orden_despacho->description = $product->description;
                $orden_despacho->quantity = 1;
                $orden_despacho->company_id = $product->company_id;
                $orden_despacho->estatus = 'pendiente';
                $orden_despacho->save();
        */  
        }elseif($desarmarValue == 'directo'){
            // retiro directo
            
        }else{
                $orden_despacho = new OrdenDespacho();
                $orden_despacho->invoice_id = $invoice->id;
                $orden_despacho->invoiceitem_id = $invoiceItem->id;
                $orden_despacho->description = $product->description;
                $orden_despacho->quantity = 1;
                $orden_despacho->company_id = $product->company_id;
                $orden_despacho->estatus = 'pendiente';
                /*$orden_despacho->lugar_embalado = '';
                $orden_despacho->forma_entrega = '';
                $orden_despacho->despachado_por = '';
                $orden_despacho->foto_guia = '';*/
                $orden_despacho->save();
        }
        //*****************************//

            //Store Invoice Taxes
            if (isset($request->tax[$invoiceItem->item_id])) {
                foreach ($request->tax[$invoiceItem->item_id] as $taxId) {
                    $tax = $taxes->firstWhere('id', $taxId);

                    $invoiceItemTax = new InvoiceItemTax();
                    $invoiceItemTax->invoice_id = $invoiceItem->invoice_id;
                    $invoiceItemTax->invoice_item_id = $invoiceItem->id;
                    $invoiceItemTax->tax_id = $tax->id;
                    $tax_type = $tax->type == 'percent' ? '%' : '';
                    $invoiceItemTax->name = $tax->tax_name . ' @ ' . $tax->rate . $tax_type;
                    $invoiceItemTax->amount = $tax->type == 'percent' ? ($invoiceItem->sub_total / 100) * $tax->rate : $tax->rate;
                    $invoiceItemTax->company_id = $company_id;
                    $invoiceItemTax->save();
                }
            }


            $stock = Product::where("id", $invoiceItem->product_id)->first();

            //dd($stock);

            if ($stock->stock < 1) { //$request->quantity[$i]
                DB::rollBack();
                return back()->with('error', $invoiceItem->item->item_name . ' ' . _lang('Stock is not available!'));
            }

            $stock->stock = $stock->stock - $invoiceItem->quantity;
            if ($estado_old=="desarme"){
                 $stock->estado = "optimo";
            }
                 //$stock->company_id = $company_id;
            $stock->save();
            
            
        
        }
    //crear comision
    $montoAgregadoComision = 0;
    $percent = $this->comisiones[$request->comision];
    //dd($percent);
    if ($request->comision == 'Venta menos a 30000') {
        // if ($invoice->grand_total < 30000) {
        $percent = 7;
        $montoAgregadoComision = 1000;
        // } else {
        //     $percent = 7;
        // }

    }


    //sacar porcentaje con el monto de la factura
    $montoComi = ($percent * $invoice->grand_total) / 100;

    //dd($invoice->comision->id);
    if (isset($invoice->comision->id)) {
        $comision = Comision::find($invoice->comision->id);
    } else {
        $comision = new Comision();
    }

    $total = $montoComi + $montoAgregadoComision;
    if ($invoice->is_usd) {
        $total = $total * $invoice->tasa;
    }

    $comision->porcentaje = $percent;
    $comision->monto = $total;
    $comision->id_venta = $invoice->id;
    $comision->id_vendedor = $invoice->user_id;
    $comision->isPaid = null;
    $comision->tipo = $request->comision;

    if ($montoAgregadoComision) {
        $comision->isAdicional = 1;
    } else {
        $comision->isAdicional = null;
    }

    $comision->save();


    //Increment Invoice Starting number
    increment_invoice_number();

    //Update Package limit
    update_package_limit('invoice_limit');



    if ($invoice->client->user->id != null) {
        Notification::send($invoice->client->user, new InvoiceCreated($invoice));
    }
    $desarme = $invoice->desarmar;
    // dd($desarme);
    $desarme = $desarme == 1 ? false : true;
    $prioridad = $request->input('prioridad_desarmar', 'normal');


    //$this->orden_desarme($invoice, $desarme, $prioridad);
    DB::commit();

    // Pagar desde saldo a favor automáticamente (si hay saldo disponible)
    /*try {
        \App\CuentaCorriente::pagarFacturaDesdeSaldoAFavor($invoice->id, $invoice->client_id);
    } catch (\Throwable $e) {
        \Log::warning('Error en pago automático desde saldo a favor: ' . $e->getMessage(), [
            'invoice_id' => $invoice->id,
            'client_id' => $invoice->client_id
        ]);
    }*/

    if (!$request->ajax()) {
        return redirect('invoices/' . $invoice->id)->with('success', _lang('Invoice Created Sucessfully'));
    } else {
        return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Invoice Created Sucessfully'), 'data' => $invoice]);
    }
}

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $invoice = Invoice::where("id", $id)->with('transaction')->first(); //->where("company_id", company_id())

        $invoice_taxes = InvoiceItemTax::where('invoice_id', $id)
            ->selectRaw('invoice_item_taxes.*,sum(invoice_item_taxes.amount) as tax_amount')
            ->groupBy('invoice_item_taxes.tax_id')
            ->get();

        if (!$invoice) {
            return back()->with('error', _lang('Sorry, Invoice not found !'));
        }

        $desarmes = Orden_desarme::where('id_venta', $id)->get();

        $transactions = Transaction::where("invoice_id", $id)->where("amount", '>', 0)->get();
		$salesReturns = SalesReturn::with('sales_return_items')->where("customer_id",$invoice->client_id)->where("invoice_id",$invoice->id)->get(); // Get all SalesReturns with items
		$salesReturnstotal = SalesReturn::where("customer_id",$invoice->client_id)->where("invoice_id",$invoice->id)->sum('grand_total');
		$allReturnItemIds = $salesReturns->pluck('sales_return_items')->flatten()->pluck('product_id')->toArray();
		//$sales = SalesReturn::where("id",$invoice->client_id)->first();
        if (!$request->ajax()) {
            $template = $invoice->template;

            if ($invoice->template == "") {
                $template = "modern";
            }

            /*if(! file_exists(resource_path("views/backend/accounting/invoice/template/$template.blade.php"))){
            $template = InvoiceTemplate::where('id',5)
            ->where('company_id',company_id())
            ->first();

            return view("backend.accounting.invoice.template.custom",compact('invoice','transactions','template', 'id'));
            }*/

            return view("backend.accounting.invoice.template.$template", compact('invoice', 'invoice_taxes', 'transactions', 'id', 'desarmes','salesReturns','allReturnItemIds','salesReturnstotal'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $invoice = Invoice::where("id", $id)->with('invoice_items', function ($q) {
            $q->with('product');
        })->first(); //->where("company_id", company_id())
        $status = $this->status;

        $rol = Role::where('name', 'Vendedor')->first()->id;
        $rol_revendedor = Role::where('name', 'Revendedor')->first()->id ?? null;
		
		$salesReturns = SalesReturn::with('sales_return_items')->where("customer_id",$invoice->client_id)->where("invoice_id",$invoice->id)->get(); // Get // Get all SalesReturns with items
		$allReturnItemIds = $salesReturns->pluck('sales_return_items')->flatten()->pluck('product_id')->toArray();
		//$devolucionIds = SalesReturn::with('sales_return_items')->where("customer_id",$invoice->client_id)->pluck('sales_return_items.product_id');//->pluck('sales_return_items.product_id');
		
		//dd($allReturnItemIds);
		
		// Get all SalesReturn IDs that have related items
/*$returnItemIds = SalesReturn::with('sales_return_items')
                            ->where('status', 'pending') // Example filter
                            ->pluck('id');*/
		
		
		//$invoiceItems = InvoiceItem::whereIn('id',$ids)->get();

        /* $anulados = DB::table('anulados_comision')
            ->select(DB::raw("GROUP_CONCAT(product_id) as anulados"))
            ->groupBy('invoice_id')
            ->where('invoice_id',$id)
            ->value('anulados');*/

        $estatus_anulado = $this->EstatusAnulado();

        $idCar = false;
        $idProduct = false;
        $vehiculos = Cars::where('company_id', company_id())->with('marca_modelo')->get();
        $users = User::all()->where('user_type', '!=', 'admin')->where('user_type', '!=', 'user');
        if (!$request->ajax()) {
            return view(
                'backend.accounting.invoice.edit',
                compact(
                    'invoice',
                    'id',
                    'users',
                    'status',
                    'idCar',
                    'idProduct',
                    'vehiculos',
                    'rol',
                    'rol_revendedor',
                    'estatus_anulado',
					'allReturnItemIds'
                )
            );
        } else {
            return view(
                'backend.accounting.invoice.modal.edit',
                compact(
                    'invoice',
                    'id',
                    'users',
                    'status',
                    'idCar',
                    'idProduct',
                    'vehiculos',
                    'rol',
                    'rol_revendedor',
                    'estatus_anulado',
					'allReturnItemIds'
                )
            );
        }
    }
	
	    public function update(Request $request, $id)
    {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);
        $validator = Validator::make($request->all(), [
            'invoice_number' => 'required|max:191',
            'related_to' => 'required',
            'client_id' => 'required_if:related_to,contacts',
            'project_id' => 'required_if:related_to,projects',
            'invoice_date' => 'required',
            'due_date' => 'required',
            'product_id' => 'required',
            'template' => 'required',
        ], [
            'product_id.required' => _lang('You must select at least one product or service'),
        ]);
		

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('invoices.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }
		
        DB::beginTransaction();
		try {
        $company_id = $request->input('company_id');

        $invoice = Invoice::where("id", $id)->first(); //->where("company_id", $company_id)
        // dd($request->input('desarmar', 1));

        if (strtolower(auth()->user()->role->name) == 'despacho') {
            //dd('a');
            $invoice->fecha_entrega = $request->input('fecha_entrega', null);
            $invoice->retiro = $request->input('retiro', null);
            $invoice->entregado_a = $request->input('entregado_a', null);
            $invoice->entregado_por = $request->input('entregado_por', null);
            $invoice->ubicacion = $request->input('ubicacion', null);

            $path = public_path() . '/uploads/guia';

            $file = $request->file('imagen');
            if (!empty($file)):

                unlink(public_path('uploads/guia/' . $invoice->guia));
                $fileName = time() . $file->getClientOriginalName();
                $file->move($path, $fileName);

                $invoice->guia = $fileName;
            endif;

            $invoice->save();


            DB::commit();
            if (!$request->ajax()) {
                return redirect('invoices/' . $invoice->id)->with('success', _lang('Invoice updated sucessfully'));
            } else {
                return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Invoice updated sucessfully'), 'data' => $invoice]);
            }
        }

        $ubicacion_old =  $invoice->ubicacion;
        $previous_amount = $invoice->grand_total;
        $invoice->invoice_number = $request->input('invoice_number');
        $invoice->invoice_date = $request->input('invoice_date');
        $invoice->due_date = $request->input('due_date');
        $invoice->grand_total = $request->product_total + $request->tax_total;
        $invoice->tax_total = $request->tax_total;
        //$invoice->status = $request->input('status');
        $invoice->template = $request->input('template');
        $invoice->note = $request->input('note');
        $invoice->revendedor = $request->input('revendedor');
        $invoice->related_to = $request->input('related_to');

        $invoice->fecha_entrega = $request->input('fecha_entrega', null);
        $invoice->retiro = $request->input('retiro', null);
        $invoice->entregado_a = $request->input('entregado_a', null);
        $invoice->entregado_por = $request->input('entregado_por', null);
        $invoice->ubicacion = $request->input('ubicacion', null);
        $invoice->desarmar = $request->input('desarmar', 1);
        $invoice->facturar = $request->input('facturar', null);
        // $invoice->is_usd = $request->input('is_usd', 0);
        $invoice->tasa = $request->input('tasa', null);


        if ($invoice->related_to == 'contacts') {
            $invoice->related_id = $request->input('client_id');
            $invoice->client_id = $invoice->related_id;
            if ($previous_amount != $invoice->grand_total) {
                $invoice->converted_total = convert_currency(base_currency(), $invoice->client->currency, $invoice->grand_total);
            }
            $invoice->client_id = $invoice->related_id;
        } else if ($invoice->related_to == 'projects') {
            $invoice->related_id = $request->input('project_id');
            $invoice->client_id = Project::find($invoice->related_id)->client_id;
            if ($previous_amount != $invoice->grand_total) {
                $invoice->converted_total = convert_currency(base_currency(), $invoice->project->client->currency, $invoice->grand_total);
            }
        }



        if ($invoice->paid > $invoice->grand_total) {
            $monto_devolucion = $invoice->paid - $invoice->grand_total;
            $this->devolucion($invoice, true, true, $monto_devolucion);
        }
       

        $invoice->company_id = $request->input('company_id') ?? 1;
        $invoice->save();

        $taxes = Tax::where('company_id', $company_id)->get();
		
		// nuevo proceso **************************
		
		foreach ($request->product_id as $key => $id_producto) {
			//echo $id_producto . " --> " . $request->product_description[$key]." / ";
				$id_invoice_item=$request->invoiceitem_id[$key];
			if ($id_invoice_item > 0){
				//modifica 
				$invoiceItem = invoiceItem::where("id", $id_invoice_item)->first(); 
				$invoiceItem->description = $request->product_description[$key];
				$invoiceItem->quantity = $request->quantity[$key];
				$invoiceItem->unit_cost = $request->unit_cost[$key];
				$invoiceItem->tax_amount = $request->product_tax[$key];
				$invoiceItem->sub_total = $request->sub_total[$key];
				$invoiceItem->save();
				

			}else{
				//inserta
				$invoiceItem = new invoiceItem();
				$invoiceItem->invoice_id = $invoice->id;
				$invoiceItem->item_id = $request->product_items_id[$key]; //$product->item->id;
				$invoiceItem->description = $request->product_description[$key];
				$invoiceItem->quantity = $request->quantity[$key];
				$invoiceItem->unit_cost = $request->unit_cost[$key];
				// $invoiceItem->discount = $request->discount[$i];
				//$invoiceItem->tax_method = $request->tax_method[$i];
				//$invoiceItem->tax_id = $request->tax_id[$i];
				$invoiceItem->tax_amount = $request->product_tax[$key];
				$invoiceItem->sub_total = $request->sub_total[$key];
				$invoiceItem->company_id = $company_id;
				$invoiceItem->idCar = $request->autos[$key] ?? null;
				$invoiceItem->product_id = $id_producto;//$product->id;
				$invoiceItem->save();
				$this->update_stock($invoiceItem,"sub");
				
			}
			
			// orden de despacho
			$orden_despacho = OrdenDespacho::updateOrCreate(
							['invoice_id' => $invoiceItem->invoice_id,'invoiceitem_id' => $invoiceItem->id],  
							['description' => $invoiceItem->description, 'quantity' => $invoiceItem->quantity,'company_id' => $invoiceItem->company_id,'estatus' => 'pendiente'] 
			);
			//dd($orden_despacho->toSql());
			 if (isset($request->tax[$invoiceItem->item_id])) {
					 
				  foreach ($request->tax[$invoiceItem->item_id] as $taxId) {
						
						$tax = $taxes->firstWhere('id', $taxId);
						$tax_type = $tax->type == 'percent' ? '%' : '';
						$invoiceItemTax = InvoiceItemTax::updateOrCreate(
							['invoice_id' => $invoiceItem->invoice_id,'invoice_item_id' => $invoiceItem->id,'tax_id' => $tax->id],  
							['name' => $tax->tax_name . ' @ ' . $tax->rate . $tax_type, 'amount' => $tax->type == 'percent' ? ($invoiceItem->sub_total / 100) * $tax->rate : $tax->rate,'company_id' => $company_id ] 
						);
						
						/*$invoiceItemTax = new InvoiceItemTax();
						-$invoiceItemTax->invoice_id = $invoiceItem->invoice_id;
						-$invoiceItemTax->invoice_item_id = $invoiceItem->id;
						-$invoiceItemTax->tax_id = $tax->id;
						$tax_type = $tax->type == 'percent' ? '%' : '';
						$invoiceItemTax->name = $tax->tax_name . ' @ ' . $tax->rate . $tax_type;
						$invoiceItemTax->amount = $tax->type == 'percent' ? ($invoiceItem->sub_total / 100) * $tax->rate : $tax->rate;
						$invoiceItemTax->company_id = $company_id;
						$invoiceItemTax->save();*/
						
					}
				 }
		
		}
		
		//Invoiceitem_id

//************************	hasta aqui	***//

        //crear comision
        $montoAgregadoComision = 0;
        $percent = $this->comisiones[$request->comision];
        //dd($percent);
        if ($request->comision == 'Venta menos a 30000') {
            // if ($invoice->grand_total < 30000) {
            $percent = 7;
            $montoAgregadoComision = 1000;
            // } else {
            //     $percent = 7;
            // }

        }


        //sacar porcentaje con el monto de la factura
        $montoComi = ($percent * $invoice->grand_total) / 100;

        //dd($invoice->comision->id);
        if (isset($invoice->comision->id)) {
            $comision = Comision::find($invoice->comision->id);
        } else {
            $comision = new Comision();
        }
        $total = $montoComi + $montoAgregadoComision;
        if ($invoice->is_usd) {
            $total = $total * $invoice->tasa;
        }

        $comision->porcentaje = $percent;
        $comision->monto = $total;
        $comision->id_venta = $invoice->id;
        $comision->id_vendedor = $invoice->user_id;
        $comision->isPaid = $comision->isPaid ?? null;
        $comision->tipo = $request->comision;

        if ($montoAgregadoComision) {
            $comision->isAdicional = 1;
        } else {
            $comision->isAdicional = null;
        }

        $comision->save();

        if ($invoice->client->user->id != null) {
            // Notification::send(2, new InvoiceUpdated($invoice));
            Notification::send($invoice->client->user, new InvoiceUpdated($invoice));
        }


        $desarme = $request->input('desarmar', false);
		
		

        if (!$desarme) {
            //Orden_desarme::where('id_venta', $invoice->id)->delete();


           // $this->orden_desarme($invoice, $desarme);

            if ($ubicacion_old != $invoice->ubicacion) { // se produjo un movimiento de lugar de la pieza

                //Notificar a vendedor
                if ($invoice->user_id != null) {
                    Notification::send($invoice->vendedor, new InvoiceUbicationChange($invoice));
                }
            }
        }

        DB::commit();
		
		 } catch (Throwable $e) {
            DB::rollBack();
			dd($e->getMessage());
		//	toast('Error al crear la venta! ' . $e->getMessage(), 'error');
            //Log::error('Error al crear la venta', ['error' => $e->getMessage()]);
            //return redirect()->route('ventas.index')->with('error', 'Ups, algo falló');
        }

        if (!$request->ajax()) {
            return redirect('invoices/' . $invoice->id)->with('success', _lang('Invoice updated sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Invoice updated sucessfully'), 'data' => $invoice]);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update_old(Request $request, $id)
    {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);
        $validator = Validator::make($request->all(), [
            'invoice_number' => 'required|max:191',
            'related_to' => 'required',
            'client_id' => 'required_if:related_to,contacts',
            'project_id' => 'required_if:related_to,projects',
            'invoice_date' => 'required',
            'due_date' => 'required',
            'product_id' => 'required',
            'template' => 'required',
        ], [
            'product_id.required' => _lang('You must select at least one product or service'),
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('invoices.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        DB::beginTransaction();
        $company_id = $request->input('company_id');

        $invoice = Invoice::where("id", $id)->first(); //->where("company_id", $company_id)
        // dd($request->input('desarmar', 1));

        if (strtolower(auth()->user()->role->name) == 'despacho') {
            //dd('a');
            $invoice->fecha_entrega = $request->input('fecha_entrega', null);
            $invoice->retiro = $request->input('retiro', null);
            $invoice->entregado_a = $request->input('entregado_a', null);
            $invoice->entregado_por = $request->input('entregado_por', null);
            $invoice->ubicacion = $request->input('ubicacion', null);

            $path = public_path() . '/uploads/guia';

            $file = $request->file('imagen');
            if (!empty($file)):

                unlink(public_path('uploads/guia/' . $invoice->guia));
                $fileName = time() . $file->getClientOriginalName();
                $file->move($path, $fileName);

                $invoice->guia = $fileName;
            endif;

            $invoice->save();


            DB::commit();
            if (!$request->ajax()) {
                return redirect('invoices/' . $invoice->id)->with('success', _lang('Invoice updated sucessfully'));
            } else {
                return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Invoice updated sucessfully'), 'data' => $invoice]);
            }
        }

        $ubicacion_old =  $invoice->ubicacion;
        $previous_amount = $invoice->grand_total;
        $invoice->invoice_number = $request->input('invoice_number');
        $invoice->invoice_date = $request->input('invoice_date');
        $invoice->due_date = $request->input('due_date');
        $invoice->grand_total = $request->product_total + $request->tax_total;
        $invoice->tax_total = $request->tax_total;
        //$invoice->status = $request->input('status');
        $invoice->template = $request->input('template');
        $invoice->note = $request->input('note');
        $invoice->revendedor = $request->input('revendedor');
        $invoice->related_to = $request->input('related_to');

        $invoice->fecha_entrega = $request->input('fecha_entrega', null);
        $invoice->retiro = $request->input('retiro', null);
        $invoice->entregado_a = $request->input('entregado_a', null);
        $invoice->entregado_por = $request->input('entregado_por', null);
        $invoice->ubicacion = $request->input('ubicacion', null);
        $invoice->desarmar = $request->input('desarmar', 1);
        $invoice->facturar = $request->input('facturar', null);
        // $invoice->is_usd = $request->input('is_usd', 0);
        $invoice->tasa = $request->input('tasa', null);


        if ($invoice->related_to == 'contacts') {
            $invoice->related_id = $request->input('client_id');
            $invoice->client_id = $invoice->related_id;
            if ($previous_amount != $invoice->grand_total) {
                $invoice->converted_total = convert_currency(base_currency(), $invoice->client->currency, $invoice->grand_total);
            }
            $invoice->client_id = $invoice->related_id;
        } else if ($invoice->related_to == 'projects') {
            $invoice->related_id = $request->input('project_id');
            $invoice->client_id = Project::find($invoice->related_id)->client_id;
            if ($previous_amount != $invoice->grand_total) {
                $invoice->converted_total = convert_currency(base_currency(), $invoice->project->client->currency, $invoice->grand_total);
            }
        }



        if ($invoice->paid > $invoice->grand_total) {
            $monto_devolucion = $invoice->paid - $invoice->grand_total;
            $this->devolucion($invoice, true, true, $monto_devolucion);
        }
        // dd([$monto_devolucion]);

        // $vendedor = $request->input('vendedor', false);
        // if (!$vendedor) {
        //     $vendedor = auth()->id();
        // }
        // $invoice->user_id = $vendedor;

        $invoice->company_id = $request->input('company_id') ?? 1;
        $invoice->save();

        $taxes = Tax::where('company_id', $company_id)->get();

        //Update Invoice item
        $invoiceItems = InvoiceItem::where("invoice_id", $id)->get();
        foreach ($invoiceItems as $p_item) {
            $invoiceItem = InvoiceItem::find($p_item->id);


            $this->update_stock($invoiceItem);
            $invoiceItem->delete();
        }

        $invoiceItemTax = InvoiceItemTax::where("invoice_id", $id);
        $invoiceItemTax->delete();


        for ($i = 0; $i < count($request->product_id); $i++) {
            $product = Product::where('id', $request->product_id[$i])->first();

            $invoiceItem = new InvoiceItem();
            $invoiceItem->invoice_id = $invoice->id;
            $invoiceItem->item_id = $product->item->id;
            $invoiceItem->description = $request->product_description[$i];
            $invoiceItem->quantity = $request->quantity[$i];
            $invoiceItem->unit_cost = $request->unit_cost[$i];
            // $invoiceItem->discount = $request->discount[$i];
            //$invoiceItem->tax_method = $request->tax_method[$i];
            //$invoiceItem->tax_id = $request->tax_id[$i];
            $invoiceItem->tax_amount = $request->product_tax[$i];
            $invoiceItem->sub_total = $request->sub_total[$i];
            $invoiceItem->company_id = $company_id;
            $invoiceItem->idCar = $request->autos[$i] ?? null;
            $invoiceItem->product_id = $product->id;
            $invoiceItem->save();
			
			$this->update_stock($invoiceItem,"sub");


            //Store Invoice Taxes
            if (isset($request->tax[$invoiceItem->item_id])) {
                foreach ($request->tax[$invoiceItem->item_id] as $taxId) {
                    $tax = $taxes->firstWhere('id', $taxId);

                    $invoiceItemTax = new InvoiceItemTax();
                    $invoiceItemTax->invoice_id = $invoiceItem->invoice_id;
                    $invoiceItemTax->invoice_item_id = $invoiceItem->id;
                    $invoiceItemTax->tax_id = $tax->id;
                    $tax_type = $tax->type == 'percent' ? '%' : '';
                    $invoiceItemTax->name = $tax->tax_name . ' @ ' . $tax->rate . $tax_type;
                    $invoiceItemTax->amount = $tax->type == 'percent' ? ($invoiceItem->sub_total / 100) * $tax->rate : $tax->rate;
                    $invoiceItemTax->company_id = $company_id;
                    $invoiceItemTax->save();
                }
            }

            //funcion de stock

            //            if(isset($invoiceItem->idCar)){
            //                $stock = Product::where("item_id", $invoiceItem->item_id)->where('car_id', $invoiceItem->idCar)
            //                    ->first();
            //            }else{
            //                $stock = Product::where("item_id", $invoiceItem->item_id)->first();
            //            }
            //
            //
            //            //dd($stock);
            //
            //            if($stock->stock  < $request->quantity[$i]){
            //                DB::rollBack();
            //                return back()->with('error', $invoiceItem->item->item_name.' '._lang('Sin stock'));
            //            }
            //
            //            $stock->stock   = $stock->stock - $invoiceItem->quantity;
            //$stock->company_id = $company_id;
            //            $stock->save();


        }

        //crear comision
        $montoAgregadoComision = 0;
        $percent = $this->comisiones[$request->comision];
        //dd($percent);
        if ($request->comision == 'Venta menos a 30000') {
            // if ($invoice->grand_total < 30000) {
            $percent = 7;
            $montoAgregadoComision = 1000;
            // } else {
            //     $percent = 7;
            // }

        }


        //sacar porcentaje con el monto de la factura
        $montoComi = ($percent * $invoice->grand_total) / 100;

        //dd($invoice->comision->id);
        if (isset($invoice->comision->id)) {
            $comision = Comision::find($invoice->comision->id);
        } else {
            $comision = new Comision();
        }
        $total = $montoComi + $montoAgregadoComision;
        if ($invoice->is_usd) {
            $total = $total * $invoice->tasa;
        }

        $comision->porcentaje = $percent;
        $comision->monto = $total;
        $comision->id_venta = $invoice->id;
        $comision->id_vendedor = $invoice->user_id;
        $comision->isPaid = $comision->isPaid ?? null;
        $comision->tipo = $request->comision;

        if ($montoAgregadoComision) {
            $comision->isAdicional = 1;
        } else {
            $comision->isAdicional = null;
        }

        $comision->save();

        if ($invoice->client->user->id != null) {
            // Notification::send(2, new InvoiceUpdated($invoice));
            Notification::send($invoice->client->user, new InvoiceUpdated($invoice));
        }


        $desarme = $request->input('desarmar', false);

        if (!$desarme) {

            Orden_desarme::where('id_venta', $invoice->id)->delete();


         //   $this->orden_desarme($invoice, $desarme);

            if ($ubicacion_old != $invoice->ubicacion) { // se produjo un movimiento de lugar de la pieza

                //Notificar a vendedor
                if ($invoice->user_id != null) {
                    Notification::send($invoice->vendedor, new InvoiceUbicationChange($invoice));
                }
            }
        }

        DB::commit();

        if (!$request->ajax()) {
            return redirect('invoices/' . $invoice->id)->with('success', _lang('Invoice updated sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Invoice updated sucessfully'), 'data' => $invoice]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
		$resultado=$this->nota_debito($id, $request);
		return redirect('invoices')->with('success', _lang('Invoice deleted sucessfully'));
		
		/*$observacion = $request->get('note');
		$estatus="Item inventario";
		DB::beginTransaction();
		try {
        if (!empty($id)) {
			$invoice = Invoice::where("id", $id)->first();	  
			
			if  ($invoice->status == 'Canceled'){
				return redirect()->back()->with('error', 'Ya se encuentra anulada'); 
			}
			
		if ($invoice) {
				//dd("item inventario");
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
						
						//$ids =  explode(",", $id);
						//$invoiceItems = InvoiceItem::whereIn('id',$ids)->get();
					    $invoiceItems = InvoiceItem::where("invoice_id", $id)->get();	
						
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
									// stock a revision
									Product::where('id', $p_item->product_id)->update(['stock' => 0]);
									$estatus_item='pendiente';
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
						
						//if ($anular_cotizacion=='si') {
								$invoice->status = 'Canceled';	
								$invoice->note = "Anulados todos los item";
								$invoice->save();
						//}
				}
			}// final
			DB::commit();
			return redirect('invoices')->with('success', _lang('Invoice deleted sucessfully'));
		} catch (Throwable $e) {
            DB::rollBack();
			dd($e->getMessage());
        }*/
		
		
        /*DB::beginTransaction();

        if ($request->estado_prod == 'descompuesto') {
            $invoiceItems = InvoiceItem::where("invoice_id", $id)->get();
            foreach ($invoiceItems as $p_item) {
                $product = Product::find($p_item->product_id);
                $product->estado = $request->estado_prod;

                $product->save();

                // $arr[] = $p_item->product_id;
            }
        }
        //    dd($arr);


        $invoice = Invoice::find($id);



        Comision::where('id_venta', $id)->delete();
        //dd($id);

        // Verificar si la factura tiene pagos desde saldo a favor (cc_expense)
        $tienePagosDesdeSaldoAFavor = \App\Transaction::where('invoice_id', $id)
            ->where('type', 'cc_expense')
            ->exists();
        
        // Solo llamar a devolucion() si NO tiene pagos desde saldo a favor
        // Si tiene pagos desde saldo a favor, el InvoiceObserver se encargará de revertirlos
        if (!$tienePagosDesdeSaldoAFavor) {
            $this->devolucion($invoice, false);
        }


        // Transaction::where('invoice_id', $id)->delete();
        $trcc = Transaction::where('invoice_id',$id)->where('type','cc_expense')->first();
        if(!empty($trcc)){
            $trcc->delete();
        } 
        $invoice->delete();
        $invoiceItems = InvoiceItem::where("invoice_id", $id)->get();
        foreach ($invoiceItems as $p_item) {
            $invoiceItem = InvoiceItem::find($p_item->id);

            $this->update_stock($invoiceItem);

            $invoiceItem->delete();
        }

        $invoiceItemTax = InvoiceItemTax::where('invoice_id', $id);
        $invoiceItemTax->delete();

        DB::commit();

        return redirect('invoices')->with('success', _lang('Invoice deleted sucessfully'));
		*/
    }

    public function create_payment(Request $request, $id)
    {
        $invoice = Invoice::where("id", $id)->first(); //->where("company_id", company_id())
		//dd($invoice);

        $invoices = Invoice::where('client_id', $invoice->client_id)->get();
        //buscar el saldo y la cotizacion cancelada con saldo a favor
        $result = [];
        foreach ($invoices as $invoice_) :


            $paid = 0;
            foreach ($invoice_->transaction as $pagos) {
                if ($pagos->type == 'income') {
                    $paid = $paid + $pagos->base_amount;
                }
            }
            $html = "";
            $paid_dev = 0;
            $product_return_ = DB::select("select invoices.id,invoices.invoice_number,invoice_items.product_id,products_returns.product_id as productoid, invoice_items.sub_total from `invoices` inner join `invoice_items` on `invoice_items`.`invoice_id` = `invoices`.`id` left join `products_returns` on products_returns.invoice_id=invoices.id and  products_returns.product_id=invoice_items.product_id AND products_returns.status='procesada' WHERE `invoices`.`related_to` = 'contacts' AND invoices.id IN ($invoice_->id)
            GROUP BY invoices.id,invoices.invoice_number,invoice_items.product_id");

            if (isset($product_return_)) {
                //$html='Anulado</br>';
                foreach ($product_return_  as $pieza) {
                    if (!is_null($pieza->productoid)) {
                        $paid_dev = $paid_dev + $pieza->sub_total;
                    }
                }

                $paid_to = $invoice_->grand_total - ($paid + $paid_dev);
                if ($paid_to < 0) {
                    $result[] = [
                        'idCotizacion' => $invoice_->id,
                        'paid_dev' => $paid_to
                    ];
                }
            }

        endforeach;
		

           // dd($result,$invoice);
        if ($request->ajax()) {
            return view('backend.accounting.invoice.modal.create_payment', compact('invoice', 'id', 'result'))->with(['paid' => $paid]);
        }
    }

    public function store_payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'invoice_id' => 'required',
            'account_id' => 'required',
            'chart_id' => 'required',
            'amount' => 'required|numeric',
            'payment_method_id' => 'required',
            'reference' => 'nullable|max:50',
            'attachment' => 'nullable|mimes:jpeg,png,jpg,doc,pdf,docx,zip',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect('income/create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $attachment = "";
        if ($request->hasfile('attachment')) {
            $file = $request->file('attachment');
            $attachment = time() . $file->getClientOriginalName();
            $file->move(public_path() . "/uploads/transactions/", $attachment);
        }

        $cheques = [];
        $chequeTotal = $request->input('amount');
        if ($request->input('payment_method_id') == 3 && $request->input('cheques_data')) {
            $cheques = json_decode($request->input('cheques_data'), true);
            if (json_last_error() === JSON_ERROR_NONE && !empty($cheques)) {
                $chequeTotal = array_sum(array_column($cheques, 'importe'));
            }
        }

        DB::beginTransaction();



        //Update Invoice Table
        $invoice = Invoice::where("id", $request->input('invoice_id'))->first(); //->where("company_id", $company_id)
        $company_id = $invoice->company_id;




        // aqui la funcion para lo de pago desde la cotizacion
        $resultCo = [];
        //si el metodo es igual a pago desde cotizacion
        if ($request->input('payment_method_id') == 11) {
            //sacar el id de la cotizacion 0 = id cotizacion 1 =  valor
            $arr = explode('-', $request->input('idCotizacionSaldo'));

            $invoiceC =  Invoice::where("id", $arr[0])->first();
            $paid = 0;
            foreach ($invoiceC->transaction as $pagos) {
                if ($pagos->type == 'income') {
                    $paid = $paid + $pagos->base_amount;
                }
            }
            $html = "";
            $paid_dev = 0;
            $product_return_ = DB::select("select invoices.id,invoices.invoice_number,invoice_items.product_id,products_returns.product_id as productoid, invoice_items.sub_total from `invoices` inner join `invoice_items` on `invoice_items`.`invoice_id` = `invoices`.`id` left join `products_returns` on products_returns.invoice_id=invoices.id and  products_returns.product_id=invoice_items.product_id AND products_returns.status='procesada' WHERE `invoices`.`related_to` = 'contacts' AND invoices.id IN ($invoiceC->id)
            GROUP BY invoices.id,invoices.invoice_number,invoice_items.product_id");

            if (isset($product_return_)) {
                //$html='Anulado</br>';
                foreach ($product_return_  as $pieza) {
                    if (!is_null($pieza->productoid)) {
                        $paid_dev = $paid_dev + $pieza->sub_total;
                    }
                }

                $paid_toC = $invoiceC->grand_total - ($paid + $paid_dev);
                if ($paid_toC < 0) {
                    $resultCo = [
                        'idCotizacion' => $invoiceC->id,
                        'paid_dev' => $paid_toC * -1
                    ];
                }

                // obtener las transacciones del invoice viejo
                $transOld = Transaction::where('invoice_id', $invoiceC->id)->get();


                // dd($transOld);
            }
        }


        $firstTxAmount = !empty($cheques) ? $cheques[0]['importe'] : $request->input('amount');

        if (!empty($resultCo)) {
            $montoNuevoUs = $resultCo['paid_dev'];
            if ($firstTxAmount >  $resultCo['paid_dev']) {
                $montoNuevoUs = $resultCo['paid_dev'];
            } else {
                $montoNuevoUs = $firstTxAmount;
            }
        }else{
            $montoNuevoUs = $firstTxAmount;
        }

        

        

        if (($invoice->paid + $chequeTotal) > $invoice->grand_total) {
            // descontar al pago de la transaccion el monto de la factura
            $montoPrevioMasPago = $invoice->paid + $chequeTotal;
            $montoFactura = $invoice->grand_total;


            $montoTransa = $montoFactura - $invoice->paid;
            $montoCC = $montoPrevioMasPago - $montoFactura;
            //            if ($invoice->paid > $montoFactura &&  $montoPrevioMasPago > $montoFactura ){
            //                $montoTransa = false;
            //                $montoCC = $montoNuevoUs;
            //
            //            }
            if ($invoice->paid == $montoFactura || $invoice->paid > $montoFactura) {
                $montoTransa = false;
                $montoCC = $montoNuevoUs;
            }

            //            dd(['$montoTransa' => $montoTransa , '$montoCC' =>$montoCC]);

            if ($montoTransa) {
                $transaction = new Transaction();
                $transaction->trans_date = date('Y-m-d');
                $transaction->account_id = $request->input('account_id');
                $transaction->chart_id = $request->input('chart_id');

                $transaction->type = 'income';
                $transaction->dr_cr = 'cr';
                $transaction->amount = $montoNuevoUs;
                $transaction->amount_usd = $request->input('amount_usd');
                $transaction->amount_peso = $request->input('amount_pesos');
                $transaction->base_amount = convert_currency($transaction->account->account_currency, base_currency(), $montoNuevoUs);
                $transaction->payer_payee_id = $request->input('client_id');
                $transaction->payment_method_id = $request->input('payment_method_id');
                $transaction->invoice_id = $request->input('invoice_id');
                $transaction->reference = $request->input('reference');
                $transaction->note = $request->input('note');
                $transaction->attachment = $attachment;
                $transaction->company_id = $company_id;

                $transaction->tasa = $request->input('tasa');
                $transaction->usd = $request->input('usd');

                $transaction->razon_social = $request->input('razon_social');
                $transaction->tipo_comprobante_id = $request->input('tipo_comprobante_id');




                $transaction->save();

                if (!empty($cheques) && isset($cheques[0])) {
                    $transaction->banco = $cheques[0]['banco_emisor'] ?? null;
                    $transaction->cheque_nro = $cheques[0]['cheque_nro'] ?? null;
                    $transaction->cheque_vencimiento = $cheques[0]['cheque_vencimiento'] ?? null;
                    $transaction->cheque_entregado_a = $cheques[0]['cheque_entregado_a'] ?? null;
                    $transaction->save();
                }

                $invoice->paid = $montoFactura;


                if (round($invoice->paid, 2) >= $invoice->grand_total) {
                    $invoice->status = 'Paid';
                } else if (round($invoice->paid, 2) > 0 && (round($invoice->paid, 2) < $invoice->grand_total)) {
                    $invoice->status = 'Partially_Paid';
                }
                $invoice->save();


                //Send Invoice Payment Confrimation to Client
                @ini_set('max_execution_time', 0);
                @set_time_limit(0);
                Overrider::load("Settings");
                $mail = new \stdClass();
                $mail->subject = _lang('Invoice Payment');
                $mail->invoice = $invoice;
                $mail->transaction = $transaction;
                $mail->method = $transaction->payment_method->name;
                $mail->currency = currency();
                $idTrans = $transaction->id;
            } else {
                // Si no hay montoTransa (la factura ya estaba pagada o sobrepagada)
                // El excedente se manejará automáticamente por el nuevo sistema de cuenta corriente
                // NO crear transacción cc automática
                $transaction = new Transaction();
                $transaction->trans_date = date('Y-m-d');
                $transaction->account_id = $request->input('account_id');
                $transaction->chart_id = $request->input('chart_id');
                $transaction->type = 'income';
                $transaction->dr_cr = 'cr';
                $transaction->amount = $montoNuevoUs;
                $transaction->amount_usd = $request->input('amount_usd');
                $transaction->amount_peso = $request->input('amount_pesos');
                $transaction->base_amount = convert_currency($transaction->account->account_currency, base_currency(), $transaction->amount);
                $transaction->payer_payee_id = $request->input('client_id');
                $transaction->payment_method_id = $request->input('payment_method_id');
                $transaction->invoice_id = $request->input('invoice_id');
                $transaction->reference = $request->input('reference');
                $transaction->note = $request->input('note');
                $transaction->attachment = $attachment;
                $transaction->company_id = $company_id;

                $transaction->tasa = $request->input('tasa');
                $transaction->usd = $request->input('usd');

                $transaction->save();

                if (!empty($cheques) && isset($cheques[0])) {
                    $transaction->banco = $cheques[0]['banco_emisor'] ?? null;
                    $transaction->cheque_nro = $cheques[0]['cheque_nro'] ?? null;
                    $transaction->cheque_vencimiento = $cheques[0]['cheque_vencimiento'] ?? null;
                    $transaction->cheque_entregado_a = $cheques[0]['cheque_entregado_a'] ?? null;
                    $transaction->save();
                }
                
                // El excedente se manejará automáticamente por el nuevo sistema de cuenta corriente
                // NO es necesario crear transacción cc adicional
            }
            
            // NOTA: El excedente ($montoCC) se manejará automáticamente por el nuevo sistema de cuenta corriente
            // a través del TransactionObserver, que registrará el saldo a favor del cliente
            // NO crear transacción cc automática
            
        } else {
            $transaction = new Transaction();
            $transaction->trans_date = date('Y-m-d');
            $transaction->account_id = $request->input('account_id');
            $transaction->chart_id = $request->input('chart_id');
            $transaction->type = 'income';
            $transaction->dr_cr = 'cr';
            $transaction->amount = $montoNuevoUs;
            $transaction->amount_usd = $request->input('amount_usd');
            $transaction->amount_peso = $request->input('amount_pesos');
            $transaction->base_amount = convert_currency($transaction->account->account_currency, base_currency(), $transaction->amount);
            $transaction->payer_payee_id = $request->input('client_id');
            $transaction->payment_method_id = $request->input('payment_method_id');
            $transaction->invoice_id = $request->input('invoice_id');
            $transaction->reference = $request->input('reference');
            $transaction->note = $request->input('note');
            $transaction->attachment = $attachment;
            $transaction->company_id = $company_id;

            $transaction->tasa = $request->input('tasa');
            $transaction->usd = $request->input('usd');

            $transaction->save();

            if (!empty($cheques) && isset($cheques[0])) {
                $transaction->banco = $cheques[0]['banco_emisor'] ?? null;
                $transaction->cheque_nro = $cheques[0]['cheque_nro'] ?? null;
                $transaction->cheque_vencimiento = $cheques[0]['cheque_vencimiento'] ?? null;
                $transaction->cheque_entregado_a = $cheques[0]['cheque_entregado_a'] ?? null;
                $transaction->save();
            }

            $invoice->paid = $invoice->paid + $transaction->base_amount;
            if (round($invoice->paid, 2) >= $invoice->grand_total) {
                $invoice->status = 'Paid';
            } else if (round($invoice->paid, 2) > 0 && (round($invoice->paid, 2) < $invoice->grand_total)) {
                $invoice->status = 'Partially_Paid';
            }
            $invoice->save();


            //Send Invoice Payment Confrimation to Client
            @ini_set('max_execution_time', 0);
            @set_time_limit(0);
            Overrider::load("Settings");
            $mail = new \stdClass();
            $mail->subject = _lang('Invoice Payment');
            $mail->invoice = $invoice;
            $mail->transaction = $transaction;
            $mail->method = $transaction->payment_method->name;
            $mail->currency = currency();
            $idTrans = $transaction->id;
        }
        if (!empty($resultCo)) {
            //sumar a grandtotal el monto completo de la cotizacion
            // $invoiceC->grand_total = $invoiceC->grand_total + ($resultCo['paid_dev']);
            // $invoiceC->save();
            //registrar en la tabla de transaciones_cotizaciones

            //recorrer las transacciones de la cotizacion anterior
            $sumMont = 0;
            $arrTrsc = [];

            foreach ($transOld as $trs) {
                $sumMont += $trs->amount;

                if ($sumMont < $montoNuevoUs) {

                    $arrTrsc[] = ['id' => $trs->id, 'monto' => $trs->amount];

                    $tr = new Transaciones_cotizaciones();
                    $tr->idInvoiceConSaldo = $invoiceC->id;
                    $tr->idInvoiceAPagar = $request->input('invoice_id');
                    $tr->monto =  $trs->amount;
                    $tr->idTransactionOld =  $trs->id;
                    $tr->idTransactionNew =  $idTrans;
                    $tr->save();
                } elseif ($sumMont == $montoNuevoUs) {
                    $arrTrsc[] = ['id' => $trs->id, 'monto' => $trs->amount];

                    $tr = new Transaciones_cotizaciones();
                    $tr->idInvoiceConSaldo = $invoiceC->id;
                    $tr->idInvoiceAPagar = $request->input('invoice_id');
                    $tr->monto =  $trs->amount;
                    $tr->idTransactionOld =  $trs->id;
                    $tr->idTransactionNew =  $idTrans;
                    $tr->save();
                    break;
                } else if ($sumMont > $montoNuevoUs) {

                    $diferencia = $trs->amount - $montoNuevoUs;
                    $sumPrev = $sumMont - $trs->amount;


                    $diferencia = $montoNuevoUs - $sumPrev;
                    $arrTrsc[] = ['id' => $trs->id, 'monto' => $diferencia];

                    $tr = new Transaciones_cotizaciones();
                    $tr->idInvoiceConSaldo = $invoiceC->id;
                    $tr->idInvoiceAPagar = $request->input('invoice_id');
                    $tr->monto =  $diferencia;
                    $tr->idTransactionOld =  $trs->id;
                    $tr->idTransactionNew =  $idTrans;
                    $tr->save();
                    break;
                }
            }
            foreach ($arrTrsc as $t) {
                $trsEd = Transaction::find($t['id']);
                $trsEd->amount = $trsEd->amount - $t['monto'];
                $trsEd->base_amount = $trsEd->base_amount - $t['monto'];
                $trsEd->save();
            }
        }


        try {
            Mail::to($invoice->client->contact_email)->send(new InvoiceReceiptMail($mail));
        } catch (\Exception $e) {
            //Nothing
        }

        if (!empty($cheques) && count($cheques) > 1) {
            $accountModel = \App\Account::find($request->input('account_id'));
            for ($i = 1; $i < count($cheques); $i++) {
                $chq = $cheques[$i];
                $extraTx = new Transaction();
                $extraTx->trans_date = date('Y-m-d');
                $extraTx->account_id = $request->input('account_id');
                $extraTx->chart_id = $request->input('chart_id');
                $extraTx->type = 'income';
                $extraTx->dr_cr = 'cr';
                $extraTx->amount = $chq['importe'];
                $extraTx->base_amount = convert_currency(
                    $accountModel->account_currency,
                    base_currency(),
                    $chq['importe']
                );
                $extraTx->payer_payee_id = $request->input('client_id');
                $extraTx->payment_method_id = 3;
                $extraTx->invoice_id = $request->input('invoice_id');
                $extraTx->reference = $request->input('reference');
                $extraTx->note = $request->input('note');
                $extraTx->company_id = $company_id;
                $extraTx->razon_social = $request->input('razon_social');
                $extraTx->tipo_comprobante_id = $request->input('tipo_comprobante_id');
                $extraTx->tasa = $request->input('tasa');
                $extraTx->usd = $request->input('usd');
                $extraTx->banco = $chq['banco_emisor'] ?? null;
                $extraTx->cheque_nro = $chq['cheque_nro'] ?? null;
                $extraTx->cheque_vencimiento = $chq['cheque_vencimiento'] ?? null;
                $extraTx->cheque_entregado_a = $chq['cheque_entregado_a'] ?? null;
                $extraTx->save();
            }
        }

        DB::commit();

        if ($request->ajax()) {
            $request->session()->flash('success', _lang('Payment was made Sucessfully'));
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Payment was made Sucessfully'), 'data' => $transaction]);
        }
    }

    public function view_payment(Request $request, $invoice_id)
    {

        $transactions = Transaction::where("invoice_id", $invoice_id)
            ->get(); // ->where("company_id", company_id())

        if (!$request->ajax()) {
            return view('backend.accounting.invoice.view_payment', compact('transactions'));
        } else {
            return view('backend.accounting.invoice.modal.view_payment', compact('transactions'));
        }
    }

    public function create_email(Request $request, $invoice_id)
    {
        $invoice = Invoice::where("id", $invoice_id)
            ->where("company_id", company_id())->first();

        $client_email = $invoice->client->contact_email;
        if ($request->ajax()) {
            return view('backend.accounting.invoice.modal.send_email', compact('client_email', 'invoice'));
        }
    }

    public function send_email(Request $request)
    {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);
        Overrider::load("Settings");

        $validator = Validator::make($request->all(), [
            'email_subject' => 'required',
            'email_message' => 'required',
            'contact_email' => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return back()->withErrors($validator)
                    ->withInput();
            }
        }

        //Send email
        $subject = $request->input("email_subject");
        $message = $request->input("email_message");
        $contact_email = $request->input("contact_email");

        $contact = Contact::where('contact_email', $contact_email)->first();
        $invoice = Invoice::where('id', $request->invoice_id)
            ->where('company_id', company_id())
            ->first();

        $currency = currency();

        if ($contact) {
            //Replace Paremeter
            $replace = array(
                '{customer_name}' => $contact->contact_name,
                '{invoice_no}' => $invoice->invoice_number,
                '{invoice_date}' => date('d M,Y', strtotime($invoice->invoice_date)),
                '{due_date}' => date('d M,Y', strtotime($invoice->due_date)),
                '{payment_status}' => _dlang(str_replace('_', ' ', $invoice->status)),
                '{grand_total}' => decimalPlace($invoice->grand_total, $currency),
                '{amount_due}' => decimalPlace(($invoice->grand_total - $invoice->paid), $currency),
                '{total_paid}' => decimalPlace($invoice->paid, $currency),
                '{invoice_link}' => url('client/view_invoice/' . md5($invoice->id)),
            );
        }

        $mail = new \stdClass();
        $mail->subject = $subject;
        $mail->body = process_string($replace, $message);

        try {
            Mail::to($contact_email)->send(new GeneralMail($mail));
        } catch (\Exception $e) {
            if (!$request->ajax()) {
                return back()->with('error', _lang('Sorry, Error Occured !'));
            } else {
                return response()->json(['result' => 'error', 'message' => _lang('Sorry, Error Occured !')]);
            }
        }

        if (!$request->ajax()) {
            return back()->with('success', _lang('Email Send Sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Email Send Sucessfully'), 'data' => $contact]);
        }
    }
	
	public function mark_as_cancelled($id, Request $request)
    {
        $observacion = $request->get('note');
        $invoice = Invoice::where("id", $id)->first(); //->where("company_id", company_id())
        if ($invoice) {
			
		if  ($invoice->status == 'Canceled'){
				return redirect()->back()->with('error', 'Ya se encuentra anulada'); 
		}
		
		DB::beginTransaction();
		try {
						$estatus="Item inventario";
						$salesReturns = SalesReturn::with('sales_return_items')->where("customer_id",$invoice->client_id)->get(); // Get all SalesReturns with items
						$allReturnItemIds = $salesReturns->pluck('sales_return_items')->flatten()->pluck('product_id')->toArray();
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
						
						$ids =  explode(",", $id);
						
						$invoiceItems = InvoiceItem::where("invoice_id", $invoice->id)->get();
						foreach ($invoiceItems as $p_item) {
								//dd($p_item->product_id,$allReturnItemIds);							
								if(!in_array($p_item->product_id,$allReturnItemIds)){
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
									//aumenta stock
									$estatus_item='procesada';
									if ($estatus=="Item inventario"){
										Product::where('id', $p_item->product_id)->update(['stock' => 1]);
									}	
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
									
								}
						}
						// se agregan los totales
						
						$salesReturn->product_total = $grand_total_dev;
						$salesReturn->grand_total = $grand_total_dev; 
						$salesReturn->converted_total = $grand_total_dev;
						$salesReturn->save();
						// se calcula comisiones
						$comision = Comision::where('id_venta', $invoice->id)->where('id_vendedor', $invoice->user_id)->first();
						$total_comision=0;
						$comision->monto=$total_comision;
						$comision->save();
						$invoice->status = 'Canceled';	
						$invoice->note = $observacion;
						$invoice->save();
						DB::commit();
		} catch (Throwable $e) {
            DB::rollBack();
			dd($e->getMessage());
		//	toast('Error al crear la venta! ' . $e->getMessage(), 'error');
        }
			
			
            /*$invoice->status = 'Canceled';
            //dd($request->get('note'));
            if ($razon || $razon != '') {
                $invoice->note = $razon;
            }

            $invoiceItems = InvoiceItem::where("invoice_id", $id)->get();
            foreach ($invoiceItems as $p_item) {
                $invoiceItem = InvoiceItem::find($p_item->id);
                //$invoiceItem->delete();
				 if ($invoiceItem) {
					$this->update_stock($invoiceItem);
				 }
            }


            $this->devolucion($invoice);

            //monto paid a 0
            $invoice->paid = 0;
            $invoice->save();
            //eliminar comision
            $comision = Comision::where('id_venta', $id)->where('id_vendedor', $invoice->user_id)->delete();*/
            
			
			
			
			
			
			return back()->with('success', _lang('Invoice Marked as Canceled'));
        }
        return back();
    }

    /*public function mark_as_cancelled($id, Request $request)
    {
        $razon = $request->get('note');
        $invoice = Invoice::where("id", $id)->first(); //->where("company_id", company_id())
        if ($invoice) {
			
			if  ($invoice->status == 'Canceled'){
				return redirect()->back()->with('error', 'Ya se encuentra anulada'); 
			}	
			
			
            $invoice->status = 'Canceled';
            //dd($request->get('note'));
            if ($razon || $razon != '') {
                $invoice->note = $razon;
            }

            $invoiceItems = InvoiceItem::where("invoice_id", $id)->get();
            foreach ($invoiceItems as $p_item) {
                $invoiceItem = InvoiceItem::find($p_item->id);
                //$invoiceItem->delete();
				 if ($invoiceItem) {
					$this->update_stock($invoiceItem);
				 }
            }


            $this->devolucion($invoice);

            //monto paid a 0
            $invoice->paid = 0;
            $invoice->save();
            //eliminar comision
            $comision = Comision::where('id_venta', $id)->where('id_vendedor', $invoice->user_id)->delete();
            return back()->with('success', _lang('Invoice Marked as Canceled'));
        }
        return back();
    }*/

    private function update_stock($invoiceItem,$tipo="add")
    {

        $stock = Product::where("id", $invoiceItem->product_id)->first();
        //Update Stock
        // dd($stock);


        if (isset($stock)) {
            if (isset($stock->car_id)) {

                Orden_desarme::where('pieza', $stock->id)->where('id_venta', '>', 0)->where("idCar", $stock->car_id)
                    ->delete();
            } else {
                Orden_desarme::where('pieza', $stock->id)->where('id_venta', '>', 0)->delete();
            }
			if ($tipo=="add"){
				$stock->stock = $stock->stock + $invoiceItem->quantity;
			}else{
				$stock->stock = $stock->stock - $invoiceItem->quantity;
			}
           // $stock->stock = $stock->stock + $invoiceItem->quantity; //($purchase + $salesReturn) - ($sales +
            // $purchaseReturn);
            $stock->save();
        }
    }

   /*  public function orden_desarme($q, $desarme = true, $prioridad = 'normal')
    {


        $products = InvoiceItem::where('invoice_id', $q->id)->get();

        foreach ($products as $product):



            // La pieza q se vende desde “ vehículos” 
            if (!is_null($product->idCar) && $product->idCar > 0) {


                $car = Cars::where('id', $product->idCar)->first();

                if ($car->idEstado != 11) { // si el estado es diferente a no apto no autorizado para a desarme

                    $orden_desarme = new Orden_desarme();
                    $company = '';

                    if ($product->product->company_id == 1) {
                        $company = 'PM-';
                    } else if ($product->product->company_id == 2) {
                        $company = 'PC-';
                    }

                    $orden_desarme->id_venta = $q->id;
                    $orden_desarme->fecha_venta = $q->invoice_date;

                    //            dd($product);
                    $orden_desarme->idCar = $product->idCar ?? null;
                    $orden_desarme->prioridad = $prioridad;

                    $orden_desarme->interno = $company . ($product->idCar ?? $product->product->nro_interno);

                    $prodMarca = Product::where('id', $product->product_id)->first();

                    $orden_desarme->marca_modelo = $prodMarca->marca_modelo;
                    $orden_desarme->pieza = $product->product_id;

                    // Aqui colocae orden procesada y asignarla al operario segun la compañia
                    $orden_desarme->procesar = 1;

                    $operario = User::wherehas('role', function ($q) {
                        $q->where('name', 'Operario');
                    })->where('company_id', $product->product->company_id)->first();

                    $orden_desarme->idCadete_operario =  $operario->id;

                    $orden_desarme->save();

                    // enviar notificacion al operario de creada una orden
                    Notification::send($operario, new OrdenCreated($orden_desarme));
                }
            } else {
                // es una pieza desarmada en stock pasa a despacho
                //Si la cotización se hace desde “piezas” es q esta desarmada. Pasa directo a embalaje y despacho


                $orden_despacho = new OrdenDespacho();
                $company = '';

                if ($product->product->company_id == 1) {
                    $company = 'PM-';
                } else if ($product->product->company_id == 2) {
                    $company = 'PC-';
                }

                $orden_despacho->invoice_id = $q->id;
                $orden_despacho->invoiceitem_id = $product->id;
                $orden_despacho->description = $product->description;
                $orden_despacho->quantity = $product->quantity;
                $orden_despacho->company_id = $product->company_id;
                $orden_despacho->estatus = 'pendiente';

                // $operario_despacho= User::wherehas('role', function ($q) {
                //     $q->where('name', 'Operario');
                // })->where('company_id', $product->product->company_id)->first();

                // $orden_despacho->idCadete_operario =  $operario->id;

                $orden_despacho->save();
            }
        endforeach;
    } */

 public function orden_desarme_old($q, $desarme = true, $prioridad = 'normal')
    {


        $products = InvoiceItem::where('invoice_id', $q->id)->get();
        foreach ($products as $product):

			if ($desarme == true){ // $invoice->desarmar = 0; true
				 $orden_despacho = new OrdenDespacho();
                $company = '';

                if ($product->product->company_id == 1) {
                    $company = 'PM-';
                } else if ($product->product->company_id == 2) {
                    $company = 'PC-';
                }
                $orden_despacho->invoice_id = $q->id;
                $orden_despacho->invoiceitem_id = $product->id;
                $orden_despacho->description = $product->description;
                $orden_despacho->quantity = $product->quantity;
                $orden_despacho->company_id = $product->company_id;
                $orden_despacho->estatus = 'pendiente';
                $orden_despacho->save();
			}else{ // pasa a desarme
			
			   // La pieza q se vende desde “ vehículos” 
					if (!is_null($product->idCar) && $product->idCar > 0) {
					$car = Cars::where('id', $product->idCar)->first();
					if ($car->idEstado != 11) { // si el estado es diferente a no apto no autorizado para a desarme
						$orden_desarme = new Orden_desarme();
						$company = '';

						if ($product->product->company_id == 1) {
							$company = 'PM-';
						} else if ($product->product->company_id == 2) {
							$company = 'PC-';
						}

						$orden_desarme->id_venta = $q->id;
						$orden_desarme->fecha_venta = $q->invoice_date;

						//            dd($product);
						$orden_desarme->idCar = $product->idCar ?? null;
						$orden_desarme->prioridad = $prioridad;

						$orden_desarme->interno = $company . ($product->idCar ?? $product->product->nro_interno);

						$prodMarca = Product::where('id', $product->product_id)->first();

						$orden_desarme->marca_modelo = $prodMarca->marca_modelo;
						$orden_desarme->pieza = $product->product_id;

						// Aqui colocae orden procesada y asignarla al operario segun la compañia
						$orden_desarme->procesar = 1;

						$operario = User::wherehas('role', function ($q) {
							$q->where('name', 'Operario');
						})->where('company_id', $product->product->company_id)->first();

						$orden_desarme->idCadete_operario =  $operario->id;

						$orden_desarme->save();

						// enviar notificacion al operario de creada una orden
						Notification::send($operario, new OrdenCreated($orden_desarme));
                }
            } else {
                // es una pieza desarmada en stock pasa a despacho
                //Si la cotización se hace desde “piezas” es q esta desarmada. Pasa directo a embalaje y despacho

                $orden_despacho = new OrdenDespacho();
                $company = '';

                if ($product->product->company_id == 1) {
                    $company = 'PM-';
                } else if ($product->product->company_id == 2) {
                    $company = 'PC-';
                }

                $orden_despacho->invoice_id = $q->id;
                $orden_despacho->invoiceitem_id = $product->id;
                $orden_despacho->description = $product->description;
                $orden_despacho->quantity = $product->quantity;
                $orden_despacho->company_id = $product->company_id;
                $orden_despacho->estatus = 'pendiente';
                $orden_despacho->save();
            }
			
				
				
				
				
			}



         
        endforeach;
    }


    public function create_comision($idVenta)
    {
        $data['invoice'] = Invoice::find($idVenta);
        $data['comision'] = Comision::where('id_venta', $idVenta)->where('id_vendedor', $data['invoice']->user_id)->first();
        $data['id'] = $idVenta;

        $ventaMotor = InvoiceItem::where('invoice_id', $data['invoice']->id)->whereHas('item', function ($sql) {
            $sql->where('item_name', 'Motor');
        })->first();
        //dd($ventaMotor);
        $data['comisionDefault'] = 7;
        if (!empty($ventaMotor)) {
            $data['comisionDefault'] = 2.5;
        }

        return view('backend.accounting.invoice.modal.createComision', $data);
    }
    public function create_observaciones($idVenta)
    {
	/*	$q=Invoice::find($idVenta);
		$products = InvoiceItem::where('invoice_id', $q->id)->get();
        foreach ($products as $product):
						$orden_desarme = new Orden_desarme();
						$company = '';

						if ($product->product->company_id == 1) {
							$company = 'PM-';
						} else if ($product->product->company_id == 2) {
							$company = 'PC-';
						}
						$orden_desarme->id_venta = $q->id;
						$orden_desarme->fecha_venta = $q->invoice_date;
						$orden_desarme->idCar = $product->idCar ?? $product->product->nro_interno;
						$orden_desarme->prioridad = "normal";
						$orden_desarme->interno = $company . ($product->idCar ?? $product->product->nro_interno);
						$prodMarca = Product::where('id', $product->product_id)->first();
						$orden_desarme->marca_modelo = $prodMarca->marca_modelo;
   					    $orden_desarme->pieza = $product->item->id; //$product->id;
						$orden_desarme->product_id = $product->id ?? 0;

						//$orden_desarme->pieza = $product->product_id;
						// Aqui colocae orden procesada y asignarla al operario segun la compañia
						$orden_desarme->procesar = 1;
						$operario = User::wherehas('role', function ($q) {
							$q->where('name', 'Operario');
						})->where('company_id', $product->product->company_id)->first();
						$orden_desarme->idCadete_operario =  $operario->id;
						$orden_desarme->save();
						// enviar notificacion al operario de creada una orden
						Notification::send($operario, new OrdenCreated($orden_desarme));
		    endforeach;
		OrdenDespacho::where('invoice_id', $q->id)->delete();
		return false;*/
        $data['invoice'] = Invoice::find($idVenta);
        $data['id'] = $idVenta;
        return view('backend.accounting.invoice.modal.observacion', $data);
    }

    public function list_comision()
    {
        if (strtolower(auth()->user()->role->name) == 'vendedor') {
            //$comisiones = Comision::where('id_vendedor',auth()->id())->get();
            $total_monto = Comision::where('id_vendedor', auth()->id())->where('isPaid', null)->get()->sum('monto');

            $total_monto_pagado = Comision::where('id_vendedor', auth()->id())->where('isPaid', 1)->get()->sum('monto');
        } else {
            //$comisiones = Comision::all();
            $total_monto = Comision::where('isPaid', null)->sum('monto');

            $total_monto_pagado = Comision::where('isPaid', 1)->sum('monto');
        }

        $rol = Role::where('name', 'Vendedor')->first()->id;


        $data = [
            //'comisiones' => $comisiones,
            'total_monto' => $total_monto,
            'total_monto_pagado' => $total_monto_pagado
        ];
        $data['rol'] = $rol;

        return view('backend.accounting.comision.list', $data);
    }

    public function table_comision_old(Request $request)
    {
        $currency = currency();
        //$company_id = company_id();
        $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();

        //pendienteFacturar
        $aFacturar = false; //$request->get('facturar',false);

        if (strtolower(auth()->user()->role->name) == 'vendedor') {
            $comisiones = Comision::where('id_vendedor', auth()->id())->with('gasto'); //->where('isPaid', null);
            //$total_monto = Comision::where('id_vendedor',auth()->id())->get()->sum('monto');
        } else {
            $comisiones = Comision::select('*')->with('gasto'); //->where('isPaid', null);
            //$total_monto = Comision::sum('monto');
        }
        $comisiones->whereHas('invoice', function ($q) use ($company_id) {
            $q->whereIn('company_id', $company_id);
        });




        return Datatables::eloquent($comisiones)
            ->filter(function ($query) use ($request) {
                if ($request->has('invoice_number')) {
                    $query->whereHas('invoice', function ($q) use ($request) {
                        $q->where('invoice_number', 'like', "%{$request->get('invoice_number')}%");
                    });
                }
                //
                if ($request->has('vendedor')) {
                    $query->where('id_vendedor', $request->get('vendedor'));
                }
                //dd($request->get('status'));
                if ($request->has('status')) {

                    if ($request->get('status') == 0) {
                        $query->where('isPaid', null);
                    } else {
                        $query->where('isPaid', $request->get('status'));
                    }
                }
                //
                //                if ($request->has('status')) {
                //                    $query->whereIn('status', json_decode($request->get('status')));
                //                }
                //
                //                if ($request->has('date_range')) {
                //                    $date_range = explode(" - ", $request->get('date_range'));
                //                    $query->whereBetween('invoice_date', [$date_range[0], $date_range[1]]);
                //                }
            })
            ->addColumn('invoice_number', function ($comision) {
                //dd($comision);
                $in = 'VEN-';
                if ($comision->invoice->company_id == 1) {
                    $in .= 'PM-';
                } else if ($comision->invoice->company_id == 2) {
                    $in .= 'PC-';
                }
                return '<a href="' . action('InvoiceController@show', $comision->id_venta) . '">' . $in . $comision->invoice->invoice_number . '</a>';
            })

            ->addColumn('gasto', function ($comision) {
                //dd($comision->gasto->id);
                if ($comision->gasto->id) {
                    return '<a href="' . action('ExpenseController@edit', $comision->gasto->id) . '" class="ajax-modal">' . $comision->gasto->id . '</a>';
                }
            })
            //            ->filterColumn('contact_name', function ($query, $keyword) {
            //                $sql = "all_contacts.contact_name  like ?";
            //                $query->whereRaw($sql, ["%{$keyword}%"]);
            //            })
            ->editColumn('venta_monto', function ($comision) {

                return $comision->invoice->grand_total;
            })
            ->editColumn('vendedor', function ($comision) {

                return $comision->vendedor->name;
            })
            ->editColumn('monto', function ($comision) use ($currency) {
                $acc_currency = currency();
                return "<span class='float-right'> " . decimalPlace($comision->monto, $currency) . " </span>";
            })
            ->editColumn('monto_pagado', function ($comision) use ($currency) {
                $acc_currency = currency();
                if ($comision->gasto->status)
                    return "<span class='float-right'> " . decimalPlace($comision->gasto->amount, $currency) . " </span>";
            })

            ->editColumn('fecha_pago', function ($comision) use ($currency) {
                $acc_currency = currency();
                // $comision->gasto->amount
                $date_format = get_company_option('date_format', 'Y-m-d');
                if ($comision->gasto->status)

                    return isset($comision->gasto->trans_date) ? date($date_format, strtotime($comision->gasto->trans_date)) : null;
            })


            ->editColumn('status', function ($invoice) {
                return invoice_status($invoice->status);
            })
            //            ->addColumn('action', function ($invoice) use ($aFacturar) {
            //                if(!$aFacturar) {
            //                    return '<div class="dropdown text-center">'
            //                        . '<button class="btn btn-primary btn-xs dropdown-toggle" type="button"
            //data-toggle="dropdown">' . _lang('Action')
            //                        . '&nbsp;<i class="fas fa-angle-down"></i></button>'
            //                        . '<div class="dropdown-menu">'
            //                        . '<a
            // class="dropdown-item" href="' . action('InvoiceController@edit', $invoice->id) . '"><i class="fas fa-edit"></i> ' . _lang('Edit') . '</a>'
            //                        . '<a class="dropdown-item ajax-modal" href="' . action('InvoiceController@create_comision', $invoice->id) .
            //                        '"><i class="fas fa-usd"></i> ' . _lang('Comisión') . '</a>'
            //                        . '<a class="dropdown-item" href="' . action('InvoiceController@show', $invoice->id) . '" data-title="' . _lang('View Invoice') . '" data-fullscreen="true"><i class="fas fa-eye"></i> ' . _lang('View') . '</a>'
            //                        . '<a href="' . url('invoices/create_payment/' . $invoice->id) . '" data-title="' . _lang('Make Payment') . '" class="dropdown-item ajax-modal"><i class="fas fa-credit-card"></i> ' . _lang('Make Payment') . '</a>'
            //                        . '<a href="' . url('invoices/view_payment/' . $invoice->id) . '" data-title="' . _lang('View Payment') . '" data-fullscreen="true" class="dropdown-item ajax-modal"><i class="fas fa-credit-card"></i> ' . _lang('View Payment') . '</a>'
            //                        . '<form action="' . action('InvoiceController@destroy', $invoice['id']) . '" method="post">'
            //                        . csrf_field()
            //                        . '<input name="_method" type="hidden" value="DELETE">'
            //                        . '<button class="button-link btn-remove" type="submit"><i class="fas fa-recycle"></i> ' . _lang('Delete') . '</button>'
            //                        . '</form>'
            //                        . '</div>'
            //                        . '</div>';
            //
            //
            //
            //                }else{
            //
            //
            //                    return '<div class="dropdown text-center">'
            //                        . '<button class="btn btn-primary btn-xs dropdown-toggle" type="button"
            //data-toggle="dropdown">' . _lang('Action')
            //                        . '&nbsp;<i class="fas fa-angle-down"></i></button>'
            //                        . '<div class="dropdown-menu">'
            //                        . '<a
            // class="dropdown-item" href="#"><i class="fas fa-money-bill"></i> ' .
            //                        _lang('Facturar') . '</a>'
            //
            //                        . '</div>'
            //                        . '</div>';
            //
            //
            //                }
            //
            //
            //
            //            })
            ->setRowId(function ($invoice) {
                return "row_" . $invoice->id;
            })
            ->rawColumns(['invoice_number', 'monto_pagado', 'gasto', 'monto', 'action'])
            ->make(true);
    }


    //guardar con ajax observaciones de la venta
    public function store_observaciones(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'note' => 'required',
            'id_venta' => 'required',
        ]);

        if ($validator->fails()) {

            return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
        }

        $invoice = Invoice::find($request->input('id_venta'));
        $invoice->note = $request->input('note');
        $invoice->save();
        return response()->json(['result' => 'success', 'message' => _lang('Observation saved successfully')]);
    }


    public function store_comision(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'porcentaje' => 'required',
            'monto' => 'required',
            'id_venta' => 'required',
            'id_vendedor' => 'required',
        ]);

        if ($validator->fails()) {

            return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
        }



        if ($request->input('id_comision', false)) {
            $comision = Comision::find(($request->input('id_comision')));
            if ($comision->isPaid != 1 && $request->input('isPaid') == 1) {
                $this->pago_de_comision($comision);
            }
            $comision = new Comision();
            $new = false;
        } else {
            $comision = new Comision();
            $new = true;
        }

        $comision->porcentaje = $request->input('porcentaje');
        $comision->monto = $request->input('monto');
        $comision->id_venta = $request->input('id_venta');
        $comision->id_vendedor = $request->input('id_vendedor');


        $comision->isPaid = $request->input('isPaid');

        $comision->save();

        $trs = Transaction::where('id_comision', $comision->id)->first();

        // dd($trs);
        if (empty($trs) && $request->input('isPaid') == 1) {
            $this->pago_de_comision($comision);
        }




        return response()->json(['result' => 'success', 'message' => _lang('Guardado correctamente')]);
    }

    public function pago_de_comision($comision)
    {



        $methodP = PaymentMethod::where('name', 'like', '%Comision')->first();
        $rubro = get_table(
            'chart_of_accounts',
            array(
                "type=" => "expense",
                // "AND company_id=" => company_id(),
                'AND name =' => 'Comision'
            )
        );


        if (!empty($rubro[0])) {
            $rubro = $rubro[0]->id;
        }

        $transaction = new Transaction();
        $transaction->trans_date = date('Y-m-d');
        $transaction->account_id = 1; //$request->input('account_id');
        $transaction->chart_id = $rubro;
        $transaction->type = 'expense';
        $transaction->dr_cr = 'dr';
        $transaction->amount = $comision->monto;
        $transaction->base_amount = convert_currency($transaction->account->account_currency, base_currency(), $transaction->amount);

        //$transaction->payer_payee_id = $request->input('payer_payee_id');

        $transaction->payment_method_id = $methodP->id;

        $transaction->note = 'comision';
        $transaction->id_comision = $comision->id;
        $transaction->company_id = $comision->invoice->company_id;

        // $transaction->status = 1;



        $transaction->save();
    }
    public function store_comisiones_multiples(Request $request)
    {

        $invoices = $request->input('paidComi');

        if (!empty($invoices[0])) {
            foreach ($invoices as $c) {
                //verificar que no este pagada
                $invoice = Invoice::find($c);
                $comision = $invoice->comision;

                if ($comision->isPaid != 1) {
                    $this->pago_de_comision($comision);
                }
                $comision->isPaid = 1;
                $comision->save();
            }
        }


        return redirect()->back()->with('success', _lang('Comisiones calculadas y marcadas como pagadas'));
    }

    public function ventasPorFacturar()
    {
        $rol = Role::where('name', 'Vendedor')->first()->id;
        $data['rol'] = $rol;
        return view('backend.accounting.invoice.listVentasFacturar', $data);
    }




    public function buscador_de_piezas(Request $request)
    {
        $pieza = $request->get('pieza', null);
        $motor = $request->get('motor', null);
        $marca = $request->get('marca', null);
        $marcaInput = $request->get('marca-input', null);
        $modelo = $request->get('modelo', null);
        $estado = $request->get('estado', null);
        $id_car = $request->get('id_car', null);
        $show = false;

        $data['vehiculos'] = $show ? $vehiculos->get() : [];
        $data['products'] = $show ? $product->get() : [];
        //dd($data['vehiculos']);
        //$data['estados'] = $estados = Estado::all();
        $data['estados'] = Estado::select('*')->where('Activo', "Si")->orderBy('estado', 'asc')->get();
        $piezasEnCotizacion = [];
        $piezasVendidas = [];

        $data['piezasEnCotizacion'] = $piezasEnCotizacion;
        $data['piezasVendidas'] = $piezasVendidas;
        $data['tipoBaja'] = $this->tipoBaja;
        $data['marcas'] = Marca::all();
		$data['lugar_entregas'] = Lugar_entregas::all();

        return view('backend.accounting.invoice.listVendedor', $data);
    }

    /* public function get_table_autos_buscador_old(Request $request)
    {
        $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
        // $user_type = Auth::user()->user_type;
        // $datos = $this->datos();
        $cars = Cars::select('cars.*')->with(['marca_modelo', 'provincias', 'estado', 'pieza_ausente']);

        //->orderBy("projects.id","desc");

        //dd($cars);
        return Datatables::eloquent($cars)
            //


            ->filterColumn('aseguradora', function ($query, $keyword) {
                $sql = "CONCAT(users.first_name,'-',users.last_name)  like ?";
                $query->orwhereHas('aseguradora', function ($str) use ($keyword) {
                    $str->where('nombre', 'like', "%{$keyword}%");
                });
            })

            ->filterColumn('estado', function ($query, $keyword) {
                // $sql = "CONCAT(users.first_name,'-',users.last_name)  like ?";
                $query->orwhereHas('estado', function ($str) use ($keyword) {
                    $str->where('estado', 'like', "{$keyword}%");
                });
            })


            ->filterColumn('localidad', function ($query, $keyword) {
                // $sql = "CONCAT(users.first_name,'-',users.last_name)  like ?";
                $query->orwhereHas('lugar_entrega', function ($str) use ($keyword) {
                    $str->where('nombre', 'like', "{$keyword}%");
                });
            })

            ->filterColumn('marca_modelo', function ($query, $keyword) {
                $sql = "CONCAT(users.first_name,'-',users.last_name)  like ?";
                $query->orwhereHas('marca_modelo', function ($str) use ($keyword) {



                    $str->whereHas('marca', function ($str) use ($keyword) {
                        $str->where('marca', 'like', "%{$keyword}%");
                    });

                    $str->orwhereHas('modelo', function ($str) use ($keyword) {
                        $str->where('modelo', 'like', "%{$keyword}%");
                    });
                });
            })
            ->editColumn('id', function ($car) {

                if ($car->company_id == 1) {
                    $in = 'PM-';
                } else if ($car->company_id == 2) {
                    $in = 'PC-';
                }

                return '<a href="' . action('VehiculoController@show', $car->id) . '">' . $in . $car->id . '</a>';
            })
            ->editColumn('motor_en_marcha', function ($car) {



                return !empty($car->motor_en_marcha) ? 'ok' : '';
            })



            ->editColumn('marca_modelo', function ($car) {

                return ($car->marca_modelo->marca->marca ?? '') . ' ' . ($car->marca_modelo->modelo->modelo ?? '');
            })
            ->editColumn('motor_nro', function ($car) {
                return $car->motor_nro;
            })
            ->editColumn('tipo_baja', function ($car) {
                return $this->tipoBaja[$car->tipo_baja] ?? null;
            })

            ->editColumn('localidad', function ($car) {
                return $car->lugar_entrega->nombre ?? null;
            })

            ->editColumn('estado', function ($car) {


                return $car->estado->estado ?? null;
            })

            ->editColumn('fecha_confirmacion_contacto', function ($car) {
                $date_format = get_company_option('date_format', 'Y-m-d');

                return isset($car->fecha_confirmacion_contacto) ? date($date_format, strtotime($car->fecha_confirmacion_contacto)) : null;
                //return $car->fecha_entrega_asegurado_cia ?? null;
            })

            ->addColumn('pieza_no_disponible', function ($car) {
                $piezas = $car->pieza_ausente;

                $html = '';
                if (!empty($piezas)) {
                    foreach ($piezas as $pieza) {
                        $html .= $pieza->name . '<br>';
                    }
                }

                return $html;
            })

            ->addColumn('pieza_vendidas', function ($car) {
                $html = '';

                $vend = Product::where('car_id', $car->id)->where('stock', 0)->where('car_id', null)->get();
                if (isset($vend)) {
                    foreach ($vend as $pieza) {
                        $html .= $pieza->item->item_name . '<br>';
                    }
                }

                $q = Quotation::where('car_id', $car->id)->with('quotation_items')
                    ->first();

                if (isset($q->quotation_items)) {
                    // dd($q);
                    // $piezasEnCotizacion[$v->id] = $q->quotation_items;
                    //                 foreach($q->quotation_items as $it) {
                    //
                    //                 }
                    foreach ($q->quotation_items as $pieza) {
                        $html .= $pieza->product->item->item_name . '<br>';
                    }
                }

                return $html;
            })

            ->addColumn('action', function ($car) {
                if ($car->company_id == 1) {
                    $in = 'PM-';
                } else if ($car->company_id == 2) {
                    $in = 'PC-';
                }
                // $filemanager = FileManager::where('name', $in . $car->id)->first();
                $enlace = '';

                // if (!empty($filemanager)) {
                //     $enlace = '<a class="btn btn-xs" target="_blank" href="' . url(
                //         'file_manager/directory/' . encrypt($filemanager->id)
                //     ) . '"><i class="far fa-folder"></i></a>';
                // }
                $a = '<a href="' . action("VehiculoController@show", $car->id) . '" class="btn btn-primary
                btn-xs ajax-modal" data-title=" Multimedia"><i class="ti-eye"></i></a>
                ';

                if ($car->idEstado == 5 || $car->idEstado == 6 || $car->idEstado == 1) {
                    $a .= '<a href="' . action("InvoiceController@create", ["idCar" => $car->id]) . '" class="btn
                    btn-primary
                btn-xs " target="_blank" data-title=" ' . _lang('Venta') . '"><i class="ti-shopping-cart-full"></i></a>';
                } elseif ($car->idEstado != 1) {
                    $a .= '<a href="' . action('QuotationController@create', ['idCar' => $car->id]) . '" class="btn
                    btn-warning
                btn-xs " target="_blank" data-title=" ' . _lang('Reserva') . '"><i class="ti-briefcase"></i></a>';
                }

                return $a;
            })
            ->setRowId(function ($car) {
                return "row_" . $car->id;
            })
            ->rawColumns(['action', 'pieza_no_disponible', 'pieza_vendidas', 'estado', 'members.name', 'status', 'id'])
            ->make(true);
    }

    public function get_table_piezas_buscador_old(Request $request)
    {
        $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
        // $user_type = Auth::user()->user_type;
        // $datos = $this->datos();
        $products = Product::select('products.*')->with(['marcaModelo', 'item'])->where('stock', 1)->where('car_id', null);
        // $items = Item::with('product')->wherehas('product', function ($string) use ($marca, $modelo) {
        // //     $string->where('stock', 1);
        // //     $string->where('car_id', null);
        // // });
        //->orderBy("projects.id","desc");

        //dd($cars);
        return Datatables::eloquent($products)
            //
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && !empty($request->search['value'])) {
                    $keyword = $request->search['value'];
                    $query->where(function ($q) use ($keyword) {
                        $q->where('id', 'like', "%{$keyword}%")
                            ->orWhere('nro_interno', 'like', "%{$keyword}%")

                            ->orWhereHas('marcaModelo.marca', function ($subQuery) use ($keyword) {
                                $subQuery->where('marca', 'like', "%{$keyword}%");
                            })
                            ->orWhereHas('marcaModelo.modelo', function ($subQuery) use ($keyword) {
                                $subQuery->where('modelo', 'like', "%{$keyword}%");
                            })
                            ->orWhereHas('deposito', function ($subQuery) use ($keyword) {
                                $subQuery->where('nombre', 'like', "%{$keyword}%");
                            })
                            ->orWhereHas('item', function ($subQuery) use ($keyword) {
                                $subQuery->where('item_name', 'like', "%{$keyword}%");
                            })

                            ->orWhere('nro_motor', 'like', "%{$keyword}%")
                            ->orWhere('nro_oblea', 'like', "%{$keyword}%")
                            ->orWhere('ubicacion', 'like', "%{$keyword}%");
                    });
                }
            })


            ->filterColumn('deposito', function ($query, $keyword) {
                $query->orwhereHas('deposito', function ($str) use ($keyword) {
                    $str->where('nombre', 'like', "%{$keyword}%");
                });
            })

            ->filterColumn('color', function ($query, $keyword) {
                $query->orwhereHas('category', function ($str) use ($keyword) {
                    $str->whereHas('categoria', function ($str) use ($keyword) {
                        $str->where('nombre', 'like', "%{$keyword}%");
                    });
                });
            })
            ->filterColumn('product', function ($query, $keyword) {
                $query->orwhereHas('item', function ($str) use ($keyword) {
                    $str->where('item_name', 'like', "%{$keyword}%");
                });
            })

            // ->filterColumn('estado', function ($query, $keyword) {
            //     // $sql = "CONCAT(users.first_name,'-',users.last_name)  like ?";
            //     $query->orwhereHas('estado', function ($str) use ($keyword) {
            //         $str->where('estado', 'like', "%{$keyword}%");
            //     });
            // })

            ->filterColumn('marca_modelo', function ($query, $keyword) {
                $sql = "CONCAT(users.first_name,'-',users.last_name)  like ?";
                $query->orwhereHas('marcaModelo', function ($str) use ($keyword) {



                    $str->whereHas('marca', function ($str) use ($keyword) {
                        $str->where('marca', 'like', "%{$keyword}%");
                    });

                    $str->orwhereHas('modelo', function ($str) use ($keyword) {
                        $str->where('modelo', 'like', "%{$keyword}%");
                    });
                });
            })


            // ->filter(function ($query) use ($request) {




            //     if ($request->has('marcaInput')) {
            //         $query->whereHas('marcaModelo', function ($string) use ($request) {
            //             // $string->where('idMarca', $request->post('marca'));

            //             $string->whereHas('marca', function ($string) use ($request) {
            //                 $string->where('marca', 'like', '%' . $request->post('marcaInput') . '%');

            //                 // dd($request->post('marca'));

            //             });
            //         });
            //     }

            //     if ($request->has('modeloInput')) {
            //         $query->whereHas('marcaModelo', function ($string) use ($request) {
            //             // $string->where('idMarca', $request->post('marca'));

            //             $string->whereHas('modelo', function ($string) use ($request) {
            //                 $string->where('modelo', 'like', '%' . $request->post('modeloInput') . '%');

            //                 // dd($request->post('marca'));

            //             });
            //         });
            //     }

            //     if ($request->has('marca') && $request->has('modelo')) {
            //         $query->wherehas('marcaModelo', function ($string) use ($request) {
            //             $string->where('idMarca', $request->post('marca'));
            //             $string->Where('idModelo', $request->post('modelo'));
            //         });
            //     } else if ($request->has('marca')) {
            //         $query->whereHas('marcaModelo', function ($string) use ($request) {
            //             $string->where('idMarca', $request->post('marca'));

            //             // dd($request->post('marca'));

            //         });
            //     } else if ($request->has('modelo')) {
            //         $query->whereHas('marcaModelo', function ($string) use ($request) {
            //             $string->Where('idModelo', $request->post('modelo'));
            //         });
            //     }



            //     if ($request->has('pieza')) {
            //         $query->where('stock', 1)->wherehas('item', function ($string) use ($request) {
            //             // $string->where('stock', 1);
            //             $string->where('item_name', 'like', '%' . $request->post('pieza') . '%');
            //         });
            //     }

            //     if ($request->has('motor')) {
            //         $query->where('nro_motor', 'like', '%' . $request->post('motor') . '%');
            //     }

            //     if ($request->has('car_id')) {
            //         $query->where('car_id', $request->post('car_id'));
            //     }
            // })




            ->editColumn('id', function ($car) {

                if ($car->company_id == 1) {
                    $in = 'PM-';
                } else if ($car->company_id == 2) {
                    $in = 'PC-';
                }

                return $in . $car->id;
            })
            ->editColumn('nro_interno', function ($car) {



                return $car->nro_interno;
            })



            ->editColumn('marca_modelo', function ($car) {

                return ($car->marcaModelo->marca->marca ?? '') . ' ' . ($car->marcaModelo->modelo->modelo ?? '');
            })
            ->editColumn('motor', function ($car) {
                return $car->nro_motor;
            })
            ->editColumn('product', function ($car) {
                return $car->item->item_name;
            })

            ->editColumn('nro_oblea', function ($car) {


                return $car->nro_oblea;
            })

            ->editColumn('deposito', function ($car) {
                // dd($car->deposito);

                return $car->deposito->nombre ?? '';
                //return $car->fecha_entrega_asegurado_cia ?? null;
            })

            ->addColumn('color', function ($car) {
                $htm = '';
                foreach ($car->category as $ca) {
                    $htm .= $ca->categoria->nombre . '<br>';
                }
                return $htm;
            })


            ->addColumn('action', function ($car) {



                $a = '<a href="' . action('InvoiceController@create', [
                    'idProduct' =>
                    $car->id
                ])
                    . '" class="btn btn-primary
btn-xs " target="_blank" data-title=" ' . _lang('Venta') . '"><i class="ti-shopping-cart-full"></i></a>
                        <a href="' . action('ProductController@show', $car['id']) . '" data-title="' . _lang('View Product') . '" class="btn btn-primary btn-xs ajax-modal"><i class="ti-eye"></i></a>';



                return $a;
            })
            ->setRowId(function ($car) {
                return "row_" . $car->id;
            })
            ->rawColumns(['action', 'color', 'estado', 'members.name', 'status', 'id'])
            ->make(true);
    }
*/
    public function get_list_item($invoice_id)
    {
        //$invoiceItems = InvoiceItem::where('invoice_id', $invoice_id)->with('item')->get();
		//$salesReturns = SalesReturn::where("invoice_id",$invoice_id)->select('id')->get()->toArray();
		//$salesReturns = SalesReturn::with('sales_return_items')->where("customer_id",$invoice->client_id)->where("invoice_id",$invoice->id)->get(); // Get
		//dd($salesReturns);
		//$invoiceItems = InvoiceItem::where('invoice_id', $invoice_id)->whereNotIn('id', $salesReturns)->with('item')->get();
		
		
		$salesReturns = SalesReturn::join('sales_return_items', 'sales_return.id', '=', 'sales_return_items.sales_return_id')
		->select('sales_return_items.product_id')
		->where('sales_return.invoice_id', '=', $invoice_id)
		->get()->toArray();
		$invoiceItems = InvoiceItem::where('invoice_id', $invoice_id)->whereNotIn('product_id', $salesReturns)->with('item')->get();
        return response()->json(['result' => 'success', 'action' => 'get_list_item', 'message' => '', 'data' => $invoiceItems]);
    }

    public function exportExcel(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        return Excel::download(new InvoicesExport($request), 'invoices.xlsx');
    }
    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        $export = new \App\Exports\InvoicesExportPdf($request);
        return $export->generate();
    }

    public function get_table_autos_buscador(Request $request)
    {
        $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
        $cars = Cars::select('cars.*')->with(['marca_modelo', 'provincias', 'estado', 'pieza_ausente'])
            ->leftJoin('marca_modelos', 'marca_modelos.id', '=', 'cars.idMarca_modelo')
            ->leftJoin('marcas', 'marcas.id', '=', 'marca_modelos.idMarca')
            ->leftJoin('modelos', 'modelos.id', '=', 'marca_modelos.idModelo')
            ->leftJoin('piezas_ausentes', 'piezas_ausentes.id_car', '=', 'cars.id')
            ->whereIn('cars.company_id', $company_id)
            ->when($request, function ($query) use ($request) {
                $sql = "";
                $buscarmarcasola = true;
                if ($request->has('marca')) {
                    $sql = "marcas.marca LIKE '%{$request->post('marca')}%'";
                    $buscarmarcasola = false;
                }

                if ($request->has('marcaInput') && $buscarmarcasola == true) {
                    $sql = "marcas.marca LIKE '%{$request->post('marcaInput')}%'";
                }
                if ($request->has('modeloInput')) {
                    $sql .= (($sql != "") ? " and " : "") . "modelos.modelo LIKE '%{$request->post('modeloInput')}%'";
                }
                if ($request->has('pieza')) {
                    $sql .= (($sql != "") ? " and " : "") . "(cars.piezas_defectuosas LIKE '%{$request->post('pieza')}%' or piezas_ausentes.name LIKE '%{$request->post('pieza')}%')";
                    //$sql .= (($sql != "") ? " and " : "") . "cars.piezas_defectuosas LIKE '%{$request->post('pieza')}%'";
                    /* $sql .= (($sql != "") ? " and " : "") . "cars.piezas_defectuosas LIKE '%{$request->post('pieza')}%'";*/
                }
                if ($request->has('motor')) {
                    $sql .= (($sql != "") ? " and " : "") . "cars.motor_nro LIKE '%{$request->post('motor')}%'";
                }
                if ($request->has('car_id')) {
                    $sql .= (($sql != "") ? " and " : "") . "cars.id LIKE '%{$request->post('car_id')}%'";
                }

                if ($request->has('estado')) {
                    $sql .= (($sql != "") ? " and " : "") . "cars.idEstado LIKE '%{$request->post('estado')}%'";
                }

                return ($sql != "") ? $query->whereRaw($sql) : "";

                /*  $sql = "";
                $buscarmarcasola = true;
                if ($request->has('marca') && $request->has('modeloInput')) {
                    $sql = "(marcas.marca LIKE '%{$request->post('marca')}%' and modelos.modelo LIKE '%{$request->post('modeloInput')}%')";
                    $buscarmarcasola = false;
                }
                if ($request->has('marcaInput') && $request->has('modeloInput')) {
                    $sql = "(marcas.marca LIKE '%{$request->post('marcaInput')}%' and modelos.modelo LIKE '%{$request->post('modeloInput')}%')";
                    $buscarmarcasola = false;
                }

                if ($buscarmarcasola == true) {
                    $sqlinterno = "";
                    if ($request->has('marca')) {
                        $sqlinterno = "marcas.marca LIKE '%{$request->post('marca')}%'";
                    }

                    if ($request->has('marcaInput')) {
                        $sqlinterno = "marcas.marca LIKE '%{$request->post('marcaInput')}%'";
                    }

                    if ($request->has('modeloInput')) {

                        if ($sqlinterno != "") {
                            $sqlinterno = "({$sqlinterno} or modelos.modelo LIKE '%{$request->post('modeloInput')}%')";
                        } else {
                            $sqlinterno = "modelos.modelo LIKE '%{$request->post('modeloInput')}%'";
                        }
                    }
                    $sql .= $sqlinterno;
                }

                if ($request->has('motor')) {
                    $sql .= ($sql != "") ? " OR " : " ";
                    $sql .= "cars.motor_nro LIKE '%{$request->post('motor')}%'";
                }

                if ($request->has('car_id')) {
                    $sql .= ($sql != "") ? " OR " : " ";
                    $sql .= "cars.id LIKE '%{$request->post('car_id')}%'";
                }

                if ($request->has('estado')) {
                    $sql .= ($sql != "") ? " OR " : " ";
                    $sql .= "cars.idEstado = '{$request->post('estado')}'";
                }


                //pieza: 1 -- item//

                return ($sql != "") ? $query->whereRaw($sql) : "";*/
            });
        //echo $cars->toSql();
        $cars->groupBy('cars.id');
        return Datatables::eloquent($cars)
            /* ->filterColumn('estado', function ($query, $keyword) {
                $query->orwhereHas('estado', function ($str) use ($keyword) {
                    $str->where('estado', 'like', "%{$keyword}%");
                });
            })*/

            /*->filterColumn('estado', function ($query, $keyword) {
                if ($keyword != "") {
                    $ids = explode(",", $keyword);
                    //$ids=($keyword!='') ? explode(",", $keyword) : array();
                    $query->wherein('idEstado', $ids);
                }
            })*/
			
			 ->filterColumn('estado', function ($query, $keyword) {
                if ($keyword != "") {
                    $ids = explode(",", $keyword);
                    if (in_array("-1", $ids)) {
                        $query->where('idEstado', '=', "")
                            ->orWhereNull('idEstado');
                    } else {
                        $query->wherein('idEstado', $ids);
                    }
                }
            })

            ->filterColumn('marca', function ($query, $keyword) {
                $query->orwhereHas('marca_modelo', function ($str) use ($keyword) {
                    $str->whereHas('marca', function ($str) use ($keyword) {
                        $str->where('marca', 'like', "%{$keyword}%");
                    });
                    /*$str->orwhereHas('modelo', function ($str) use ($keyword) {
                        $str->where('modelo', 'like', "%{$keyword}%");
                    });*/
                });
            })

            ->filterColumn('modelo', function ($query, $keyword) {
                $query->orwhereHas('marca_modelo', function ($str) use ($keyword) {
                    $str->whereHas('modelo', function ($str) use ($keyword) {
                        $str->where('modelo', 'like', "%{$keyword}%");
                    });
                    /*$str->orwhereHas('modelo', function ($str) use ($keyword) {
                        $str->where('modelo', 'like', "%{$keyword}%");
                    });*/
                });
            })

			->filterColumn('localidad', function ($query, $keyword) {
                    $query->orWhereHas('lugar_entrega', function ($str) use ($keyword) {

                        if ($keyword != "") {
                            $ids = explode(",", $keyword);


                            if (in_array("-1", $ids)) {
                                $str->where('idLugar_entrega', '=', "")
                                    ->orWhereNull('idLugar_entrega');
                            } else {
                                $str->wherein('idLugar_entrega', $ids);
                            }
                        }
                    });
                })

            ->editColumn('id', function ($car) {

                return nroInternoAlias($car->company_id, $car->tipo_vehiculo, $car->id);;
            })
            ->editColumn('marca', function ($car) {

                return ($car->marca_modelo->marca->marca ?? '');
            })
            ->editColumn('modelo', function ($car) {

                return ($car->marca_modelo->modelo->modelo ?? '');
            })
            ->editColumn('motor_nro', function ($car) {
                return $car->motor_nro;
            })
            ->editColumn('tipo_baja', function ($car) {
                return $this->tipoBaja[$car->tipo_baja] ?? null;
            })

            ->editColumn('localidad', function ($car) {
                return $car->lugar_entrega->nombre ?? null;
            })

            ->editColumn('estado', function ($car) {


                return $car->estado->estado ?? null;
            })

            ->editColumn('fecha_confirmacion_contacto', function ($car) {
                $date_format = get_company_option('date_format', 'Y-m-d');

                return isset($car->fecha_confirmacion_contacto) ? date($date_format, strtotime($car->fecha_confirmacion_contacto)) : null;
                //return $car->fecha_entrega_asegurado_cia ?? null;
            })

            ->addColumn('pieza_no_disponible', function ($car) {
                $piezas = $car->pieza_ausente;

                $html = '';
                if (!empty($piezas)) {
                    foreach ($piezas as $pieza) {
                        $html .= $pieza->name . '<br>';
                    }
                }

                return $html;
            })

            ->addColumn('pieza_vendidas', function ($car) {
                $html = '';

                /*$vend = Product::where('nro_interno', "$car->id")->where('stock', 0)->get();
                if (isset($vend)) {
                    foreach ($vend as $pieza) {
                        $html .= $pieza->item->item_name . '<br>';
                    }
                }*/

                $vend = DB::select("SELECT t4.item_name,t1.id,t3.nro_interno,t1.invoice_number FROM invoices t1 INNER JOIN invoice_items t2 ON t2.invoice_id = t1.id INNER JOIN products t3 ON t3.id=t2.product_id LEFT JOIN items t4 ON t4.id = t3.item_id WHERE nro_interno IN (" . $car->id . ")");

                if (isset($vend)) {
                    foreach ($vend as $pieza) {
                        $html .= "($pieza->invoice_number) $pieza->item_name" . '<br>';
                    }
                }


                $q = Quotation::where('car_id', $car->id)->with('quotation_items')
                    ->first();

                if (isset($q->quotation_items)) {
                    // dd($q);
                    // $piezasEnCotizacion[$v->id] = $q->quotation_items;
                    //                 foreach($q->quotation_items as $it) {
                    //
                    //                 }
                    foreach ($q->quotation_items as $pieza) {
                        $html .= $pieza->product->item->item_name . '<br>';
                    }
                }

                return $html;
            })

            ->addColumn('action', function ($car) {
                if ($car->company_id == 1) {
                    $in = 'PM-';
                } else if ($car->company_id == 2) {
                    $in = 'PC-';
                }
                // $filemanager = FileManager::where('name', $in . $car->id)->first();
                $enlace = '';

                // if (!empty($filemanager)) {
                //     $enlace = '<a class="btn btn-xs" target="_blank" href="' . url(
                //         'file_manager/directory/' . encrypt($filemanager->id)
                //     ) . '"><i class="far fa-folder"></i></a>';
                // }
                $a = '<a href="' . action("VehiculoController@show", $car->id) . '" class="btn btn-primary
                btn-xs ajax-modal" data-title=" Multimedia"><i class="ti-eye"></i></a>
                ';

                //$diseabled = ($car->idEstado == 4) ? "disabled" : "";
                $diseabled = (in_array($car->idEstado,[4])) ? "disabled" : "";
                if ($car->idEstado == 5 || $car->idEstado == 6 || $car->idEstado == 1 || $car->idEstado == 8 || $car->idEstado == 12) {
                    $a .= '<a href="' . action("InvoiceController@create", ["idCar" => $car->id]) . '" class="btn
                    btn-primary
                btn-xs ' . $diseabled . '" target="_blank" data-title=" ' . _lang('Venta') . '"><i class="ti-shopping-cart-full"></i></a>';
                } else { //elseif ($car->idEstado != 1) {
                    $a .= '<a href="' . action('QuotationController@create', ['idCar' => $car->id]) . '" class="btn
                    btn-warning
                btn-xs ' . $diseabled . '" target="_blank"  data-title=" ' . _lang('Reserva') . '"><i class="ti-briefcase"></i></a>';
                }

                return $a;
            })
            ->setRowId(function ($car) {
                return "row_" . $car->id;
            })
            ->rawColumns(['action', 'pieza_no_disponible', 'pieza_vendidas', 'estado', 'members.name', 'status', 'id'])
            ->make(true);
    }


    public function get_table_piezas_buscador(Request $request)
    {
        $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
        //        $products = Product::select('products.*')->with(['marcaModelo', 'item'])->where('stock', 1)->where('car_id', null);

        $products = Product::select('products.*', 'cars.tipo_vehiculo', 'cars.company_id as company_id_cars')->with(['marcaModelo', 'item'])
            ->leftJoin('marca_modelos', 'marca_modelos.id', '=', 'products.marca_modelo')
            ->leftJoin('marcas', 'marcas.id', '=', 'marca_modelos.idMarca')
            ->leftJoin('modelos', 'modelos.id', '=', 'marca_modelos.idModelo')
            ->leftJoin('cars', 'cars.id', '=', 'products.nro_interno')
            ->leftJoin('items', 'items.id', '=', 'products.item_id')
            ->where('stock', '>=', 1)->where('car_id', null)
            //->whereNotIn('estado', ['desarme','desarme-stock'])
            ->where(function ($query) {
                    $query->whereNotIn('products.estado', ['desarme', 'desarme-stock'])
                          ->orWhereNull('products.estado');
                })
            //->where('stock', 1)->where('car_id', null)
            ->whereIn('products.company_id', $company_id)
            ->when($request, function ($query) use ($request) {
                $sql = "";
                $buscarmarcasola = true;
                if ($request->has('marca')) {
                    $sql = "marcas.marca LIKE '%{$request->post('marca')}%'";
                    $buscarmarcasola = false;
                }

                if ($request->has('marcaInput') && $buscarmarcasola == true) {
                    $sql = "marcas.marca LIKE '%{$request->post('marcaInput')}%'";
                }
                if ($request->has('modeloInput')) {
                    $sql .= (($sql != "") ? " and " : "") . "modelos.modelo LIKE '%{$request->post('modeloInput')}%'";
                }
                if ($request->has('pieza')) {
                    $sql .= (($sql != "") ? " and " : "") . "items.item_name LIKE '%{$request->post('pieza')}%'";
                }
                if ($request->has('motor')) {
                    $sql .= (($sql != "") ? " and " : "") . "products.nro_motor LIKE '%{$request->post('motor')}%'";
                }
                if ($request->has('car_id')) {
                    $sql .= (($sql != "") ? " and " : "") . "products.nro_interno LIKE '%{$request->post('car_id')}%'";
                }

                /*if ($request->has('estado'))  {
                    $sql .= (($sql !="") ? " and ":"") . "estados.estado LIKE '%{$request->post('estado')}%'";
                }*/

                //dd($sql);
                return ($sql != "") ? $query->whereRaw($sql) : "";
            })->orderBy('products.nro_interno', 'asc');


        return Datatables::eloquent($products)
            //
            /*  ->filter(function ($query) use ($request) {
                if ($request->has('search') && !empty($request->search['value'])) {
                    $keyword = $request->search['value'];
                    $query->where(function ($q) use ($keyword) {
                        $q->where('products.id', 'like', "%{$keyword}%")
                            ->orWhere('nro_interno', 'like', "%{$keyword}%")

                            ->orWhereHas('marcaModelo.marca', function ($subQuery) use ($keyword) {
                                $subQuery->where('marca', 'like', "%{$keyword}%");
                            })
                            ->orWhereHas('marcaModelo.modelo', function ($subQuery) use ($keyword) {
                                $words = explode(' ', $keyword);

                                foreach ($words as $word) {
                                    $subQuery->where('modelo', 'like', "%{$word}%");
                                }
                            })

                            ->orWhereHas('deposito', function ($subQuery) use ($keyword) {
                                $subQuery->where('nombre', 'like', "%{$keyword}%");
                            })
                            ->orWhereHas('item', function ($subQuery) use ($keyword) {
                                $subQuery->where('item_name', 'like', "%{$keyword}%");
                            })

                            ->orWhere('nro_motor', 'like', "%{$keyword}%")
                            ->orWhere('nro_oblea', 'like', "%{$keyword}%")
                            ->orWhere('ubicacion', 'like', "%{$keyword}%");
                    });
                }
            })*/

            ->filterColumn('id', function ($query, $keyword) {
                $query->where('products.id', 'like', "%{$keyword}");
            })
            ->filterColumn('nro_interno', function ($query, $keyword) {
                $query->where('products.nro_interno', 'like', "%{$keyword}");
            })

            /*->filterColumn('deposito', function ($query, $keyword) {
                $query->orwhereHas('deposito', function ($str) use ($keyword) {
                    $str->where('nombre', 'like', "%{$keyword}%");
                });
            })*/
			
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
            ->filterColumn('color', function ($query, $keyword) {
                $query->orwhereHas('category', function ($str) use ($keyword) {
                    $str->whereHas('categoria', function ($str) use ($keyword) {
                        $str->where('nombre', 'like', "%{$keyword}%");
                    });
                });
            })
            ->filterColumn('product', function ($query, $keyword) {
                $query->orwhereHas('item', function ($str) use ($keyword) {
                    $str->where('item_name', 'like', "%{$keyword}%");
                });
            })

            ->filterColumn('marca', function ($query, $keyword) {
                $query->orwhereHas('marcaModelo', function ($str) use ($keyword) {

                    $str->whereHas('marca', function ($str) use ($keyword) {
                        $str->where('marca', 'like', "%{$keyword}%");
                    });
                    /*$str->orwhereHas('modelo', function ($str) use ($keyword) {
                        $words = explode(' ', $keyword);
                        foreach ($words as $word) {
                            $str->where('modelo', 'like', "%{$word}%");
                        }
                    });*/
                });
            })

            ->filterColumn('modelo', function ($query, $keyword) {
                $query->orwhereHas('marcaModelo', function ($str) use ($keyword) {

                    $str->whereHas('modelo', function ($str) use ($keyword) {
                        $str->where('modelo', 'like', "%{$keyword}%");
                    });
                    /*$str->orwhereHas('modelo', function ($str) use ($keyword) {
                        $words = explode(' ', $keyword);
                        foreach ($words as $word) {
                            $str->where('modelo', 'like', "%{$word}%");
                        }
                    });*/
                });
            })


            ->editColumn('id', function ($products) {

                if ($products->company_id == 1) {
                    $in = 'PM-';
                } else if ($products->company_id == 2) {
                    $in = 'PC-';
                }

                return $in . $products->id;
            })
            ->editColumn('nro_interno', function ($products) {
                //return $car->nro_interno;
                return nroInternoAlias($products->company_id_cars, $products->tipo_vehiculo, $products->nro_interno);
            })
            ->editColumn('marca', function ($car) {

                return ($car->marcaModelo->marca->marca ?? '');
            })
            ->editColumn('modelo', function ($car) {

                return ($car->marcaModelo->modelo->modelo ?? '');
            })
            ->editColumn('motor', function ($car) {
                return $car->nro_motor;
            })
            ->editColumn('product', function ($car) {
                return $car->item->item_name;
            })

            ->editColumn('nro_oblea', function ($car) {


                return $car->nro_oblea;
            })

            ->editColumn('deposito', function ($car) {
                return $car->deposito->nombre ?? '';
            })

            ->addColumn('color', function ($car) {
                $htm = '';
                foreach ($car->category as $ca) {
                    $htm .= $ca->categoria->nombre . '<br>';
                }
                return $htm;
            })


            ->addColumn('action', function ($car) {



                $a = '<a href="' . action('InvoiceController@create', [
                    'idProduct' =>
                    $car->id
                ])
                    . '" class="btn btn-primary
btn-xs " target="_blank" data-title=" ' . _lang('Venta') . '"><i class="ti-shopping-cart-full"></i></a>
                        <a href="' . action('ProductController@show', $car['id']) . '" data-title="' . _lang('View Product') . '" class="btn btn-primary btn-xs ajax-modal"><i class="ti-eye"></i></a>';



                return $a;
            })
            ->setRowId(function ($car) {
                return "row_" . $car->id;
            })
            ->rawColumns(['action', 'color', 'estado', 'members.name', 'status', 'id'])
            ->make(true);
    }

    public function table_comision(Request $request)
    {
        $currency = currency();
        $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();


        $comisiones = Comision::select('*')->with(['gasto', 'vendedor', 'invoice'])
            //->withSum('comision','monto')
            //->withSum('punches as ptoTotal', 'pto')
            ->when($request, function ($q) use ($request) {
                if (strtolower(auth()->user()->role->name) == 'vendedor') {
                    $q->where('id_vendedor', auth()->id());
                }
                if ($request->has('vendedor')) {
                    $q->where('id_vendedor', $request->get('vendedor'));
                }
                if ($request->has('status')) {
                    if ($request->get('status') == 0) {
                        $q->where('isPaid', null);
                    } else {
                        $q->where('isPaid', $request->get('status'));
                    }
                }

                return $q;
            })
            ->whereHas('invoice', function ($q) use ($company_id, $request) {
                if ($request->has('invoice_number')) {
                    $q->where('invoice_number', 'like', "%{$request->get('invoice_number')}%");
                }

                $q->whereIn('company_id', $company_id);
                //$q->where('id_venta', 1667);
                return $q;
            });

        return Datatables::eloquent($comisiones)
            ->addColumn('invoice_number', function ($comisiones) {
                if (!empty($comisiones->invoice)) {
                    return '<a href="' . action('InvoiceController@show', $comisiones->invoice->id) . '">' .  $comisiones->invoice->invoice_number . '</a>';
                };
            })
            ->addColumn('invoice_venta', function ($comisiones) {
                if (!empty($comisiones->invoice)) {
                    $date_format = get_company_option('date_format', 'Y-m-d');
                    return isset($comisiones->invoice->invoice_date) ? date($date_format, strtotime($comisiones->invoice->invoice_date)) : '';
                };
            })
            ->addColumn('cliente', function ($comisiones) {
                return $comisiones->invoice->client->contact_name ?? '';
            })
            ->addColumn('resumen_pieza', function ($comisiones) use ($currency) {
                $html = '<table class="table">';
                $mostrado = array();
                $invoice_item = InvoiceItem::where('invoice_id', $comisiones->invoice->id)->get();
                if (!empty($invoice_item)):
                    foreach ($invoice_item as $item):
                        $modelo_actual = "";
                        if (!in_array($item->product->marca_modelo, $mostrado)) {
                            array_push($mostrado, $item->product->marca_modelo);
                            $modelo_actual = ($item->product->marcaModelo->marca->marca ?? '') . ' ' .  ($item->product->marcaModelo->modelo->modelo ?? '');
                        }
                        $html .= " <tr ><td>*.-</td><td>{$modelo_actual}</td><td>$item->quantity x " . decimalPlace($item->unit_cost, $currency) . "</td></tr>";
                    endforeach;
                endif;
                return $html .= "</table>";
            })
            ->addColumn('venta_neta', function ($comisiones) use ($currency) {
                return decimalPlace($comisiones->invoice->grand_total, $currency);
            })
            ->addColumn('anulado', function ($comisiones) use ($currency) {
                $html = '<table class="table">';
                $mostrado = array();
                $invoice_item = Anulados_comision::where('invoice_id', $comisiones->invoice->id)->get();
                if (!empty($invoice_item)):
                    foreach ($invoice_item as $item):
                        $modelo_actual = "";
                        if (!in_array($item->product->marca_modelo, $mostrado)) {
                            array_push($mostrado, $item->product->marca_modelo);
                            $modelo_actual = ($item->product->marcaModelo->marca->marca ?? '') . ' ' .  ($item->product->marcaModelo->modelo->modelo ?? '');
                        }
                        $class = "";
                        //$class= ($item->estatus=="") ? "class='bg-danger'" :"";
                        $html .= " <tr {$class}><td>*.-</td><td>{$modelo_actual}</td><td>" . decimalPlace($item->monto_anulado, $currency) . "</td></tr>";
                        if ($item->estatus != "") {
                            $html .= " <tr><td>Estatus</td><td colspan='2'>{$item->estatus}</td></tr>";
                        }
                        if ($item->observaciones != "") {
                            $html .= " <tr><td>Observacion</td><td colspan='2'>{$item->observaciones}</td></tr>";
                        }
                    endforeach;
                endif;
                return $html .= "</table>";


                //return "1111";
            })
            ->addColumn('comision', function ($comisiones) use ($currency) {
                return "<center> " . $comisiones->porcentaje . "% </center>";
            })
            ->addColumn('importe_liq', function ($comisiones) use ($currency) {
                return "<span class='float-right'> " . decimalPlace($comisiones->monto, $currency) . " </span>";
            })
            ->addColumn('importe_pag',  function ($comisiones) use ($currency) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                $html = '<table class="table">';
                $transactions = Transaction::where('id_comision', $comisiones->id)->whereIn('chart_id', array(7))->orderBy('id', 'desc')->get();
                if (!empty($transactions)):
                    foreach ($transactions as $item):
                        $html .= " <tr><td>" . decimalPlace($item->amount, $currency) . "</td><td>" . date($date_format, strtotime($item->trans_date)) . "</td></tr>";
                    endforeach;
                endif;
                return $html .= "</table>";
            })
            ->addColumn('observaciones', function ($comisiones) {
                return $comisiones->invoice->note;
            })
            ->addColumn('checkbox', function ($comisiones) use ($request) {

                if ($request->has('status') && ($request->get('status') != 0)) {
                    return "";
                }



                $d = 'disabled';
                if (!isset($comisiones->invoice->isPaid)) {
                    $d = '';
                }
                if (auth()->user()->role->name == 'Gerencial' || auth()->user()->role->name == null) {
                    return "<input $d class='form-check' name='paidComi[]' type='checkbox' value='" . $comisiones->id . "' >";
                }
                return "x";
            })
            ->withQuery('total_importe', function ($comisiones) use ($currency) {
                $total_importe = 0;
                if (!empty($comisiones)) {
                    $total_importe = $comisiones->sum('monto');
                };
                return decimalPlace($total_importe, $currency);
            })
            ->withQuery('total_pagado', function ($comisiones) use ($currency, $company_id, $request) {
                $id_vendedor = 0;
                $status = null;

                if (strtolower(auth()->user()->role->name) == 'vendedor') {
                    $id_vendedor = auth()->id();
                }
                if ($request->has('vendedor')) {
                    $id_vendedor = $request->get('vendedor');
                }

                if ($request->has('status')) {
                    $status = ($request->get('status') == 0) ? null : $request->get('status');
                }

                $datos = Comision::select('*')
                    ->leftJoin('invoices as t1', 't1.id', '=', 'comisiones.id_venta')
                    ->leftJoin('transactions as t2', 't2.id_comision', '=', 'comisiones.id');

                if ($id_vendedor != 0) {
                    $datos->where('comisiones.id_vendedor', $id_vendedor);
                }
                if ($request->has('invoice_number')) {
                    $datos->where('invoice_number', 'like', "%{$request->get('invoice_number')}%");
                }



                $datos->whereIn('t1.company_id', $company_id);
                $total_monto_pagado = $datos->where('isPaid', $status)->get()->sum('amount');

                return decimalPlace($total_monto_pagado, $currency);
            })


            ->setRowId(function ($comisiones) {
                return $comisiones->id;
            })
            ->rawColumns(['invoice_number', 'resumen_pieza', 'comision', 'importe_liq', 'importe_pag', 'checkbox', 'anulado'])
            //->with('total_importe', decimalPlace($comisiones->sum('monto'), $currency))
            //->with('total_pagado', decimalPlace(($comisiones->where('isPaid', 1)->get()->sum('monto')), $currency))
            ->make(true);

        //            $balance = DB::table('data')->sum('balance')->where('user_id' '=' $id);


    }


    public function piezasExportExcel(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $data = $this->piezas_data_export($request);

        return Excel::download(new \App\Exports\PiezasExport($data), 'piezas.xlsx');
    }

    public function piezas_data_export(Request $request)
    {
        $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();

        $products = Product::select('products.*', 'cars.tipo_vehiculo')
            ->with(['marcaModelo.marca', 'marcaModelo.modelo', 'item', 'deposito', 'category.categoria'])
            ->leftJoin('cars', 'cars.id', '=', 'products.nro_interno')
            ->where('stock', 1)
            ->where('car_id', null)
            ->when($request, function ($query) use ($request) {
                $sql = "";
                $buscarmarcasola = true;

                if ($request->has('marca') && $request->has('modeloInput')) {
                    $sql = "(marcas.marca LIKE '%{$request->post('marca')}%' and modelos.modelo LIKE '%{$request->post('modeloInput')}%')";
                    $buscarmarcasola = false;
                }
                if ($request->has('marcaInput') && $request->has('modeloInput')) {
                    $sql = "(marcas.marca LIKE '%{$request->post('marcaInput')}%' and modelos.modelo LIKE '%{$request->post('modeloInput')}%')";
                    $buscarmarcasola = false;
                }

                if ($buscarmarcasola == true) {
                    $sqlinterno = "";
                    if ($request->has('marca')) {
                        $sqlinterno = "marcas.marca LIKE '%{$request->post('marca')}%'";
                    }

                    if ($request->has('marcaInput')) {
                        $sqlinterno = "marcas.marca LIKE '%{$request->post('marcaInput')}%'";
                    }

                    if ($request->has('modeloInput')) {
                        if ($sqlinterno != "") {
                            $sqlinterno = "({$sqlinterno} or modelos.modelo LIKE '%{$request->post('modeloInput')}%')";
                        } else {
                            $sqlinterno = "modelos.modelo LIKE '%{$request->post('modeloInput')}%'";
                        }
                    }
                    $sql .= $sqlinterno;
                }

                if ($request->has('motor')) {
                    $sql .= ($sql != "") ? " OR " : " ";
                    $sql .= "products.nro_motor LIKE '%{$request->post('motor')}%'";
                }

                if ($request->has('car_id')) {
                    $sql .= ($sql != "") ? " OR " : " ";
                    $sql .= "products.nro_interno LIKE '%{$request->post('car_id')}%'";
                }

                if ($request->has('pieza')) {
                    $sql .= ($sql != "") ? " OR " : " ";
                    $sql .= "items.item_name LIKE '%{$request->post('pieza')}%'";
                }

                if ($request->has('selected_ids')) {
                    // dd($request->post('selected_ids'));
                    if ($request->post('selected_ids'))
                        $query->whereIn('products.id', $request->post('selected_ids'));
                }

                return ($sql != "") ? $query->whereRaw($sql) : "";
            });

        $products = $products->get();

        $exportData = $products->map(function ($product) {
            return [

                'id' => ($product->company_id == 1 ? 'PM-' : 'PC-') . $product->id,
                'Interno' =>  nroInternoAlias($product->company_id, $product->tipo_vehiculo, $product->nro_interno),
                'product' => $product->item->item_name,
                'marca_modelo' => ($product->marcaModelo->marca->marca ?? '') . ' ' . ($product->marcaModelo->modelo->modelo ?? ''),
                'nro_motor' => $product->nro_motor,
                'nro_oblea' => $product->nro_oblea,
                'deposito' => $product->deposito->nombre ?? '',
                'ubicacion' => $product->ubicacion,
                'description' => $product->description,

            ];
        });

        return $exportData;
    }


    public function comisiones_multiples(Request $request)
    {

        $todas = $request->input('todas');
        if (!empty($todas)) {

            $exception = DB::transaction(function () use ($request) {
                try {

                    $methodP = PaymentMethod::where('name', 'like', '%Comision')->first();
                    $rubro = get_table(
                        'chart_of_accounts',
                        array(
                            "type=" => "expense",
                            // "AND company_id=" => company_id(),
                            'AND name =' => 'Comision'
                        )
                    );


                    if (!empty($rubro[0])) {
                        $rubro = $rubro[0]->id;
                    } else {

                        return false;
                    }

                    $where = " f.isPaid is null ";

                    if ($request->has('vendedor')) {
                        $id_vendedor = $request->get('vendedor');
                        $where .= " and id_vendedor={$id_vendedor}";
                    }

                    //actualiza las comisiones
                    DB::insert("insert into transactions(trans_date,account_id,chart_id,type,dr_cr,amount,base_amount,payment_method_id,note,id_comision,company_id,created_at,updated_at) select '" . date('Y-m-d') . "',1,{$rubro},'expense','dr',
                   f.monto,f.monto,{$methodP->id},'comision',f.id,IFNULL(b.company_id,0) ,'" . date('Y-m-d H:i:s') . "','" . date('Y-m-d H:i:s') . "' from comisiones f left JOIN invoices b ON b.id=f.id_venta  where {$where}");
                    // se actulizan los pagos
                    DB::update("update comisiones as f set isPaid = 1 where {$where}");

                    DB::commit(); // all good
                    //DB::rollback();
                } catch (Exception $e) {
                    DB::rollback(); // something went wrong
                    //return $e->getMessage();
                    return $e;
                }
            });
            return is_null($exception) ? true : false;
        }


        $ids = $request->input('ids');
        if (!empty($ids[0])) {
            $exception = DB::transaction(function () use ($ids) {
                // try...catch
                try {

                    $methodP = PaymentMethod::where('name', 'like', '%Comision')->first();
                    $rubro = get_table(
                        'chart_of_accounts',
                        array(
                            "type=" => "expense",
                            // "AND company_id=" => company_id(),
                            'AND name =' => 'Comision'
                        )
                    );


                    if (!empty($rubro[0])) {
                        $rubro = $rubro[0]->id;
                    } else {

                        return false;
                    }


                    $parameter = collect($ids)->implode(',');
                    //actualiza las comisiones
                    DB::insert("insert into transactions(trans_date,account_id,chart_id,type,dr_cr,amount,base_amount,payment_method_id,note,id_comision,company_id,created_at,updated_at) select '" . date('Y-m-d') . "',1,{$rubro},'expense','dr',
                     f.monto,f.monto,{$methodP->id},'comision',f.id,b.company_id,'" . date('Y-m-d H:i:s') . "','" . date('Y-m-d H:i:s') . "' from comisiones f left JOIN invoices b ON b.id=f.id_venta  WHERE f.id in({$parameter})");
                    // se actulizan los pagos
                    DB::update("update comisiones set isPaid = 1 where id in({$parameter})");
                    DB::commit(); // all good
                } catch (Exception $e) {
                    DB::rollback(); // something went wrong
                    //return $e->getMessage();
                    return $e;
                }
            });
            return is_null($exception) ? true : false;
        }
    }

    public function comisiones_anulados(Request $request)
    {
		$id = $request->input('idProd');
		$anular_cotizacion = $request->input('anular_cotizacion');
        $estatus = $request->input('estado_prod');
        $observacion = $request->input('observacion-text');
        $coti = $request->input('id_coti');
		
		DB::beginTransaction();
		try {
		//dd($estatus);
        if (!empty($id)) {
			$invoice = Invoice::where("id", $coti)->first();
			if  ($invoice->status == 'Canceled'){
				return redirect()->back()->with('error', 'Ya se encuentra anulada'); 
			}
			
		if ($invoice) {
			
			//if ($estatus=="Item inventario" or $estatus=="Item Descompuesto") 
			if ($estatus=="pendiente") 
			{
				//dd("item inventario");
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
						
						$ids =  explode(",", $id);
						
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
									$estatus_item='procesada';
									//aumenta stock
									if ($estatus=="Item inventario"){
										Product::where('id', $p_item->product_id)->update(['stock' => 1]);
									}else{
										Product::where('id', $p_item->product_id)->update(['stock' => 0]);
										$estatus_item='pendiente';
									}	
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
						
						if ($anular_cotizacion=='si') {
								$invoice->status = 'Canceled';	
								$invoice->note = "Anulados todos los item";
								$invoice->save();
						}
				}
					
					
					
					
				}
			}// final
			DB::commit();
		} catch (Throwable $e) {
            DB::rollBack();
			dd($e->getMessage());
		//	toast('Error al crear la venta! ' . $e->getMessage(), 'error');
            //Log::error('Error al crear la venta', ['error' => $e->getMessage()]);
            //return redirect()->route('ventas.index')->with('error', 'Ups, algo falló');
        }
      return false;
    }

/*$inserts = [];
$bids = [insert your random query here]
foreach($bids as $bid) {
    $inserts[] = [ 'project_id' => $projects->project_id,
           'providers_id' => $providers->providers_id, 
           'category_id' => $projects->category , 
           'bid_price' => $bid->bid_price]; 
}
DB::table('saved_estimations')->insert($inserts);*/

    public function get_table_data_old(Request $request)
    {

        $currency = currency();
        //$company_id = company_id();
		$company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();

        //pendienteFacturar
        $aFacturar = $request->get('facturar', false);




        $projects = DB::table('projects')
            ->select('id', 'name as contact_name', DB::raw('"projects" as type'))
			->whereIn('company_id', $company_id);
        //  ->where('company_id', $company_id);
			

        $all_contacts = DB::table('contacts')
            ->select('id', 'contact_name', DB::raw('"contacts" as type'))
			->whereIn('company_id', $company_id)
            //->where('company_id', $company_id)
            ->union($projects);
			
 //->select(DB::raw('SUM(column1 + column2) as total_sum'))			

        $invoices = Invoice::joinSub($all_contacts, 'all_contacts', function ($join) {
            $join->on('invoices.related_id', '=', 'all_contacts.id')
                ->on('invoices.related_to', '=', 'all_contacts.type');
        })
            ->select("invoices.*", "all_contacts.contact_name", "all_contacts.id as contact_id")
			->whereIn('company_id', $company_id)
            // ->where('invoices.company_id', $company_id)
            ->orderBy('invoices.id', 'desc');
        if (strTolower(auth()->user()->role->name) == 'vendedor') {
            $invoices->where('invoices.user_id', auth()->id());
        }
        if ($aFacturar) {
            $invoices->where('invoices.facturar', 1)->where('invoices.facturado', null);
        }

        $invoices->when($request, function ($query) use ($request) {
            if ($request->has('invoice_number')) {
                $query->where('invoice_number', 'like', "%{$request->get('invoice_number')}%");
            }

            if ($request->has('vendedor')) {
                $query->where('user_id', $request->get('vendedor'));
            }

            if ($request->has('revendedor')) {
                $query->where('revendedor', $request->get('revendedor'));
            }

            if ($request->has('company_id')) {
                $query->where('company_id', $request->get('company_id'));
            }

            if ($request->has('status')) {
                $query->whereIn('status', json_decode($request->get('status')));
            }

            if ($request->has('date_range')) {
                $date_range = explode(" - ", $request->get('date_range'));
                //dd($date_range );
                //$query->whereBetween('invoice_date', [$date_range[0], $date_range[1]]);
                $query->whereRaw("DATE(invoice_date) BETWEEN STR_TO_DATE(?, '%d-%m-%Y') AND STR_TO_DATE(?, '%d-%m-%Y')", [$date_range[0], $date_range[1]]);
            }
        });



        return Datatables::eloquent($invoices)
            /*->filter(function ($query) use ($request) {
                if ($request->has('invoice_number')) {
                    $query->where('invoice_number', 'like', "%{$request->get('invoice_number')}%");
                }

                if ($request->has('vendedor')) {
                    $query->where('user_id', $request->get('vendedor'));
                }

                if ($request->has('revendedor')) {
                    $query->where('revendedor', $request->get('revendedor'));
                }

                if ($request->has('company_id')) {
                    $query->where('company_id', $request->get('company_id'));
                }

                if ($request->has('status')) {
                    $query->whereIn('status', json_decode($request->get('status')));
                }

                if ($request->has('date_range')) {
                    $date_range = explode(" - ", $request->get('date_range'));
                    //dd($date_range );
                    //$query->whereBetween('invoice_date', [$date_range[0], $date_range[1]]);
                    $query->whereRaw("DATE(invoice_date) BETWEEN STR_TO_DATE(?, '%d-%m-%Y') AND STR_TO_DATE(?, '%d-%m-%Y')", [$date_range[0], $date_range[1]]);
                }
            })*/
            ->addColumn('checkbox', function ($invoice) {
                //dd($comision);
                $d = 'disabled';
                // dd(!isset($invoice->comision->isPaid));
                if (!isset($invoice->comision->isPaid)) {
                    $d = '';
                }
                if (auth()->user()->role->name == 'Gerencial' || auth()->user()->role->name == null) {
                    return "<input $d class='form-check' name='paidComi[]' type='checkbox' value='" . $invoice->id . "' >";
                }
                return "x";
            })
            ->addColumn('invoice_number', function ($invoice) {
                //dd($comision);
                $in = 'VEN-';
                if ($invoice->company_id == 1) {
                    $in .= 'PM-';
                } else if ($invoice->company_id == 2) {
                    $in .= 'PC-';
                }
                return '<a href="' . action('InvoiceController@show', $invoice->id) . '">' . $in . $invoice->invoice_number . '</a>';
            })
            ->addColumn('contact_name', function ($invoice) {
                if ($invoice->related_to == 'contacts') {
                    return '<a href="' . action('ContactController@show', $invoice->related_id) . '">' . $invoice->contact_name . ' <span class="text-muted small">(' . _lang('Customer') . ')</span></a>';
                }
                return '<a href="' . action('ProjectController@show', $invoice->related_id) . '">' . $invoice->contact_name . ' <span class="text-muted small">(' . _lang('Project') . ')</span></a>';
            })


            ->addColumn('producto', function ($invoice) {
                $invoice_item = InvoiceItem::where('invoice_id', $invoice->id)->get();
                if (!empty($invoice_item)):
                    $html = '';
                    foreach ($invoice_item as $item):
						$mostrar="";
						
						if ($item->product_id > 0){
							$mostrar= $item->product->item->item_name;
						}else{
							$mostrar= $item->item->item_name;
						}	
							
                        $html .= $mostrar . (($html != "") ? ',' : '') . '<br>';
                    endforeach;

                endif;

                return $html;
            })
            ->addColumn('idproducto', function ($invoice) {
                $invoice_item = InvoiceItem::where('invoice_id', $invoice->id)->get();
                //dd($invoice_item);
                if (!empty($invoice_item)):
                    $html = '';
                    foreach ($invoice_item as $item):
                        $in = ($item->company_id == 1) ? 'PM-' : 'PC-';
                        $html .= $in . $item->product_id . (($html != "") ? ',' : '') . '<br>';
                    endforeach;
                endif;
                return $html;
            })
            ->filterColumn('idproducto', function ($query, $keyword) {
                $query->whereHas('invoice_items', function ($q) use ($keyword) {
                    $q->where('product_id', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('nro_interno', function ($invoice) {
                $invoice_item = InvoiceItem::where('invoice_id', $invoice->id)->get();

                //dd($invoice_item);
                if (!empty($invoice_item)):
                    $html = '';
                    $ingresado = array();
                    foreach ($invoice_item as $item):
                        if (!in_array($item->product->nro_interno, $ingresado)) {
                            array_push($ingresado, $item->product->nro_interno);
                            $html .= nroInternoAlias($item->product->company_id, $item->product->tipo_vehiculo, $item->product->nro_interno) . '<br>';
                        }
                    endforeach;

                endif;

                return $html;
            })->filterColumn('nro_interno', function ($query, $keyword) {
                $query->orwhereHas('invoice_items', function ($str) use ($keyword) {
                    $str->whereHas('product', function ($str) use ($keyword) {
                        $str->where('products.nro_interno', 'like', "%{$keyword}");
                        /*$str->whereHas('item', function ($str) use ($keyword) {
                            $str->where('items.product.nro_interno', 'like', "%{$keyword}%");
                        });*/
                    });
                });
            })
            ->filterColumn('producto', function ($query, $keyword) {
                $query->orwhereHas('invoice_items', function ($str) use ($keyword) {
                    $str->whereHas('product', function ($str) use ($keyword) {
                        $str->whereHas('item', function ($str) use ($keyword) {
                            $str->where('item_name', 'like', "%{$keyword}%");
                        });
                    });
                });
            })

            ->filterColumn('vendedor', function ($query, $keyword) {
                $query->orwhereHas('vendedor', function ($str) use ($keyword) {
                    $str->where('name', 'like', "%{$keyword}%");
                });
            })

            ->filterColumn('fecha_pago', function ($query, $keyword) {
                $query->orwhereHas('transaction', function ($str) use ($keyword) {
                    $str->where('trans_date', 'like', "%{$keyword}%");
                });
            })

           /*  ->filterColumn('porcentajeComision', function ($query, $keyword) {



                $query->orwhereHas('comision', function ($str) use ($keyword) {
                    $str->where('porcentaje', 'like', "%{$keyword}%");
                });
            })*/
            ->filterColumn('invoice_number', function ($query, $keyword) {


                $sql = "CONCAT(users.first_name,'-',users.last_name)  like ?";
                // $query->orwhereHas('tramitador', function ($str) use ($keyword) {
                //     $str->where('name', 'like', "%{$keyword}%");
                // });

                $query->where('invoice_number', 'like', "%{$keyword}%");
            })
            ->filterColumn('contact_name', function ($query, $keyword) {
                $sql = "all_contacts.contact_name  like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->editColumn('invoice_date', function ($invoice) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return date($date_format, strtotime($invoice->invoice_date));
            })
            ->editColumn('fecha_entrega', function ($invoice) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                if ($invoice->fecha_entrega)
                    return date($date_format, strtotime($invoice->fecha_entrega));
                else
                    return '';
            })


            //ultima fecha de pago
            ->editColumn('fecha_pago', function ($invoice) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                $transactions = Transaction::where("invoice_id", $invoice->id)->orderBy('id', 'desc')->first();
                if (isset($transactions)) {
                    return date($date_format, strtotime($transactions->trans_date));
                }
                return '';
            })
            ->editColumn('monto_adeudado', function ($invoice) use ($currency) {
				$salesReturnstotal = SalesReturn::where("customer_id",$invoice->client_id)->where("invoice_id",$invoice->id)->sum('grand_total');
				$invoicepaidtotal = Transaction::where("type","income")->where("dr_cr","cr")->where("invoice_id",$invoice->id)->sum('base_amount');
                $t = (($invoice->grand_total-$salesReturnstotal) - $invoicepaidtotal);
                $acc_currency = currency($invoice->client->currency);
                if ($invoice->usd) {
                    $acc_currency = 'USD';
                }

                // if ($acc_currency != $currency) {
                //     return "<span class='float-right'>" . decimalPlace($t, $currency) . "</span><br>
                // 						<span class='float-right'><b>" . decimalPlace($t, $acc_currency) . "</b></span>";
                // } else {
                //     return "<span class='float-right'>" . decimalPlace($t, $currency) . "</span>";
                // }
                return "<span class='float-right'>" . decimalPlace($t, $acc_currency) . "</span>";
                //return $t;
            })
			->editColumn('paid', function ($invoice) use ($currency) {
                //$t = $invoice->paid;
				$t = Transaction::where("type","income")->where("dr_cr","cr")->where("invoice_id",$invoice->id)->sum('base_amount');
				
				/*$results = YourModel::query()
    ->selectRaw('SUM(column1) as total_c1, SUM(column2) as total_c2')
    ->first();

// Accessing the results
$totalC1 = $results->total_c1;
$totalC2 = $results->total_c2;*/
				
                $acc_currency = currency($invoice->client->currency);
                if ($invoice->usd) {
                    $acc_currency = 'USD';
                }
                //return "<span class='float-right'>" . $acc_currency . "1111</span>";
                return "<span class='float-right'>" . decimalPlace($t, $acc_currency) . "</span>";
            })



            ->filterColumn('monto_adeudado', function ($query, $keyword) {})
            // ->editColumn('due_date', function ($invoice) {
            //     $date_format = get_company_option('date_format', 'Y-m-d');
            //     return date($date_format, strtotime($invoice->due_date));
            // })
            ->editColumn('vendedor', function ($invoice) {
                $vend = $invoice->vendedor->name;
                return $vend;
            })

            ->editColumn('porcentajeComision', function ($invoice) {
                $percent = $invoice->comision->porcentaje;
                return $percent;
            })

            ->editColumn('comision', function ($invoice) {
                $comision = $invoice->comision->monto;


                return $comision;
            })
            ->editColumn('grand_total', function ($invoice) use ($currency) {
				$salesReturnstotal = SalesReturn::where("customer_id",$invoice->client_id)->where("invoice_id",$invoice->id)->sum('grand_total');
                $acc_currency = currency($invoice->client->currency);
                // dump($invoice->is_usd);
                if ($invoice->is_usd) {

                    $acc_currency = 'USD';
                }


                // if ($acc_currency != $currency) {
                //     return "<span class='float-right'>" . decimalPlace($invoice->grand_total, $currency) . "</span><br>
                // 						<span class='float-right'><b>" . decimalPlace($invoice->converted_total, $acc_currency) . "</b></span>";
                // } else {
                //     return "<span class='float-right'>" . decimalPlace($invoice->grand_total, $currency) . "</span>";
                // }
                return "<span class='float-right'>" . decimalPlace(($invoice->grand_total-$salesReturnstotal), $acc_currency) . "</span>";
            })
            ->editColumn('status', function ($invoice) {
                return invoice_status($invoice->status);
            })
            ->addColumn('action', function ($invoice) use ($aFacturar) {
				if  ($invoice->status == 'Canceled')
				{
					return $html = 'CANCELADA'; 
				}
                if (!$aFacturar) {
                    $class = 'd-none';
                    if (auth()->user()->role->name == 'Gerencial' || auth()->user()->role->name == null) {
                        $class = '';
                    }

                    $html = '<div class="dropdown text-center">'
                        . '<button class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown">'
                        . _lang('Action') . '&nbsp;<i class="fas fa-angle-down"></i></button>'
                        . '<div class="dropdown-menu">'
                        . '<a class="dropdown-item" href="' . action('InvoiceController@edit', $invoice->id) . '"><i class="fas fa-edit"></i> ' . _lang('Edit') . '</a>'
                        . '<a class="dropdown-item ajax-modal ' . $class . '" href="' . action('InvoiceController@create_comision', $invoice->id) .
                        '"><i class="fas fa-usd"></i> ' . _lang('Comisión') . '</a>'
                        . '<a class="dropdown-item ajax-modal ' . $class . '" href="' . action('InvoiceController@create_observaciones', $invoice->id) .
                        '"><i class="fas fa-usd"></i> ' . _lang('Observaciones') . '</a>'
                        . '<a class="dropdown-item" href="' . action('InvoiceController@show', $invoice->id) . '" data-title="' . _lang('View Invoice') . '" data-fullscreen="true"><i class="fas fa-eye"></i> ' . _lang('View') . '</a>'
                        . '<a href="' . url('invoices/create_payment/' . $invoice->id) . '" data-title="' . _lang('Make Payment') . '" class="dropdown-item ajax-modal"><i class="fas fa-credit-card"></i> ' . _lang('Make Payment') . '</a>'
                        . '<a href="' . url('invoices/view_payment/' . $invoice->id) . '" data-title="' . _lang('View Payment') . '" data-fullscreen="true" class="dropdown-item ajax-modal"><i class="fas fa-credit-card"></i> ' . _lang('View Payment') . '</a>';

                    //if (auth()->user()->role->name == 'Gerencial' || auth()->user()->role->name == 'Cajera') {
                        $html .= '<form action="' . action('InvoiceController@destroy', $invoice['id']) . '" method="post">'
                            . csrf_field()
                            . '<input name="_method" type="hidden" value="DELETE">'
                            . '<button class="button-link btn-remove-invoice" type="submit"><i class="fas fa-recycle"></i> ' . _lang('Anular') . '</button>'
                            . '</form>';
                    //}

                    $html .= '</div></div>';
                    return $html;
                } else {
                    return '<div class="dropdown text-center">'
                        . '<button class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown">'
                        . _lang('Action') . '&nbsp;<i class="fas fa-angle-down"></i></button>'
                        . '<div class="dropdown-menu">'
                        . '<a class="dropdown-item" href="#"><i class="fas fa-money-bill"></i> ' . _lang('Facturar') . '</a>'
                        . '</div></div>';
                }
            })

            ->setRowId(function ($invoice) {
                return "row_" . $invoice->id;
            })
            ->rawColumns(['grand_total', 'status', 'action', 'contact_name', 'producto', 'idproducto', 'checkbox', 'invoice_number', 'monto_adeudado', 'nro_interno','paid'])
            ->make(true);
    }
	
	 public function ConsultaVentas(Request $request)
    {
        return view('backend.accounting.invoice.consulta_ventas');
    }
	
	public function getConsultaVentas(Request $request)
    {
		 $invoices = Invoice::select();
        $currency = currency();
        return Datatables::eloquent($invoices)
		->addColumn('invoice_number', function ($invoice) {
                //dd($comision);
                $in = 'VEN-';
                if ($invoice->company_id == 1) {
                    $in .= 'PM-';
                } else if ($invoice->company_id == 2) {
                    $in .= 'PC-';
                }
                //return '<a href="' . action('InvoiceController@show', $invoice->id) . '">' . $in . $invoice->invoice_number . '</a>';
                return $in . $invoice->invoice_number;
            })
            ->addColumn('nro_interno', function ($invoice) {
                $invoice_item = InvoiceItem::where('invoice_id', $invoice->id)->get();
                $html = '';
                //dd($invoice_item);
                if (!empty($invoice_item)):
                    $ingresado = array();
                    foreach ($invoice_item as $item):
                        if (!in_array($item->product->nro_interno, $ingresado)) {
                            array_push($ingresado, $item->product->nro_interno);
                            $html .= nroInternoAlias($item->product->company_id, $item->product->tipo_vehiculo, $item->product->nro_interno) . '<br>';
                        }
                    endforeach;

                endif;

                return $html;
			})	
			->addColumn('marcamodelo', function ($invoice) {
				$html = '';
                $mostrado = array();
                $invoice_item = InvoiceItem::where('invoice_id', $invoice->id)->get();
                if (!empty($invoice_item)):
                    foreach ($invoice_item as $item):
                        $modelo_actual = "";
                        if (!in_array($item->product->marca_modelo, $mostrado)) {
                            array_push($mostrado, $item->product->marca_modelo);
                            $modelo_actual = ($item->product->marcaModelo->marca->marca ?? '') . ' ' .  ($item->product->marcaModelo->modelo->modelo ?? '');
                        }
						$html .= $modelo_actual . '<br>';
                    endforeach;
                endif;
                return $html;
			})
			->addColumn('producto', function ($invoice) {
                $invoice_item = InvoiceItem::where('invoice_id', $invoice->id)->get();
                $html = '';
                //dd($invoice_item);
                if (!empty($invoice_item)):
                    
                    foreach ($invoice_item as $item):
                        $html .= $item->product->item->item_name . (($html != "") ? ',' : '') . '<br>';
                    endforeach;

                endif;

                return $html;
            })
            ->addColumn('idproducto', function ($invoice) {
                $invoice_item = InvoiceItem::where('invoice_id', $invoice->id)->get();
                $html = '';
                if (!empty($invoice_item)):
                    foreach ($invoice_item as $item):
                        $in = ($item->company_id == 1) ? 'PM-' : 'PC-';
                        $html .= $in . $item->product_id . (($html != "") ? ',' : '') . '<br>';
                    endforeach;
                endif;
                return $html;
            })
			->editColumn('comision', function ($invoice)  use ($currency) {
                $comision = $invoice->comision->monto;
                return decimalPlace($comision);
            })
			->editColumn('comision', function ($invoice)  use ($currency) {
                $comision = $invoice->comision->monto;
                return decimalPlace($comision);
            })
		    ->editColumn('invoice_date', function ($invoice) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return date($date_format, strtotime($invoice->invoice_date));
            })
            ->editColumn('fecha_entrega', function ($invoice) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                if ($invoice->fecha_entrega)
                    return date($date_format, strtotime($invoice->fecha_entrega));
                else
                    return '';
            })
			->editColumn('vendedor', function ($invoice) {
                $vend = $invoice->vendedor->name;
                return $vend;
            })
			->editColumn('nro_oblea', function ($invoice) {
				
				$invoice_item = InvoiceItem::where('invoice_id', $invoice->id)->get();
                $html = '';
                if (!empty($invoice_item)):
                    foreach ($invoice_item as $item):
                        if (isset($item->product->nro_oblea))
						{
							$html .= $item->product->nro_oblea . (($html != "") ? ',' : '') . '<br>';
						}	
                    endforeach;
                endif;
                return $html;
            })
			->filterColumn('comision', function ($query, $keyword) {
				 $query->orwhereHas('comision', function ($str) use ($keyword) {
                    $str->where('monto', 'like', "%{$keyword}%");
                });
			})
			->filterColumn('invoice_number', function ($query, $keyword) {
                $query->where('invoice_number', 'like', "%{$keyword}%");
			})
			->filterColumn('marcamodelo', function ($query, $keyword) {
				$query->orwhereHas('invoice_items', function ($str) use ($keyword) {
					$str->whereHas('product', function ($str) use ($keyword) {
						$str->whereHas('marcaModelo', function ($str) use ($keyword) {
							
							 $str->whereHas('marca', function ($str) use ($keyword) {
                        $str->where('marca', 'like', "%{$keyword}%");
                    });

                    $str->orwhereHas('modelo', function ($str) use ($keyword) {
                        $str->where('modelo', 'like', "%{$keyword}%");
                    });
							
						});   
                    });
                    
                });
            })
			->filterColumn('nro_interno', function ($query, $keyword) {
                $query->orwhereHas('invoice_items', function ($str) use ($keyword) {
                    $str->whereHas('product', function ($str) use ($keyword) {
                        $str->where('products.nro_interno', 'like', "%{$keyword}");
                    });
                });
            })
			->filterColumn('idproducto', function ($query, $keyword) {
                $query->whereHas('invoice_items', function ($q) use ($keyword) {
                    $q->where('product_id', 'like', "%{$keyword}%");
                });
            })
			 ->filterColumn('producto', function ($query, $keyword) {
                $query->orwhereHas('invoice_items', function ($str) use ($keyword) {
                    $str->whereHas('product', function ($str) use ($keyword) {
                        $str->whereHas('item', function ($str) use ($keyword) {
                            $str->where('item_name', 'like', "%{$keyword}%");
                        });
                    });
                });
            })
			
			 ->filterColumn('nro_oblea', function ($query, $keyword) {
                $query->orwhereHas('invoice_items', function ($str) use ($keyword) {
                    $str->whereHas('product', function ($str) use ($keyword) {
                        //$str->whereHas('nro_oblea', function ($str) use ($keyword) {
                            $str->where('nro_oblea', 'like', "%{$keyword}%");
                        //});
                    });
                });
            })
			
			->filterColumn('vendedor', function ($query, $keyword) {
                $query->orwhereHas('vendedor', function ($str) use ($keyword) {
                            $str->where('name', 'like', "%{$keyword}%");
                });
            })
			 ->rawColumns(['grand_total', 'contact_name', 'producto','invoice_number', 'idproducto','monto_adeudado', 'nro_interno','marcamodelo','nro_oblea'])
			->make(true);
    }
	
	
	 public function orden_desarme_2025($q, $desarme = true, $prioridad = 'normal')
    {


        $products = InvoiceItem::where('invoice_id', $q->id)->get();
        foreach ($products as $product):


			/*if ($desarme == true){ // $invoice->desarmar = 0; true
				 $orden_despacho = new OrdenDespacho();
                $company = '';

                if ($product->product->company_id == 1) {
                    $company = 'PM-';
                } else if ($product->product->company_id == 2) {
                    $company = 'PC-';
                }
                $orden_despacho->invoice_id = $q->id;
                $orden_despacho->invoiceitem_id = $product->id;
                $orden_despacho->description = $product->description;
                $orden_despacho->quantity = $product->quantity;
                $orden_despacho->company_id = $product->company_id;
                $orden_despacho->estatus = 'pendiente';
                $orden_despacho->save();
			}else{ // pasa a desarme
			
			   // La pieza q se vende desde “ vehículos” 
					if (!is_null($product->idCar) && $product->idCar > 0) {
					$car = Cars::where('id', $product->idCar)->first();
					if ($car->idEstado != 11) { // si el estado es diferente a no apto no autorizado para a desarme
						$orden_desarme = new Orden_desarme();
						$company = '';

						if ($product->product->company_id == 1) {
							$company = 'PM-';
						} else if ($product->product->company_id == 2) {
							$company = 'PC-';
						}

						$orden_desarme->id_venta = $q->id;
						$orden_desarme->fecha_venta = $q->invoice_date;

						//            dd($product);
						$orden_desarme->idCar = $product->idCar ?? null;
						$orden_desarme->prioridad = $prioridad;

						$orden_desarme->interno = $company . ($product->idCar ?? $product->product->nro_interno);

						$prodMarca = Product::where('id', $product->product_id)->first();

						$orden_desarme->marca_modelo = $prodMarca->marca_modelo;
						$orden_desarme->pieza = $product->product_id;

						// Aqui colocae orden procesada y asignarla al operario segun la compañia
						$orden_desarme->procesar = 1;

						$operario = User::wherehas('role', function ($q) {
							$q->where('name', 'Operario');
						})->where('company_id', $product->product->company_id)->first();

						$orden_desarme->idCadete_operario =  $operario->id;

						$orden_desarme->save();

						// enviar notificacion al operario de creada una orden
						Notification::send($operario, new OrdenCreated($orden_desarme));
                }
            } else {
                // es una pieza desarmada en stock pasa a despacho
                //Si la cotización se hace desde “piezas” es q esta desarmada. Pasa directo a embalaje y despacho

                $orden_despacho = new OrdenDespacho();
                $company = '';

                if ($product->product->company_id == 1) {
                    $company = 'PM-';
                } else if ($product->product->company_id == 2) {
                    $company = 'PC-';
                }

                $orden_despacho->invoice_id = $q->id;
                $orden_despacho->invoiceitem_id = $product->id;
                $orden_despacho->description = $product->description;
                $orden_despacho->quantity = $product->quantity;
                $orden_despacho->company_id = $product->company_id;
                $orden_despacho->estatus = 'pendiente';
                $orden_despacho->save();
            }
			
				
				
				
				
			}*/
        endforeach;
    }
	
	 public function devolucion_mercacia()
    {	
	/*	$validator = Validator::make($request->all(), [
			'return_date' => 'required',
			'customer_id' => 'required',
			'sub_total.*' => 'required|numeric',
			'attachemnt' => 'nullable|mimes:jpeg,png,jpg,doc,pdf,docx,zip',
			'product_id'     => 'required',
        ], [
            'product_id.required' => _lang('You must select at least one product or service'),
        ]);
		
		if ($validator->fails()) {
			if($request->ajax()){ 
			    return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
			}else{
				return redirect('sales_returns/create')
							->withErrors($validator)
							->withInput();
			}			
		}
		
		DB::beginTransaction();
		
		$company_id = company_id();
			
		$attachemnt = "";
	    if($request->hasfile('attachemnt'))
		{
			$file = $request->file('attachemnt');
			$attachemnt = time().$file->getClientOriginalName();
			$file->move(public_path()."/uploads/attachments/", $attachemnt);
		}
		

        $salesReturn = new SalesReturn();
	    $salesReturn->return_date = $request->input('return_date');
		$salesReturn->customer_id = $request->input('customer_id');
		$salesReturn->tax_amount = $request->tax_total;
		$salesReturn->product_total = $request->input('product_total');
		$salesReturn->grand_total = ($salesReturn->product_total + $salesReturn->tax_amount);
		$salesReturn->converted_total = convert_currency(base_currency(), $salesReturn->customer->currency, $salesReturn->grand_total);
		$salesReturn->attachemnt = $attachemnt;
		$salesReturn->note = $request->input('note');
		$salesReturn->company_id = $company_id;
	
		$salesReturn->save();
		
		$taxes = Tax::where('company_id',$company_id)->get();

		//Save Sales Return item
		for($i = 0; $i < count($request->product_id); $i++ ){
			$salesReturnItem = new SalesReturnItem();
			$salesReturnItem->sales_return_id = $salesReturn->id;
			$salesReturnItem->product_id = $request->product_id[$i];
			$salesReturnItem->description = $request->product_description[$i];
			$salesReturnItem->quantity = $request->quantity[$i];
			$salesReturnItem->unit_cost = $request->unit_cost[$i];
			$salesReturnItem->discount = $request->discount[$i];
			//$salesReturnItem->tax_method = $request->tax_method[$i];
			//$salesReturnItem->tax_id = $request->tax_id[$i];
			$salesReturnItem->tax_amount = $request->product_tax[$i];
			$salesReturnItem->sub_total = $request->sub_total[$i];
			$salesReturnItem->company_id = $company_id;
			$salesReturnItem->save();
			
			//Store Sales Return Taxes
			if(isset($request->tax[$salesReturnItem->product_id])){
				foreach($request->tax[$salesReturnItem->product_id] as $taxId){
					$tax = $taxes->firstWhere('id', $taxId);
					
					$salesReturnItemTax = new SalesReturnItemTax();
					$salesReturnItemTax->sales_return_id = $salesReturnItem->sales_return_id;
					$salesReturnItemTax->sales_return_item_id = $salesReturnItem->id;
					$salesReturnItemTax->tax_id = $tax->id;
					$tax_type = $tax->type == 'percent' ? '%' : '';
					$salesReturnItemTax->name = $tax->tax_name.' @ '.$tax->rate.$tax_type;
					$salesReturnItemTax->amount = $tax->type == 'percent' ? ($salesReturnItem->sub_total / 100) * $tax->rate : $tax->rate;
					$salesReturnItemTax->company_id = $company_id;
					$salesReturnItemTax->save();
				}
			}

			//Update Stock
			$stock = Stock::where("product_id", $salesReturnItem->product_id)
						  ->where("company_id",$company_id)->first();
			$stock->quantity = $stock->quantity + $salesReturnItem->quantity;
			$stock->company_id = $company_id;
			$stock->save();
		}
		
		DB::commit();

        
		if(! $request->ajax()){
           return redirect('sales_returns/'.$salesReturn->id)->with('success', _lang('Sales Returned Sucessfully'));
        }else{
		   return response()->json(['result'=>'success','action'=>'store','message'=>_lang('Sales Returned Sucessfully'),'data'=>$purchase]);
		}
        */
   }
   
    public function nota_debito($id, $request)
    {
   		$observacion = $request->get('note') ?? '';
		$estatus="Item inventario";
	
		DB::beginTransaction();
		try {
        if (!empty($id)) {
			$invoice = Invoice::where("id", $id)->first();	  
			
			if  ($invoice->status == 'Canceled'){
				return redirect()->back()->with('error', 'Ya se encuentra anulada'); 
			}
			
		if ($invoice) {
				//dd("item inventario");
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
						
						//$ids =  explode(",", $id);
						//$invoiceItems = InvoiceItem::whereIn('id',$ids)->get();
					    $invoiceItems = InvoiceItem::where("invoice_id", $id)->get();	
						
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
									// stock a revision
									Product::where('id', $p_item->product_id)->update(['stock' => 0]);
									$estatus_item='pendiente';
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
						
						//if ($anular_cotizacion=='si') {
								$invoice->status = 'Canceled';	
								$invoice->note = "Anulados todos los item";
								$invoice->save();
						//}
				}
			}// final
			DB::commit();

			// Recalcular despues de commit para que el FIFO vea el saldo correcto
			if (isset($invoice) && $invoice) {
			    \App\CuentaCorriente::recalcular($invoice->client_id);
			}

			// FIFO automático: reimputar saldo a favor a facturas impagas (excluir la factura anulada)
			try {
			    if (isset($invoice) && $invoice) {
			        \App\CuentaCorriente::reimputarSaldoFavorFIFO($invoice->client_id, "reimputacion FIFO de coti {$invoice->invoice_number}", $invoice->id);
			    }
			} catch (\Throwable $e) {
			    \Log::error('Error en FIFO reimputation: ' . $e->getMessage());
			}

			return redirect('invoices')->with('success', _lang('Invoice deleted sucessfully'));
		} catch (Throwable $e) {
            DB::rollBack();
			dd($e->getMessage());
        }
		
	}

public function get_table_data(Request $request)
    {

        $currency = currency();
        //$company_id = company_id();
		$company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();

        //pendienteFacturar
        $aFacturar = $request->get('facturar', false);




        $projects = DB::table('projects')
            ->select('id', 'name as contact_name', DB::raw('"projects" as type'))
			->whereIn('company_id', $company_id);
        //  ->where('company_id', $company_id);
			

        $all_contacts = DB::table('contacts')
            ->select('id', 'contact_name', DB::raw('"contacts" as type'))
			->whereIn('company_id', $company_id)
            //->where('company_id', $company_id)
            ->union($projects);
			
 
        $invoices = Invoice::joinSub($all_contacts, 'all_contacts', function ($join) {
            $join->on('invoices.related_id', '=', 'all_contacts.id')
                ->on('invoices.related_to', '=', 'all_contacts.type');
        })
            ->select("invoices.*", "all_contacts.contact_name", "all_contacts.id as contact_id")
			->whereIn('company_id', $company_id)
            // ->where('invoices.company_id', $company_id)
            ->orderBy('invoices.id', 'desc');
        if (strTolower(auth()->user()->role->name) == 'vendedor') {
            $invoices->where('invoices.user_id', auth()->id());
        }
        if ($aFacturar) {
            $invoices->where('invoices.facturar', 1)->where('invoices.facturado', null);
        }

        $invoices->when($request, function ($query) use ($request) {
            if ($request->has('invoice_number')) {
                $query->where('invoice_number', 'like', "%{$request->get('invoice_number')}%");
            }

            if ($request->has('vendedor')) {
                $query->where('user_id', $request->get('vendedor'));
            }

            if ($request->has('revendedor')) {
                $query->where('revendedor', $request->get('revendedor'));
            }

            if ($request->has('company_id')) {
                $query->where('company_id', $request->get('company_id'));
            }

            if ($request->has('status')) {
                $query->whereIn('status', json_decode($request->get('status')));
            }

            if ($request->has('date_range')) {
                $date_range = explode(" - ", $request->get('date_range'));
                //dd($date_range );
                //$query->whereBetween('invoice_date', [$date_range[0], $date_range[1]]);
                $query->whereRaw("DATE(invoice_date) BETWEEN STR_TO_DATE(?, '%d-%m-%Y') AND STR_TO_DATE(?, '%d-%m-%Y')", [$date_range[0], $date_range[1]]);
            }
        });



        return Datatables::eloquent($invoices)
            ->addColumn('checkbox', function ($invoice) {
                $d = 'disabled';
                if (!isset($invoice->comision->isPaid)) {
                    $d = '';
                }
                if (auth()->user()->role->name == 'Gerencial' || auth()->user()->role->name == null) {
                    return "<input $d class='form-check' name='paidComi[]' type='checkbox' value='" . $invoice->id . "' >";
                }
                return "x";
            })
            ->addColumn('invoice_number', function ($invoice) {
                //dd($comision);
                $in = 'VEN-';
                if ($invoice->company_id == 1) {
                    $in .= 'PM-';
                } else if ($invoice->company_id == 2) {
                    $in .= 'PC-';
                }
                return '<a href="' . action('InvoiceController@show', $invoice->id) . '">' . $in . $invoice->invoice_number . '</a>';
            })
            ->addColumn('contact_name', function ($invoice) {
                if ($invoice->related_to == 'contacts') {
                    return '<a href="' . action('ContactController@show', $invoice->related_id) . '">' . $invoice->contact_name . ' <span class="text-muted small">(' . _lang('Customer') . ')</span></a>';
                }
                return '<a href="' . action('ProjectController@show', $invoice->related_id) . '">' . $invoice->contact_name . ' <span class="text-muted small">(' . _lang('Project') . ')</span></a>';
            })
         
			 ->addColumn('pieza', function ($invoice) {
				 
			$invoice_items = InvoiceItem::with(['product.item']) 
			->where('invoice_id', $invoice->id)
			->get()
			->filter()
			->sortBy('product.nro_interno');


if ($invoice_items->count() === 1) {
    $item = $invoice_items->first();
    $producto_id = $item->product->id ?? '';
    $nro_interno = $item->product->nro_interno ?? null;
    $company_id = $item->product->company_id ?? null;
    $tipo_vehiculo = $item->product->tipo_vehiculo ?? null;
    $item_nombre = $item->product->item->item_name ?? '';
    
    $alias_producto = nroInternoAlias($company_id, $tipo_vehiculo, $nro_interno);
    $alias_mostrar = $alias_producto ? $alias_producto : 'Sin Nro Interno';

    return "({$producto_id}) {$alias_mostrar} - {$item_nombre}";
}

$tabla_html = '';

if ($invoice_items->isNotEmpty()):
    $tabla_html .= '<table class="table table-striped">';
    $tabla_html .= '<thead><tr><th>ID</th><th>Nro Interno</th><th>Ítem</th></tr></thead>';
    $tabla_html .= '<tbody>';

    foreach ($invoice_items as $item):
        $producto_id = $item->product->id ?? '';
        $nro_interno = $item->product->nro_interno ?? null;
        $company_id = $item->product->company_id ?? null;
        $tipo_vehiculo = $item->product->tipo_vehiculo ?? null;
        $item_nombre = $item->product->item->item_name ?? '';
        
        $alias_producto = nroInternoAlias($company_id, $tipo_vehiculo, $nro_interno);
        $alias_mostrar = $alias_producto ? $alias_producto : 'Sin Nro Interno';
            
        $tabla_html .= '<tr>';
        $tabla_html .= '<td>'.$producto_id.'</td>';
        $tabla_html .= '<td>'.$alias_mostrar.'</td>';
        $tabla_html .= '<td>'.$item_nombre.'</td>';
        $tabla_html .= '</tr>';
    endforeach;

    $tabla_html .= '</tbody></table>';
endif;

$boton_modal = '<a class="view-details" href="javascript:void(0)" 
    data-title="Detalles de Factura : '. ($invoice->invoice_number ?? '') .'"
    data-body=\''.$tabla_html.'\' 
    data-toggle="modal" 
    data-target="#detailsModal">
    <i class="fa fa-list-alt text-primary" aria-hidden="true"></i> Detalle
</a>';

if (!isset($request->exportar)) {
    return $boton_modal;	
}

return $tabla_html;


				
				/*$invoice_item = InvoiceItem::where('invoice_id', $invoice->id)->get();
                $html = '';
                if (!empty($invoice_item)):

                    $ingresado = array();
                    foreach ($invoice_item as $item):
									$producto_completo=$item->product->item->item_name ?? '';
									$producto_id=$item->product->id ?? '';
									$html .= "($producto_id) $producto_completo";
                    endforeach;

                endif;

                return $html;*/
            })
			 ->addColumn('nro_interno', function ($invoice)  {
			
			$invoice_items = InvoiceItem::with(['product'])
    ->where('invoice_id', $invoice->id)
    ->get()
    ->groupBy('product.nro_interno')
    ->sortKeys()
    ->map(function ($grupo) {
        return $grupo->first();
    })
    ->filter();

if ($invoice_items->count() === 1) {
    $item = $invoice_items->first();
    $nro_interno = $item->product->nro_interno ?? null;
    $company_id = $item->product->company_id ?? null;
    $tipo_vehiculo = $item->product->tipo_vehiculo ?? null;
    
    return nroInternoAlias($company_id, $tipo_vehiculo, $nro_interno);
}

$tabla_html = '';

if ($invoice_items->isNotEmpty()):
    $tabla_html .= '<table class="table table-striped">';
    $tabla_html .= '<thead><tr><th>Nro Interno</th></tr></thead>';
    $tabla_html .= '<tbody>';

    foreach ($invoice_items as $item):
        $nro_interno = $item->product->nro_interno ?? null;
        $company_id = $item->product->company_id ?? null;
        $tipo_vehiculo = $item->product->tipo_vehiculo ?? null;
        $alias_producto = nroInternoAlias($company_id, $tipo_vehiculo, $nro_interno);
            
        $tabla_html .= '<tr>';
        $tabla_html .= '<td>'.($alias_producto ? $alias_producto : 'Sin Nro Interno').'</td>';
        $tabla_html .= '</tr>';
    endforeach;

    $tabla_html .= '</tbody></table>';
endif;

$boton_modal = '<a class="view-details" href="javascript:void(0)" 
    data-title="Detalles de Factura : '. ($invoice->invoice_number ?? '') .'"
    data-body=\''.$tabla_html.'\' 
    data-toggle="modal" 
    data-target="#detailsModal">
    <i class="fa fa-list-alt text-primary" aria-hidden="true"></i> Detalle
</a>';

if (!isset($request->exportar)) {
    return $boton_modal;	
}

return $tabla_html;

				 
              /* $invoice_item = InvoiceItem::where('invoice_id', $invoice->id)->get();

                //dd($invoice_item);
                if (!empty($invoice_item)):
                    $html = '';
                    $ingresado = array();
                    foreach ($invoice_item as $item):
                        if (!in_array($item->product->nro_interno, $ingresado)) {
                            array_push($ingresado, $item->product->nro_interno);
                            $html .= nroInternoAlias($item->product->company_id, $item->product->tipo_vehiculo, $item->product->nro_interno) . '<br>';
                        }
                    endforeach;

                endif;

                return $html;*/
            })
			            ->addColumn('action', function ($invoice) use ($aFacturar) {
				if  ($invoice->status == 'Canceled')
				{
					return $html = 'Anulada'; 
				}
                if (!$aFacturar) {
                    $class = 'd-none';
                    if (auth()->user()->role->name == 'Gerencial' || auth()->user()->role->name == null) {
                        $class = '';
                    }

                    $html = '<div class="dropdown text-center">'
                        . '<button class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown">'
                        . _lang('Action') . '&nbsp;<i class="fas fa-angle-down"></i></button>'
                        . '<div class="dropdown-menu">'
                        . '<a class="dropdown-item" href="' . action('InvoiceController@edit', $invoice->id) . '"><i class="fas fa-edit"></i> ' . _lang('Edit') . '</a>'
                        . '<a class="dropdown-item ajax-modal ' . $class . '" href="' . action('InvoiceController@create_comision', $invoice->id) .
                        '"><i class="fas fa-usd"></i> ' . _lang('Comisión') . '</a>'
                        . '<a class="dropdown-item ajax-modal ' . $class . '" href="' . action('InvoiceController@create_observaciones', $invoice->id) .
                        '"><i class="fas fa-usd"></i> ' . _lang('Observaciones') . '</a>'
                        . '<a class="dropdown-item" href="' . action('InvoiceController@show', $invoice->id) . '" data-title="' . _lang('View Invoice') . '" data-fullscreen="true"><i class="fas fa-eye"></i> ' . _lang('View') . '</a>'
                        . '<a href="' . url('invoices/create_payment/' . $invoice->id) . '" data-title="' . _lang('Make Payment') . '" class="dropdown-item ajax-modal"><i class="fas fa-credit-card"></i> ' . _lang('Make Payment') . '</a>'
                        . '<a href="' . route('auditoriaInvHistorial', $invoice->id) . '" data-title="' . _lang('Historial de Invoices') . '" data-fullscreen="true" class="dropdown-item ajax-modal"><i class="ti-list"></i> ' . _lang('Historial') . '</a>'
                        . '<a href="' . url('invoices/view_payment/' . $invoice->id) . '" data-title="' . _lang('View Payment') . '" data-fullscreen="true" class="dropdown-item ajax-modal"><i class="fas fa-credit-card"></i> ' . _lang('View Payment') . '</a>';


                       // $result .= '<a href="' .  route('auditoriaInvHistorial', $data->id) . '" 
					//data-title="' . _lang('Historial de Invoices') . '" data-fullscreen="true" class="btn btn-warning btn-xs ajax-modal"><i class="ti-list"></i></a>&nbsp;';
                    //if (auth()->user()->role->name == 'Gerencial' || auth()->user()->role->name == 'Cajera') {
                        $html .= '<form action="' . action('InvoiceController@destroy', $invoice['id']) . '" method="post">'
                            . csrf_field()
                            . '<input name="_method" type="hidden" value="DELETE">'
                            . '<button class="button-link btn-remove-invoice" type="submit"><i class="fas fa-recycle"></i> ' . _lang('Anular') . '</button>'
                            . '</form>';
                    //}

                    $html .= '</div></div>';
                    return $html;
                } else {
                    return '<div class="dropdown text-center">'
                        . '<button class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown">'
                        . _lang('Action') . '&nbsp;<i class="fas fa-angle-down"></i></button>'
                        . '<div class="dropdown-menu">'
                        . '<a class="dropdown-item" href="#"><i class="fas fa-money-bill"></i> ' . _lang('Facturar') . '</a>'
                        . '</div></div>';
                }
            })
			->editColumn('invoice_date', function ($invoice) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return date($date_format, strtotime($invoice->invoice_date));
            })
            ->editColumn('fecha_entrega', function ($invoice) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                if ($invoice->fecha_entrega)
                    return date($date_format, strtotime($invoice->fecha_entrega));
                else
                    return '';
            })


            //ultima fecha de pago
            ->editColumn('fecha_pago', function ($invoice) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                $transactions = Transaction::where("invoice_id", $invoice->id)->orderBy('id', 'desc')->first();
                if (isset($transactions)) {
                    return date($date_format, strtotime($transactions->trans_date));
                }
                return '';
            })
            ->editColumn('monto_adeudado', function ($invoice) use ($currency) {
				$salesReturnstotal = SalesReturn::where("customer_id",$invoice->client_id)->where("invoice_id",$invoice->id)->sum('grand_total');
				$invoicepaidtotal = Transaction::where("type","income")->where("dr_cr","cr")->where("invoice_id",$invoice->id)->sum('base_amount');
                $t = (($invoice->grand_total-$salesReturnstotal) - $invoicepaidtotal);
                $acc_currency = currency($invoice->client->currency);
                if ($invoice->usd) {
                    $acc_currency = 'USD';
                }

                // if ($acc_currency != $currency) {
                //     return "<span class='float-right'>" . decimalPlace($t, $currency) . "</span><br>
                // 						<span class='float-right'><b>" . decimalPlace($t, $acc_currency) . "</b></span>";
                // } else {
                //     return "<span class='float-right'>" . decimalPlace($t, $currency) . "</span>";
                // }
                return "<span class='float-right'>" . decimalPlace($t, $acc_currency) . "</span>";
                //return $t;
            })
			->editColumn('paid', function ($invoice) use ($currency) {
				$t = Transaction::where("type","income")->where("dr_cr","cr")->where("invoice_id",$invoice->id)->sum('base_amount');
                $acc_currency = currency($invoice->client->currency);
                if ($invoice->usd) {
                    $acc_currency = 'USD';
                }
                //return "<span class='float-right'>" . $acc_currency . "1111</span>";
                return "<span class='float-right'>" . decimalPlace($t, $acc_currency) . "</span>";
            })
			 ->editColumn('status', function ($invoice) {
                return invoice_status($invoice->status);
            })
           /* ->filterColumn('idproducto', function ($query, $keyword) {
                $query->whereHas('invoice_items', function ($q) use ($keyword) {
                    $q->where('product_id', 'like', "%{$keyword}%");
                });
            })*/
           ->filterColumn('nro_interno', function ($query, $keyword) {
                $query->orwhereHas('invoice_items', function ($str) use ($keyword) {
                    $str->whereHas('product', function ($str) use ($keyword) {
                        $str->where('products.nro_interno', 'like', "%{$keyword}");
                        /*$str->whereHas('item', function ($str) use ($keyword) {
                            $str->where('items.product.nro_interno', 'like', "%{$keyword}%");
                        });*/
                    });
                });
            })
           /* ->filterColumn('producto', function ($query, $keyword) {
                $query->orwhereHas('invoice_items', function ($str) use ($keyword) {
                    $str->whereHas('product', function ($str) use ($keyword) {
                        $str->whereHas('item', function ($str) use ($keyword) {
                            $str->where('item_name', 'like', "%{$keyword}%");
                        });
                    });
                });
            })*/

            ->filterColumn('vendedor', function ($query, $keyword) {
                $query->orwhereHas('vendedor', function ($str) use ($keyword) {
                    $str->where('name', 'like', "%{$keyword}%");
                });
            })

            ->filterColumn('fecha_pago', function ($query, $keyword) {
                $query->orwhereHas('transaction', function ($str) use ($keyword) {
                    $str->where('trans_date', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('invoice_number', function ($query, $keyword) {


                $sql = "CONCAT(users.first_name,'-',users.last_name)  like ?";
                // $query->orwhereHas('tramitador', function ($str) use ($keyword) {
                //     $str->where('name', 'like', "%{$keyword}%");
                // });

                $query->where('invoice_number', 'like', "%{$keyword}%");
            })
            ->filterColumn('contact_name', function ($query, $keyword) {
                $sql = "all_contacts.contact_name  like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->filterColumn('monto_adeudado', function ($query, $keyword) {})
            ->editColumn('vendedor', function ($invoice) {
                $vend = $invoice->vendedor->name;
                return $vend;
            })

            ->editColumn('porcentajeComision', function ($invoice) {
                $percent = $invoice->comision->porcentaje;
                return $percent;
            })

            ->editColumn('comision', function ($invoice) {
                $comision = $invoice->comision->monto;
                return $comision;
            })
            ->editColumn('grand_total', function ($invoice) use ($currency) {
				$salesReturnstotal = SalesReturn::where("customer_id",$invoice->client_id)->where("invoice_id",$invoice->id)->sum('grand_total');
                $acc_currency = currency($invoice->client->currency);
                // dump($invoice->is_usd);
                if ($invoice->is_usd) {

                    $acc_currency = 'USD';
                }
                return "<span class='float-right'>" . decimalPlace(($invoice->grand_total-$salesReturnstotal), $acc_currency) . "</span>";
            })
            ->setRowId(function ($invoice) {
                return "row_" . $invoice->id;
            })
			 ->filterColumn('pieza', function ($query, $keyword) {
                  $query->whereHas('invoice_items', function ($q) use ($keyword) {
					   $q->whereHas('product', function ($q) use ($keyword) {
                                $q->whereRaw('products.id LIKE ?', ['%' . strtolower($keyword) . '%']);
                       });
                    
						$q->orwhereHas('item', function ($q) use ($keyword) {
							$q->whereRaw('LOWER(item_name) LIKE ?', ['%' . strtolower($keyword) . '%']);
						});
					
					
                });
				
				
				  /*if (!empty($invoice_item)):

                    $ingresado = array();
                    foreach ($invoice_item as $item):
									$producto_completo=$item->product->item->item_name ?? '';
									$producto_id=$item->product->id ?? '';
									$html .= "($producto_id) $producto_completo";
                    endforeach;*/
				
				
            })
            ->rawColumns(['grand_total', 'status', 'action', 'contact_name', 'producto', 'idproducto', 'checkbox', 'invoice_number', 'monto_adeudado', 'nro_interno','paid','pieza'])
            ->make(true);
    }	


    public function auditoriaInvHistorial(Request $request, $id)
{

    return view('backend.accounting.invoice.modal.historial', compact('id')); 
}


public function auditoriaInvoice(Request $request)
{
    $id = $request->id;
    
    if (request()->ajax()) {
        
        $invoiceAudits = Audit::where('auditable_type', \App\Invoice::class) 
            ->where('auditable_id', $id)
            ->select(['id', 'user_id', 'event', 'auditable_type', 'auditable_id', 'old_values', 'new_values', 'url', 'ip_address', 'user_agent', 'tags', 'created_at']);

        $detailAudits = Audit::where('auditable_type', \App\InvoiceItem::class)
            ->whereIn('auditable_id', function ($query) use ($id) {
                $query->select('id')
                      ->from('invoice_items') 
                      ->where('invoice_id', $id);
            })
            ->select(['id', 'user_id', 'event', 'auditable_type', 'auditable_id', 'old_values', 'new_values', 'url', 'ip_address', 'user_agent', 'tags', 'created_at']);


        $allAudits = $invoiceAudits->union($detailAudits)->get();

         $users = \App\User::whereIn('id', $allAudits->pluck('user_id'))->get()->keyBy('id');
   
    $allAudits->each(function($audit) use ($users) {
        $audit->setRelation('user', $users->get($audit->user_id));
    });

    $allAudits->load(['auditable' => function ($morphTo) {
    $morphTo->morphWith([
        InvoiceItem::class => ['item'] 
    ]);
    }]);

        //$allAudits->load(['auditable', 'user']);

        return DataTables::of($allAudits)
            ->addIndexColumn()
            
            ->addColumn('model', function ($data) {
                return class_basename($data->auditable_type) === 'Invoice' 
                    ? 'Factura' 
                    : 'Ítem de Factura';
            })

            ->addColumn('event', function ($data) {
                switch ($data->event) {
                    case 'created': return '<span class="badge badge-success">Creado</span>';
                    case 'updated': return '<span class="badge badge-warning">Actualizado</span>';
                    case 'deleted': return '<span class="badge badge-danger">Eliminado</span>';
                    default: return '<span class="badge badge-secondary">' . ucfirst($data->event) . '</span>';
                }
            })

            ->addColumn('usuario', function ($data) {
                return $data->user->name ?? '';
            })

            ->addColumn('created_at', function ($data) {
                return $data->created_at ? $data->created_at->format('d/m/Y H:i:s') : '';
            })

            ->addColumn('valores_ant', function ($data) {
                if (empty($data->old_values)) return '-';
                $html = '<table class="table table-sm table-borderless mb-0" style="font-size:11px;">';
                foreach ($data->old_values as $attribute => $value) {
                    $html .= '<tr><td style="padding:2px; width:40%;"><b>' . e($attribute) . '</b></td><td style="padding:2px;">' . (is_array($value) ? json_encode($value) : e($value)) . '</td></tr>';
                }
                $html .= '</table>';
                return $html;
            })

            ->addColumn('historial_items', function ($data) {
                $accion = '';
                $badgeClass = '';

                if (empty($data->old_values) && !empty($data->new_values)) {
                    $accion = 'Añadió'; $badgeClass = 'success';
                } elseif (!empty($data->old_values) && empty($data->new_values)) {
                    $accion = 'Eliminó'; $badgeClass = 'danger';
                } else {
                    $accion = 'Modificó'; $badgeClass = 'warning';
                }

                $htmlBadge = '<span class="badge badge-' . $badgeClass . '">' . $accion . '</span>';

                if ($data->auditable_type == \App\InvoiceItem::class) {
                    $itemModel = $data->auditable; 
                    $itemName = $itemModel && $itemModel->item ? $itemModel->item->item_name : null;
                    if ($itemName) {
                        $tituloEntidad = '<span class="text-dark font-weight-bold">' . e($itemName) . '</span>';
                    } else {
                        $tituloEntidad = '<span class="text-dark font-weight-bold">Ítem ID ' . $data->auditable_id . '</span>';
                    }
                } else {
                    $tituloEntidad = '<span class="text-muted font-weight-bold">Datos de la Cotización</span>';
                }

                $valoresAmostrar = empty($data->new_values) ? $data->old_values : $data->new_values;
                $htmlDatos = '<table class="table table-sm table-borderless mb-0" style="font-size:11px;">';
                
                if ($valoresAmostrar) {
                    foreach ($valoresAmostrar as $attribute => $value) {
                        if (in_array($attribute, ['created_at', 'updated_at', 'invoice_id', 'company_id'])) {
                            continue;
                        }
                        $print_value = is_array($value) ? json_encode($value) : e($value);
                        $htmlDatos .= '<tr><td style="padding:1px;"><b>' . e($attribute) . '</b></td><td style="padding:1px;">' . $print_value . '</td></tr>';
                    }
                }
                $htmlDatos .= '</table>';

                return $htmlBadge . ' ' . $tituloEntidad . '<br>' . $htmlDatos;
            })

            ->rawColumns(['event', 'valores_ant', 'historial_items'])
            ->make(true);
    }
}
}