<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;

class CuentaCorriente extends Model
{
    use HasFactory;

    protected $table = "cuenta_corriente";
    protected $fillable = [
        "payer_payee_id",
        "comprobable_type",
        "comprobable_id",
        "debe_peso",
        "haber_peso",
        "saldo_peso",
        "debe_usd",
        "haber_usd",
        "saldo_usd",
        "tasa_cambio",
        "nota",
        "fue_revertido",
        "movimiento_reversion_id",
        "monto_original",
        "moneda_original",
        "monto_convertido",
        "moneda_convertida",
        "tasa_aplicada",
        "tiene_conversion",
        "detalle_conversion",
        "monto_aplicado",
        "moneda_aplicada",
        "sobrante",
        "moneda_sobrante",
    ];

    /**
     * Boot the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // Mapear los tipos de modelos para la relación morphTo
        Relation::morphMap([
            "App\Invoice" => "App\Invoice",
            "App\Transaction" => "App\Transaction",
            "App\SalesReturn" => "App\SalesReturn",
            "App\Quotation" => "App\Quotation",
        ]);
    }

    public function comprobable()
    {
        return $this->morphTo();
    }

    public static function recalcular($entidadId)
    {
        $movimientos = self::where("payer_payee_id", $entidadId)
            ->orderBy("created_at", "asc") // Orden cronológico correcto
            ->orderBy("id", "asc") // Desempate por ID
            ->get();

        $saldoPeso = 0;
        $saldoUsd = 0;

        foreach ($movimientos as $m) {
            $saldoPeso += $m->debe_peso - $m->haber_peso;
            $saldoUsd += $m->debe_usd - $m->haber_usd;

            // Actualizamos sin disparar eventos para evitar bucles
            self::where("id", $m->id)->update([
                "saldo_peso" => $saldoPeso,
                "saldo_usd" => $saldoUsd,
            ]);
        }
    }

    /**
     * Obtener el saldo actual de un cliente en cuenta corriente
     *
     * @param int $clienteId ID del cliente
     * @return array Saldo en pesos y USD [saldo_peso, saldo_usd]
     */
    public static function obtenerSaldoCliente($clienteId)
    {
        $ultimoMovimiento = self::where("payer_payee_id", $clienteId)
            ->orderBy("id", "desc")
            ->first();

        if (!$ultimoMovimiento) {
            return [
                'saldo_peso' => 0,
                'saldo_usd' => 0,
            ];
        }

        return [
            'saldo_peso' => $ultimoMovimiento->saldo_peso,
            'saldo_usd' => $ultimoMovimiento->saldo_usd,
        ];
    }

    /**
     * Obtener saldo disponible para pagar una factura (considerando moneda)
     *
     * @param int $clienteId ID del cliente
     * @param bool $esUsd Si la factura es en USD
     * @return float Monto disponible para pagar (negativo significa saldo a favor)
     */
    public static function saldoDisponibleParaPago($clienteId, $esUsd = false)
    {
        $saldo = self::obtenerSaldoCliente($clienteId);
        
        if ($esUsd) {
            // Para factura en USD, usar saldo en USD
            return -$saldo['saldo_usd']; // Negativo porque saldo_usd negativo = saldo a favor
        } else {
            // Para factura en pesos, usar saldo en pesos
            return -$saldo['saldo_peso']; // Negativo porque saldo_peso negativo = saldo a favor
        }
    }

    /**
     * Obtener información detallada del saldo disponible para un cliente
     * 
     * @param int $clienteId ID del cliente
     * @param bool $esUsd Indica si se necesita saldo en USD (true) o ARS (false)
     * @param int $diasMaximosAntiguedad Límite de días para considerar movimientos (0 = sin límite)
     * @param float|null $tasaConversion Tasa de cambio a usar si hay conversión de moneda
     * @param int|null $excluirFacturaId ID de factura a excluir del cálculo (para evitar incluir movimiento DEBE de factura nueva)
     * @return array Información detallada del saldo disponible
     */
    public static function obtenerInformacionSaldoDisponible($clienteId, $esUsd = false, $diasMaximosAntiguedad = 0, $tasaConversion = null, $excluirFacturaId = null)
    {
        // Obtener saldo actual del cliente
        $query = self::where('payer_payee_id', $clienteId);
        
        // Si se especificó un límite de antigüedad, filtrar por fecha
        if ($diasMaximosAntiguedad > 0) {
            $fechaLimite = now()->subDays($diasMaximosAntiguedad);
            $query->where('created_at', '>=', $fechaLimite);
        }
        
        // Excluir movimiento de factura específica si se proporciona
        if ($excluirFacturaId) {
            $query->where(function($q) use ($excluirFacturaId) {
                $q->where('comprobable_type', '!=', 'App\Invoice')
                  ->orWhere('comprobable_id', '!=', $excluirFacturaId);
            });
        }
        
        $ultimoMovimiento = $query->orderBy('created_at', 'desc')->orderBy('id', 'desc')->first();

        $saldoPeso = $ultimoMovimiento ? $ultimoMovimiento->saldo_peso : 0;
        $saldoUsd = $ultimoMovimiento ? $ultimoMovimiento->saldo_usd : 0;

        // Determinar saldo disponible según moneda
        $saldoDisponible = 0;
        $moneda = '';
        $conversionAplicada = false;
        $tasaUsada = $tasaConversion;
        
        if ($esUsd) {
            // Saldo disponible en USD
            $saldoDisponible = -$saldoUsd; // Negativo porque saldo_usd negativo = saldo a favor
            
            // Si no hay saldo en USD pero hay en ARS y tenemos tasa, convertir
            if ($saldoDisponible <= 0 && $saldoPeso < 0 && $tasaConversion && $tasaConversion > 1) {
                $saldoDisponible = -$saldoPeso / $tasaConversion;
                $conversionAplicada = true;
                $tasaUsada = $tasaConversion;
            }
            $moneda = 'USD';
        } else {
            // Saldo disponible en ARS
            $saldoDisponible = -$saldoPeso; // Negativo porque saldo_peso negativo = saldo a favor
            
            // Si no hay saldo en ARS pero hay en USD y tenemos tasa, convertir
            if ($saldoDisponible <= 0 && $saldoUsd < 0 && $tasaConversion && $tasaConversion > 1) {
                $saldoDisponible = -$saldoUsd * $tasaConversion;
                $conversionAplicada = true;
                $tasaUsada = $tasaConversion;
            }
            $moneda = 'Pesos';
        }

        // Obtener historial reciente de movimientos
        $movimientosRecientes = self::where('payer_payee_id', $clienteId)
            ->orderBy('created_at', 'desc')
            ->orderBy('id', 'desc')
            ->limit(10)
            ->get()
            ->map(function($movimiento) {
                return [
                    'id' => $movimiento->id,
                    'tipo' => $movimiento->comprobable_type,
                    'debe_peso' => $movimiento->debe_peso,
                    'haber_peso' => $movimiento->haber_peso,
                    'debe_usd' => $movimiento->debe_usd,
                    'haber_usd' => $movimiento->haber_usd,
                    'saldo_peso' => $movimiento->saldo_peso,
                    'saldo_usd' => $movimiento->saldo_usd,
                    'nota' => $movimiento->nota,
                    'fecha' => $movimiento->created_at->format('Y-m-d H:i:s'),
                ];
            });

        return [
            'cliente_id' => $clienteId,
            'moneda' => $moneda,
            'saldo_disponible' => $saldoDisponible,
            'saldo_formateado' => ($saldoDisponible > 0 ? '$' . number_format($saldoDisponible, 2) : '$0.00'),
            'saldo_peso_actual' => $saldoPeso,
            'saldo_usd_actual' => $saldoUsd,
            'tiene_saldo_a_favor' => $saldoDisponible > 0,
            'dias_antiguedad_filtro' => $diasMaximosAntiguedad,
            'movimientos_recientes' => $movimientosRecientes,
            'fecha_consulta' => now()->format('Y-m-d H:i:s'),
            'conversion_aplicada' => $conversionAplicada,
            'tasa_usada' => $tasaUsada,
            'saldo_original_peso' => $saldoPeso,
            'saldo_original_usd' => $saldoUsd,
        ];
    }

    /**
     * Pagar una factura automáticamente desde el saldo a favor en cuenta corriente
     * 
     * @param int $invoiceId ID de la factura
     * @param int $clientId ID del cliente
     * @param int $diasMaximosAntiguedad Días máximos de antigüedad del saldo a usar (0 = sin límite)
     * @return array Resultado de la operación con detalles del pago
     */
    public static function pagarFacturaDesdeSaldoAFavor($invoiceId, $clientId, $diasMaximosAntiguedad = 0, $notaPersonalizada = null)
    {
        try {
            DB::beginTransaction();
            
            \Log::info('Iniciando pago automático desde saldo a favor', [
                'invoice_id' => $invoiceId,
                'client_id' => $clientId,
                'dias_maximos_antiguedad' => $diasMaximosAntiguedad,
                'timestamp' => now()
            ]);

            // Obtener la factura
            $invoice = \App\Invoice::find($invoiceId);
            if (!$invoice) {
                return ['success' => false, 'message' => 'Factura no encontrada'];
            }

            // Verificar si la factura ya está pagada
            if ($invoice->status == 'Paid') {
                return ['success' => false, 'message' => 'La factura ya está pagada'];
            }

            // Obtener información del saldo disponible usando la nueva función
            // Necesitamos una tasa para conversión si es necesaria
            $tasaConversion = $invoice->tasa ?? 1;
            $infoSaldo = self::obtenerInformacionSaldoDisponible(
                $clientId, 
                $invoice->is_usd, 
                $diasMaximosAntiguedad,
                $tasaConversion,
                $invoiceId  // Excluir movimiento de factura actual del cálculo
            );
            
            $saldoDisponible = $infoSaldo['saldo_disponible'];
            $saldoPeso = $infoSaldo['saldo_peso_actual'];
            $saldoUsd = $infoSaldo['saldo_usd_actual'];

            // Verificar si hay saldo a favor disponible
            if ($saldoDisponible <= 0) {
                $mensaje = 'El cliente no tiene saldo a favor disponible';
                if ($infoSaldo['conversion_aplicada']) {
                    $mensaje .= ' (se intentó conversión con tasa ' . $infoSaldo['tasa_usada'] . ')';
                }
                if ($diasMaximosAntiguedad > 0) {
                    $mensaje .= ' dentro de los últimos ' . $diasMaximosAntiguedad . ' días';
                }
                return ['success' => false, 'message' => $mensaje];
            }

            // Calcular monto a pagar (no puede exceder el total de la factura ni el saldo disponible)
            $montoAPagar = min($saldoDisponible, $invoice->grand_total - $invoice->paid);
            
            // Si se aplicó conversión, necesitamos ajustar el monto a descontar del saldo original
            $montoADescontarPeso = 0;
            $montoADescontarUsd = 0;
            
            if ($infoSaldo['conversion_aplicada']) {
                if ($invoice->is_usd) {
                    // Factura en USD, saldo original en ARS
                    $montoADescontarPeso = $montoAPagar * $infoSaldo['tasa_usada'];
                    $montoADescontarUsd = 0;
                } else {
                    // Factura en ARS, saldo original en USD
                    $montoADescontarUsd = $montoAPagar / $infoSaldo['tasa_usada'];
                    $montoADescontarPeso = 0;
                }
            } else {
                // No hubo conversión
                if ($invoice->is_usd) {
                    $montoADescontarUsd = $montoAPagar;
                    $montoADescontarPeso = 0;
                } else {
                    $montoADescontarPeso = $montoAPagar;
                    $montoADescontarUsd = 0;
                }
            }
        
            if ($montoAPagar <= 0) {
                return ['success' => false, 'message' => 'No hay monto pendiente por pagar'];
            }

            // Buscar método de pago "Gasto cc" (similar a devoluciones)
            $methodP = \App\PaymentMethod::where('name', 'like', '%Gasto cc')->first();
            if (!$methodP) {
                return ['success' => false, 'message' => 'Método de pago "Gasto cc" no configurado'];
            }

            // Buscar rubro de venta o usar uno por defecto
            $rubroVenta = \App\ChartOfAccount::where('type', 'income')
                ->where('name', 'like', '%Venta%')
                ->where('company_id', $invoice->company_id)
                ->first();

            if (!$rubroVenta) {
                $rubroVenta = \App\ChartOfAccount::where('type', 'income')
                    ->where('company_id', $invoice->company_id)
                    ->first();
            }

            if (!$rubroVenta) {
                $rubroVenta = \App\ChartOfAccount::where('company_id', $invoice->company_id)
                    ->first();
                    
                if (!$rubroVenta) {
                    return ['success' => false, 'message' => 'No se encontró un rubro contable para la compañía'];
                }
            }

            // Crear transacción para registrar el pago desde cuenta corriente
            // No es de tipo dr_cr porque el dinero ya está en la cuenta corriente del cliente
            // Usamos withoutEvents para evitar que el TransactionObserver cree un movimiento duplicado en cuenta_corriente
            $transaction = \App\Transaction::withoutEvents(function () use ($invoice, $rubroVenta, $methodP, $montoAPagar, $clientId, $invoiceId, $notaPersonalizada) {
                $transaction = new \App\Transaction();
                $transaction->trans_date = date('Y-m-d');
                $transaction->chart_id = $rubroVenta->id;
                $transaction->type = 'cc_expense';
                $transaction->dr_cr = 'cc';
                $transaction->amount = $montoAPagar;
                
                // Asignar montos según moneda
                if ($invoice->is_usd) {
                    $transaction->amount_usd = $montoAPagar;
                    $transaction->amount_peso = 0;
                } else {
                    $transaction->amount_peso = $montoAPagar;
                    $transaction->amount_usd = 0;
                }
                
                $transaction->base_amount = $montoAPagar;
                $transaction->payer_payee_id = $clientId;
                $transaction->payment_method_id = $methodP->id;
                $transaction->invoice_id = $invoiceId;
                $transaction->note = $notaPersonalizada ?? 'Factura #' . $invoice->invoice_number . ' pagada automáticamente desde saldo a favor en cuenta corriente';
                $transaction->company_id = $invoice->company_id;
                $transaction->usd = $invoice->is_usd ? 1 : 0;
                $transaction->tasa = $invoice->tasa ?? 1;
                
                $transaction->save();
                return $transaction;
            });

            // Buscar si ya existe un movimiento para esta factura (creado por el observer)
            $movimientoExistente = self::where('comprobable_type', 'App\Invoice')
                ->where('comprobable_id', $invoiceId)
                ->where('payer_payee_id', $clientId)
                ->where('nota', 'like', 'Factura #%')
                ->where('fue_revertido', 0)
                ->first();
                
            \Log::debug('DEBUG pagarFacturaDesdeSaldoAFavor - Buscando movimiento existente', [
                'invoice_id' => $invoiceId,
                'client_id' => $clientId,
                'movimiento_existente_id' => $movimientoExistente ? $movimientoExistente->id : null,
                'movimiento_existente_debe_usd' => $movimientoExistente ? $movimientoExistente->debe_usd : 0,
                'movimiento_existente_debe_peso' => $movimientoExistente ? $movimientoExistente->debe_peso : 0,
                'movimiento_existente_nota' => $movimientoExistente ? $movimientoExistente->nota : 'NO ENCONTRADO',
            ]);

            // Determinar si es pago completo o parcial
            $esPagoCompleto = ($montoAPagar >= $invoice->grand_total - $invoice->paid);
            
            \Log::debug('DEBUG pagarFacturaDesdeSaldoAFavor - Determinando tipo de pago', [
                'montoAPagar' => $montoAPagar,
                'invoice_grand_total' => $invoice->grand_total,
                'invoice_paid' => $invoice->paid,
                'esPagoCompleto' => $esPagoCompleto,
                'condicion' => $montoAPagar . ' >= ' . ($invoice->grand_total - $invoice->paid),
            ]);
            
            // Si existe un movimiento DEBE para esta factura Y es pago COMPLETO, ELIMINARLO y crear solo un movimiento neto
            if ($movimientoExistente && $movimientoExistente->debe_peso + $movimientoExistente->debe_usd > 0 && $esPagoCompleto) {
                \Log::debug('DEBUG pagarFacturaDesdeSaldoAFavor - Entrando en rama de pago COMPLETO', [
                    'movimiento_id_a_eliminar' => $movimientoExistente->id,
                    'debe_total' => $movimientoExistente->debe_peso + $movimientoExistente->debe_usd,
                ]);
                // Eliminar el movimiento de creación de factura (solo para pagos completos)
                \Log::debug('DEBUG pagarFacturaDesdeSaldoAFavor - Eliminando movimiento de factura', [
                    'movimiento_id' => $movimientoExistente->id,
                    'movimiento_nota' => $movimientoExistente->nota,
                ]);
                
                // Verificar que el movimiento existe antes de eliminarlo
                $movimientoParaEliminar = self::find($movimientoExistente->id);
                if ($movimientoParaEliminar) {
                    \Log::debug('DEBUG pagarFacturaDesdeSaldoAFavor - Movimiento encontrado, procediendo a eliminar', [
                        'movimiento_id' => $movimientoParaEliminar->id,
                        'debe_usd' => $movimientoParaEliminar->debe_usd,
                    ]);
                    $movimientoParaEliminar->delete();
                    
                    // Verificar que se eliminó
                    $movimientoDespuesDeEliminar = self::find($movimientoExistente->id);
                    \Log::debug('DEBUG pagarFacturaDesdeSaldoAFavor - Verificando eliminación', [
                        'movimiento_despues_de_eliminar' => $movimientoDespuesDeEliminar ? 'EXISTE' : 'ELIMINADO',
                    ]);
                } else {
                    \Log::debug('DEBUG pagarFacturaDesdeSaldoAFavor - Movimiento no encontrado para eliminar', [
                        'movimiento_id_buscado' => $movimientoExistente->id,
                    ]);
                }
                
                // Crear un solo movimiento neto que represente la factura pagada automáticamente
                $movimientoNeto = new self();
                $movimientoNeto->payer_payee_id = $clientId;
                $movimientoNeto->comprobable_type = 'App\Transaction';
                $movimientoNeto->comprobable_id = $transaction->id;
                
                // Para una factura pagada automáticamente desde saldo a favor:
                // - DEBE: monto de la factura (disminuye el saldo negativo/saldo a favor)
                // - HABER: 0 (no hay crédito porque se usó saldo existente)
                // El efecto neto es reducir el saldo a favor del cliente (hacerlo menos negativo)
                if ($infoSaldo['conversion_aplicada']) {
                    // Hubo conversión de moneda
                    if ($invoice->is_usd) {
                        // Factura en USD, se descontó de saldo en ARS
                        $movimientoNeto->debe_usd = $montoAPagar;
                        $movimientoNeto->haber_usd = 0;
                        $movimientoNeto->debe_peso = $montoADescontarPeso;
                        $movimientoNeto->haber_peso = 0;
                    } else {
                        // Factura en ARS, se descontó de saldo en USD
                        $movimientoNeto->debe_peso = $montoAPagar;
                        $movimientoNeto->haber_peso = 0;
                        $movimientoNeto->debe_usd = $montoADescontarUsd;
                        $movimientoNeto->haber_usd = 0;
                    }
                } else {
                    // No hubo conversión
                    if ($invoice->is_usd) {
                        $movimientoNeto->debe_usd = $montoAPagar;
                        $movimientoNeto->haber_usd = 0;
                        $movimientoNeto->debe_peso = 0;
                        $movimientoNeto->haber_peso = 0;
                    } else {
                        $movimientoNeto->debe_peso = $montoAPagar;
                        $movimientoNeto->haber_peso = 0;
                        $movimientoNeto->debe_usd = 0;
                        $movimientoNeto->haber_usd = 0;
                    }
                }
                
                $movimientoNeto->tasa_cambio = $invoice->tasa ?? 1;
                
                // Determinar símbolo de moneda
                $simboloMoneda = $invoice->is_usd ? 'USD ' : '$';
                
                $movimientoNeto->nota = $notaPersonalizada ?? 'Factura #' . $invoice->invoice_number . 
                                       ' pagada automáticamente desde saldo a favor: ' . $simboloMoneda . number_format($montoAPagar, 2);
                $movimientoNeto->fue_revertido = 0;
                
                $movimientoNeto->save();
                
                \Log::debug('DEBUG pagarFacturaDesdeSaldoAFavor - Movimiento neto creado para pago completo', [
                    'movimiento_neto_id' => $movimientoNeto->id,
                    'debe_usd' => $movimientoNeto->debe_usd,
                    'haber_usd' => $movimientoNeto->haber_usd,
                    'debe_peso' => $movimientoNeto->debe_peso,
                    'haber_peso' => $movimientoNeto->haber_peso,
                    'nota' => $movimientoNeto->nota,
                ]);
                
                // Verificar movimientos actuales después de crear el neto
                $movimientosDespuesDeNeto = self::where('payer_payee_id', $clientId)
                    ->orderBy('id', 'asc')
                    ->get()
                    ->map(function($mov) {
                        return [
                            'id' => $mov->id,
                            'tipo' => $mov->comprobable_type,
                            'debe_usd' => $mov->debe_usd,
                            'haber_usd' => $mov->haber_usd,
                            'saldo_usd' => $mov->saldo_usd,
                            'nota' => $mov->nota,
                        ];
                    });
                
                \Log::debug('DEBUG pagarFacturaDesdeSaldoAFavor - Movimientos después de crear neto', [
                    'movimientos' => $movimientosDespuesDeNeto,
                    'count' => $movimientosDespuesDeNeto->count(),
                ]);
                
                $movimientoPago = $movimientoNeto;
            } else {
                \Log::debug('DEBUG pagarFacturaDesdeSaldoAFavor - Entrando en rama de pago PARCIAL o sin movimiento existente', [
                    'tiene_movimiento_existente' => !!$movimientoExistente,
                    'es_pago_completo' => $esPagoCompleto,
                ]);
                // PAGO PARCIAL: Crear dos movimientos:
                // 1. DEBE para cancelar el saldo a favor (llevar saldo a 0)
                // 2. HABER para aplicar el pago a la factura
                
                // 1. Movimiento para cancelar saldo a favor (DEBE)
                $movimientoCancelacionSaldo = new self();
                $movimientoCancelacionSaldo->payer_payee_id = $clientId;
                $movimientoCancelacionSaldo->comprobable_type = 'App\Transaction';
                $movimientoCancelacionSaldo->comprobable_id = $transaction->id;
                
                // Cancelar el saldo a favor: DEBE del monto del saldo a favor
                if ($infoSaldo['conversion_aplicada']) {
                    // Hubo conversión de moneda
                    if ($invoice->is_usd) {
                        // Factura en USD, se descontó de saldo en ARS
                        $movimientoCancelacionSaldo->debe_usd = $montoAPagar;
                        $movimientoCancelacionSaldo->haber_usd = 0;
                        $movimientoCancelacionSaldo->debe_peso = $montoADescontarPeso;
                        $movimientoCancelacionSaldo->haber_peso = 0;
                    } else {
                        // Factura en ARS, se descontó de saldo en USD
                        $movimientoCancelacionSaldo->debe_peso = $montoAPagar;
                        $movimientoCancelacionSaldo->haber_peso = 0;
                        $movimientoCancelacionSaldo->debe_usd = $montoADescontarUsd;
                        $movimientoCancelacionSaldo->haber_usd = 0;
                    }
                } else {
                    // No hubo conversión
                    if ($invoice->is_usd) {
                        $movimientoCancelacionSaldo->debe_usd = $montoAPagar;
                        $movimientoCancelacionSaldo->haber_usd = 0;
                        $movimientoCancelacionSaldo->debe_peso = 0;
                        $movimientoCancelacionSaldo->haber_peso = 0;
                    } else {
                        $movimientoCancelacionSaldo->debe_peso = $montoAPagar;
                        $movimientoCancelacionSaldo->haber_peso = 0;
                        $movimientoCancelacionSaldo->debe_usd = 0;
                        $movimientoCancelacionSaldo->haber_usd = 0;
                    }
                }
                
                $movimientoCancelacionSaldo->tasa_cambio = $invoice->tasa ?? 1;
                $movimientoCancelacionSaldo->nota = $notaPersonalizada ?? 'Cancelación de saldo a favor: ' . ($invoice->is_usd ? 'USD ' : '$') . number_format($montoAPagar, 2) . 
                                                  ' aplicado a Factura #' . $invoice->invoice_number;
                $movimientoCancelacionSaldo->fue_revertido = 0;
                $movimientoCancelacionSaldo->save();
                
                // 2. Movimiento de pago parcial (HABER para reducir la deuda de la factura)
                $movimientoPagoParcial = new self();
                $movimientoPagoParcial->payer_payee_id = $clientId;
                $movimientoPagoParcial->comprobable_type = 'App\Transaction';
                $movimientoPagoParcial->comprobable_id = $transaction->id;
                
                // Pago parcial: HABER del monto pagado
                if ($infoSaldo['conversion_aplicada']) {
                    // Hubo conversión de moneda
                    if ($invoice->is_usd) {
                        // Factura en USD, se aplicó pago en USD
                        $movimientoPagoParcial->debe_usd = 0;
                        $movimientoPagoParcial->haber_usd = $montoAPagar;
                        $movimientoPagoParcial->debe_peso = 0;
                        $movimientoPagoParcial->haber_peso = 0;
                    } else {
                        // Factura en ARS, se aplicó pago en ARS
                        $movimientoPagoParcial->debe_peso = 0;
                        $movimientoPagoParcial->haber_peso = $montoAPagar;
                        $movimientoPagoParcial->debe_usd = 0;
                        $movimientoPagoParcial->haber_usd = 0;
                    }
                } else {
                    // No hubo conversión
                    if ($invoice->is_usd) {
                        $movimientoPagoParcial->debe_usd = 0;
                        $movimientoPagoParcial->haber_usd = $montoAPagar;
                        $movimientoPagoParcial->debe_peso = 0;
                        $movimientoPagoParcial->haber_peso = 0;
                    } else {
                        $movimientoPagoParcial->debe_peso = 0;
                        $movimientoPagoParcial->haber_peso = $montoAPagar;
                        $movimientoPagoParcial->debe_usd = 0;
                        $movimientoPagoParcial->haber_usd = 0;
                    }
                }
                
                $movimientoPagoParcial->tasa_cambio = $invoice->tasa ?? 1;
                
                // Determinar símbolo de moneda
                $simboloMoneda = $invoice->is_usd ? 'USD ' : '$';
                
                $movimientoPagoParcial->nota = $notaPersonalizada ?? 'Pago parcial desde saldo a favor: ' . $simboloMoneda . number_format($montoAPagar, 2) . 
                                             ' aplicado a Factura #' . $invoice->invoice_number;
                $movimientoPagoParcial->fue_revertido = 0;
                
                $movimientoPagoParcial->save();
                
                $movimientoPago = $movimientoPagoParcial;
            }

            // Actualizar factura
            $invoice->paid = $invoice->paid + $montoAPagar;
        
            if (round($invoice->paid, 2) >= $invoice->grand_total) {
                $invoice->status = 'Paid';
            } else if (round($invoice->paid, 2) > 0 && (round($invoice->paid, 2) < $invoice->grand_total)) {
                $invoice->status = 'Partially_Paid';
            }
        
            $invoice->save();
            
            \Log::debug('DEBUG pagarFacturaDesdeSaldoAFavor - Factura actualizada', [
                'invoice_id' => $invoice->id,
                'paid' => $invoice->paid,
                'status' => $invoice->status,
                'grand_total' => $invoice->grand_total,
            ]);

            // Recalcular saldos después del pago automático
            \Log::debug('DEBUG pagarFacturaDesdeSaldoAFavor - Llamando a recalcular', [
                'client_id' => $invoice->client_id,
            ]);
            self::recalcular($invoice->client_id);
            
            // Verificar movimientos finales
            $movimientosFinales = self::where('payer_payee_id', $clientId)
                ->orderBy('id', 'asc')
                ->get()
                ->map(function($mov) {
                    return [
                        'id' => $mov->id,
                        'tipo' => $mov->comprobable_type,
                        'debe_usd' => $mov->debe_usd,
                        'haber_usd' => $mov->haber_usd,
                        'saldo_usd' => $mov->saldo_usd,
                        'nota' => $mov->nota,
                    ];
                });
            
            \Log::debug('DEBUG pagarFacturaDesdeSaldoAFavor - Movimientos finales después de recalcular', [
                'movimientos' => $movimientosFinales,
                'count' => $movimientosFinales->count(),
                'ultimo_saldo' => $movimientosFinales->last()['saldo_usd'] ?? 0,
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => 'Pago automático realizado: ' . ($invoice->is_usd ? 'USD ' : '$ ') . number_format($montoAPagar, 2),
                'monto_pagado' => $montoAPagar,
                'saldo_restante' => $invoice->grand_total - $invoice->paid,
                'nuevo_status' => $invoice->status,
                'saldo_anterior_cliente' => $saldoDisponible + $montoAPagar,
                'saldo_posterior_cliente' => $saldoDisponible - $montoAPagar,
                'detalle' => 'Se aplicó saldo a favor del cliente para pagar la factura automáticamente',
                'dias_antiguedad_filtro' => $diasMaximosAntiguedad,
                'transaction_id' => $transaction->id
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al pagar factura desde saldo a favor: ' . $e->getMessage(), [
                'invoice_id' => $invoiceId,
                'client_id' => $clientId,
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()
            ]);

            return [
                'success' => false,
                'message' => 'Error al procesar pago automático: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Revertir un pago automático desde saldo a favor
     * 
     * @param int $movimientoId ID del movimiento en cuenta corriente a revertir
     * @param string $motivo Motivo de la reversión
     * @return array Resultado de la operación
     */
    public static function revertirPagoAutomatico($movimientoId, $motivo = '')
    {
        try {
            DB::beginTransaction();
            
            \Log::info('Iniciando reversión de pago automático', [
                'movimiento_id' => $movimientoId,
                'motivo' => $motivo,
                'timestamp' => now()
            ]);

            // Obtener el movimiento en cuenta corriente a revertir
            $movimiento = self::find($movimientoId);
            if (!$movimiento) {
                return ['success' => false, 'message' => 'Movimiento en cuenta corriente no encontrado'];
            }

            // Verificar que sea un movimiento de pago automático (por nota)
            // Acepta tanto movimientos tradicionales como movimientos netos
            $esPagoAutomaticoTradicional = strpos($movimiento->nota, 'Pago automático desde saldo a favor') !== false;
            $esMovimientoNeto = strpos($movimiento->nota, 'pagada automáticamente desde saldo a favor') !== false;
            
            if (!$esPagoAutomaticoTradicional && !$esMovimientoNeto) {
                return ['success' => false, 'message' => 'Este movimiento no es un pago automático y no puede ser revertido con este método'];
            }

            // Verificar que no esté ya revertido
            if ($movimiento->fue_revertido) {
                return ['success' => false, 'message' => 'Este movimiento ya fue revertido anteriormente'];
            }

            // Obtener la factura relacionada
            $invoice = \App\Invoice::find($movimiento->comprobable_id);
            if (!$invoice) {
                return ['success' => false, 'message' => 'Factura relacionada no encontrada'];
            }

            // Verificar que la factura no esté anulada o eliminada
            if ($invoice->status == 'Cancelled') {
                return ['success' => false, 'message' => 'La factura está anulada, no se puede revertir el pago'];
            }

            // Determinar si es un movimiento neto (DEBE>0, HABER=0 para pagos automáticos)
            $esMovimientoNeto = ($movimiento->debe_peso > 0 && $movimiento->haber_peso == 0 && 
                               $movimiento->debe_usd == 0 && $movimiento->haber_usd == 0) ||
                               ($movimiento->debe_peso == 0 && $movimiento->haber_peso == 0 && 
                               $movimiento->debe_usd > 0 && $movimiento->haber_usd == 0);
            
            if ($esMovimientoNeto) {
                // Para movimientos netos, el monto a revertir es el DEBE del movimiento
                $montoRevertir = $movimiento->debe_peso + $movimiento->debe_usd;
                
                // Calcular nuevo estado de la factura después de revertir el pago
                $nuevoPagado = $invoice->paid - $montoRevertir;
                
                // Validar que no quede pagado negativo
                if ($nuevoPagado < 0) {
                    return ['success' => false, 'message' => 'No se puede revertir: el pago revertido dejaría un saldo pagado negativo'];
                }

                // Crear movimiento de reversión que representa la factura NO PAGADA
                $movimientoReversion = new self();
                $movimientoReversion->payer_payee_id = $movimiento->payer_payee_id;
                $movimientoReversion->comprobable_type = 'App\Invoice';
                $movimientoReversion->comprobable_id = $invoice->id;
                
                // Para revertir un movimiento neto, creamos un movimiento HABER
                // que cancela el DEBE original (restaura el saldo a favor)
                if ($invoice->is_usd) {
                    $movimientoReversion->debe_usd = 0;
                    $movimientoReversion->haber_usd = $montoRevertir;
                    $movimientoReversion->debe_peso = 0;
                    $movimientoReversion->haber_peso = 0;
                } else {
                    $movimientoReversion->debe_peso = 0;
                    $movimientoReversion->haber_peso = $montoRevertir;
                    $movimientoReversion->debe_usd = 0;
                    $movimientoReversion->haber_usd = 0;
                }
                
                $movimientoReversion->tasa_cambio = $invoice->tasa ?? 1;
                $movimientoReversion->nota = 'Reversión de pago automático: Factura #' . $invoice->invoice_number . 
                                           ' restaurada como pendiente | Motivo: ' . ($motivo ?: 'Corrección del sistema');
                $movimientoReversion->fue_revertido = 0;
                
                $movimientoReversion->save();

                // Marcar el movimiento original como revertido
                $movimiento->fue_revertido = 1;
                $movimiento->movimiento_reversion_id = $movimientoReversion->id;
            } else {
                // Para movimientos tradicionales (con HABER)
                // Calcular monto a revertir
                $montoRevertir = $movimiento->haber_peso + $movimiento->haber_usd;
                
                // Calcular nuevo estado de la factura después de revertir el pago
                $nuevoPagado = $invoice->paid - $montoRevertir;
                
                // Validar que no quede pagado negativo
                if ($nuevoPagado < 0) {
                    return ['success' => false, 'message' => 'No se puede revertir: el pago revertido dejaría un saldo pagado negativo'];
                }

                // Crear movimiento de reversión en cuenta corriente (DEBE para restaurar saldo)
                $movimientoReversion = new self();
                $movimientoReversion->payer_payee_id = $movimiento->payer_payee_id;
                $movimientoReversion->comprobable_type = 'App\Invoice';
                $movimientoReversion->comprobable_id = $invoice->id;
                
                // Invertir los montos: si el pago original fue DEBE, la reversión es HABER
                $movimientoReversion->debe_peso = 0;
                $movimientoReversion->haber_peso = $movimiento->debe_peso;
                $movimientoReversion->debe_usd = 0;
                $movimientoReversion->haber_usd = $movimiento->debe_usd;
                
                $movimientoReversion->tasa_cambio = $movimiento->tasa_cambio;
                $movimientoReversion->nota = 'Reversión de pago automático: ' . $movimiento->nota . ' | Motivo: ' . ($motivo ?: 'Corrección del sistema');
                $movimientoReversion->fue_revertido = 0;
                
                $movimientoReversion->save();

                // Marcar el movimiento original como revertido
                $movimiento->fue_revertido = 1;
                $movimiento->movimiento_reversion_id = $movimientoReversion->id;
            }
            $movimiento->save();

            // Actualizar la factura
            // Actualizar estado de la factura
            $invoice->paid = $nuevoPagado;
            
            if (round($invoice->paid, 2) >= $invoice->grand_total) {
                $invoice->status = 'Paid';
            } else if (round($invoice->paid, 2) > 0 && (round($invoice->paid, 2) < $invoice->grand_total)) {
                $invoice->status = 'Partially_Paid';
            } else {
                $invoice->status = 'Unpaid';
            }
            
            $invoice->save();

            // Recalcular saldos
            self::recalcular($movimiento->payer_payee_id);

            DB::commit();

            \Log::info('Reversión de pago automático completada exitosamente', [
                'movimiento_id' => $movimientoId,
                'reversion_id' => $movimientoReversion->id,
                'invoice_id' => $invoice->id,
                'monto_revertido' => $montoRevertir,
                'nuevo_estado_factura' => $invoice->status
            ]);

            return [
                'success' => true,
                'message' => 'Pago automático revertido exitosamente: $' . number_format($montoRevertir, 2),
                'movimiento_reversion_id' => $movimientoReversion->id,
                'monto_revertido' => $montoRevertir,
                'nuevo_estado_factura' => $invoice->status,
                'nuevo_pagado_factura' => $invoice->paid,
                'detalle' => 'Se revirtió el pago automático y se restauró el saldo del cliente'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error al revertir pago automático: ' . $e->getMessage(), [
                'movimiento_id' => $movimientoId,
                'motivo' => $motivo,
                'trace' => $e->getTraceAsString(),
                'timestamp' => now()
            ]);

            return [
                'success' => false,
                'message' => 'Error al revertir pago automático: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Obtener movimientos automáticos revertibles para una factura
     * 
     * @param int $invoiceId ID de la factura
     * @return array Lista de movimientos automáticos que pueden ser revertidos
     */
    public static function obtenerPagosAutomaticosRevertibles($invoiceId)
    {
        $movimientos = self::where('comprobable_type', 'App\Invoice')
            ->where('comprobable_id', $invoiceId)
            ->where('nota', 'like', 'Pago automático desde saldo a favor%')
            ->where('fue_revertido', 0)
            ->orderBy('id', 'desc')
            ->get();
        
        $resultado = [];
        foreach ($movimientos as $mov) {
            $monto = $mov->haber_peso + $mov->haber_usd;
            $moneda = $mov->haber_usd > 0 ? 'USD' : 'Pesos';
            
            $resultado[] = [
                'id' => $mov->id,
                'monto' => $monto,
                'fecha' => $mov->created_at->format('Y-m-d'),
                'nota' => $mov->nota,
                'moneda' => $moneda,
                'fue_revertido' => $mov->fue_revertido,
                'puede_revertirse' => true
            ];
        }
        
        return $resultado;
    }

    /**
     * Verificar si una factura tiene pagos automáticos aplicados
     * 
     * @param int $invoiceId ID de la factura
     * @return array Información sobre pagos automáticos
     */
    public static function verificarPagosAutomaticosFactura($invoiceId)
    {
        $invoice = \App\Invoice::find($invoiceId);
        if (!$invoice) {
            return ['tiene_pagos_automaticos' => false, 'mensaje' => 'Factura no encontrada'];
        }
        
        $pagosAutomaticos = self::where('comprobable_type', 'App\Invoice')
            ->where('comprobable_id', $invoiceId)
            ->where('nota', 'like', 'Pago automático desde saldo a favor%')
            ->count();
        
        $pagosRevertidos = self::where('comprobable_type', 'App\Invoice')
            ->where('comprobable_id', $invoiceId)
            ->where('nota', 'like', 'Pago automático desde saldo a favor%')
            ->where('fue_revertido', 1)
            ->count();
        
        $totalPagosAutomaticos = self::where('comprobable_type', 'App\Invoice')
            ->where('comprobable_id', $invoiceId)
            ->where('nota', 'like', 'Pago automático desde saldo a favor%')
            ->where('fue_revertido', 0)
            ->get()
            ->sum(function($mov) {
                return $mov->haber_peso + $mov->haber_usd;
            });
        
        return [
            'tiene_pagos_automaticos' => $pagosAutomaticos > 0,
            'total_pagos_automaticos' => $pagosAutomaticos,
            'pagos_revertidos' => $pagosRevertidos,
            'pagos_activos' => $pagosAutomaticos - $pagosRevertidos,
            'monto_total_automatico' => $totalPagosAutomaticos,
            'factura_id' => $invoiceId,
            'factura_numero' => $invoice->invoice_number,
            'estado_factura' => $invoice->status,
            'pagado_factura' => $invoice->paid
        ];
    }

    /**
     * Reimputar saldo a favor FIFO a las facturas impagas más antiguas
     * 
     * @param int $clientId ID del cliente
     * @param string $notaFifo Nota personalizada para los movimientos FIFO
     * @return array Resultado de la operación
     */
    public static function reimputarSaldoFavorFIFO($clientId, $notaFifo = 'reimputacion FIFO automatica', $exceptInvoiceId = null)
    {
        try {
            $saldo = self::obtenerSaldoCliente($clientId);

            if ($saldo['saldo_peso'] >= 0 && $saldo['saldo_usd'] >= 0) {
                return ['success' => true, 'message' => 'No hay crédito disponible para FIFO'];
            }

            $invoicesQuery = \App\Invoice::where('client_id', $clientId)
                ->whereIn('status', ['Unpaid', 'Partially_Paid'])
                ->where(function ($q) {
                    $q->whereNull('paid')
                      ->orWhereRaw('paid < grand_total');
                });

            if ($exceptInvoiceId) {
                $invoicesQuery->where('id', '!=', $exceptInvoiceId);
            }

            $invoices = $invoicesQuery->orderBy('invoice_date')
                ->orderBy('id')
                ->get();

            $totalAplicado = 0;
            $creditoPeso = max(0, -$saldo['saldo_peso']);
            $creditoUsd = max(0, -$saldo['saldo_usd']);

            foreach ($invoices as $invoice) {
                $saldoPendiente = $invoice->grand_total - max(0, $invoice->paid ?? 0);
                if ($saldoPendiente <= 0) continue;

                // Verificar si hay crédito disponible en la moneda de la factura
                $creditoDisponible = $invoice->is_usd ? $creditoUsd : $creditoPeso;
                if ($creditoDisponible <= 0) {
                    // Sin crédito en esta moneda, intentar siguiente factura
                    continue;
                }

                $resultado = self::pagarFacturaDesdeSaldoAFavor($invoice->id, $clientId, 0, $notaFifo);

                if ($resultado['success']) {
                    $montoPagado = $resultado['monto_pagado'];
                    $totalAplicado += $montoPagado;
                    // Consumir del crédito local para evitar aplicar el mismo crédito múltiples veces
                    if ($invoice->is_usd) {
                        $creditoUsd = max(0, $creditoUsd - $montoPagado);
                    } else {
                        $creditoPeso = max(0, $creditoPeso - $montoPagado);
                    }
                    \Log::info("FIFO: Pago aplicado a factura #{$invoice->invoice_number} - \${$montoPagado}");
                } else {
                    \Log::warning("FIFO: No se pudo pagar factura #{$invoice->invoice_number}: " . $resultado['message']);
                }
            }

            return [
                'success' => true,
                'total_aplicado' => $totalAplicado,
                'message' => "FIFO: Se aplicaron \${$totalAplicado} a facturas pendientes"
            ];
        } catch (\Throwable $e) {
            \Log::error("Error en reimputarSaldoFavorFIFO para cliente {$clientId}: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'message' => 'Error en FIFO: ' . $e->getMessage()
            ];
        }
    }
}