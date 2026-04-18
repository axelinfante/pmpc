<?php

namespace App\Http\Controllers;

use App\Aseguradora;
use App\Cars;
use App\Checkpoint;
use App\Company;
use App\Estado;
use App\Marca;
use App\MarcaModelo;
use App\Modelo;
use App\User;
use App\Role;
use App\Provincia;
use App\Lugar_entregas;
use App\FileManager;
use App\Imagen;
use Illuminate\Http\Request;
use Validator;
use DataTables;
use Auth;
use DB;
use Notification;
use App\CheckpointVehiculo;
use Carbon\Carbon;
use App\Notifications\RetiroVehiculoUpdated;
use App\Utilities\Imagenes;
use Maatwebsite\Excel\Facades\Excel;

class TramitadorController extends Controller
{
    use Imagenes;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        date_default_timezone_set(get_company_option('timezone', get_option('timezone', 'Asia/Dhaka')));

        $this->middleware(function ($request, $next) {
            if (has_membership_system() == 'enabled') {
                if (!has_feature('project_management_module')) {
                    if (!$request->ajax()) {
                        return redirect('membership/extend')->with('message', _lang('Sorry, This feature is not available in your current subscription. You can upgrade your package !'));
                    } else {
                        return response()->json(['result' => 'error', 'message' => _lang('Sorry, This feature is not available in your current subscription !')]);
                    }
                }
            }

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $datos = $this->datos();
        $comp = Company::all();
        $datos['cias'] = $comp;
        // if (strtolower(auth()->user()->role->name) == 'receptor') {
        //     return view('backend.accounting.vehiculo.list_receptor', $datos);
        // }

        // if (strtolower(auth()->user()->role->name) == 'gerente de operarios') {
        //     return view('backend.accounting.vehiculo.list_gerente_operario', $datos);
        // }

        // if (strtolower(auth()->user()->role->name) == 'tramitador') {
        //     return view('backend.accounting.vehiculo.list_tramitador', $datos);
        // }

        //dd($datos['responsable_entregas'][1]);
        return view('backend.accounting.tramitador.list', $datos);
    }

    public function create()
    {
        $datos = $this->datos();
        $comp = Company::all();
        $datos['cias'] = $comp;

        return view('backend.accounting.tramitador.create', $datos);
    }


    public function datos(): array
    {
        //traer datos necesarios para crear un nuevo registro de auto.
        $marca_modelos = MarcaModelo::all();

        //marcas
        $marcas = Marca::all();
        //modelos
        $modelos = Modelo::all();

        //tramitadores (usuarios con ese rol de la empresa)

        // $tramitadores = User::where('company_id', auth()->user()->company_id)->wherehas('role', function ($string) {
        //   //  $string->where('name', 'Tramitador');
        //     $string->where('name', 'Tramitador con gastos');
        //     $string->where('company_id', auth()->user()->company_id);
        // })->where('user_type', 'staff')
        //     ->get();

        //  dd( $tramitadores);


        $tramitadores = User::where('company_id', auth()->user()->company_id)
            ->whereHas('role', function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'Tramitador')
                        ->orWhere('name', 'Tramitador con gastos');
                })
                    ->where('company_id', auth()->user()->company_id);
            })
            ->where('user_type', 'staff')
            ->get();




        //asegurdadoras

        $aseguradoras = Aseguradora::all();

        //provincias
        $provincias = Provincia::all();

        //responsable entregas y de retiros
        $responsable_entregas = [
            1 => 'Asegurado',
            2 => 'Gestor Compañia',
            3 => 'Productor',
            4 => 'Compañia'
        ];
        //tipo de baja

        $tipo_baja = [
            1 => '04 D',
            2 => '04 C',
            3 => 'Moto c/alta motor',
            4 => 'Moto baja definitiva',
            5 => 'BD',
            6 => 'Alta de Motor',
        ];
        $responsable_retiros = User::where('company_id', auth()->user()->company_id)->wherehas('role', function ($string) {
            $string->where('name', 'Transportista')->where('company_id', auth()->user()->company_id);
        })->where('user_type', 'staff')
            ->get();

        // dd($responsable_retiros);

        //lugar de entregas y estado
        $lugar_entregas = Lugar_entregas::all();
        $estados = Estado::all();
        return compact([
            'estados',
            'marca_modelos',
            'tramitadores',
            'aseguradoras',
            'provincias',
            'responsable_entregas',
            'responsable_retiros',
            'lugar_entregas',
            'tipo_baja',
            'marcas',
            'modelos'
        ]);
    }
    public function update(Request $request)
    {
        $company_id = company_id();
        $vehiculo_id = $request->input('vehiculo_id');
        $checkpoint_id = $request->input('checkpoint_id');

        $car = Cars::where('id', $vehiculo_id)
            ->first();
        $CheckpointVehiculo = CheckpointVehiculo::where('vehiculo_id', $vehiculo_id)->where('checkpoint_id', $checkpoint_id)->first();

        // if (($checkpoint_id < 8) && ($CheckpointVehiculo->status != $request->input('status'))) {
        //     $checkpoint_id_siguiente = $checkpoint_id + 1;
        //     $statusSiguiente = CheckpointVehiculo::where('vehiculo_id', $vehiculo_id)->where('checkpoint_id', $checkpoint_id_siguiente)->first();
        //     if ($statusSiguiente) {
        //         if ( ($statusSiguiente->status == 'completado' || $statusSiguiente->status == 'iniciado') && $checkpoint_id != 2){
        //             // rechazo
        //             return response()->json(['result' => 'error', 'action' => 'update', 'message' => 'Este paso no se puede realizar, hay procesos posteriores iniciados o complatados']);
        //         }
        //     } else {
        //         return response()->json(['result' => 'error', 'action' => 'update', 'message' => 'Este paso no se puede realizar, hay procesos posteriores iniciados o complatados']);
        //     }
        // };

        switch ($checkpoint_id) {
            case '1':
                $car->asegurado = $request->input('asegurado');
                $car->contacto = $request->input('contacto');
                $car->fecha_confirmacion_contacto =  $request->input('fecha_confirmacion');
                if (!$car->fecha_inicio) {
                    $car->fecha_inicio = \Carbon\Carbon::now();
                }
                break;
            case '2':
                // $car->fecha_limite_retiro = $request->input('fecha_limite_retiro');
                $car->coordinar_retiro = $request->input('coordinar_retiro');
                $currentFechaLimiteRetiro = Carbon::parse($car->fecha_limite_retiro);
                $newFechaLimiteRetiro = Carbon::parse($request->input('fecha_limite_retiro'));
                if ($currentFechaLimiteRetiro->toDateString() !== $newFechaLimiteRetiro->toDateString()) {
              //  if (!$currentFechaLimiteRetiro->eq($newFechaLimiteRetiro)) {
                    $car->fecha_limite_retiro = $newFechaLimiteRetiro;
                    $avisarRetiros = $request->input('coordinar_retiro');
                    if ($avisarRetiros) {
                        $idRol = Role::where('name', 'Retiros')->first()->id;
                        Notification::send(User::where('role_id', $idRol)->get(), new RetiroVehiculoUpdated($car));
                    }
                }


                break;
            case '3':
                $car->fecha_entrega_asegurado_cia = $request->input('fecha_entrega');
                $car->entregado_a =  $request->input('entregado_a');
                break;
            case '4':
                $car->gestor = $request->input('gestor');
                break;
            case '5':
                $car->fecha_envio_baja = $request->input('fecha_envio_baja');
                break;
            case '6':
                $car->fecha_recepcion = $request->input('fecha_documento');
                break;
            case '7':
                $car->fecha_envio_doc = $request->input('fecha_envio_doc');
                break;
            case '8':
                $car->fecha_envio_drnpa = $request->input('fecha_envio_drnpa');
                break;
            case '9':
                if ($request->input('status') == 'iniciado')
                    $car->fecha_finalizacion = Null;
                else $car->fecha_finalizacion = $request->input('fecha_finalizacion');
                break;
            case '10':
                $car->fecha_inicio_preinforme = $request->input('fecha_inicio_preinforme');
                $car->fecha_finalizacion_preinforme = $request->input('fecha_finalizacion_preinforme');
                break;
            case '11':
                   // $car->observaciones_tramitadores = $request->input('observaciones');
                   $CheckpointVehiculo->observaciones =  $request->input('observaciones');
                    break;
            default:
                echo "Error Checkpoint no especificado";
                break;
        }

        $car->save();

        $CheckpointVehiculo->status =  $request->input('status');
        $CheckpointVehiculo->observaciones =  $request->input('observaciones');
        $CheckpointVehiculo->user_id =  auth()->user()->id;
        $CheckpointVehiculo->status_date =  Carbon::now();
        $CheckpointVehiculo->save();

        $this->updateCheckPoint($car);

        // $max_tramite = DB::table('vehiculos_checkpoints')
        //     ->where('vehiculo_id', $car->id)
        //     ->where('status', 'completado')
        //     ->max('checkpoint_id');

        // if ($max_tramite >= 1 && $max_tramite <= 2) $status = 'En Proceso';
        // else if ($max_tramite >= 3 && $max_tramite <= 8) $status = 'En Gestoria';
        // else if ($max_tramite >= 9) $status = 'Finalizado';
        // else $status = 'Pendiente';
        // $car->estado_tramite = $status;
        // $car->save();

        return response()->json(['result' => 'success', 'action' => 'update', 'message' => 'Actualizacion Satisfactoria']);
    }
    public function checkpointVehiculosGetTramite(Request $request, $vehiculo_id, $checkpoint_id)
    {

        // Se comenta para permitir edicion libre
        // if ($checkpoint_id > 1 && $checkpoint_id != 9) {
        //     $checkpoint_id_anterior = $checkpoint_id - 1;
        //     $statusAnterior = CheckpointVehiculo::where('vehiculo_id', $vehiculo_id)->where('checkpoint_id', $checkpoint_id_anterior)->first();
        //     if ($statusAnterior) {
        //         if ($statusAnterior->status == 'pendiente' || $statusAnterior->status == 'iniciado') {
        //             // rechazo
        //             return ('<p> Este paso no se puede realizar, debe completar los pasos previos</p>');
        //         }
        //     } else {
        //         return ('<p> Este paso no se puede realizar, debe completar los pasos previos</p>');
        //     }
        // }
        $car = Cars::where('id', (int) $vehiculo_id)->first();
        $checkpoint_vehiculo = CheckpointVehiculo::where('vehiculo_id', $vehiculo_id)->where('checkpoint_id', $checkpoint_id)->first();

        return view('backend.accounting.tramitador.modal.checkpoint_' . $checkpoint_id, compact('car', 'checkpoint_vehiculo', 'vehiculo_id', 'checkpoint_id'));
    }
    public function get_table_data(Request $request)
    {
        $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
        $cars = Cars::with([
            'aseguradora',
            'marca_modelo.marca',
            'marca_modelo.modelo',
            'company',
            'provincias',
            'tramitador',
            'lugar_entrega',
            'responsable_retiro',
            'estado'
        ])
            ->whereIn('company_id', $company_id)
            ->orderBy('created_at', 'desc');


        return Datatables::eloquent($cars)
            ->filterColumn('company', function ($query, $keyword) {
                $query->orwhereHas('company', function ($str) use ($keyword) {
                    $str->where('business_name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('tramitador', function ($query, $keyword) {
                $query->orwhereHas('tramitador', function ($str) use ($keyword) {
                    $str->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('aseguradora', function ($query, $keyword) {
                $query->orwhereHas('aseguradora', function ($str) use ($keyword) {
                    $str->where('nombre', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('marca_modelo', function ($query, $keyword) {
                $query->orwhereHas('marca_modelo', function ($str) use ($keyword) {
                    $str->whereHas('marca', function ($str) use ($keyword) {
                        $str->where('marca', 'like', "%{$keyword}%");
                    });
                    $str->orwhereHas('modelo', function ($str) use ($keyword) {
                        $str->where('modelo', 'like', "%{$keyword}%");
                    });
                });
            })
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && !empty($request->search['value'])) {
                    $search = $request->search['value'];
                    $query->where(function ($query) use ($search) {
                        $query->where('dominio', 'like', "%{$search}%")
                            ->orWhere('nro_interno', 'like', "%{$search}%")
                            ->orWhere('siniestro', 'like', "%{$search}%")
                            ->orwhere('estado_tramite', 'like', "%{$search}%")

                            ->orWhereRaw("DATE_FORMAT(fecha_inicio, '%d-%m-%Y') LIKE ?", ['%' . $search . '%'])
                            ->orWhereRaw("DATE_FORMAT(fecha_finalizacion, '%d-%m-%Y') LIKE ?", ['%' . $search . '%'])
                            ->orWhereRaw("DATE_FORMAT(fecha_asignacion, '%d-%m-%Y') LIKE ?", ['%' . $search . '%'])

                            ->orWhereHas('marca_modelo.marca', function ($q) use ($search) {
                                $q->where('marca', 'like', "%{$search}%");
                            })
                            ->orWhereHas('marca_modelo.modelo', function ($q) use ($search) {
                                $q->where('modelo', 'like', "%{$search}%");
                            })
                            ->orWhereHas('company', function ($q) use ($search) {
                                $q->where('business_name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('tramitador', function ($q) use ($search) {
                                $q->where('name', 'like', "%{$search}%");
                            })
                            ->orWhereHas('aseguradora', function ($q) use ($search) {
                                $q->where('nombre', 'like', "%{$search}%");
                            })
                            ->orWhereHas('provincias', function ($q) use ($search) {
                                $q->where('provincia', 'like', "%{$search}%");
                            })
                            ->orWhereHas('estado', function ($q) use ($search) {
                                $q->where('estado', 'like', "%{$search}%");
                            });
                    });
                }

                if ($request->has('status')) {
                    $query->whereHas('estado', function ($s) use ($request) {
                        $s->whereIn('id', json_decode($request->post('status')));
                    });
                }
                if ($request->has('estado_tramite')) {
                    $query->whereIn('estado_tramite', json_decode($request->post('estado_tramite')));
                }
            })

            ->editColumn('id', function ($car) {
                return '<a class="" href="' . action('TramitadorController@seguimiento', $car['id']) . '">' . ($car->company_id == 1 ? 'PM-' : 'PC-') . $car->id .  '</a>';
            })
            ->editColumn('anulado', function ($car) {
                return $car->deleted_at ? 'Si' : 'No';
            })
            ->editColumn('company', function ($car) {
                return $car->company->business_name;
            })
            ->editColumn('fecha_asignacion', function ($car) {
                return date(get_company_option('date_format', 'Y-m-d'), strtotime($car->fecha_asignacion));
            })
            ->editColumn('tramitador', function ($car) {
                return $car->tramitador->name ?? null;
            })
            ->editColumn('aseguradora', function ($car) {
                return $car->aseguradora->nombre ?? null;
            })
            ->editColumn('marca_modelo', function ($car) {
                return ($car->marca_modelo->marca->marca ?? '') . ' ' . ($car->marca_modelo->modelo->modelo ?? '');
            })
            ->addColumn('estado_tramite', function ($car) {
                // $max_tramite = DB::table('vehiculos_checkpoints')
                //     ->where('vehiculo_id', $car->id)
                //     ->where('status', 'completado')
                //     ->max('checkpoint_id');
                $max_tramite = DB::table('vehiculos_checkpoints')
                    ->join('checkpoints', 'vehiculos_checkpoints.checkpoint_id', '=', 'checkpoints.id')
                    ->where('vehiculo_id', $car->id)
                    ->where('status', 'completado')
                    ->where('orden', '<',999)
                    ->max('orden');

                if ($max_tramite >= 1 && $max_tramite <= 3) return 'En Proceso';
                if ($max_tramite >= 4 && $max_tramite <= 9) return 'En Gestoria';
                if ($max_tramite >= 10 ) return 'Finalizado';
                return 'Pendiente';
            })
            ->addColumn('action', function ($car) {
                $filemanager = FileManager::where('name', ($car->company_id == 1 ? 'PM-' : 'PC-') . $car->id)->first();
                $enlace = !empty($filemanager) ? '<a class="btn btn-xs btn-secondary" target="_blank" href="' . url('file_manager/directory/' . encrypt($filemanager->id)) . '"><i class="far fa-folder"></i> Ver Archivos</a>' : '';

                $buttons = '<div class="btn-group">';
                $buttons .= '<button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Acciones</button>';
                $buttons .= '<div class="dropdown-menu action">';
                $buttons .= '<a class="dropdown-item ajax-modal" href="' . action('VehiculoController@show', $car['id']) . '" data-title="' . _lang('Multimedia') . '"><i class="ti-eye"></i> ' . _lang('Multimedia') . '</a>';
                $buttons .= '<a class="dropdown-item ajax-modal" href="' . action('VehiculoController@edit', $car['id']) . '" data-title="' . _lang('Update Vehicle') . '"><i class="ti-pencil"></i> ' . _lang('Update Vehicle') . '</a>';
                $buttons .= '<a class="dropdown-item" target="_blank" href="' . action('VehiculoController@movimientos', $car['id']) . '" data-title="' . _lang('Ver movimientos') . '"><i class="ti-receipt"></i> ' . _lang('Ver movimientos') . '</a>';
                $buttons .= '<a class="dropdown-item ajax-modal" href="' . action('VehiculoController@seguimiento', $car['id']) . '" data-title="' . _lang('Ver Estado') . '"><i class="fas fa-search"></i> ' . _lang('Ver Estado') . '</a>';
                if (!empty($enlace)) {
                    $buttons .= '<a class="dropdown-item" href="' . url('file_manager/directory/' . encrypt($filemanager->id)) . '" target="_blank"><i class="far fa-folder"></i> ' . _lang('File Manager') . '</a>';
                }
                $buttons .= '<form action="' . action('VehiculoController@destroy', $car['id']) . '" method="post" style="display:inline-block;">'
                    . csrf_field()
                    . '<input name="_method" type="hidden" value="DELETE">'
                    . '<button class="dropdown-item btn-remove" type="submit"><i class="ti-eraser"></i> ' . _lang('Delete') . '</button>'
                    . '</form>';
                $buttons .= '<a class="dropdown-item" href="' . action('TramitadorController@seguimiento', $car['id']) . '" data-title="Ver Check List"><i class="fas fa-check-double"></i> Ver Check List</a>';
                $buttons .= '</div>';
                $buttons .= '</div>';



                // $buttons .= '<form action="' . action('VehiculoController@destroy', $car['id']) . '" method="post" style="display:inline-block;">';
                // $buttons .= csrf_field();
                // $buttons .= '<input name="_method" type="hidden" value="DELETE">';
                // $buttons .= '<button class="dropdown-item btn btn-danger btn-xs btn-remove" type="submit"><i class="ti-eraser"></i> Eliminar</button>';
                // $buttons .= '</form>';
                $buttons .= '</div>';
                $buttons .= '</div>';

                return $buttons;
            })
            ->editColumn('fecha_inicio', function ($car) {
                if ($car->fecha_inicio)
                    return date(get_company_option('date_format', 'Y-m-d'), strtotime($car->fecha_inicio));
                else return '';
            })
            ->editColumn('fecha_finalizacion', function ($car) {
                if ($car->fecha_finalizacion)
                    return date(get_company_option('date_format', 'Y-m-d'), strtotime($car->fecha_finalizacion));
                else return '';
            })

            ->setRowId(function ($car) {
                return "row_" . $car->id;
            })
            ->rawColumns(['action', 'pieza_no_disponible', 'estado', 'members.name', 'status', 'id'])
            ->make(true);
    }


    public function changeCompany($id)
    {
        session(['cia' => $id]);
    }


    public function seguimiento($id)
    {
        $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
        $user_type = Auth::user()->user_type;
        $datos = $this->datos();
        $datos['cias'] = Company::all();

        $cars = Cars::select('cars.*')->withTrashed()
            ->with('marca_modelo')
            ->with('company')
            ->with('provincias')
            ->with('tramitador')
            ->with('lugar_entrega')
            ->with('responsable_retiro')
            ->with('estado')
            ->where('id', $id)->first();
        $in = '';
        if ($cars->company_id == 1) {
            $in = 'PM-';
        } else if ($cars->company_id == 2) {
            $in = 'PC-';
        }

        $filemanager = FileManager::where('name', $in . $cars->id)->first();

        $enlace1 = '';
        if (empty($filemanager)) {

            $filemanager = new FileManager();
            $filemanager->name = $in . $cars->id;
            $filemanager->is_dir = 'yes';
            $filemanager->parent_id = null;
            $filemanager->company_id = $cars->company_id;
            $filemanager->created_by = Auth::user()->id;

            $filemanager->save();
        }
        $enlace1 = '<a class="btn btn-xs btn-primary" target="_blank" href="' . url(
            'file_manager/directory/' . encrypt($filemanager->id)
        ) . '"><i class="far fa-folder"></i> Gestor de Archivos </a>';

        $enlace2 = '';
        $enlace2 = '<a class="btn btn-xs btn-primary" target="_blank"
        href="' . action('VehiculoController@movimientos', $cars->id) . '" data-title="' . _lang('Ver movimientos') . '" class="btn btn-warning btn-xs"><i 
        class="ti-receipt"></i> Ver movimientos</a>';

        $enlace3 = '';
        $enlace3 = '<a href="' . action("TramitadorController@show04D", $cars->id) . '" class="btn btn-primary
        btn-xs ajax-modal" data-title=" Imagenes 04D"><i class="ti-eye"></i> Fotos</a>';

        $datos['filemanager'] = $enlace1;
        $datos['movimientos'] = $enlace2;
        $datos['multimedia'] = $enlace3;

        $datos['car'] = $cars;

        $max_tramite_completado = 0;
        $max_tramite_completado = DB::table('vehiculos_checkpoints')
            ->join('checkpoints', 'vehiculos_checkpoints.checkpoint_id', '=', 'checkpoints.id')
            ->where('vehiculo_id', $cars->id)
            ->where('status', 'completado')
            ->where('orden', '<',999)
            ->max('orden');

        if ($max_tramite_completado >= 1 && $max_tramite_completado <= 3) {
            $statusC = 'En Proceso';
        } elseif ($max_tramite_completado >= 4 && $max_tramite_completado <= 9) {
            $statusC = 'En Gestoria';
        } elseif ($max_tramite_completado >= 10) {
            $statusC = 'Finalizado';
        } else {
            $statusC = 'Pendiente';
        }

        $max_tramite_iniciado=0;
        $max_tramite_iniciado = DB::table('vehiculos_checkpoints')
            ->join('checkpoints', 'vehiculos_checkpoints.checkpoint_id', '=', 'checkpoints.id')
            ->where('vehiculo_id', $cars->id)
            ->where('status', 'completado')
            ->where('orden', '<',999)
            ->max('orden');
            if ($max_tramite_iniciado >= 1 && $max_tramite_iniciado <= 3) {
                $statusI = 'En Proceso';
            } elseif ($max_tramite_iniciado >= 4 && $max_tramite_iniciado <= 9) {
                $statusI = 'En Gestoria';
            } elseif ($max_tramite_iniciado >= 10) {
                $statusI = 'Finalizado';
            } else {
                $statusI = 'Pendiente';
            }
            $estado_Final='';
        if ($max_tramite_completado >= $max_tramite_iniciado){
            $name_estado = Checkpoint::where('orden',$max_tramite_completado)->pluck('nombre')->first();
            $estado_Final = $statusC . ': '.$name_estado;
        } else {
            $name_estado = Checkpoint::where('orden',$max_tramite_iniciado)->pluck('nombre')->first();
            $estado_Final = $statusI . ': '.$name_estado;
        }

        $datos['estadofinal'] = $estado_Final;



        return view('backend.accounting.tramitador.seguimiento.list', $datos);
    }

    public function storeCheckPoint(Request $request)
    {
        $vehiculo_id = $request->vehiculo_id;
        $checkpoint_id = $request->checkpoint_id;
        $valor = $request->valor;



        if ($valor == 0)
            $vehiculos_checkpoints = CheckpointVehiculo::where('vehiculo_id', $vehiculo_id)->where('checkpoint_id', $checkpoint_id)->delete();
        else {
            $vehiculos_checkpoints = new CheckpointVehiculo;
            $vehiculos_checkpoints->vehiculo_id = $vehiculo_id;
            $vehiculos_checkpoints->checkpoint_id = $checkpoint_id;
            $vehiculos_checkpoints->status = 'pendiente';
            $vehiculos_checkpoints->user_id = Auth::user()->id;
            $vehiculos_checkpoints->save();
        }

        return response()->json([
            'result' => 'success',
            'action' => 'update',
            'message' => _lang('Save sucessfully'),
            'data' => $vehiculos_checkpoints
        ]);
    }

    public function checkpointVehiculosGetTableData(Request $request)
    {
        $vehiculo_id = $request->vehiculo_id;

        $vehiculos_checkpoints = CheckpointVehiculo::join('checkpoints', 'checkpoints.id', '=', 'checkpoint_id')
            ->where('vehiculo_id', $vehiculo_id)
            ->select('vehiculos_checkpoints.*') // Selecciona solo los campos de CheckpointVehiculo
            ->orderBy('checkpoints.orden', 'asc');

        return Datatables::eloquent($vehiculos_checkpoints)

            ->addColumn('numero', function ($vehiculos_checkpoints) {

                return $vehiculos_checkpoints->checkpoint->orden;
            })

            ->addColumn('nombre', function ($vehiculos_checkpoints) {

                return '<a href=' . route("checkpoints_vehiculos.get_tramite", [$vehiculos_checkpoints->vehiculo_id, $vehiculos_checkpoints->checkpoint_id]) . ' data-status="' . $vehiculos_checkpoints->status . '" data-prev="' . ($vehiculos_checkpoints->checkpoint->orden - 1) . '" data-orden="' . $vehiculos_checkpoints->checkpoint->orden . '" data-title="' . _lang('Procesar') . '" class="ajax-modal">' . $vehiculos_checkpoints->checkpoint->nombre . '</a>';
            })
            ->addColumn('fecha_inicio', function ($vehiculos_checkpoints) {

                if ($vehiculos_checkpoints->start_date) {
                    return $vehiculos_checkpoints->start_date;
                } else return '';
            })
            ->addColumn('user', function ($vehiculos_checkpoints) {
                $user = User::where('id', $vehiculos_checkpoints->user_id)->first();
                if ($user)
                    return $user->name;
                else return '';
            })
            ->addColumn('action', function ($vehiculos_checkpoints) {

                $vehiculo = Cars::where('id', $vehiculos_checkpoints->vehiculo_id)->first();
                if ($vehiculo->no_drnpa == 1 && $vehiculos_checkpoints->checkpoint_id == 8)
                    return '';
                else
                    return '<div class="action-buttons">'
                        . '<a href=' . route("checkpoints_vehiculos.get_tramite", [$vehiculos_checkpoints->vehiculo_id, $vehiculos_checkpoints->checkpoint_id]) . ' data-status="' . $vehiculos_checkpoints->status . '" data-prev="' . ($vehiculos_checkpoints->checkpoint->orden - 1) . '" data-orden="' . $vehiculos_checkpoints->checkpoint->orden . '" data-title="' . _lang('Procesar') . '" class="btn btn-warning btn-xs ajax-modal"><i class="far fa-folder"></i></a>'
                        . '</div>';
            })

            ->editColumn('status', function ($vehiculos_checkpoints) {
                $vehiculo = Cars::where('id', $vehiculos_checkpoints->vehiculo_id)->first();
                if ($vehiculo->no_drnpa == 1 && $vehiculos_checkpoints->checkpoint_id == 8)
                    return '<span class="badge badge-success"><i class="ti-na"></i></span>';
                else
                if ($vehiculos_checkpoints->status == 'completado') {
                    return '<span class="badge badge-success"><i class="ti-check"></i></span>';
                } else {
                    return '<span class="badge badge-danger"><i class="ti-close"></i></span>';
                }
            })
            ->rawColumns(['nombre', 'action', 'status'])

            ->setRowId(function ($vehiculos_checkpoints) {
                return "row_" . $vehiculos_checkpoints->checkpoint_id;
            })
            ->make(true);
    }


    public function getTitular($vehiculo_id)
    {
        $car = Cars::where('id', $vehiculo_id)->first();
        return view('backend.accounting.tramitador.modal.titular', compact('car'));
    }

    public function setTitular(Request $request)
    {
        //
        $vehiculo_id = $request->post('vehiculo_id');
        $vehiculo = Cars::where('id', $vehiculo_id)->first();
        $vehiculo->titular = $request->post('titular');
        $vehiculo->asegurado = $request->post('titular');
        $vehiculo->save();
        return response()->json(['result' => 'success', 'action' => 'update', 'message' => 'Actualizacion Satisfactoria']);
    }

    public function updateCheckPoint($car)
    {
        // Fetch all checkpoints
        $checkpoints = $car->checkpoints;

        // Data to insert
        $data = [];

        foreach ($checkpoints as $checkPointVehiculo) {
            try {


                switch ($checkPointVehiculo->checkpoint_id) {
                    case '1':
                        if ($car->asegurado != null && $car->contacto != null && $car->fecha_confirmacion_contacto != null) {
                            $checkPointVehiculo->status = 'completado';
                        } elseif ($car->asegurado == null && $car->contacto == null && $car->fecha_confirmacion_contacto == null) {
                            $checkPointVehiculo->status = 'pendiente';
                        } else {
                            $checkPointVehiculo->status = 'iniciado';
                        }
                        break;
                    case '2':
                        if ($car->fecha_limite_retiro != null  && $car->fecha_retiro != null) {
                            $checkPointVehiculo->status = 'completado';
                        } elseif ($car->fecha_limite_retiro == null  && $car->fecha_retiro == null) {
                            $checkPointVehiculo->status = 'pendiente';
                        } else {
                            $checkPointVehiculo->status = 'iniciado';
                        }
                        break;
                    case '3':
                        if ($car->fecha_entrega_asegurado_cia != null && $car->entregado_a != null) {
                            $checkPointVehiculo->status = 'completado';
                        } elseif ($car->fecha_entrega_asegurado_cia == null && $car->entregado_a == null) {
                            $checkPointVehiculo->status = 'pendiente';
                        } else {
                            $checkPointVehiculo->status = 'iniciado';
                        }
                        break;
                    case '4':
                        if ($car->gestor != null) {
                            $checkPointVehiculo->status = 'completado';
                        } else {
                            $checkPointVehiculo->status = 'pendiente';
                        }
                        break;
                    case '5':
                        if ($car->fecha_envio_baja != null) {
                            $checkPointVehiculo->status = 'completado';
                        } else {
                            $checkPointVehiculo->status = 'pendiente';
                        }
                        break;
                    case '6':
                        if ($car->fecha_recepcion != null) {
                            $checkPointVehiculo->status = 'completado';
                        } else {
                            $checkPointVehiculo->status = 'pendiente';
                        }
                        break;
                    case '7':
                        if ($car->fecha_envio_doc != null) {
                            $checkPointVehiculo->status = 'completado';
                        } else {
                            $checkPointVehiculo->status = 'pendiente';
                        }
                        break;
                    case '8':
                        if ($car->fecha_envio_drnpa != null || $car->no_drnpa == 1) {
                            $checkPointVehiculo->status = 'completado';
                        } else {
                            $checkPointVehiculo->status = 'pendiente';
                        }
                        break;
                    case '9':
                        if ($car->fecha_finalizacion != null) {
                            $checkPointVehiculo->status = 'completado';
                        } else {
                            $checkPointVehiculo->status = 'pendiente';
                        }
                        break;
                    case '10':
                        if ($car->fecha_inicio_preinforme != null && $car->fecha_finalizacion_preinforme != null) {
                            $checkPointVehiculo->status = 'completado';
                        } elseif ($car->fecha_inicio_preinforme == null && $car->fecha_finalizacion_preinforme == null) {
                            $checkPointVehiculo->status = 'pendiente';
                        } else {
                            $checkPointVehiculo->status = 'iniciado';
                        }
                        break;
                }

                $checkPointVehiculo->status_date = Carbon::now();
                $checkPointVehiculo->save();
            } catch (\Exception $e) {
                Log::error('Error updating or creating CheckpointVehiculo for car ID ' . $car->id . ' and checkpoint ID ' . $checkpoint->id . ': ' . $e->getMessage());
            }
        }

        try {
            // $max_tramite = DB::table('vehiculos_checkpoints')
            //     ->where('vehiculo_id', $car->id)
            //     ->where('status', 'completado')
            //     ->max('checkpoint_id');

            $max_tramite = DB::table('vehiculos_checkpoints')
                ->join('checkpoints', 'vehiculos_checkpoints.checkpoint_id', '=', 'checkpoints.id')
                ->where('vehiculo_id', $car->id)
                ->where('status', 'completado')
                ->where('orden', '<',999)
                ->max('orden');

            if ($max_tramite >= 1 && $max_tramite <= 3) {
                $status = 'En Proceso';
            } elseif ($max_tramite >= 4 && $max_tramite <= 9) {
                $status = 'En Gestoria';
            } elseif ($max_tramite >= 10 && $max_tramite != 999) {
                $status = 'Finalizado';
            } else {
                $status = 'Pendiente';
            }
            $car->estado_tramite = $status;
            $car->save();
        } catch (\Exception $e) {
            Log::error('Error updating car status for car ID ' . $car->id . ': ' . $e->getMessage());
        }
    }


    public function show04D(Request $request, $id)
    {
        $company_id = company_id_arr();
        $user_type = Auth::user()->user_type;
        $data = array();

        $data['car'] = Cars::where('cars.id', $id)
            ->whereIn('company_id', $company_id)
            ->with('marca_modelo')
            ->with('aseguradora')
            ->with('provincias')
            ->with('tramitador')
            ->with('lugar_entrega')
            ->with('estado')
            ->first();
        $data['save'] = true;
        if (!$data['car']) {
            return back()->with('error', _lang('Sorry, Car not found !'));
        }

        return view('backend.accounting.tramitador.modal.imagenes04D', $data);
    }

    public function update04D(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //        'name' => 'required',
            //        'client_id' => 'required',
            //        'billing_type' => 'required',
            //        'status' => 'required',
            //        'fixed_rate' => 'required_if:billing_type,fixed',
            //        'hourly_rate' => 'required_if:billing_type,hourly',
            //        'start_date' => 'required',
        ]);

        $id =  $request->input('id');

        if ($validator->fails()) {

            return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
        }

        $project = Cars::where('id', $id)->first();


        //eliminar las imagenes seleccionadas
        $arrImgDelete = $request->input('imgDeleteRecepcion', false);
        if ($arrImgDelete && isset($arrImgDelete[0])) {
            foreach ($arrImgDelete as $imgdelete) {
                $img = Imagen::where('id', $imgdelete)->first();

                unlink(public_path('uploads/vehiculos/' . $img->img));
                Imagen::where('id', $imgdelete)->delete();
            }
        }

        if (!empty($request->file('imagen_recepcion'))) {
            $this->uploadImg($request, ['dir' => 'vehiculos', 'idCar' => $project->id]);
        }


        return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated Sucessfully')]);
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        $data = $this->data_export($request);
        $export = new \App\Exports\CarsExportPdf($data);
        return $export->generate();
    }



    public function exportExcel(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $data = $this->data_export($request);

        return Excel::download(new \App\Exports\CarsExport($data), 'vehiculos.xlsx');
    }

    public function data_export($request)
    {

        $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
        $cars = Cars::with([
            'marca_modelo.marca',
            'marca_modelo.modelo',
            'company',
            'provincias',
            'tramitador',
            'lugar_entrega',
            'responsable_retiro',
            'estado'
        ])
            ->whereIn('company_id', $company_id);


        foreach ($request->input('columns') as $index => $column) {
            $columnName = $column['data'];
            $searchValue = $column['search']['value'];

            if (!empty($searchValue)) {
                switch ($columnName) {
                    case 'tramitador':
                        $cars->whereHas('tramitador', function ($q) use ($searchValue) {
                            $q->where('name', 'like', "%{$searchValue}%");
                        });
                        break;

                    case 'aseguradora':
                        $cars->whereHas('aseguradora', function ($q) use ($searchValue) {
                            $q->where('nombre', 'like', "%{$searchValue}%");
                        });
                        break;

                    case 'marca_modelo':
                        $cars->whereHas('marca_modelo', function ($q) use ($searchValue) {
                            $q->whereHas('marca', function ($marca) use ($searchValue) {
                                $marca->where('marca', 'like', "%{$searchValue}%");
                            })->orWhereHas('modelo', function ($modelo) use ($searchValue) {
                                $modelo->where('modelo', 'like', "%{$searchValue}%");
                            });
                        });
                        break;

                    case 'estado':
                        $cars->whereHas('estado', function ($q) use ($searchValue) {
                            $q->where('estado', 'like', "%{$searchValue}%");
                        });
                        break;

                    case 'lugar_entrega':
                        $cars->whereHas('lugar_entrega', function ($q) use ($searchValue) {
                            $q->where('nombre', 'like', "%{$searchValue}%");
                        });
                        break;

                    default:
                        // Filtro genérico para columnas directas de la tabla `cars`
                        $cars->where($columnName, 'like', "%{$searchValue}%");
                        break;
                }
            }
        }

        // Manejar filtros adicionales enviados por el usuario (por ejemplo, status)
        if ($request->has('status')) {
            $cars->whereHas('estado', function ($q) use ($request) {
                $q->whereIn('id', json_decode($request->post('status')));
            });
        }

        $cars = $cars->get();

        $data = $cars->map(function ($car) {

            if ($car->company_id == 1) {
                $in = 'PM-';
            } else if ($car->company_id == 2) {
                $in = 'PC-';
            }


            return [
                'Interno' => $in . $car->id,
                'Dominio' => $car->dominio,
                'Anulado' => $car->deleted_at ? 'Sí' : 'No',
                'Fecha Asignación' => $car->fecha_asignacion ? date(get_company_option('date_format', 'Y-m-d'), strtotime($car->fecha_asignacion)) : '',
                'Tramitador' => $car->tramitador->name ?? '',
                'Aseguradora' => $car->aseguradora->nombre ?? '',
                'Tramitador Compañía' => $car->tramitador_compania,
                'Siniestro' => $car->siniestro,
                'Marca/Modelo' => ($car->marca_modelo->marca->marca ?? '') . ' ' . ($car->marca_modelo->modelo->modelo ?? ''),
                'Motor' => $car->motor_nro,
                'Tipo Baja' =>  $this->datos['tipo_baja'][$car->tipo_baja] ?? '',
                'Asegurado' => $car->asegurado,
                'Contacto' => $car->contacto,
                'Lugar Retiro' => $car->lugar_retiro,
                'Localidad' => $car->localidad,
                'Provincia' => $car->provincias->provincia ?? '',
                'Estado' => $car->estado->estado ?? '',
                'Entregado A' => $car->entregado_a,
                'Fecha Entrega' => $car->fecha_entrega ? date(get_company_option('date_format', 'Y-m-d'), strtotime($car->fecha_entrega)) : '',
                'Observación Admin' => strip_tags(clean($car->observaciones_admin)),
                'Fecha Recepción' => $car->fecha_recepcion ? date(get_company_option('date_format', 'Y-m-d'), strtotime($car->fecha_recepcion)) : '',
                'Coordinar Retiro' => $car->coordinar_retiro == 1 ? 'Sí' : 'No',
                'Fecha Envío Doc' => $car->fecha_envio_doc ? date(get_company_option('date_format', 'Y-m-d'), strtotime($car->fecha_envio_doc)) : '',
                'Chasis' => $car->chasis,
                'Fecha Confirmación Contacto' => $car->fecha_confirmacion_contacto ? date(get_company_option('date_format', 'Y-m-d'), strtotime($car->fecha_confirmacion_contacto)) : '',
                'Fecha Límite Retiro' => $car->fecha_limite_retiro ? date(get_company_option('date_format', 'Y-m-d'), strtotime($car->fecha_limite_retiro)) : '',
                'Responsable Retiro' => $car->responsable_retiro->name ?? '',
                'CRP Nro' => $car->crp_nro,
                'Entregar En' => $car->lugar_entrega->nombre ?? '',
                'Fecha Retiro' => $car->fecha_retiro ? date(get_company_option('date_format', 'Y-m-d'), strtotime($car->fecha_retiro)) : '',
                'Fecha Ingreso' => $car->fecha_ingreso ? date(get_company_option('date_format', 'Y-m-d'), strtotime($car->fecha_ingreso)) : '',
                'Observación Gerente/Operario' => $car->observacion_gerente_operario,
                'Observación Retiro' => $car->observacion_retiro,
            ];
        });
        return $data;
    }
}
