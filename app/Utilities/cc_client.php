<?php
/**
 * Created by PhpStorm.
 * User: MALZ
 * Date: 31/10/2023
 * Time: 9:26
 */

namespace App\Utilities;

use App\ChartOfAccount;
use App\Invoice;
use App\Mail\InvoiceReceiptMail;
use App\PaymentMethod;
use App\Transaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

trait cc_client
{
    public function disponibleCc($idClient)
    {
        //$company_id = company_id();
//        $sub1 = Transaction::where('type','cc')->where('payer_payee_id',$idClient)
//            ->where("company_id", $company_id)->sum('amount');

        $sub1 = Transaction::where('type','cc_expense')->where('payer_payee_id',$idClient)
//            ->where("company_id", $company_id)
            ->sum('amount');


//        $sub2 = Invoice::where('client_id',$idClient)->where('status','Paid')->orwhere('status','Partially_Paid')
////            ->where("company_id", $company_id)
//            ->sum('grand_total');
//
//        $sub3 = Invoice::where('client_id',$idClient)->where('status','Paid')->orwhere('status','Partially_Paid')
////            ->where("company_id", $company_id)
//            ->sum('paid');

        $sub4 = Transaction::where('type','cc')->where('payer_payee_id',$idClient)->where('invoice_id',null)
//            ->where("company_id", $company_id)
            ->sum('amount');
        $sub5 = Transaction::where('type','cc')->where('payer_payee_id',$idClient)->where('invoice_id','>',0)
            ->sum('amount');

        //dd( $sub3 - $sub2);

//        $total =  ($sub3 - $sub2  ) - $sub1 + $sub4;
        $total =  ($sub4 + $sub5) - $sub1;
        return $total;
    }

    public function pago_desde_cc($id_invoice,$id_client)
    {
        $result = false;
        //buscar datos de la factura
        $invoice = Invoice::find($id_invoice);

        //$montoFactura = $invoice->grand_total;

        // comprobar disponibilidad
        $monto_disponible_cc = $this->disponibleCc($id_client);

        if($monto_disponible_cc > 0 && $invoice->status != 'Paid'){
            $attachment = "";


            DB::beginTransaction();

            $company_id = company_id();


            //Update Invoice Table
//            $invoice = Invoice::where("id", $request->input('invoice_id'))
//                ->where("company_id", $company_id)->first();

            $rubro = get_table('chart_of_accounts',array("type="=>"income",
                "AND company_id="=>company_id(), 'AND name =' => 'Venta'
            ));
            $idRubroVenta = null;
            if(!empty($rubro[0])) {
                $idRubroVenta =$rubro[0]->id;
            }

            $methodP = PaymentMethod::where('name','like','%Gasto cc')->first();

                $transaction                    = new Transaction();
                $transaction->trans_date        = date('Y-m-d');
                //$transaction->account_id        = 1; //$request->input('account_id');
                $transaction->chart_id          = $idRubroVenta;//$request->input('chart_id');
                $transaction->type              = 'cc_expense';
                $transaction->dr_cr             = 'cc';

                $transaction->base_amount       = $transaction->amount;//convert_currency
            //($transaction->account->account_currency,
               // base_currency(), $transaction->amount);
                $transaction->payer_payee_id    = $id_client;//$request->input('client_id');
                $transaction->payment_method_id = $methodP->id;
                $transaction->invoice_id        = $invoice->id;
                $transaction->reference         = '';//$request->input('reference');
                $transaction->note              = '';//$request->input('note');
                $transaction->attachment        = $attachment;
                $transaction->company_id        = $company_id;

                if($monto_disponible_cc <= $invoice->grand_total) {
                    $transaction->amount            = $monto_disponible_cc;
                    $invoice->paid = $invoice->paid + $transaction->amount;
                }else {
                    $transaction->amount            = $invoice->grand_total;
                    $invoice->paid = $transaction->amount ;
                }

                $transaction->save();


                if (round($invoice->paid, 2) >= $invoice->grand_total) {
                    $invoice->status = 'Paid';
                } else if (round($invoice->paid, 2) >= 0 && (round($invoice->paid, 2) < $invoice->grand_total)) {
                    $invoice->status = 'Partially_Paid';
                }
                $invoice->save();


                //Send Invoice Payment Confrimation to Client
                @ini_set('max_execution_time', 0);
                @set_time_limit(0);
                Overrider::load("Settings");
                $mail              = new \stdClass();
                $mail->subject     = _lang('Invoice Payment');
                $mail->invoice     = $invoice;
                $mail->transaction = $transaction;
                $mail->method      = $transaction->payment_method->name;
                $mail->currency    = currency();

                $result = true;
            }




            try {
                Mail::to($invoice->client->contact_email)->send(new InvoiceReceiptMail($mail));
            } catch (\Exception$e) {
                //Nothing
            }

            DB::commit();
        return $result; //response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Payment
        // was
//made
        // Sucessfully'), 'data' => $transaction]);




    }


    public function devolucion($invoice,$saveInvoice = true,$is_devolucion_product = false, $monto = 0)
    {
        // Verificar si la factura tiene pagos desde saldo a favor (cc_expense)
        $tienePagosDesdeSaldoAFavor = \App\Transaction::where('invoice_id', $invoice->id)
            ->where('type', 'cc_expense')
            ->exists();
        
        // Verificar si la factura tiene pagos con dinero real (income)
        $tienePagosDineroReal = \App\Transaction::where('invoice_id', $invoice->id)
            ->where('type', 'income')
            ->exists();
        
        // Si es una devolución por exceso de pago ($is_devolucion_product = true)
        if ($is_devolucion_product) {
            // Para devoluciones por exceso de pago:
            // 1. Si hay pagos desde saldo a favor Y NO hay pagos con dinero real → NO crear devolución
            // 2. Si hay pagos con dinero real (con o sin saldo a favor) → SÍ crear devolución
            // 3. Si no hay ningún tipo de pago → NO crear devolución
            
            if ($tienePagosDesdeSaldoAFavor && !$tienePagosDineroReal) {
                \Log::info('Factura con pagos solo desde saldo a favor - No se crearán transacciones de devolución por exceso de pago', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client_id' => $invoice->client_id,
                    'monto' => $monto,
                ]);
                return;
            }
            
            if (!$tienePagosDineroReal) {
                \Log::info('Factura sin pagos con dinero real - No se crearán transacciones de devolución por exceso de pago', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client_id' => $invoice->client_id,
                    'monto' => $monto,
                ]);
                return;
            }
            
            // Si llegamos aquí, hay pagos con dinero real → proceder con devolución
            \Log::info('Creando devolución por exceso de pago con dinero real', [
                'invoice_id' => $invoice->id,
                'monto' => $monto,
            ]);
        } else {
            // Para eliminación de facturas ($is_devolucion_product = false):
            // Si hay pagos desde saldo a favor, NO crear transacciones de devolución
            // porque el InvoiceObserver ya se encargará de revertir los pagos automáticamente
            if ($tienePagosDesdeSaldoAFavor) {
                \Log::info('Factura con pagos desde saldo a favor - No se crearán transacciones de devolución', [
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'client_id' => $invoice->client_id,
                ]);
                return;
            }
        }
        
        // Si llegamos aquí, proceder con la devolución normal
        //devolucion
        $methodP = PaymentMethod::where('name', 'like', '%Abono cc')->first();
        if(!$is_devolucion_product) {
            $transactions = Transaction::where('invoice_id',$invoice->id)->get();

           

                foreach ($transactions as $transaction) {
                    
                    $trans = new Transaction();
                    $trans->trans_date = date('Y-m-d');
                    $trans->type = 'cc';
                    $trans->dr_cr = 'cc';
                    $trans->amount = $transaction->amount;
                    $trans->base_amount = convert_currency($trans->account->account_currency, base_currency(), $trans->amount);
                    $trans->payer_payee_id = $invoice->client_id;
                    $trans->payment_method_id = $methodP->id; //$request->input('payment_method_id');
                    

                    $trans->amount_usd = $transaction->amount_usd;
                    $trans->amount_peso = $transaction->amount_peso;

                    $trans->tasa = $transaction->tasa;
                    $trans->usd = $transaction->usd ;

                    //$trans->reference = $request->input('reference');
                    $trans->note = 'Devolución';
            
                    $trans->save();
                }
                
        }else {
                    $trans = new Transaction();
                    $trans->trans_date = date('Y-m-d');
                    $trans->type = 'cc';
                    $trans->dr_cr = 'cc';
                    $trans->amount = $monto;
                    $trans->base_amount = convert_currency($trans->account->account_currency, base_currency(), $trans->amount);
                    $trans->payer_payee_id = $invoice->client_id;
                    $trans->payment_method_id = $methodP->id; //$request->input('payment_method_id');
                    

                    // $trans->amount_usd = $transaction->amount_usd;
                    // $trans->amount_peso = $transaction->amount_peso;

                    // $trans->tasa = $transaction->tasa;
                    // $trans->usd = $transaction->usd ;

                    //$trans->reference = $request->input('reference');
                    $trans->note = 'Devolución por exceso de pago';
            
                    $trans->save();
                    // dd($trans);
        }

        
       

        /// fin
    }

    public function devolucionRetiro($idCuenta,$idEmpresa,$idCliente,$monto,$montoUsd,$montoPeso) {
        $methodP = PaymentMethod::where('name','like','%Gasto cc')->first();
        
        
        $trans = new Transaction();

        $trans->trans_date = date('Y-m-d');
                    $trans->type = 'cc_expense';
                    $trans->dr_cr = 'cc';
                    $trans->amount = $monto;
                    $trans->base_amount = convert_currency($trans->account->account_currency, base_currency(), $trans->amount);
                    $trans->payer_payee_id = $idCliente;
                    $trans->payment_method_id = $methodP->id; //$request->input('payment_method_id');
                    

                    $trans->amount_usd = $montoUsd;
                    $trans->amount_peso = $montoPeso;

                    // $trans->tasa = $transaction->tasa;
                    // $trans->usd = $transaction->usd ;

                    //$trans->reference = $request->input('reference');
                    $trans->note = 'Retiro Cuenta Corriente';
            
                    $trans->save();

                    //----------------------------------------------------------------
                    //sacar de la cuenta enviada

                    $transaction = new Transaction();
                    $transaction->trans_date = date('Y-m-d');
                    $transaction->account_id = $idCuenta;
                    $transaction->chart_id = $request->input('chart_id');
    
                    $transaction->type = 'expense';
                    $transaction->dr_cr = 'dr';
                    $transaction->amount = $request->input('amount');
                    $transaction->amount_usd = $request->input('amount_usd');
                    $transaction->amount_peso = $request->input('amount_pesos');
                    $transaction->base_amount = convert_currency($transaction->account->account_currency, base_currency(), $request->input('amount'));
                    $transaction->payer_payee_id = $request->input('client_id');
                    $transaction->payment_method_id = $request->input('payment_method_id');
                    // $transaction->invoice_id = $request->input('invoice_id');
                    $transaction->reference = $request->input('reference');
                    $transaction->note = $request->input('note');
                    // $transaction->attachment = $attachment;
                    $transaction->company_id = $idEmpresa;
    
                    $transaction->tasa = $request->input('tasa');
                    $transaction->usd = $request->input('usd');
    
                    $transaction->razon_social = $request->input('razon_social');
                    $transaction->tipo_comprobante_id = $request->input('tipo_comprobante_id');
    
    
    
    
                    $transaction->save();
                    // dd($trans);

    }

    /**
     * tipo de baja 
     */
        public function TipoBaja($id=null, $descripcion=null)
        {
            if(($id=="") && ($descripcion=="")) return "";
            $resul="";

            $tipo_baja = [
                1 => '04 D',
                2 => '04 C',
                3 => 'Moto c/alta motor',
                4 => 'Moto baja definitiva',
                5 => 'BD',
                6 => 'Alta de Motor'
            ];

            if ($id!=''){
                $resul = ($tipo_baja[$id] ?? "");
            }
            if($descripcion!=''){ 
                $resul=implode(',',array_keys($this->like($tipo_baja,"%{$descripcion}%")));
                //$resul=array_keys($this->like($tipo_baja,"{$descripcion}"));
            }
            return $resul; 
        }


        public function like(array $arr, string $patron): array
        {
            return array_filter($arr, static function (mixed $value) use ($patron): bool {
                return 1 === preg_match(sprintf('/^%s$/i', preg_replace('/(^%)|(%$)/', '.*', $patron)), $value);
            });

        }

        /**
     * tipo de vehiculo 
     */
    public function TipoVehiculo($id=null, $descripcion=null)
    {

        $datos = [
            "01" => '01',
            "02" => '02',
            "03" => '03',
            "04" => '04',
        ];

        if(($id=="") && ($descripcion=="")) 
        return $datos;
        $resul="";

       
       if ($id!=''){
            $resul = ($datos[$id] ?? "");
        }
        if($descripcion!=''){ 
            $resul=implode(',',array_keys($this->like($datos,"%{$descripcion}%")));
        }
        return $resul; 
    }

    public function EstatusAnulado($id=null, $descripcion=null)
    {

        $datos = [
            // "Devolver Dinero" => 'Devolver Dinero', 
            "Item inventario" => 'Item inventario',
            "Item Descompuesto" => 'Item Descompuesto'
        ];

        if(($id=="") && ($descripcion=="")) 
        return $datos;
        $resul="";

       
       if ($id!=''){
            $resul = ($datos[$id] ?? "");
        }
        if($descripcion!=''){ 
            $resul=implode(',',array_keys($this->like($datos,"%{$descripcion}%")));
        }
        return $resul; 
    }


     /**
     * tipo de responsableEntregas 
     */
        public function responsableEntregas($id=null, $descripcion=null)
        {
            if(($id=="") && ($descripcion=="")) return "";
            $resul="";

            $responsable_entregas = [
            1 => 'Asegurado',
            2 => 'Gestor Compañia',
            3 => 'Productor',
            4 => 'Compañia'
            ];

            if ($id!=''){
                $resul = ($responsable_entregas[$id] ?? "");
            }
            if($descripcion!=''){ 
                $resul=implode(',',array_keys($this->like($responsable_entregas,"%{$descripcion}%")));
            }
            return $resul; 
        }


}