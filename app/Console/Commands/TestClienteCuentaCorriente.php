<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestClienteCuentaCorriente extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:cuenta-corriente {cliente_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Probar la nivelación de cuenta corriente para un cliente específico';

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $clienteId = $this->argument('cliente_id');
        
        $this->info("=== INICIANDO PRUEBA PARA CLIENTE ID: {$clienteId} ===");
        
        // 1. Verificar si el cliente existe en facturas o transacciones
        $enFacturas = DB::table('invoices')->where('client_id', $clienteId)->exists();
        $enTransacciones = DB::table('transactions')->where('payer_payee_id', $clienteId)->exists();
        
        $this->info("Cliente encontrado en:");
        $this->info("  - Facturas: " . ($enFacturas ? 'SÍ' : 'NO'));
        $this->info("  - Transacciones: " . ($enTransacciones ? 'SÍ' : 'NO'));
        
        if (!$enFacturas && !$enTransacciones) {
            $this->error("El cliente {$clienteId} no existe en facturas ni transacciones.");
            return 1;
        }
        
        // 2. Limpiar registros existentes para este cliente
        DB::table('cuenta_corriente')->where('payer_payee_id', $clienteId)->delete();
        $this->info("Registros anteriores eliminados.");
        
        // 3. Traer Facturas
        $facturas = DB::table('invoices')->where('client_id', $clienteId)->get()->map(function ($i) {
            return [
                'fecha' => $i->invoice_date,
                'created_at' => $i->created_at,
                'type'  => 'App\Invoice',
                'id'    => $i->id,
                'debe_peso' => !$i->is_usd ? $i->grand_total : 0,
                'haber_peso' => 0,
                'debe_usd'  => $i->is_usd ? $i->grand_total : 0,
                'haber_usd' => 0,
                'tasa' => $i->tasa ?? 1,
                'nota' => "Factura #{$i->invoice_number}"
            ];
        });
        
        $this->info("Facturas encontradas: " . $facturas->count());
        foreach ($facturas as $factura) {
            $this->info("  - Factura ID: {$factura['id']} | Debe ARS: {$factura['debe_peso']} | Debe USD: {$factura['debe_usd']}");
        }
        
        // 4. Devoluciones procesadas (HABER) - Ahora usando SalesReturn
        $devoluciones = DB::table('sales_return')
            ->leftJoin('invoices', 'invoices.id', '=', 'sales_return.invoice_id')
            ->where('sales_return.customer_id', $clienteId)
            ->select('sales_return.*', 'invoices.is_usd as invoice_is_usd', 'invoices.tasa as tasa_inv', 'invoices.status as invoice_status', 'invoices.paid as invoice_paid')
            ->get()->map(function ($dev) {
                // Verificar si la factura relacionada está cancelada
                $facturaCancelada = isset($dev->invoice_status) && $dev->invoice_status == 'Canceled';
                $facturaTienePagos = isset($dev->invoice_paid) && $dev->invoice_paid > 0;
                
                // Si la factura está cancelada y no tiene pagos, no crear movimiento para la devolución
                if ($facturaCancelada && !$facturaTienePagos) {
                    return null;
                }
                
                // Usar moneda de la factura relacionada (sales_return no tiene campo is_usd)
                $isUsd = isset($dev->invoice_is_usd) ? $dev->invoice_is_usd : false;
                $tasa = $dev->tasa_inv ?? 1;
                
                // Determinar montos - usar grand_total de sales_return (campo que existe)
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
                
                return [
                    'fecha' => $dev->created_at,
                    'created_at' => $dev->created_at,
                    'type'  => 'App\SalesReturn',
                    'id'    => $dev->id,
                    'debe_peso'  => 0,
                    'haber_peso' => $montoPeso,
                    'debe_usd'   => 0,
                    'haber_usd'  => $montoUsd,
                    'tasa'       => $tasa,
                    'nota'       => "Devolución de venta" . ($dev->invoice_id ? " (Factura Relacionada)" : "")
                ];
            })->filter(); // Filtrar nulos
        
        $this->info("Devoluciones encontradas: " . $devoluciones->count());
        
        // 5. Traer Transacciones (Pagos/Gastos/Retiros) con lógica mejorada de moneda
        // Excluir transacciones type='cc' y dr_cr='cc' que son internas del sistema
        $transacciones = DB::table('transactions')
            ->where('payer_payee_id', $clienteId)
            ->where(function ($query) {
                $query->where('type', '!=', 'cc')
                      ->orWhere('dr_cr', '!=', 'cc');
            })
            ->get();
        
        $this->info("Transacciones encontradas (excluyendo type='cc' y dr_cr='cc'): " . $transacciones->count());
        
        $pagos = $transacciones->map(function ($t) use ($clienteId) {
            $es_debe = ($t->dr_cr == 'dr');

            // LÓGICA MEJORADA PARA DETERMINAR MONTOS SEGÚN MONEDA
            $m_peso = $t->amount_peso;
            $m_usd  = $t->amount_usd;

            // Determinar moneda de la factura si existe
            $moneda_factura = null;
            if ($t->invoice_id) {
                $invoice = DB::table('invoices')->where('id', $t->invoice_id)->first();
                if ($invoice) {
                    $moneda_factura = $invoice->is_usd ? 'USD' : 'ARS';
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
            
            // Detectar retiro de cuenta corriente (cc_expense)
            if ($t->type == 'cc_expense' && $t->dr_cr == 'cc') {
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
            $nota = $t->note ?? "Movimiento histórico (Migrado)";
            
            if ($es_devolucion_dinero) {
                // DEVOLUCIÓN DE DINERO: Invertir la lógica
                $debe_peso = $m_peso ?? 0;
                $haber_peso = 0;
                $debe_usd = $m_usd ?? 0;
                $haber_usd = 0;
                $nota = $t->note ?? "Devolución de dinero";
            } elseif ($es_ingreso_cuenta_corriente) {
                // INGRESO A CUENTA CORRIENTE
                $debe_peso = 0;
                $haber_peso = $m_peso ?? 0;
                $debe_usd = 0;
                $haber_usd = $m_usd ?? 0;
                $nota = $t->note ?? "Ingreso a cuenta corriente";
            } elseif ($es_retiro_cuenta_corriente) {
                // RETIRO DE CUENTA CORRIENTE
                $debe_peso = $m_peso ?? 0;
                $haber_peso = 0;
                $debe_usd = $m_usd ?? 0;
                $haber_usd = 0;
                $nota = $t->note ?? "Retiro de cuenta corriente";
            } elseif ($es_devolucion_producto) {
                // DEVOLUCIÓN DE PRODUCTO
                $debe_peso = 0;
                $haber_peso = $m_peso ?? 0;
                $debe_usd = 0;
                $haber_usd = $m_usd ?? 0;
                $nota = $t->note ?? "Devolución de producto";
            } elseif ($es_gasto_normal) {
                // GASTO NORMAL
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
            }

            $this->info("  - Transacción ID: {$t->id} | Tipo: {$t->type} | DrCr: {$t->dr_cr}");
            $this->info("    Monto: {$t->amount} | USD: {$t->usd} | Tasa: {$t->tasa}");
            $this->info("    amount_peso: " . ($m_peso ?? 'null') . " | amount_usd: " . ($m_usd ?? 'null'));
            $this->info("    Debe ARS: {$debe_peso} | Haber ARS: {$haber_peso}");
            $this->info("    Debe USD: {$debe_usd} | Haber USD: {$haber_usd}");
            $this->info("    Nota: {$nota}");

            return [
                'fecha' => $t->trans_date,
                'created_at' => $t->created_at,
                'type'  => 'App\Transaction',
                'id'    => $t->id,
                'debe_peso'  => $debe_peso,
                'haber_peso' => $haber_peso,
                'debe_usd'   => $debe_usd,
                'haber_usd'  => $haber_usd,
                'tasa'       => $t->tasa ?? 1,
                'nota'       => $nota,
            ];
        });

        // 6. Unir y ordenar por FECHA y luego por ID para mantener orden lógico
        $historial = $facturas->concat($pagos)->concat($devoluciones)->sortBy(['fecha', 'created_at']);

        $this->info("=== PROCESANDO MOVIMIENTOS ===");
        $this->info("Total movimientos a procesar: " . count($historial));

        $saldoPeso = 0;
        $saldoUsd = 0;
        $contador = 0;

        foreach ($historial as $mov) {
            $contador++;
            $saldoPeso += ($mov['debe_peso'] - $mov['haber_peso']);
            $saldoUsd += ($mov['debe_usd'] - $mov['haber_usd']);

            $this->info("Movimiento {$contador}:");
            $this->info("  Tipo: {$mov['type']} ID: {$mov['id']}");
            $this->info("  Debe ARS: {$mov['debe_peso']} | Haber ARS: {$mov['haber_peso']} | Saldo ARS: {$saldoPeso}");
            $this->info("  Debe USD: {$mov['debe_usd']} | Haber USD: {$mov['haber_usd']} | Saldo USD: {$saldoUsd}");
            $this->info("  Nota: {$mov['nota']}");

            DB::table('cuenta_corriente')->insert([
                'payer_payee_id'   => $clienteId,
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
                'created_at'       => $mov['created_at'],
                'updated_at'       => now(),
            ]);
        }
        
        $this->info("=== RESULTADO FINAL ===");
        $this->info("Procesados {$contador} movimientos.");
        $this->info("Saldo final: ARS {$saldoPeso} | USD {$saldoUsd}");
        
        return 0;
    }
}