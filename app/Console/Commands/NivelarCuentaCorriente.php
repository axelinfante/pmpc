<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class NivelarCuentaCorriente extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'NivelarCuentaCorriente
                            {--cliente= : ID del cliente específico a procesar}
                            {--pagar-facturas : Pagar facturas pendientes con saldo a favor}
                            {--dias-maximos=30 : Días máximos de antigüedad para pagar facturas}
                            {--dry-run : Mostrar cambios sin aplicarlos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Nivelación de cuentas corrientes incluyendo facturas, pagos, devoluciones, retiros y gastos. Opcionalmente procesar solo un cliente específico.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Determina si hay conversión de moneda y calcula los detalles
     *
     * @param object $transaccion
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

        // Caso 1: Transacción con tasa aplicada y montos en ambas monedas
        if ($transaccion->tasa && $transaccion->tasa != 1 && ($m_peso > 0 && $m_usd > 0)) {
            $tiene_conversion = true;

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
            if ($moneda_factura === 'USD' && $m_peso > 0 && $m_usd == 0) {
                $tiene_conversion = true;
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
            $monto_original = $m_peso;
            $moneda_original = 'ARS';
            $monto_convertido = $m_peso / $tasa_aplicada;
            $moneda_convertida = 'USD';
            $detalle_conversion = "Transacción marcada como USD pero monto en ARS";
        } elseif ($transaccion->usd == 0 && $m_usd > 0 && $m_peso == 0) {
            $tiene_conversion = true;
            $monto_original = $m_usd;
            $moneda_original = 'USD';
            $monto_convertido = $m_usd * $tasa_aplicada;
            $moneda_convertida = 'ARS';
            $detalle_conversion = "Transacción marcada como ARS pero monto en USD";
        }

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
     * Procesar pagos desde cuenta corriente similar a pagarFacturaDesdeSaldoAFavor
     *
     * @param object $transaccion
     * @param object|null $invoice_data
     * @return array|null
     */
    private function procesarPagoDesdeCuentaCorriente($transaccion, $invoice_data)
    {
        if (!$invoice_data) {
            return null;
        }

        // Determinar si es pago completo o parcial
        $es_pago_completo = false;
        $monto_factura = $invoice_data->grand_total;
        $monto_pagado_anteriormente = $invoice_data->paid;
        $monto_restante = $monto_factura - $monto_pagado_anteriormente;
        $monto_transaccion = $transaccion->amount;

        // Ajustar por conversión de moneda si es necesario
        if ($invoice_data->is_usd && $transaccion->usd == 0) {
            // Factura en USD, pago en ARS - convertir
            $tasa = $transaccion->tasa ?? $invoice_data->tasa ?? 1;
            $monto_transaccion_usd = $monto_transaccion / $tasa;
            $es_pago_completo = ($monto_transaccion_usd >= $monto_restante);
        } elseif (!$invoice_data->is_usd && $transaccion->usd == 1) {
            // Factura en ARS, pago en USD - convertir
            $tasa = $transaccion->tasa ?? $invoice_data->tasa ?? 1;
            $monto_transaccion_ars = $monto_transaccion * $tasa;
            $es_pago_completo = ($monto_transaccion_ars >= $monto_restante);
        } else {
            // Misma moneda
            $es_pago_completo = ($monto_transaccion >= $monto_restante);
        }

        // Determinar montos según moneda
        $monto_pago = $transaccion->amount;
        $es_usd_factura = (bool)$invoice_data->is_usd;
        $tasa_cambio = $transaccion->tasa ?? ($invoice_data->tasa ?? 1);

        if ($es_pago_completo) {
            // PAGO COMPLETO: NO crear movimiento - ya está reflejado en el saldo
            // Cuando se paga desde saldo a favor, no se debe crear movimiento adicional
            // porque el saldo ya se ajustó cuando se creó la factura
            return null;
        } else {
            // PAGO PARCIAL: Crear dos movimientos
            // 1. Cancelación de saldo a favor (DEBE)
            // 2. Pago parcial a factura (HABER)

            return [
                [
                    'fecha' => $transaccion->trans_date,
                    'created_at' => $transaccion->created_at,
                    'type' => 'App\Transaction',
                    'id' => $transaccion->id,
                    'debe_peso' => $es_usd_factura ? 0 : $monto_pago,
                    'haber_peso' => 0,
                    'debe_usd' => $es_usd_factura ? $monto_pago : 0,
                    'haber_usd' => 0,
                    'tasa' => $tasa_cambio,
                    'nota' => 'Cancelación de saldo a favor: ' . ($es_usd_factura ? 'USD ' : '$') .
                             number_format($monto_pago, 2) . ' aplicado a Factura #' . $invoice_data->invoice_number,
                    'tiene_conversion' => false,
                    'monto_original' => null,
                    'moneda_original' => null,
                    'monto_convertido' => null,
                    'moneda_convertida' => null,
                    'tasa_aplicada' => $tasa_cambio,
                    'detalle_conversion' => null
                ],
                [
                    'fecha' => $transaccion->trans_date,
                    'created_at' => $transaccion->created_at,
                    'type' => 'App\Transaction',
                    'id' => $transaccion->id,
                    'debe_peso' => 0,
                    'haber_peso' => $es_usd_factura ? 0 : $monto_pago,
                    'debe_usd' => 0,
                    'haber_usd' => $es_usd_factura ? $monto_pago : 0,
                    'tasa' => $tasa_cambio,
                    'nota' => 'Pago parcial desde saldo a favor: ' . ($es_usd_factura ? 'USD ' : '$') .
                             number_format($monto_pago, 2) . ' aplicado a Factura #' . $invoice_data->invoice_number,
                    'tiene_conversion' => false,
                    'monto_original' => null,
                    'moneda_original' => null,
                    'monto_convertido' => null,
                    'moneda_convertida' => null,
                    'tasa_aplicada' => $tasa_cambio,
                    'detalle_conversion' => null
                ]
            ];
        }
    }

    /**
     * Handle the console command.
     *
     * @return int
     */
    public function handle()
    {
        // 1. Obtenemos todos los IDs de clientes/proveedores únicos
        $clienteId = $this->option('cliente');

        if ($clienteId) {
            // Procesar solo el cliente específico
            $entidades = collect([$clienteId]);
            $this->info("Procesando solo cliente ID: " . $clienteId);
        } else {
            // Procesar todos los clientes
            $entidades = DB::table('invoices')->pluck('client_id')
                ->merge(DB::table('transactions')->pluck('payer_payee_id'))
                ->unique()->filter();
            $this->info("Iniciando nivelación de " . $entidades->count() . " entidades...");
        }

        foreach ($entidades as $entidadId) {
            // Limpiar registros existentes para esta entidad
            DB::table('cuenta_corriente')->where('payer_payee_id', $entidadId)->delete();

            // 2. Traer Facturas - ordenar por fecha de factura
            $facturas = DB::table('invoices')
                ->where('client_id', $entidadId)
                ->orderBy('invoice_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get()->map(function ($i) {
                return [
                    'fecha' => $i->invoice_date,
                    'created_at' => $i->created_at,
                    'type'  => 'App\Invoice',
                    'id'    => $i->id,
                    'invoice_id' => $i->id, // Agregar invoice_id para agrupación (mismo que id)
                    'debe_peso' => !$i->is_usd ? $i->grand_total : 0,
                    'haber_peso' => 0,
                    'debe_usd'  => $i->is_usd ? $i->grand_total : 0,
                    'haber_usd' => 0,
                    'tasa' => $i->tasa ?? 1,
                    'nota' => "Factura #{$i->invoice_number}",
                    'tiene_conversion' => false,
                    'monto_original' => null,
                    'moneda_original' => null,
                    'monto_convertido' => null,
                    'moneda_convertida' => null,
                    'tasa_aplicada' => $i->tasa ?? 1,
                    'detalle_conversion' => null
                ];
            });

            // 3. Devoluciones procesadas (HABER) - Ahora usando SalesReturn - ordenar por fecha de creación
            $devoluciones = DB::table('sales_return')
                ->leftJoin('invoices', 'invoices.id', '=', 'sales_return.invoice_id')
                ->leftJoin('contacts', 'contacts.id', '=', 'sales_return.customer_id')
                ->where('sales_return.customer_id', $entidadId)
                ->select('sales_return.*', 'invoices.is_usd as invoice_is_usd', 'invoices.tasa as tasa_inv', 'invoices.status as invoice_status', 'invoices.paid as invoice_paid', 'contacts.currency as cliente_currency')
                ->orderBy('sales_return.created_at', 'asc')
                ->orderBy('sales_return.id', 'asc')
                ->get()->map(function ($dev) {
                    // Verificar si la factura relacionada está cancelada
                    $facturaCancelada = isset($dev->invoice_status) && $dev->invoice_status == 'Canceled';
                    $facturaTienePagos = isset($dev->invoice_paid) && $dev->invoice_paid > 0;

                    // Determinar la moneda correcta
                    $isUsd = false;
                    $tasa = 1;
                    $monedaCliente = 'ARS'; // Por defecto ARS

                    if (isset($dev->invoice_is_usd)) {
                        // Usar la moneda de la factura relacionada (prioridad 1)
                        $isUsd = (bool) $dev->invoice_is_usd;
                        $tasa = $dev->tasa_inv ?? 1;
                        $monedaCliente = $isUsd ? 'USD' : 'ARS';
                    } else if (isset($dev->cliente_currency)) {
                        // Si no hay factura, usar la moneda del cliente
                        $monedaCliente = $dev->cliente_currency;
                        $isUsd = strtoupper($monedaCliente) === 'USD';
                    }

                    // Asegurar que tasa siempre tenga un valor válido
                    if (is_null($tasa) || $tasa <= 0) {
                        $tasa = 1;
                    }

                    // Determinar montos - usar grand_total de sales_return
                    $montoTotal = $dev->grand_total ?? 0;
                    $montoPeso = 0;
                    $montoUsd = 0;

                    if ($isUsd) {
                        // Si es USD, el monto total está en USD - solo afectar USD
                        $montoUsd = $montoTotal;
                        $montoPeso = 0; // No convertir a pesos
                    } else {
                        // Si es ARS, el monto total está en ARS - solo afectar ARS
                        $montoPeso = $montoTotal;
                        $montoUsd = 0; // No convertir a USD
                    }

                    // Siempre usar monto completo de la devolución para mantener consistencia de datos
                    // Factura (monto completo) + Pago (monto pagado) + Devolución (monto completo)
                    // Esto permite que el cálculo matemático dé el resultado correcto
                    // Obtener datos de la factura para logging
                    $invoice_for_log = null;
                    if ($dev->invoice_id) {
                        $invoice_for_log = DB::table('invoices')->where('id', $dev->invoice_id)->first();
                    }

                    if ($invoice_for_log) {
                        $this->info("Procesando devolución #" . $dev->id . " para factura #" . $invoice_for_log->invoice_number .
                                   " - Monto devolución: " . ($isUsd ? 'USD ' : '$') . $montoTotal .
                                   " - Monto pagado factura: " . ($isUsd ? 'USD ' : '$') . ($dev->invoice_paid ?? 0));
                    }

                    return [
                        // Usamos la fecha de creación de la devolución para que ocurra DESPUÉS del pago
                        'fecha' => $dev->created_at,
                        'created_at' => $dev->created_at,
                        'type'  => 'App\SalesReturn',
                        'id'    => $dev->id,
                        'invoice_id' => $dev->invoice_id, // Agregar invoice_id para agrupación
                        'debe_peso'  => 0,
                        'haber_peso' => $montoPeso,
                        'debe_usd'   => 0,
                        'haber_usd'  => $montoUsd,
                        'tasa'       => $tasa, // Usar la tasa (por defecto 1 si no hay factura)
                        'nota'       => "Devolución de venta" . ($dev->invoice_id ? " (Factura Relacionada)" : ""),
                        'tiene_conversion' => false,
                        'monto_original' => null,
                        'moneda_original' => null,
                        'monto_convertido' => null,
                        'moneda_convertida' => null,
                        'tasa_aplicada' => $tasa,
                        'detalle_conversion' => null
                    ];
                });

            // 4. Traer Transacciones (Pagos/Gastos/Retiros) con lógica mejorada de moneda
            // Excluir transacciones type='cc' y dr_cr='cc' que son internas del sistema
            // Ordenar por fecha de transacción y creación para mantener orden cronológico
            $pagos = DB::table('transactions')
                ->where('payer_payee_id', $entidadId)
                ->where(function ($query) {
                    $query->where('type', '!=', 'cc')
                          ->orWhere('dr_cr', '!=', 'cc');
                })
                ->orderBy('trans_date', 'asc')
                ->orderBy('created_at', 'asc')
                ->orderBy('id', 'asc')
                ->get()->map(function ($t) use ($entidadId) {
                    $es_debe = ($t->dr_cr == 'dr');

                    // LÓGICA MEJORADA PARA DETERMINAR MONTOS SEGÚN MONEDA
                    $m_peso = $t->amount_peso;
                    $m_usd  = $t->amount_usd;

                    // Determinar moneda de la factura si existe
                    $moneda_factura = null;
                    $invoice_data = null;
                    if ($t->invoice_id) {
                        $invoice_data = DB::table('invoices')->where('id', $t->invoice_id)->first();
                        if ($invoice_data) {
                            $moneda_factura = $invoice_data->is_usd ? 'USD' : 'ARS';
                        }
                    }

                    // Aplicar lógica mejorada similar a TransactionObserver
                    if (is_null($m_peso) && is_null($m_usd)) {
                        // Caso 1: Ambos montos son null
                        if ($moneda_factura === 'USD') {
                            // Factura en USD - amount está en USD
                            $m_usd = $t->amount;
                            $m_peso = 0;
                        } elseif ($moneda_factura === 'ARS') {
                            // Factura en ARS - amount está en ARS
                            $m_peso = $t->amount;
                            $m_usd = 0;
                        } else {
                            // No hay factura - determinar según campo usd de la transacción
                            if ($t->usd == 1) {
                                $m_usd = $t->amount;
                                $m_peso = 0;
                            } else {
                                $m_peso = $t->amount;
                                $m_usd = 0;
                            }
                        }
                    } elseif (!is_null($m_peso) && is_null($m_usd)) {
                        // Caso 2: Solo amount_peso tiene valor
                        $m_usd = 0;
                    } elseif (is_null($m_peso) && !is_null($m_usd)) {
                        // Caso 3: Solo amount_usd tiene valor
                        $m_peso = 0;
                    }
                    // Caso 4: Ambos tienen valor - mantener como están

                    // DETECTAR TIPO DE TRANSACCIÓN ESPECIAL - LÓGICA MEJORADA
                    $es_devolucion_dinero = false;
                    $es_ingreso_cuenta_corriente = false;
                    $es_devolucion_producto = false;
                    $es_retiro_cuenta_corriente = false;
                    $es_gasto_normal = false;
                    $es_pago_desde_cuenta_corriente = false;

                    // Detectar pagos desde cuenta corriente a facturas
                    if ($t->type == 'cc_expense' && $t->dr_cr == 'cc' && $t->invoice_id) {
                        // Verificar si es pago desde cuenta corriente a factura
                        if ($t->note && (
                            stripos($t->note, 'pagada automáticamente desde saldo a favor') !== false ||
                            stripos($t->note, 'pago desde cuenta corriente') !== false ||
                            stripos($t->note, 'pago automático desde saldo') !== false
                        )) {
                            $es_pago_desde_cuenta_corriente = true;
                        }
                        // Si no tiene nota específica pero tiene invoice_id, asumir que es pago desde cuenta corriente
                        else {
                            $es_pago_desde_cuenta_corriente = true;
                        }
                    }

                    // Detectar retiro de cuenta corriente (cc_expense sin invoice_id o con nota específica de retiro)
                    if ($t->type == 'cc_expense' && $t->dr_cr == 'cc' && !$es_pago_desde_cuenta_corriente) {
                        // Verificar si es retiro de cuenta corriente
                        if ($t->note && (
                            stripos($t->note, 'retiro de cuenta') !== false ||
                            stripos($t->note, 'retiro de saldo') !== false ||
                            stripos($t->note, 'retiro cuenta corriente') !== false
                        )) {
                            $es_retiro_cuenta_corriente = true;
                        }
                        // Si no es retiro específico, podría ser devolución de dinero
                        elseif ($t->note && (
                            stripos($t->note, 'devolución de dinero') !== false ||
                            stripos($t->note, 'devolución de saldo') !== false ||
                            stripos($t->note, 'saldo a favor') !== false
                        )) {
                            $es_devolucion_dinero = true;
                        }
                        // Si no tiene nota o no se detectó tipo específico, asumir que es retiro genérico
                        else {
                            $es_retiro_cuenta_corriente = true;
                        }
                    }

                    // Detectar ingreso a cuenta corriente (cc_income)
                    if ($t->type == 'cc_income' && $t->dr_cr == 'cc') {
                        // Verificar si es ingreso a cuenta corriente
                        if ($t->note && (
                            stripos($t->note, 'ingreso manual') !== false ||
                            stripos($t->note, 'ingreso a cuenta') !== false ||
                            stripos($t->note, 'abono a cuenta') !== false ||
                            stripos($t->note, 'depósito cuenta') !== false
                        )) {
                            $es_ingreso_cuenta_corriente = true;
                        }
                    }

                    // Detectar devolución de producto
                    if (!$es_devolucion_dinero && !$es_ingreso_cuenta_corriente && !$es_retiro_cuenta_corriente && $t->note && stripos($t->note, 'devolución de ítem') !== false) {
                        $es_devolucion_producto = true;
                    }

                    // Detectar gasto normal (no relacionado con cuenta corriente)
                    if ($t->dr_cr == 'dr' && $t->type != 'cc_expense' && $t->type != 'cc_income') {
                        $es_gasto_normal = true;
                    }

                    // Detectar ingreso a cuenta corriente sin nota específica
                    if ($t->type == 'cc_income' && $t->dr_cr == 'cc' && !$es_ingreso_cuenta_corriente) {
                        $es_ingreso_cuenta_corriente = true;
                    }

                    // Calcular debe y haber según el tipo de transacción
                    $debe_peso = 0;
                    $haber_peso = 0;
                    $debe_usd = 0;
                    $haber_usd = 0;

                    // Procesar pagos desde cuenta corriente (lógica especial)
                    if ($es_pago_desde_cuenta_corriente) {
                        $movimientos_pago = $this->procesarPagoDesdeCuentaCorriente($t, $invoice_data);
                        if ($movimientos_pago) {
                            // Devolver los movimientos especiales para procesamiento posterior
                            return $movimientos_pago;
                        } else {
                            // Retornar null para que no se procese con lógica normal
                            return null;
                        }
                    }

                    if ($es_devolucion_dinero) {
                        // DEVOLUCIÓN DE DINERO: Invertir la lógica
                        // Cuando el cliente tiene saldo negativo (nosotros le debemos) y le devolvemos dinero,
                        // registramos DEBE (reduce el saldo negativo)
                        $debe_peso = $m_peso ?? 0;
                        $haber_peso = 0;
                        $debe_usd = $m_usd ?? 0;
                        $haber_usd = 0;
                        $nota = $t->note ?? "Devolución de dinero";
                    } elseif ($es_ingreso_cuenta_corriente) {
                        // INGRESO A CUENTA CORRIENTE: El cliente ingresa dinero (aumenta su saldo a favor)
                        // Cuando el cliente ingresa dinero, registramos HABER (aumenta el saldo negativo/saldo a favor)
                        $debe_peso = 0;
                        $haber_peso = $m_peso ?? 0;
                        $debe_usd = 0;
                        $haber_usd = $m_usd ?? 0;
                        $nota = $t->note ?? "Ingreso a cuenta corriente";
                    } elseif ($es_retiro_cuenta_corriente) {
                        // RETIRO DE CUENTA CORRIENTE: El cliente retira dinero (disminuye su saldo a favor)
                        // Cuando el cliente retira dinero, registramos DEBE (disminuye el saldo negativo/saldo a favor)
                        $debe_peso = $m_peso ?? 0;
                        $haber_peso = 0;
                        $debe_usd = $m_usd ?? 0;
                        $haber_usd = 0;
                        $nota = $t->note ?? "Retiro de cuenta corriente";
                    } elseif ($es_devolucion_producto) {
                        // DEVOLUCIÓN DE PRODUCTO: Mantener lógica actual (HABER)
                        // Cuando el cliente devuelve un producto, le damos crédito (aumenta su saldo a favor)
                        $debe_peso = 0;
                        $haber_peso = $m_peso ?? 0;
                        $debe_usd = 0;
                        $haber_usd = $m_usd ?? 0;
                        $nota = $t->note ?? "Devolución de producto";
                    } elseif ($es_gasto_normal) {
                        // GASTO NORMAL: Transacción de débito normal (aumenta el debe)
                        // Estos son gastos que el cliente debe pagar
                        $debe_peso = $m_peso ?? 0;
                        $haber_peso = 0;
                        $debe_usd = $m_usd ?? 0;
                        $haber_usd = 0;
                        $nota = $t->note ?? "Gasto/Compra";
                    } else {
                        // LÓGICA NORMAL PARA TRANSACCIONES REGULARES (pagos, etc.)
                        $debe_peso = $es_debe ? ($m_peso ?? 0) : 0;
                        $haber_peso = !$es_debe ? ($m_peso ?? 0) : 0;
                        $debe_usd = $es_debe ? ($m_usd ?? 0) : 0;
                        $haber_usd = !$es_debe ? ($m_usd ?? 0) : 0;
                        $nota = $t->note ?? "Movimiento histórico (Migrado)";
                    }

                    // Determinar información de conversión
                    $info_conversion = $this->determinarConversion($t, $moneda_factura, $m_peso ?? 0, $m_usd ?? 0);

                    // Ajustar campos para conversiones con sobrante
                    if ($info_conversion['tiene_conversion'] && $info_conversion['sobrante'] && $info_conversion['moneda_sobrante']) {
                        // Para factura en ARS pagada en USD con sobrante
                        if ($moneda_factura === 'ARS' && $m_usd > 0 && $m_peso == 0) {
                            // El haber_peso debe ser el monto aplicado a la factura en ARS
                            $haber_peso = $info_conversion['monto_aplicado'] ?? 0;
                            // El haber_usd debe ser solo el sobrante en USD
                            $haber_usd = $info_conversion['sobrante'] ?? 0;
                            $debe_usd = 0;
                        }
                        // Para factura en USD pagada en ARS con sobrante
                        elseif ($moneda_factura === 'USD' && $m_peso > 0 && $m_usd == 0) {
                            // El haber_usd debe ser el monto aplicado a la factura en USD
                            $haber_usd = $info_conversion['monto_aplicado'] ?? 0;
                            // El haber_peso debe ser solo el sobrante en ARS
                            $haber_peso = $info_conversion['sobrante'] ?? 0;
                            $debe_peso = 0;
                        }
                    }

                    return [
                        'fecha' => $t->trans_date,
                        'created_at' => $t->created_at,
                        'type'  => 'App\Transaction',
                        'id'    => $t->id,
                        'invoice_id' => $t->invoice_id, // Agregar invoice_id para agrupación
                        'debe_peso'  => $debe_peso,
                        'haber_peso' => $haber_peso,
                        'debe_usd'   => $debe_usd,
                        'haber_usd'  => $haber_usd,
                        'tasa'       => $t->tasa ?? 1,
                        'nota'       => $nota,
                        'tiene_conversion' => $info_conversion['tiene_conversion'],
                        'monto_original' => $info_conversion['monto_original'],
                        'moneda_original' => $info_conversion['moneda_original'],
                        'monto_convertido' => $info_conversion['monto_convertido'],
                        'moneda_convertida' => $info_conversion['moneda_convertida'],
                        'tasa_aplicada' => $info_conversion['tasa_aplicada'],
                        'detalle_conversion' => $info_conversion['detalle_conversion'],
                        'monto_aplicado' => $info_conversion['monto_aplicado'],
                        'moneda_aplicada' => $info_conversion['moneda_aplicada'],
                        'sobrante' => $info_conversion['sobrante'],
                        'moneda_sobrante' => $info_conversion['moneda_sobrante'],
                    ];
                });

            // 5. Unir y ordenar por FECHA y luego por ID para mantener orden lógico
            // Filtrar valores null de devoluciones antes de concatenar
            $devolucionesFiltradas = $devoluciones->filter(function($item) {
                return $item !== null;
            });

            // Procesar pagos que pueden devolver múltiples movimientos
            $pagosProcesados = collect();

            foreach ($pagos as $pago) {
                if ($pago === null) {
                    // Pago completo desde cuenta corriente - no crear movimiento
                    continue;
                }

                if (is_array($pago) && isset($pago[0]) && is_array($pago[0])) {
                    // Es un array de movimientos (pago desde cuenta corriente)
                    foreach ($pago as $movimiento) {
                        $pagosProcesados->push($movimiento);
                    }
                } else {
                    // Es un movimiento individual
                    $pagosProcesados->push($pago);
                }
            }

            // Usar todas las facturas (no filtrar las pagadas completamente)
            $facturasFiltradas = $facturas;

            // Agrupar movimientos por factura para mantener orden lógico: factura → pagos → devoluciones
            $movimientosAgrupados = collect();

            // Primero agregar todas las facturas
            foreach ($facturasFiltradas as $factura) {
                $movimientosAgrupados->push($factura);

                // Buscar pagos relacionados con esta factura
                $pagosFactura = $pagosProcesados->filter(function($pago) use ($factura) {
                    if (isset($pago['invoice_id'])) {
                        return $pago['invoice_id'] == $factura['id'];
                    }

                    // Buscar por número de factura en la nota
                    if (isset($pago['nota']) && preg_match('/Factura #(\d+)/', $pago['nota'], $matches)) {
                        $invoice_number = $matches[1];
                        $invoice = DB::table('invoices')->where('invoice_number', $invoice_number)->first();
                        return $invoice && $invoice->id == $factura['id'];
                    }

                    return false;
                });

                // Agregar pagos de esta factura - ordenar por fecha antes de agregar
                $pagosFacturaOrdenados = $pagosFactura->sortBy(function($pago) {
                    return $pago['created_at'] ?? $pago['fecha'] ?? '1970-01-01';
                });

                foreach ($pagosFacturaOrdenados as $pagoFactura) {
                    $movimientosAgrupados->push($pagoFactura);
                }

                // Buscar devoluciones relacionadas con esta factura
                $devolucionesFactura = $devolucionesFiltradas->filter(function($devolucion) use ($factura) {
                    if (isset($devolucion['invoice_id'])) {
                        return $devolucion['invoice_id'] == $factura['id'];
                    }

                    // Buscar por número de factura en la nota
                    if (isset($devolucion['nota']) && preg_match('/Factura #(\d+)/', $devolucion['nota'], $matches)) {
                        $invoice_number = $matches[1];
                        $invoice = DB::table('invoices')->where('invoice_number', $invoice_number)->first();
                        return $invoice && $invoice->id == $factura['id'];
                    }

                    return false;
                });

                // Agregar devoluciones de esta factura - ordenar por fecha antes de agregar
                $devolucionesFacturaOrdenadas = $devolucionesFactura->sortBy(function($devolucion) {
                    return $devolucion['created_at'] ?? $devolucion['fecha'] ?? '1970-01-01';
                });

                foreach ($devolucionesFacturaOrdenadas as $devolucionFactura) {
                    $movimientosAgrupados->push($devolucionFactura);
                }
            }

            // Agregar movimientos que no están relacionados con facturas (transacciones generales)
            $pagosSinFactura = $pagosProcesados->filter(function($pago) use ($facturasFiltradas) {
                // Verificar si este pago ya fue agregado
                foreach ($facturasFiltradas as $factura) {
                    if (isset($pago['invoice_id']) && $pago['invoice_id'] == $factura['id']) {
                        return false;
                    }

                    if (isset($pago['nota']) && preg_match('/Factura #(\d+)/', $pago['nota'], $matches)) {
                        $invoice_number = $matches[1];
                        $invoice = DB::table('invoices')->where('invoice_number', $invoice_number)->first();
                        if ($invoice && $invoice->id == $factura['id']) {
                            return false;
                        }
                    }
                }
                return true;
            });

            $devolucionesSinFactura = $devolucionesFiltradas->filter(function($devolucion) use ($facturasFiltradas) {
                // Verificar si esta devolución ya fue agregada
                foreach ($facturasFiltradas as $factura) {
                    if (isset($devolucion['invoice_id']) && $devolucion['invoice_id'] == $factura['id']) {
                        return false;
                    }

                    if (isset($devolucion['nota']) && preg_match('/Factura #(\d+)/', $devolucion['nota'], $matches)) {
                        $invoice_number = $matches[1];
                        $invoice = DB::table('invoices')->where('invoice_number', $invoice_number)->first();
                        if ($invoice && $invoice->id == $factura['id']) {
                            return false;
                        }
                    }
                }
                return true;
            });

            // Agregar movimientos sin factura al final - ordenar por fecha antes de agregar
            $pagosSinFacturaOrdenados = $pagosSinFactura->sortBy(function($pago) {
                return $pago['created_at'] ?? $pago['fecha'] ?? '1970-01-01';
            });

            foreach ($pagosSinFacturaOrdenados as $pagoSinFactura) {
                $movimientosAgrupados->push($pagoSinFactura);
            }

            $devolucionesSinFacturaOrdenadas = $devolucionesSinFactura->sortBy(function($devolucion) {
                return $devolucion['created_at'] ?? $devolucion['fecha'] ?? '1970-01-01';
            });

            foreach ($devolucionesSinFacturaOrdenadas as $devolucionSinFactura) {
                $movimientosAgrupados->push($devolucionSinFactura);
            }

            $historial = $movimientosAgrupados;

            $saldoPeso = 0;
            $saldoUsd = 0;

            foreach ($historial as $mov) {
                $saldoPeso += ($mov['debe_peso'] - $mov['haber_peso']);
                $saldoUsd += ($mov['debe_usd'] - $mov['haber_usd']);

                DB::table('cuenta_corriente')->insert([
                    'payer_payee_id'   => $entidadId,
                    'comprobable_type' => $mov['type'],
                    'comprobable_id'   => $mov['id'],
                    'debe_peso'        => $mov['debe_peso'],
                    'haber_peso'       => $mov['haber_peso'],
                    'saldo_peso'       => $saldoPeso,
                    'debe_usd'         => $mov['debe_usd'],
                    'haber_usd'        => $mov['haber_usd'],
                    'saldo_usd'        => $saldoUsd,
                    'tasa_cambio'      => $mov['tasa'],
                    'nota'             => $mov['nota'],
                    'monto_original'   => $mov['monto_original'] ?? null,
                    'moneda_original'  => $mov['moneda_original'] ?? null,
                    'monto_convertido' => $mov['monto_convertido'] ?? null,
                    'moneda_convertida' => $mov['moneda_convertida'] ?? null,
                    'tasa_aplicada'    => $mov['tasa_aplicada'] ?? null,
                    'tiene_conversion' => $mov['tiene_conversion'] ?? false,
                    'detalle_conversion' => $mov['detalle_conversion'] ?? null,
                    'monto_aplicado'   => $mov['monto_aplicado'] ?? null,
                    'moneda_aplicada'  => $mov['moneda_aplicada'] ?? null,
                    'sobrante'         => $mov['sobrante'] ?? null,
                    'moneda_sobrante'  => $mov['moneda_sobrante'] ?? null,
                    'created_at'       => $mov['created_at'],
                    'updated_at'       => now(),
                ]);


            }

            $this->info("Entidad ID {$entidadId}: Procesados " . count($historial) . " movimientos. Saldo final: ARS " . $saldoPeso . " | USD " . $saldoUsd);
        }

        $this->info("Nivelación finalizada exitosamente.");
        return 0;
    }
}
