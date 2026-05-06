<?php

namespace App\Observers;

use App\CuentaCorriente;
use App\ProductReturn;
use App\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProductReturnObserver
{
    /**
     * Handle the ProductReturn "created" event.
     *
     * @param  \App\ProductReturn  $productReturn
     * @return void
     */
    public function created(ProductReturn $productReturn)
    {
        //
        if ($productReturn->status == 'procesada') {
            $this->registrarEnCuentaCorriente($productReturn);

            $this->ejecutarFifo($productReturn);
        }
    }

    /**
     * Handle the ProductReturn "updated" event.
     *
     * @param  \App\ProductReturn  $productReturn
     * @return void
     */
    public function updated(ProductReturn $productReturn)
    {
        // Verificamos si el estado cambió a procesada en esta actualización
        if ($productReturn->wasChanged('status') && $productReturn->status == 'procesada') {
            $this->registrarEnCuentaCorriente($productReturn);

            $this->ejecutarFifo($productReturn);
        }
    }


    protected function registrarEnCuentaCorriente(ProductReturn $productReturn)
    {
        $invoice = $productReturn->invoice;
        
        $item = DB::table('invoice_items')
                  ->where('invoice_id', $productReturn->invoice_id)
                  ->where('product_id', $productReturn->product_id)
                  ->first();

        if ($item) {
            CuentaCorriente::create([
                'payer_payee_id'   => $invoice->client_id,
                'comprobable_type' => 'App\ProductReturn',
                'comprobable_id'   => $productReturn->id,
                'debe_peso'        => 0,
                'haber_peso'       => !$invoice->is_usd ? $item->sub_total : 0,
                'debe_usd'         => 0,
                'haber_usd'        => $invoice->is_usd ? $item->sub_total : 0,
                'tasa_cambio'      => $invoice->tasa ?? 1,
                'nota'             => "Devolución de ítem (Factura #" . $invoice->invoice_number . ")",
            ]);

            // Recalcular para asegurar que el saldo (ej. -1.00) sea exacto
            CuentaCorriente::recalcular($invoice->client_id);
        }
    }

    /**
     * Handle the ProductReturn "deleted" event.
     *
     * @param  \App\ProductReturn  $productReturn
     * @return void
     */
    public function deleted(ProductReturn $productReturn)
    {
        //
        $invoice = $productReturn->invoice;
        if ($invoice) {
            CuentaCorriente::recalcular($invoice->client_id);
        }
    }

    /**
     * Handle the ProductReturn "restored" event.
     *
     * @param  \App\ProductReturn  $productReturn
     * @return void
     */
    public function restored(ProductReturn $productReturn)
    {
        //
    }

    /**
     * Handle the ProductReturn "force deleted" event.
     *
     * @param  \App\ProductReturn  $productReturn
     * @return void
     */
    public function forceDeleted(ProductReturn $productReturn)
    {
        //
    }

    protected function ejecutarFifo(ProductReturn $productReturn)
    {
        try {
            $invoice = $productReturn->invoice;
            if ($invoice && $invoice->client_id) {
                if ($invoice->paid > 0) {
                    CuentaCorriente::where('comprobable_type', 'App\Invoice')
                        ->where('comprobable_id', $invoice->id)
                        ->where('fue_revertido', 0)
                        ->delete();

                    // Buscar facturas impagas
                    $targetInvoices = Invoice::where('client_id', $invoice->client_id)
                        ->where('id', '!=', $invoice->id)
                        ->whereIn('status', ['Unpaid', 'Partially_Paid'])
                        ->where(function ($q) {
                            $q->whereNull('paid')
                              ->orWhereRaw('paid < grand_total');
                        })
                        ->orderBy('invoice_date')
                        ->orderBy('id')
                        ->get();

                    if ($targetInvoices->isNotEmpty()) {
                        // Solo eliminar HABERs si hay donde redistribuir
                        CuentaCorriente::where('comprobable_type', 'App\ProductReturn')
                            ->where('comprobable_id', $productReturn->id)
                            ->delete();
                    }
                    // Sin facturas impagas: los HABERs se quedan como credito

                    CuentaCorriente::recalcular($invoice->client_id);
                }

                CuentaCorriente::reimputarSaldoFavorFIFO($invoice->client_id);
            }
        } catch (\Throwable $e) {
            Log::error('Error en FIFO (ProductReturn): ' . $e->getMessage(), [
                'product_return_id' => $productReturn->id,
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}
