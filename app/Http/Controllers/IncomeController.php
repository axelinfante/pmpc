<?php

namespace App\Http\Controllers;

use App\Company;
use App\Invoice;
use App\Transaciones_cotizaciones;
use App\Transaction;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;

class IncomeController extends Controller
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
		//        $month     = date('m');
		//        $year      = date('Y');

		$income = \App\Transaction::selectRaw("IFNULL(SUM(base_amount),0) as total")
			->where("dr_cr", "cr")
			//->where("company_id", $compan_id)
			->where("usd", null)
			->first()->total;
		$income_USD = \App\Transaction::selectRaw("IFNULL(SUM(base_amount),0) as total")
			->where("dr_cr", "cr")
			//->where("company_id", $compan_id)
			->where("usd", 1)
			->first()->total;

		$data = [
			'total' => $income,
			'totalUsd' => $income_USD
		];
		return view('backend.accounting.income.list', $data);
	}

	public function get_table_data()
	{

		$currency = currency();

		$transactions = Transaction::with("account")->with("income_type")
			->with("payer")->with("payment_method")
			->with("tipo_comprobante")
			->select('transactions.*')
			// ->where("transactions.company_id", company_id())
			->where("transactions.dr_cr", "cr")
			->orderBy("transactions.id", "desc");

		return Datatables::eloquent($transactions)


			->editColumn('trans_date', function ($trans) {
				$date_format = get_company_option('date_format', 'd/m/Y');
				return date($date_format, strtotime($trans->trans_date));
			})
			->editColumn('amount', function ($trans) use ($currency) {
				$acc_currency = currency($trans->account->account_currency);
				if ($acc_currency != $currency) {
					return "<span class='float-right'>" . decimalPlace($trans->amount, currency($trans->account->account_currency)) . "</span><br>
										<span class='float-right'><b>" . decimalPlace($trans->base_amount, $currency) . "</b></span>";
				} else {
					return "<span class='float-right'>" . decimalPlace($trans->amount, currency($trans->account->account_currency)) . "</span>";
				}
			})
			->editColumn('payer.name', function ($trans) {
				return isset($trans->payer->name) ? $trans->payer->name : '';
			})
			->editColumn('payer.name', function ($trans) {
				return isset($trans->payer->name) ? $trans->payer->name : '';
			})
			->editColumn('tipo_comprobante.descripcion', function ($trans) {
				return isset($trans->tipo_comprobante->descripcion) ? $trans->tipo_comprobante->descripcion : '';
			})
			->editColumn('imputar_a', function ($trans) {
				return $trans->imputar_a ?? null;
			})
			->editColumn('tasa', function ($trans) {
				return $trans->tasa;
			})
			->editColumn('income_type.name', function ($trans) {
				if ($trans->invoice_id == null || !$trans->Invoice) {
					return isset($trans->income_type->name) ? $trans->income_type->name : _lang('Transfer');
				}
				// dd($trans->invoice);
				return $trans->income_type->name . "<br><a href='" . action('InvoiceController@show', $trans->invoice_id) . "' target='_blank'>" . $trans->Invoice->invoice_number ?? 'Ver comprobante' . "</a>";
			})

			->editColumn('tasa', function ($trans) {
				return $trans->tasa;
			})
			->addColumn('action', function ($trans) {
				if (isset($trans->income_type->name)) {
					return '<form action="' . action('IncomeController@destroy', $trans['id']) . '" class="text-center" method="post">'
						. '<a href="' . action('IncomeController@edit', $trans['id']) . '" data-title="' . _lang('Update Income') . '" class="btn btn-warning btn-xs ajax-modal"><i class="ti-pencil"></i></a>&nbsp;'
						. '<a href="' . action('IncomeController@show', $trans['id']) . '" data-title="' . _lang('View Income') . '" class="btn btn-primary btn-xs ajax-modal"><i class="ti-eye"></i></a>&nbsp;'
						. csrf_field()
						. '<input name="_method" type="hidden" value="DELETE">'
						. '<button class="btn btn-danger btn-xs btn-remove" type="submit"><i class="ti-eraser"></i></button>'
						. '</form>';
				} else {
					return '<form action="' . action('IncomeController@destroy', $trans['id']) . '" class="text-center" method="post">'
						. '<a href="#" data-title="' . _lang('Update Income') . '" class="btn btn-warning btn-xs ajax-modal disabled"><i class="ti-pencil"></i></a>&nbsp;'
						. '<a href="' . action('IncomeController@show', $trans['id']) . '" data-title="' . _lang('View Income') . '" class="btn btn-info btn-xs ajax-modal"><i class="ti-eye"></i></a>&nbsp;'
						. csrf_field()
						. '<input name="_method" type="hidden" value="DELETE">'
						. '<button class="btn btn-danger btn-xs btn-remove" type="submit"><i class="ti-eraser"></i></button>'
						. '</form>';
				}
			})
			->filterColumn('trans_date', function ($query, $keyword) {
					$date_range = ($keyword != '') ? explode(" - ", $keyword) : array();
                    if (count($date_range) == 2) {
                        $query->whereDate('trans_date', '>=', $date_range[0])
                            ->whereDate('trans_date', '<=', $date_range[1]);
                    }
			})
			->setRowId(function ($trans) {
				return "row_" . $trans->id;
			})
			->rawColumns(['income_type.name', 'amount', 'base_amount', 'status', 'action'])
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
			return view('backend.accounting.income.create');
		} else {
			return view('backend.accounting.income.modal.create');
		}
	}

	public function createCuentaCorriente(Request $request)
	{
		// dd();
		$id = $request->get('id');
		if (!$request->ajax()) {
			return view('backend.accounting.cuenta_corriente.create');
		} else {
			return view('backend.accounting.cuenta_corriente.modal.create', ['id' => $id]);
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
			//			'account_id' => 'required',
			//			'chart_id' => 'required',
			'amount' => 'required|numeric',
			'payment_method_id' => 'required',
			//			'reference' => 'nullable|max:50',
			//			'attachment' => 'nullable|mimes:jpeg,png,jpg,doc,pdf,docx,zip',
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

		$attachment = '';
		if ($request->hasfile('attachment')) {
			$file = $request->file('attachment');
			$attachment = time() . $file->getClientOriginalName();
			$file->move(public_path() . "/uploads/transactions/", $attachment);
		}

		$transaction = new Transaction();
		$transaction->trans_date = $request->input('trans_date');
		// $transaction->account_id = $request->input('account_id');
		$transaction->chart_id = $request->input('chart_id');
		$transaction->type = 'cc';
		$transaction->dr_cr = 'cc';
		$transaction->amount = $request->input('amount');
		$transaction->base_amount = $request->input('amount');
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
		$transaction->note = $request->input('note');
		$transaction->attachment = $attachment;
		$transaction->company_id = company_id();

		$transaction->usd = $request->input('usd');
		$transaction->tasa = $request->input('tasa');

		$transaction->save();
		$idAsoc = $transaction->id;
		//////////////////////////////////////////////////////////////////////////////////////////////////////
		$transaction = new Transaction();
		$transaction->trans_date = $request->input('trans_date');
		$transaction->account_id = $request->input('account_id');
		$transaction->chart_id = $request->input('chart_id');
		$transaction->type = 'income';
		$transaction->dr_cr = 'cr';
		$transaction->amount = $request->input('amount');
		$transaction->base_amount = $request->input('amount');
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
		$transaction->note = $request->input('note');
		$transaction->attachment = $attachment;
		$transaction->trans_asoc = $idAsoc;
		// $transaction->company_id = company_id();

		$transaction->usd = $request->input('usd');
		$transaction->tasa = $request->input('tasa');

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


		$transaction->save();

		//Set Related Data
		//		$transaction->trans_date = date('d M, Y', strtotime($transaction->trans_date));
		//		$transaction->amount = currency() . " " . decimalPlace($transaction->amount);
		//		$transaction->account_id = $transaction->account->account_title;
		//		$transaction->chart_id = $transaction->income_type->name;
		//		$transaction->payer_payee_id = isset($transaction->payer->name) ? $transaction->payer->name : '';
		//		$transaction->payment_method_id = $transaction->payment_method->name;

		if (!$request->ajax()) {
			//return redirect('income/create')->with('success', _lang('Saved Sucessfully'));
		} else {
			return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved Sucessfully'), 'data' => $transaction]);
		}
	}

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
				return redirect('income/create')
					->withErrors($validator)
					->withInput();
			}
		}

		$attachment = '';
		if ($request->hasfile('attachment')) {
			$file = $request->file('attachment');
			$attachment = time() . $file->getClientOriginalName();
			$file->move(public_path() . "/uploads/transactions/", $attachment);
		}

		$transaction = new Transaction();
		$transaction->trans_date = $request->input('trans_date');
		$transaction->account_id = $request->input('account_id');
		$transaction->chart_id = $request->input('chart_id');
		$transaction->type = 'income';
		$transaction->dr_cr = 'cr';
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

		$transaction->usd = $request->input('usd');
		$transaction->tasa = $request->input('tasa');

		$transaction->save();

		//Set Related Data
		$transaction->trans_date = date('d M, Y', strtotime($transaction->trans_date));
		$transaction->amount = currency() . " " . decimalPlace($transaction->amount);
		$transaction->account_id = $transaction->account->account_title;
		$transaction->chart_id = $transaction->income_type->name;
		$transaction->payer_payee_id = isset($transaction->payer->name) ? $transaction->payer->name : '';
		$transaction->payment_method_id = $transaction->payment_method->name;

		if (!$request->ajax()) {
			return redirect('income/create')->with('success', _lang('Saved Sucessfully'));
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
			return view('backend.accounting.income.view', compact('transaction', 'id'));
		} else {
			return view('backend.accounting.income.modal.view', compact('transaction', 'id'));
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
			return view('backend.accounting.income.edit', compact('transaction', 'id'));
		} else {
			return view('backend.accounting.income.modal.edit', compact('transaction', 'id'));
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
				return redirect()->route('income.edit', $id)
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
		$transaction->type = 'income';
		$transaction->dr_cr = 'cr';
		$transaction->amount = $request->input('amount');
		if (($previous_amount != $transaction->amount) || $transaction->base_amount == '') {
			$transaction->base_amount = convert_currency($transaction->account->account_currency, base_currency(), $transaction->amount);
		}
		$transaction->payer_payee_id = $request->input('payer_payee_id');
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

		$transaction->amount_usd = $request->input('amount_usd');
		$transaction->amount_peso = $request->input('amount_pesos');

		$transaction->usd = $request->input('usd');
		$transaction->tasa = $request->input('tasa');

		$transaction->save();

		//Set Related Data
		$transaction->trans_date = date('d M, Y', strtotime($transaction->trans_date));
		$transaction->amount = currency() . " " . decimalPlace($transaction->amount);
		$transaction->account_id = $transaction->account->account_title;
		$transaction->chart_id = $transaction->income_type->name;
		$transaction->payer_payee_id = isset($transaction->payer->contact_name) ? $transaction->payer->contact_name : '';
		$transaction->payment_method_id = $transaction->payment_method->name;

		// si es el pago de una venta modificar

		if (!empty($transaction->invoice_id)) {
			$sumMont = Transaction::where('invoice_id', $transaction->invoice_id)->sum('amount');
			$inv = Invoice::where('id', $transaction->invoice_id)->update(['paid' => $sumMont]);
			// dd(Invoice::find($transaction->invoice_id));
		}



		if (!$request->ajax()) {
			return redirect('income')->with('success', _lang('Updated Sucessfully'));
		} else {
			return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated Sucessfully'), 'data' => $transaction]);
		}
	}

	public function calendar()
	{
		$transactions = Transaction::where("company_id", company_id())
			->where("type", "income")
			->orderBy("id", "desc")->get();
		return view('backend.accounting.income.calendar', compact('transactions'));
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
		$transaction = Transaction::where("id", $id)->first(); //->where("company_id", company_id())


		//si existen transacciones y pagadas desde otras transacciones
		$trs = Transaciones_cotizaciones::where('idTransactionNew', $id);

		if (!empty($trs)) {
			foreach ($trs->get() as $tr) {
				// dd($tr);
				$t = Transaction::find($tr->idTransactionOld);
				$t->amount = $t->amount + $tr->monto;
				$t->base_amount = $t->base_amount + $tr->monto;
				$t->save();
			}
			$trs->delete();
		}






		//If Invoice Exists
		if ($transaction->invoice_id != null) {
			$invoice = Invoice::find($transaction->invoice_id);
			$invoice->paid = $invoice->paid - $transaction->base_amount;
			if (round($invoice->paid, 2) > 0 && (round($invoice->paid, 2) < $invoice->grand_total)) {
				$invoice->status = 'Partially_Paid';
			} else {
				$invoice->status = 'Unpaid';
			}
			$invoice->save();
		}

		//eliminar transactions asociada
		if (!empty($transaction->trans_asoc)) {
			$t_asoc = Transaction::find($transaction->trans_asoc);
			if ($t_asoc) {
				$t_asoc->delete();
			}
		}

		$transaction->delete();
		DB::commit();
		return redirect('income')->with('success', _lang('Removed Sucessfully'));
	}

	public function agregarAjuste(Request $request)
	{
		$validator = Validator::make($request->all(), [
			'trans_date' => 'required',
			'amount' => 'required|numeric',
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

		$type = $request->input('tipo_ajuste');
		if ($type == 'si+' || $type == 'co+')
			$amount =  $request->input('amount');
		else 
			if ($type == 'si-' || $type == 'co-')
			$amount =  $request->input('amount') * -1;

		$transaction = new Transaction();
		$transaction->trans_date = $request->input('trans_date');
		$transaction->account_id = $request->input('payer_payee_id');
		//$transaction->account_id = $request->input('account_id');
		//$transaction->chart_id = $request->input('chart_id');
		$transaction->type = 'cc';
		$transaction->dr_cr =  $type;
		$transaction->amount = $amount;
		$transaction->base_amount = $amount;
		//$transaction->base_amount = convert_currency($transaction->account->account_currency, base_currency(), $transaction->amount);
		$transaction->payer_payee_id = $request->input('payer_payee_id');
		//$transaction->payment_method_id = $request->input('payment_method_id');
		//$transaction->reference = $request->input('reference');
		//$transaction->razon_social = $request->input('razon_social');
		//$transaction->tipo_comprobante_id = $request->input('tipo_comprobante_id');
		//$transaction->imputar_a = $request->input('imputar_a');
		//$transaction->detalle_rubro = $request->input('detalle_rubro');
		//$transaction->banco = $request->input('banco');
		//$transaction->cheque_nro = $request->input('cheque_nro');
		//$transaction->cheque_vencimiento = $request->input('cheque_vencimiento');
		//$transaction->cheque_entregado_a = $request->input('cheque_entregado_a');
		$transaction->note = $request->input('detalle');
		$transaction->company_id = $request->input('company_id'); //company_id();
		//$transaction->attachment = $attachment;

		// if ($transaction->imputar_a == 'distribuir') {
		// 	$company = Company::where('business_name', 'A dividir')->first();
		// 	$transaction->company_id = $company->id;
		// }

		// if ($transaction->imputar_a == 'triunvirato') {
		// 	$company = Company::where('business_name', 'Triunvirato')->first();
		// 	$transaction->company_id = $company->id;
		// }

		// if ($transaction->imputar_a == 'pentacar') {
		// 	$company = Company::where('business_name', 'Pentacar')->first();
		// 	$transaction->company_id = $company->id;
		// }
		// if ($transaction->imputar_a == 'paternal') {
		// 	$company = Company::where('business_name', 'Paternal')->first();
		// 	$transaction->company_id = $company->id;
		// }

		// $transaction->usd = $request->input('usd');
		// $transaction->tasa = $request->input('tasa');

		$transaction->save();

		// //Set Related Data
		// $transaction->trans_date = date('d M, Y', strtotime($transaction->trans_date));
		// $transaction->amount = currency() . " " . decimalPlace($transaction->amount);
		// $transaction->account_id = $transaction->account->account_title;
		// $transaction->chart_id = $transaction->income_type->name;
		// $transaction->payer_payee_id = isset($transaction->payer->name) ? $transaction->payer->name : '';
		// $transaction->payment_method_id = $transaction->payment_method->name;

		if (!$request->ajax()) {
			return redirect('contacts/' . $id)->with('success', _lang('Agregado Sucessfully'));
		} else {
			return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved Sucessfully'), 'data' => $transaction]);
		}
	}
}
