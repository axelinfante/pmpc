<?php
namespace App\Http\Controllers;

use App\Modelo;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Validator;
use App\Rules\SimilarNameRule;
use Illuminate\Validation\Rule;

class ModelosController extends Controller
{
    function __construct()
    {
        /*$this->middleware('permission:ver-modelo|crear-modelo|editar-modelo|eliminar-modelo', ['only' => ['index']]);
        $this->middleware('permission:crear-modelo', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-modelo', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-modelo', ['only' => ['destroy']]);*/
		//$this->middleware('can:editar-modelo', ['only' => ['edit', 'update']]);
    }
	
     public function index(Request $request)
    {
		if ($request->ajax()) {
			$query = Modelo::query();

			return DataTables::of($query)
            ->editColumn('activo', function ($row) use ($request) {
					
					if (!isset($request->exportar)){
                        return view('backend.accounting.modelo.include.activo', ['data' => $row]);
                    }
                    return $row->activo ?? "";
            })
            ->addColumn('action', function($row) {
					return '<div class="dropdown">
                            <button class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown">'. _lang('Action') .'
                                <i class="fa fa-angle-down"></i>
                            </button>
                            <div class="dropdown-menu" style = "z-index: 10000; position: relative;">
                                <a class="dropdown-item ajax-modal" href="'.route('modelos.edit', $row->id).'" data-title="'. _lang
										('Update') .'" data-reload="false"><i class="fas fa-edit"></i>Editar</a>
								<!-- Formulario de borrado asíncrono -->
								<form action="'.route('modelos.destroy', $row->id).'" method="post">
									'.csrf_field().'
									<input name="_method" type="hidden" value="DELETE">
									<button type="button" class="dropdown-item btn-delete">
										<i class="fas fa-trash-alt"></i> '._lang('Deshabilitar').'
									</button>
								</form>
                            </div>
                        </div>';
            })->filterColumn('activo', function ($query, $keyword) {
                    if ($keyword == "Si") {
                        $query->where('activo', "Si");
                    } elseif ($keyword == "No") {
                        $query->where('activo', "No")->orwherenull('activo');
                    }
                })
            ->rawColumns(['modelo', 'action'])
            ->make(true);
    }
		
        return view('backend.accounting.modelo.index');
    }

   

    // GET /modelos/create
    public function create()
    {
        return view('backend.accounting.modelo.modal.create');
    }

	 // POST /modelos
	public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
					'modelo' => [
					'required',
					'max:150',
					'unique:modelos,modelo',
					new SimilarNameRule('modelos', 'modelo') // Ajustado a tu tabla y columna
				],			
				]);

        if ($validator->fails()) {
            if($request->ajax()){
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('modelos.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }
		
			    // Crear la modelo
    $modelo = Modelo::create($request->only('modelo', 'activo'));

        if(! $request->ajax()){
            return redirect()->route('modelos.create')->with('success', _lang('Saved Successfully'));
        }else{
			 return response()->json([
            'result' => 'success',
            'action' => 'store',
            'message' => _lang('Saved Successfully'),
            'data' => $modelo->id
			]);
        }
 }
	

    // GET /modelos/{modelo}/edit
    public function edit(Modelo $modelo)
    {
        return view('backend.accounting.modelo.modal.edit', compact('modelo'));
    }

    // PUT/PATCH /modelos/{modelo}
   public function update(Request $request, Modelo $modelo)
{
	$validator = Validator::make($request->all(), [
        'modelo' => [
            'required',
            'max:150',
            Rule::unique('modelos', 'modelo')->ignore($modelo->id),
            new SimilarNameRule('modelos', 'modelo', $modelo->id)
        ],
    ]);

    if ($validator->fails()) {
        if($request->ajax()){
            return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
        }
        return redirect()->back() 
            ->withErrors($validator)
            ->withInput();
    }
	
	 $modelo->update($request->only('modelo', 'activo'));
	 
	  if(!$request->ajax()){
            return redirect()->route('modelos.index')->with('success', _lang('Saved Successfully'));
        }else{
			
			 return response()->json([
				'result' => 'success',
				'action' => 'update', 
				'message' => "Registro actualizado correctamente...",
				'data' => $modelo->id
			]);
        }

}

    // DELETE /modelos/{modelo}
		public function destroy(Modelo $modelo)
		{
			$nuevoEstado = $modelo->activo == "Si" ? "No" : "Si";
			$modelo->activo = $nuevoEstado;
			$modelo->save();
			//$modelo->update(['activo' => $nuevoEstado]);
			$mensaje = $nuevoEstado == "No" ? _lang('Disabled Successfully') : _lang('Enabled Successfully');
			return response()->json([
				'result' => 'success',
				'action' => 'delete', // Mantener 'delete' para que tu JS detecte la acción
				'message' => $mensaje,
				'data' => $modelo->id
			]);
		}

 public function actualizaActivo(Request $request)
    {
        $id = $request->id;

        $modelo = modelo::find($id);
		 if (!$modelo) {
            return back()->with('error', _lang('Sorry, Car not found !'));
        }
		
		if (isset($request->activo)) {
			$modelo->activo = $request->activo;
		}
		
        $modelo->save();
		
       return response()->json([
				'result' => 'success',
				'action' => 'update', // Mantener 'delete' para que tu JS detecte la acción
				'message' => "Registro actualizado correctamente...",
				'data' => $modelo->id
			]);
    }		


public function buscarAjax(Request $request)
{
    $search = $request->input('q');
    // Limitamos a 30 resultados para mantener la consulta ultra veloz
    $modelos = Modelo::where('activo', 'Si')
        ->where('modelo', 'LIKE', "%{$search}%")
        ->limit(30) 
        ->get(['id', 'modelo']);

    // Estructuramos la respuesta con el formato exacto requerido por Select2
    $formatted = $modelos->map(function ($item) {
        return [
            'id' => $item->id,
            'text' => $item->modelo
        ];
    });

    return response()->json($formatted);
}
	
		
}
