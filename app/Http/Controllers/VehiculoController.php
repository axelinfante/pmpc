<?php

namespace App\Http\Controllers;

use App\Aseguradora;
use App\Cars;
use App\Checkpoint;
use App\Company;
use App\Estado;
use App\FileManager;
use App\Imagen;
use App\Imports\CarsImport;
use App\Imports\CarsUpdateImport;
use App\Invoice;
use App\InvoiceItem;
use App\Item;
use App\Lugar_entregas;
use App\Marca;
use App\MarcaModelo;
use App\Modelo;
use App\Notifications\CargaImagenVehiculo;
use App\Notifications\RetiroVehiculoUpdated;
use App\Pagos_car;
use App\Pieza_ausente;
use App\Product;
use App\Provincia;
use App\Quotation;
use App\Responsable_entregas;
use App\Responsable_retiros;
use App\Role;
use App\Seguimiento_car;
use App\Transaction;
use App\User;
use App\Utilities\Imagenes;
use Illuminate\Http\Request;
use App\Project;
use App\ProjectMember;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xls;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use Validator;
use DataTables;
use Auth;
use DB;
use Notification;
use App\Notifications\ProjectCreated;
use App\Notifications\ProjectUpdated;
use Maatwebsite\Excel\Facades\Excel;

use App\CheckpointVehiculo;
use App\Historial_ordenes_desarme;
use App\HistorialStateCar;
use App\Imports\CarsImportIfNotExist;
use App\Notifications\CambioEstadoVehiculo;
use Carbon\Carbon;

use Illuminate\Support\Facades\Log;

use App\Notifications\PagosCarCreated;
use App\Notifications\PagosCarChangePriority;
use App\Notifications\PagosCarChangeStatus;

use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfReader;
use Illuminate\Http\Response;

use App\Exports\CarsExport;
use App\Exports\CarsExportPDF;

use ZipArchive;
use File;
use App\Utilities\cc_client;

use App\Notifications\CambioEstadoVehiculoGerenciales;
use OwenIt\Auditing\Models\Audit;

class VehiculoController extends Controller
{
    use Imagenes;
    use cc_client;



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
        if (strtolower(auth()->user()->role->name) == 'receptor') {
            return view('backend.accounting.vehiculo.list_receptor', $datos);
        }
        if (strtolower(auth()->user()->role->name) == 'gerente de operarios' || strtolower(auth()->user()->role->name) == 'operario') {
            return view('backend.accounting.vehiculo.list_gerente_operario', $datos);
        }
        // if (strtolower(auth()->user()->role->name) == 'tramitador') {
        //     return view('backend.accounting.vehiculo.list_tramitador', $datos);
        // }

        //dd($datos['responsable_entregas'][1]);
        return view('backend.accounting.vehiculo.list', $datos);
    }

    public function historial(Request $request)
    {
        $id = $request->id;



        if (request()->ajax()) {

            $carAudit = Audit::where('auditable_type', Cars::class)
                ->where('auditable_id', $id)
                ->with('user')
                ->with('auditable');
            // $car = Cars::find($id);


            return DataTables::eloquent($carAudit)
                ->addColumn('fecha', function ($data) {
                    return $data->created_at;
                })
                ->addColumn('interno', function ($data) {
                    $car = $data->auditable;
                    return nroInternoAlias($car->company_id, $car->tipo_vehiculo, $car->id);
                })

                ->addColumn('dominio', function ($data) {
                    $car = $data->auditable;

                    return  $car->dominio;
                })
                ->addColumn('valores_old', function ($data) {
                    $dats = $data->old_values;
                    if (empty($dats) || !is_array($dats)) {
                        return 'Sin datos previos';
                    }

                    $html = '';
                    foreach ($dats as $key => $dat) :
                        $html .= "$key : " . htmlspecialchars($dat) . " <br>";
                    endforeach;
                    // dd($html);

                    return $html;
                })

                ->addColumn('new_values', function ($data) {
                    $dats = $data->new_values;
                    $html = '';
                    foreach ($dats as $key => $dat) :
                        $html .= "$key : " . htmlspecialchars($dat) . " <br>";
                    endforeach;

                    return $html;
                })
                ->addColumn('usuario', function ($data) {
                    $user = $data->user;

                    return $user->name . ' ' . $user->lastname;
                })
                ->rawColumns(['valores_old', 'new_values'])
                ->tojson();
        }

        // return view('backend.accounting.vehiculo.historial', compact('id'));
    }
    public function vistaHistorial(Request $request)
    {
        $id = $request->id;
        return view('backend.accounting.vehiculo.modal.historial', compact('id'));
    }

    public function importxlsCars()
    {

        $file = storage_path() . '/app/public/carspc.xlsx';
        try {
            set_time_limit(0);
            DB::beginTransaction();
            Excel::import(new CarsImport(), $file);
            DB::commit();
            // return back()
            //     ->with('notification', ['type' => 'success', 'title' => 'Usuarios importados']);
        } catch (\Exception $exception) {
            //DB::rollBack();
            throw ($exception);
            // return back()
            //     ->with('notification', ['type' => 'danger', 'title' => 'Error importando usuarios']);
        }
    }
    public function importxlsCarsIfNotExist()
    {

        $file = storage_path() . '/app/public/autosPm.xlsx';
        try {
            set_time_limit(0);
            DB::beginTransaction();
            Excel::import(new CarsImportIfNotExist, $file);
            DB::commit();
            // return back()
            //     ->with('notification', ['type' => 'success', 'title' => 'Usuarios importados']);
        } catch (\Exception $exception) {
            //DB::rollBack();
            throw ($exception);
            // return back()
            //     ->with('notification', ['type' => 'danger', 'title' => 'Error importando usuarios']);
        }
    }
    public function importxlsCarsEstados()
    {

        $file = storage_path() . '/app/public/pcEstados.xlsx';
        try {
            set_time_limit(0);
            // DB::beginTransaction();
            Excel::import(new CarsUpdateImport(), $file);
            // DB::commit();
            // return back()
            //     ->with('notification', ['type' => 'success', 'title' => 'Usuarios importados']);
        } catch (\Exception $exception) {
            //DB::rollBack();
            throw ($exception);
            // return back()
            //     ->with('notification', ['type' => 'danger', 'title' => 'Error importando usuarios']);
        }
    }
    public function importXls()
    {

        //cargar archivo storage  public disk
        //$file = storage_path(). '/app/public/estados.xlsx' ;
        // $file = storage_path() . '/app/public/aseguradoras.xlsx';
        $file = storage_path() . '/app/public/vehiculo pc.xlsx';

        $spreadsheet =  IOFactory::load($file);
        $totalDeHojas = $spreadsheet->getSheetCount();

        $datos = [];
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {

            # Obtener hoja en el índice que vaya del ciclo
            $hojaActual = $spreadsheet->getSheet($indiceHoja);

            $hojaActual->getRowIterator();
            foreach ($hojaActual->getRowIterator() as $fila) {
                foreach ($fila->getCellIterator() as $celda) {
                    $res = $celda->getValue();
                    $resSinNumeros = preg_replace('/[0-9]+/', '', $res);
                    if (trim($resSinNumeros) != '') {
                        $datos[$resSinNumeros] = $resSinNumeros;
                    }
                }
            }


            # Imprimir
            //echo "En <strong>$coordenadas</strong> tenemos el valor <strong>$valorRaw</strong>. ";
            //            echo "Formateado es: <strong>$valorFormateado</strong>. ";
            //            echo "Calculado es: <strong>$valorCalculado</strong><br><br>";

        }
        foreach ($datos as $dat => $value) :
            //$estados = new Estado();
            $estados = new Aseguradora();
            //$estados-> estado = $dat;
            $estados->nombre = $dat;
            $estados->save();
        endforeach;
        dd($estados);
    }


    public function datos(): array
    {
        $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
        //traer datos necesarios para crear un nuevo registro de auto.
        $marca_modelos = MarcaModelo::all();

        //marcas
        $marcas = Marca::all();
        //modelos
        $modelos = Modelo::all();

        //tramitadores (usuarios con ese rol de la empresa)
/*        $tramitadores = User::whereIn('company_id', $company_id)
            ->whereIn('role_id', [6, 18,21])
            ->where('user_type', 'staff')
            ->get();*/

            $tramitadores = User::whereIn('role_id', [6, 18,21])
            ->where('user_type', 'staff')
            ->get();


        // $tramitadores = User::where('company_id', auth()->user()->company_id)->wherehas('role', function ($string) {
        //     $string->where('name', 'Tramitador');
        //     $string->where('company_id', auth()->user()->company_id);
        // })->where('user_type', 'staff')
        //     ->get();

        /*$tramitadores = User::where('company_id', auth()->user()->company_id)
            ->whereHas('role', function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'Tramitador')
                        ->orWhere('name', 'Tramitador con gastos');
                })
                    ->where('company_id', auth()->user()->company_id);
            })
            ->where('user_type', 'staff')
            ->get();*/

        $tramitadoresAll = User::whereHas('role', function ($query) {
            $query->where(function ($q) {
                $q->where('name', 'Tramitador')
                    ->orWhere('name', 'Tramitador con gastos');
            });
            // ->where('company_id', auth()->user()->company_id);
        })
            ->where('user_type', 'staff')
            ->get();

        //asegurdadoras

        $aseguradoras = Aseguradora::all();

        //provincias
        $provincias = Provincia::all();

        //responsable entregas y de retiros
        /*  $responsable_entregas = [
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
        ];*/

        $tipo_baja = $this->tipo_baja;
        $responsable_entregas = $this->responsable_entregas;
        $responsable_retiros = User::where('company_id', auth()->user()->company_id)->wherehas('role', function ($string) {
            $string->where('name', 'Transportista')->where('company_id', auth()->user()->company_id);
        })->where('user_type', 'staff')
            ->get();

        // dd($responsable_retiros);

        //lugar de entregas y estado
        $lugar_entregas = Lugar_entregas::all();

        $tipo_vehiculo = $this->TipoVehiculo();

        $estados = Estado::select('*')->where('Activo', "Si")->orderBy('estado', 'asc')->get();
        //Estado::all();
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
            'modelos',
            'tramitadoresAll',
            'tipo_vehiculo'
        ]);
    }

    public function updateEstado(Request $request, $id)
    {
        $idEstado = $request->estado;
        $car = Cars::find($id);

        // grabar historial cambio de estado
        $estado_ant =  $car->idEstado;

        $car->idEstado = $idEstado;
        $car->save();

        // grabar historial cambio de estado
        $this->updateHistorialEstado($car->id, $estado_ant, $car->idEstado);

        $this->notificarCambioEstado($car->id, $estado_ant, $car->idEstado);

        return response()->json(['result' => 'ok']);
    }

    public function updateUbicacion(Request $request, $id)
    {
        $idUbicacion = $request->ubicacion;
        $car = Cars::find($id);
        $car->idLugar_entrega = $idUbicacion;
        $car->save();

        return response()->json(['result' => 'ok']);
    }

    /*    public function get_table_data_old(Request $request)
    {
        //dd(session('cia'));
        $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
        $user_type = Auth::user()->user_type;
        $datos = $this->datos();
        $cars = Cars::select('cars.*')->withTrashed()
            ->with('marca_modelo')
            ->with('company')
            ->with('provincias')
            ->with('tramitador')
            ->with('lugar_entrega')
            ->with('responsable_retiro')
            ->with('estado')
            ->whereIn('company_id', $company_id);
        //->orderBy("projects.id","desc");

        //dd($cars);
        return Datatables::eloquent($cars)
            //
            ->filterColumn('tramitador', function ($query, $keyword) {


                $sql = "CONCAT(users.first_name,'-',users.last_name)  like ?";
                $query->orwhereHas('tramitador', function ($str) use ($keyword) {
                    $str->where('name', 'like', "%{$keyword}%");
                });
            })

            ->filterColumn('entregar_en', function ($query, $keyword) {


                $sql = "CONCAT(users.first_name,'-',users.last_name)  like ?";
                $query->orwhereHas('lugar_entrega', function ($str) use ($keyword) {
                    $str->where('nombre', 'like', "%{$keyword}%");
                });
            })

            ->filterColumn('aseguradora', function ($query, $keyword) {
                $sql = "CONCAT(users.first_name,'-',users.last_name)  like ?";
                $query->orwhereHas('aseguradora', function ($str) use ($keyword) {
                    $str->where('nombre', 'like', "%{$keyword}%");
                });
            })

            ->filterColumn('fecha_ingreso', function ($query, $keyword) {

                    $date_range=($keyword!='') ? explode(" - ",$keyword) : array();
                    //echo count($date_range);
                    if (count($date_range)==2){
                        $query->whereBetween('cars.fecha_ingreso', [$date_range[0], $date_range[1]]);
                    }
            })
            ->filterColumn('marca_modelo', function ($query, $keyword) {
                $sql = "CONCAT(users.first_name,'-',users.last_name)  like ?";
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
                ////                            if ($request->has('client_id')) {
                ////                                $query->where('client_id', 'like', "%{$request->post('client_id')}%");
                ////                            }
                if ($request->has('status')) {
                    $query->whereHas('estado', function ($s) use ($request) {
                        $s->whereIn('id', json_decode($request->post('status')));
                    });
                }
            })
            //            ->filterColumn('status', function($query, $keyword) use ($request) {
            //                $query->whereHas('estado',function($s) use ($request) {
            //                    $s->whereIn('id', json_decode($request->post('status')));
            //                });
            //            })



            ->editColumn('id', function ($car) {

                //if ($car->company_id == 1) {
                   // $in = 'PM';
                //} else if ($car->company_id == 2) {
                  //  $in = 'PC';
                //}

                //$in.=$car->tipo_vehiculo.'-'; 
                return '<a href="' . action('VehiculoController@show', $car->id) . '" class="btn-xs ajax-modal" data-title=" Multimedia">' . nroInternoAlias($car->company_id,$car->tipo_vehiculo,$car->id) .  '</a>';
            })
            ->editColumn('anulado', function ($car) {

                if ($car->deleted_at) {
                    $in = 'Si';
                } else {
                    $in = 'No';
                }

                return $in;
            })

            ->editColumn('company', function ($car) {



                return $car->company->business_name;
            })
            ->editColumn('fecha_asignacion', function ($car) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return date($date_format, strtotime($car->fecha_asignacion));
            })
            //            ->editColumn('Forma', function ($car) {
            //
            //                return $car->forma;
            //            })
            ->editColumn('tramitador', function ($car) {

                return $car->tramitador->name ?? null;
            })
            ->editColumn('aseguradora', function ($car) {

                return $car->aseguradora->nombre ?? null;
            })
            ->editColumn('tramitador_compania', function ($car) {
                return $car->tramitador_compania;
            })
            ->editColumn('siniestro', function ($car) {
                return $car->siniestro;
            })
            ->editColumn('dominio', function ($car) {
                return $car->dominio;
            })
            ->editColumn('marca_modelo', function ($car) {

                return ($car->marca_modelo->marca->marca ?? '') . ' ' . ($car->marca_modelo->modelo->modelo ?? '');
            })
            ->editColumn('motor', function ($car) {
                return $car->motor_nro;
            })
            ->editColumn('tipo_baja', function ($car) use ($datos) {
                return $datos['tipo_baja'][$car->tipo_baja] ?? null;
            })
            ->editColumn('contacto', function ($car) {
                return $car->contacto;
            })
            ->editColumn('lugar_retiro', function ($car) {
                return $car->lugar_retiro;
            })
            ->editColumn('provincia', function ($car) {
                return $car->provincias->provincia ?? null;
            })
            ->editColumn('estado', function ($car) {

                if (strtolower(auth()->user()->role->name) == 'gerente de operarios' || strtolower(auth()->user()->role->name) == 'operario') {

                    $html = "<select class='form-control' idCar='" . $car->id . "' name='estadoMod' onchange='updatedStado(this)'>";
                    $html .= "<option value=''>Seleciona el estado</option>";
                    foreach (Estado::all() as $estado) {
                        $html .= "<option value='{$estado->id}' " . ($estado->id == $car->idEstado ? 'selected' : '') . " >{$estado->estado}</option>";
                    }

                    $html .= "</select>";

                    return $html;
                }
                return $car->estado->estado ?? null;
            })
            ->editColumn('entregado_a', function ($car) use ($datos) {

                return $datos['responsable_entregas'][$car->entregado_a] ?? null;
            })
            ->editColumn('lugar_entrega', function ($car) {

                if (strtolower(auth()->user()->role->name) == 'gerente de operarios' || strtolower(auth()->user()->role->name) == 'operario') {

                    $html = "<select class='form-control' idCar='" . $car->id . "' name='estadoMod' onchange='updateUbicacion(this)'>";
                    $html .= "<option value=''>Seleciona la ubicacion</option>";
                    foreach (Lugar_entregas::all() as $ubicacion) {
                        $html .= "<option value='{$ubicacion->id}' " . ($ubicacion->id == $car->idLugar_entrega ? 'selected' : '') . " >{$ubicacion->nombre}</option>";
                    }

                    $html .= "</select>";

                    return $html;
                }

                return $car->lugar_entrega->nombre ?? null;
            })
            ->editColumn('fecha_entrega', function ($car) {
                $date_format = get_company_option('date_format', 'Y-m-d');

                return isset($car->fecha_entrega) ? date($date_format, strtotime($car->fecha_entrega)) : null;
                //return $car->fecha_entrega_asegurado_cia ?? null;
            })
            ->editColumn('fecha_confirmacion', function ($car) {
                $date_format = get_company_option('date_format', 'Y-m-d');

                return isset($car->fecha_confirmacion) ? date($date_format, strtotime($car->fecha_confirmacion)) : null;
                //return $car->fecha_entrega_asegurado_cia ?? null;
            })->editColumn('fecha_recepcion', function ($car) {
                $date_format = get_company_option('date_format', 'Y-m-d');

                return isset($car->fecha_recepcion) ? date($date_format, strtotime($car->fecha_recepcion)) : null;
                //return $car->fecha_entrega_asegurado_cia ?? null;
            })
            ->editColumn('fecha_confirmacion_contacto', function ($car) {
                $date_format = get_company_option('date_format', 'Y-m-d');

                return isset($car->fecha_confirmacion_contacto) ? date($date_format, strtotime($car->fecha_confirmacion_contacto)) : null;
                //return $car->fecha_entrega_asegurado_cia ?? null;
            })
            ->editColumn('fecha_limite_retiro', function ($car) {
                $date_format = get_company_option('date_format', 'Y-m-d');

                return isset($car->fecha_limite_retiro) ? date($date_format, strtotime($car->fecha_limite_retiro)) : null;
                //return $car->fecha_entrega_asegurado_cia ?? null;
            })

            ->editColumn('fecha_retiro', function ($car) {
                $date_format = get_company_option('date_format', 'Y-m-d');

                return isset($car->fecha_retiro) ? date($date_format, strtotime($car->fecha_retiro)) : null;
                //return $car->fecha_entrega_asegurado_cia ?? null;
            })

            ->editColumn('fecha_ingreso', function ($car) {
                $date_format = get_company_option('date_format', 'Y-m-d');

                return isset($car->fecha_ingreso) ? date($date_format, strtotime($car->fecha_ingreso)) : null;
                //return $car->fecha_entrega_asegurado_cia ?? null;
            })
            ->editColumn('fecha_envio_doc', function ($car) {
                $date_format = get_company_option('date_format', 'Y-m-d');

                return isset($car->fecha_envio_doc) ? date($date_format, strtotime($car->fecha_envio_doc)) : null;
                //return $car->fecha_entrega_asegurado_cia ?? null;
            })
            ->editColumn('observacion_admin', function ($car) {
                return strip_tags(clean($car->observaciones_admin));
            })->editColumn('coordinar_retiro', function ($car) {
                return $car->coordinar_retiro == 1 ? 'X' : '';
            })
            ->editColumn('responsable_retiro', function ($car) {
                return $car->responsable_retiro->name ?? null;
            })
            ->editColumn('entregar_en', function ($car) {
                return $car->lugar_entrega->nombre ?? null;
            })->editColumn('control', function ($car) {
                return $car->control == 1 ? 'En fecha' : 'Explicar';
            })
            ->editColumn('observacion_retiro', function ($car) {
                return strip_tags(clean($car->observacion_retiro));
            })
            ->editColumn('observacion_gerente_operario', function ($car) {
                if (strtolower(auth()->user()->role->name) == 'gerencial' || strtolower(auth()->user()->role->name) == 'gerente de operarios' || strtolower(auth()->user()->role->name) == 'operario') {
                    return strip_tags(clean($car->observacion_gerente_operario));
                }
                return '';
            })
            ->addColumn('pieza_no_disponible', function ($car) {
                $piezas = $car->pieza_ausente;

                $html = '';
                if (!empty($piezas)) {
                    foreach ($piezas as $pieza) {
                        $html .= $pieza->name . '<br>';
                    }
                }

                return $html;
            })
            ->addColumn('action', function ($car) {
                if ($car->company_id == 1) {
                    $in = 'PM-';
                } else if ($car->company_id == 2) {
                    $in = 'PC-';
                }
                $filemanager = FileManager::where('name', $in . $car->id)->first();
                $enlace = '';

                if (!empty($filemanager)) {
                    $enlace = '<a class="btn btn-xs" target="_blank" href="' . url(
                        'file_manager/directory/' . encrypt($filemanager->id)
                    ) . '"><i class="far fa-folder"></i></a>';
                }
                return '<form action="' . action('VehiculoController@destroy', $car['id']) . '" class="text-center" method="post">'
                    . '<a href="' . action('VehiculoController@show', $car['id']) . '" class="btn btn-primary
btn-xs ajax-modal" data-title=" ' . _lang('Multimedia') . '"><i class="ti-eye"></i></a>&nbsp;'
                    . '<a href="' . action('VehiculoController@edit', $car['id']) . '" 
data-title="' . _lang('Update Vehicle') . '" class="btn btn-warning btn-xs ajax-modal"><i class="ti-pencil"></i></a>&nbsp;'
                    . '<a target="_blank"
href="' . action('VehiculoController@movimientos', $car['id']) . '" data-title="' . _lang('Ver movimientos') . '" class="btn btn-warning btn-xs"><i 
class="ti-receipt"></i></a>&nbsp;'
                    . '<a
href="' . action('VehiculoController@seguimiento', $car['id']) . '" data-title="' . _lang('Ver Estado') . '" class="btn btn-warning btn-xs ajax-modal"><i 
class="fas fa-search"></i></a>&nbsp;'
                    .
                    '<a
href="' . action('VehiculoController@certificado', $car['id']) . '" data-title="' . _lang('Certificado') . '" class="btn btn-success btn-xs ajax-modal"><i 
class="">C</i></a>&nbsp;'
                    .
                    $enlace

                    . csrf_field()
                    . '<input name="_method" type="hidden" value="DELETE">'
                    . '<button 
class="btn btn-danger btn-xs btn-remove" type="submit"><i class="ti-eraser"></i></button>'
                    . '</form>';
            })
            ->setRowId(function ($car) {
                return "row_" . $car->id;
            })
            ->rawColumns(['action', 'pieza_no_disponible', 'estado', 'members.name', 'status', 'id', 'lugar_entrega'])
            ->make(true);
    }*/

    public function get_project_info($id = '')
    {
        $project = Project::with('client')
            ->where("id", $id)
            ->whereIn('company_id', company_id_arr())->first();
        echo json_encode($project);
    }

    public function movimiento($id)
    {
        $car = Cars::where('id', $id)->first();

        $dominio = $car->dominio;
        $company = $car->company_id;

        $in = '';
        if ($company == 1) {
            $in .= 'PM-';
        } else if ($company == 2) {
            $in .= 'PC-';
        }
        $interno = $in . $id;

        $datos = [
            'idCar' => $id,
            'dominio' => $dominio,
            'interno' => $interno,
            'control' => $this->control
        ];

        return view('backend.accounting.vehiculo.modal.movimiento', $datos);
    }

    public function movimientos($id)
    {
        $pagos_car = Pagos_car::where('id_car', $id)->with('transaction')->first();

        $car = Cars::where('id', $id)->first();

        $dominio = $car->dominio;
        $company = $car->company_id;

        $in = '';
        if ($company == 1) {
            $in .= 'PM-';
        } else if ($company == 2) {
            $in .= 'PC-';
        }
        $interno = $in . $id;



        //dd($id);
        $datos = [
            'idCar' => $id,
            'dominio'  => $dominio,
            'movimientos' => $pagos_car ? $pagos_car->transaction : [],
        ];

        return view('backend.accounting.vehiculo.list_movimiento', $datos);
    }

    public function storeMovimiento(Request $request)
    {
        $validator = Validator::make($request->all(), [
            //            'trans_date' => 'required',
            //            'account_id' => 'required',
            //            'chart_id' => 'required',
            'amount' => 'required|numeric',
            //            'payment_method_id' => 'required',
            'reference' => 'nullable|max:50',
            //'detalle_rubro' => 'required',
            'attachment' => 'nullable|mimes:jpeg,png,jpg,doc,pdf,docx,zip',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            }
            //            else {
            //                return redirect('expense/create')
            //                    ->withErrors($validator)
            //                    ->withInput();
            //            }
        }

        $company_id_car = Cars::where('id',  $request->input('idCar'))->first()->company_id;
        $attachment = "";
        if ($request->hasfile('attachment')) {
            $file = $request->file('attachment');
            $attachment = time() . $file->getClientOriginalName();
            $file->move(public_path() . "/uploads/transactions/", $attachment);
        }

        $transaction = new Transaction();
        $transaction->trans_date = $request->input('trans_date');
        $transaction->account_id = $request->input('account_id');
        $transaction->chart_id = $request->input('chart_id');
        $transaction->type = 'expense';
        $transaction->dr_cr = 'dr';
        $transaction->amount = $request->input('amount');
        $transaction->base_amount = convert_currency($transaction->account->account_currency, base_currency(), $transaction->amount);

        $transaction->payer_payee_id = $request->input('payer_payee_id');

        $transaction->payment_method_id = $request->input('payment_method_id');

        $transaction->payment_priority = $request->has('payment_priority') && $request->input('payment_priority') !== '' ? $request->input('payment_priority') : null;

        $transaction->reference = $request->input('reference');
        $transaction->razon_social = $request->input('razon_social');
        $transaction->tipo_comprobante_id = $request->input('tipo_comprobante_id');
        $transaction->imputar_a = $request->input('imputar_a');
        $transaction->detalle_rubro = $request->input('detalle_rubro');
        $transaction->banco = $request->input('banco');
        $transaction->cheque_nro = $request->input('cheque_nro');
        $transaction->cheque_vencimiento = $request->input('cheque_vencimiento');
        $transaction->cheque_entregado_a = $request->input('cheque_entregado_a');
        $transaction->note = $request->input('note');
        $transaction->attachment = $attachment;
        $transaction->company_id = $company_id_car;

        $transaction->usd = $request->input('usd');
        $transaction->tasa = $request->input('tasa');


        $transaction->save();

        //Set Prefix Data
        $date_format = get_company_option('date_format', 'Y-m-d');
        $transaction->trans_date = date("$date_format", strtotime($transaction->trans_date));
        $transaction->amount = decimalPlace($transaction->amount, currency());
        $transaction->account_id = $transaction->account->account_title;
        $transaction->chart_id = $transaction->expense_type->name;
        $transaction->payer_payee_id = isset($transaction->payee->name) ? $transaction->payee->name : '';
        $transaction->payment_method_id = $transaction->payment_method->name;

        // relacion de transaccion con auto

        $pagos_car = new Pagos_car();
        $pagos_car->id_car = $request->input('idCar');
        $pagos_car->id_gasto = $transaction->id;
        $pagos_car->save();

        //aqui notificacion de pago urgente y muy urgente


        $cajeros = User::wherehas('role', function ($q) {
            $q->where('name', 'Cajera');
        })->where('company_id', $company_id_car)->get();


        Notification::send($cajeros, new PagosCarCreated($transaction));


        if (!$request->ajax()) {
            //            return redirect('expense/create')->with('success', _lang('Saved Sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved Sucessfully'), 'data' => $transaction]);
        }
    }


    public function updateTransactionCompanyByCar()
    {
        $pagos_car = Pagos_car::all();
        foreach ($pagos_car as $p) :

            $transaction = Transaction::find($p->id_gasto);
            if (isset($transaction)) :
                $car = Cars::where('id', $p->id_car)->first();
                if (isset($car)) {
                    $transaction->company_id = $car->company_id;
                }

                $transaction->save();
            endif;
        endforeach;
    }


    public function changeCompany($id)
    {
        session(['cia' => $id]);
    }


    public function seguimiento($id)
    {
        $car = Cars::where('id', $id)->with(['estado', 'seguimiento'])->first();
        // dd($car);

        //verificar si se vendio el motor
        $product = Product::where('car_id', $car->id)->whereHas('item', function ($str) {
            $str->where('item_name', 'like', '%motor%');
        })->first();
        $mVendido = false;
        $vendedor = false;
        if ($product) {
            $it = InvoiceItem::where('item_id', $product->item->id)->first();
            if (isset($it)) {
                $mVendido = 1;
                $invoice = Invoice::where('id', $it->invoice_id)->with('vendedor')->first();
                $vendedor = $invoice->vendedor->name;

                //dd($vendedor);
                if (isset($car->seguimiento->id)) {
                    Seguimiento_car::find($car->seguimiento->id)->update(['idVendedorMotor' => $vendedor]);
                }
            }
        }


        //dd($product);
        $datos['estados'] = $estados = Estado::select('*')->where('Activo', "Si")->get();  //Estado::all();
        $datos['car'] = $car;
        $datos['idCar'] = $id;
        $datos['mVendido'] = $mVendido;
        $datos['vendedor'] = $vendedor;


        return view('backend.accounting.vehiculo.seguimiento.list', $datos);
    }

    public function storeSeguimiento(Request $request)
    {

        $idSeguimiento = $request->input('idSeguimiento', null);
        if (!$idSeguimiento) {
            $sgue = new Seguimiento_car();
        } else {
            $sgue = Seguimiento_car::find($idSeguimiento);
        }

        $sgue->idCar = $request->input('idCar');
        $sgue->motor_vendido_reservado = $request->input('motor_vendido_reservado');
        $sgue->entra_desarme = $request->input('entra_desarme');
        $sgue->traslado_notificado = $request->input('traslado_notificado');
        $sgue->traer_a = $request->input('traer_a');
        $sgue->fecha_traslado = $request->input('fecha_traslado');
        $sgue->fecha_act_estado = $request->input('fecha_act_estado');
        $sgue->ubicacion = $request->input('ubicacion');

        $sgue->save();

        if ($request->input('estado', false)) {
            $car = Cars::find($request->input('idCar'));

            // grabar historial cambio de estado
            $estado_ant =  $car->idEstado;

            $car->idEstado = $request->input('estado');
            $car->save();

            // grabar historial cambio de estado
            $this->updateHistorialEstado($car->id, $estado_ant, $car->idEstado);

            $this->notificarCambioEstado($car->id, $estado_ant, $car->idEstado);
        }
        return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved Sucessfully'), 'data' => $sgue]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $datos = $this->datos();
        $comp = Company::all();
        $datos['cias'] = $comp;
        $interno = $this->get_last_interno();
        $datos['interno'] = $interno;

        $datos['estados'] = Estado::where('activo', "Si")->orderBy('estado', 'ASC')->get();
        $datos['items'] = Item::where('activo', "Si")->orderBy('item_name', 'ASC')->get();
        //        $items = Item::all();

        //      $datos['items'] = $items;
        //return view('backend.accounting.vehiculo.create', $datos);
        //dd($datos);
        if (!$request->ajax()) {
            return view('backend.accounting.vehiculo.create', $datos);
        } else {
            return view('backend.accounting.vehiculo.modal.create', $datos);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {



        $validator = Validator::make($request->all(), [
			'dominio' => 'nullable|unique:cars',
			'siniestro' => 'nullable|unique:cars',
			'imagen_recepcion.*'          => ['mimes:jpg,jpeg,png,gif,svg']
            //'name' => 'required',
            //'client_id' => 'required',
            //'billing_type' => 'required',
            //'status' => 'required',
            //'fixed_rate' => 'required_if:billing_type,fixed',
            //'hourly_rate' => 'required_if:billing_type,hourly',
            //'start_date' => 'required',
        ]);
		
		$referer = $request->headers->get('referer');
		$path = parse_url($referer, PHP_URL_PATH);
        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
				
				if (strpos($path, 'tramitadores/create') !== false) {
                    return back()->with('error', _lang('Nro de dominio y/o siniestro ya existe'))->withInput();
                }
                return redirect()->route('vehiculo.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }
        //$referer = $request->headers->get('referer');
//        $path = parse_url($referer, PHP_URL_PATH);

        // Verificar si 'dominio' ya existe
        $dominio = $request->input('dominio');
        $siniestro = $request->input('siniestro');
        /*if (Cars::where('dominio', $dominio)->orwhere('siniestro', $siniestro)->exists()) {
            if (!$request->ajax()) {
                if (strpos($path, 'tramitadores/create') !== false) {
                    return back()->with('error', _lang('Nro de dominio y/o siniestro ya existe'))->withInput();
                }
                return redirect()->route('vehiculo.create')->with('erros', 'Nro de dominio y/o siniestro ya existe');
            } else {
                return response()->json(['result' => 'error', 'action' => 'store', 'message' => 'Nro de dominio y/o siniestro ya existe', 'data' => '', 'table' => '#projects_table']);
            }
        }*/


        DB::beginTransaction();

        try {


            //---------------------------------------------------------------- A PEDIDO

            $car = Cars::where('id', '>=', 3000)->where('id', '<', 1000002)->orderBy('id', 'desc')->withTrashed()->first();
            $id = ($car->id ?? null) + 1;
            $auto = Cars::find($id);

            ////////////////////////////////

            // grabar historial cambio de estado


            $project = new Cars();
            $project->id = $id ?? null;
            $project->fecha_asignacion = $request->input('fecha_asignacion');
            $project->forma = $request->input('forma');
            $project->idTramitador = $request->input('idTramitador');
            $project->idAseguradora = $request->input('idAseguradora');
            $project->tramitador_compania = $request->input('tramitador_compania');
            $project->siniestro = $request->input('siniestro');
            $project->dominio = $request->input('dominio');
            $project->idMarca_modelo = $request->input('marca_modelo');
            $project->motor_nro = $request->input('motor');

            $project->asegurado = $request->input('asegurado');
            $project->contacto = $request->input('contacto');
            $project->lugar_retiro = $request->input('lugar_retiro');
            $project->localidad = $request->input('localidad');
            $project->provincia = $request->input('provincia');
            $project->fecha_entrega_asegurado_cia = $request->input('fecha_entrega');
            $project->entregado_a = $request->input('entregado_a');
            $project->observaciones_admin = $request->input('observacion');
            $project->fecha_recepcion = $request->input('fecha_documento');
            $project->coordinar_retiro = $request->input('coordinar_retiro');
            $project->fecha_envio_doc = $request->input('fecha_envio_doc');
            $project->chasis = $request->input('chasis');
            if ($request->filled('fecha_confirmacion_contacto')) {
                $project->fecha_confirmacion_contacto = $request->input('fecha_confirmacion_contacto');
            }
            $project->fecha_limite_retiro = $request->input('fecha_limite_retiro');
            $project->idResponsable_retiro = $request->input('retira');
            $project->crp_nro = $request->input('crp');
            $project->idLugar_entrega = $request->input('lugar_entregas');
            if ($request->filled('fecha_retiro')) {
                //if ($request->input('fecha_retiro')!=null && $request->input('fecha_retiro') != ''){
                $project->fecha_retiro = $request->input('fecha_retiro');
            }

            $project->tipo_baja = $request->input('tipo_baja');

            if ($request->input('tipo_baja') == 2 &&  $request->input('estado') == '')
                $project->idEstado = 8;
            else
                $project->idEstado = $request->input('estado');

            //
            if ($request->input('estado') == '') {
                $project->idEstado = 4;
            }


            $project->fecha_ingreso = $request->input('fecha_ingreso');
            $project->control = $request->input('control');
            $project->observacion_retiro = $request->input('observacion_retiro');
            $project->company_id = $request->input('company');
            $project->nro_interno = $request->input('nro_interno');
            $project->kilometraje = $request->input('kilometraje');
            $project->gestor = $request->input('gestor');
            $project->motor_en_marcha = $request->input('motor_en_marcha');
            $project->color = $request->input('color');
            $project->no_drnpa = $request->input('no_drnpa') ? $request->input('no_drnpa') : 0;
            $project->piezas_defectuosas = $request->input('piezas_defectuosa');

            $project->tipo = $request->input('tipo') ? $request->input('tipo') : '';
            $project->marca_motor = $request->input('marca_motor') ? $request->input('marca_motor') : '';
            $project->marca_chasis = $request->input('marca_chasis') ? $request->input('marca_chasis') : '';

            $project->tipo_vehiculo = $request->input('tipo_vehiculo');

            ///  $project->titular = $request->input('datos-titular');

            if (strtolower(auth()->user()->role->name) == 'gerencial' || strtolower(auth()->user()->role->name) == 'gerente de operarios' || strtolower(auth()->user()->role->name) == 'operario') {
                $project->observacion_gerente_operario = $request->input('observacion_gerente_operario)');
            }
            //video
       
	   /*if ($request->file('video', null)) {
                $nombre = $this->uploadVideo($request);
                $project->video = $nombre;
            }*/
			
			if ($request->file('video', null)) {
                $nombre = $this->uploadVideo($request);
                $project->video = $nombre;
            }


            $project->save();

            $this->crearCheckPoint($project);

            // grabar historial cambio de estado
            if ($request->input('estado'))
                $this->updateHistorialEstado($car->id, Null, $request->input('estado'));


            $avisarRetiros = $request->input('coordinar_retiro');
            if ($avisarRetiros) {
                $idRol = Role::where('name', 'Retiros')->first()->id;
                Notification::send(User::where('role_id', $idRol)->get(), new RetiroVehiculoUpdated($project));
            }
            /*if (!empty($request->file('imagen'))) {
                $this->uploadImg($request, ['dir' => 'vehiculos', 'idCar' => $project->id]);
            }*/
			
			if (!empty($request->file('imagen'))) {
				$path = public_path('uploads/vehiculos');
				if(!file_exists($path) && !is_dir($path)) mkdir($path, 0755, true);
                $this->uploadImg($request, ['dir' => 'vehiculos', 'idCar' => $project->id]);
            }

            /*if (!empty($request->file('imagen_recepcion'))) {
                $this->uploadImg($request, ['dir' => 'vehiculos', 'idCar' => $project->id]);
            }*/
			
			
			 if (!empty($request->file('imagen_recepcion'))) {
				$path = public_path('uploads/vehiculos');
				if(!file_exists($path) && !is_dir($path)) mkdir($path, 0755, true);
                $this->uploadImg($request, ['dir' => 'vehiculos', 'idCar' => $project->id]);
            }
			
			

            $piezasAusentes = $request->input('piezasAu', false);

            if (!empty($piezasAusentes[0])) {

                foreach ($piezasAusentes as $pieza) {
                    $piezaAu = new Pieza_ausente();
                    $piezaAu->id_car = $project->id;

                    $item = Item::find($pieza);

                    $piezaAu->name = $item->item_name;
                    $piezaAu->save();
                }
            }

            if ($request->input('otraPieza', false)) {
                $piezaAu = new Pieza_ausente();
                $piezaAu->id_car = $project->id;

                $piezaAu->name = $request->input('otraPieza', false);
                $piezaAu->save();

                $item = new Item();
                $item->item_name = $piezaAu->name;
                $item->item_type = 'product';
                $item->company_id = company_id();


                $item->save();
            }

            //$this->creaProductAutosDefecto($project, $piezasAusentes);

            $avisarCargaImg = $request->input('carga_de_imagen');
            if ($avisarCargaImg) {
                Notification::send(User::find($project->idTramitador), new CargaImagenVehiculo($project));
            }
            //create_log('vehiculo', $project->id, _lang('Created Project'));

            //        if($this->is_duplicate_folder($request->input('name'), $request->input('parent_id'))){
            //            if($request->ajax()){
            //                return response()->json(['result'=>'error','message'=>array('error'=> _lang('Folder Name already exists !'))]);
            //            }else{
            //                return back()->withErrors($validator)
            //                    ->withInput();
            //            }
            //        }
            if ($project->company_id == 1) {
                $in = 'PM-';
            } else if ($project->company_id == 2) {
                $in = 'PC-';
            }

            $filemanager = new FileManager();
            $filemanager->name = $in . $project->id;
            $filemanager->is_dir = 'yes';
            $filemanager->parent_id = null;
            $filemanager->company_id = company_id();
            $filemanager->created_by = Auth::user()->id;

            $filemanager->save();


            DB::commit();

            if (!$request->ajax()) {
                if (strpos($path, 'tramitadores/create') !== false) {
                    return redirect()->action([TramitadorController::class, 'seguimiento'], ['id' => $project->id]);
                }

                return redirect()->route('vehiculo.create')->with('success', _lang('Saved Sucessfully'));
            } else {
                return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved Sucessfully'), 'data' => $project, 'table' => '#projects_table']);
            }
        } catch (\Exception $e) {
            DB::rollBack();
            //dd($e);
            if (!$request->ajax()) {
                if (strpos($path, 'tramitadores/create') !== false) {

                    return back()->with('error', _lang('Error al crear el registro'))->withInput();
                }
                return redirect()->route('vehiculo.create')->with('erros', 'Error al crear el registro');
            } else {
                return response()->json(['result' => 'error', 'action' => 'store', 'message' => 'Error al crear el registro', 'data' => $project, 'table' => '#projects_table']);
            }
        }
    }

    public function creaProductAutosDefecto($car, $excepto = [])
    {
        //$items = Item::where('allCar', 1)->get();
        $items = Item::where('activo', "Si")->orderBy('item_name', 'ASC')->get();
        foreach ($items as $item) {
            if (!empty($excepto[0]) &&  in_array($item->id, $excepto)) {
            } else {
                $product = new Product();
                $product->item_id = $item->id;
                $product->car_id = $car->id;
                $product->marca_modelo = $car->idMarca_modelo;
                //$product->product_cost = $request->input('product_cost');
                $product->product_price = 0;
                //$product->product_unit = $request->input('product_unit');
                $product->tax_method = 'exclusive';
                //$product->tax_id = $request->input('tax_id');
                //$product->description = $request->input('description');
                $product->stock = 1;
                $product->nro_interno = $car->id;
                $product->company_id = $car->company_id;
                $product->allCar = 1;

                $product->save();
            }
        }
    }

    /*public function uploadVideo(Request $request)
    {



        $video = $request->file('video', false);
        if (!empty($video[0])) {


            $result = '';
            foreach ($video as  $v) {
                $file = $v;
                // dd($file);
                //obtenemos el nombre del archivo
                $nombre = time() . $file->getClientOriginalName();

                //indicamos que queremos guardar un nuevo archivo en el disco local
                \Storage::disk('vehiculo')->put($nombre,  \File::get($file));
                $result  .= $nombre . ';';
            }

            $nombre = substr($result, 0, -1);
        }


        return $nombre;
    }*/
	
	public function uploadVideo(Request $request)
{
    // Validamos que existan archivos
    $videos = $request->file('video');
    
    if (!$videos || !is_array($videos)) {
        return response()->json(['error' => 'No se subieron videos'], 400);
    }

    $nombresSubidos = [];

    foreach ($videos as $v) {
        // Generamos el nombre único
       // $nombre = time() . '_' . $v->getClientOriginalName();
        
        // Guardamos directamente usando el disco 'vehiculo'
        // Esto es mucho más eficiente en memoria que \File::get()
//        $v->storeAs('/', $nombre, 'vehiculo');
        
  //      $nombresSubidos[] = $nombre;
  
   // Guarda en el disco 'vehiculo' y genera el nombre único
        $path = $v->store('/', 'vehiculo'); 
        // Extrae solo el nombre (ej: "asdf123.mp4") del path completo
        $nombresSubidos[] = basename($path);
  
    }

    // Retornamos los nombres separados por punto y coma
    return implode(';', $nombresSubidos);
}

    public function getVideo($video)
    {

        $file =null;
		if (Storage::disk('vehiculo')->exists($video)) {
			$file = Storage::disk('vehiculo')->get($video);
		}else{
			if (Storage::disk('gcs')->exists("/vehiculos/{$video}")) {
				$file = Storage::disk('gcs')->get("/vehiculos/{$video}");
			}					
		}
        
		if ($file) {
            return $file; //Storage::disk('public')->response("$video");
        }
        //si no se encuentra lanzamos un error 404.
        abort(404);
    }




    public function show_vehiculo_piezas() {}




    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        $company_id = company_id_arr();
        $user_type = Auth::user()->user_type;
        $data = array();

        $data['tipo_baja'] = $this->tipo_baja;
        $data['responsable_entregas'] = $this->responsable_entregas;

        $data['cars'] = Cars::where('cars.id', $id)
            ->whereIn('company_id', $company_id)
            ->with('marca_modelo')
            ->with('aseguradora')
            ->with('provincias')
            ->with('tramitador')
            ->with('lugar_entrega')
            ->with('estado')
            ->first();
        if (!$data['cars']) {
            return back()->with('error', _lang('Sorry, Car not found !'));
        }

        return view('backend.accounting.vehiculo.view', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $datos = $this->datos();

        //$items = Item::all();
        $interno = $this->get_last_interno();
        $datos['interno'] = $interno;
        //$datos['items'] = $items;
        $datos['items'] = Item::where('activo', "Si")->orderBy('item_name', 'ASC')->get();


        $car = Cars::where('id', $id)
            ->with('marca_modelo')
            ->with('marca_modelo.marca')
            ->with('marca_modelo.modelo')

            ->with('aseguradora')
            ->with('provincias')
            ->with('tramitador')
            ->with('lugar_entrega')
            ->with('estado')
            //->where('company_id', company_id())
            ->first();
        //dd($car);
			 //$this->crearCheckPoint($car);
			 //dd();
			
			
        if ($car->company_id != auth()->user()->company_id && strtolower(auth()->user()->role->name) == 'actualización de estados') {
            if ($request->ajax()) {
                return new Response('<h5 class="text-center red">' . _lang('No puede hacer cambios de otras compañías !') . '</h5>');
            } else {
                return back()->with('error', _lang('No puede hacer cambios de otras compañías !'));
            }
        }

        $comp = Company::all();
        $datos['cias'] = $comp;
        if (!$request->ajax()) {
            return view('backend.accounting.vehiculo.edit', compact('car', 'id'))->with($datos);
        } else {
            // if (strtolower(auth()->user()->role->name) == 'tramitador') {
            //     return view('backend.accounting.vehiculo.modal.edit-tramitadores', compact('car', 'id'))->with($datos);
            // }
            return view('backend.accounting.vehiculo.modal.edit', compact('car', 'id'))->with($datos);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
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

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('vehiculo.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        // if (strtolower(auth()->user()->role->name) == 'tramitador') {

        //     $project = Cars::where('id', $id)
        //         //->where('company_id', $request->input('company'))
        //         ->first();
        //     //dd(route('vehiculo.edit', 2));
        //     $project->company_id = $request->input('company');
        //     $project->siniestro = $request->input('siniestro');
        //     $project->dominio = $request->input('dominio');
        //     $project->idMarca_modelo = $request->input('marca_modelo');
        //     $project->save();

        //     $avisarTramit = $request->input('avisar_tramitador', false);


        // }
        // else 
        $project = Cars::where('id', $id)->first();




        if ($request->filled('fecha_pago_cia')) {

            $regitro_attributes = [
                'fecha_pago_cia' =>  Carbon::parse($request->input('fecha_pago_cia'))
            ];
            $project->properties = $regitro_attributes;
        }

        $estadoInicial = $project->idEstado;

        //      // Verificar si 'dominio' ya existe
        //      $dominio = $request->input('dominio');
        //      if (Cars::where('dominio', $dominio)->where('id','!=',$id)->exists()) {

        //        if (!$request->ajax()) {
        //            return redirect()->route('vehiculo.edit', $id)->with('erros', 'Nro de dominio ya existe')->withInput();
        //        } else {
        //            return response()->json(['result' => 'error', 'action' => 'update', 'message' =>'Nro de dominio ya existe', 'data' => '', 'table' => '#projects_table']);

        //        }
        //    }


        if (strtolower(auth()->user()->role->name) == 'retiros') {
            //dd(route('vehiculo.edit', 2));
            // $project->fecha_limite_retiro = $request->input('fecha_limite_retiro');
            $project->idResponsable_retiro = $request->input('retira');
            $project->idLugar_entrega = $request->input('lugar_entregas');
            //   $project->fecha_ingreso = $request->input('fecha_ingreso');
            $project->observacion_retiro = $request->input('observacion_retiro');
            //   $project->fecha_confirmacion_contacto = $request->input('fecha_confirmacion');
            $project->lugar_retiro = $request->input('lugar_retiro');

            $project->localidad = $request->input('localidad');
            $project->provincia = $request->input('provincia');
            // dd($request->filled('fecha_retiro'));
            if ($request->filled('fecha_retiro')) {

                $currentFechaRetiro = Carbon::parse($project->fecha_retiro);
                $newFechaRetiro = Carbon::parse($request->input('fecha_retiro'));
                $project->fecha_retiro = $request->input('fecha_retiro');
                //  dd( ($currentFechaRetiro->toDateString() !== $newFechaRetiro->toDateString()) );
                if ($currentFechaRetiro->toDateString() !== $newFechaRetiro->toDateString()) {
                    // if (!$currentFechaRetiro->eq($newFechaRetiro)) {
                    //$project->fecha_limite_retiro = $newFechaLimiteRetiro;

                    if ($project->idTramitador)
                        Notification::send(User::find($project->idTramitador), new RetiroVehiculoUpdated($project));
                }
            }


            $project->save();


            // $avisarTramit = $request->input('avisar_tramitador', false);

            // if ($avisarTramit) {
            // }

            //eliminar las imagenes seleccionadas
            $arrImgDelete = $request->input('imgDelete', false);
            if ($arrImgDelete && isset($arrImgDelete[0])) {
                foreach ($arrImgDelete as $imgdelete) {
                    $img = Imagen::where('id', $imgdelete)->first();

                    unlink(public_path('uploads/vehiculos/' . $img->img));
                    Imagen::where('id', $imgdelete)->delete();
                }
            }

            if (!empty($request->file('imagen'))) {
                $this->uploadImg($request, ['dir' => 'vehiculos', 'idCar' => $project->id]);
            }
        } else if (strtolower(auth()->user()->role->name) == 'receptor') {
            // $project = Cars::where('id', $id)->first();

            //            $project->fecha_recepcion = $request->input('fecha_documento');
            $project->fecha_ingreso = $request->input('fecha_ingreso');
            $project->kilometraje = $request->input('kilometraje');
            $project->motor_en_marcha = $request->input('motor_en_marcha');
            $project->fecha_entrega_asegurado_cia = $request->input('fecha_entrega_asegurado_cia');
            $project->idLugar_entrega = $request->input('lugar_entregas');
            $project->idEstado = $request->input('estado');

            $video = $request->file('video', false);
            if (!empty($video[0])) {
                $videos = explode(';', $project->video);
                foreach ($videos as $v) {
                    $this->deleteVideo($v);
                }

                $nombre = $this->uploadVideo($request);
                $project->video = $nombre;
            }
            $project->piezas_defectuosas = trim($request->input('piezas_defectuosa'));

            $project->save();


            $arrImgDelete = $request->input('imgDelete', false);
            if ($arrImgDelete && isset($arrImgDelete[0])) {
                foreach ($arrImgDelete as $imgdelete) {
                    $img = Imagen::where('id', $imgdelete)->first();

                    unlink(public_path('uploads/vehiculos/' . $img->img));
                    Imagen::where('id', $imgdelete)->delete();
                }
            }

            if (!empty($request->file('imagen'))) {
                $this->uploadImg($request, ['dir' => 'vehiculos', 'idCar' => $project->id]);
            }


            // //eliminar las imagenes seleccionadas
            // $arrImgDelete = $request->input('imgDeleteRecepcion', false);
            // if ($arrImgDelete && isset($arrImgDelete[0])) {
            //     foreach ($arrImgDelete as $imgdelete) {
            //         $img = Imagen::where('id', $imgdelete)->first();

            //         unlink(public_path('uploads/vehiculos/' . $img->img));
            //         Imagen::where('id', $imgdelete)->delete();
            //     }
            // }

            // if (!empty($request->file('imagen_recepcion'))) {
            //     $this->uploadImg($request, ['dir' => 'vehiculos', 'idCar' => $project->id]);
            // }

            $piezasAusentes = $request->input('piezasAu', false);
            Pieza_ausente::where('id_car', $id)->delete();
            if (!empty($piezasAusentes[0])) {

                foreach ($piezasAusentes as $pieza) {
                    if ($pieza) {
                        $piezaAu = new Pieza_ausente();
                        $piezaAu->id_car = $project->id;

                        $item = Item::find($pieza);

                        $piezaAu->name = $item->item_name;
                        $piezaAu->save();
                    }
                }
            }

            if ($request->input('otraPieza', false)) {
                $piezaAu = new Pieza_ausente();
                $piezaAu->id_car = $project->id;

                $piezaAu->name = $request->input('otraPieza', false);
                $piezaAu->save();

                $item = new Item();
                $item->item_name = $piezaAu->name;
                $item->item_type = 'product';
                $item->company_id = company_id();


                $item->save();
            }
        } else {
            $company_id = company_id();

            $currentFechaRetiro = Carbon::parse($project->fecha_retiro);
            $currentFechaLimiteRetiro = Carbon::parse($project->fecha_limite_retiro);

            // $project = Cars::where('id', $id)->first();

            $project->fecha_asignacion = $request->input('fecha_asignacion');
            $project->forma = $request->input('forma');
            $project->idTramitador = $request->input('idTramitador');
            $project->idAseguradora = $request->input('idAseguradora');
            $project->tramitador_compania = $request->input('tramitador_compania');
            $project->siniestro = $request->input('siniestro');
            $project->dominio = $request->input('dominio');
            $project->idMarca_modelo = $request->input('marca_modelo');
            $project->motor_nro = $request->input('motor');
            $project->tipo_baja = $request->input('tipo_baja');
            $project->asegurado = $request->input('asegurado');
            $project->contacto = $request->input('contacto');
            $project->lugar_retiro = $request->input('lugar_retiro');
            $project->localidad = $request->input('localidad');
            $project->provincia = $request->input('provincia');
            $project->fecha_entrega_asegurado_cia = $request->input('fecha_entrega');
            $project->entregado_a = $request->input('entregado_a');
            $project->observaciones_admin = $request->input('observacion');
            $project->fecha_recepcion = $request->input('fecha_documento');
            $project->coordinar_retiro = $request->input('coordinar_retiro');
            $project->fecha_envio_doc = $request->input('fecha_envio_doc');
            $project->chasis = $request->input('chasis');
            $project->fecha_confirmacion_contacto = $request->input('fecha_confirmacion_contacto');
            $project->fecha_limite_retiro = $request->input('fecha_limite_retiro');
            $project->idResponsable_retiro = $request->input('retira');
            $project->crp_nro = $request->input('crp');
            $project->idLugar_entrega = $request->input('lugar_entregas');
            // $project->fecha_retiro = $request->input('fecha_retiro');
            $project->idEstado = $request->input('estado');
            $project->fecha_ingreso = $request->input('fecha_ingreso');
            $project->control = $request->input('control');
            $project->observacion_retiro = $request->input('observacion_retiro');
            $project->company_id = $request->input('company');
            $project->nro_interno = $request->input('nro_interno');
            $project->kilometraje = $request->input('kilometraje');
            $project->gestor = $request->input('gestor');
            $project->motor_en_marcha = $request->input('motor_en_marcha');
            $project->color = $request->input('color');
            $project->no_drnpa = $request->input('no_drnpa') ? $request->input('no_drnpa') : 0;
            $project->piezas_defectuosas = trim($request->input('piezas_defectuosa'));
            //   $project->titular = $request->input('datos-titular');
            $project->fecha_envio_drnpa = $request->input('fecha_envio_drnpa');
            $project->fecha_finalizacion = $request->input('fecha_finalizacion');
            $project->fecha_recibo_carpeta = $request->input('fecha_recibo_carpeta');
            $project->fecha_envio_mail_drnpa = $request->input('fecha_envio_mail_drnpa');


            if ($request->input('tipo'))
                $project->tipo = $request->input('tipo');

            if ($request->input('marca_motor'))
                $project->marca_motor = $request->input('marca_motor');

            if ($request->input('marca_chasis'))
                $project->marca_chasis = $request->input('marca_chasis');

            if ($request->input('tipo_vehiculo'))
                $project->tipo_vehiculo = $request->input('tipo_vehiculo');


            $avisarRetiros = $request->input('coordinar_retiro');
            // if ($avisarRetiros) {
            //     $idRol = Role::where('name', 'Retiros')->first()->id;
            //     Notification::send(User::where('role_id', $idRol)->get(), new RetiroVehiculoUpdated($project));
            // }

            $project->coordinar_retiro = $request->input('coordinar_retiro');

            $newFechaLimiteRetiro = Carbon::parse($request->input('fecha_limite_retiro'));

            if ($currentFechaLimiteRetiro->toDateString() !== $newFechaLimiteRetiro->toDateString()) {
                //  if (!$currentFechaLimiteRetiro->eq($newFechaLimiteRetiro)) {
                $project->fecha_limite_retiro = $newFechaLimiteRetiro;
                $avisarRetiros = $request->input('coordinar_retiro');
                if ($avisarRetiros) {
                    $idRol = Role::where('name', 'Retiros')->first()->id;
                    Notification::send(User::where('role_id', $idRol)->get(), new RetiroVehiculoUpdated($project));
                }
            }

            if ($request->filled('fecha_retiro')) {
                $newFechaRetiro = Carbon::parse($request->input('fecha_retiro'));
                $project->fecha_retiro = $request->input('fecha_retiro');
                if ($currentFechaRetiro->toDateString() !== $newFechaRetiro->toDateString()) {
                    //  if (!$currentFechaRetiro->eq($newFechaRetiro)) {
                    //$project->fecha_limite_retiro = $newFechaLimiteRetiro;
                    Notification::send(User::find($project->idTramitador), new RetiroVehiculoUpdated($project));
                }
            }

            //video
            //eliminar video anterior y subir el nuevo
            $video = $request->file('video', false);
            if (!empty($video[0])) {

                $videos = explode(';', $project->video);
                foreach ($videos as $v) {
                    $this->deleteVideo($v);
                }

                $nombre = $this->uploadVideo($request);


                $project->video = $nombre;
            }

            if (strtolower(auth()->user()->role->name) == 'gerencial' || strtolower(auth()->user()->role->name) == 'gerente de operarios' || strtolower(auth()->user()->role->name) == 'operario') {
                // dd($request->input('observacion_gerente_operario'));
                $project->observacion_gerente_operario = $request->input('observacion_gerente_operario');
            }



            $project->save();

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


            //eliminar las imagenes seleccionadas
            $arrImgDelete = $request->input('imgDelete', false);
            if ($arrImgDelete && isset($arrImgDelete[0])) {
                foreach ($arrImgDelete as $imgdelete) {
                    $img = Imagen::where('id', $imgdelete)->first();

                    $filePath = public_path('uploads/vehiculos/' . $img->img);
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }

                    // unlink(public_path('uploads/vehiculos/' . $img->img));
                    Imagen::where('id', $imgdelete)->delete();
                }
            }

            if (!empty($request->file('imagen'))) {
                $this->uploadImg($request, ['dir' => 'vehiculos', 'idCar' => $project->id]);
            }

            $piezasAusentes = $request->input('piezasAu', false);
            Pieza_ausente::where('id_car', $id)->delete();
            if (!empty($piezasAusentes[0])) {

                foreach ($piezasAusentes as $pieza) {
                    if ($pieza) {
                        $piezaAu = new Pieza_ausente();
                        $piezaAu->id_car = $project->id;

                        $item = Item::find($pieza);

                        $piezaAu->name = $item->item_name;
                        $piezaAu->save();
                    }
                }
            }

            if ($request->input('otraPieza', false)) {
                $piezaAu = new Pieza_ausente();
                $piezaAu->id_car = $project->id;

                $piezaAu->name = $request->input('otraPieza', false);
                $piezaAu->save();

                $item = new Item();
                $item->item_name = $piezaAu->name;
                $item->item_type = 'product';
                $item->company_id = company_id();


                $item->save();
            }
        }
        $avisarCargaImg = $request->input('carga_de_imagen');
        if ($avisarCargaImg) {
            Notification::send(User::find($project->idTramitador), new CargaImagenVehiculo($project));
        }

        $estadoFinal = $project->idEstado;

        if ($estadoInicial != $estadoFinal) {

            // grabar historial cambio de estado
            $this->updateHistorialEstado($project->id, $estadoInicial, $estadoFinal);

            $this->notificarCambioEstado($project->id, $estadoInicial, $estadoFinal);
        }

        $this->updateCheckPoint($project);


        //modificar la marca y modelo de los productos del auto

        Product::where('car_id', $project->id)->update(['marca_modelo' => $project->idMarca_modelo]);

        if (!$request->ajax()) {
            return redirect()->route('vehiculo.index')->with('success', _lang('Updated Sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated Sucessfully'), 'data' => $project, 'table' => '#vehiculo_table']);
        }
    }

    private function get_last_interno()
    {
        $interno = Cars::select('nro_interno')->orderBy('nro_interno', 'desc')->first();

        return $interno->nro_interno ?? null;
    }

    public function deleteVideo($video)
    {
        Storage::disk('vehiculo')->delete($video);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function delete_project_member(Request $request, $member_id)
    {
        DB::beginTransaction();
        $project_member = ProjectMember::join('projects', 'projects.id', 'project_members.project_id')
            ->where('project_members.user_id', $member_id)
            ->where('company_id', company_id())
            ->select('project_members.*')
            ->first();

        create_log('projects', $project_member->project_id, _lang('Removed') . ' ' . $project_member->user->name . ' ' . _lang('from Project'));

        $project_member->delete();
        DB::commit();

        if (!$request->ajax()) {
            return back()->with('success', _lang('Removed Sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'delete', 'message' => _lang('Member Removed'), 'id' => $member_id, 'table' => '#project_members_table']);
        }
    }


    /* Get Logs Data*/
    public function get_logs_data($project_id)
    {

        $logs = \App\ActivityLog::with('created_by')
            ->select('activity_logs.*')
            ->where("activity_logs.company_id", company_id())
            ->where('related_to', 'projects')
            ->where('related_id', $project_id)
            ->orderBy("activity_logs.id", "desc")
            ->get();

        echo json_encode($logs);
    }

    /**
     * Store File to Project.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function upload_file(Request $request)
    {

        $max_size = get_option('file_manager_max_upload_size', 2) * 1024;
        $supported_file_types = get_option('file_manager_file_type_supported', 'png,jpg,jpeg');

        $validator = Validator::make($request->all(), [
            'related_id' => 'required',
            'file' => "required|file|max:$max_size|mimes:$supported_file_types",
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return back()->withErrors($validator)
                    ->withInput();
            }
        }

        $file_path = '';
        if ($request->hasfile('file')) {
            $file = $request->file('file');
            $file_path = time() . $file->getClientOriginalName();
            $file->move(public_path() . "/uploads/project_files/", $file_path);
        }

        $projectfile = new \App\ProjectFile();
        $projectfile->related_to = 'projects';
        $projectfile->related_id = $request->input('related_id');
        $projectfile->file = $file_path;
        $projectfile->user_id = Auth::id();
        $projectfile->company_id = company_id();

        $projectfile->save();

        create_log('projects', $projectfile->related_id, _lang('Uploaded File'));

        //Prefix output
        $projectfile->file = '<a href="' . url('projects/download_file/' . $projectfile->file) . '">' . $projectfile->file . '</a>';
        $projectfile->user_id = '<a href="' . action('StaffController@show', $projectfile->user->id) . '" data-title="' . _lang('View Staf Information') . '"class="ajax-modal-2">' . $projectfile->user->name . '</a>';
        $projectfile->remove = '<a class="ajax-get-remove" href="' . url('projects/delete_file/' . $projectfile->id) . '">' . _lang('Remove') . '</a>';

        if (!$request->ajax()) {
            return back()->with('success', _lang('File Uploaded Sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('File Uploaded Sucessfully'), 'data' => $projectfile, 'table' => '#files_table']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function delete_file(Request $request, $id)
    {
        if (Auth::user()->user_type == 'admin') {
            $projectfile = \App\ProjectFile::where($id)
                ->where('company_id', $company_id());
            unlink(public_path('uploads/project_files/' . $projectfile->file));
            $projectfile->delete();

            create_log('projects', $id, _lang('File Removed'));
        }

        if (Auth::user()->user_type != 'admin') {
            $projectfile = \App\ProjectFile::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
            if (!$projectfile) {
                if (!$request->ajax()) {
                    return back()->with('error', _lang('Sorry only admin or creator can remove this file !'));
                } else {
                    return response()->json(['result' => 'error', 'message' => _lang('Sorry only admin or creator can remove this file !')]);
                }
            }
            unlink(public_path('uploads/project_files/' . $projectfile->file));
            $projectfile->delete();

            create_log('projects', $id, _lang('File Removed'));
        }

        if (!$request->ajax()) {
            return back()->with('success', _lang('Removed Sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'delete', 'message' => _lang('Removed Sucessfully'), 'id' => $id, 'table' => '#files_table']);
        }
    }

    public function download_file(Request $request, $file)
    {
        $file = 'public/uploads/project_files/' . $file;
        return response()->download($file);
    }

    /**
     * Store note.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function create_note(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'related_id' => 'required',
            'note' => 'required',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->route('notes.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $note = new \App\Note();
        $note->related_to = 'projects';
        $note->related_id = $request->input('related_id');
        $note->note = $request->input('note');
        $note->user_id = Auth::id();
        $note->company_id = company_id();

        $note->save();

        create_log('projects', $note->related_id, _lang('Added Note'));

        //Prefix Output
        $note->created = '<small>' . $note->user->name . '(' . $note->created_at . ')<br>' . $note->note . '</small>';
        $note->action = '<a href="' . url('projects/delete_note/' . $note->id) . '" class="ajax-get-remove"><i class="far fa-trash-alt text-danger"></i></a>';

        if (!$request->ajax()) {
            return back()->with('success', _lang('Saved Sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved Sucessfully'), 'data' => $note, 'table' => '#notes_table']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function delete_note(Request $request, $id)
    {
        if (Auth::user()->user_type == 'admin') {
            $note = \App\Note::where('id', $id)
                ->where('company_id', company_id());
            $note->delete();
            create_log('projects', $id, _lang('Removed Note'));
        }

        if (Auth::user()->user_type != 'admin') {
            $note = \App\Note::where('id', $id)
                ->where('user_id', Auth::id())
                ->first();
            if (!$note) {
                if (!$request->ajax()) {
                    return back()->with('error', _lang('Sorry only admin or creator can remove this file !'));
                } else {
                    return response()->json(['result' => 'error', 'message' => _lang('Sorry only admin or creator can remove this file !')]);
                }
            }
            $note->delete();
            create_log('projects', $id, _lang('Removed Note'));
        }

        if (!$request->ajax()) {
            return back()->with('success', _lang('Removed Sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'delete', 'message' => _lang('Removed Sucessfully'), 'id' => $id, 'table' => '#notes_table']);
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {


        $company_id = company_id_arr();
        $project = Cars::where('id', $id)->whereIn('company_id', $company_id)->first();
        if ($project->video) {
            //eliminar primero el video luego eliminar el registro
            $videos = explode(';', $project->video);
            foreach ($videos as $v) {
                $this->deleteVideo($v);
            }
        }


        $project = Cars::where('id', $id)->whereIn('company_id', $company_id);
        $project->delete();


        return redirect()->route('vehiculo.index')->with('success', _lang('Deleted Sucessfully'));
    }

    public function companyByCar($idCar)
    {
        $company = Cars::find($idCar);

        $company = ['company' => $company->company_id];

        return response()->json($company);
    }

    public function getMarcaModeloByCar($idCar)
    {
        $marca_modelo = Cars::where('id', $idCar)->with('marca_modelo')->first();
        //dd($idCar);
        $marca_modelo = ['marca_modelo' => $marca_modelo->marca_modelo];

        return response()->json($marca_modelo);
    }

	
	  public function crearCheckPoint($car)
    {
		
	try {
			$now = Carbon::now()->toDateTimeString();
			$userId = Auth::id() ?? 1; 
			$ids = "1,2,5,10,11,12,13,14";
			//$idsArray = explode(',', $ids);
			DB::statement("
				INSERT INTO vehiculos_checkpoints (
					vehiculo_id, 
					checkpoint_id, 
					start_date, 
					end_date, 
					observaciones, 
					user_id, 
					status, 
					status_date, 
					created_at, 
					updated_at
				)
				SELECT 
					:vehiculo_id AS vehiculo_id,
					c.id AS checkpoint_id,
					NULL AS start_date,
					NULL AS end_date,
					'' AS observaciones,
					:user_id AS user_id,
					'pendiente' AS status,
					:status_date AS status_date,
					:created_at AS created_at,
					:updated_at AS updated_at
				FROM checkpoints c
				WHERE c.id in ($ids)
			", [
				'vehiculo_id' => $car->id,
				'user_id'     => $userId,
				'status_date' => $now,
				'created_at'  => $now,
				'updated_at'  => $now
			]);
			

			Log::info("¡Inicialización masiva de checkpoints en 'pendiente' completada!");
				} catch (\Exception $e) {
					//dd($e->getMessage());
					Log::error('Error updating car status for car ID ' . $car->id . ': ' . $e->getMessage());
				}
			}
	
	
 /*   public function crearCheckPoint($car)
    {
        // Fetch all checkpoints
        $checkpoints = Checkpoint::all();

        // Data to insert
        $data = [];

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
                        'user_id' => Auth::id(),
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ]
                );

                switch ($checkpoint->id) {
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
                        if ($car->fecha_limite_retiro != null && $car->coordinar_retiro != null && $car->fecha_retiro != null) {
                            $checkPointVehiculo->status = 'completado';
                        } elseif ($car->fecha_limite_retiro == null && $car->coordinar_retiro == null && $car->fecha_retiro == null) {
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
                        if ($car->fecha_envio_drnpa != null) {
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
                ->where('orden', '<', 999)
                ->max('orden');

            if ($max_tramite >= 1 && $max_tramite <= 3) {
                $status = 'En Proceso';
            } elseif ($max_tramite >= 4 && $max_tramite <= 10) {
                $status = 'En Gestoria';
            } elseif ($max_tramite >= 11 && $max_tramite != 999) {
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
*/
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
                        if ($car->fecha_limite_retiro != null && $car->fecha_retiro != null) {
                            $checkPointVehiculo->status = 'completado';
                        } elseif ($car->fecha_limite_retiro == null && $car->fecha_retiro == null) {
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
                ->where('orden', '<', 999)
                ->max('orden');

            if ($max_tramite >= 1 && $max_tramite <= 3) {
                $status = 'En Proceso';
            } elseif ($max_tramite >= 4 && $max_tramite <= 10) {
                $status = 'En Gestoria';
            } elseif ($max_tramite >= 11 && $max_tramite != 999) {
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

    public function expense_get_table_data(Request $request)
    {

        $currency = currency();

        $transactions = Transaction::with("account")->with("expense_type")
            ->with("payment_method")
            ->with("payment_method")
            ->with("tipo_comprobante")
            ->select('transactions.*')
            // ->where("transactions.company_id", company_id())
            ->where("transactions.dr_cr", "dr")
            ->orderBy("transactions.id", "desc");
        if (isset($request->idCar)) {
            $transactions->whereHas('pagos_car', function ($strg) use ($request) {
                $strg->where('id_car', $request->idCar);
            });
        }
        return Datatables::eloquent($transactions)
            ->filterColumn('trans_date', function ($query, $keyword) {
                //fecha en formato Y-m-d
                $fecha = date('Y-m-d', strtotime($keyword));
                // dd($fecha);
                $query->where('trans_date', 'like', '%' . $fecha . '%');
            })



            ->filterColumn('payer.name', function ($query, $keyword) {

                $query->whereHas('payer', function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%');
                });
            })
            ->filterColumn('status', function ($query, $keyword) {
                if ($keyword == 2) {
                    //resuelto
                    $query->where('status', 1);
                } else {
                    //pendiente
                    $query->where('status', 0)->orwherenull('status');
                }
            })
            ->filterColumn('pagos_car', function ($query, $keyword) {

                $query->whereHas('pagos_car', function ($q) use ($keyword) {
                    $q->where('id_car', 'like', '%' . $keyword . '%');
                });
            })
            ->filterColumn('payment_priority', function ($query, $keyword) {


                if ($keyword == '-1') {
                    $query->whereNull('payment_priority');
                } else {
                    $query->where('payment_priority', $keyword);
                }
            })
            ->editColumn('trans_date', function ($trans) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return date($date_format, strtotime($trans->trans_date));
            })

            ->editColumn('trans_date', function ($trans) {
                $date_format = get_company_option('date_format', 'Y-m-d');
                return date($date_format, strtotime($trans->trans_date));
            })
            ->editColumn('amount', function ($trans) use ($currency) {
                $acc_currency = currency($trans->account->account_currency ?? '');
                if ($acc_currency != $currency) {
                    return "<span class='float-right'>" . decimalPlace($trans->amount, currency($trans->account->account_currency ?? '')) . "</span><br>
										<span class='float-right'><b>" . decimalPlace($trans->base_amount, $currency) . "</b></span>";
                } else {
                    return "<span class='float-right'>" . decimalPlace($trans->amount, currency($trans->account->account_currency ?? '')) . "</span>";
                }
            })
            ->editColumn('payee.contact_name', function ($trans) {
                return isset($trans->payee->contact_name) ? $trans->payee->contact_name : '';
            })
            ->editColumn('pagos_car', function ($trans) {
                return $trans->pagos_car->id_car ?? '';
            })
            ->editColumn('expense_type.name', function ($trans) {
                return isset($trans->expense_type->name) ? $trans->expense_type->name : _lang('Transfer');
            })
            ->editColumn('payer.name', function ($trans) {
                return isset($trans->payer->name) ? $trans->payer->name : '';
            })
            ->editColumn('tipo_comprobante.descripcion', function ($trans) {
                return isset($trans->tipo_comprobante->descripcion) ? $trans->tipo_comprobante->descripcion : '';
            })
            ->editColumn('account.account_title', function ($trans) {
                return $trans->account->account_title;
            })

            ->editColumn('payment_method.name', function ($trans) {
                return $trans->payment_method->name;
            })

            ->editColumn('tasa', function ($trans) {
                return $trans->tasa;
            })
            ->editColumn('status', function ($trans) {
                return $trans->status == 1 ? 'Resuelto' : 'pendiente';
            })
            ->editColumn('payment_priority', function ($trans) {
                return $trans->payment_priority == null ? 'Normal' : ucwords(str_replace('_', ' ', $trans->payment_priority));
            })
            ->addColumn('action', function ($trans) {

                $updateButton = '';
                if ($trans->status == 1 && ($trans->pagos_car->id_car ?? '') != '') { //evita editar el gasto de vehiculo resuelto
                    $updateButton = '<a href="#" data-title="' . _lang('Update Expense') . '" class="btn btn-warning btn-xs ajax-modal disabled"><i class="ti-pencil"></i></a>&nbsp;';
                } else {
                    $updateButton = '<a href="' . action('VehiculoController@edit_expense', $trans['id']) . '" data-title="' . _lang('Update Expense') . '" class="btn btn-warning btn-xs ajax-modal"><i class="ti-pencil"></i></a>&nbsp;';
                }

                if (isset($trans->expense_type->name) || true) {
                    return '<form action="' . action('ExpenseController@destroy', $trans['id']) . '" class="text-center" method="post">'
                        //. '<a href="' . action('ExpenseController@edit', $trans['id']) . '" data-title="' . _lang('Update Expense') . '" class="btn btn-warning btn-xs ajax-modal"><i class="ti-pencil"></i></a>&nbsp;'
                        . $updateButton
                        . '<a href="' . action('ExpenseController@show', $trans['id']) . '" data-title="' . _lang('View Expense') . '" class="btn btn-primary btn-xs ajax-modal"><i class="ti-eye"></i></a>&nbsp;'
                        . csrf_field()
                        . '<input name="_method" type="hidden" value="DELETE">'
                        . '<button class="btn btn-danger btn-xs btn-remove" type="submit"><i class="ti-eraser"></i></button>'
                        . '</form>';
                } else {
                    return '<form action="' . action('ExpenseController@destroy', $trans['id']) . '" class="text-center" method="post">'
                        . '<a href="#" data-title="' . _lang('Update Expense') . '" class="btn btn-warning btn-xs ajax-modal disabled"><i class="ti-pencil"></i></a>&nbsp;'
                        . '<a href="' . action('ExpenseController@show', $trans['id']) . '" data-title="' . _lang('View Expense') . '" class="btn btn-primary btn-xs ajax-modal"><i class="ti-eye"></i></a>&nbsp;'
                        . csrf_field()
                        . '<input name="_method" type="hidden" value="DELETE">'
                        . '<button class="btn btn-danger btn-xs btn-remove" type="submit"><i class="ti-eraser"></i></button>'
                        . '</form>';
                }
            })
            ->setRowId(function ($trans) {
                return "row_" . $trans->id;
            })
            ->rawColumns(['status', 'action', 'amount'])
            ->make(true);
    }

    public function edit_expense(Request $request, $id)
    {
        $control = $this->control;
        $transaction = Transaction::where("id", $id)
            ->first(); //->where("company_id", company_id())
        // if (!$request->ajax()) {
        // 	return view('backend.accounting.vehiculo.expense.edit', compact('transaction', 'id'));
        // } else {
        $dominio = $transaction->pagos_car->vehiculo->dominio;
        $company = $transaction->pagos_car->vehiculo->company_id;
        $card_id = $transaction->pagos_car->vehiculo->id;
        $in = '';
        if ($company == 1) {
            $in .= 'PM-';
        } else if ($company == 2) {
            $in .= 'PC-';
        }
        $interno = $in . $card_id;

        return view('backend.accounting.vehiculo.modal.edit_movimiento', compact('transaction', 'id', 'control', 'dominio', 'interno'));
        // }
    }

    public function updateExpense(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'trans_date' => 'required',
            //'account_id' => 'required',
            'chart_id' => 'required',
            'amount' => 'required|numeric',
            //'payment_method_id' => 'required',
            //'reference' => 'nullable|max:50',
            //'attachment' => 'nullable|mimes:jpeg,png,jpg,doc,pdf,docx,zip',
        ]);

        if ($validator->fails()) {
            if ($request->ajax()) {
                return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
            } else {
                return redirect()->back()
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        $attachment = "";
        if ($request->hasfile('attachment')) {
            $file = $request->file('attachment');
            $attachment = time() . $file->getClientOriginalName();
            $file->move(public_path() . "/uploads/transactions/", $attachment);
        }

        $transaction = Transaction::where("id", $id)->first(); //->where("company_id", company_id())
        $previous_amount = $transaction->amount;
        $transaction->trans_date = $request->input('trans_date');
        //$transaction->account_id = $request->input('account_id');
        $transaction->chart_id = $request->input('chart_id');
        // $transaction->type = 'expense';
        // $transaction->dr_cr = 'dr';
        $transaction->amount = $request->input('amount');
        //$transaction->imputar_a = $request->input('imputar_a');
        $transaction->payer_payee_id = $request->input('payer_payee_id');
        $transaction->razon_social = $request->input('razon_social');


        if (($previous_amount != $transaction->amount) || $transaction->base_amount == '') {
            $transaction->base_amount = convert_currency($transaction->account->account_currency, base_currency(), $transaction->amount);
        }

        // if ($request->input('related_to') == '') {
        // 	// $transaction->payer_payee_id = null;
        // 	// $transaction->project_id = null;
        // } else if ($request->input('related_to') == 'contacts') {
        // 	// $transaction->payer_payee_id = $request->input('payer_payee_id');
        // } else if ($request->input('related_to') == 'projects') {
        // 	// $transaction->project_id = $request->input('project_id');
        // }

        //$transaction->payment_method_id = $request->input('payment_method_id');
        //$transaction->reference = $request->input('reference');
        //$transaction->note = $request->input('note');
        // if ($request->hasfile('attachment')) {
        // 	$transaction->attachment = $attachment;
        // }
        // if ($transaction->imputar_a == 'distribuir') {
        // 	$company = Company::where('business_name', 'A dividir')->first();
        // 	$transaction->company_id = $company->id;
        // }

        // if ($transaction->imputar_a == 'triunvirato') {
        // 	$company = Company::where('business_name', 'Triunvirato')->first();
        // 	$transaction->company_id = $company->id;
        // }

        // if ($transaction->imputar_a == 'pentacar') {
        // 	$company = Company::where('business_name', 'Pentacar')->first();
        // 	$transaction->company_id = $company->id;
        // }
        // if ($transaction->imputar_a == 'paternal') {
        // 	$company = Company::where('business_name', 'Paternal')->first();
        // 	$transaction->company_id = $company->id;
        // }

        $old_status = $transaction->status;

        // $transaction->status = $request->input('status', null);
        // $transaction->usd = $request->input('usd');
        // $transaction->tasa = $request->input('tasa');


        $old_payment_priority = $transaction->payment_priority;


        $transaction->payment_priority = $request->has('payment_priority') && $request->input('payment_priority') !== '' ? $request->input('payment_priority') : null;

        $transaction->save();

        if (($transaction->pagos_car->id_car ?? '') != '') { // si la orden esta asociada a un vehiculo

            $car = Cars::where('id',  $transaction->pagos_car->id_car)->first();

            if ($old_status != $transaction->status) {  // cambio de estado

                //$tramitador= User::where('id',$transaction->payer_payee_id)->get();
                $tramitador = User::where('id', $car->idTramitador)->get();

                if ($tramitador) {
                    Notification::send($tramitador, new PagosCarChangeStatus($transaction));
                }
            }

            if ($old_payment_priority != $transaction->payment_priority) {  // cambio de prioridad
                $company_id_car = $car->company_id;
                $cajeros = User::wherehas('role', function ($q) {
                    $q->where('name', 'Cajera');
                })->where('company_id', $company_id_car)->get();


                Notification::send($cajeros, new PagosCarChangePriority($transaction));
            }
        };

        // //Set Related Data
        // $date_format = get_company_option('date_format', 'Y-m-d');
        // $transaction->trans_date = date("$date_format", strtotime($transaction->trans_date));
        // $transaction->amount = decimalPlace($transaction->amount, currency());
        // $transaction->account_id = $transaction->account->account_title;
        // $transaction->chart_id = $transaction->expense_type->name;
        // $transaction->payer_payee_id = isset($transaction->payee->contact_name) ? $transaction->payee->contact_name : '';
        // $transaction->payment_method_id = $transaction->payment_method->name;

        if (!$request->ajax()) {
            return redirect('expense')->with('success', _lang('Updated Sucessfully'));
        } else {
            return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated Sucessfully'), 'data' => $transaction]);
        }
    }

    public function updateHistorialEstado($car_id, $state_ant, $state_new)
    {
        $historial_state = new HistorialStateCar;
        $historial_state->fecha = now();
        $historial_state->idCar = $car_id;
        $historial_state->id_current_state = $state_ant;
        $historial_state->id_new_state = $state_new;
        $historial_state->id_user = Auth::user()->id;
        $historial_state->save();



        return response()->json(['result' => 'ok']);
    }


    public function historialEstados(Request $request)
    {
        //

        return view('backend.accounting.vehiculo.list_historial_estados');
    }


    public function get_estados_fecha_table_data(Request $request)
    {


        $movimientos = HistorialStateCar::select('historial_state_cars.*')->with('vehiculo');

        return DataTables::eloquent($movimientos)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && !empty($request->search['value'])) {
                    $search = $request->search['value'];
                    $query->where(function ($query) use ($search) {

                        $query->orWhereHas('vehiculo', function ($q) use ($search) {
                            $q->where('dominio', 'like', "%{$search}%");
                        })
                            ->orWhereRaw("DATE_FORMAT(fecha, '%d-%m-%Y') LIKE ?", ['%' . $search . '%'])

                            ->orWhereHas('vehiculo', function ($q) use ($search) {
                                $q->where('id', 'like', "%{$search}%");
                            })
                            ->orWhereHas('oldState', function ($q) use ($search) {
                                $q->where('estado', 'like', "%{$search}%");
                            })
                            ->orWhereHas('newState', function ($q) use ($search) {
                                $q->where('estado', 'like', "%{$search}%");
                            });
                    });
                }

                if ($request->has('estado') && $request->post('estado') != '') {
                    $query->whereHas('newState', function ($s) use ($request) {
                        $s->where('id', $request->post('estado'));
                    });
                }
                //dd($request->post('date1'));
                if ($request->has('date1') && $request->post('date1') != '') {
                    $query->whereRaw("DATE(fecha) >= ?", [$request->post('date1')]);
                }
                if ($request->has('date2') && $request->post('date2') != '') {
                    $query->whereRaw("DATE(fecha) <= ?", [$request->post('date2')]);
                }
            })
            ->addColumn('interno', function ($movimiento) {


                if ($movimiento->vehiculo->company_id == 1) {
                    $in = 'PM-';
                } else if ($movimiento->vehiculo->company_id == 2) {
                    $in = 'PC-';
                }

                return  $in . $movimiento->vehiculo->id;
            })
            ->editColumn('fecha', function ($movimiento) {

                return  Carbon::parse($movimiento->fecha)->format('d-m-Y H:i:s');
            })
            ->addColumn('dominio', function ($movimiento) {

                return  $movimiento->vehiculo->dominio;
            })
            ->addColumn('state_ant', function ($movimiento) {

                return  $movimiento->oldState->estado;
            })
            ->addColumn('state_new', function ($movimiento) {

                return  $movimiento->newState->estado;
            })
            ->addColumn('usuario', function ($movimiento) {

                return  $movimiento->user->name;
            })

            ->setRowId(function ($movimiento) {
                return "row_" . $movimiento->id;
            })
            //  ->rawColumns(['action', 'members.name', 'status', 'id', 'procesar','venta'])
            ->make(true);
    }

    public function verificaPiezaByCar($itemId, $idCar)
    {
        $data['nro_motor'] = '';
        $car = Cars::where('id', $idCar)->first();
        $data['existe_pieza'] = Product::where('item_id', $itemId)->whereNull('car_id')->where('nro_interno', $car->id)->first();
        if ($car) {
            $data['nro_motor'] = $car->motor_nro;
        }

        return response()->json($data, 200);
    }


    public function notificarCambioEstado($car_id, $estado_ant, $estadoFinal)
    {

        if ($estadoFinal != $estado_ant) {
            //notificar a los vendedores relacionados en reservas cambio de estado

            $reservas = Quotation::where('car_id', $car_id)->get();
            // dd($reservas);

        if (in_array($estadoFinal, array(1,5,6))) {
            foreach ($reservas as $reserva) {
                $vendedor = User::find($reserva->user_id);

                Notification::send($vendedor, new CambioEstadoVehiculo($reserva));
            }
        }  

            // id estado es APTO notificar a Gerenciales
            if ($estadoFinal == 6) {
                $gerenciales = User::where('role_id', 2)->get();

                $car = Cars::where('id', $car_id)->first();
                //dd($car);

                Notification::send($gerenciales, new CambioEstadoVehiculoGerenciales($car));
            }
        }
    }

    public function certificado(Request $request, $id)
    {
        $car = Cars::where('id', $id)->first();

        if ($car->company_id != auth()->user()->company_id && strtolower(auth()->user()->role->name) == 'actualización de estados') {
            if ($request->ajax()) {
                return new Response('<h5 class="text-center red">' . _lang('No puede hacer cambios de otras compañías !') . '</h5>');
            } else {
                return back()->with('error', _lang('No puede hacer cambios de otras compañías !'));
            }
        }

        return view('backend.accounting.vehiculo.certificado.list', compact('car'));
    }



    public function generateCertificatePdf(Request $request)
    {
        $updated = Cars::where('dominio', $request->input('dominio'))->first();
        $impresa = $updated->cc_impresa;
        $updated->cc_impresa = 1;
        $updated->save;

        // Ruta del PDF base en el almacenamiento
        $pdfPath = storage_path('app/certificados/certificado_template2.pdf');
        $outputPath = storage_path('app/certificados/' . $request->input('dominio', 'NA') . '.pdf');

        // Crear una nueva instancia de FPDI
        $pdf = new Fpdi();

        // Cargar el PDF base para obtener las dimensiones de la página original
        $pageCount = $pdf->setSourceFile($pdfPath);
        $template = $pdf->importPage(1); // Importar la primera página
        $size = $pdf->getTemplateSize($template); // Obtener el tamaño de la plantilla

        // Establecer el tamaño de la página a las dimensiones de la plantilla original
        $pdf->AddPage('P', [$size['width'], $size['height']]);

        // Usar la plantilla en la nueva página
        $pdf->useTemplate($template);

        // Establecer la fuente y tamaño del texto
        $pdf->SetFont('Helvetica');
        $pdf->SetFontSize(18);

        // Posicionar y escribir los datos del formulario
        $pdf->SetXY(185, 221);
        $pdf->Write(0, $request->input('dominio', ''));

        $pdf->SetXY(185, 242);
        $pdf->Write(0, $request->input('marca', ''));

        $pdf->SetXY(185, 263);
        $pdf->Write(0, $request->input('tipo', ''));

        $pdf->SetXY(185, 284);
        $pdf->Write(0, $request->input('modelo', ''));

        $pdf->SetXY(185, 305);
        $pdf->Write(0, $request->input('marca_motor', ''));

        $pdf->SetXY(185, 328);
        $pdf->Write(0, $request->input('numero_motor', ''));

        $pdf->SetXY(185, 350);
        $pdf->Write(0, $request->input('marca_chasis', ''));

        $pdf->SetXY(185, 372);
        $pdf->Write(0, $request->input('numero_chasis', ''));



        if ($impresa == 1) {
            $pdf->SetXY(400, 600);
            $pdf->Write(0, '*** COPIA ***');
        }

        // Guardar el PDF en el almacenamiento temporal
        $pdf->Output($outputPath, 'F');

        // Retornar el archivo generado como una respuesta de descarga
        return response()->file($outputPath, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $request->input('dominio', 'certificado') . '.pdf"',
        ]);
    }
    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        $data = $this->data_export_vehiculos($request);
        //$data = $this->data_export($request);
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

            /*if ($car->company_id == 1) {
                $in = 'PM';
            } else if ($car->company_id == 2) {
                $in = 'PC';
            }

            $in.=$car->tipo_vehiculo.'-'; */
            return [
                'Interno' => nroInternoAlias($car->company_id, $car->tipo_vehiculo, $car->id),
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

    public function expenseExportPdf(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        $data = $this->expense_data_export($request);
        $export = new \App\Exports\ExpensesCarsExportPdf($data);
        return $export->generate();
    }
    public function expenseExportExcel(Request $request)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $data = $this->expense_data_export($request);

        return Excel::download(new \App\Exports\ExpensesCarsExport($data), 'vehiculos.xlsx');
    }

    public function expense_data_export($request)
    {

        $currency = currency();

        // Consulta base con relaciones y selección completa de columnas
        $transactions = Transaction::with([
            'payer',
            'tipo_comprobante',
            'account',
            'expense_type',
            'payment_method',
            'pagos_car'
        ])
            ->select('transactions.*') // Todas las columnas necesarias
            ->where('transactions.dr_cr', 'dr')
            ->orderBy('transactions.trans_date', 'desc');

        // Filtros dinámicos
        if (isset($request->idCar)) {
            $transactions->whereHas('pagos_car', function ($query) use ($request) {
                $query->where('id_car', $request->idCar);
            });
        }

        if ($request->has('trans_date')) {
            $transactions->whereDate('trans_date', $request->input('trans_date'));
        }

        if ($request->has('payer_name')) {
            $transactions->whereHas('payer', function ($query) use ($request) {
                $query->where('name', 'like', '%' . $request->input('payer_name') . '%');
            });
        }

        if ($request->has('status')) {
            $status = $request->input('status') == 2 ? 1 : 0; // Resuelto o pendiente
            $transactions->where(function ($query) use ($status) {
                $query->where('status', $status)->orWhereNull('status');
            });
        }

        if ($request->has('payment_priority')) {
            $priority = $request->input('payment_priority');
            if ($priority == '-1') {
                $transactions->whereNull('payment_priority');
            } else {
                $transactions->where('payment_priority', $priority);
            }
        }

        // Obtener datos completos
        $data = $transactions->get();

        // Formatear y mapear datos para exportación
        $exportData = $data->map(function ($trans) use ($currency) {

            $acc_currency = currency($trans->account->account_currency ?? '');

            if ($acc_currency != $currency) {
                $monto = decimalPlace($trans->amount, currency($trans->account->account_currency ?? '')) . ' (' . decimalPlace($trans->base_amount, $currency) . ')';
            } else {
                $monto = decimalPlace($trans->amount, currency($trans->account->account_currency ?? ''));
            }

            $interno = '';
            if ($trans->pagos_car) {

                if ($trans->pagos_car->id_car) {
                    $in = '';
                    if ($trans->pagos_car->vehiculo->company_id == 1) {
                        $in = 'PM-';
                    } else if ($trans->pagos_car->vehiculo->company_id == 2) {
                        $in = 'PC-';
                    }
                    $interno = $in . $trans->pagos_car->id_car;
                }
            }

            return [
                'Interno' => $interno,
                'Fecha' => date(get_company_option('date_format', 'Y-m-d'), strtotime($trans->trans_date)),
                'Razón Social' => $trans->razon_social ?? '',
                'Monto' => $monto,
                'Pagador' => $trans->payer->name ?? '',
                'Tipo de Comprobante' => $trans->tipo_comprobante->descripcion ?? '',
                'Cuenta' => $trans->account->account_title ?? '',
                'Imputar A' => $trans->imputar_a ?? '',
                'Tipo de Gasto' => $trans->expense_type->name ?? '',
                'Detalle del Rubro' => $trans->detalle_rubro ?? '',
                'Método de Pago' => $trans->payment_method->name ?? '',
                'Banco' => $trans->banco ?? '',
                'Cheque Nro' => $trans->cheque_nro ?? '',
                'Cheque Vencimiento' => $trans->cheque_vencimiento ?? '',
                'Cheque Entregado A' => $trans->cheque_entregado_a ?? '',
                'Tasa' => $trans->tasa ?? '',
                'Estatus' => $trans->status == 1 ? 'Resuelto' : 'Pendiente',
                'Prioridad de Pago' => $trans->payment_priority == null ? 'Normal' : ucwords(str_replace('_', ' ', $trans->payment_priority)),
            ];
        });

        // Devuelve datos formateados
        return $exportData;
    }


    /**
     *  Bajar zip de imagenes
     */

    public function veh_imag_zip($id, $tipo = "imagenes")
    {

        $company_id = company_id_arr();
        $user_type = Auth::user()->user_type;

        $cars = Cars::where('cars.id', $id)
            ->whereIn('company_id', $company_id)
            ->with('marca_modelo')
            ->with('aseguradora')
            ->with('provincias')
            ->with('tramitador')
            ->with('lugar_entrega')
            ->with('estado')
            ->first();
        if (!$cars) {
            return back()->with('error', _lang('Sorry, Car not found !'));
        }

        $path = public_path("uploads/");
        $carpeta_comprimir = "{$path}veh_img_{$id}_" . date("Y-m-d_His");
		$disk = Storage::disk('gcs');
		$path2 = "/vehiculos/";

        if (!File::isDirectory($carpeta_comprimir)) {
            File::makeDirectory($carpeta_comprimir, 0777, true, true);
        }
        // se despliegan las imagenes
        //videos
        if (!empty($cars->video) && in_array($tipo, ['all', 'videos'])) {
            $videos = explode(';', $cars->video);
            foreach ($videos as $v) {
                $file = Storage::path('vehiculo/' . $v);
                if (is_file($file)) {
                    //Storage::path                    //storage_path('vehiculo/'.$v
                    $valor = File::copy($file, $carpeta_comprimir . "/" . $v);
                }else{
					if (Storage::disk('gcs')->exists($path2. $v)) {
						$stream = Storage::disk('gcs')->readStream($path2. $v);
						$destinationStream = fopen($carpeta_comprimir . "/" . $v, 'w');
							if ($stream && $destinationStream) {
								stream_copy_to_stream($stream, $destinationStream);
								fclose($destinationStream);
							}
					}
				}
            }
        }
        sleep(5);
        //Fotos 04D
        if (!empty($cars->img_recepcion) && in_array($tipo, ['all', '4d'])) {
            foreach ($cars->img_recepcion as $v) {
				if (file_exists($path . 'vehiculos/' . $v->img)) {
					$file= $path . 'vehiculos/' . $v->img;
					$valor = File::copy($file, $carpeta_comprimir . "/" . $v->img);
					//GuardarmarcaAgua($path . 'vehiculos/' . $v->img, $v->company_id, $carpeta_comprimir);		
				}else{
					if (Storage::disk('gcs')->exists($path2. $v->img)) {
						$stream = Storage::disk('gcs')->readStream($path2. $v->img);
						$destinationStream = fopen($carpeta_comprimir . "/" . $v->img, 'w');
							if ($stream && $destinationStream) {
								stream_copy_to_stream($stream, $destinationStream);
								fclose($destinationStream);
							}
					}
				}
            }
        }
		
        sleep(5);
        //Fotos generales
        if (!empty($cars->img) && in_array($tipo, ['all', 'imagenes'])) {
            foreach ($cars->img as $v) {
				if (file_exists($path . 'vehiculos/' . $v->img)) {
					$file= $path . 'vehiculos/' . $v->img;
					$valor = File::copy($file, $carpeta_comprimir . "/" . $v->img);
					//GuardarmarcaAgua($path . 'vehiculos/' . $v->img, $v->company_id, $carpeta_comprimir);		
				}else{
					if (Storage::disk('gcs')->exists($path2. $v->img)) {
						$stream = Storage::disk('gcs')->readStream($path2. $v->img);
						$destinationStream = fopen($carpeta_comprimir . "/" . $v->img, 'w');
							if ($stream && $destinationStream) {
								stream_copy_to_stream($stream, $destinationStream);
								fclose($destinationStream);
							}
					}
					
/*					$fileExists = $disk->exists($path2. $v->img);
					if ($fileExists) {
						Storage::disk('gcs')->copy($path2. $v->img, $carpeta_comprimir . "/" . $v);
//						$fileContents = $disk->get($path2. $v);
//						Storage::disk('local')->put($carpeta_comprimir . "/" . $v, $fileContents);
						//GuardarmarcaAgua($path2. $v->img, $v->company_id, $carpeta_comprimir);		
					}*/
				}
                //GuardarmarcaAgua($path . 'vehiculos/' . $v->img, $v->company_id, $carpeta_comprimir);
                //echo $v->img."</br>";
            }
        }
        sleep(3);

        // se comprimime
        $zip = new ZipArchive;
        $fileName = "veh_img_{$id}_" . date("Y-m-d_His") . ".zip";

        if ($zip->open($path . $fileName, ZipArchive::CREATE) === TRUE) {
            $files = File::files($carpeta_comprimir);

            foreach ($files as $key => $value) {
                $relativeNameInZipFile = basename($value);
                $zip->addFile($value, $relativeNameInZipFile);
            }
            $zip->close();
        }
		sleep(1);
        if (\File::isDirectory($carpeta_comprimir)) \File::deleteDirectory($carpeta_comprimir);
        // if (!is_file($url)) File::delete($path.$fileName);
		if (file_exists($path . $fileName)) {
			return response()->download($path . $fileName)->deleteFileAfterSend(true);
		}else{
			 return back()->withErrors(['error' => 'File not found.']);
		}
    }

    public function estado_seguimiento(Request $request)
    {
        if ($request->ajax()) {

            $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
            $user_type = Auth::user()->user_type;

            $data  = Cars::select('cars.*')->withTrashed()
                ->whereIn('company_id', $company_id);
            //->orderBy("projects.id","desc");

            //dd($cars);
            return Datatables::eloquent($data)
                ->addColumn('interno', function ($data) {
                    /* $Company_text='';
            if ($data->company_id!=null){
                if ($data->company_id == 1) {
                    $Company_text = 'PM-';
                } elseif ($data->company_id == 2) {
                    $Company_text = 'PC-';
                }
            }
            //return "{$Company_text}{$data->id}";   */
                    return nroInternoAlias($data->company_id, $data->tipo_vehiculo, $data->id);
                })
                ->addColumn('marcamodelo', function ($data) {
                    return ($data->marca_modelo->marca->marca ?? '') . ' ' .
                        ($data->marca_modelo->modelo->modelo ?? '');
                })
                ->addColumn('tipo_baja', function ($data) {
                    return $this->TipoBaja($data->tipo_baja);
                })
                ->addColumn('ubicacion', function ($data) {
                    return $data->lugar_entrega->nombre ?? '';
                })
                ->addColumn('estado', function ($data) {
                    return $data->estado->estado ?? null;
                })
                ->addColumn('observacion_retiro', function ($car) {
                    return strip_tags(clean($car->observacion_retiro));
                })
                ->filterColumn('interno', function ($query, $keyword) {
                    $query->where('id', 'like', '%' . $keyword . '%');
                })
                ->filterColumn('marcamodelo', function ($query, $keyword) {
                    $query->whereHas('marca_modelo', function ($str) use ($keyword) {
                        $str->whereHas('marca', function ($str) use ($keyword) {
                            $str->where('marca', 'like', "%{$keyword}%");
                        });

                        $str->orwhereHas('modelo', function ($str) use ($keyword) {
                            $str->where('modelo', 'like', "%{$keyword}%");
                        });
                    });
                })
                ->filterColumn('tipo_baja', function ($query, $keyword) {
                    //$valor_devuelto= $this->TipoBaja("","{$keyword}");
                    $valor_devuelto = $this->TipoBaja("", "{$keyword}");
                    $ids = ($valor_devuelto != '') ? explode(",", $valor_devuelto) : array();
                    $query->wherein('tipo_baja', $ids);
                    //}   
                })
                ->filterColumn('estado', function ($query, $keyword) {
                    $query->whereHas('estado', function ($q) use ($keyword) {
                        $q->where('estado', 'like', "%{$keyword}%");
                    });
                })
                ->filterColumn('ubicacion', function ($query, $keyword) {
                    $query->whereHas('lugar_entrega', function ($q) use ($keyword) {
                        $q->where('nombre', 'like', "%{$keyword}%");
                    });
                })

                /*        ->addColumn('observacion_retiro', function ($car) {
            return strip_tags(clean($car->observacion_retiro));
        })*/



                ->rawColumns(['invoice_number'])
                ->make(true);
        }

        return view('backend.accounting.vehiculo.list_seguimiento');
    }

    public function get_table_data(Request $request)
    {
        $date_format = get_company_option('date_format', 'Y-m-d');
        $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
        $user_type = Auth::user()->user_type;
        $datos = $this->datos();
        $cars = Cars::select('cars.*')->withTrashed()
            ->with('marca_modelo')
            ->with('company')
            ->with('provincias')
            ->with('tramitador')
            ->with('lugar_entrega')
            ->with('responsable_retiro')
            ->with('estado')
            ->whereIn('company_id', $company_id);

        if (isset($request->regitro_activo)) {
            if ($request->regitro_activo == 0) {
                $cars->where('cars.deleted_at', null);
            } else {
                $cars->whereNotNull('cars.deleted_at');
            }
        } else {
            $cars->where('cars.deleted_at', null);
        }

        //dd($cars->toSql());   
        return Datatables::eloquent($cars)
            ->filterColumn('tramitador', function ($query, $keyword) {
                $query->orwhereHas('tramitador', function ($str) use ($keyword) {
                    $str->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('entregar_en', function ($query, $keyword) {

                if ($keyword != "") {
                    $ids = explode(",", $keyword);
                    if (in_array("-1", $ids)) {
                        $query->where('idLugar_entrega', '=', "")
                            ->orWhereNull('idLugar_entrega');
                    } else {
                        $query->wherein('idLugar_entrega', $ids);
                    }
                }
                /*$query->orwhereHas('lugar_entrega', function ($str) use ($keyword) {
                    $str->where('nombre', 'like', "%{$keyword}%");
                });*/
            })->filterColumn('lugar_entrega', function ($query, $keyword) {

                if ($keyword != "") {
                    $ids = explode(",", $keyword);
                    if (in_array("-1", $ids)) {
                        $query->where('idLugar_entrega', '=', "")
                            ->orWhereNull('idLugar_entrega');
                    } else {
                        $query->wherein('idLugar_entrega', $ids);
                    }
                }
                /*$query->orwhereHas('lugar_entrega', function ($str) use ($keyword) {
                    $str->where('nombre', 'like', "%{$keyword}%");
                });*/
            })
            ->filterColumn('aseguradora', function ($query, $keyword) {
                $query->orwhereHas('aseguradora', function ($str) use ($keyword) {
                    $str->where('nombre', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('marca', function ($query, $keyword) {
                $query->whereHas('marca_modelo', function ($str) use ($keyword) {
                    $str->whereHas('marca', function ($str) use ($keyword) {
                        //$str->where('marca', 'like', "%{$keyword}%");

                        if ($keyword == "todos") {
                            $str->where('marca', '=', "")
                                ->orWhereNull('marca');
                        } elseif ($keyword != "") {
                            $str->where('marca', 'like', "%{$keyword}%");
                        }
                    });
                });
            })
            ->filterColumn('modelo', function ($query, $keyword) {
                $query->whereHas('marca_modelo', function ($str) use ($keyword) {
                    $str->whereHas('modelo', function ($str) use ($keyword) {
                        $str->where('modelo', 'like', "%{$keyword}%");
                    });
                });
            })
            ->filterColumn('estado', function ($query, $keyword) {
                if ($keyword != "") {
                    $ids = explode(",", $keyword);
                    if (in_array("-1", $ids)) {
                        $query->where('idEstado', '=', "")
                            ->orWhereNull('idEstado');
                    } else {
                        $query->wherein('idEstado', $ids);
                    }
                }
                /*                if ($keyword=="todos") {
                                          $query->where('idEstado','=', "")
                                            ->orWhereNull('idEstado');
                                }elseif($keyword!=""){
                                    $query->wherein('idEstado',$ids);
                         }*/


                /* if ($keyword!=""){
                    $ids= explode(",", $keyword);
                    //$ids=($keyword!='') ? explode(",", $keyword) : array();
                    $query->wherein('idEstado',$ids);
                }*/
            })
            ->filterColumn('provincia', function ($query, $keyword) {
                $query->WhereHas('provincias', function ($q) use ($keyword) {
                    $q->where('provincia', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('fecha_envio_doc', function ($query, $keyword) {

                $date_range = ($keyword != '') ? explode(" - ", $keyword) : array();
                if (count($date_range) == 2) {
                    $query->whereBetween('cars.fecha_envio_doc', [$date_range[0], $date_range[1]]);
                }
            })
            ->filterColumn('fecha_ingreso', function ($query, $keyword) {

                $date_range = ($keyword != '') ? explode(" - ", $keyword) : array();
                if (count($date_range) == 2) {
                    $query->whereBetween('cars.fecha_ingreso', [$date_range[0], $date_range[1]]);
                }
            })

            ->filterColumn('anulado', function ($query, $keyword) {
                if ($keyword == "Si") {
                    $query->whereNotNull('cars.deleted_at');
                } elseif ($keyword == "No") {
                    $query->whereNull('cars.deleted_at');
                }
            })
            ->filterColumn('fecha_asignacion', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(fecha_asignacion,'%d/%m/%Y') LIKE ?", ["%$keyword%"]);
            })
            ->filterColumn('fecha_entrega', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(fecha_entrega_asegurado_cia,'%d/%m/%Y') LIKE ?", ["%$keyword%"]);
            })
            ->filterColumn('observacion_admin', function ($query, $keyword) {
				
				 if ($keyword == "novacios") {
					 $query->where(DB::raw('TRIM(observaciones_admin)'), '!=', '');
                } elseif ($keyword != "") {
                    $query->where('observaciones_admin', 'like', "%{$keyword}%");
                }
                //$query->where('observaciones_admin', 'like', "%{$keyword}%");
            })
            ->filterColumn('fecha_recepcion', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(fecha_recepcion,'%d/%m/%Y') LIKE ?", ["%$keyword%"]);
            })
            ->filterColumn('coordinar_retiro', function ($query, $keyword) {
                if ($keyword == "Si") {
                    $query->where('coordinar_retiro', "1");
                } elseif ($keyword == "No") {
                    $query->orwhereNull('coordinar_retiro');
                }
            })
            ->filterColumn('fecha_confirmacion_contacto', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(fecha_confirmacion_contacto,'%d/%m/%Y') LIKE ?", ["%$keyword%"]);
            })
            ->filterColumn('fecha_limite_retiro', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(fecha_limite_retiro,'%d/%m/%Y') LIKE ?", ["%$keyword%"]);
            })
            ->filterColumn('responsable_retiro', function ($query, $keyword) {
                $query->orwhereHas('responsable_retiro', function ($str) use ($keyword) {
                    $str->where('name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('fecha_retiro', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(fecha_retiro,'%d/%m/%Y') LIKE ?", ["%$keyword%"]);
            })
            ->filterColumn('crp_nro', function ($query, $keyword) {
                if ($keyword == "todos") {
                    $query->where('crp_nro', '=', "")
                        ->orWhereNull('crp_nro');
                } elseif ($keyword != "") {
                    $query->where('crp_nro', 'like', "%{$keyword}%");
                }
            })
            ->filterColumn('dominio', function ($query, $keyword) {
                if ($keyword == "todos") {
                    $query->where('cars.dominio', '=', "")
                        ->orWhereNull('cars.dominio');
                } elseif ($keyword != "") {
                    $query->where('cars.dominio', 'like', "%{$keyword}%");
                }
            })
            ->filterColumn('motor_nro', function ($query, $keyword) {
                if ($keyword == "todos") {
                    $query->where('cars.motor_nro', '=', "")
                        ->orWhereNull('cars.motor_nro');
                } elseif ($keyword != "") {
                    $query->where('cars.motor_nro', 'like', "%{$keyword}%");
                }
            })
            ->filterColumn('fecha_de_pago_cia', function ($query, $keyword) {
                //$query->Where('cars.properties->fecha_pago_cia', 'like', "%{$keyword}%");
                $query->whereRaw("DATE_FORMAT(JSON_UNQUOTE(JSON_EXTRACT(cars.properties, '$.fecha_pago_cia')), '%d/%m/%Y') LIKE ?", ["%$keyword%"]);

                //DATE_FORMAT(JSON_UNQUOTE(JSON_EXTRACT(cars.properties, '$.fecha_pago_cia')), '%d/%m/%Y')

                //dd($query->toSql());
                //  $query->Where('cars.properties->fecha_pago_cia', 'like', "%{$keyword}%");
            })
            ->filterColumn('company', function ($query, $keyword) {
                $query->WhereHas('company', function ($q) use ($keyword) {
                    $q->where('business_name', 'like', "%{$keyword}%");
                });
            })->filterColumn('pieza_no_disponible', function ($query, $keyword) {
                $query->WhereHas('pieza_ausente', function ($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%");
                });
            })



            ->editColumn('id', function ($car) {
                return '<a href="' . action('VehiculoController@show', $car->id) . '" class="btn-xs ajax-modal" data-title=" Multimedia">' . nroInternoAlias($car->company_id, $car->tipo_vehiculo, $car->id) .  '</a>';
            })
            ->editColumn('anulado', function ($car) {
                if ($car->deleted_at) {
                    $in = 'Si';
                } else {
                    $in = 'No';
                }
                return $in;
            })
            ->editColumn('company', function ($car) {
                return $car->company->business_name;
            })
            ->editColumn('fecha_asignacion', function ($car) use ($date_format) {
                //$date_format = get_company_option('date_format', 'Y-m-d');
                return date($date_format, strtotime($car->fecha_asignacion));
            })
            ->editColumn('tramitador', function ($car) {
                return $car->tramitador->name ?? null;
            })
            ->editColumn('aseguradora', function ($car) {
                return $car->aseguradora->nombre ?? null;
            })
            ->editColumn('tramitador_compania', function ($car) {
                return $car->tramitador_compania;
            })
            ->editColumn('siniestro', function ($car) {
                return $car->siniestro;
            })
            ->editColumn('dominio', function ($car) {
                return $car->dominio;
            })
            ->editColumn('marca', function ($car) {

                return $car->marca_modelo->marca->marca ?? '';
            })
            ->editColumn('modelo', function ($car) {

                return $car->marca_modelo->modelo->modelo ?? '';
            })
            ->editColumn('motor', function ($car) {
                return $car->motor_nro;
            })
            ->editColumn('tipo_baja', function ($car) use ($datos) {
                return $datos['tipo_baja'][$car->tipo_baja] ?? null;
            })
            ->editColumn('contacto', function ($car) {
                return $car->contacto;
            })
            ->editColumn('lugar_retiro', function ($car) {
                return $car->lugar_retiro;
            })
            ->editColumn('provincia', function ($car) {
                return $car->provincias->provincia ?? null;
            })
            ->editColumn('estado', function ($car) {

                if (strtolower(auth()->user()->role->name) == 'gerente de operarios' || strtolower(auth()->user()->role->name) == 'operario') {

                    $html = "<select class='form-control' idCar='" . $car->id . "' name='estadoMod' onchange='updatedStado(this)'>";
                    $html .= "<option value=''>Seleciona el estado</option>";
                    //$estados = Estado::select('*')->where('Activo', "Si")->get(); 
                    foreach (Estado::select('*')->where('Activo', "Si")->get() as $estado) {
                        $html .= "<option value='{$estado->id}' " . ($estado->id == $car->idEstado ? 'selected' : '') . " >{$estado->estado}</option>";
                    }

                    $html .= "</select>";

                    return $html;
                }
                return $car->estado->estado ?? null;
            })
            ->editColumn('entregado_a', function ($car) use ($datos) {

                return $datos['responsable_entregas'][$car->entregado_a] ?? null;
            })
            ->editColumn('lugar_entrega', function ($car) {

                if (strtolower(auth()->user()->role->name) == 'gerente de operarios' || strtolower(auth()->user()->role->name) == 'operario') {

                    $html = "<select class='form-control' idCar='" . $car->id . "' name='estadoMod' onchange='updateUbicacion(this)'>";
                    $html .= "<option value=''>Seleciona la ubicacion</option>";
                    foreach (Lugar_entregas::all() as $ubicacion) {
                        $html .= "<option value='{$ubicacion->id}' " . ($ubicacion->id == $car->idLugar_entrega ? 'selected' : '') . " >{$ubicacion->nombre}</option>";
                    }

                    $html .= "</select>";

                    return $html;
                }

                return $car->lugar_entrega->nombre ?? null;
            })
            ->editColumn('fecha_entrega', function ($car) use ($date_format) {
                return isset($car->fecha_entrega_asegurado_cia) ? date($date_format, strtotime($car->fecha_entrega_asegurado_cia)) : null;
            })
            ->editColumn('fecha_confirmacion', function ($car) use ($date_format) {
                return isset($car->fecha_confirmacion) ? date($date_format, strtotime($car->fecha_confirmacion)) : null;
            })->editColumn('fecha_recepcion', function ($car) use ($date_format) {
                return isset($car->fecha_recepcion) ? date($date_format, strtotime($car->fecha_recepcion)) : null;
            })
            ->editColumn('fecha_confirmacion_contacto', function ($car) use ($date_format) {
                return isset($car->fecha_confirmacion_contacto) ? date($date_format, strtotime($car->fecha_confirmacion_contacto)) : null;
            })
            ->editColumn('fecha_limite_retiro', function ($car) use ($date_format) {
                return isset($car->fecha_limite_retiro) ? date($date_format, strtotime($car->fecha_limite_retiro)) : null;
            })
            ->editColumn('fecha_retiro', function ($car) use ($date_format) {
                return isset($car->fecha_retiro) ? date($date_format, strtotime($car->fecha_retiro)) : null;
            })

            ->editColumn('fecha_ingreso', function ($car) use ($date_format) {
                return isset($car->fecha_ingreso) ? date($date_format, strtotime($car->fecha_ingreso)) : null;
            })
            ->editColumn('fecha_envio_doc', function ($car) use ($date_format) {
                return isset($car->fecha_envio_doc) ? date($date_format, strtotime($car->fecha_envio_doc)) : null;
            })
            ->editColumn('observacion_admin', function ($car) {
                return strip_tags(clean($car->observaciones_admin));
            })->editColumn('coordinar_retiro', function ($car) {
                return $car->coordinar_retiro == 1 ? "X" : "";
            })
            ->editColumn('responsable_retiro', function ($car) {
                return $car->responsable_retiro->name ?? null;
            })
            ->editColumn('entregar_en', function ($car) {
                return $car->lugar_entrega->nombre ?? null;
            })->editColumn('control', function ($car) {
                return $car->control == 1 ? 'En fecha' : 'Explicar';
            })
            ->editColumn('observacion_retiro', function ($car) {
                return strip_tags(clean($car->observacion_retiro));
            })
            ->editColumn('observacion_gerente_operario', function ($car) {
                if (strtolower(auth()->user()->role->name) == 'gerencial' || strtolower(auth()->user()->role->name) == 'gerente de operarios' || strtolower(auth()->user()->role->name) == 'operario') {
                    return strip_tags(clean($car->observacion_gerente_operario));
                }
                return '';
            })
            ->addColumn('pieza_no_disponible', function ($car) {
                $piezas = $car->pieza_ausente;

                $html = '';
                if (!empty($piezas)) {
                    foreach ($piezas as $pieza) {
                        $html .= $pieza->name . '<br>';
                    }
                }

                return $html;
            })
            ->addColumn('fecha_de_pago_cia', function ($car)  use ($date_format) {
                return isset($car->properties['fecha_pago_cia']) ? date($date_format, strtotime($car->properties['fecha_pago_cia'])) : null;
                //   return $car->properties['fecha_pago_cia'] ?? '';
            })
            ->addColumn('action', function ($car) {
                if ($car->company_id == 1) {
                    $in = 'PM-';
                } else if ($car->company_id == 2) {
                    $in = 'PC-';
                }
                $filemanager = FileManager::where('name', $in . $car->id)->first();
                $enlace = '';

                if (!empty($filemanager)) {
                    $enlace = '<a class="btn btn-xs" target="_blank" href="' . url(
                        'file_manager/directory/' . encrypt($filemanager->id)
                    ) . '"><i class="far fa-folder"></i></a>';
                }

                $botondelete = "";

                $os = array("Gerencial");
                if (in_array(strtolower(auth()->user()->role->name), $os)) {
                    $botondelete = '<input name="_method" type="hidden" value="DELETE"><button class="btn btn-danger btn-xs btn-remove" type="submit"><i class="ti-eraser"></i></button>';
                }

                $action_mostrar = '<form action="' . action('VehiculoController@destroy', $car['id']) . '" class="text-center" method="post">'
                    . '<a href="' . action('VehiculoController@show', $car['id']) . '" class="btn btn-primary
btn-xs ajax-modal" data-title=" ' . _lang('Multimedia') . '"><i class="ti-eye"></i></a>&nbsp;'
                    . '<a href="' . action('VehiculoController@edit', $car['id']) . '" 
data-title="' . _lang('Update Vehicle') . '" class="btn btn-warning btn-xs ajax-modal"><i class="ti-pencil"></i></a>&nbsp;'
                    . '<a target="_blank"
href="' . action('VehiculoController@movimientos', $car['id']) . '" data-title="' . _lang('Ver movimientos') . '" class="btn btn-warning btn-xs"><i 
class="ti-receipt"></i></a>&nbsp;'
                    . '<a
href="' . action('VehiculoController@seguimiento', $car['id']) . '" data-title="' . _lang('Ver Estado') . '" class="btn btn-warning btn-xs ajax-modal"><i 
class="fas fa-search"></i></a>&nbsp;'
                    .
                    '<a
href="' . action('VehiculoController@certificado', $car['id']) . '" data-title="' . _lang('Certificado') . '" class="btn btn-success btn-xs ajax-modal"><i 
class="">C</i></a>&nbsp;' 
. '<a href="' . url('vehiculo/historial?id='. $car['id']) . '" 
data-title="' . _lang('Historial') . '" data-fullscreen="true" class="btn btn-warning btn-xs ajax-modal"><i class="ti-list"></i></a>&nbsp;'
                    .
                    $enlace
                    . csrf_field()
                    . $botondelete
                    . '</form>';

                if ($car->deleted_at) {
                    $action_mostrar = "";
                }

                return $action_mostrar;
            })
            ->setRowId(function ($car) {
                return "row_" . $car->id;
            })
            ->rawColumns(['action', 'pieza_no_disponible', 'estado', 'members.name', 'status', 'id', 'lugar_entrega'])
            ->make(true);
    }

    public function data_export_vehiculos($request)
    {
        $date_format = get_company_option('date_format', 'Y-m-d');
        $company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
        $user_type = Auth::user()->user_type;
        $datos = $this->datos();
        $cars = Cars::select('cars.*')->withTrashed()
            ->with('marca_modelo')
            ->with('company')
            ->with('provincias')
            ->with('tramitador')
            ->with('lugar_entrega')
            ->with('responsable_retiro')
            ->with('estado')
            ->whereIn('company_id', $company_id);

        if (isset($request->regitro_activo)) {
            if ($request->regitro_activo == 0) {
                $cars->where('cars.deleted_at', null);
            } else {
                $cars->whereNotNull('cars.deleted_at');
            }
        } else {
            $cars->where('cars.deleted_at', null);
        }


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
                    case 'marca':
                        $cars->whereHas('marca_modelo', function ($q) use ($searchValue) {
                            $q->whereHas('marca', function ($marca) use ($searchValue) {
                                $marca->where('marca', 'like', "%{$searchValue}%");
                            });
                        });
                        break;
                    case 'modelo':
                        $cars->whereHas('marca_modelo', function ($q) use ($searchValue) {
                            $q->whereHas('modelo', function ($marca) use ($searchValue) {
                                $marca->where('modelo', 'like', "%{$searchValue}%");
                            });
                        });
                        break;
                    case 'estado':
                        $ids = ($searchValue != '') ? explode(",", $searchValue) : array();
                        $cars->wherein('idEstado', $ids);
                        break;

                    case 'entregar_en':
                        $cars->whereHas('lugar_entrega', function ($q) use ($searchValue) {
                            $q->where('nombre', 'like', "%{$searchValue}%");
                        });

                        break;

                    case 'provincia':
                        $cars->whereHas('provincias', function ($q) use ($searchValue) {
                            $q->where('provincia', 'like', "%{$searchValue}%");
                        });

                        break;

                    case 'fecha_envio_doc':
                        $date_range = ($searchValue != '') ? explode(" - ", $searchValue) : array();
                        if (count($date_range) == 2) {
                            $cars->whereBetween('cars.fecha_envio_doc', [$date_range[0], $date_range[1]]);
                        }
                        break;

                    case 'fecha_ingreso':
                        $date_range = ($searchValue != '') ? explode(" - ", $searchValue) : array();
                        if (count($date_range) == 2) {
                            $cars->whereBetween('cars.fecha_ingreso', [$date_range[0], $date_range[1]]);
                        }
                        break;
                    case 'anulado':
                        if ($searchValue == "Si") {
                            $cars->whereNotNull('cars.deleted_at');
                        } elseif ($searchValue == "No") {
                            $cars->whereNull('cars.deleted_at');
                        }
                        break;
                    case 'fecha_asignacion':
                        $cars->whereRaw("DATE_FORMAT(fecha_asignacion,'%d/%m/%Y') LIKE ?", ["%$searchValue%"]);
                        break;
                    case 'fecha_entrega':
                        $cars->whereRaw("DATE_FORMAT(fecha_entrega_asegurado_cia,'%d/%m/%Y') LIKE ?", ["%$searchValue%"]);
                        break;
                    case 'fecha_recepcion':
                        $cars->whereRaw("DATE_FORMAT(fecha_recepcion,'%d/%m/%Y') LIKE ?", ["%$searchValue%"]);
                        break;

                    case 'coordinar_retiro':
                        if ($searchValue == "Si") {
                            $cars->where('coordinar_retiro', "1");
                        } elseif ($searchValue == "No") {
                            $cars->orwhereNull('coordinar_retiro');
                        }
                        break;

                    case 'fecha_confirmacion_contacto':
                        $cars->whereRaw("DATE_FORMAT(fecha_confirmacion_contacto,'%d/%m/%Y') LIKE ?", ["%$searchValue%"]);
                        break;

                    case 'fecha_limite_retiro':
                        $cars->whereRaw("DATE_FORMAT(fecha_limite_retiro,'%d/%m/%Y') LIKE ?", ["%$searchValue%"]);
                        break;
                    case 'responsable_retiro':
                        $cars->whereHas('responsable_retiro', function ($q) use ($searchValue) {
                            $q->where('name', 'like', "%{$searchValue}%");
                        });
                        break;
                    case 'fecha_retiro':
                        $cars->whereRaw("DATE_FORMAT(fecha_retiro,'%d/%m/%Y') LIKE ?", ["%$searchValue%"]);
                        break;

                    default:
                        // Filtro genérico para columnas directas de la tabla `cars`
                        $cars->where($columnName, 'like', "%{$searchValue}%");
                        break;
                }
            }
        }

        $cars = $cars->get();

        return $cars->map(function ($car) {
            return [
                'Interno' => nroInternoAlias($car->company_id, $car->tipo_vehiculo, $car->id),
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
                'Motor Vendido' => $car->crp_nro,
                'Entregar En' => $car->lugar_entrega->nombre ?? '',
                'Fecha Retiro' => $car->fecha_retiro ? date(get_company_option('date_format', 'Y-m-d'), strtotime($car->fecha_retiro)) : '',
                'Fecha Ingreso' => $car->fecha_ingreso ? date(get_company_option('date_format', 'Y-m-d'), strtotime($car->fecha_ingreso)) : '',
                'Observación Gerente/Operario' => $car->observacion_gerente_operario,
                'Observación Retiro' => $car->observacion_retiro,
            ];
        });
    }


    public function seleccionadoPiezaByCar($nro_interno)
    {
        $data['pieza_listas'] = Product::select(DB::raw('GROUP_CONCAT(DISTINCT item_id) AS seleccionados'))->where('nro_interno', $nro_interno)->whereNull('car_id')->get();
        return response()->json($data, 200);
    }
}
