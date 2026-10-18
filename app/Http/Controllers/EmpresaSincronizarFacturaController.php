<?php
namespace App\Http\Controllers;
use App\EmpresaSincronizarFactura;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use PDF;
use Carbon\Carbon;


class EmpresaSincronizarFacturaController extends Controller
{
    /**
     * Display a listing of the resource. (LISTAR)
     */
    public function index(Request $request)
    {
		
	  if ($request->ajax()) {
            $query = EmpresaSincronizarFactura::select('empresa_sincronizar_facturas.*');
            if ($request->query('trashed') === 'true') {
                $query->withTrashed();
            }

            $datos = $query->orderBy('empresa_sincronizar_facturas.id', 'desc');

            return DataTables::eloquent($datos)
                ->addColumn('status_badge', function ($data) {
                    if ($data->trashed()) {
                        return '<span class="badge bg-dark text-white">' . _lang('Eliminado') . '</span>';
                    }
                    return '<span class="badge bg-success text-white">' . _lang('Activo') . '</span>';
                })
                ->addColumn('info_json', function ($data) {
                    return '<span class="text-muted small">' . _lang('No aplica') . '</span>';
                }) 
				->addColumn('company_id', function ($data) {
                    return $data->company->business_name ?? '';
                })
                ->addColumn('action', function ($data) {
                    return view('backend.accounting.empresa_sincronizar_factura.partials.actions', compact('data'));
                })
                ->rawColumns(['status_badge', 'info_json', 'action'])
                ->toJson();
        }

        return view('backend.accounting.empresa_sincronizar_factura.index');
    }

    /**
     * Show the form for creating a new resource. (VISTA CREAR)
     */
    public function create()
    {
         //$companies = Company::all();
		 $companies = \App\Company::where('business_name', 'Pentacar')->orwhere('business_name', 'Paternal')->get();
        return view('backend.accounting.empresa_sincronizar_factura.create', compact('companies'));
    }

    /**
     * Store a newly created resource in storage. (GUARDAR)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'token'      => 'required|string|max:255',
            'url'        => 'required|url|max:255',
            'company_id' => 'required|integer|unique:empresa_sincronizar_facturas,id',

        ]);
		
        EmpresaSincronizarFactura::create($validated);

        return redirect()
            ->route('empresa_sincronizar_factura.index')
            ->with('success', 'Registro creado exitosamente.');
    }

    /**
     * Display the specified resource. (VER DETALLE)
     */
    public function show(EmpresaSincronizarFactura $empresaSincronizarFactura)
    {
        return view('backend.accounting.empresa_sincronizar_factura.show', compact('empresaSincronizarFactura'));
    }

    /**
     * Show the form for editing the specified resource. (VISTA EDITAR)
     */
    public function edit(EmpresaSincronizarFactura $empresaSincronizarFactura)
    {
	    $companies = \App\Company::where('business_name', 'Pentacar')->orwhere('business_name', 'Paternal')->get();
        return view('backend.accounting.empresa_sincronizar_factura.edit', compact('empresaSincronizarFactura','companies'));
    }

    /**
     * Update the specified resource in storage. (ACTUALIZAR)
     */
    public function update(Request $request, EmpresaSincronizarFactura $empresaSincronizarFactura)
    {
        $validated = $request->validate([
            'token'      => 'required|string|max:255',
            'url'        => 'required|url|max:255',
	        'company_id' => 'required|integer|unique:empresa_sincronizar_facturas,id,' . $empresaSincronizarFactura->company_id,
        ]);

        $empresaSincronizarFactura->update($validated);

        return redirect()
            ->route('empresa_sincronizar_factura.index')
            ->with('success', 'Registro actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage. (ELIMINAR / SOFT DELETE)
     */
    public function destroy(EmpresaSincronizarFactura $empresaSincronizarFactura)
    {
        // Esto ejecutará el Soft Delete gracias al trait del modelo
        $empresaSincronizarFactura->delete();

        return redirect()
            ->route('empresa_sincronizar_factura.index')
            ->with('success', 'Registro eliminado (Soft Delete) correctamente.');
    }
	
	
	 /**
     * Restaura una configuración que fue eliminada lógicamente (Soft Delete).
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function restore($id)
    {
        // Buscamos el registro únicamente dentro de los eliminados
        $configuracion = EmpresaSincronizarFactura::onlyTrashed()->findOrFail($id);
        
        // Restauramos el registro (asigna NULL a la columna deleted_at)
        $configuracion->restore();

        return redirect()
            ->route('empresa_sincronizar_factura.index')
            ->with('success', _lang('Configuración sincronización restaurada con éxito.'));
    }

    /**
     * Elimina permanentemente una configuración de la base de datos (Force Delete).
     *
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function forceDelete($id)
    {
        // Buscamos el registro únicamente dentro de los eliminados
        $configuracion = EmpresaSincronizarFactura::onlyTrashed()->findOrFail($id);
        
        // Borramos físicamente la fila de la base de datos
        $configuracion->forceDelete();

        return redirect()
            ->route('empresa_sincronizar_factura.index')
            ->with('success', _lang('Configuración eliminada de forma permanente.'));
    }
	
	
	
	
	
	
	
	
}
