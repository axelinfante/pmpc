<?php

namespace App\Http\Controllers;

use App\Company;
use App\Transaction;
use DB;
use Illuminate\Http\Request;


class ReportController extends Controller
{

    public function __construct()
    {
        date_default_timezone_set(get_company_option('timezone', get_option('timezone', 'Asia/Dhaka')));
    }

    public function account_statement(Request $request, $view = "")
    {

        if ($view == '') {
            return view('backend.accounting.reports.account_statement');
        } else if ($view == "view") {
            $data       = array();
            $dr_cr      = $request->trans_type;
            $date1      = $request->date1;
            $date2      = $request->date2;
            $account    = $request->account;
            $company_id = company_id();

            if ($dr_cr == "dr") {
                $data['report_data'] = DB::select("SELECT opening_date as date,'Account Opening Balance' as note,'' as debit,opening_balance as credit FROM accounts WHERE id='$account'
			 UNION ALL
			 SELECT '$date1' as date,'Opening Balance' as note,(SELECT IFNULL(SUM(amount),0) as credit FROM transactions WHERE dr_cr='dr' AND trans_date<'$date1' AND account_id='$account') as debit, (SELECT IFNULL(SUM(amount),0) as credit FROM transactions WHERE dr_cr='cr' AND trans_date<'$date1' AND account_id='$account') as credit
			 UNION ALL
			 SELECT trans_date,note,IF(dr_cr='dr',amount,NULL) as debit,IF(dr_cr='cr',amount,NULL) as credit FROM transactions WHERE trans_date BETWEEN '$date1' AND '$date2' AND account_id='$account' AND dr_cr='dr'");
            } else if ($dr_cr == "cr") {
                $data['report_data'] = DB::select("SELECT opening_date as date,'Account Opening Balance' as note,'' as debit,opening_balance as credit FROM accounts WHERE id='$account'
				UNION ALL
				SELECT '$date1' as date,'Opening Balance' as note,(SELECT IFNULL(SUM(amount),0) as credit FROM transactions WHERE dr_cr='dr' AND trans_date<'$date1' AND account_id='$account') as debit, (SELECT IFNULL(SUM(amount),0) as credit FROM transactions WHERE dr_cr='cr' AND trans_date<'$date1' AND account_id='$account') as credit
				UNION ALL
				SELECT trans_date,note,IF(dr_cr='dr',amount,NULL) as debit,IF(dr_cr='cr',amount,NULL) as credit FROM transactions WHERE trans_date BETWEEN '$date1' AND '$date2' AND account_id='$account' AND dr_cr='cr'");
            } else if ($dr_cr == "all") {
                $data['report_data'] = DB::select("SELECT opening_date as date,'Account Opening Balance' as note,0 as debit,opening_balance as credit FROM accounts WHERE id='$account'
				UNION ALL
				SELECT '$date1' as date,'Opening Balance' as note,(SELECT IFNULL(SUM(amount),0) as credit FROM transactions WHERE dr_cr='dr' AND trans_date<'$date1' AND account_id='$account') as debit, (SELECT IFNULL(SUM(amount),0) as credit FROM transactions WHERE dr_cr='cr' AND date<'$date1' AND account_id='$account') as credit
				UNION ALL
				SELECT trans_date,note,IF(dr_cr='dr',amount,NULL) as debit,IF(dr_cr='cr',amount,NULL) as credit FROM transactions WHERE trans_date BETWEEN '$date1' AND '$date2' AND account_id='$account'");
            }

            $data['dr_cr']   = $request->trans_type;
            $data['date1']   = $request->date1;
            $data['date2']   = $request->date2;
            $data['account'] = $request->account;
            $data['acc']     = DB::table('accounts')
                ->where('id', $request->account)
                ->where('company_id', $company_id)
                ->first();

            return view('backend.accounting.reports.account_statement', $data);
        }
    }

    //Day Wise Income Report
    public function day_wise_income(Request $request, $view = "")
    {
        if ($view == '') {
            return view('backend.accounting.reports.day_wise_income_report');
        } else {
            $date1      = $request->date1;
            $date2      = $request->date2;
            $account    = $request->account;
            $company_id = company_id();
            $data       = array();

            $data['report_data'] = DB::select("SELECT transactions.trans_date,chart_of_accounts.name as income_type,transactions.note,accounts.account_title as account,transactions.amount
			FROM transactions JOIN accounts ON transactions.account_id = accounts.id LEFT JOIN chart_of_accounts ON transactions.chart_id=chart_of_accounts.id
			WHERE transactions.trans_date BETWEEN '$date1' AND '$date2' AND transactions.dr_cr='cr' AND accounts.id='$account' AND transactions.company_id='$company_id'
			UNION ALL
			SELECT '$date2','Total Amount','','',SUM(transactions.amount) as amount FROM transactions,accounts WHERE transactions.account_id = accounts.id AND transactions.trans_date
			BETWEEN '$date1' AND '$date2' AND transactions.dr_cr='cr' AND accounts.id='$account' AND transactions.company_id='$company_id'");

            $data['date1']   = $request->date1;
            $data['date2']   = $request->date2;
            $data['account'] = $request->account;
            $data['acc']     = DB::table('accounts')
                ->where('id', $request->account)
                ->where('company_id', $company_id)
                ->first();
            return view('backend.accounting.reports.day_wise_income_report', $data);
        }
    }

    //Date Wise Income Report
    public function date_wise_income(Request $request, $view = "")
    {
        if ($view == '') {
            return view('backend.accounting.reports.date_wise_income_report');
        } else {
            $date1      = $request->date1;
            $date2      = $request->date2;
            $account    = $request->account;
            $company_id = company_id();
            $data       = array();

            $data['report_data'] = DB::select("SELECT DATE_FORMAT(transactions.trans_date,'%d %b, %Y') as trans_date,SUM(transactions.amount) as amount
		   FROM transactions WHERE transactions.trans_date BETWEEN '$date1' AND '$date2' AND transactions.dr_cr='cr' AND transactions.account_id='$account' AND transactions.company_id='$company_id' GROUP BY transactions.trans_date
		   UNION ALL
		   SELECT '$date2',SUM(transactions.amount) as amount FROM transactions,accounts WHERE transactions.account_id=accounts.id AND transactions.trans_date BETWEEN '$date1' AND '$date2' AND transactions.dr_cr='cr' AND transactions.company_id='$company_id'");

            $data['date1']   = $request->date1;
            $data['date2']   = $request->date2;
            $data['account'] = $request->account;
            $data['acc']     = DB::table('accounts')
                ->where('id', $request->account)
                ->where('company_id', $company_id)
                ->first();
            return view('backend.accounting.reports.date_wise_income_report', $data);
        }
    }

    //Day Wise Expense Report
    public function day_wise_expense(Request $request, $view = "")
    {
        if ($view == '') {
            return view('backend.accounting.reports.day_wise_expense_report');
        } else {
            $date1      = $request->date1;
            $date2      = $request->date2;
            $account    = $request->account;
            $company_id = company_id();
            $data       = array();

            $data['report_data'] = DB::select("SELECT transactions.trans_date,chart_of_accounts.name as expense_type,transactions.note,accounts.account_title as account,transactions.amount
			FROM transactions JOIN accounts ON transactions.account_id = accounts.id LEFT JOIN chart_of_accounts ON transactions.chart_id=chart_of_accounts.id
			WHERE transactions.trans_date BETWEEN '$date1' AND '$date2' AND accounts.id='$account' AND transactions.dr_cr='dr' AND transactions.company_id='$company_id'
			UNION ALL
			SELECT '$date2','Total Amount','','',SUM(transactions.amount) as amount FROM transactions,accounts WHERE transactions.account_id = accounts.id AND transactions.trans_date BETWEEN '$date1' AND '$date2' AND accounts.id='$account' AND transactions.dr_cr='dr' AND transactions.company_id='$company_id'");

            $data['date1']   = $request->date1;
            $data['date2']   = $request->date2;
            $data['account'] = $request->account;
            $data['acc']     = DB::table('accounts')
                ->where('id', $request->account)
                ->where('company_id', $company_id)
                ->first();
            return view('backend.accounting.reports.day_wise_expense_report', $data);
        }
    }

    //Date Wise Expense Report
    public function date_wise_expense(Request $request, $view = "")
    {
        if ($view == '') {
            return view('backend.accounting.reports.date_wise_expense_report');
        } else {
            $date1      = $request->date1;
            $date2      = $request->date2;
            $account    = $request->account;
            $company_id = company_id();
            $data       = array();

            $data['report_data'] = DB::select("SELECT DATE_FORMAT(transactions.trans_date,'%d %b, %Y') as trans_date,SUM(transactions.amount) as amount
		   FROM transactions WHERE transactions.trans_date BETWEEN '$date1' AND '$date2' AND transactions.account_id='$account' AND transactions.dr_cr='dr' AND transactions.company_id='$company_id' GROUP BY transactions.trans_date
		   UNION ALL
		   SELECT '$date2',SUM(transactions.amount) as amount FROM transactions,accounts WHERE transactions.account_id=accounts.id AND transactions.trans_date BETWEEN '$date1' AND '$date2' AND transactions.account_id='$account' AND transactions.dr_cr='dr' AND transactions.company_id='$company_id'");

            $data['date1']   = $request->date1;
            $data['date2']   = $request->date2;
            $data['account'] = $request->account;
            $data['acc']     = DB::table('accounts')
                ->where('id', $request->account)
                ->where('company_id', $company_id)
                ->first();
            return view('backend.accounting.reports.date_wise_expense_report', $data);
        }
    }

    public function transfer_report(Request $request, $view = "")
    {
        if ($view == '') {
            return view('backend.accounting.reports.transfer_report');
        } else {
            $date1               = $request->date1;
            $date2               = $request->date2;
            $company_id          = company_id();
            $data                = array();
            $data['report_data'] = DB::select("SELECT transactions.trans_date,transactions.note,accounts.account_title as account,accounts.account_currency, dr_cr,
		   IF(transactions.dr_cr='dr',transactions.amount,NULL) as debit,IF(transactions.dr_cr='cr',transactions.amount,NULL) as credit
		   FROM transactions,accounts WHERE transactions.account_id=accounts.id AND transactions.trans_date BETWEEN '$date1' AND '$date2'
		   AND transactions.type='transfer' AND transactions.company_id='$company_id'");

            $data['date1'] = $request->date1;
            $data['date2'] = $request->date2;
            return view('backend.accounting.reports.transfer_report', $data);
        }
    }

    //Income Vs Expense Report
    public function income_vs_expense(Request $request, $view = '')
    {
        if ($view == '') {
            return view('backend.accounting.reports.income_vs_expense_report');
        } else if ($view == 'view') {
            $date1      = $request->date1;
            $date2      = $request->date2;
            $account    = $request->account;
            $company_id = company_id();

            $data['report_data'] = $this->get_income_vs_expense($date1, $date2, $account);

            $data['date1']   = $request->date1;
            $data['date2']   = $request->date2;
            $data['account'] = $request->account;
            $data['acc']     = DB::table('accounts')
                ->where('id', $request->account)
                ->where('company_id', $company_id)
                ->first();
            return view('backend.accounting.reports.income_vs_expense_report', $data);
        }
    }

    //Report By Payer
    public function report_by_payer(Request $request, $view = "")
    {
        if ($view == '') {
            return view('backend.accounting.reports.report_by_payer');
        } else {
            $date1      = $request->date1;
            $date2      = $request->date2;
            $account    = $request->account;
            $payer_id   = $request->payer_id;
            $company_id = company_id();
            $data       = array();

            $data['report_data'] = DB::select("SELECT trans_date, chart_of_accounts.name as c_type,transactions.note,accounts.account_title as account,transactions.amount,contacts.contact_name as payer
		   FROM transactions,accounts,contacts,chart_of_accounts WHERE transactions.account_id=accounts.id AND transactions.payer_payee_id=contacts.id
		   AND transactions.chart_id=chart_of_accounts.id AND transactions.trans_date BETWEEN '$date1' AND '$date2' AND accounts.id='$account' AND transactions.dr_cr='cr' AND transactions.payer_payee_id='$payer_id' AND transactions.company_id='$company_id'
		   UNION ALL
		   SELECT '$date2','','TOTAL AMOUNT','',SUM(transactions.amount) as amount,'' FROM transactions
		   WHERE transactions.trans_date BETWEEN '$date1' AND '$date2' AND transactions.account_id='$account' AND transactions.dr_cr='cr' AND transactions.payer_payee_id='$payer_id' AND transactions.company_id='$company_id'");

            $data['date1']    = $request->date1;
            $data['date2']    = $request->date2;
            $data['payer_id'] = $request->payer_id;
            $data['account']  = $request->account;
            $data['acc']      = DB::table('accounts')
                ->where('id', $request->account)
                ->where('company_id', $company_id)
                ->first();
            return view('backend.accounting.reports.report_by_payer', $data);
        }
    }

    //Report By Payee
    public function report_by_payee(Request $request, $view = "")
    {
        if ($view == '') {
            return view('backend.accounting.reports.report_by_payee');
        } else {
            $date1      = $request->date1;
            $date2      = $request->date2;
            $account    = $request->account;
            $payee_id   = $request->payee_id;
            $company_id = company_id();
            $data       = array();

            $data['report_data'] = DB::select("SELECT trans_date,chart_of_accounts.name as c_type,transactions.note,accounts.account_title as account,transactions.amount,contacts.contact_name as payee
		   FROM transactions,accounts,contacts,chart_of_accounts WHERE transactions.account_id=accounts.id AND transactions.payer_payee_id=contacts.id
		   AND transactions.chart_id=chart_of_accounts.id AND transactions.trans_date BETWEEN '$date1' AND '$date2' AND accounts.id='$account' AND transactions.dr_cr='dr' AND transactions.payer_payee_id='$payee_id' AND transactions.company_id='$company_id'
		   UNION ALL
		   SELECT '$date2','','TOTAL AMOUNT','',SUM(transactions.amount) as amount,'' FROM transactions
		   WHERE transactions.trans_date BETWEEN '$date1' AND '$date2' AND transactions.account_id='$account' AND transactions.dr_cr='dr' AND transactions.payer_payee_id='$payee_id' AND transactions.company_id='$company_id'");

            $data['date1']    = $request->date1;
            $data['date2']    = $request->date2;
            $data['payee_id'] = $request->payee_id;
            $data['account']  = $request->account;
            $data['acc']      = DB::table('accounts')
                ->where('id', $request->account)
                ->where('company_id', $company_id)
                ->first();
            return view('backend.accounting.reports.report_by_payee', $data);
        }
    }

    public function tax_report(Request $request, $view = '')
    {
        if ($view == '') {
            return view('backend.accounting.reports.tax_report');
        } else {
            $date1      = $request->date1;
            $date2      = $request->date2;
            $company_id = company_id();
            $data       = array();

            //Sales Tax
            $data['sales_taxes'] = DB::select("SELECT taxs.id, taxs.tax_name, taxs.rate, taxs.type, SUM(invoice_items.sub_total) as sales_amount, SUM(invoice_item_taxes.amount) as sales_tax FROM invoice_items LEFT JOIN invoice_item_taxes ON invoice_items.id=invoice_item_taxes.invoice_item_id AND invoice_items.company_id = $company_id JOIN invoices ON invoices.id=invoice_items.invoice_id AND invoices.status='Paid' AND invoices.invoice_date >= '$date1' AND invoices.invoice_date <= '$date2' RIGHT JOIN taxs ON taxs.id=invoice_item_taxes.tax_id AND taxs.company_id = $company_id GROUP BY taxs.id");

            //Sales Return Tax
            $data['sales_return_taxes'] = DB::select("SELECT taxs.id, taxs.tax_name, taxs.rate, taxs.type, SUM(sales_return_items.sub_total) as sales_return_amount, SUM(sales_return_item_taxes.amount) as sales_return_tax FROM sales_return_items LEFT JOIN sales_return_item_taxes ON sales_return_items.id=sales_return_item_taxes.sales_return_item_id AND sales_return_items.company_id = $company_id JOIN sales_return ON sales_return.id=sales_return_items.sales_return_id AND sales_return.return_date >= '$date1' AND sales_return.return_date <= '$date2' RIGHT JOIN taxs ON taxs.id=sales_return_item_taxes.tax_id AND taxs.company_id = $company_id GROUP BY taxs.id");

            //Purchase Order Tax
            $data['purchase_taxes'] = DB::select("SELECT taxs.id, taxs.tax_name, taxs.rate, taxs.type, SUM(purchase_order_items.sub_total) as purchase_amount, SUM(purchase_order_item_taxes.amount) as purchase_tax FROM purchase_order_items LEFT JOIN purchase_order_item_taxes ON purchase_order_items.id=purchase_order_item_taxes.purchase_order_item_id AND purchase_order_items.company_id = $company_id JOIN purchase_orders ON purchase_orders.id=purchase_order_items.purchase_order_id AND purchase_orders.order_date >= '$date1' AND purchase_orders.order_date <= '$date2' RIGHT JOIN taxs ON taxs.id=purchase_order_item_taxes.tax_id AND taxs.company_id = $company_id GROUP BY taxs.id");

            //Purchase Return Tax
            $data['purchase_return_taxes'] = DB::select("SELECT taxs.id, taxs.tax_name, taxs.rate, taxs.type, SUM(purchase_return_items.sub_total) as purchase_return_amount, SUM(purchase_return_item_taxes.amount) as purchase_return_tax FROM purchase_return_items LEFT JOIN purchase_return_item_taxes ON purchase_return_items.id=purchase_return_item_taxes.purchase_return_item_id AND purchase_return_items.company_id = $company_id JOIN purchase_return ON purchase_return.id=purchase_return_items.purchase_return_id AND purchase_return.return_date >= '$date1' AND purchase_return.return_date <= '$date2' RIGHT JOIN taxs ON taxs.id=purchase_return_item_taxes.tax_id AND taxs.company_id = $company_id GROUP BY taxs.id");

            $data['date1']    = $request->date1;
            $data['date2']    = $request->date2;

            return view('backend.accounting.reports.tax_report', $data);
        }
    }

    public function report_by_day(Request $request)
    {
        $data = [];
        $company = $request->company;
        $date1      = $request->date1;
        $date2      = $request->date2;

        $data['current_day_income_expense_by_account'] = current_day_income_expense_by_account($company, $date1, $date2);
        $data['current_day_income_expense_by_account_usd'] = current_day_income_expense_by_account_usd($company, $date1, $date2);

        // a dividir
        $companyDividir = Company::where('business_name', 'A dividir')->first();
        //pesos
        $a_dividir = Transaction::selectRaw("case when dr_cr = 'cr'then SUM(base_amount)end as totalIngreso, (select sum(base_amount)  FROM `transactions` as t where dr_cr = 'dr' and t.account_id = transactions.account_id and t.payment_method_id = transactions.payment_method_id ) as totalEgreso")->where('company_id', $companyDividir->id)->where('dr_cr', 'cr')->where('usd', null);

        if (!$date1 and !$date2) {

            $a_dividir->where("trans_date", date('Y-m-d'));
        } else {
            $a_dividir->whereBetween("trans_date", [$date1, $date2]);
        }

        $data['a_dividir'] = $a_dividir->first();
        //dd($data['a_dividir']);

        //usd

        $a_dividir = Transaction::selectRaw("case when dr_cr = 'cr'then SUM(base_amount)end as totalIngreso, (select sum(base_amount)  FROM `transactions` as t where dr_cr = 'dr' and t.account_id = transactions.account_id and t.payment_method_id = transactions.payment_method_id ) as totalEgreso")->where('company_id', $companyDividir->id)->where('dr_cr', 'cr')->where('usd', 1);

        if (!$date1 and !$date2) {

            $a_dividir->where("trans_date", date('Y-m-d'));
        } else {
            $a_dividir->whereBetween("trans_date", [$date1, $date2]);
        }

        $data['a_dividir_usd'] = $a_dividir->first();


        if ($request->date1) {
            $data['date1']    = $request->date1;
        }
        if ($request->date2) {
            $data['date2']    = $request->date2;
        }

        if ($request->company) {
            $data['company']    = $request->company;
        }


        //dd($data['current_day_income_expense_by_account']);
        return view('backend.accounting.reports.report_by_day', $data);
    }

    public function report_by_day_new(Request $request)
    {
        $data = [];
    
        // Determinar la compañía
        if ($request->company) {
            $data['company'] = $request->company;
        } else {
            $data['company'] = company_id();
        }
        $company = $data['company'];
    
        $idUbicacion = $request->idUbicacion;
        $account_currency = $request->account_currency;
        $account = $request->account;
    
        $date1 = $request->date1;
        $date2 = $request->date2;
    
        if (!$date1) {
            $date1 = date('Y-m-d');
        }
        if (!$date2) {
            $date2 = date('Y-m-d');
        }
    
        $query = DB::table('transactions as t')
            ->join('companies as c', 'c.id', '=', 't.company_id')
            ->join('accounts', function ($join) {
                $join->on('accounts.id', '=', 't.account_id')
                    ->on('accounts.company_id', '=', 't.company_id');
            })
            ->join('payment_methods', function ($join) {
                $join->on('payment_methods.id', '=', 't.payment_method_id');
                    //->on('payment_methods.company_id', '=', 't.company_id');
            })
            ->select(
                't.company_id',
                'c.business_name',
                'accounts.id',
                'accounts.account_title',
                't.payment_method_id',
                'payment_methods.name as payment_method',
                'accounts.idUbicacion',
                'accounts.account_currency',
                DB::raw("DATE_FORMAT(t.trans_date, '%d-%m-%Y') as trans_date"),
                // Cálculo ingresos en ARS
                DB::raw("SUM(CASE WHEN (t.dr_cr = 'cr' or t.dr_cr = 'cc') AND accounts.account_currency = 'ARS' THEN COALESCE(t.amount_peso, t.base_amount) ELSE 0 END) AS creditoARS"),
                // Cálculo ingresos en USD
                //DB::raw("SUM(CASE WHEN t.dr_cr = 'cr' AND accounts.account_currency = 'USD' THEN t.amount_usd ELSE 0 END) AS creditoUSD"),
                DB::raw("SUM(CASE WHEN (t.dr_cr = 'cr' or t.dr_cr = 'cc') AND accounts.account_currency = 'USD' THEN COALESCE(t.amount_usd, t.base_amount) ELSE 0 END) AS creditoUSD"),
                // Cálculo egresos en ARS
                DB::raw("SUM(CASE WHEN (t.dr_cr = 'dr' or t.dr_cr = 'cc_expense') AND accounts.account_currency = 'ARS' THEN COALESCE(t.amount_peso, t.base_amount) ELSE 0 END) AS debitoARS"),
                // Cálculo egresos en USD
                //DB::raw("SUM(CASE WHEN t.dr_cr = 'dr' AND accounts.account_currency = 'USD' THEN t.amount_usd ELSE 0 END) AS debitoUSD"),
                DB::raw("SUM(CASE WHEN (t.dr_cr = 'dr' or t.dr_cr = 'cc_expense') AND accounts.account_currency = 'USD' THEN COALESCE(t.amount_usd, t.base_amount) ELSE 0 END) AS debitoUSD"),

                // Cálculo del saldo final en ARS
                DB::raw("SUM(CASE WHEN (t.dr_cr = 'cr' or t.dr_cr = 'cc') AND accounts.account_currency = 'ARS' THEN COALESCE(t.amount_peso, t.base_amount) ELSE 0 END) - SUM(CASE WHEN (t.dr_cr = 'dr' or t.dr_cr = 'cc') AND accounts.account_currency = 'ARS' THEN COALESCE(t.amount_peso, t.base_amount) ELSE 0 END) AS saldoARS"),
                // Cálculo del saldo final en USD
                //DB::raw("SUM(CASE WHEN t.dr_cr = 'cr' AND accounts.account_currency = 'USD' THEN t.amount_usd ELSE 0 END) - SUM(CASE WHEN t.dr_cr = 'dr' AND accounts.account_currency = 'USD' THEN t.amount_usd ELSE 0 END) AS saldoUSD")
                DB::raw("SUM(CASE WHEN (t.dr_cr = 'cr' or t.dr_cr = 'cc') AND accounts.account_currency = 'USD' THEN COALESCE(t.amount_usd, t.base_amount) ELSE 0 END) - SUM(CASE WHEN (t.dr_cr = 'dr' or t.dr_cr = 'cc_expense') AND accounts.account_currency = 'ARS' THEN COALESCE(t.amount_usd, t.base_amount) ELSE 0 END) AS saldoUSD"),

                )
            ->whereBetween("t.trans_date", [$date1, $date2])
           // ->where('t.type', '<>', 'cc')
            ->whereNotIn('t.account_id', [27,28])
            ->when(($company!=""), function ($query) use ($company) {
                return $query->where('t.company_id', $company);
            })
            ->when(($idUbicacion!=""), function ($query) use ($idUbicacion) {
                return $query->where('accounts.idUbicacion', $idUbicacion);
            })
            ->when(($account!=""), function ($query) use ($account) {
                return $query->where('accounts.id', $account);
            })
            ->when(($account_currency!=""), function ($query) use ($account_currency) {
                return $query->where('accounts.account_currency', $account_currency);
            })
            ->groupBy(
                't.company_id',
                'c.business_name',
                'accounts.id',
                'accounts.account_title',
                't.payment_method_id',
                'accounts.idUbicacion',
                'accounts.account_currency',
                't.trans_date'
            )
            ->orderBy('t.trans_date', 'desc')
            ->get();
    
        $data['date1'] = $date1;
        $data['date2'] = $date2;
        $data['idUbicacion'] = $idUbicacion;
        $data['account_currency'] = $account_currency;
        $data['account'] = $account;
    
        $data['current_day_income_expense_by_account'] = $query->map(function ($item) {
            return (array) $item;
        })->toArray();
    
        return view('backend.accounting.reports.report_by_day_new', $data);
    }
    

    //private methods
    private function get_income_vs_expense($from_date, $to_date, $account)
    {
        $company_id = company_id();

        $income = DB::select("SELECT id FROM transactions
				  WHERE dr_cr='cr' AND company_id='$company_id' AND trans_date between '" . $from_date . "'
				  AND '" . $to_date . "' AND transactions.account_id='$account'");

        $expense = DB::select("SELECT id FROM transactions
				  WHERE dr_cr='dr' AND company_id='$company_id' AND trans_date between '" . $from_date . "'
				  AND '" . $to_date . "' AND transactions.account_id='$account'");

        if (count($income) > count($expense)) {
            return DB::select("SELECT income.*,expense.* FROM (SELECT @a:=@a+1 as sl,DATE_FORMAT(transactions.trans_date,'%d %b, %Y') income_date,transactions.note as income_note,chart_of_accounts.name as income_type,transactions.amount income_amount
			    FROM transactions,accounts,chart_of_accounts, (SELECT @a:= 0) AS a WHERE
				transactions.account_id=accounts.id AND transactions.chart_id=chart_of_accounts.id AND transactions.dr_cr='cr'
				AND transactions.company_id='$company_id' AND trans_date between '$from_date' AND '$to_date' AND transactions.account_id='$account') as income LEFT JOIN
				(SELECT @b:=@b+1 as sl,DATE_FORMAT(transactions.trans_date,'%d %b, %Y') expense_date,transactions.note as expense_note,chart_of_accounts.name as expense_type,transactions.amount expense_amount FROM transactions,accounts,chart_of_accounts,
				(SELECT @b:= 0) AS a WHERE transactions.account_id=accounts.id AND transactions.chart_id=chart_of_accounts.id AND transactions.dr_cr='dr'
				AND transactions.company_id='$company_id' AND trans_date between '$from_date' AND '$to_date' AND transactions.account_id='$account') as expense ON income.sl=expense.sl");
        } else {
            return DB::select("SELECT income.*,expense.* FROM (SELECT @a:=@a+1 as sl,DATE_FORMAT(transactions.trans_date,'%d %b, %Y') income_date,transactions.note as income_note,chart_of_accounts.name as income_type,transactions.amount income_amount
			    FROM transactions,accounts,chart_of_accounts, (SELECT @a:= 0) AS a WHERE
				transactions.account_id=accounts.id AND transactions.chart_id=chart_of_accounts.id AND transactions.dr_cr='cr'
				AND transactions.company_id='$company_id' AND trans_date between '$from_date' AND '$to_date' AND transactions.account_id='$account') as income RIGHT JOIN
				(SELECT @b:=@b+1 as sl,DATE_FORMAT(transactions.trans_date,'%d %b, %Y') expense_date,transactions.note as expense_note,chart_of_accounts.name as expense_type,transactions.amount expense_amount FROM transactions,accounts,chart_of_accounts,
				(SELECT @b:= 0) AS a WHERE transactions.account_id=accounts.id AND transactions.chart_id=chart_of_accounts.id AND transactions.dr_cr='dr'
				AND transactions.company_id='$company_id' AND trans_date between '$from_date' AND '$to_date' AND transactions.account_id='$account') as expense ON income.sl=expense.sl");
        }
    }
}
