<?php

namespace App\Http\Controllers;

use App\Utilities\cc_client;
use Illuminate\Http\Request;
use App\Contact;
use Validator;
use Auth;
use App\User;
use App\Invoice;
use App\Quotation;
use App\Transaction;
use App\SalesReturn;
use App\ChartOfAccount;
use Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Mail;
use App\Notifications\ContactAccount as ContactAccountNotification;
use App\Mail\GeneralMail;
use App\Utilities\Overrider;
use App\Imports\ContactsImport;
use Maatwebsite\Excel\Facades\Excel;
use DataTables;
//use DB;
use Illuminate\Support\Facades\DB;

class ContactController extends Controller
{
	use cc_client;
	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		date_default_timezone_set(get_company_option('timezone', get_option('timezone', 'Asia/Dhaka')));

		$this->middleware(function ($request, $next) {
			if (has_membership_system() == 'enabled') {
				if (! has_feature('contacts_limit')) {
					return redirect('membership/extend')->with('message', _lang('Your Current package not support this feature. You can upgrade your package !'));
				}

				//If request is create/store
				$route_name = \Request::route()->getName();
				if ($route_name == 'contacts.store') {
					if (! has_feature_limit('contacts_limit')) {
						if (! $request->ajax()) {
							return redirect('membership/extend')->with('message', _lang('Your have already reached your usages limit. You can upgrade your package !'));
						} else {
							return response()->json(['result' => 'error', 'message' => _lang('Your have already reached your usages limit. You can upgrade your package !')]);
						}
					}
				}
			}

			return $next($request);
		});
	}

	/**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index()
	{

		return view('backend.accounting.contacts.contact.list');
	}


	public function get_table_data()
	{
		$currency = currency();
		$company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
		$contacts = Contact::with("group")->select('contacts.*')
			//->where("contacts.company_id", company_id())
			 ->whereIn('company_id', $company_id)
			->orderBy("contacts.id", "desc");

		return Datatables::eloquent($contacts)

			->editColumn('contact_image', function ($contact) {
				return '<img class="thumb-sm rounded-btn-xs mr-2" src="' . asset('public/uploads/contacts/' . $contact->contact_image) . '">';
			})

			->editColumn('contact_name', function ($contact) {
				return '<a href="' . action('ContactController@show', $contact['id']) . '">' . $contact->contact_name . '</a';
			})

			->addColumn('action', function ($contact) {
				return '<form action="' . action('ContactController@destroy', $contact['id']) . '" class="text-center" method="post">'
					. '<a href="' . action('ContactController@show', $contact['id']) . '" class="btn btn-primary btn-xs"><i class="ti-eye"></i></a>&nbsp;'
					. '<a href="' . action('ContactController@edit', $contact['id']) . '" class="btn btn-warning btn-xs"><i class="ti-pencil"></i></a>&nbsp;'
					. csrf_field()
					. '<input name="_method" type="hidden" value="DELETE">'
					. '<button class="btn btn-danger btn-xs btn-remove" type="submit"><i class="ti-eraser"></i></button>&nbsp;'
					. '<a class="btn btn-success btn-xs ajax-modal" data-title="Ajustes en cuenta"
					href="' . action('ContactController@ajusteCuenta', $contact['id']) . '">A</a>'
					. '</form>';
			})
			->setRowId(function ($contact) {
				return "row_" . $contact->id;
			})
			->rawColumns(['action', 'contact_image', 'contact_name'])
			->make(true);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create(Request $request)
	{
		if (! $request->ajax()) {
			return view('backend.accounting.contacts.contact.create', ['estadosIva' => $this->estadosIva]);
		} else {
			return view('backend.accounting.contacts.contact.modal.create', ['estadosIva' => $this->estadosIva]);
		}
	}


	public function import(Request $request)
	{
		if ($request->isMethod('get')) {
			return view('backend.accounting.contacts.contact.import');
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
					return redirect('contacts/import')->withErrors($validator)
						->withInput();
				}
			}

			//Import Contacts
			//$file_type = $request->file('file')->getClientOriginalExtension();
			$new_rows = 0;

			DB::beginTransaction();

			$previous_rows = Contact::where('company_id', company_id())->count();

			$data = array();
			$data['group_id'] = $request->group_id;
			$import = Excel::import(new ContactsImport($data), request()->file('file'));

			$current_rows = Contact::where('company_id', company_id())->count();

			$new_rows = $current_rows - $previous_rows;

			DB::commit();

			return back()->with('success', $new_rows . ' ' . _lang('Rows Imported Sucessfully'));
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
		$validator = Validator::make($request->all(), [
			'profile_type'  => 'required|max:20',
			'company_name'  => 'nullable|max:50',
			'contact_name'  => 'required|max:50',
			// 'contact_email' => [
			//     //'required',
			//     //'email',
			//     Rule::unique('contacts'),
			// ],
			'contact_phone' => 'nullable|max:20',
			'country'       => 'nullable|max:50',
			'currency' 		=> 'required|max:3',
			'city' 			=> 'nullable|max:50',
			'state' 		=> 'nullable|max:50',
			'zip' 			=> 'nullable|max:20',
			'contact_image' => 'nullable|image||max:5120',
			'nombre_env'    => 'nullable|max:60',
			'apellidos_env' => 'nullable|max:60',
			'dni_env'       => 'nullable|max:60',
			'calle_env'     => 'nullable|max:100',
			'numero_env'    => 'nullable|max:30',
			'piso_env'      => 'nullable|max:30',
			'depto_env'     => 'nullable|max:30',
			'cp_env'        => 'nullable|max:30',
			'localidad_env' => 'nullable|max:30',
			'pcia_env'      => 'nullable|max:30',
			'tel_env'       => 'nullable|max:30',
			'group_id' 		=> 'required',
			//'name' => 'required_if:client_login,on|max:191', //User Login Attribute
			//'email' => 'required_if:client_login,on|email|unique:users|max:191', //User Login Attribute
			//'password' => 'required_if:client_login,on|max:20|min:6|confirmed', //User Login Attribute
			//'status' => 'required_if:client_login,on', //User Login Attribute
		], [
			'group_id.required' => 'The group field is required.'
		]);

		if ($validator->fails()) {
			if ($request->ajax()) {
				return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
			} else {
				return redirect('contacts/create')
					->withErrors($validator)
					->withInput();
			}
		}

		$contact_image = "avatar.png";
		if ($request->hasfile('contact_image')) {
			$file = $request->file('contact_image');
			$contact_image = "contact_image" . time() . '.' . $file->getClientOriginalExtension();
			$file->move(public_path() . "/uploads/contacts/", $contact_image);
		}


		DB::beginTransaction();

		//Check client has already an account
		$other = User::where('email', $request->contact_email)
			->where('user_type', '!=', 'client')->first();

		if ($other) {
			if ($request->ajax()) {
				return response()->json(['result' => 'error', 'message' => 'Sorry, This email already registered with an company admin or staff !']);
			} else {
				return redirect('contacts/create')->with('error', _lang('Sorry, This email already registered with an company admin or staff !'))->withInput();
			}
		}

		$client = User::where('email', $request->contact_email)
			->where('user_type', 'client')->first();


		$contact = new Contact();
		$contact->profile_type = $request->input('profile_type');
		$contact->company_name = $request->input('company_name');
		$contact->contact_name = $request->input('contact_name');
		$contact->contact_email = $request->input('contact_email');
		$contact->dni_cuit = $request->input('vat_id');
		$contact->reg_no = $request->input('reg_no');
		$contact->contact_phone = $request->input('contact_phone');
		$contact->country = $request->input('country');
		$contact->currency = $request->input('currency');
		$contact->city = $request->input('city');
		$contact->state = $request->input('state');
		$contact->zip = $request->input('zip');
		$contact->estadoIva = $request->input('estadoIva');
		$contact->address = $request->input('address');
		$contact->facebook = $request->input('facebook');
		$contact->twitter = $request->input('twitter');
		$contact->linkedin = $request->input('linkedin');
		$contact->remarks = $request->input('remarks');
		if ($client) {
			$contact->user_id = $client->id;
		}
		$contact->nombre_env = $request->input('nombre_env');
		$contact->apellidos_env = $request->input('apellidos_env');
		$contact->dni_env = $request->input('dni_env');
		$contact->calle_env = $request->input('calle_env');
		$contact->numero_env = $request->input('numero_env');
		$contact->piso_env = $request->input('piso_env');
		$contact->depto_env = $request->input('depto_env');
		$contact->cp_env = $request->input('cp_env');
		$contact->localidad_env = $request->input('localidad_env');
		$contact->pcia_env = $request->input('pcia_env');
		$contact->tel_env = $request->input('tel_env');
		$contact->group_id = $request->input('group_id');
		$contact->company_id = company_id();
		$contact->contact_image = $contact_image;

		$contact->save();

		//Update Package limit
		update_package_limit('contacts_limit');

		DB::commit();


		if (! $request->ajax()) {
			return redirect('contacts/create')->with('success', _lang('New client added sucessfully'));
		} else {
			return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('New client added sucessfully'), 'data' => $contact]);
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
		// dd('a');
		$company_id = company_id();
		$data = array();

		$data['contact'] = Contact::where("id", $id)
			//->where("company_id", $company_id)
			->first();


		$data['invoices'] = Invoice::where('client_id', $id)
			//->where("company_id", $company_id)
			->get();





		/*	$Invoice = Invoice::where('client_id', $id)
    ->withCount([
        'forums as forum_points' => fn ($query) => $query->select(DB::raw('sum(points)')),
        'posts as post_points'   => fn ($query) => $query->select(DB::raw('sum(points)')),
    ])
    ->orderByRaw('forum_points + post_points DESC')
    ->get();	*/

		$data['quotations'] = Quotation::where('related_id', $id)
			->where('related_to', 'contacts')
			//									   ->where("company_id", $company_id)
			->get();

		//aqui mostrqr listado resumen
		$data['summaries'] = $this->clientSummary2($id);


		$condicional = "if((invoices.status = 'paid' or invoices.status = 'Partially_Paid') && , SUM('grand_total'),0)";
		$data['transactions'] = Transaction::where('payer_payee_id', $id)->select('transactions.*', 'chart_of_accounts.name as income_type', 'invoices.grand_total as invoiceGrand', 'invoices.paid as invoicePaid')->where('transactions.payer_payee_id', $id)
			->leftJoin('invoices', 'invoices.id', '=', 'transactions.invoice_id')
			->leftJoin('chart_of_accounts', 'chart_of_accounts.id', '=', 'transactions.chart_id')
			->get();
			
		$data['sales_returns'] = SalesReturn::where("customer_id",$id)
							 	    ->orderBy("id","desc")->get();	
									
			
		//							       		   ->where("company_id", $company_id)
		//            ->get();
		//        $data['transactions'] = DB::table('transactions')
		//->select('transactions.*','chart_of_accounts.name as income_type')->where
		//        ('transactions.payer_payee_id',$id)
		//            ->leftJoin('invoices', 'invoices.id', '=', 'transactions.invoice_id')
		//            ->leftJoin('chart_of_accounts', 'chart_of_accounts.id', '=', 'transactions.chart_id')
		//            ->get();

		//        dd($data['transactions']);
		$data['disponible'] = $this->disponibleCc($id);

		//Summary Data
		//$data['total_project'] = DB::table('projects')->where('client_id',$id)->count();

		$data['invoice_value'] = DB::table('invoices')
			->where('client_id', $id)
			->selectRaw('sum(grand_total) as grand_total, sum(paid) as paid')
			->first();

		$data['invoice_due_amount'] = DB::table('invoices')
			->selectRaw('sum(grand_total) as grand_total, sum(paid) as paid')
			->whereRaw("(Status = 'Unpaid' or Status = 'Partially_Paid')")
			->where('client_id', $id)
			->first();

		$data['id'] = $id;




		if (! $request->ajax()) {
			return view('backend.accounting.contacts.contact.view', $data);
		} else {
			return view('backend.accounting.contacts.contact.modal.view', $data);
		}
	}


	public function cotizacionesConSaldo(Request $request)
	{
		$idClient = $request->id;
		
		$invoices = Invoice::where('client_id', $idClient)
			//->where("company_id", $company_id)
			->get();
		//buscar el saldo y la cotizacion cancelada con saldo a favor
		$result = [];
		foreach ($invoices as $invoice) :


			$paid = 0;
			foreach ($invoice->transaction as $pagos) {
				if ($pagos->type == 'income') {
					$paid = $paid + $pagos->base_amount;
				}
			}
			$html = "";
			$paid_dev = 0;
			$product_return_ = DB::select("select invoices.id,invoices.invoice_number,invoice_items.product_id,products_returns.product_id as productoid, invoice_items.sub_total from `invoices` inner join `invoice_items` on `invoice_items`.`invoice_id` = `invoices`.`id` left join `products_returns` on products_returns.invoice_id=invoices.id and  products_returns.product_id=invoice_items.product_id AND products_returns.status='procesada' WHERE `invoices`.`related_to` = 'contacts' AND invoices.id IN ($invoice->id)
            GROUP BY invoices.id,invoices.invoice_number,invoice_items.product_id");

			if (isset($product_return_)) {
				//$html='Anulado</br>';
				foreach ($product_return_  as $pieza) {
					if (!is_null($pieza->productoid)) {
						$paid_dev = $paid_dev + $pieza->sub_total;
					}
				}

				$paid_to = $invoice->grand_total - ($paid + $paid_dev);
				if ($paid_to < 0) {
					$result[] = [
						'idCotizacion' => $invoice->id,
						'paid_dev' => $paid_to
					];
				}
			}

		endforeach;

		// dd($result);

		// $cotizConSaldo = $result;
		return response()->json(['cotizaciones' => $result]);
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function edit(Request $request, $id)
	{
		$contact = Contact::where("id", $id)->first();
			//->where("company_id", company_id())->first();
		$estadosIva = 	$this->estadosIva;
		if (! $request->ajax()) {
			return view('backend.accounting.contacts.contact.edit', compact('contact', 'id', 'estadosIva'));
		} else {
			return view('backend.accounting.contacts.contact.modal.edit', compact('contact', 'id', 'estadosIva'));
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
		DB::beginTransaction();

		$contact = Contact::where("id", $id)->first();
		//->where("company_id", company_id())->first();

		$validator = Validator::make($request->all(), [
			'profile_type' => 'required|max:20',
			'company_name' => 'nullable|max:50',
			'contact_name' => 'required|max:50',
			// 'contact_email' => [


			//     Rule::unique('contacts')->ignore($contact->id),
			// ],
			'contact_phone' => 'nullable|max:20',
			'country' => 'nullable|max:50',
			'currency' => 'required|max:3',
			'city' => 'nullable|max:50',
			'state' => 'nullable|max:50',
			'zip' => 'nullable|max:20',
			'nombre_env'    => 'nullable|max:60',
			'apellidos_env' => 'nullable|max:60',
			'dni_env'       => 'nullable|max:60',
			'calle_env'     => 'nullable|max:100',
			'numero_env'    => 'nullable|max:30',
			'piso_env'      => 'nullable|max:30',
			'depto_env'     => 'nullable|max:30',
			'cp_env'        => 'nullable|max:30',
			'localidad_env' => 'nullable|max:30',
			'pcia_env'      => 'nullable|max:30',
			'tel_env'       => 'nullable|max:30',
			'contact_image' => 'nullable|image||max:5120',
			'group_id' => 'required',

			//'name' => 'required_if:client_login,on|max:191', //User Login Attribute
			//'email' => [
			//    'required_if:client_login,on',
			//    Rule::unique('users')->ignore($contact->user_id),
			//], //User Login Attribute
			//'password' => 'nullable|max:20|min:6|confirmed', //User Login Attribute
			//'status' => 'required_if:client_login,on', //User Login Attribute
		]);

		if ($validator->fails()) {
			if ($request->ajax()) {
				return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
			} else {
				return redirect()->route('contacts.edit', $id)
					->withErrors($validator)
					->withInput();
			}
		}

		if ($request->hasfile('contact_image')) {
			$file = $request->file('contact_image');
			$contact_image = "contact_image" . time() . '.' . $file->getClientOriginalExtension();
			$file->move(public_path() . "/uploads/contacts/", $contact_image);
		}

		//Check client has already an account
		$other = User::where('email', $request->contact_email)
			->where('user_type', '!=', 'client')->first();

		if ($other) {
			if ($request->ajax()) {
				return response()->json(['result' => 'error', 'message' => 'Sorry, This email already registered with an company or staff !']);
			} else {
				return back()->with('error', _lang('Sorry, This email already registered with an company or staff !'))->withInput();
			}
		}

		$client = User::where('email', $request->contact_email)
			->where('user_type', 'client')->first();

		$contact->profile_type = $request->input('profile_type');
		$contact->company_name = $request->input('company_name');
		$contact->contact_name = $request->input('contact_name');
		$contact->contact_email = $request->input('contact_email');
		$contact->contact_phone = $request->input('contact_phone');
		$contact->dni_cuit = $request->input('vat_id');
		$contact->reg_no = $request->input('reg_no');
		$contact->country = $request->input('country');
		$contact->currency = $request->input('currency');
		$contact->city = $request->input('city');
		$contact->state = $request->input('state');
		$contact->zip = $request->input('zip');
		$contact->estadoIva = $request->input('estadoIva');
		$contact->address = $request->input('address');
		$contact->facebook = $request->input('facebook');
		$contact->twitter = $request->input('twitter');
		$contact->linkedin = $request->input('linkedin');
		$contact->remarks = $request->input('remarks');
		$contact->group_id = $request->input('group_id');
		if ($client) {
			$contact->user_id = $client->id;
		}
		$contact->nombre_env = $request->input('nombre_env');
		$contact->apellidos_env = $request->input('apellidos_env');
		$contact->dni_env = $request->input('dni_env');
		$contact->calle_env = $request->input('calle_env');
		$contact->numero_env = $request->input('numero_env');
		$contact->piso_env = $request->input('piso_env');
		$contact->depto_env = $request->input('depto_env');
		$contact->cp_env = $request->input('cp_env');
		$contact->localidad_env = $request->input('localidad_env');
		$contact->pcia_env = $request->input('pcia_env');
		$contact->tel_env = $request->input('tel_env');
		$contact->company_id = company_id();
		if ($request->hasfile('contact_image')) {
			$contact->contact_image = $contact_image;
		}

		$contact->save();

		DB::commit();

		if (! $request->ajax()) {
			return redirect('contacts')->with('success', _lang('Client information updated sucessfully'));
		} else {
			return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Client information updated sucessfully'), 'data' => $contact]);
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

		$contact = Contact::where("id", $id)->first();
			/*->where("company_id", company_id())
			->first();*/

		/*$user = User::find($contact->user_id);
		if($user){
			$user->delete();
		}*/

		$contact->delete();

		DB::commit();


		return redirect('contacts')->with('success', _lang('Information has been deleted sucessfully'));
	}


	public function get_client_info($id = '')
	{
		$contact = Contact::where("id", $id)->first();
			//->where("company_id", company_id())->first();
		echo json_encode($contact);
	}


	public function send_email(Request $request, $id)
	{
		@ini_set('max_execution_time', 0);
		@set_time_limit(0);
		Overrider::load("Settings");

		$validator = Validator::make($request->all(), [
			'email_subject' => 'required',
			'email_message' => 'required',
		]);

		if ($validator->fails()) {
			if ($request->ajax()) {
				return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
			} else {
				return back()->withErrors($validator)
					->withInput();
			}
		}

		$contact = Contact::where("id", $id)->first();
			//->where("company_id", company_id())->first();

		//Send email
		$subject = $request->input("email_subject");
		$message = $request->input("email_message");

		$mail  = new \stdClass();
		$mail->subject = $subject;
		$mail->body = $message;

		try {
			Mail::to($contact->contact_email)
				->send(new GeneralMail($mail));
		} catch (\Exception $e) {
			if (! $request->ajax()) {
				return back()->with('error', _lang('Sorry, Error Occured !'));
			} else {
				return response()->json(['result' => 'error', 'message' => _lang('Sorry, Error Occured !')]);
			}
		}

		if (! $request->ajax()) {
			return back()->with('success', _lang('Email Send Sucessfully'));
		} else {
			return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Email Send Sucessfully'), 'data' => $contact]);
		}
	}


	public function clientSummary($clientId)
	{

		// Primera consulta: Facturas
		$firstQuery = DB::table('invoices')
			->select(
				'invoices.invoice_date as date',
				DB::raw("concat('Venta-', invoices.invoice_number) as description"),
				'invoices.grand_total as amount',
				DB::raw("'dr' as type"),
				DB::raw("'$' as currency")
			)
			->where('invoices.related_to', 'contacts')
			->where('invoices.related_id', $clientId);

		// Segunda consulta: Transacciones de pago de facturas
		$secondQuery = DB::table('transactions')
			->join('invoices', 'transactions.invoice_id', '=', 'invoices.id')
			->select(
				'transactions.trans_date as date',
				DB::raw("concat('Pago Venta ', invoices.invoice_number) as description"),
				'transactions.amount',
				DB::raw("'cr' as type"),
				DB::raw("'$' as currency")
			)
			->where('invoices.related_to', 'contacts')
			->where('invoices.related_id', $clientId);

		// Tercera consulta: Devoluciones con tipo 'cc'
		
		// Cuarta consulta: Reservas
	/*	$devolucionesQuery = DB::table('sales_return')
			->select(
				'sales_return.return_date as date',
				DB::raw("concat('Devolucion-', sales_return.id) as description"),
				'sales_return.grand_total as amount',
				DB::raw("'dr' as type"),
				DB::raw("'$' as currency")
			)
			->where('quotations.related_to', 'contacts')
			->where('quotations.related_id', $clientId);
	*/	
		$thirdQuery = DB::table('transactions')
			->select(
				'transactions.trans_date as date',
				DB::raw("'DEV CC' as description"),
				'transactions.amount',
				DB::raw("'cr' as type"),
				DB::raw("'$' as currency")
			)
			->whereNull('transactions.invoice_id')
			->where('transactions.type', 'cc')
			->where('transactions.payer_payee_id', $clientId);

		// Cuarta consulta: Reservas
		$fourthQuery = DB::table('quotations')
			->select(
				'quotations.quotation_date as date',
				DB::raw("concat('Reserva-', quotations.quotation_number) as description"),
				'quotations.grand_total as amount',
				DB::raw("'dr' as type"),
				DB::raw("'$' as currency")
			)
			->where('quotations.related_to', 'contacts')
			->where('quotations.related_id', $clientId);

		// Quinta consulta: Pagos de reservas
		$fifthQuery = DB::table('transactions')
			->join('quotations', 'transactions.id_quotation', '=', 'quotations.id')
			->select(
				'transactions.trans_date as date',
				DB::raw("concat('Pago Reserva', quotations.quotation_number) as description"),
				'transactions.amount',
				DB::raw("'cr' as type"),
				DB::raw("'$' as currency")
			)
			->where('quotations.related_to', 'contacts')
			->where('quotations.related_id', $clientId);

		// Combinar todas las consultas con `union`
		$combinedQuery = $firstQuery
			->union($secondQuery)
			//->union($devolucionesQuery)
			->union($thirdQuery)
			->union($fourthQuery)
			->union($fifthQuery);

		// Aplicar el ordenamiento al resultado final
		$data = DB::table(DB::raw("({$combinedQuery->toSql()}) as sub"))
			->mergeBindings($combinedQuery)
			->orderBy('date', 'asc') // Cambia 'asc' a 'desc' según necesites
			->get();

		return $data;
	}

	public function ajusteCuenta(Request $request, $id)
	{
		if ($request->ajax()) {
			$contact = Contact::where('id', $id)->first();
			return view('backend.accounting.contacts.contact.modal.ajustes_cuenta', ['id' => $id,'company_id' => $contact->company_id]);
		}
	}


	public function clientSummary2($clientId)
	{
		$ventas = DB::table('invoices AS i')
			->select(
				'i.id',
				DB::raw("CASE WHEN i.company_id = 1 THEN CONCAT('PM-', i.invoice_number) 
                      WHEN i.company_id = 2 THEN CONCAT('PC-', i.invoice_number) 
                      ELSE i.invoice_number END AS number"),
				'i.invoice_date as date',
				DB::raw("'Cotización' AS tipo"),
				'i.grand_total',
				'i.status',
				DB::raw('COALESCE(SUM(pagos.amount), 0) AS cobrado'),
				DB::raw('i.grand_total - COALESCE(SUM(pagos.amount), 0) AS saldo')
			)
			->leftJoin('transactions AS pagos', function ($join) {
				$join->on('pagos.invoice_id', '=', 'i.id')
					->where('pagos.type', '=', 'income');
			})
			->where('i.related_id', '=', $clientId)
			->groupBy('i.id', 'i.invoice_number', 'i.invoice_date', 'i.grand_total', 'i.status')
			->get();

		$ajusteSaldoInicial = DB::table('transactions AS i')
			->select(
				'i.id',
				'i.id as number',
				'i.trans_date AS date',
				DB::raw("'Carga Inicial' AS tipo"),
				'i.amount AS grand_total',
				'i.status',
				DB::raw('COALESCE(SUM(pagos.amount), 0) AS cobrado'),
				DB::raw('i.amount - COALESCE(SUM(pagos.amount), 0) AS saldo')
			)
			->leftJoin('transactions AS pagos', function ($join) {
				$join->on('pagos.trans_asoc', '=', 'i.id')
					->where('pagos.type', '=', 'income');
			})
			->where('i.type', '=', 'cc')
			->where('i.dr_cr', '=', 'si')
			->where('i.payer_payee_id', '=', $clientId)
			->groupBy('i.id', 'i.trans_date', 'i.amount', 'i.status')
			->get();


		$conciliacion = DB::table('transactions AS i')
			->select(
				'i.id',
				'i.id  as number',
				'i.trans_date AS date',
				DB::raw("'Conciliación' AS tipo"),
				'i.amount AS grand_total',
				'i.status',
				DB::raw('COALESCE(SUM(pagos.amount), 0) AS cobrado'),
				DB::raw('i.amount - COALESCE(SUM(pagos.amount), 0) AS saldo')
			)
			->leftJoin('transactions AS pagos', function ($join) {
				$join->on('pagos.trans_asoc', '=', 'i.id')
					->where('pagos.type', '=', 'income');
			})
			->where('i.type', '=', 'cc')
			->where('i.dr_cr', '=', 'co')
			->where('i.payer_payee_id', '=', $clientId)
			->groupBy('i.id', 'i.trans_date', 'i.amount', 'i.status')
			->get();

		$reservas = DB::table('quotations AS i')
			->select(
				'i.id',
				'i.quotation_number AS number',
				'i.quotation_date AS date',
				DB::raw("'Reserva' AS tipo"),
				'i.grand_total',
				'i.status',
				DB::raw('COALESCE(SUM(pagos.amount), 0) AS cobrado'),
				DB::raw('i.grand_total - COALESCE(SUM(pagos.amount), 0) AS saldo')
			)
			->leftJoin('transactions AS pagos', function ($join) {
				$join->on('pagos.id_quotation', '=', 'i.id')
					->where('pagos.type', '=', 'income');
			})
			->where('i.related_id', '=', $clientId)
			->groupBy('i.id', 'i.quotation_number', 'i.quotation_date', 'i.grand_total', 'i.status')
			->get();
			
		$devoluciones = DB::table('sales_return AS i')
			->select(
				'i.id',
				'i.note AS note',
				'i.return_date AS date',
				DB::raw("'Devolucion' AS tipo"),
				'i.grand_total',
//				'i.status',
				DB::raw('COALESCE(SUM(pagos.amount), 0) AS cobrado'),
				DB::raw('i.grand_total - COALESCE(SUM(pagos.amount), 0) AS saldo')
			)
			->leftJoin('transactions AS pagos', function ($join) {
				$join->on('pagos.id_quotation', '=', 'i.id')
					->where('pagos.type', '=', 'sales_return');
			})
			->where('i.customer_id', '=', $clientId)
			->groupBy('i.id',  'i.note', 'i.grand_total', 'i.return_date')
			->get();	

		$saldos = $ventas->concat($ajusteSaldoInicial)
			->concat($conciliacion)
			->concat($reservas);



		return $saldos;
	}
	
	
	public function movimiento_saldo(Request $request)
	{
			if ($request->ajax()) {
			$currency = currency();	
			$sql = saldo_sql_list();
			$sql .= "select t1.number, t1.referencia,t1.clientesid,t1.date,t1.note,t1.movimiento, t1.debe, t1.haber,t1.status,t1.tipo,
						  @acumulador:=(debe - abs(haber) + @acumulador) as saldo, SUM(debe - abs(haber)) OVER () AS gran_total_general
			from cte_second t1,(select @acumulador := 0) as init_var
			WHERE t1.clientesid=? and t1.tipo=?
			order by t1.date";
			$data = DB::select($sql, [$request->id,'ajuste_saldo']);
            return Datatables::of($data)
                    ->addIndexColumn()
					->editColumn('date', function ($data) {
						$date_format = get_company_option('date_format', 'd/m/Y');
						return date($date_format, strtotime($data->date));
                    })
					->editColumn('note', function ($data) {
						return $data->note ?? '';
                    })
					->editColumn('debe', function ($data)  use ($currency) {
						return "<span class='float-right'>" . decimalPlace($data->debe ?? 0) . "</span>";
                    })
					->editColumn('haber', function ($data)  use ($currency) {
						return "<span class='float-right'>" . decimalPlace($data->haber ?? 0) . "</span>";
                    })
					->editColumn('saldo', function ($data)  use ($currency) {
								$saldo = $data->saldo ?? 0;
								$clase = $saldo >= 0 ? "text-success" : "text-danger";			
						return "<span class='float-right $clase'>" . decimalPlace($data->saldo ?? 0) . "</span>";
                    })
					->addColumn('action', function($data){
						
										return '<div class="dropdown">
                                                    <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                                        data-toggle="dropdown">'. _lang('Action') .'
                                                        <i class="fa fa-angle-down"></i></button>
                                                    <ul class="dropdown-menu">
                                                        <a class="dropdown-item ajax-modal"
                                                            href="'. url('contacts/view_payment/' . $data->number) .'"
                                                            data-title="'. _lang('View') .'"
                                                            data-fullscreen="true"><i class="fas fa-credit-card"></i>
                                                            '. _lang('View') .'</a>
                                                    </ul>
                                                </div>';
                    })
					->with('saldo_actual', function() use ($data) {
							return $data[0]->gran_total_general ?? 0;
					})
                    ->rawColumns(['action', 'debe','haber','saldo'])
                    ->make(true);
					/*->with('total', function() use ($data) {
							return $data->sum('saldo');
					});*/
        }	
	}
	
	
	public function mov_devolucion_saldo(Request $request)
	{
			if ($request->ajax()) {
			$currency = currency();	
			$sql = saldo_sql_list();
			$sql .= "select t1.number, t1.referencia,t1.clientesid,t1.date,t1.note,t1.movimiento, t1.debe, t1.haber,t1.status,t1.tipo,
						  @acumulador:=(debe - abs(haber) + @acumulador) as saldo, SUM(debe - abs(haber)) OVER () AS gran_total_general, t1.adicional,t1.documento_id
			from cte_second t1,(select @acumulador := 0) as init_var
			WHERE t1.clientesid=? and t1.tipo=?
			order by t1.date";
			$data = DB::select($sql, [$request->id,'sales_return']);
			
			//dd($data,$request->id);
			
		/*$product_returns = ProductReturn::select('invoice_id','status')
		->where('status','pendiente')->whereIn("company_id",$company_id)->groupBy('invoice_id');
		
		$sales_returns = SalesReturn::select('sales_return.*', 't1.status')
		->leftJoinSub($product_returns, 't1', function ($join) {
				$join->on('sales_return.invoice_id', '=', 't1.invoice_id');
		})->orderBy("id","desc")->get();		*/							
			
			
            return Datatables::of($data)
                    ->addIndexColumn()
					->editColumn('date', function ($data) {
						$date_format = get_company_option('date_format', 'd/m/Y');
						return date($date_format, strtotime($data->date));
                    })
					->editColumn('note', function ($data) {
						return ($data->note ?? '') . ('</br>'.$data->adicional ?? '');
                    })
					->editColumn('debe', function ($data)  use ($currency) {
						return "<span class='float-right'>" . decimalPlace($data->debe ?? 0) . "</span>";
                    })
					->editColumn('haber', function ($data)  use ($currency) {
						return "<span class='float-right'>" . decimalPlace($data->haber ?? 0) . "</span>";
                    })
					->editColumn('saldo', function ($data)  use ($currency) {
						
						return "<span class='float-right'>" . decimalPlace($data->saldo ?? 0) . "</span>";
                    })
					->addColumn('action', function($data){
						
										return '<div class="dropdown">
                                                    <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                                        data-toggle="dropdown">'. _lang('Action') .'
                                                        <i class="fa fa-angle-down"></i></button>
                                                    <ul class="dropdown-menu">
                                                        <a class="dropdown-item ajax-modal"
                                                            href="'. url('contacts/view_payment/' . $data->number) .'"
                                                            data-title="'. _lang('View') .'"
                                                            data-fullscreen="true"><i class="fas fa-credit-card"></i>
                                                            '. _lang('View') .'</a>
                                                    </ul>
                                                </div>';
						
						/*return '<div class="dropdown">
                                                    <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                                        data-toggle="dropdown">'. _lang('Action') .'
                                                        <i class="fa fa-angle-down"></i></button>
                                                    <ul class="dropdown-menu">
                                                        <form action="' . action('ContactController@destroy', $data->clientesid) .'"
                                                            method="post">'.csrf_field() .'
                                                            <button class="button-link btn-remove" type="submit"><i
                                                                    class="fas fa-trash-alt"></i>'. _lang('Delete') .'</button>
                                                        </form>
                                                    </ul>
											</div>';
						
						return '<form action="' . action('ContactController@destroy', $data->clientesid) . '" class="text-center" method="post">'
					. '<a class="btn btn-success btn-xs ajax-modal" data-title="Ajustes en cuenta"
					href="' . action('ContactController@ajusteCuenta', $data->clientesid) . '">A</a>'
					. '</form>';*/
                    })
					->with('saldo_actual', function() use ($data) {
							return $data[0]->gran_total_general ?? 0;
					})
                    ->rawColumns(['action','note', 'debe','haber','saldo'])
                    ->make(true);
					/*->with('total', function() use ($data) {
							return $data->sum('saldo');
					});*/
        }	
	}
	
	
	public function mov_resumen_saldo(Request $request)
	{
			if ($request->ajax()) {
			$currency = currency();	
			$sql = saldo_sql_list();
			$sql .= "select t1.number, t1.referencia,t1.clientesid,t1.date,t1.note,t1.movimiento, t1.debe, t1.haber,t1.status,t1.tipo,
						  @acumulador:=(debe - abs(haber) + @acumulador) as saldo, SUM(debe - abs(haber)) OVER () AS gran_total_general, t1.adicional,t1.documento_id
			from cte_second t1,(select @acumulador := 0) as init_var
			WHERE t1.clientesid=? and t1.tipo IN ('sales_return','invoices','retiros','ajuste_saldo') 
			order by t1.date desc";
			$data = DB::select($sql, [$request->id]);
			
			//dd($data,$request->id);
			
		/*$product_returns = ProductReturn::select('invoice_id','status')
		->where('status','pendiente')->whereIn("company_id",$company_id)->groupBy('invoice_id');
		
		$sales_returns = SalesReturn::select('sales_return.*', 't1.status')
		->leftJoinSub($product_returns, 't1', function ($join) {
				$join->on('sales_return.invoice_id', '=', 't1.invoice_id');
		})->orderBy("id","desc")->get();		*/							
			
			
            return Datatables::of($data)
                    ->addIndexColumn()
					->editColumn('date', function ($data) {
						$date_format = get_company_option('date_format', 'd/m/Y');
						return date($date_format, strtotime($data->date));
                    })
					->editColumn('note', function ($data) {
						return ($data->note ?? '') . ('</br>'.$data->adicional ?? '');
                    })
					->editColumn('debe', function ($data)  use ($currency) {
						return "<span class='float-right'>" . decimalPlace($data->debe ?? 0) . "</span>";
                    })
					->editColumn('haber', function ($data)  use ($currency) {
						return "<span class='float-right'>" . decimalPlace($data->haber ?? 0) . "</span>";
                    })
					->editColumn('saldo', function ($data)  use ($currency) {
						$saldo = $data->saldo ?? 0;
						$clase = $saldo >= 0 ? "text-success" : "text-danger";			
						return "<span class='float-right $clase'>" . decimalPlace($data->saldo ?? 0) . "</span>";
						//return "<span class='float-right'>" . decimalPlace($data->saldo ?? 0) . "</span>";
                    })
					->addColumn('action', function($data){
						
										return '<div class="dropdown">
                                                    <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                                        data-toggle="dropdown">'. _lang('Action') .'
                                                        <i class="fa fa-angle-down"></i></button>
                                                    <ul class="dropdown-menu">
                                                        <a class="dropdown-item ajax-modal"
                                                            href="'. url('contacts/view_payment/' . $data->number) .'"
                                                            data-title="'. _lang('View') .'"
                                                            data-fullscreen="true"><i class="fas fa-credit-card"></i>
                                                            '. _lang('View') .'</a>
                                                    </ul>
                                                </div>';
						
						/*return '<div class="dropdown">
                                                    <button class="btn btn-primary btn-xs dropdown-toggle" type="button"
                                                        data-toggle="dropdown">'. _lang('Action') .'
                                                        <i class="fa fa-angle-down"></i></button>
                                                    <ul class="dropdown-menu">
                                                        <form action="' . action('ContactController@destroy', $data->clientesid) .'"
                                                            method="post">'.csrf_field() .'
                                                            <button class="button-link btn-remove" type="submit"><i
                                                                    class="fas fa-trash-alt"></i>'. _lang('Delete') .'</button>
                                                        </form>
                                                    </ul>
											</div>';
						
						return '<form action="' . action('ContactController@destroy', $data->clientesid) . '" class="text-center" method="post">'
					. '<a class="btn btn-success btn-xs ajax-modal" data-title="Ajustes en cuenta"
					href="' . action('ContactController@ajusteCuenta', $data->clientesid) . '">A</a>'
					. '</form>';*/
                    })
					->with('saldo_actual', function() use ($data) {
							return $data[0]->gran_total_general ?? 0;
					})
                    ->rawColumns(['action','note', 'debe','haber','saldo'])
                    ->make(true);
					/*->with('total', function() use ($data) {
							return $data->sum('saldo');
					});*/
        }	
	}
	
	
	 public function create_payment(Request $request, $id)
    {
		
			/*$sql = saldo_sql_list();
			$sql .= "select t1.number, t1.referencia,t1.clientesid,t1.date,t1.note,t1.movimiento, t1.debe, t1.haber,t1.status,t1.tipo,
						  @acumulador:=(debe - abs(haber) + @acumulador) as saldo, SUM(debe - abs(haber)) OVER () AS gran_total_general
			from cte_second t1,(select @acumulador := 0) as init_var
			WHERE t1.clientesid=? and t1.tipo=?
			order by t1.date limit 1";
			$data = DB::select($sql, [$id,'ajuste_saldo']);*/
			
			$sql = saldo_sql_list();
			$sql .= "select t1.number, t1.referencia,t1.clientesid,t1.date,t1.note,t1.movimiento, t1.debe, t1.haber,t1.status,t1.tipo,
						  @acumulador:=(debe - abs(haber) + @acumulador) as saldo, SUM(debe - abs(haber)) OVER () AS gran_total_general, t1.adicional,t1.documento_id
			from cte_second t1,(select @acumulador := 0) as init_var
			WHERE t1.clientesid=? and t1.tipo IN ('sales_return','invoices','retiros','ajuste_saldo') 
			order by t1.date";
			$data = DB::select($sql, [$request->id]);
			
			
			$contact = Contact::where('id', $id)->first();
			
			$grand_total=abs($data[0]->gran_total_general ?? 0);
			
	
        /*$invoice = Invoice::where("id", 4755)->first();


        $invoices = Invoice::where('client_id', $invoice->client_id)->get();
        //buscar el saldo y la cotizacion cancelada con saldo a favor
        $result = [];
        foreach ($invoices as $invoice) :


            $paid = 0;
            foreach ($invoice->transaction as $pagos) {
                if ($pagos->type == 'income') {
                    $paid = $paid + $pagos->base_amount;
                }
            }
            $html = "";
            $paid_dev = 0;
            $product_return_ = DB::select("select invoices.id,invoices.invoice_number,invoice_items.product_id,products_returns.product_id as productoid, invoice_items.sub_total from `invoices` inner join `invoice_items` on `invoice_items`.`invoice_id` = `invoices`.`id` left join `products_returns` on products_returns.invoice_id=invoices.id and  products_returns.product_id=invoice_items.product_id AND products_returns.status='procesada' WHERE `invoices`.`related_to` = 'contacts' AND invoices.id IN ($invoice->id)
            GROUP BY invoices.id,invoices.invoice_number,invoice_items.product_id");

            if (isset($product_return_)) {
                //$html='Anulado</br>';
                foreach ($product_return_  as $pieza) {
                    if (!is_null($pieza->productoid)) {
                        $paid_dev = $paid_dev + $pieza->sub_total;
                    }
                }

                $paid_to = $invoice->grand_total - ($paid + $paid_dev);
                if ($paid_to < 0) {
                    $result[] = [
                        'idCotizacion' => $invoice->id,
                        'paid_dev' => $paid_to
                    ];
                }
            }

        endforeach;
*/

        if ($request->ajax()) {
			$invoice=null;
			$result=null;
            return view('backend.accounting.contacts.contact.modal.create_payment', compact('invoice', 'id', 'result','contact','grand_total'));
        }
    }

    public function store_payment(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|lte:pending_amount',
            'payment_method_id' => 'required',
            'reference' => 'nullable|max:50',
            'attachment' => 'nullable|mimes:jpeg,png,jpg,doc,pdf,docx,zip',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            }
        }

        $attachment = "";
        if ($request->hasfile('attachment')) {
            $file = $request->file('attachment');
            $attachment = time() . $file->getClientOriginalName();
            $file->move(public_path() . "/uploads/transactions/", $attachment);
        }

        DB::beginTransaction();
		$type = 'co-';
		// se descuenta el valor a favor del cliente
		$amount =  $request->input('amount') * -1;

		/*$transaction = new Transaction();
		$transaction->trans_date = date('Y-m-d');
		$transaction->type = 'cc';
		$transaction->dr_cr =  $type;
		$transaction->amount = $amount;
		$transaction->account_id = $request->input('account_id');
		//$transaction->chart_id = $request->input('chart_id');
		$transaction->payer_payee_id = $request->input('payer_payee_id');
		$transaction->payment_method_id = $request->input('payment_method_id');
		$transaction->reference = $request->input('reference');
		$transaction->razon_social = $request->input('razon_social');
		//$transaction->tipo_comprobante_id = $request->input('tipo_comprobante_id');
		$transaction->imputar_a = $request->input('imputar_a');
		//$transaction->detalle_rubro = $request->input('detalle_rubro');
		$transaction->banco = $request->input('banco');
		$transaction->cheque_nro = $request->input('cheque_nro');
		$transaction->cheque_vencimiento = $request->input('cheque_vencimiento');
		//$transaction->cheque_entregado_a = $request->input('cheque_entregado_a');
		$transaction->note = $request->input('note');
		//$transaction->attachment = $attachment;
		$transaction->company_id = $request->input('company_id');
		$transaction->save();*/
		
		
		// metodo para devolver dinero
		
		$rubro = ChartOfAccount::where('name', '=', 'VUELTO')->first();
		
		$transaction1 = new Transaction();
		$transaction1->trans_date = date('Y-m-d');
		$transaction1->account_id = $request->input('account_id');
		$transaction1->chart_id = $rubro->id ?? 0;
		$transaction1->type = 'expense';
		$transaction1->dr_cr = 'dr';
		$transaction1->amount = $request->input('amount');
		$transaction1->base_amount = convert_currency($transaction1->account->account_currency, base_currency(), $transaction1->amount);
		$transaction1->payer_payee_id = $request->input('payer_payee_id');

		$transaction1->payment_method_id = $request->input('payment_method_id');
		$transaction1->reference = $request->input('reference');
		$transaction1->razon_social = $request->input('razon_social');
		$transaction1->tipo_comprobante_id = $request->input('tipo_comprobante_id');
		$transaction1->imputar_a = $request->input('imputar_a');
		$transaction1->detalle_rubro = "Se reintegra dinero disponible";
		$transaction1->banco = $request->input('banco');
		$transaction1->cheque_nro = $request->input('cheque_nro');
		$transaction1->cheque_vencimiento = $request->input('cheque_vencimiento');
		//$transaction->cheque_entregado_a = $request->input('cheque_entregado_a');
		$transaction1->note = ($request->input('note') ?? '') . '</br> Egreso por devolución a cliente - Saldo a favor: ' . $amount;
		//$transaction->note = 'Egreso por devolución a cliente - Saldo a favor: ' . ($esUsd ? 'USD ' . $monto : '$ ' . $monto);
		$transaction1->attachment = $attachment;
		$transaction1->usd = $request->input('usd');
		$transaction1->tasa = $request->input('tasa');
		//$transaction1->trans_asoc = $transaction->id;
		$transaction1->company_id = $request->input('company_id');
		//$transaction1->status = $request->input('status', null);
		$transaction1->save();
		
			/*$transaction->tipo_comprobante_id = $request->input('tipo_comprobante_id');
			$transaction->imputar_a = $request->input('imputar_a');
			$transaction->detalle_rubro = $request->input('detalle_rubro');
			$imputarA = $request->input('imputar_a');*/
		

        DB::commit();

        if ($request->ajax()) {
            $request->session()->flash('success', _lang('Payment was made Sucessfully'));
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Payment was made Sucessfully'), 'data' => $transaction1]);
        }
    }

    public function view_payment(Request $request, $id)
    {

        $transactions = Transaction::where("id", $id)
            ->get(); 
		
        if ($request->ajax()) {
            return view('backend.accounting.contacts.contact.modal.view_payment', compact('transactions'));
        }
    }
	
	
}
