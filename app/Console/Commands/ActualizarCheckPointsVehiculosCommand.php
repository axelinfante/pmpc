<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Cars;
use App\Checkpoint;
use App\CheckpointVehiculo;
use DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ActualizarCheckPointsVehiculosCommand extends Command
{
    protected $signature = 'actualizar:checkpoints';
    protected $description = 'Actualizar checkpoints de vehículos';

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
     * @return void
     */
    public function handle()
    {
        $old_mem_limit = ini_get("memory_limit");
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);

        try {
            $checkpoints = Checkpoint::Where('id',12)->get();

            Cars::chunk(100, function ($cars) use ($checkpoints) {
                foreach ($cars as $car) {
                    foreach ($checkpoints as $checkpoint) {
                        try {
                            $checkPointVehiculo = CheckpointVehiculo::updateOrCreate(
                                ['vehiculo_id' => $car->id, 'checkpoint_id' => $checkpoint->id],
                                [
                                    'checkpoint_id' => $checkpoint->id,
                                    'vehiculo_id' => $car->id,
                                    'start_date' => null,
                                    'end_date' => null,
                                    'observaciones' => '',
                                    'user_id' => 2,
                                    'created_at' => Carbon::now(),
                                    'updated_at' => Carbon::now(),
                                ]
                            );

                            // switch ($checkpoint->id) {
                            //     case '1':
                            //         if ($car->asegurado != null && $car->contacto != null && $car->fecha_confirmacion_contacto != null) {
                            //             $checkPointVehiculo->status = 'completado';
                            //         } elseif ($car->asegurado == null && $car->contacto == null && $car->fecha_confirmacion_contacto == null) {
                            //             $checkPointVehiculo->status = 'pendiente';
                            //         } else {
                            //             $checkPointVehiculo->status = 'iniciado';
                            //         }
                            //         break;
                            //     case '2':
                            //         if ($car->fecha_limite_retiro != null && $car->coordinar_retiro != null) {
                            //             $checkPointVehiculo->status = 'completado';
                            //         } elseif ($car->fecha_limite_retiro == null && $car->coordinar_retiro == null) {
                            //             $checkPointVehiculo->status = 'pendiente';
                            //         } else {
                            //             $checkPointVehiculo->status = 'iniciado';
                            //         }
                            //         break;
                            //     case '3':
                            //         if ($car->fecha_entrega_asegurado_cia != null && $car->entregado_a != null) {
                            //             $checkPointVehiculo->status = 'completado';
                            //         } elseif ($car->fecha_entrega_asegurado_cia == null && $car->entregado_a == null) {
                            //             $checkPointVehiculo->status = 'pendiente';
                            //         } else {
                            //             $checkPointVehiculo->status = 'iniciado';
                            //         }
                            //         break;
                            //     case '4':
                            //         if ($car->gestor != null) {
                            //             $checkPointVehiculo->status = 'completado';
                            //         } else {
                            //             $checkPointVehiculo->status = 'pendiente';
                            //         }
                            //         break;
                            //     case '5':
                            //         if ($car->fecha_envio_baja != null) {
                            //             $checkPointVehiculo->status = 'completado';
                            //         } else {
                            //             $checkPointVehiculo->status = 'pendiente';
                            //         }
                            //         break;
                            //     case '6':
                            //         if ($car->fecha_recepcion != null) {
                            //             $checkPointVehiculo->status = 'completado';
                            //         } else {
                            //             $checkPointVehiculo->status = 'pendiente';
                            //         }
                            //         break;
                            //     case '7':
                            //         if ($car->fecha_envio_doc != null) {
                            //             $checkPointVehiculo->status = 'completado';
                            //         } else {
                            //             $checkPointVehiculo->status = 'pendiente';
                            //         }
                            //         break;
                            //     case '8':
                            //         if ($car->fecha_envio_drnpa != null) {
                            //             $checkPointVehiculo->status = 'completado';
                            //         } else {
                            //             $checkPointVehiculo->status = 'pendiente';
                            //         }
                            //         break;
                            //     case '9':
                            //         if ($car->fecha_finalizacion != null) {
                            //             $checkPointVehiculo->status = 'completado';
                            //         } else {
                            //             $checkPointVehiculo->status = 'pendiente';
                            //         }
                            //         break;
                            //     case '10':

                            //         if ($car->fecha_inicio_preinforme != null && $car->fecha_finalizacion_preinforme != null) {
                            //             $checkPointVehiculo->status = 'completado';
                            //         } elseif ($car->fecha_inicio_preinforme == null && $car->fecha_finalizacion_preinforme == null) {
                            //             $checkPointVehiculo->status = 'pendiente';
                            //         } else {
                            //             $checkPointVehiculo->status = 'iniciado';
                            //         }
                            //         break;
                            // }

                            $checkPointVehiculo->status_date = Carbon::now();
                            $checkPointVehiculo->save();
                        } catch (\Exception $e) {
                            Log::error('Error updating or creating CheckpointVehiculo for car ID ' . $car->id . ' and checkpoint ID ' . $checkpoint->id . ': ' . $e->getMessage());
                        }
                    }

                    // try {
                    //     // $max_tramite = DB::table('vehiculos_checkpoints')
                    //     //     ->where('vehiculo_id', $car->id)
                    //     //     ->where('status', 'completado')
                    //     //     ->max('checkpoint_id');
                    //     $max_tramite = DB::table('vehiculos_checkpoints')
                    //     ->join('checkpoints','vehiculos_checkpoints.checkpoint_id', '=', 'checkpoints.id')
                    //     ->where('vehiculo_id', $car->id)
                    //     ->where('status', 'completado')
                    //     ->max('orden');

                        

                    //     if ($max_tramite >= 1 && $max_tramite <= 3) {
                    //         $status = 'En Proceso';
                    //     } elseif ($max_tramite >= 4 && $max_tramite <= 9) {
                    //         $status = 'En Gestoria';
                    //     } elseif ($max_tramite >= 10) {
                    //         $status = 'Finalizado';
                    //     } else {
                    //         $status = 'Pendiente';
                    //     }
                    //     $car->estado_tramite = $status;
                    //     $car->save();
                    // } catch (\Exception $e) {
                    //     Log::error('Error updating car status for car ID ' . $car->id . ': ' . $e->getMessage());
                    // }
                }
            });
        } catch (\Exception $e) {
            Log::error('Error in handle method of ActualizarCheckPointsVehiculos job: ' . $e->getMessage());
        } finally {
            // ini_set('memory_limit', $old_mem_limit);
            // ini_set('max_execution_time', 120);
        }
    }
}
