<?php

namespace App\Http\Controllers;

use App\Role;
use App\Cars;
use App\Item;
use App\Orden_desarme;
use App\Puesto;
use App\Product;
use App\User;
use Illuminate\Http\Request;
use App\Quotation;
use App\QuotationItem;
use App\QuotationItemTax;
use App\CompanySetting;
use App\OrdenDespacho;
use App\Invoice;
use App\InvoiceItem;
use App\InvoiceItemTax;
use App\Stock;
use App\Contact;
use App\Tax;
use App\Notifications\InvoiceCreated;
use App\Notifications\InvoiceUpdated;
use App\Notifications\OrdenCreated;
use App\Notifications\InvoiceUbicationChange;
use App\Notifications\InvoiceProductMercadoLibre;
use Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Mail\GeneralMail;
use App\Notifications\InvoiceProductOctubre;
use App\PaymentMethod;
use App\Transaction;
use App\Utilities\Overrider;
use Carbon\Carbon;
use DataTables;
use DB;
use Illuminate\Support\Facades\Notification;
use PDF;
use OwenIt\Auditing\Models\Audit;


class QuotationController extends Controller
{

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
                if (!has_feature('quotation_limit')) {
                    return redirect('membership/extend')->with('message', _lang('Your Current package not support this feature. You can upgrade your package !'));
                }

                // If request is create/store
                $route_name = \Request::route()->getName();
                if ($route_name == 'quotations.store') {
                    if (!has_feature_limit('quotation_limit')) {
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
        return view('backend.accounting.quotation.list');
    }

    public function get_table_data()
    {

        $currency = currency();
        //$company_id = company_id();
		$company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();

        $leads = DB::table('leads')
         ->select('id', 'name as contact_name', DB::raw('"leads" as type'))
         ->whereIn('company_id', $company_id);

        $all_contacts = DB::table('contacts')
            ->select('id', 'contact_name', DB::raw('"contacts" as type'))
            ->whereIn('company_id', $company_id)
            ->union($leads);

        $quotations = Quotation::joinSub($all_contacts, 'all_contacts', function ($join) {
            $join->on('quotations.related_id', '=', 'all_contacts.id')
                ->on('quotations.related_to', '=', 'all_contacts.type');
        })
            ->select("quotations.*", "all_contacts.contact_name", "all_contacts.id as contact_id")
            ->whereIn('quotations.company_id',$company_id)
            ->orderBy('quotations.id', 'desc');
        if (strtolower(auth()->user()->role->name) == 'vendedor') {
            $quotations->where('user_id', auth()->id());
        }



        return Datatables::eloquent($quotations)
            ->addColumn('contact_name', function ($quotation) {
                // dd($quotation);
                if ($quotation->related_to == 'contacts') {
                    return '<a href="' . action('ContactController@show', $quotation->related_id) . '">' . $quotation->contact_name . ' <span class="text-muted small">(' . _lang('Customer') . ')</span></a>';
                }
                return '<a href="' . action('LeadController@show', $quotation->related_id) . '" data-title="' . _lang('View Lead Details') . '" class="ajax-modal">' . $quotation->contact_name . ' <span class="text-muted small">(' . _lang('Lead') . ')</span></a>';
            })
            ->filterColumn('contact_name', function ($query, $keyword) {
                $sql = "all_contacts.contact_name  like ?";

                $query->whereRaw($sql, ["%{$keyword}%"]);

            })
            ->filterColumn('idCar', function ($query, $keyword) {
                $sql = "car_id  like ?";

                $query->whereRaw($sql, ["%{$keyword}%"]);

            })
			 ->filterColumn('modelo', function ($query, $keyword) {
				 $query->orwhereHas('vehiculo.marca_modelo', function ($str) use ($keyword) {
                    $str->whereHas('marca', function ($str) use ($keyword) {
                        $str->where('marca', 'like', "%{$keyword}%");
                    });
                    $str->orwhereHas('modelo', function ($str) use ($keyword) {
                        $str->where('modelo', 'like', "%{$keyword}%");
                    });
                });
            })
            ->editColumn('quotation_number', function ($quotation) {
                return '<a href="' . action('QuotationController@show', $quotation->id) . '">' . $quotation->quotation_number . '</a>';
            })
            ->editColumn('quotation_date', function ($quotation) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return date($date_format, strtotime($quotation->quotation_date));
            })
            ->editColumn('grand_total', function ($quotation) use ($currency) {
                if ($quotation->related_to == 'contacts') {
                    $acc_currency = currency($quotation->client->currency);
                } else {
                    $acc_currency = currency($quotation->lead->currency);
                }
                if ($acc_currency != $currency) {
                    return "<span class='float-right'>" . decimalPlace($quotation->grand_total, $currency) . "</span><br>
										<span class='float-right'><b>" . decimalPlace($quotation->converted_total, $currency) . "</b></span>";
                } else {
                    return "<span class='float-right'>" . decimalPlace($quotation->grand_total, $currency) . "</span>";
                }
            })->editColumn('status', function ($quotation) {
                $status = $quotation->status == '0' ? null : $quotation->status;
                //    if(!empty($quotation->status)){
                //        $status = $this->status[$quotation->status];
                //    }
                return $status;
            })
            ->editColumn('modelo', function ($quotation) {
				  return ($quotation->vehiculo->marca_modelo->marca->marca ?? '') ." ". ($quotation->vehiculo->marca_modelo->modelo->modelo ?? '');
            })
            ->addColumn('action', function ($quotation) {
			//	return $quotation->status;
                // dd($quotation->id);
				if (in_array($quotation->status, ['Anulada','Convertida'])) {
					
					 return '<div class="dropdown">'
                        . '<button class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown">' . _lang('Action')
                        . '&nbsp;<i class="fas fa-angle-down"></i></button>'
                        . '<div class="dropdown-menu">'
                        . '<a class="dropdown-item" href="' . action('QuotationController@show', $quotation->id) . '"><i class="fas fa-eye"></i> ' . _lang('View') . '</a></li>'
                        . '<a href="' . route('auditoriaQuoHistorial', $quotation->id) . '" data-title="' . _lang('Historial de Quotations') . '" data-fullscreen="true" class="dropdown-item ajax-modal"><i class="ti-list"></i> ' . _lang('Historial') . '</a></li>'
                        . '</div>'
                        . '</div>';
				}
				
                if ($quotation->related_to == 'contacts') {
                    return '<div class="dropdown">'
                        . '<button class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown">' . _lang('Action')
                        . '&nbsp;<i class="fas fa-angle-down"></i></button>'
                        . '<div class="dropdown-menu">'
                        . '<a class="dropdown-item" href="' . action('QuotationController@edit', $quotation->id) . '"><i class="fas fa-edit"></i> ' . _lang('Edit') . '</a></li>'
                        . '<a class="dropdown-item" href="' . action('QuotationController@show', $quotation->id) . '"><i class="fas fa-eye"></i> ' . _lang('View') . '</a></li>'
                        . '<a href="' . route('auditoriaQuoHistorial', $quotation->id) . '" data-title="' . _lang('Historial de Quotations') . '" data-fullscreen="true" class="dropdown-item ajax-modal"><i class="ti-list"></i> ' . _lang('Historial') . '</a></li>'
                        . '<a href="' . url('reservas/create_payment/' . $quotation->id) . '" data-title="' . _lang('Make Payment') . '" class="dropdown-item ajax-modal"><i class="fas fa-credit-card"></i> ' . _lang('Make Payment') . '</a>'
                        // . '<a href="' . url('reservas/view_payment/' . $quotation->id) . '" data-title="' . _lang('View Payment') . '" data-fullscreen="true" class="dropdown-item ajax-modal"><i class="fas fa-credit-card"></i> ' . _lang('View Payment') . '</a>'
    
                        . '<a class="dropdown-item" href="' . action('QuotationController@convert_invoice', $quotation->id) . '"><i class="fas fa-exchange-alt"></i> ' . _lang('Convertir a Venta') . '</a></li>'
                        . '<form action="' . action('QuotationController@destroy', $quotation['id']) . '" method="post">'
                        . csrf_field()
                        . '<input name="_method" type="hidden" value="DELETE">'
                        . '<button class="button-link btn-remove" type="submit"><i class="fas fa-recycle"></i> ' . _lang('Delete') . '</button>'
                        . '</form>'
                        . '</div>'
                        . '</div>';
                } else {
                    return '<div class="dropdown">'
                        . '<button class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown">' . _lang('Action')
                        . '&nbsp;<i class="fas fa-angle-down"></i></button>'
                        . '<div class="dropdown-menu">'
                        . '<a class="dropdown-item" href="' . action('QuotationController@edit', $quotation->id) . '"><i class="fas fa-edit"></i> ' . _lang('Edit') . '</a></li>'
                        . '<a class="dropdown-item" href="' . action('QuotationController@show', $quotation->id) . '"><i class="fas fa-eye"></i> ' . _lang('View') . '</a></li>'

                        . '<a href="' . route('auditoriaQuoHistorial', $quotation->id) . '" data-title="' . _lang('Historial de Quotations') . '" data-fullscreen="true" class="dropdown-item ajax-modal"><i class="ti-list"></i> ' . _lang('Historial') . '</a></li>'
                        
                        . '<form action="' . action('QuotationController@destroy', $quotation['id']) . '" method="post">'
                        . csrf_field()
                        . '<input name="_method" type="hidden" value="DELETE">'
                        . '<button class="button-link btn-remove" type="submit"><i class="fas fa-recycle"></i> ' . _lang('Delete') . '</button>'
                        . '</form>'
                        . '</div>'
                        . '</div>';
                }
            })
			 ->filterColumn('status', function ($query, $keyword) {
                    if ($keyword != "todos") {
                        $query->where('status', '=', $keyword);
					}		
                    
                })
				
				->filterColumn('quotation_date', function ($query, $keyword) {
				 $date_range = ($keyword != '') ? explode(" - ", $keyword) : array();
                    if (count($date_range) == 2) {
                        $query->whereDate('quotation_date', '>=', $date_range[0])
                            ->whereDate('.quotation_date', '<=', $date_range[1]);
                    }                   
                })
				
            ->setRowId(function ($invoice) {
                return "row_" . $invoice->id;
            })
            ->rawColumns(['grand_total', 'action', 'contact_name', 'quotation_number'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
		
        $idCar = $request->get('idCar', false);
        $idProduct = $request->get('idProduct', false);
        $vehiculo = Cars::with('marca_modelo')->where('id', $idCar)->first();
		//dd($vehiculo->idEstado);
		if (in_array($vehiculo->idEstado, array(1,5,6))) {
        //if ($vehiculo->idEstado==4){
            return redirect('invoices/buscador_de_piezas')
            ->withErrors("Estado no esta autorizado...".$idCar)
            ->withInput();
        }
		
		
        $item = Item::whereHas('product', function ($p) use ($idProduct) {
            $p->where('id', $idProduct);
        })->with('product')->first();
        //dd($idProduct);

        $users = User::all()->where('user_type', '!=', 'admin')->where('user_type', '!=', 'user');
        if (!$request->ajax()) {
            return view('backend.accounting.quotation.create', compact('idCar', 'vehiculo', 'idProduct', 'item', 'users'));
        } else {
            return view('backend.accounting.quotation.modal.create');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $car_id = $request->input('car_id');
		
        $validator = Validator::make(
            $request->all(),
            [
                // 'quotation_number' => 'required|max:191',
                'related_to' => 'required',
                'client_id' => 'required_if:related_to,contacts',
                'lead_id' => 'required_if:related_to,leads',
                'quotation_date' => 'required',
                'product_id' => 'required',
				'product_id.*' => 'required', 'product_id' => ['nullable', function ($attribute, $value, $fail) use ($request,$car_id): void {
                $item = QuotationItem::selectRaw('GROUP_CONCAT(DISTINCT  items.item_name) AS codigos')
					->join('items', 'items.id', '=', 'quotation_items.item_id')
                    ->whereIn('quotation_items.item_id', $request->product_id)
                    ->where('quotation_items.idCar',$car_id)->first();
                if ($item->codigos) {
                    $fail('Items ya se encuentra vendido.'.$item->codigos);
                    return;
                }
            },
            ], ///['required', 'distinct', Rule::unique('quotation_items','item_id','idCar')],
                //'car_id' => 'required',
                'template' => 'required',
            ],
            [
                'product_id.required' => _lang('You must select at least one product or service')
            ]
        );
	
		
       // dd($request->product_id);
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect('reservas/create?idCar='.$car_id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }
        $car = Cars::find($request->input('car_id'));
        //if ($car->idEstado==4){
		if (in_array($car->idEstado, array(1,5,6))) {	
            return redirect('reservas/create?idCar='.$car_id)
            ->withErrors("Estado no esta autorizado...")
            ->withInput();
        }

        DB::beginTransaction();
        $company_id = $car->company_id;
        $quotation_number=get_company_option('quotation_starting');

        $quotation = new Quotation();
        $quotation->quotation_number = $quotation_number;
        $quotation->quotation_date = $request->input('quotation_date');
        $quotation->template = $request->input('template');
        $quotation->grand_total = $request->product_total + $request->tax_total;
        $quotation->tax_total = $request->input('tax_total');
        $quotation->note = $request->input('note');
        $quotation->related_to = $request->related_to;
        $quotation->car_id = $request->input('car_id', null);
        $quotation->desarmar = $request->input('desarmar', null);
        $quotation->fecha_entrega = $request->input('fecha_entrega', null);
        $quotation->retiro = $request->input('retiro', null);
        $quotation->entregado_a = $request->input('entregado_a', null);
        $quotation->entregado_por = $request->input('entregado_por', null);
        if ($request->related_to == 'contacts') {
            $quotation->related_id = $request->client_id;
            $quotation->converted_total = convert_currency(base_currency(), $quotation->client->currency, $quotation->grand_total);

        } else {
            $quotation->related_id = $request->lead_id;
            $quotation->converted_total = convert_currency(base_currency(), $quotation->lead->currency, $quotation->grand_total);
        }

        $quotation->company_id = $company_id;
        $quotation->user_id = auth()->id();
        $quotation->status = 'Pendiente';
        //dd($quotation->desarmar);
        $quotation->save();



        $taxes = Tax::where('company_id', $company_id)->get();

        //Save quotation Item
        for ($i = 0; $i < count($request->product_id); $i++) {
            //$product = Product::where('id', $request->product_id[$i])->first();
            $quotationItem = new quotationItem();
            $quotationItem->quotation_id = $quotation->id;
           // $quotationItem->item_id = $product->item->id;
            $quotationItem->item_id = $request->product_id[$i];
           
            //$quotationItem->product_id = $request->product_id[$i];
            $quotationItem->description = $request->product_description[$i];
            $quotationItem->quantity = $request->quantity[$i];
            $quotationItem->unit_cost = $request->unit_cost[$i];
            $quotationItem->discount = $request->discount[$i];
            //$quotationItem->tax_method = $request->tax_method[$i];
            //$quotationItem->tax_id = $request->tax_id[$i];
            $quotationItem->tax_amount = $request->product_tax[$i];
            $quotationItem->sub_total = $request->sub_total[$i];
            $quotationItem->idCar = $request->autos[$i] ?? null;
            $quotationItem->product_id = $request->product_id[$i] ?? null;

            $quotationItem->save();

          //  $this->orden_desarme($quotation, $quotationItem->item_id);
          // Se comenta, Axel dice que debe sacarse ya que desde reserva es imposible generar orden de desarme.

            //Store Quotation Taxes
            if (isset($request->tax[$quotationItem->item_id])) {
                foreach ($request->tax[$quotationItem->item_id] as $taxId) {
                    $tax = $taxes->firstWhere('id', $taxId);

                    $quotationItemTax = new QuotationItemTax();
                    $quotationItemTax->quotation_id = $quotationItem->quotation_id;
                    $quotationItemTax->quotation_item_id = $quotationItem->id;
                    $quotationItemTax->tax_id = $tax->id;
                    $tax_type = $tax->type == 'percent' ? '%' : '';
                    $quotationItemTax->name = $tax->tax_name . ' @ ' . $tax->rate . $tax_type;
                    $quotationItemTax->amount = $tax->type == 'percent' ? ($quotationItem->sub_total / 100) * $tax->rate : $tax->rate;
                    $quotationItemTax->company_id = $company_id;
                    $quotationItemTax->save();
                }
            }

        }

        //Increment quotation Starting number
        $data = array();
        $data['value'] = $quotation_number + 1;
        $data['company_id'] = $company_id;
        $data['updated_at'] = Carbon::now();
		
		
		if (CompanySetting::where('name', "quotation_starting")->exists()) {
            CompanySetting::where('name', 'quotation_starting')
                ->update($data);
        } else {
            $data['name'] = 'quotation_starting';
            $data['created_at'] = Carbon::now();
            CompanySetting::insert($data);
        }
		

       /*if (CompanySetting::where('name', "quotation_starting")->where("company_id", $company_id)->exists()) {
            CompanySetting::where('name', 'quotation_starting')
                ->where("company_id", $company_id)
                ->update($data);
        } else {
            $data['name'] = 'quotation_starting';
            $data['created_at'] = Carbon::now();
            CompanySetting::insert($data);
        }*/

        //Update Package limit
        update_package_limit('quotation_limit');




        DB::commit();

        if (!$request->ajax()) {
            return redirect('reservas/' . $quotation->id)->with('success', _lang('Quotation Created Sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Quotation Created Sucessfully'), 'data' => $quotation]);
        }

    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $quotation = Quotation::where("id", $id)->first(); //->where("company_id",company_id())
		$car = Cars::with('marca_modelo')->where('id', $quotation->car_id)->first();
        $quotation_taxes = QuotationItemTax::where('quotation_id', $id)
            ->selectRaw('quotation_item_taxes.*,sum(quotation_item_taxes.amount) as tax_amount')
            ->groupBy('quotation_item_taxes.tax_id')
            ->get();


        $transactions = Transaction::where("id_quotation", $id)->get();
        if (!$request->ajax()) {
            $template = $quotation->template;
            if ($template == "") {
                $template = "modern";
            }

            return view("backend.accounting.quotation.template.$template", compact('quotation', 'quotation_taxes', 'id', 'transactions','car'));
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
        $quotation = Quotation::where("id", $id)->first(); //->where("company_id",company_id())
        $status = $this->status;
        $users = User::all()->where('user_type', '!=', 'admin')->where('user_type', '!=', 'user');
        $showStatus = auth()->user()->role->name == 'administrativo' ? true : false;
        $idCar = false;
        $idProduct = false;
		$rol = Role::where('name', 'Vendedor')->first()->id;
        // $vehiculos= Cars::where('company_id',company_id())->with('marca_modelo')->get();
        $vehiculo = Cars::with('marca_modelo')->where('id', $quotation->car_id)->first();
        if (!$request->ajax()) {
            return view('backend.accounting.quotation.edit', compact(
                'quotation',
                'id',
                'status',
                'showStatus',
                'users',
                'idCar',
                'idProduct',
                'vehiculo',
				'rol'
            ));
        } else {
            return view('backend.accounting.quotation.modal.edit', compact(
                'quotation',
                'id',
                'status',
                'showStatus',
                'users',
                'idCar',
                'idProduct',
                'vehiculos',
				'rol'
            ));
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
        $validator = Validator::make(
            $request->all(),
            [
                'quotation_number' => 'required|max:191',
                'related_to' => 'required',
                'client_id' => 'required_if:related_to,customer',
                'lead_id' => 'required_if:related_to,lead',
                'quotation_date' => 'required',
                'product_id' => 'required', 
				//'product_id.*' => ['required', 'distinct', Rule::unique('quotation_items','item_id',$id,'quotation_id')],
				//'product_id.*' =>  ['required', Rule::unique('quotation_items','item_id')->unique('quotation_items','idCar')->where('quotation_items.quotation_id', $id)],
				'product_id.*' => ['distinct',Rule::unique('quotation_items', 'item_id')->where('quotation_id', $id)->where('idCar', 'autos.*') ],
                'template' => 'required',
            ],
            [
                'product_id.required' => _lang('You must select at least one product or service')
            ]
        );
		
		
//		Rule::unique('quotation_items')->where('company_id', $this->company_id)

//  'doctor_id.*' => 'unique:project_orders,doctor_id,NULL,id,student_id,'.$student->id,

//    'doctor_id.*' => [ Rule::unique('project_orders', 'doctor_id')->where('student_id', $student->id) ]
//'name' => 'unique:table,field,$id,id,field1,value1,field2,value2,field3,value3'



        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('reservas.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        DB::beginTransaction();

        $quotation = Quotation::where("id", $id)->first();
        // $car = Cars::find(quotation);
        $company_id = $quotation->company_id;


        $previous_amount = $quotation->grand_total;
        $quotation->quotation_number = $request->input('quotation_number');
        $quotation->quotation_date = $request->input('quotation_date');
        $quotation->template = $request->input('template');
        $quotation->grand_total = $request->product_total + $request->tax_total;
        $quotation->tax_total = $request->input('tax_total');
        $quotation->note = $request->input('note');
        $quotation->desarmar = $request->input('desarmar', null);

        $quotation->fecha_entrega = $request->input('fecha_entrega', null);
        $quotation->retiro = $request->input('retiro', null);
        $quotation->entregado_a = $request->input('entregado_a', null);
        $quotation->entregado_por = $request->input('entregado_por', null);

        if ($quotation->related_to == 'contacts') {
            $quotation->related_id = $request->input('client_id');
            if ($previous_amount != $quotation->grand_total) {
                $quotation->converted_total = convert_currency(base_currency(), $quotation->client->currency, $quotation->grand_total);
            }
        } else {
            $quotation->related_id = $request->input('lead_id');
            if ($previous_amount != $quotation->grand_total) {
                $quotation->converted_total = convert_currency(base_currency(), $quotation->lead->currency, $quotation->grand_total);
            }
        }

        $quotation->company_id = $company_id;
        $status = $request->input('status', false);
        if ($status) {
            $quotation->status = $status;
        }
		
		/*$vendedor = $request->input('vendedor', false);
        if (!$vendedor) {
            $vendedor = auth()->id();
        }*/
        $quotation->user_id = $request->input('vendedor', false);
		
		
        $quotation->save();

        $taxes = Tax::where('company_id', $company_id)->get();

        //Update quotation item
        $quotationItem = QuotationItem::where("quotation_id", $id);
        $quotationItem->delete();

        $quotationItemTax = QuotationItemTax::where("quotation_id", $id);
        $quotationItemTax->delete();

        for ($i = 0; $i < count($request->product_id); $i++) {
            $quotationItem = new quotationItem();
            $quotationItem->quotation_id = $quotation->id;
            $quotationItem->item_id = $request->product_id[$i];
            $quotationItem->description = $request->product_description[$i];
            $quotationItem->quantity = $request->quantity[$i];
            $quotationItem->unit_cost = $request->unit_cost[$i];
            $quotationItem->discount = $request->discount[$i];
            //$quotationItem->tax_method = $request->tax_method[$i];
            //$quotationItem->tax_id = $request->tax_id[$i];
            $quotationItem->tax_amount = $request->product_tax[$i];
            $quotationItem->sub_total = $request->sub_total[$i];
            $quotationItem->idCar = $request->autos[$i] ?? null;
            $quotationItem->save();

            //Store Quotation Taxes
            if (isset($request->tax[$quotationItem->item_id])) {
                foreach ($request->tax[$quotationItem->item_id] as $taxId) {
                    $tax = $taxes->firstWhere('id', $taxId);

                    $quotationItemTax = new QuotationItemTax();
                    $quotationItemTax->quotation_id = $quotationItem->quotation_id;
                    $quotationItemTax->quotation_item_id = $quotationItem->id;
                    $quotationItemTax->tax_id = $tax->id;
                    $tax_type = $tax->type == 'percent' ? '%' : '';
                    $quotationItemTax->name = $tax->tax_name . ' @ ' . $tax->rate . $tax_type;
                    $quotationItemTax->amount = $tax->type == 'percent' ? ($quotationItem->sub_total / 100) * $tax->rate : $tax->rate;
                    $quotationItemTax->company_id = $company_id;
                    $quotationItemTax->save();
                }
            }
        }

        DB::commit();

        if (!$request->ajax()) {
            return redirect('reservas/' . $quotation->id)->with('success', _lang('Quotation updated sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Quotation updated sucessfully'), 'data' => $quotation]);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        DB::beginTransaction();
		$quotation = Quotation::where("id", $id)->first();
        $quotation->status='Anulada';
		$quotation->save();
		
		
		
        /*Transaction::where("id_quotation", $id)->delete();
        $quotation = Quotation::where("id", $id);
        $quotation->delete();

        $quotationItem = QuotationItem::where("quotation_id", $id);
        $quotationItem->delete();

        $quotationItemTax = QuotationItemTax::where('quotation_id', $id);
        $quotationItemTax->delete();*/

        DB::commit();

        return redirect('reservas')->with('success', _lang('Quotation Removed Sucessfully'));
    }

    public function convert_invoice($quotation_id)
    {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);
		
		$quotation = Quotation::where("id", $quotation_id)->first();

		$vehiculo = Cars::with('marca_modelo')->where('id', $quotation->car_id)->first();

		if (!in_array($vehiculo->idEstado, array(1,5,6))) {
            return redirect('invoices/buscador_de_piezas')
            ->withErrors("Estado no esta autorizado...".$quotation->car_id)
            ->withInput();
        }


        DB::beginTransaction();

		
        $invoice = new Invoice();
        $invoice->invoice_number = get_company_option('invoice_starting');
        $invoice->invoice_date = date('Y-m-d');
        $invoice->due_date = date('Y-m-d');
        $invoice->grand_total = $quotation->grand_total;
        $invoice->tax_total = $quotation->tax_total;
        $invoice->paid = $quotation->paid;
        $invoice->status = $quotation->status;
        $invoice->note = $quotation->note;
        $invoice->template = $quotation->template;
        $invoice->related_to = $quotation->related_to;
        $invoice->related_id = $quotation->related_id;
        $invoice->client_id = $quotation->related_id;
        $invoice->user_id = $quotation->user_id; //auth()->id();
        $invoice->fecha_entrega = $quotation->fecha_entrega;
        $invoice->retiro = $quotation->retiro;
        $invoice->entregado_a = $quotation->entregado_a;
        $invoice->entregado_por = $quotation->entregado_por;
		//--$invoice->desarmar = 0;


        if ($invoice->related_to == 'contacts') {
            $invoice->related_id = $quotation->related_id;
            $invoice->client_id = $quotation->related_id;
            $invoice->converted_total = $quotation->converted_total;
        }

        $invoice->company_id = $quotation->company_id;

        $invoice->save();

        Transaction::where("id_quotation", $quotation->id)->update(['invoice_id' => $invoice->id, 'id_quotation' => null]);
        $taxes = Tax::all();

/*

$hasPiezaSavedForUser = Product::query()
                                ->where('item_id', $value)
                                ->where('nro_interno', $request->input('nro_interno'))
                                ->where('car_id', null)
                                ->exists();

                            if ($hasPiezaSavedForUser) {
                                $fail('Item ya se encuentra asignado al nro interno.');
                                return;
                            }
*/

        //Save Invoice Item
        foreach ($quotation->quotation_items as $quotation_item) {
            //$product = Product::where('id', $quotation_item->product_id)->first();
            $product = Product::where('item_id', $quotation_item->item_id)
			->where('nro_interno', $quotation_item->idCar)
			// ->where('car_id', null)
			->first();
			//dd($product);
			$invoiceItem = new InvoiceItem();
            $invoiceItem->invoice_id = $invoice->id;
            $invoiceItem->item_id = $quotation_item->item_id;
            $invoiceItem->quantity = $quotation_item->quantity;
            $invoiceItem->unit_cost = $quotation_item->unit_cost;
            $invoiceItem->discount = $quotation_item->discount;
            //$invoiceItem->tax_method = $quotation_item->tax_method;
            //$invoiceItem->tax_id = $quotation_item->tax_id;
            $invoiceItem->tax_amount = $quotation_item->tax_amount;
            $invoiceItem->sub_total = $quotation_item->sub_total;
            $invoiceItem->idCar = $quotation_item->idCar ?? null;
            $invoiceItem->company_id = $quotation->company_id;
 		    $invoiceItem->product_id = $quotation_item->product_id;
            $invoiceItem->save();
			
			
			$company_id =  $invoiceItem->company_id;
            //Store Invoice Taxes
          
		  foreach ($quotation_item->taxes as $quotation_tax) {
                $tax = $taxes->firstWhere('id', $quotation_tax->tax_id);

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
			

			if (!empty($product)){ // envia a retiro
			
					if ($product->stock < 1) { //$request->quantity[$i]
						DB::rollBack();
						return back()->with('error', $invoiceItem->item->item_name . ' ' . _lang('Stock is not available!'));
					}
					
					//si es del deposito octubre notificar
					if ($product->idDeposito == 4 && !$product->allCar) {
						//notificar venta de producto
						$user_all = User::find(['47', '49', '58', '169']);
						foreach ($user_all as $enviar_user) {
							Notification::send($enviar_user, new InvoiceProductOctubre($product));
							sleep(1);
						}
					}
					
			
					if ($product->mercado_libre == 1 && !$product->allCar) {
						//notificar venta de producto de mercado libre
						$user_all = User::find(['47', '49', '58', '169']);
						foreach ($user_all as $enviar_user) {
							Notification::send($enviar_user, new InvoiceProductMercadoLibre($product));
							sleep(1);
						}
					}
					
					 	$invoiceItem->product_id = $product->id;
						$invoiceItem->save();
						
						$orden_despacho = new OrdenDespacho();
					    $orden_despacho->invoice_id = $invoice->id;
						$orden_despacho->invoiceitem_id = $invoiceItem->id;//$product->id;---
						$orden_despacho->description = $quotation_item->description;
						$orden_despacho->quantity = $quotation_item->quantity;
						$orden_despacho->company_id = $product->company_id;
						$orden_despacho->estatus = 'pendiente';
						$orden_despacho->save();
					 
					
					
					 //Update Stock
					//$stock = Product::where("id", $product->id)->first();
					//if (!empty($stock)) {
						$product->stock = $product->stock - 1;
						$product->save();
					//}
				
			}else{ // envia a desarme
			
						$orden_desarme = new Orden_desarme();
						$company = '';
						$prioridad = 'normal';

						if ($invoiceItem->company_id == 1) {
							$company = 'PM-';
						} else if ($invoiceItem->company_id == 2) {
							$company = 'PC-';
						}

						$orden_desarme->id_venta = $invoiceItem->invoice_id;
						$orden_desarme->fecha_venta = $invoice->invoice_date;

						//            dd($product);
						$orden_desarme->idCar = $invoiceItem->idCar ?? null;
						$orden_desarme->prioridad = $prioridad;

						$orden_desarme->interno = $company . ($invoiceItem->idCar);
						$orden_desarme->pieza = $invoiceItem->item_id;

						// Aqui colocae orden procesada y asignarla al operario segun la compañia
						$orden_desarme->procesar = 1;

						/*$operario = User::wherehas('role', function ($q) {
							$q->where('name', 'Operario');
						})->where('company_id', $invoiceItem->company_id)->first();

						$orden_desarme->idCadete_operario =  $operario->id;*/

                        $operario = Puesto::where('predeterminada', '1')->where('company_id', $product->company_id)->first();
						$orden_desarme->idCadete_operario =  $operario->user_id;

						$orden_desarme->save();

						// enviar notificacion al operario de creada una orden
						Notification::send($operario, new OrdenCreated($orden_desarme));
		
				
			}
        }

		if ($invoice->client->user->id != null) {
			Notification::send($invoice->client->user, new InvoiceCreated($invoice));
		}


        //Increment Invoice Starting number
        increment_invoice_number();

		///// comision
		/*$data['invoice'] = Invoice::find($idVenta);
        $data['comision'] = Comision::where('id_venta', $idVenta)->where('id_vendedor', $data['invoice']->user_id)->first();
        $data['id'] = $idVenta;

        $ventaMotor = InvoiceItem::where('invoice_id', $data['invoice']->id)->whereHas('item', function ($sql) {
            $sql->where('item_name', 'Motor');
        })->first();
        //dd($ventaMotor);
        $data['comisionDefault'] = 7;
        if (!empty($ventaMotor)) {
            $data['comisionDefault'] = 2.5;
        }*/


        //Remove Existing Quotation
		/*$quotationItem = QuotationItem::where("quotation_id", $quotation_id);
        $quotationItem->delete();
		
        $quotation = Quotation::where("id", $quotation_id);//->where("company_id", company_id());
        $quotation->delete();*/
		
		$quotation = Quotation::where("id", $quotation_id)->first();
        $quotation->status='Convertida';
		$quotation->save();

        DB::commit();

        return redirect('invoices/' . $invoice->id)->with('success', _lang('Quotation Converted Sucessfully'));

/**************************************************************************************************************************



        
        




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


        $this->orden_desarme($invoice, $desarme, $prioridad);
        DB::commit();

        $this->pago_desde_cc($invoice->id, $invoice->client_id);

**************************************************************************************************************************/

    }

    public function create_email(Request $request, $quotation_id)
    {
        $quotation = Quotation::where("id", $quotation_id)
            ->where("company_id", company_id())->first();

        $client_email = $quotation->client->contact_email;
        if ($request->ajax()) {
            return view('backend.accounting.quotation.modal.send_email', compact('client_email', 'quotation'));
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
        $quotation = Quotation::where('id', $request->quotation_id)
            ->where('company_id', company_id())
            ->first();

        $currency = currency();

        if ($contact) {
            //Replace Paremeter
            $replace = array(
                '{customer_name}' => $contact->contact_name,
                '{quotation_no}' => $quotation->quotation_number,
                '{quotation_date}' => date('d M,Y', strtotime($quotation->quotation_date)),
                '{grand_total}' => decimalPlace($quotation->grand_total, $currency),
                '{quotation_link}' => url('client/view_quotation/' . md5($quotation->id)),
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
            return response()->json(['result' => 'success', 'message' => _lang('Email Send Sucessfully')]);
        }
    }

    public function orden_desarme($q, $idProduct, $tipo = 'quo', $idBuscar = false)
    {

        if ($q->desarmar) {
            if ($tipo == 'quo') {
                $orden_desarme = new Orden_desarme();
                $orden_desarme->id_cotizacion = $q->id;
                // $product = QuotationItem::where('id',$idProduct)->first();
                $orden_desarme->idCar = $q->car_id ?? null;

                $prodMarca = Product::where('item_id', $idProduct)->first();

                $orden_desarme->marca_modelo = $prodMarca->marca_modelo;
                $orden_desarme->pieza = $idProduct;



            } else {
                $orden_desarme = Orden_desarme::where('id_cotizacion', $idBuscar)->first();
                $orden_desarme->id_venta = $q->id;
                $orden_desarme->fecha_venta = $q->invoice_date;
                // $product = InvoiceItem::where('id',$idProduct)->first();
                $orden_desarme->idCar = $q->idCar ?? null;
            }

            $orden_desarme->save();
        }


    }

    public function create_payment(Request $request, $id)
    {
        $invoice = Quotation::where("id", $id)->first(); //->where("company_id", company_id())
		
		//dd($invoice->client->contact_name);

        if ($request->ajax()) {
            return view('backend.accounting.quotation.modal.create_payment', compact('invoice', 'id'));
        }
    }

    public function store_payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'quotation_id' => 'required',
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

        DB::beginTransaction();



        //Update Invoice Table
        $invoice = Quotation::where("id", $request->input('quotation_id'))->first(); //->where("company_id", $company_id)
        $company_id = $invoice->company_id;


        if (($invoice->paid + $request->input('amount')) > $invoice->grand_total) {
            // descontar al pago de la transaccion el monto de la factura
            $montoPrevioMasPago = $invoice->paid + $request->input('amount');
            $montoFactura = $invoice->grand_total;


            $montoTransa = $montoFactura - $invoice->paid;
            $montoCC = $montoPrevioMasPago - $montoFactura;
            //            if ($invoice->paid > $montoFactura &&  $montoPrevioMasPago > $montoFactura ){
//                $montoTransa = false;
//                $montoCC = $request->input('amount');
//
//            }
            if ($invoice->paid == $montoFactura || $invoice->paid > $montoFactura) {
                $montoTransa = false;
                $montoCC = $request->input('amount');

            }

            //            dd(['$montoTransa' => $montoTransa , '$montoCC' =>$montoCC]);

            if ($montoTransa) {
                $transaction = new Transaction();
                $transaction->trans_date = date('Y-m-d');
                $transaction->account_id = $request->input('account_id');
                $transaction->chart_id = $request->input('chart_id');
                $transaction->type = 'income';
                $transaction->dr_cr = 'cr';
                $transaction->amount = $request->input('amount');
                $transaction->base_amount = convert_currency($transaction->account->account_currency, base_currency(), $transaction->amount);
                $transaction->payer_payee_id = $request->input('client_id');
                $transaction->payment_method_id = $request->input('payment_method_id');
                $transaction->id_quotation = $request->input('quotation_id');
                $transaction->reference = $request->input('reference');
                $transaction->note = $request->input('note');
                $transaction->attachment = $attachment;
                $transaction->company_id = $company_id;

                $transaction->razon_social = $request->input('razon_social');
                $transaction->tipo_comprobante_id = $request->input('tipo_comprobante_id');

                $transaction->tasa = $request->input('tasa');
                $transaction->usd = $request->input('usd');
                $transaction->amount_usd = $request->input('amount_usd');
                $transaction->amount_peso = $request->input('amount_pesos');

                $transaction->save();
                $invoice->paid = $montoFactura;


                if (round($invoice->paid, 2) >= $invoice->grand_total) {
                    $invoice->status = 'Pagado';
                } else if (round($invoice->paid, 2) > 0 && (round($invoice->paid, 2) < $invoice->grand_total)) {
                    $invoice->status = 'Pago parcial';
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


            }


            // monto agregar a la cuenta corriente
            // buscar Abono cc

            $methodP = PaymentMethod::where('name', 'like', '%Abono cc')->first();
            //dd($methodP);

            $transaction = new Transaction();
            $transaction->trans_date = date('Y-m-d');
            $transaction->account_id = $request->input('account_id');
            $transaction->chart_id = $request->input('chart_id');
            $transaction->type = 'cc';
            $transaction->dr_cr = 'cc';
            $transaction->amount = $montoCC;
            $transaction->base_amount = convert_currency($transaction->account->account_currency, base_currency(), $transaction->amount);
            $transaction->payer_payee_id = $request->input('client_id');
            $transaction->payment_method_id = $methodP->id; //$request->input('payment_method_id');
            $transaction->id_quotation = $request->input('quotation_id');
            $transaction->reference = $request->input('reference');
            $transaction->note = $request->input('note');
            $transaction->attachment = $attachment;
            $transaction->company_id = $company_id;

            $transaction->tasa = $request->input('tasa');
            $transaction->usd = $request->input('usd');
            $transaction->amount_usd = $request->input('amount_usd');
            $transaction->amount_peso = $request->input('amount_pesos');

            $transaction->save();
        } else {
            $transaction = new Transaction();
            $transaction->trans_date = date('Y-m-d');
            $transaction->account_id = $request->input('account_id');
            $transaction->chart_id = $request->input('chart_id');
            $transaction->type = 'income';
            $transaction->dr_cr = 'cr';
            $transaction->amount = $request->input('amount');
            $transaction->base_amount = convert_currency($transaction->account->account_currency, base_currency(), $transaction->amount);
            $transaction->payer_payee_id = $request->input('client_id');
            $transaction->payment_method_id = $request->input('payment_method_id');
            $transaction->id_quotation = $request->input('quotation_id');
            $transaction->reference = $request->input('reference');
            $transaction->note = $request->input('note');
            $transaction->attachment = $attachment;
            $transaction->company_id = $company_id;

            $transaction->razon_social = $request->input('razon_social');
            $transaction->tipo_comprobante_id = $request->input('tipo_comprobante_id');

            $transaction->tasa = $request->input('tasa');
            $transaction->usd = $request->input('usd');
            $transaction->amount_usd = $request->input('amount_usd');
            $transaction->amount_peso = $request->input('amount_pesos');

            $transaction->save();

            $invoice->paid = $invoice->paid + $transaction->base_amount;
            if (round($invoice->paid, 2) >= $invoice->grand_total) {
                $invoice->status = 'Paid';
            } else if (round($invoice->paid, 2) > 0 && (round($invoice->paid, 2) < $invoice->grand_total)) {
                $invoice->status = 'Partially_Paid';
            }
            $invoice->save();


            //Send Invoice Payment Confrimation to Client
            // @ini_set('max_execution_time', 0);
            // @set_time_limit(0);
            // Overrider::load("Settings");
            // $mail = new \stdClass();
            // $mail->subject = _lang('Invoice Payment');
            // $mail->invoice = $invoice;
            // $mail->transaction = $transaction;
            // $mail->method = $transaction->payment_method->name;
            // $mail->currency = currency();
        }


        // try {
        //     Mail::to($invoice->client->contact_email)->send(new InvoiceReceiptMail($mail));
        // } catch (\Exception$e) {
        //     //Nothing
        // }

        DB::commit();

        if ($request->ajax()) {
            $request->session()->flash('success', _lang('Payment was made Sucessfully'));
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Payment was made Sucessfully'), 'data' => $transaction]);
        }
    }
	
		public function pdf(Request $request, $id){
			//$pdf = \PDF::loadView('backend.accounting.quotation.pdf');
			//return $pdf->download('ejemplo.pdf');
			
			$quotation = Quotation::where("id", $id)->first(); //->where("company_id",company_id())
		$car = Cars::with('marca_modelo')->where('id', $quotation->car_id)->first();
        $quotation_taxes = QuotationItemTax::where('quotation_id', $id)
            ->selectRaw('quotation_item_taxes.*,sum(quotation_item_taxes.amount) as tax_amount')
            ->groupBy('quotation_item_taxes.tax_id')
            ->get();


        $transactions = Transaction::where("id_quotation", $id)->get();
        //return view("backend.accounting.quotation.pdf", compact('quotation', 'quotation_taxes', 'id', 'transactions','car'));

			$pdf = \PDF::loadView('backend.accounting.quotation.pdf',compact('quotation', 'quotation_taxes', 'id', 'transactions','car'));
			return $pdf->download("reserva_$id.pdf");
		}




        public function auditoriaQuoHistorial(Request $request, $id)
{

    return view('backend.accounting.quotation.modal.historial', compact('id')); 
}


public function auditoriaQuotation(Request $request)
    {
		   $id = $request->id;
		   
		  if (request()->ajax()) {
            $datosAudit = Audit::where('auditable_type', Quotation::class)
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

}
