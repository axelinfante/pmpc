<?php

namespace App\Http\Controllers;

use App\ChartOfAccount;
use App\Company;
use App\Pagos_car;
use App\PaymentMethod;
use App\Purchase;
use App\Transaction;
use App\CuentaCorriente;
use App\User;
use App\Cars;
use App\Invoice;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use Illuminate\Support\Facades\Notification;
use App\Notifications\PagosCarChangePriority;
use App\Notifications\PagosCarChangeStatus;
use App\TransactionRetiroCotizacion;

class ExpenseController extends Controller
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
	public function index()
	{
		$compan_id = company_id();
		//$month     = date('m');
		//$year      = date('Y');

		$monthly_expense = Transaction::selectRaw("IFNULL(SUM(base_amount),0) as total")
			->where("dr_cr", "dr")
			->where("status", 1)
			->where("company_id", $compan_id)
			//->whereMonth("trans_date", $month)
			//->whereYear("trans_date", $year)
			->first()->total;
		$data = array('total' => $monthly_expense);
		return view('backend.accounting.expense.list', $data);
	}

	public function get_table_data(Request $request)
	{


		$currency = currency();

		$transactions = Transaction::with("account")->with("expense_type")
			->with("payment_method")
			// ->with("payment_method")
			->with("tipo_comprobante")
			->select('transactions.*')

			->where(function ($e) {
				$e->where('transactions.company_id', company_id())
					->orWhere('transactions.company_id', 3)
					->orWhere('transactions.company_id', 4)
					->orWhere('transactions.company_id', 5);
			})
			->where("transactions.dr_cr", "dr")
			->orderBy("transactions.trans_date", "desc");
		if (isset($request->idCar)) {
			$transactions->whereHas('pagos_car', function ($strg) use ($request) {
				$strg->where('id_car', $request->idCar);
			});
		}
		if ($request->verVehiculo == 'true') {
			$transactions->whereHas('pagos_car', function ($strg) use ($request) {
				$strg->where('id_car', '>', 0);
			});
		}

		return Datatables::eloquent($transactions)
			->filterColumn('trans_date', function ($query, $keyword) {
				//fecha en formato Y-m-d
				$fecha = date('Y-m-d', strtotime($keyword));
				// dd($fecha);
				$query->where('trans_date', 'like', '%' . $fecha . '%');
			})



			->filterColumn('payer.name', function ($query, $keyword) {

				$query->whereHas('payer', function ($q) use ($keyword) {
					$q->where('name', 'like', '%' . $keyword . '%');
				});
			})
			->filterColumn('status', function ($query, $keyword) {
				if ($keyword == 2) {
					//resuelto
					$query->where('status', 1);
				} else {
					//pendiente
					$query->where('status', 0)->orwherenull('status');
				}
			})
			->filterColumn('pagos_car', function ($query, $keyword) {

				$query->whereHas('pagos_car', function ($q) use ($keyword) {
					$q->where('id_car', 'like', '%' . $keyword . '%');
				});
			})
			->filterColumn('dominio', function ($query, $keyword) {

				$query->whereHas('pagos_car', function ($q) use ($keyword) {
					$q->whereHas('vehiculo', function ($q) use ($keyword) {
						$q->where('dominio', 'like', '%' . $keyword . '%');
					});
				});
			})
			->filterColumn('payment_priority', function ($query, $keyword) {


				if ($keyword == '-1') {
					$query->whereNull('payment_priority');
				} else {
					$query->where('payment_priority', $keyword);
				}
			})
			->editColumn('trans_date', function ($trans) {
				$date_format = get_company_option('date_format', 'Y-m-d');
				return date($date_format, strtotime($trans->trans_date));
			})

			->editColumn('trans_date', function ($trans) {
				$date_format = get_company_option('date_format', 'Y-m-d');
				return date($date_format, strtotime($trans->trans_date));
			})
			->editColumn('amount', function ($trans) use ($currency) {
				$acc_currency = currency($trans->account->account_currency ?? '');
				if ($acc_currency != $currency) {
					return "<span class='float-right'>" . decimalPlace($trans->amount, currency($trans->account->account_currency ?? '')) . "</span><br>
										<span class='float-right'><b>" . decimalPlace($trans->base_amount, $currency) . "</b></span>";
				} else {
					return "<span class='float-right'>" . decimalPlace($trans->amount, currency($trans->account->account_currency ?? '')) . "</span>";
				}
			})
			->editColumn('payee.contact_name', function ($trans) {
				return isset($trans->payee->contact_name) ? $trans->payee->contact_name : '';
			})
			->editColumn('pagos_car', function ($trans) {
				if ($trans->pagos_car->vehiculo->company_id == 1) {
				}
				$result = '';
				if ($trans->pagos_car->id_car) {
					$in = '';
					if ($trans->pagos_car->vehiculo->company_id == 1) {
						$in = 'PM-';
					} else if ($trans->pagos_car->vehiculo->company_id == 2) {
						$in = 'PC-';
					}
					$result = $in . $trans->pagos_car->id_car;
				}

				return $result;
			})
			->addColumn('dominio', function ($trans) {
				return $trans->pagos_car->vehiculo->dominio ?? '';
			})
			->editColumn('expense_type.name', function ($trans) {
				return isset($trans->expense_type->name) ? $trans->expense_type->name : _lang('Transfer');
			})
			->editColumn('payer.name', function ($trans) {
				return isset($trans->payer->name) ? $trans->payer->name : '';
			})
			->editColumn('tipo_comprobante.descripcion', function ($trans) {
				return isset($trans->tipo_comprobante->descripcion) ? $trans->tipo_comprobante->descripcion : '';
			})
			->editColumn('account.account_title', function ($trans) {
				return $trans->account->account_title;
			})

			->editColumn('payment_method.name', function ($trans) {
				return $trans->payment_method->name;
			})

			->editColumn('tasa', function ($trans) {
				return $trans->tasa;
			})
			->editColumn('status', function ($trans) {
				return $trans->status == 1 ? 'Resuelto' : 'pendiente';
			})
			->editColumn('payment_priority', function ($trans) {
				return $trans->payment_priority == null ? 'Normal' : ucwords(str_replace('_', ' ', $trans->payment_priority));
			})
			->addColumn('action', function ($trans) {

				$updateButton = '';
				if ($trans->status == 1 && ($trans->pagos_car->id_car ?? '') != '') { //evita editar el gasto de vehiculo resuelto
					$updateButton = '<a href="#" data-title="' . _lang('Update Expense') . '" class="btn btn-warning btn-xs ajax-modal disabled"><i class="ti-pencil"></i></a>&nbsp;';
				} else {
					$updateButton = '<a href="' . action('ExpenseController@edit', $trans['id']) . '" data-title="' . _lang('Update Expense') . '" class="btn btn-warning btn-xs ajax-modal"><i class="ti-pencil"></i></a>&nbsp;';
				}

				if (isset($trans->expense_type->name) || true) {
					return '<form action="' . action('ExpenseController@destroy', $trans['id']) . '" class="text-center" method="post">'
						//. '<a href="' . action('ExpenseController@edit', $trans['id']) . '" data-title="' . _lang('Update Expense') . '" class="btn btn-warning btn-xs ajax-modal"><i class="ti-pencil"></i></a>&nbsp;'
						. $updateButton
						. '<a href="' . action('ExpenseController@show', $trans['id']) . '" data-title="' . _lang('View Expense') . '" class="btn btn-primary btn-xs ajax-modal"><i class="ti-eye"></i></a>&nbsp;'
						. csrf_field()
						. '<input name="_method" type="hidden" value="DELETE">'
						. '<button class="btn btn-danger btn-xs btn-remove" type="submit"><i class="ti-eraser"></i></button>'
						. '</form>';
				} else {
					return '<form action="' . action('ExpenseController@destroy', $trans['id']) . '" class="text-center" method="post">'
						. '<a href="#" data-title="' . _lang('Update Expense') . '" class="btn btn-warning btn-xs ajax-modal disabled"><i class="ti-pencil"></i></a>&nbsp;'
						. '<a href="' . action('ExpenseController@show', $trans['id']) . '" data-title="' . _lang('View Expense') . '" class="btn btn-primary btn-xs ajax-modal"><i class="ti-eye"></i></a>&nbsp;'
						. csrf_field()
						. '<input name="_method" type="hidden" value="DELETE">'
						. '<button class="btn btn-danger btn-xs btn-remove" type="submit"><i class="ti-eraser"></i></button>'
						. '</form>';
				}
			})
			->setRowId(function ($trans) {
				return "row_" . $trans->id;
			})
			->rawColumns(['status', 'action', 'amount'])
			->make(true);
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create(Request $request)
	{
		if (!$request->ajax()) {
			return view('backend.accounting.expense.create');
		} else {
			return view('backend.accounting.expense.modal.create');
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
			'trans_date' => 'required',
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
				return redirect('expense/create')
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

		$rubro = ChartOfAccount::find($request->input('chart_id'));
		DB::beginTransaction();




		if ($rubro->name == 'Devolución') {


			$validator = Validator::make($request->all(), [

				'client_id' => 'required',
				'idCotizacionSaldo' => 'required'

			]);

			if ($validator->fails()) {
				if ($request->ajax()) {
					return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
				} else {
					return redirect('expense/create')
						->withErrors($validator)
						->withInput();
				}
			}


			// aqui la funcion para lo de pago desde la cotizacion
			$resultCo = [];
			//si el metodo es igual a pago desde cotizacion
			// if ($request->input('payment_method_id') == 11) {
			//sacar el id de la cotizacion 0 = id cotizacion 1 =  valor
			// $arr = explode('-', $request->input('idCotizacionSaldo'));

			$invoiceC =  Invoice::where("id", $request->input('idCotizacionSaldo'))->first();
			$paid = 0;
			// $arrTransactions = [];
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

			if (!empty($resultCo)) {
				$montoNuevoUs = $resultCo['paid_dev'];
				if ($request->input('amount') >  $resultCo['paid_dev']) {
					$nuevoMonto = $resultCo['paid_dev'];
				} else {
					$nuevoMonto = $request->input('amount');
				}
			} else {
				$nuevoMonto = $request->input('amount');
			}

			$methodP = PaymentMethod::where('name', 'like', '%Gasto cc')->first();


			$transactionDevolucion = new Transaction();
			$transactionDevolucion->trans_date = $request->input('trans_date');
			$transactionDevolucion->account_id = $request->input('account_id');
			$transactionDevolucion->chart_id = $request->input('chart_id');
			$transactionDevolucion->type = 'cc_expense';
			$transactionDevolucion->dr_cr = 'cc';
			$transactionDevolucion->amount = $nuevoMonto;
			$transactionDevolucion->base_amount = convert_currency($transactionDevolucion->account->account_currency, base_currency(), $transactionDevolucion->amount);

			$transactionDevolucion->payer_payee_id = $request->input('client_id');

			$transactionDevolucion->payment_method_id = $methodP->id;
			$transactionDevolucion->reference = $request->input('reference');
			$transactionDevolucion->razon_social = $request->input('razon_social');
			$transactionDevolucion->tipo_comprobante_id = $request->input('tipo_comprobante_id');
			$transactionDevolucion->imputar_a = $request->input('imputar_a');
			$transactionDevolucion->detalle_rubro = $request->input('detalle_rubro');
			$transactionDevolucion->banco = $request->input('banco');
			$transactionDevolucion->cheque_nro = $request->input('cheque_nro');
			$transactionDevolucion->cheque_vencimiento = $request->input('cheque_vencimiento');
			$transactionDevolucion->cheque_entregado_a = $request->input('cheque_entregado_a');
			$transactionDevolucion->note = $request->input('note');
			$transactionDevolucion->attachment = $attachment;
			$transactionDevolucion->usd = $request->input('usd');
			$transactionDevolucion->tasa = $request->input('tasa');

			$transactionDevolucion->status = $request->input('status', null);

			$imputarA = $request->input('imputar_a');

			$companyNames = [
				'distribuir' => 'A dividir',
				'triunvirato' => 'Triunvirato',
				'pentacar' => 'Pentacar',
				'paternal' => 'Paternal',
				'g.u.t.' => 'Gut',
			];

			if (isset($companyNames[$imputarA])) {
				// Solo se ejecuta UNA consulta si el valor existe
				$company = Company::where('business_name', $companyNames[$imputarA])->first();
				if ($company) {
					$transactionDevolucion->company_id = $company->id;
				}
			}



			$transactionDevolucion->save();

			$transaction = new Transaction();
			$transaction->trans_date = $request->input('trans_date');
			$transaction->account_id = $request->input('account_id');
			$transaction->chart_id = $request->input('chart_id');
			$transaction->type = 'expense';
			$transaction->dr_cr = 'dr';
			$transaction->amount = $nuevoMonto;
			$transaction->base_amount = convert_currency($transaction->account->account_currency, base_currency(), $transaction->amount);

			$transaction->payer_payee_id = $request->input('payer_payee_id');

			$transaction->payment_method_id = $request->input('payment_method_id');
			$transaction->reference = $request->input('reference');
			$transaction->razon_social = $request->input('razon_social');
			$transaction->tipo_comprobante_id = $request->input('tipo_comprobante_id');
			$transaction->imputar_a = $request->input('imputar_a');
			$transaction->detalle_rubro = $request->input('detalle_rubro');
			$transaction->banco = $request->input('banco');
			$transaction->cheque_nro = $request->input('cheque_nro');
			$transaction->cheque_vencimiento = $request->input('cheque_vencimiento');
			$transaction->cheque_entregado_a = $request->input('cheque_entregado_a');
			$transaction->note = $request->input('note');
			$transaction->attachment = $attachment;
			$transaction->usd = $request->input('usd');
			$transaction->tasa = $request->input('tasa');
			$transaction->trans_asoc = $transactionDevolucion->id;

			$imputarA = $request->input('imputar_a');

			$companyNames = [
				'distribuir' => 'A dividir',
				'triunvirato' => 'Triunvirato',
				'pentacar' => 'Pentacar',
				'paternal' => 'Paternal',
				'g.u.t.' => 'Gut',
			];

			if (isset($companyNames[$imputarA])) {
				// Solo se ejecuta UNA consulta si el valor existe
				$company = Company::where('business_name', $companyNames[$imputarA])->first();
				if ($company) {
					$transaction->company_id = $company->id;
				}
			}



			$transaction->save();

			$idTrans = $transaction->id;


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

					if ($sumMont < $nuevoMonto) {

						$arrTrsc[] = ['id' => $trs->id, 'monto' => $trs->amount];

						$tr = new TransactionRetiroCotizacion();
						$tr->idInvoiceConSaldo = $invoiceC->id;
						$tr->idCLientePagar = $request->input('client_id');
						$tr->monto =  $trs->amount;
						$tr->idTransactionOld =  $trs->id;
						$tr->idTransactionNew =  $idTrans;
						$tr->idTransactionCC = $transactionDevolucion->id;
						$tr->save();
					} elseif ($sumMont == $nuevoMonto) {
						$arrTrsc[] = ['id' => $trs->id, 'monto' => $trs->amount];

						$tr = new TransactionRetiroCotizacion();
						$tr->idInvoiceConSaldo = $invoiceC->id;
						$tr->idCLientePagar = $request->input('client_id');
						$tr->monto =  $trs->amount;
						$tr->idTransactionOld =  $trs->id;
						$tr->idTransactionNew =  $idTrans;
						$tr->idTransactionCC = $transactionDevolucion->id;
						$tr->save();
						break;
					} else if ($sumMont > $nuevoMonto) {

						$diferencia = $trs->amount - $nuevoMonto;
						$sumPrev = $sumMont - $trs->amount;


						$diferencia = $nuevoMonto - $sumPrev;
						$arrTrsc[] = ['id' => $trs->id, 'monto' => $diferencia];

						$tr = new TransactionRetiroCotizacion();
						$tr->idInvoiceConSaldo = $invoiceC->id;
						$tr->idCLientePagar = $request->input('client_id');
						$tr->monto =  $diferencia;
						$tr->idTransactionOld =  $trs->id;
						$tr->idTransactionNew =  $idTrans;
						$tr->idTransactionCC = $transactionDevolucion->id;
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
		} else {
			$transaction = new Transaction();
			$transaction->trans_date = $request->input('trans_date');
			$transaction->account_id = $request->input('account_id');
			$transaction->chart_id = $request->input('chart_id');
			$transaction->type = 'expense';
			$transaction->dr_cr = 'dr';
			$transaction->amount = $request->input('amount');
			$transaction->base_amount = convert_currency($transaction->account->account_currency, base_currency(), $transaction->amount);

			$transaction->payer_payee_id = $request->input('payer_payee_id');

			$transaction->payment_method_id = $request->input('payment_method_id');
			$transaction->reference = $request->input('reference');
			$transaction->razon_social = $request->input('razon_social');
			$transaction->tipo_comprobante_id = $request->input('tipo_comprobante_id');
			$transaction->imputar_a = $request->input('imputar_a');
			$transaction->detalle_rubro = $request->input('detalle_rubro');
			$transaction->banco = $request->input('banco');
			$transaction->cheque_nro = $request->input('cheque_nro');
			$transaction->cheque_vencimiento = $request->input('cheque_vencimiento');
			$transaction->cheque_entregado_a = $request->input('cheque_entregado_a');
			$transaction->note = $request->input('note');
			$transaction->attachment = $attachment;
			$transaction->usd = $request->input('usd');
			$transaction->tasa = $request->input('tasa');

			$transaction->status = $request->input('status', null);
			$imputarA = $request->input('imputar_a');

			$companyNames = [
				'distribuir' => 'A dividir',
				'triunvirato' => 'Triunvirato',
				'pentacar' => 'Pentacar',
				'paternal' => 'Paternal',
				'g.u.t.' => 'Gut',
			];

			if (isset($companyNames[$imputarA])) {
				// Solo se ejecuta UNA consulta si el valor existe
				$company = Company::where('business_name', $companyNames[$imputarA])->first();
				if ($company) {
					$transaction->company_id = $company->id;
				}
			}


			$transaction->save();
		}










		// relacion de transaccion con auto
		if ($request->input('idCar')) {
			$pagos_car = new Pagos_car();
			$pagos_car->id_car = $request->input('idCar');
			$pagos_car->id_gasto = $transaction->id;
			$pagos_car->save();
		}


		//Set Prefix Data
		$date_format = get_company_option('date_format', 'Y-m-d');
		$transaction->trans_date = date("$date_format", strtotime($transaction->trans_date));
		$transaction->amount = decimalPlace($transaction->amount, currency());
		$transaction->account_id = $transaction->account->account_title;
		$transaction->chart_id = $transaction->expense_type->name;
		$transaction->payer_payee_id = isset($transaction->payee->name) ? $transaction->payee->name : '';
		$transaction->payment_method_id = $transaction->payment_method->name;

		DB::commit();

		if (!$request->ajax()) {
			return redirect('expense/create')->with('success', _lang('Saved Sucessfully'));
		} else {
			return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved Sucessfully'), 'data' => $transaction]);
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
		$transaction = Transaction::where("id", $id)
			->first(); //->where("company_id", company_id())

		if (!$request->ajax()) {
			return view('backend.accounting.expense.view', compact('transaction', 'id'));
		} else {
			return view('backend.accounting.expense.modal.view', compact('transaction', 'id'));
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
		$transaction = Transaction::where("id", $id)
			->first(); //->where("company_id", company_id())
		if (!$request->ajax()) {
			return view('backend.accounting.expense.edit', compact('transaction', 'id'));
		} else {
			return view('backend.accounting.expense.modal.edit', compact('transaction', 'id'));
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
		$validator = Validator::make($request->all(), [
			'trans_date' => 'required',
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
				return redirect()->route('expense.edit', $id)
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

		$transaction = Transaction::where("id", $id)->first(); //->where("company_id", company_id())
		$previous_amount = $transaction->amount;
		$transaction->trans_date = $request->input('trans_date');
		$transaction->account_id = $request->input('account_id');
		$transaction->chart_id = $request->input('chart_id');
		$transaction->type = 'expense';
		$transaction->dr_cr = 'dr';
		$transaction->amount = $request->input('amount');
		$transaction->imputar_a = $request->input('imputar_a');

		if (($previous_amount != $transaction->amount) || $transaction->base_amount == '') {
			$transaction->base_amount = convert_currency($transaction->account->account_currency, base_currency(), $transaction->amount);
		}

		if ($request->input('related_to') == '') {
			// $transaction->payer_payee_id = null;
			// $transaction->project_id = null;
		} else if ($request->input('related_to') == 'contacts') {
			// $transaction->payer_payee_id = $request->input('payer_payee_id');
		} else if ($request->input('related_to') == 'projects') {
			// $transaction->project_id = $request->input('project_id');
		}

		$transaction->payment_method_id = $request->input('payment_method_id');
		$transaction->reference = $request->input('reference');
		$transaction->note = $request->input('note');
		if ($request->hasfile('attachment')) {
			$transaction->attachment = $attachment;
		}
		if ($transaction->imputar_a == 'distribuir') {
			$company = Company::where('business_name', 'A dividir')->first();
			$transaction->company_id = $company->id;
		}

		if ($transaction->imputar_a == 'triunvirato') {
			$company = Company::where('business_name', 'Triunvirato')->first();
			$transaction->company_id = $company->id;
		}

		if ($transaction->imputar_a == 'pentacar') {
			$company = Company::where('business_name', 'Pentacar')->first();
			$transaction->company_id = $company->id;
		}
		if ($transaction->imputar_a == 'paternal') {
			$company = Company::where('business_name', 'Paternal')->first();
			$transaction->company_id = $company->id;
		}
		if ($transaction->imputar_a == 'g.u.t.') {
			$company = Company::where('business_name', 'Gut')->first();
			$transaction->company_id = $company->id;
		}

		$old_status = $transaction->status;

		$transaction->status = $request->input('status', null);
		$transaction->usd = $request->input('usd');
		$transaction->tasa = $request->input('tasa');


		$old_payment_priority = $transaction->payment_priority;


		$transaction->payment_priority = $request->has('payment_priority') && $request->input('payment_priority') !== '' ? $request->input('payment_priority') : null;

		$transaction->save();

		if (($transaction->pagos_car->id_car ?? '') != '') { // si la orden esta asociada a un vehiculo

			$car = Cars::where('id',  $transaction->pagos_car->id_car)->first();

			if ($old_status != $transaction->status) {  // cambio de estado

				//$tramitador= User::where('id',$transaction->payer_payee_id)->get();
				$tramitador = User::where('id', $car->idTramitador)->get();

				if ($tramitador) {
					Notification::send($tramitador, new PagosCarChangeStatus($transaction));
				}
			}

			if ($old_payment_priority != $transaction->payment_priority) {  // cambio de prioridad
				$company_id_car = $car->company_id;
				$cajeros = User::wherehas('role', function ($q) {
					$q->where('name', 'Cajera');
				})->where('company_id', $company_id_car)->get();


				Notification::send($cajeros, new PagosCarChangePriority($transaction));
			}
		};

		//Set Related Data
		$date_format = get_company_option('date_format', 'Y-m-d');
		$transaction->trans_date = date("$date_format", strtotime($transaction->trans_date));
		$transaction->amount = decimalPlace($transaction->amount, currency());
		$transaction->account_id = $transaction->account->account_title;
		$transaction->chart_id = $transaction->expense_type->name;
		$transaction->payer_payee_id = isset($transaction->payee->contact_name) ? $transaction->payee->contact_name : '';
		$transaction->payment_method_id = $transaction->payment_method->name;

		if (!$request->ajax()) {
			return redirect('expense')->with('success', _lang('Updated Sucessfully'));
		} else {
			return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated Sucessfully'), 'data' => $transaction]);
		}
	}

	public function calendar()
	{
		$transactions = Transaction::where("company_id", company_id())
			->where("type", "expense")
			->orderBy("id", "desc")->get();
		return view('backend.accounting.expense.calendar', compact('transactions'));
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id)
	{

		$transaction = Transaction::where("id", $id)->first(); //->where("company_id", company_id())

		//buscar en transacciones_retiro_cotizaciones si existe para restaurar

		$transRetiro = TransactionRetiroCotizacion::where('idTransactionNew', $id);

		if (!empty($transRetiro)) {

			foreach ($transRetiro->get() as $tr) {
				// dd($tr);
				$t = Transaction::find($tr->idTransactionOld);
				$t->amount = $t->amount + $tr->monto;
				$t->base_amount = $t->base_amount + $tr->monto;
				$t->save();


				$t_cc = Transaction::where('id', $tr->idTransactionCC)->where('type', 'cc_expense')->first();
				if ($t_cc) {
					$t_cc->delete();
				}
			}

			$transRetiro->delete();
		}

		if ($transaction->status != 1) {
			DB::beginTransaction();
			if ($transaction->purchase_id != null) {
				$purchase = Purchase::find($transaction->purchase_id);
				$purchase->paid = $purchase->paid - $transaction->base_amount;

				if (round($purchase->paid, 2) >= $purchase->grand_total) {
					$purchase->payment_status = 1;
				} else {
					$purchase->payment_status = 0;
				}
				$purchase->save();
			}

	//eliminar trans asoc
			if (!empty($transaction->trans_asoc)) {
				$t_asoc = Transaction::find($transaction->trans_asoc);
				if ($t_asoc) {
					$t_asoc->delete(); 
				}
			}




			$transaction->delete();
			DB::commit();
			return redirect('expense')->with('success', _lang('Removed Sucessfully'));
		} else {
			return redirect('expense')->with('error', _lang('No puede eliminar orden ya esta pagada'));
		}
		//If Purchase Exists

	}

	public function caja_diaria()
	{
		$gastos = Transaction::where('trans_date', date('Y-m-d'))->where('company_id', company_id())->where('dr_cr', 'dr')->sum('amount');
		$ingresos = Transaction::where('trans_date', date('Y-m-d'))->where('company_id', company_id())->where('dr_cr', 'cr')
			->sum('amount');

		return view('backend.accounting.caja_diaria.list', compact('gastos', 'ingresos'));
	}

	public function get_caja_table_data(Request $request)
	{

		$currency = currency();

		$transactions = Transaction::with("account")->with("expense_type")->with("income_type")
			->with("payment_method")
			->with("tipo_comprobante")
			->select('transactions.*')
			->where("transactions.company_id", company_id())
			//->where("transactions.dr_cr", "dr")
			->where('trans_date', date('Y-m-d'))
			->orderBy("transactions.id", "desc");
		if (isset($request->idCar)) {
			$transactions->whereHas('pagos_car', function ($strg) use ($request) {
				$strg->where('id_car', $request->idCar);
			});
		}
		return Datatables::eloquent($transactions)
			->editColumn('trans_date', function ($trans) {
				$date_format = get_company_option('date_format', 'Y-m-d');
				return date($date_format, strtotime($trans->trans_date));
			})
			->editColumn('amount', function ($trans) use ($currency) {
				$acc_currency = currency($trans->account->account_currency);
				if ($trans->dr_cr == 'dr') {
					if ($acc_currency != $currency) {
						return "<span class='float-right'>" . decimalPlace(-$trans->amount, currency($trans->account->account_currency)) . "</span><br>
										<span class='float-right'><b>" . decimalPlace($trans->base_amount, $currency) . "</b></span>";
					} else {
						return "<span class='float-right'>" . decimalPlace(-$trans->amount, currency($trans->account->account_currency)) . "</span>";
					}
				} else {
					if ($acc_currency != $currency) {
						return "<span class='float-right'>" . decimalPlace($trans->amount, currency($trans->account->account_currency)) . "</span><br>
										<span class='float-right'><b>" . decimalPlace($trans->base_amount, $currency) . "</b></span>";
					} else {
						return "<span class='float-right'>" . decimalPlace($trans->amount, currency($trans->account->account_currency)) . "</span>";
					}
				}
			})
			->editColumn('payee.contact_name', function ($trans) {
				return isset($trans->payee->contact_name) ? $trans->payee->contact_name : '';
			})
			->editColumn('expense_type.name', function ($trans) {
				return isset($trans->expense_type->name) ? $trans->expense_type->name : _lang('Transfer');
			})
			->editColumn('payer.name', function ($trans) {
				return isset($trans->payer->name) ? $trans->payer->name : '';
			})
			->editColumn('tipo_comprobante.descripcion', function ($trans) {
				return isset($trans->tipo_comprobante->descripcion) ? $trans->tipo_comprobante->descripcion : '';
			})
			->editColumn('cuenta_imputar.account_title', function ($trans) {
				return isset($trans->cuenta_imputar->account_title) ? $trans->cuenta_imputar->account_title . 'dasdasd' : 'A distribuir';
			})
			->addColumn('action', function ($trans) {
				if (isset($trans->expense_type->name)) {
					return '<form action="' . action('ExpenseController@destroy', $trans['id']) . '" class="text-center" method="post">'
						. '<a href="' . action('ExpenseController@edit', $trans['id']) . '" data-title="' . _lang('Update Expense') . '" class="btn btn-warning btn-xs ajax-modal"><i class="ti-pencil"></i></a>&nbsp;'
						. '<a href="' . action('ExpenseController@show', $trans['id']) . '" data-title="' . _lang('View Expense') . '" class="btn btn-primary btn-xs ajax-modal"><i class="ti-eye"></i></a>&nbsp;'
						. csrf_field()
						. '<input name="_method" type="hidden" value="DELETE">'
						. '<button class="btn btn-danger btn-xs btn-remove" type="submit"><i class="ti-eraser"></i></button>'
						. '</form>';
				} else {
					return '<form action="' . action('ExpenseController@destroy', $trans['id']) . '" class="text-center" method="post">'
						. '<a href="#" data-title="' . _lang('Update Expense') . '" class="btn btn-warning btn-xs ajax-modal disabled"><i class="ti-pencil"></i></a>&nbsp;'
						. '<a href="' . action('ExpenseController@show', $trans['id']) . '" data-title="' . _lang('View Expense') . '" class="btn btn-primary btn-xs ajax-modal"><i class="ti-eye"></i></a>&nbsp;'
						. csrf_field()
						. '<input name="_method" type="hidden" value="DELETE">'
						. '<button class="btn btn-danger btn-xs btn-remove" type="submit"><i class="ti-eraser"></i></button>'
						. '</form>';
				}
			})
			->setRowId(function ($trans) {
				return "row_" . $trans->id;
			})
			->rawColumns(['status', 'action', 'amount'])
			->make(true);
	}



	public function createCuentaCorriente(Request $request)
	{
		$id = $request->get('id');

		// Obtener saldo actual del cliente
		$ultimoMovimiento = CuentaCorriente::where('payer_payee_id', $id)
			->orderBy('created_at', 'desc')
			->first();

		$saldoPeso = $ultimoMovimiento ? $ultimoMovimiento->saldo_peso : 0;
		$saldoUsd = $ultimoMovimiento ? $ultimoMovimiento->saldo_usd : 0;

		$data = [
			'id' => $id,
			'saldo_peso' => $saldoPeso,
			'saldo_usd' => $saldoUsd,
		];

		if (!$request->ajax()) {
			return view('backend.accounting.cuenta_corriente.createGasto', $data);
		} else {
			return view('backend.accounting.cuenta_corriente.modal.createGasto', $data);
		}
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function storeCuentaCorriente(Request $request)
	{
		$id = $request->get('id');

		$validator = Validator::make($request->all(), [
			'trans_date' => 'required',
			'account_id' => 'required',
			'amount' => 'required|numeric|min:0.01',
			'payment_method_id' => 'required',
			'usd' => 'nullable|in:0,1',
			'tasa' => 'required_if:usd,1|numeric|min:0.01',
			'reference' => 'nullable|max:50',
			'attachment' => 'nullable|mimes:jpeg,png,jpg,doc,pdf,docx,zip',
		], [
			'tasa.required_if' => 'La tasa de cambio es requerida cuando se retira en USD.',
		]);

		// 		if ($validator->fails()) {
		// 			if ($request->ajax()) {
		// 				return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
		// 			} else {
		// 				return redirect('income/create')
		// 					->withErrors($validator)
		// 					->withInput();
		// 			}
		// 		}

		// 		$attachment = '';
		// 		if ($request->hasfile('attachment')) {
		// 			$file = $request->file('attachment');
		// 			$attachment = time() . $file->getClientOriginalName();
		// 			$file->move(public_path() . "/uploads/transactions/", $attachment);
		// 		}

		// 		$transaction = new Transaction();
		// 		$transaction->trans_date = $request->input('trans_date');
		// 		// $transaction->account_id = $request->input('account_id');
		// 		$transaction->chart_id = $request->input('chart_id');
		// 		$transaction->type = 'cc_expense';
		// 		$transaction->dr_cr = 'cc';
		// 		$transaction->amount = $request->input('amount');
		// 		$transaction->base_amount = $request->input('amount');
		// 		$transaction->payer_payee_id = $id;
		// 		$transaction->payment_method_id = $request->input('payment_method_id');
		// 		$transaction->reference = $request->input('reference');
		// 		$transaction->razon_social = $request->input('razon_social');
		// 		$transaction->tipo_comprobante_id = $request->input('tipo_comprobante_id');
		// 		$transaction->imputar_a = $request->input('imputar_a');
		// 		$transaction->detalle_rubro = $request->input('detalle_rubro');
		// 		$transaction->banco = $request->input('banco');
		// 		$transaction->cheque_nro = $request->input('cheque_nro');
		// 		$transaction->cheque_vencimiento = $request->input('cheque_vencimiento');
		// 		$transaction->cheque_entregado_a = $request->input('cheque_entregado_a');
		// 		$transaction->note = $request->input('note');
		// 		$transaction->attachment = $attachment;
		// 		$transaction->company_id = company_id();

		// 		$transaction->usd = $request->input('usd');
		// 		$transaction->tasa = $request->input('tasa');

		// 		$transaction->save();
		// //////////////////////////////////////////////////////////////////////////////////////////////////////
		// 		$transaction = new Transaction();
		// 		$transaction->trans_date = $request->input('trans_date');
		// 		$transaction->account_id = $request->input('account_id');
		// 		$transaction->chart_id = $request->input('chart_id');
		// 		$transaction->type = 'income';
		// 		$transaction->dr_cr = 'cr';
		// 		$transaction->amount = $request->input('amount');
		// 		$transaction->base_amount = $request->input('amount');
		// 		$transaction->payer_payee_id = $id;
		// 		$transaction->payment_method_id = $request->input('payment_method_id');
		// 		$transaction->reference = $request->input('reference');
		// 		$transaction->razon_social = $request->input('razon_social');
		// 		$transaction->tipo_comprobante_id = $request->input('tipo_comprobante_id');
		// 		$transaction->imputar_a = $request->input('imputar_a');
		// 		$transaction->detalle_rubro = $request->input('detalle_rubro');
		// 		$transaction->banco = $request->input('banco');
		// 		$transaction->cheque_nro = $request->input('cheque_nro');
		// 		$transaction->cheque_vencimiento = $request->input('cheque_vencimiento');
		// 		$transaction->cheque_entregado_a = $request->input('cheque_entregado_a');
		// 		$transaction->note = $request->input('note');
		// 		$transaction->attachment = $attachment;
		// 		// $transaction->company_id = company_id();

		// 		$transaction->usd = $request->input('usd');
		// 		$transaction->tasa = $request->input('tasa');

		// 		if ($transaction->imputar_a == 'distribuir') {
		// 			$company = Company::where('business_name', 'A dividir')->first();
		// 			$transaction->company_id = $company->id;
		// 		}

		// 		if ($transaction->imputar_a == 'triunvirato') {
		// 			$company = Company::where('business_name', 'Triunvirato')->first();
		// 			$transaction->company_id = $company->id;
		// 		}

		// 		if ($transaction->imputar_a == 'pentacar') {
		// 			$company = Company::where('business_name', 'Pentacar')->first();
		// 			$transaction->company_id = $company->id;
		// 		}
		// 		if ($transaction->imputar_a == 'paternal') {
		// 			$company = Company::where('business_name', 'Paternal')->first();
		// 			$transaction->company_id = $company->id;
		// 		}


		// 		$transaction->save();

		// 		//Set Related Data
		// //		$transaction->trans_date = date('d M, Y', strtotime($transaction->trans_date));
		// //		$transaction->amount = currency() . " " . decimalPlace($transaction->amount);
		// //		$transaction->account_id = $transaction->account->account_title;
		// //		$transaction->chart_id = $transaction->income_type->name;
		// //		$transaction->payer_payee_id = isset($transaction->payer->name) ? $transaction->payer->name : '';
		// //		$transaction->payment_method_id = $transaction->payment_method->name;





		if ($validator->fails()) {
			if ($request->ajax()) {
				return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
			} else {
				return redirect('expense/create')
					->withErrors($validator)
					->withInput();
			}
		}

		// Validación: Verificar que el cliente tenga saldo a favor (saldo negativo)
		$ultimoMovimiento = CuentaCorriente::where('payer_payee_id', $id)
			->orderBy('created_at', 'desc')
			->first();

		$saldoPeso = $ultimoMovimiento ? $ultimoMovimiento->saldo_peso : 0;
		$saldoUsd = $ultimoMovimiento ? $ultimoMovimiento->saldo_usd : 0;

		$monto = $request->input('amount');
		$esUsd = $request->input('usd') == 1;

		if ($esUsd) {
			// Validar saldo en USD
			if ($saldoUsd >= 0) {
				return response()->json([
					'result' => 'error',
					'message' => ['El cliente no tiene saldo a favor en USD para retirar.']
				]);
			}
			if (abs($saldoUsd) < $monto) {
				return response()->json([
					'result' => 'error',
					'message' => ['El monto a retirar (' . $monto . ' USD) excede el saldo a favor disponible (' . abs($saldoUsd) . ' USD).']
				]);
			}
		} else {
			// Validar saldo en Pesos
			if ($saldoPeso >= 0) {
				return response()->json([
					'result' => 'error',
					'message' => ['El cliente no tiene saldo a favor en Pesos para retirar.']
				]);
			}
			if (abs($saldoPeso) < $monto) {
				return response()->json([
					'result' => 'error',
					'message' => ['El monto a retirar (' . $monto . ' $) excede el saldo a favor disponible (' . abs($saldoPeso) . ' $).']
				]);
			}
		}

		DB::beginTransaction();
		$methodP = PaymentMethod::where('name', 'like', '%Gasto cc')->first();


		// dd($id);
		$transactionDevolucion = new Transaction();
		$transactionDevolucion->trans_date = $request->input('trans_date');
		// $transactionDevolucion->account_id = $request->input('account_id');
		$transactionDevolucion->chart_id = $request->input('chart_id');
		$transactionDevolucion->type = 'cc_expense';
		$transactionDevolucion->dr_cr = 'cc';
		$transactionDevolucion->amount = $request->input('amount');
		$transactionDevolucion->base_amount = convert_currency($transactionDevolucion->account->account_currency, base_currency(), $transactionDevolucion->amount);

		$transactionDevolucion->payer_payee_id = $id;

		$transactionDevolucion->payment_method_id = $methodP->id;
		$transactionDevolucion->reference = $request->input('reference');
		$transactionDevolucion->razon_social = $request->input('razon_social');
		$transactionDevolucion->tipo_comprobante_id = $request->input('tipo_comprobante_id');
		$transactionDevolucion->imputar_a = $request->input('imputar_a');
		$transactionDevolucion->detalle_rubro = $request->input('detalle_rubro');
		$transactionDevolucion->banco = $request->input('banco');
		$transactionDevolucion->cheque_nro = $request->input('cheque_nro');
		$transactionDevolucion->cheque_vencimiento = $request->input('cheque_vencimiento');
		$transactionDevolucion->cheque_entregado_a = $request->input('cheque_entregado_a');
		$transactionDevolucion->note = 'Retiro de cuenta';;
		// $transactionDevolucion->attachment = $attachment;
		$transactionDevolucion->usd = $request->input('usd');
		$transactionDevolucion->tasa = $request->input('tasa');

		$transactionDevolucion->status = $request->input('status', 1);
		if ($transactionDevolucion->imputar_a == 'distribuir') {
			$company = Company::where('business_name', 'A dividir')->first();
			$transactionDevolucion->company_id = $company->id;
		}

		if ($transactionDevolucion->imputar_a == 'triunvirato') {
			$company = Company::where('business_name', 'Triunvirato')->first();
			$transactionDevolucion->company_id = $company->id;
		}

		if ($transactionDevolucion->imputar_a == 'pentacar') {
			$company = Company::where('business_name', 'Pentacar')->first();
			$transactionDevolucion->company_id = $company->id;
		}
		if ($transactionDevolucion->imputar_a == 'paternal') {
			$company = Company::where('business_name', 'Paternal')->first();
			$transactionDevolucion->company_id = $company->id;
		}
		if ($transactionDevolucion->imputar_a == 'g.u.t.') {
			$company = Company::where('business_name', 'Gut')->first();
			$transactionDevolucion->company_id = $company->id;
		}


		$transactionDevolucion->save();

		$transaction = new Transaction();
		$transaction->trans_date = $request->input('trans_date');
		$transaction->account_id = $request->input('account_id');
		$transaction->chart_id = $request->input('chart_id');
		$transaction->type = 'expense';
		$transaction->dr_cr = 'dr';
		$transaction->amount = $request->input('amount');
		$transaction->base_amount = convert_currency($transaction->account->account_currency, base_currency(), $transaction->amount);

		$transaction->payer_payee_id = $id;

		$transaction->payment_method_id = $request->input('payment_method_id');
		$transaction->reference = $request->input('reference');
		$transaction->razon_social = $request->input('razon_social');
		$transaction->tipo_comprobante_id = $request->input('tipo_comprobante_id');
		$transaction->imputar_a = $request->input('imputar_a');
		$transaction->detalle_rubro = $request->input('detalle_rubro');
		$transaction->banco = $request->input('banco');
		$transaction->cheque_nro = $request->input('cheque_nro');
		$transaction->cheque_vencimiento = $request->input('cheque_vencimiento');
		$transaction->cheque_entregado_a = $request->input('cheque_entregado_a');
		$transaction->note = 'Retiro de cuenta';
		// $transaction->attachment = $attachment;
		$transaction->usd = $request->input('usd');
		$transaction->tasa = $request->input('tasa');
		$transaction->trans_asoc = $transactionDevolucion->id;
		$transaction->company_id = $transactionDevolucion->company_id;

		$transaction->save();

		DB::commit();
		if (!$request->ajax()) {
			//return redirect('income/create')->with('success', _lang('Saved Sucessfully'));
		} else {
			return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved Sucessfully'), 'data' => $transaction]);
		}
	}
}
