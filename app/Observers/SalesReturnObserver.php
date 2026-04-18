<?php

namespace App\Observers;

use App\CuentaCorriente;
use App\SalesReturn;
use App\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SalesReturnObserver
{
    /**
     * Handle the SalesReturn "created" event.
     *
     * @param  \App\SalesReturn  $salesReturn
     * @return void
     */
    public function created(SalesReturn $salesReturn)
    {
        // Registrar en cuenta corriente cuando se crea una devolución
        $this->registrarEnCuentaCorriente($salesReturn);
    }

    /**
     * Handle the SalesReturn "updated" event.
     *
     * @param  \App\SalesReturn  $salesReturn
     * @return void
     */
    public function updated(SalesReturn $salesReturn)
    {
        // Si la devolución fue modificada, recalcular cuenta corriente
        // Verificar cambios en campos relacionados con montos
        if ($salesReturn->wasChanged(['grand_total', 'customer_id', 'invoice_id'])) {
            // Primero eliminar registros anteriores si existen
            CuentaCorriente::where('comprobable_type', 'App\SalesReturn')
                          ->where('comprobable_id', $salesReturn->id)
                          ->delete();
            
            // Luego registrar de nuevo
            $this->registrarEnCuentaCorriente($salesReturn);
            
            // Recalcular saldo del cliente
            if ($salesReturn->customer_id) {
                CuentaCorriente::recalcular($salesReturn->customer_id);
            }
        }
    }

    /**
     * Handle the SalesReturn "deleted" event.
     *
     * @param  \App\SalesReturn  $salesReturn
     * @return void
     */
    public function deleted(SalesReturn $salesReturn)
    {
        // Eliminar registros de cuenta corriente asociados
        CuentaCorriente::where('comprobable_type', 'App\SalesReturn')
                      ->where('comprobable_id', $salesReturn->id)
                      ->delete();
        
        // Recalcular saldo del cliente
        if ($salesReturn->customer_id) {
            CuentaCorriente::recalcular($salesReturn->customer_id);
        }
    }

    /**
     * Handle the SalesReturn "restored" event.
     *
     * @param  \App\SalesReturn  $salesReturn
     * @return void
     */
    public function restored(SalesReturn $salesReturn)
    {
        // Restaurar en cuenta corriente
        $this->registrarEnCuentaCorriente($salesReturn);
        
        // Recalcular saldo del cliente
        if ($salesReturn->customer_id) {
            CuentaCorriente::recalcular($salesReturn->customer_id);
        }
    }

    /**
     * Handle the SalesReturn "force deleted" event.
     *
     * @param  \App\SalesReturn  $salesReturn
     * @return void
     */
    public function forceDeleted(SalesReturn $salesReturn)
    {
        // Eliminar registros de cuenta corriente asociados
        CuentaCorriente::where('comprobable_type', 'App\SalesReturn')
                      ->where('comprobable_id', $salesReturn->id)
                      ->delete();
        
        // Recalcular saldo del cliente
        if ($salesReturn->customer_id) {
            CuentaCorriente::recalcular($salesReturn->customer_id);
        }
    }

    /**
     * Registrar la devolución en cuenta corriente
     *
     * @param  \App\SalesReturn  $salesReturn
     * @return void
     */
    protected function registrarEnCuentaCorriente(SalesReturn $salesReturn)
    {
        // Obtener información de la factura relacionada si existe
        $invoice = null;
        if ($salesReturn->invoice_id) {
            $invoice = Invoice::find($salesReturn->invoice_id);
        }
        
        // Determinar la moneda correcta basada en la factura relacionada
        $isUsd = false;
        $tasa = 1;
        
        if ($invoice) {
            // Usar la moneda de la factura relacionada
            $isUsd = (bool) ($invoice->is_usd ?? false);
            $tasa = $invoice->tasa ?? 1;
        }
        
        // Asegurar que tasa siempre tenga un valor válido
        if (is_null($tasa) || $tasa <= 0) {
            $tasa = 1;
        }
        
        // Determinar montos según la moneda
        $montoPeso = 0;
        $montoUsd = 0;
        
        // Usar grand_total de sales_return (campo que existe según migración)
        $montoTotal = $salesReturn->grand_total ?? 0;
        
        if ($isUsd) {
            // Si es USD, el monto total está en USD - solo afectar USD
            $montoUsd = $montoTotal;
            $montoPeso = 0; // No convertir a pesos
        } else {
            // Si es ARS, el monto total está en ARS - solo afectar ARS
            $montoPeso = $montoTotal;
            $montoUsd = 0; // No convertir a USD
        }
        
        // Siempre crear movimiento con monto completo de la devolución
        // Mantener consistencia de datos: factura (monto completo) + pago (monto pagado) + devolución (monto completo)
        if ($invoice) {
            $facturaCancelada = $invoice->status == 'Canceled';
            $facturaTienePagos = $invoice->paid > 0;
            
            // Registrar información para auditoría
            Log::info('SalesReturnObserver: Creando movimiento para SalesReturn #' . $salesReturn->id . 
                     ' - Factura #' . $invoice->invoice_number . 
                     ' (Status: ' . $invoice->status . 
                     ', Pagado: ' . $invoice->paid . 
                     ', Total: ' . $invoice->grand_total . 
                     ', Devolución: ' . ($isUsd ? $montoUsd : $montoPeso) . ')');
        }
        
        // Verificar si hay monto para registrar (evitar movimientos con monto 0)
        if ($montoPeso == 0 && $montoUsd == 0) {
            Log::warning('SalesReturnObserver: No se creó movimiento para SalesReturn #' . $salesReturn->id . ' - Monto total es 0');
            return;
        }
        
        // Crear movimiento en cuenta corriente
        CuentaCorriente::create([
            'payer_payee_id'   => $salesReturn->customer_id,
            'comprobable_type' => 'App\SalesReturn',
            'comprobable_id'   => $salesReturn->id,
            'debe_peso'        => 0,
            'haber_peso'       => $montoPeso,
            'debe_usd'         => 0,
            'haber_usd'        => $montoUsd,
            'tasa_cambio'      => $tasa, // Usar la tasa (por defecto 1 si no hay factura)
            'nota'             => $this->generarNota($salesReturn, $invoice),
        ]);

        // Recalcular para asegurar que el saldo sea exacto
        CuentaCorriente::recalcular($salesReturn->customer_id);
    }

    /**
     * Generar nota descriptiva para el movimiento
     *
     * @param  \App\SalesReturn  $salesReturn
     * @param  \App\Invoice|null  $invoice
     * @return string
     */
    protected function generarNota(SalesReturn $salesReturn, $invoice = null)
    {
        $nota = "Devolución de venta";
        
        if ($invoice) {
            $nota .= " (Factura #" . $invoice->invoice_number . ")";
        }
        
        // Usar el monto formateado con moneda
        $monto = $salesReturn->grand_total ?? 0;
        
        // Determinar moneda basada en factura relacionada
        $moneda = '$'; // Por defecto ARS
        if ($invoice && isset($invoice->is_usd) && $invoice->is_usd) {
            $moneda = 'USD';
        }
        
        $nota .= " - Total: " . $moneda . " " . number_format($monto, 2);
        
        // Agregar referencia a la devolución
        $nota .= " - Devolución #" . $salesReturn->id;
        
        return $nota;
    }
}