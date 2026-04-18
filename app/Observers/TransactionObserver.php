<?php

namespace App\Observers;

use App\CuentaCorriente;
use App\Transaction;
use App\Invoice;
use Illuminate\Support\Facades\DB;

class TransactionObserver
{
    /**
     * Determina si hay conversión de moneda y calcula los detalles
     *
     * @param \App\Transaction $transaccion
     * @param string|null $moneda_factura
     * @param float $m_peso
     * @param float $m_usd
     * @return array
     */
    private function determinarConversion($transaccion, $moneda_factura, $m_peso, $m_usd)
    {
        $tiene_conversion = false;
        $monto_original = null;
        $moneda_original = null;
        $monto_convertido = null;
        $moneda_convertida = null;
        $tasa_aplicada = $transaccion->tasa ?? 1;
        $detalle_conversion = null;
        
        // Variables para manejar sobrante
        $monto_aplicado = null;
        $moneda_aplicada = null;
        $sobrante = null;
        $moneda_sobrante = null;
        
        // Depuración
        \Log::debug('determinarConversion - Parámetros:', [
            'transaccion_id' => $transaccion->id,
            'moneda_factura' => $moneda_factura,
            'm_peso' => $m_peso,
            'm_usd' => $m_usd,
            'tasa' => $tasa_aplicada,
            'usd_field' => $transaccion->usd,
            'invoice_id' => $transaccion->invoice_id
        ]);

        // Caso 1: Transacción con tasa aplicada y montos en ambas monedas
        if ($transaccion->tasa && $transaccion->tasa != 1 && ($m_peso > 0 && $m_usd > 0)) {
            $tiene_conversion = true;
            \Log::debug('Caso 1 detectado: Transacción con tasa y montos en ambas monedas');

            // Determinar qué monto es el original y cuál es el convertido
            if ($transaccion->usd == 1) {
                // Transacción marcada como USD, entonces USD es original
                $monto_original = $m_usd;
                $moneda_original = 'USD';
                $monto_convertido = $m_peso;
                $moneda_convertida = 'ARS';
                $detalle_conversion = "Pago en USD convertido a ARS";
            } else {
                // Transacción marcada como ARS, entonces ARS es original
                $monto_original = $m_peso;
                $moneda_original = 'ARS';
                $monto_convertido = $m_usd;
                $moneda_convertida = 'USD';
                $detalle_conversion = "Pago en ARS convertido a USD";
            }
        }
        // Caso 2: Pago en moneda diferente a la factura
        elseif ($moneda_factura && $transaccion->tasa && $transaccion->tasa != 1) {
            \Log::debug('Caso 2 evaluando: moneda_factura=' . $moneda_factura . ', m_peso=' . $m_peso . ', m_usd=' . $m_usd);
            if ($moneda_factura === 'USD' && $m_peso > 0 && $m_usd == 0) {
                $tiene_conversion = true;
                \Log::debug('Caso 2.1 detectado: Factura en USD pagada en ARS');
                $monto_original = $m_peso;
                $moneda_original = 'ARS';
                $monto_convertido = $m_peso / $tasa_aplicada;
                $moneda_convertida = 'USD';
                
                // Calcular si hay sobrante
                $monto_factura_usd = $transaccion->invoice_id ? DB::table('invoices')->where('id', $transaccion->invoice_id)->value('grand_total') : 0;
                $sobrante_usd = max(0, $monto_convertido - $monto_factura_usd);
                
                if ($sobrante_usd > 0) {
                    $detalle_conversion = "Factura en USD pagada en ARS - Sobrante: " . 
                        number_format($sobrante_usd, 2) . " USD";
                    $monto_aplicado = $monto_factura_usd;
                    $moneda_aplicada = 'USD';
                    $sobrante = $sobrante_usd;
                    $moneda_sobrante = 'USD';
                } else {
                    $detalle_conversion = "Factura en USD pagada en ARS";
                }
            } elseif ($moneda_factura === 'ARS' && $m_usd > 0 && $m_peso == 0) {
                $tiene_conversion = true;
                \Log::debug('Caso 2.2 detectado: Factura en ARS pagada en USD');
                $monto_original = $m_usd;
                $moneda_original = 'USD';
                $monto_convertido = $m_usd * $tasa_aplicada;
                $moneda_convertida = 'ARS';
                
                // Calcular si hay sobrante
                $monto_factura_ars = $transaccion->invoice_id ? DB::table('invoices')->where('id', $transaccion->invoice_id)->value('grand_total') : 0;
                $sobrante_ars = max(0, $monto_convertido - $monto_factura_ars);
                
                if ($sobrante_ars > 0) {
                    $sobrante_usd = $sobrante_ars / $tasa_aplicada;
                    $detalle_conversion = "Factura en ARS pagada en USD - Sobrante: " . 
                        number_format($sobrante_usd, 2) . " USD";
                    $monto_aplicado = $monto_factura_ars;
                    $moneda_aplicada = 'ARS';
                    $sobrante = $sobrante_usd;
                    $moneda_sobrante = 'USD';
                } else {
                    $detalle_conversion = "Factura en ARS pagada en USD";
                }
            }
        }
        // Caso 3: Transacción con usd=true pero monto en pesos (o viceversa)
        elseif ($transaccion->usd == 1 && $m_peso > 0 && $m_usd == 0) {
            $tiene_conversion = true;
            \Log::debug('Caso 3.1 detectado: Transacción marcada como USD pero monto en ARS');
            $monto_original = $m_peso;
            $moneda_original = 'ARS';
            $monto_convertido = $m_peso / $tasa_aplicada;
            $moneda_convertida = 'USD';
            $detalle_conversion = "Transacción marcada como USD pero monto en ARS";
        } elseif ($transaccion->usd == 0 && $m_usd > 0 && $m_peso == 0) {
            $tiene_conversion = true;
            \Log::debug('Caso 3.2 detectado: Transacción marcada como ARS pero monto en USD');
            $monto_original = $m_usd;
            $moneda_original = 'USD';
            $monto_convertido = $m_usd * $tasa_aplicada;
            $moneda_convertida = 'ARS';
            $detalle_conversion = "Transacción marcada como ARS pero monto en USD";
        }

        // Depuración del resultado
        \Log::debug('determinarConversion - Resultado:', [
            'tiene_conversion' => $tiene_conversion,
            'monto_original' => $monto_original,
            'moneda_original' => $moneda_original,
            'monto_convertido' => $monto_convertido,
            'sobrante' => $sobrante,
            'moneda_sobrante' => $moneda_sobrante
        ]);
        
        return [
            'tiene_conversion' => $tiene_conversion,
            'monto_original' => $monto_original,
            'moneda_original' => $moneda_original,
            'monto_convertido' => $monto_convertido,
            'moneda_convertida' => $moneda_convertida,
            'tasa_aplicada' => $tasa_aplicada,
            'detalle_conversion' => $detalle_conversion,
            'monto_aplicado' => $monto_aplicado,
            'moneda_aplicada' => $moneda_aplicada,
            'sobrante' => $sobrante,
            'moneda_sobrante' => $moneda_sobrante,
        ];
    }
    /**
     * Handle the Transaction "created" event.
     *
     * @param  \App\Transaction  $transaction
     * @return void
     */
    public function created(Transaction $transaction)
    {
      if (!$transaction->payer_payee_id) return;

        $es_debe = ($transaction->dr_cr == 'dr');

        // LÓGICA DE RETROCOMPATIBILIDAD (Igual a NivelarCuentaCorriente)
        // Si las nuevas columnas están vacías, usamos el 'amount' antiguo
        $monto_peso = $transaction->amount_peso;
        $monto_usd  = $transaction->amount_usd;

        // Determinar moneda de la factura si existe
        $moneda_factura = null;
        if ($transaction->invoice_id) {
            $invoice = \App\Invoice::find($transaction->invoice_id);
            if ($invoice) {
                $moneda_factura = $invoice->is_usd ? 'USD' : 'ARS';
            }
        }

        // LÓGICA MEJORADA PARA DETERMINAR MONTOS
        // Caso 1: Ambos montos son null - determinar según reglas
        if (is_null($monto_peso) && is_null($monto_usd)) {
            if ($moneda_factura === 'USD') {
                // Factura en USD - amount está en USD
                $monto_usd = $transaction->amount;
                $monto_peso = 0;
            } elseif ($moneda_factura === 'ARS') {
                // Factura en ARS - amount está en ARS
                $monto_peso = $transaction->amount;
                $monto_usd = 0;
            } else {
                // No hay factura - determinar según campo usd de la transacción
                if ($transaction->usd == 1) {
                    $monto_usd = $transaction->amount;
                    $monto_peso = 0;
                } else {
                    $monto_peso = $transaction->amount;
                    $monto_usd = 0;
                }
            }
        }
        // Caso 2: Solo amount_peso tiene valor
        elseif (!is_null($monto_peso) && is_null($monto_usd)) {
            $monto_usd = 0;
            // Si amount_peso > 0, verificar si corresponde a pago en ARS
            if ($monto_peso > 0 && $transaction->usd == 0) {
                // Pago en ARS - correcto
            }
        }
        // Caso 3: Solo amount_usd tiene valor
        elseif (is_null($monto_peso) && !is_null($monto_usd)) {
            $monto_peso = 0;
            // Si amount_usd > 0, verificar si corresponde a pago en USD
            if ($monto_usd > 0 && $transaction->usd == 1) {
                // Pago en USD - correcto
            }
        }
        // Caso 4: Ambos tienen valor (raro, pero posible)
        elseif (!is_null($monto_peso) && !is_null($monto_usd)) {
            // Verificar consistencia según reglas
            if ($moneda_factura === 'USD' && $transaction->usd == 0) {
                // Factura en USD, pago en ARS: amount_peso debería tener valor, amount_usd = 0
                if ($monto_peso > 0 && $monto_usd == 0) {
                    // Correcto
                }
            } elseif ($moneda_factura === 'ARS' && $transaction->usd == 1) {
                // Factura en ARS, pago en USD: amount_usd debería tener valor, amount_peso = 0
                if ($monto_usd > 0 && $monto_peso == 0) {
                    // Correcto
                }
            }
        }

        // DETECTAR TIPO DE TRANSACCIÓN
        // 1. DEVOLUCIÓN DE DINERO: Cuando el cliente tiene saldo negativo y le devolvemos dinero
        //    - Debe registrar DEBE (reduce el saldo negativo)
        // 2. INGRESO A CUENTA CORRIENTE: Cuando el cliente ingresa dinero a su cuenta
        //    - Debe registrar HABER (aumenta saldo a favor = saldo negativo más negativo)
        // 3. DEVOLUCIÓN DE PRODUCTO: Cuando el cliente devuelve un producto
        //    - Puede tener lógica diferente
        // 4. TRANSACCIÓN NORMAL: Lógica estándar
        
        $es_devolucion_dinero = false;
        $es_ingreso_cuenta_corriente = false;
        $es_devolucion_producto = false;
        
        // Detectar devolución de dinero por tipo y dr_cr
        if ($transaction->type == 'cc_expense' && $transaction->dr_cr == 'cc') {
            // Verificar si es devolución de dinero (no de producto)
            if ($transaction->note && (
                stripos($transaction->note, 'devolución de dinero') !== false ||
                stripos($transaction->note, 'devolución de saldo') !== false ||
                stripos($transaction->note, 'saldo a favor') !== false
            )) {
                $es_devolucion_dinero = true;
            }
        }
        
        // Detectar ingreso a cuenta corriente
        if ($transaction->type == 'cc_income' && $transaction->dr_cr == 'cc') {
            // Verificar si es ingreso a cuenta corriente
            if ($transaction->note && (
                stripos($transaction->note, 'ingreso manual') !== false ||
                stripos($transaction->note, 'ingreso a cuenta') !== false ||
                stripos($transaction->note, 'abono a cuenta') !== false
            )) {
                $es_ingreso_cuenta_corriente = true;
            }
        }
        
        // Detectar devolución de producto
        if (!$es_devolucion_dinero && !$es_ingreso_cuenta_corriente && $transaction->note && stripos($transaction->note, 'devolución de ítem') !== false) {
            $es_devolucion_producto = true;
        }

        // Calcular debe y haber según el tipo de transacción
        if ($es_devolucion_dinero) {
            // DEVOLUCIÓN DE DINERO: Invertir la lógica
            // Cuando el cliente tiene saldo negativo (nosotros le debemos) y le devolvemos dinero,
            // registramos DEBE (reduce el saldo negativo)
            $debe_peso = $monto_peso ?? 0;
            $haber_peso = 0;
            $debe_usd = $monto_usd ?? 0;
            $haber_usd = 0;
        } elseif ($es_ingreso_cuenta_corriente) {
            // INGRESO A CUENTA CORRIENTE: El cliente ingresa dinero (aumenta su saldo a favor)
            // Cuando el cliente ingresa dinero, registramos HABER (aumenta el saldo negativo/saldo a favor)
            $debe_peso = 0;
            $haber_peso = $monto_peso ?? 0;
            $debe_usd = 0;
            $haber_usd = $monto_usd ?? 0;
        } elseif ($es_devolucion_producto) {
            // DEVOLUCIÓN DE PRODUCTO: Mantener lógica actual (HABER)
            // Cuando el cliente devuelve un producto, le damos crédito (aumenta su saldo a favor)
            $debe_peso = 0;
            $haber_peso = $monto_peso ?? 0;
            $debe_usd = 0;
            $haber_usd = $monto_usd ?? 0;
        } else {
            // LÓGICA NORMAL PARA TRANSACCIONES REGULARES
            $debe_peso = $es_debe ? ($monto_peso ?? 0) : 0;
            $haber_peso = !$es_debe ? ($monto_peso ?? 0) : 0;
            $debe_usd = $es_debe ? ($monto_usd ?? 0) : 0;
            $haber_usd = !$es_debe ? ($monto_usd ?? 0) : 0;
        }

        // Determinar información de conversión
        $info_conversion = $this->determinarConversion($transaction, $moneda_factura, (float)($monto_peso ?? 0), (float)($monto_usd ?? 0));
        
        // Depuración de info_conversion
        \Log::debug('TransactionObserver - info_conversion:', $info_conversion);

        // Ajustar campos para conversiones con sobrante
        if ($info_conversion['tiene_conversion'] && $info_conversion['sobrante'] && $info_conversion['moneda_sobrante']) {
            // Para factura en ARS pagada en USD con sobrante
            if ($moneda_factura === 'ARS' && (float)($monto_usd ?? 0) > 0 && (float)($monto_peso ?? 0) == 0) {
                // El haber_peso debe ser el monto aplicado a la factura en ARS
                $haber_peso = (float)($info_conversion['monto_aplicado'] ?? 0);
                // El haber_usd debe ser solo el sobrante en USD
                $haber_usd = (float)($info_conversion['sobrante'] ?? 0);
                $debe_usd = 0;
            }
            // Para factura en USD pagada en ARS con sobrante
            elseif ($moneda_factura === 'USD' && (float)($monto_peso ?? 0) > 0 && (float)($monto_usd ?? 0) == 0) {
                // El haber_usd debe ser el monto aplicado a la factura en USD
                $haber_usd = (float)($info_conversion['monto_aplicado'] ?? 0);
                // El haber_peso debe ser solo el sobrante en ARS
                $haber_peso = (float)($info_conversion['sobrante'] ?? 0);
                $debe_peso = 0;
            }
        }

        // Insertamos el movimiento en la cuenta corriente sin calcular saldo manual
        CuentaCorriente::create([
            'payer_payee_id'   => $transaction->payer_payee_id,
            'comprobable_type' => 'App\Transaction',
            'comprobable_id'   => $transaction->id,
            'debe_peso'        => $debe_peso,
            'haber_peso'       => $haber_peso,
            'debe_usd'         => $debe_usd,
            'haber_usd'        => $haber_usd,
            'tasa_cambio'      => $transaction->tasa ?? 1,
            'nota'             => $transaction->note ?? "Movimiento de caja",
            'monto_original'   => $info_conversion['monto_original'] ?? null,
            'moneda_original'  => $info_conversion['moneda_original'] ?? null,
            'monto_convertido' => $info_conversion['monto_convertido'] ?? null,
            'moneda_convertida' => $info_conversion['moneda_convertida'] ?? null,
            'tasa_aplicada'    => $info_conversion['tasa_aplicada'] ?? null,
            'tiene_conversion' => $info_conversion['tiene_conversion'] ? 1 : 0,
            'detalle_conversion' => $info_conversion['detalle_conversion'] ?? null,
            'monto_aplicado'   => $info_conversion['monto_aplicado'] ?? null,
            'moneda_aplicada'  => $info_conversion['moneda_aplicada'] ?? null,
            'sobrante'         => $info_conversion['sobrante'] ?? null,
            'moneda_sobrante'  => $info_conversion['moneda_sobrante'] ?? null,
        ]);

        // Ejecutamos el recálculo centralizado para arrastrar el saldo correctamente
        CuentaCorriente::recalcular($transaction->payer_payee_id);
    }

    /**
     * Handle the Transaction "updated" event.
     *
     * @param  \App\Transaction  $transaction
     * @return void
     */
    public function updated(Transaction $transaction)
    {
        //
    }

    /**
     * Handle the Transaction "deleted" event.
     *
     * @param  \App\Transaction  $transaction
     * @return void
     */
    public function deleted(Transaction $transaction)
    {
        // Eliminar el movimiento correspondiente en cuenta corriente
        CuentaCorriente::where('comprobable_type', 'App\Transaction')
            ->where('comprobable_id', $transaction->id)
            ->delete();
            
        // Recalcular saldos
        if ($transaction->payer_payee_id) {
            CuentaCorriente::recalcular($transaction->payer_payee_id);
        }
    }

    /**
     * Handle the Transaction "restored" event.
     *
     * @param  \App\Transaction  $transaction
     * @return void
     */
    public function restored(Transaction $transaction)
    {
        //
    }

    /**
     * Handle the Transaction "force deleted" event.
     *
     * @param  \App\Transaction  $transaction
     * @return void
     */
    public function forceDeleted(Transaction $transaction)
    {
        //
    }
}
