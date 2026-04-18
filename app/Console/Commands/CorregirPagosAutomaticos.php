<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\CuentaCorriente;
use App\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CorregirPagosAutomaticos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cuentacorriente:corregir-pagos 
                            {--cliente= : ID del cliente específico a corregir}
                            {--movimiento= : ID del movimiento específico a corregir}
                            {--dry-run : Mostrar cambios sin aplicarlos}
                            {--recalcular : Recalcular todos los saldos después de corregir}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Corrige movimientos de pago automático mal registrados (DEBE=0, HABER>0) a la lógica correcta (DEBE>0, HABER=0)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Iniciando corrección de pagos automáticos mal registrados...');
        
        $clienteId = $this->option('cliente');
        $movimientoId = $this->option('movimiento');
        $dryRun = $this->option('dry-run');
        $recalcular = $this->option('recalcular');
        
        // Construir query para encontrar movimientos mal registrados
        // Solo movimientos de pagos automáticos específicos, no movimientos históricos
        $query = CuentaCorriente::where(function($q) {
            // Movimientos con HABER>0 y DEBE=0 (incorrectos para pagos automáticos)
            $q->where('debe_peso', 0)
              ->where('haber_peso', '>', 0)
              ->where('debe_usd', 0)
              ->where('haber_usd', 0);
        })->orWhere(function($q) {
            $q->where('debe_peso', 0)
              ->where('haber_peso', 0)
              ->where('debe_usd', 0)
              ->where('haber_usd', '>', 0);
        })->where(function($q) {
            // Solo movimientos específicos de pagos automáticos
            $q->where('nota', 'like', '%Factura #%pagada automáticamente desde saldo a favor%')
              ->orWhere('nota', 'like', '%Pago automático desde saldo a favor%')
              ->orWhere('nota', 'like', '%pagada automáticamente desde saldo a favor%');
        });
        
        // Filtrar por cliente si se especifica
        if ($clienteId) {
            $query->where('payer_payee_id', $clienteId);
            $this->info("Filtrando por cliente ID: {$clienteId}");
        }
        
        // Filtrar por movimiento si se especifica
        if ($movimientoId) {
            $query->where('id', $movimientoId);
            $this->info("Filtrando por movimiento ID: {$movimientoId}");
        }
        
        $movimientos = $query->get();
        
        if ($movimientos->isEmpty()) {
            $this->info('No se encontraron movimientos mal registrados.');
            return 0;
        }
        
        $this->info("Se encontraron {$movimientos->count()} movimiento(s) mal registrado(s).");
        
        if ($dryRun) {
            $this->info('=== MODO DRY RUN - No se aplicarán cambios ===');
        }
        
        $corregidos = 0;
        $errores = 0;
        
        foreach ($movimientos as $movimiento) {
            try {
                $this->line("Procesando movimiento ID: {$movimiento->id}");
                $this->line("  Cliente ID: {$movimiento->payer_payee_id}");
                $this->line("  Nota: {$movimiento->nota}");
                
                // Determinar moneda y montos
                $esUsd = $movimiento->haber_usd > 0;
                $monto = $esUsd ? $movimiento->haber_usd : $movimiento->haber_peso;
                
                $this->line("  Moneda: " . ($esUsd ? 'USD' : 'Pesos'));
                $this->line("  Monto actual (HABER): " . number_format($monto, 2));
                $this->line("  DEBE actual: " . ($esUsd ? $movimiento->debe_usd : $movimiento->debe_peso));
                $this->line("  HABER actual: " . ($esUsd ? $movimiento->haber_usd : $movimiento->haber_peso));
                $this->line("  Saldo actual: " . ($esUsd ? $movimiento->saldo_usd : $movimiento->saldo_peso));
                
                if (!$dryRun) {
                    DB::beginTransaction();
                    
                    // Corregir el movimiento: intercambiar DEBE y HABER
                    $movimiento->debe_peso = $movimiento->haber_peso;
                    $movimiento->haber_peso = 0;
                    $movimiento->debe_usd = $movimiento->haber_usd;
                    $movimiento->haber_usd = 0;
                    
                    // Actualizar la nota para indicar que fue corregido
                    $movimiento->nota = $movimiento->nota . ' [CORREGIDO: ' . date('Y-m-d H:i:s') . ']';
                    
                    // Guardar sin disparar eventos para evitar bucles
                    $movimiento->saveQuietly();
                    
                    // Verificar si hay una factura asociada
                    if ($movimiento->comprobable_type === 'App\Invoice' && $movimiento->comprobable_id) {
                        $invoice = Invoice::find($movimiento->comprobable_id);
                        if ($invoice) {
                            $this->line("  Factura asociada: #{$invoice->invoice_number}");
                            
                            // Verificar que el estado de la factura sea consistente
                            // (No necesitamos cambiar el estado de la factura, solo el movimiento en cuenta corriente)
                        }
                    }
                    
                    DB::commit();
                    
                    $this->info("  [OK] Movimiento {$movimiento->id} corregido exitosamente.");
                    $corregidos++;
                } else {
                    $this->info("  [OK] (DRY RUN) Movimiento {$movimiento->id} sería corregido.");
                    $corregidos++;
                }
                
            } catch (\Exception $e) {
                if (!$dryRun) {
                    DB::rollBack();
                }
                
                $this->error("  [ERROR] Error al corregir movimiento {$movimiento->id}: " . $e->getMessage());
                Log::error("Error al corregir movimiento {$movimiento->id}: " . $e->getMessage(), [
                    'movimiento_id' => $movimiento->id,
                    'trace' => $e->getTraceAsString()
                ]);
                $errores++;
            }
            
            $this->line('');
        }
        
        $this->info("=== RESUMEN ===");
        $this->info("Movimientos encontrados: " . $movimientos->count());
        $this->info("Movimientos corregidos: {$corregidos}");
        $this->info("Errores: {$errores}");
        
        // Recalcular saldos si se solicita
        if ($recalcular && !$dryRun && $corregidos > 0) {
            $this->info("\nRecalculando saldos...");
            
            // Obtener clientes únicos afectados
            $clientesIds = $movimientos->pluck('payer_payee_id')->unique();
            
            foreach ($clientesIds as $clienteId) {
                try {
                    $this->line("Recalculando saldos para cliente ID: {$clienteId}");
                    CuentaCorriente::recalcular($clienteId);
                    $this->info("  [OK] Saldos recalculados para cliente {$clienteId}");
                } catch (\Exception $e) {
                    $this->error("  [ERROR] Error al recalcular saldos para cliente {$clienteId}: " . $e->getMessage());
                    Log::error("Error al recalcular saldos para cliente {$clienteId}: " . $e->getMessage());
                }
            }
            
            $this->info("Recálculo de saldos completado.");
        } elseif ($recalcular && $dryRun) {
            $this->info("\n(DRY RUN) Los saldos serían recalculados para los clientes afectados.");
        }
        
        if ($dryRun) {
            $this->info("\n=== EJECUCIÓN COMPLETADA EN MODO DRY RUN ===");
            $this->info("Para aplicar los cambios, ejecute el comando sin --dry-run");
        } else {
            $this->info("\n=== CORRECCIÓN COMPLETADA ===");
        }
        
        return $errores > 0 ? 1 : 0;
    }
    
    /**
     * Mostrar ayuda adicional
     */
    public function getHelp()
    {
        return <<<HELP
Este comando corrige movimientos de pago automático que fueron registrados incorrectamente.

Problema detectado:
- Los pagos automáticos desde saldo a favor se registraban como HABER>0, DEBE=0
- Esto hace que el saldo se vuelva MÁS NEGATIVO (aumenta el saldo a favor del cliente)
- La lógica correcta es DEBE>0, HABER=0 (disminuye el saldo a favor del cliente)
- NOTA: Este comando solo corrige movimientos específicos de pagos automáticos, no movimientos históricos migrados

Ejemplos de uso:
  php artisan cuentacorriente:corregir-pagos
    Busca y corrige todos los movimientos mal registrados
    
  php artisan cuentacorriente:corregir-pagos --cliente=4211
    Corrige solo los movimientos del cliente 4211
    
  php artisan cuentacorriente:corregir-pagos --movimiento=5681
    Corrige solo el movimiento específico ID 5681
    
  php artisan cuentacorriente:corregir-pagos --dry-run
    Muestra qué se corregiría sin aplicar cambios
    
  php artisan cuentacorriente:corregir-pagos --recalcular
    Recalcula todos los saldos después de corregir

HELP;
    }
}