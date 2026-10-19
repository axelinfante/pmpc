<?php
namespace App\Http\Controllers;

use App\DatosSincronizarFactura; // Reemplaza por el nombre exacto de tu modelo
use App\Invoice; // Reemplaza por el nombre exacto de tu modelo
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB; 
use Exception;


class DatosSincronizarFacturaController extends Controller
{
    /**
     * Muestra el listado principal con Yajra DataTables y filtros dinámicos.
     */
    public function index(Request $request)
    {
		
        if ($request->ajax()) {

				$estado = $request->query('filtrado', 'pendiente');
				$company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();	

				$query = DatosSincronizarFactura::select([
						'invoices.id as id_factura_original',
						'invoices.facturar',
						'invoices.invoice_number',
						'empresa_sincronizar_facturas.company_id as invoice_company_id', 
						'datos_sincronizar_facturas.factura_fiscal', 
						'datos_sincronizar_facturas.mensaje_error', 
						\DB::raw('CASE WHEN datos_sincronizar_facturas.id IS NULL THEN 0 ELSE datos_sincronizar_facturas.id END as id'),
						\DB::raw('COALESCE(datos_sincronizar_facturas.invoice_id, invoices.id) as invoice_id'),
						\DB::raw("COALESCE(datos_sincronizar_facturas.estado, 'pendiente') as estado"),
						'datos_sincronizar_facturas.created_at',
						'datos_sincronizar_facturas.updated_at',
						'datos_sincronizar_facturas.deleted_at',
					])
					->rightJoin('invoices', 'datos_sincronizar_facturas.invoice_id', '=', 'invoices.id')
					->leftJoin('empresa_sincronizar_facturas', 'empresa_sincronizar_facturas.company_id', '=', 'invoices.company_id')
					->whereIn('invoices.company_id', $company_id)
					->where('invoices.facturar', true)
					->where('invoices.status', '!=', 'Canceled');

				if ($request->query('trashed') === 'true') {
					$query->withTrashed();
				} else {
					$query->where(function ($subQuery) use ($estado) {
						$subQuery->where('datos_sincronizar_facturas.estado', $estado);
						if ($estado === 'pendiente') {
							$subQuery->orWhereNull('datos_sincronizar_facturas.invoice_id');
						}
					});
				}

				$datos = $query->orderBy('invoices.id', 'desc');
			

            return DataTables::eloquent($datos)
                // Estructura visual para los campos JSON auxiliares
				->addColumn('invoice_id', function ($data) {
                    return $data->invoice_number ?? '';
                })
                ->addColumn('info_json', function ($data) {
                    $html = [];
                    if (!empty($data->json_pmpc)) {
                        $html[] = '<strong>PMPC:</strong> <span class="text-success"><i class="fa fa-check-circle"></i></span>';
                    }
                    if (!empty($data->json_afip)) {
                        $html[] = '<strong>AFIP:</strong> <span class="text-success"><i class="fa fa-check-circle"></i></span>';
                    }
                    return !empty($html) ? implode('<br>', $html) : '<span class="text-muted small">' . _lang('Vacío') . '</span>';
                })
				->addColumn('mensaje_error', function ($data) {
					  if (!empty($data->mensaje_error)) {
						 //return nl2br(e($data->mensaje_error));
						 return $data->mensaje_error;
						}
					return '';
                })
                // Insignias de colores según el ENUM del estado o si está eliminado
                ->addColumn('status_badge', function ($data) {
                    if ($data->trashed()) {
                        return '<span class="badge bg-dark text-white">' . _lang('Eliminado') . '</span>';
                    }

                    $badges = [
                        'pendiente'  => 'badge-warning',
                        'procesando' => 'badge-info',
                        'completado' => 'badge-success',
                        'error'      => 'badge-danger',
                    ];
                    
                    $clase = $badges[$data->estado] ?? 'badge-secondary';
                    return '<span class="badge ' . $clase . '">' . ucfirst($data->estado) . '</span>';
                })
                // Conectar con tu archivo parcial de acciones pequeñas
                ->addColumn('action', function ($data) {
                    return view('backend.accounting.datos_sincronizar_factura.partials.actions', compact('data'));
                })
                ->rawColumns(['info_json','mensaje_error', 'status_badge', 'action'])
                ->toJson();
        }

        return view('backend.accounting.datos_sincronizar_factura.index');
    }

    /**
     * Muestra el formulario para una nueva sincronización.
     */
    public function create()
    {
        //return view('backend.accounting.datos_sincronizar_factura.create');
    }

    /**
     * Almacena un registro validando los campos estrictamente.
     */
    public function store(Request $request)
    {
     /*   $validated = $request->validate([
            'invoice_id'     => 'required|integer',
            'json_pmpc'      => 'nullable|json',
            'json_afip'      => 'nullable|json',
            'factura_fiscal' => 'nullable|string|max:100',
            'estado'         => 'required|in:pendiente,procesando,completado,error',
            'mensaje_error'  => 'nullable|string',
        ], [
            'invoice_id.required' => _lang('El ID de la factura es obligatorio.'),
            'estado.required'     => _lang('El estado es un campo requerido.'),
        ]);

        DatosSincronizarFactura::create($validated);

        return redirect()
            ->route('datos_sincronizar_factura.index')
            ->with('success', _lang('Sincronización de factura registrada con éxito.'));
			*/
    }

    /**
     * Muestra el detalle completo ocupando el 100% de la pantalla.
     */
    public function show($id)
    {
        //$sincronizacion = DatosSincronizarFactura::withTrashed()->findOrFail($id);
        //return view('backend.accounting.datos_sincronizar_factura.show', compact('sincronizacion'));
    }

    /**
     * Muestra el formulario de edición.
     */
    public function edit($id)
    {
        $sincronizacion = DatosSincronizarFactura::findOrFail($id);
        return view('backend.accounting.datos_sincronizar_factura.edit', compact('sincronizacion'));
    }

    /**
     * Actualiza el registro en la base de datos.
     */
    public function update(Request $request, $id)
    {
        $sincronizacion = DatosSincronizarFactura::findOrFail($id);

        $validated = $request->validate([
            //'invoice_id'     => 'required|integer',
            'json_pmpc'      => 'nullable|json',
            'json_afip'      => 'nullable|json',
            'factura_fiscal' => 'nullable|string|max:100',
            'estado'         => 'required|in:pendiente,procesando,completado,error',
            'mensaje_error'  => 'nullable|string',
        ]);

        $sincronizacion->update($validated);

        return redirect()
            ->route('datos_sincronizar_factura.index')
            ->with('success', _lang('Sincronización actualizada con éxito.'));
    }

    /**
     * Envía temporalmente el registro a la papelera (Soft Delete).
     */
    public function destroy($id)
    {
     /*  $sincronizacion = DatosSincronizarFactura::findOrFail($id);
        $sincronizacion->delete();

        return redirect()
            ->route('datos_sincronizar_factura.index')
            ->with('success', _lang('Módulo movido a la papelera de reciclaje.'));
			*/
    }

    /**
     * Recupera el registro desde la papelera.
     */
    public function restore($id)
    {
       /* $sincronizacion = DatosSincronizarFactura::onlyTrashed()->findOrFail($id);
        $sincronizacion->restore();

        return redirect()
            ->route('datos_sincronizar_factura.index')
            ->with('success', _lang('Sincronización restaurada con éxito.'));
			*/
    }

    /**
     * Destruye de forma física y permanente la fila en la base de datos.
     */
    public function forceDelete($id)
    {
      /*  $sincronizacion = DatosSincronizarFactura::onlyTrashed()->findOrFail($id);
        $sincronizacion->forceDelete();

        return redirect()
            ->route('datos_sincronizar_factura.index')
            ->with('success', _lang('Registro eliminado definitivamente de la base de datos de manera irreversible.'));
			*/
    }
	
	
	
	public function generar(Request $request, $id)
{
			$invoiceId = $request->query('invoice_id') ?? '0';

			if (!$invoiceId || $invoiceId == 0) {
				return redirect()->back()->with('error', 'Falta el parámetro válido de la factura original para generar el registro.');
			}

			$registro = null; 

			try {
				DB::transaction(function () use ($invoiceId, &$registro) {
					
					$factura = DB::table('invoices as t1')
						->join('contacts as t2', 't2.id', '=', 't1.client_id')
						->where('t1.id', $invoiceId)
						->select([
							't1.*',
							't2.contact_name',
							't2.dni_cuit',
							't2.contact_email'
						])
						->first();
				
					if (!$factura) {
						throw new Exception('La factura seleccionada no existe en el sistema.');
					}

					$items = DB::table('invoices as t1')
						->join('invoice_items as t2', 't2.invoice_id', '=', 't1.id')
						->join('products as t3', 't3.id', '=', 't2.product_id')
						->leftJoin('items as t4', 't4.id', '=', 't3.item_id')
						->where('t1.id', $invoiceId)
						->select([
							't4.item_name',
							't3.nro_interno',
							't3.nro_motor',
							't3.nro_oblea',
							't2.*' 
						])
						->get();	
						
					if ($items->isEmpty()) {
						throw new Exception('La factura seleccionada no tiene ítems asociados.');
					}

					$DatosEstructurado = [
						'invoices'      => (array) $factura,
						'invoice_items' => $items->toArray()
					];

					$registro = DatosSincronizarFactura::where('invoice_id', $invoiceId)->first();
					
					if (!$registro) {
						$registro = new DatosSincronizarFactura();
						$registro->invoice_id = $invoiceId; 
					}
					
					$registro->json_pmpc     = $DatosEstructurado; // Array nativo
					//$registro->estado        = 'procesando';
					$registro->save();
				});

				try {
					$this->enviarAFacturarEnCI3($registro->id);

					return redirect()
						->route('datos_sincronizar_factura.index')
						->with('success', 'El JSON de la factura se ha generado y enviado a CI3 con éxito.');

				} catch (Exception $e) {
					$nueva_linea_historial = "[" . date('Y-m-d H:i:s') . "] " .  $e->getMessage();
					$registro->mensaje_error = $registro->mensaje_error . "\n" . $nueva_linea_historial;
					$registro->save();
					return redirect()
						->back()
						->with('error', 'La factura se guardó localmente, pero falló el envío a CI3: ' . $e->getMessage());
				}

			} catch (Exception $e) {
				return redirect()
					->back()
					->with('error', 'Error al procesar la factura: ' . $e->getMessage());
			}
	}
	
	
public function enviarAFacturarEnCI3($cotizacionId)
{
    //$token="ydVQb2XAijsWfCGdd3aCErrAyty0StzLOR0CiyiqMi9plxjW58VuczTArs5qeyiX";	
	//$url_api="http://ci.test/paternalmotor_flor/api/recibir_de_laravel";		

//    $cotizacion = DatosSincronizarFactura::findOrFail($cotizacionId);
	
	$cotizacion = DatosSincronizarFactura::with('invoice.empresaConfig')->findOrFail($cotizacionId);
	$config = $cotizacion->invoice->empresaConfig ?? null;
	if (!$config) {
		throw new \Exception('No hay configuración de sincronización para la empresa de esta factura.');
	}

	$token   = $config->token;
	$url_api = $config->url;
    
    $datos = [
        'id_sincronalizar' => $cotizacion->id,
        'invoice_id'       => $cotizacion->invoice_id,
        'json_pmpc'        => $cotizacion->json_pmpc
    ];

    try {
        $response = \Illuminate\Support\Facades\Http::withToken($token)
            ->timeout(30) 
            ->acceptJson() 
            ->post($url_api, $datos);

        // if ($response->failed()) {
            // throw new \Exception('CI3 respondió con error HTTP ' . $response->status() . ': ' . $response->body());
        // }
		
		
		if ($response->failed()) {
			$error_msg = '[' . date('Y-m-d H:i:s') . '] CI3 respondió con error HTTP ' . $response->status() . ': ' . $response->body();
				$cotizacion->update([
				'mensaje_error' => trim(($cotizacion->mensaje_error ?? '') . "\n" . $error_msg)
				]);
				return false; 
		}
		
        $resultadoFiscal = $response->json(); 
        $historial_previo = !empty($cotizacion->mensaje_error) ? $cotizacion->mensaje_error . "\n" : '';
        $nuevo_mensaje = $resultadoFiscal['mensaje'] ?? 'Factura procesada correctamente en CI3';
        $nueva_linea_historial = "[" . date('Y-m-d H:i:s') . "] " . $nuevo_mensaje;
        $historial_actualizado = $historial_previo . $nueva_linea_historial;
		$factura_fiscal = $resultadoFiscal['factura_fiscal'] ?? null;
		//$factura_fiscal = !empty(trim((string) $factura_fiscal));
		$estado = $factura_fiscal ? 'completado' : 'pendiente';
        $cotizacion->update([
            'factura_fiscal' => $factura_fiscal,
			'estado'        => $estado,
            'mensaje_error'  => $historial_actualizado 
        ]);
		
        return true;

    } catch (\Exception $e) {
        $historial_previo = !empty($cotizacion->mensaje_error) ? $cotizacion->mensaje_error . "\n" : '';
        $cotizacion->update([
            'mensaje_error' => $historial_previo . "[" . date('Y-m-d H:i:s') . "] ERROR: " . $e->getMessage()
        ]);
        throw $e; 
    }
}
	
	/**
     * Ejecuta la consulta de integración para el botón azul (Consultar).
     * Enlace de origen: datos_sincronizar_factura.consultar
     */
    public function consultar(Request $request, $id)
    {
		/*
        // 1. Validamos que el registro no sea 0 (las facturas no generadas no tienen integración que consultar)
        if ($id == 0 || $id == '0') {
            return redirect()
                ->route('datos_sincronizar_factura.index')
                ->with('error', 'No se puede consultar la integración de una factura que aún no ha sido generada.')
                ->with('flash_danger', 'No se puede consultar la integración de una factura que aún no ha sido generada.');
        }

        // 2. Buscamos el registro de sincronización
        $sincronizacion = DatosSincronizarFactura::find($id);

        if (!$sincincronizacion) {
            return redirect()
                ->route('datos_sincronizar_factura.index')
                ->with('error', 'El registro de sincronización solicitado no existe.')
                ->with('flash_danger', 'El registro de sincronización solicitado no existe.');
        }

        // 3. Consultamos los datos de la factura vinculada mediante un Query Builder limpio
        $factura = DB::table('invoices')
            ->where('id', $sincronizacion->invoice_id)
            ->first();

        // 4. Opcional: Si los datos se leen como texto, los decodificamos para enviarlos estructurados a la vista
        $payloadContenido = json_decode($sincronizacion->json_pmpc);

        // 5. Retornamos la vista dedicada de consulta de integración
        // Ajusta 'backend.accounting...' según la nomenclatura de carpetas de tu proyecto
        return view('backend.accounting.datos_sincronizar_factura.consultar', compact('sincronizacion', 'factura', 'payloadContenido'));
		*/
    }
}
