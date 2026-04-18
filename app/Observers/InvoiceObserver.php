<?php

namespace App\Observers;

use App\CuentaCorriente;
use App\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceObserver
{
    /**
     * Handle the Invoice "created" event.
     *
     * @param  \App\Invoice  $invoice
     * @return void
     */
    public function created(Invoice $invoice)
    {
        // 1. Determinar montos según la moneda de la factura
        $debe_peso = !$invoice->is_usd ? $invoice->grand_total : 0;
        $debe_usd  = $invoice->is_usd ? $invoice->grand_total : 0;

        // 2. Insertar en cuenta corriente (Sin calcular saldo manualmente)
        CuentaCorriente::create([
            'payer_payee_id'   => $invoice->client_id,
            'comprobable_type' => 'App\Invoice',
            'comprobable_id'   => $invoice->id,
            'debe_peso'        => $debe_peso,
            'haber_peso'       => 0,
            'debe_usd'         => $debe_usd,
            'haber_usd'        => 0,
            'tasa_cambio'      => $invoice->tasa ?? 1,
            'nota'             => "Factura #" . $invoice->invoice_number,
        ]);

        // 3. RECALCULAR: Esta función arrastra el saldo anterior automáticamente
        CuentaCorriente::recalcular($invoice->client_id);
    }

    /**
     * Handle the Invoice "updated" event.
     *
     * @param  \App\Invoice  $invoice
     * @return void
     */
    public function updated(Invoice $invoice)
    {
        //
    }

    /**
     * Handle the Invoice "deleted" event.
     *
     * @param  \App\Invoice  $invoice
     * @return void
     */
    public function deleted(Invoice $invoice)
    {
        // 1. Encontrar y revertir todos los pagos automáticos relacionados con esta factura
        $this->revertirPagosAutomaticos($invoice);
        
        // 2. Eliminar el movimiento correspondiente en cuenta corriente (si aún existe)
        // Solo eliminar movimientos de factura que no sean de reversión
        CuentaCorriente::where('comprobable_type', 'App\Invoice')
            ->where('comprobable_id', $invoice->id)
            ->where('nota', 'not like', '%Reversión%')
            ->delete();
            
        // 3. Eliminar transacciones relacionadas con esta factura (pagos desde cuenta corriente)
        \App\Transaction::where('invoice_id', $invoice->id)
            ->where('type', 'cc_expense')
            ->where('dr_cr', 'cc')
            ->delete();
            
        // 4. Recalcular saldos
        CuentaCorriente::recalcular($invoice->client_id);
    }
    
    /**
     * Revertir todos los pagos automáticos relacionados con una factura
     * 
     * @param \App\Invoice $invoice Factura a revertir pagos
     * @return void
     */
    private function revertirPagosAutomaticos(Invoice $invoice)
    {
        try {
            // Buscar todos los movimientos de pago automático para esta factura
            $movimientosPago = \App\CuentaCorriente::where('payer_payee_id', $invoice->client_id)
                ->where(function($query) use ($invoice) {
                    // Movimientos de pago parcial
                    $query->where('nota', 'like', '%Pago parcial desde saldo a favor%')
                          ->orWhere('nota', 'like', '%Pago automático desde saldo a favor%')
                          ->orWhere('nota', 'like', '%Cancelación de saldo a favor%')
                          // Movimientos netos de pago completo
                          ->orWhere('nota', 'like', '%pagada automáticamente desde saldo a favor%');
                })
                ->where(function($query) use ($invoice) {
                    // Relacionados con esta factura
                    $query->where('nota', 'like', '%Factura #' . $invoice->invoice_number . '%')
                          ->orWhere('nota', 'like', '%Factura #' . $invoice->id . '%');
                })
                ->where('fue_revertido', 0)
                ->get();
            
            foreach ($movimientosPago as $movimiento) {
                \Log::debug('DEBUG: Procesando movimiento para reversión', [
                    'movimiento_id' => $movimiento->id,
                    'nota' => $movimiento->nota,
                    'debe_usd' => $movimiento->debe_usd,
                    'haber_usd' => $movimiento->haber_usd,
                    'debe_peso' => $movimiento->debe_peso,
                    'haber_peso' => $movimiento->haber_peso,
                ]);
                
                // Crear movimiento de reversión para restaurar el crédito
                $this->crearMovimientoReversion($movimiento, $invoice);
                    
                // Marcar como revertido
                $movimiento->fue_revertido = 1;
                $movimiento->nota .= ' | REVERTIDO por eliminación de factura';
                $movimiento->save();
                    
                \Log::info('Movimiento de pago automático revertido por eliminación de factura', [
                    'movimiento_id' => $movimiento->id,
                    'invoice_id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                    'nota' => $movimiento->nota,
                ]);
            }
            
            // Buscar y revertir movimientos de cancelación de saldo a favor
            $movimientosCancelacion = \App\CuentaCorriente::where('payer_payee_id', $invoice->client_id)
                ->where('nota', 'like', '%Cancelación de saldo a favor%')
                ->where('nota', 'like', '%Factura #' . $invoice->invoice_number . '%')
                ->where('fue_revertido', 0)
                ->get();
            
            foreach ($movimientosCancelacion as $movimiento) {
                // Crear movimiento de reversión para restaurar el saldo a favor
                $movimientoReversion = new \App\CuentaCorriente();
                $movimientoReversion->payer_payee_id = $invoice->client_id;
                $movimientoReversion->comprobable_type = 'App\Invoice';
                $movimientoReversion->comprobable_id = $invoice->id;
                
                // Invertir los montos: HABER para restaurar saldo a favor
                $movimientoReversion->debe_peso = $movimiento->haber_peso;
                $movimientoReversion->haber_peso = $movimiento->debe_peso;
                $movimientoReversion->debe_usd = $movimiento->haber_usd;
                $movimientoReversion->haber_usd = $movimiento->debe_usd;
                
                $movimientoReversion->tasa_cambio = $movimiento->tasa_cambio;
                $movimientoReversion->nota = 'Reversión por eliminación de factura: ' . $movimiento->nota;
                $movimientoReversion->fue_revertido = 0;
                $movimientoReversion->save();
                
                // Marcar original como revertido
                $movimiento->fue_revertido = 1;
                $movimiento->nota .= ' | REVERTIDO';
                $movimiento->save();
                
                \Log::info('Movimiento de cancelación de saldo revertido por eliminación de factura', [
                    'movimiento_original_id' => $movimiento->id,
                    'movimiento_reversion_id' => $movimientoReversion->id,
                    'invoice_id' => $invoice->id,
                ]);
            }
            
        } catch (\Exception $e) {
            \Log::error('Error al revertir pagos automáticos por eliminación de factura', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
    
    /**
     * Crear movimiento de reversión para restaurar crédito
     * 
     * @param \App\CuentaCorriente $movimientoOriginal Movimiento original a revertir
     * @param \App\Invoice $invoice Factura relacionada
     * @return void
     */
    private function crearMovimientoReversion($movimientoOriginal, $invoice)
    {
        try {
            \Log::debug('DEBUG crearMovimientoReversion: Iniciando', [
                'movimiento_original_id' => $movimientoOriginal->id,
                'nota' => $movimientoOriginal->nota,
                'debe_usd' => $movimientoOriginal->debe_usd,
                'haber_usd' => $movimientoOriginal->haber_usd,
                'debe_peso' => $movimientoOriginal->debe_peso,
                'haber_peso' => $movimientoOriginal->haber_peso,
            ]);
            
            // Determinar el tipo de movimiento original
            $esMovimientoNeto = strpos($movimientoOriginal->nota, 'pagada automáticamente desde saldo a favor') !== false;
            $esMovimientoPagoParcial = strpos($movimientoOriginal->nota, 'Pago parcial desde saldo a favor') !== false;
            $esMovimientoCancelacion = strpos($movimientoOriginal->nota, 'Cancelación de saldo a favor') !== false;
        
            \Log::debug('DEBUG crearMovimientoReversion: Tipo de movimiento', [
                'esMovimientoNeto' => $esMovimientoNeto,
                'esMovimientoPagoParcial' => $esMovimientoPagoParcial,
                'esMovimientoCancelacion' => $esMovimientoCancelacion,
            ]);
        
            // Crear movimiento de reversión
            $movimientoReversion = new \App\CuentaCorriente();
            $movimientoReversion->payer_payee_id = $invoice->client_id;
            $movimientoReversion->comprobable_type = 'App\Transaction';
            $movimientoReversion->comprobable_id = 0; // No vinculado a transacción específica
        
            // Para movimientos DEBE (pagos desde crédito), crear HABER para restaurar crédito
            // Para movimientos HABER (pagos parciales), crear DEBE para restaurar deuda
            if ($movimientoOriginal->debe_usd > 0 || $movimientoOriginal->debe_peso > 0) {
                // Movimiento DEBE original: crear HABER para restaurar crédito
                $movimientoReversion->debe_usd = 0;
                $movimientoReversion->haber_usd = $movimientoOriginal->debe_usd;
                $movimientoReversion->debe_peso = 0;
                $movimientoReversion->haber_peso = $movimientoOriginal->debe_peso;
                
                \Log::debug('DEBUG crearMovimientoReversion: Creando HABER para restaurar crédito', [
                    'haber_usd' => $movimientoReversion->haber_usd,
                    'haber_peso' => $movimientoReversion->haber_peso,
                ]);
            } else if ($movimientoOriginal->haber_usd > 0 || $movimientoOriginal->haber_peso > 0) {
                // Movimiento HABER original: crear DEBE para restaurar deuda
                $movimientoReversion->debe_usd = $movimientoOriginal->haber_usd;
                $movimientoReversion->haber_usd = 0;
                $movimientoReversion->debe_peso = $movimientoOriginal->haber_peso;
                $movimientoReversion->haber_peso = 0;
                
                \Log::debug('DEBUG crearMovimientoReversion: Creando DEBE para restaurar deuda', [
                    'debe_usd' => $movimientoReversion->debe_usd,
                    'debe_peso' => $movimientoReversion->debe_peso,
                ]);
            } else {
                // No hay montos, no crear reversión
                \Log::debug('DEBUG crearMovimientoReversion: No hay montos, saliendo', []);
                return;
            }
        
            $movimientoReversion->tasa_cambio = $movimientoOriginal->tasa_cambio;
        
            // Determinar descripción según tipo de movimiento
            $descripcion = '';
            if ($esMovimientoNeto) {
                $descripcion = 'Reversión de pago completo desde saldo a favor';
            } else if ($esMovimientoPagoParcial) {
                $descripcion = 'Reversión de pago parcial desde saldo a favor';
            } else if ($esMovimientoCancelacion) {
                $descripcion = 'Reversión de cancelación de saldo a favor';
            } else {
                $descripcion = 'Reversión de pago automático';
            }
        
            $movimientoReversion->nota = $descripcion . ' | Factura #' . $invoice->invoice_number . ' eliminada';
            $movimientoReversion->fue_revertido = 0;
            $movimientoReversion->save();
        
            \Log::info('Movimiento de reversión creado para restaurar crédito', [
                'movimiento_original_id' => $movimientoOriginal->id,
                'movimiento_reversion_id' => $movimientoReversion->id,
                'invoice_id' => $invoice->id,
                'tipo' => $descripcion,
                'debe_usd' => $movimientoReversion->debe_usd,
                'haber_usd' => $movimientoReversion->haber_usd,
                'debe_peso' => $movimientoReversion->debe_peso,
                'haber_peso' => $movimientoReversion->haber_peso,
            ]);
        
        } catch (\Exception $e) {
            \Log::error('Error al crear movimiento de reversión', [
                'movimiento_original_id' => $movimientoOriginal->id,
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Handle the Invoice "restored" event.
     *
     * @param  \App\Invoice  $invoice
     * @return void
     */
    public function restored(Invoice $invoice)
    {
        //
    }

    /**
     * Handle the Invoice "force deleted" event.
     *
     * @param  \App\Invoice  $invoice
     * @return void
     */
    public function forceDeleted(Invoice $invoice)
    {
        //
    }
}
