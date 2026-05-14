<?php
namespace App\Http\Controllers;

use App\Marca;
use App\Modelo;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Validator;
use App\Rules\SimilarNameRule;
use Illuminate\Validation\Rule;

class MarcasController extends Controller
{
    function __construct()
    {
        /*$this->middleware('permission:ver-marca|crear-marca|editar-marca|eliminar-marca', ['only' => ['index']]);
        $this->middleware('permission:crear-marca', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-marca', ['only' => ['edit', 'update']]);
        $this->middleware('permission:eliminar-marca', ['only' => ['destroy']]);*/
		//$this->middleware('can:editar-marca', ['only' => ['edit', 'update']]);
    }
	
    // GET /marcas
    public function index(Request $request)
    {
		if ($request->ajax()) {
			$query = Marca::with('modelos');

        // Filtro 1: Estado Activo de la marca
        if ($request->filled('activo')) {
            $query->where('marcas.activo', $request->activo);
        }

        // Filtro 2: Filtrado eficiente por tabla intermedia pivote
        if ($request->filled('modelo_id')) {
            $query->whereHas('modelos', function ($q) use ($request) {
                $q->where('modelos.id', $request->modelo_id);
            });
        }

        return DataTables::of($query)
            ->editColumn('modelo', function ($row) use ($request) {
                // Renderizado optimizado de los badges que creamos previamente
                if (!isset($request->exportar)) {
                    return $row->modelos->map(function ($item) {
                        return '<span class="badge badge-info">' . e($item->modelo) . '</span>';
                    })->implode(' ');
                }
                return $row->modelos->pluck('modelo')->implode(', ');
			})->editColumn('activo', function ($row) use ($request) {
					
					if (!isset($request->exportar)){
                        return view('backend.accounting.marca.include.activo', ['data' => $row]);
                    }
                    return $row->activo ?? "";
            })
            ->addColumn('action', function($row) {
					return '<div class="dropdown">
                            <button class="btn btn-primary btn-xs dropdown-toggle" type="button" data-toggle="dropdown">'. _lang('Action') .'
                                <i class="fa fa-angle-down"></i>
                            </button>
                            <div class="dropdown-menu" style = "z-index: 10000; position: relative;">
                                <a class="dropdown-item ajax-modal" href="'.route('marcas.edit', $row->id).'" data-title="'. _lang
										('Update') .'" data-reload="false"><i class="fas fa-edit"></i>Editar</a>
								<!-- Formulario de borrado asíncrono -->
								<form action="'.route('marcas.destroy', $row->id).'" method="post">
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
		
        return view('backend.accounting.marca.index');
    }

    // GET /marcas/create
    public function create()
    {
        return view('backend.accounting.marca.modal.create');
    }

	 // POST /marcas
	public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
					'marca' => [
					'required',
					'max:50',
					'unique:marcas,marca',
					new SimilarNameRule('marcas', 'marca') // Ajustado a tu tabla y columna
				],
				'modelos' => 'required|array',
				'modelos.*' => 'exists:modelos,id'
				]);

        if ($validator->fails()) {
            if($request->ajax()){
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('marcas.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }
		
			    // Crear la marca
    $marca = Marca::create($request->only('marca', 'activo'));
    // Relacionar en la tabla pivote (marca_modelos)
    $marca->modelos()->attach($request->modelos);



        if(! $request->ajax()){
            return redirect()->route('marcas.create')->with('success', _lang('Saved Successfully'));
        }else{
			 return response()->json([
            'result' => 'success',
            'action' => 'store',
            'message' => _lang('Saved Successfully'),
            'data' => $marca->id
			]);
        }
 }
	

    // GET /marcas/{marca}/edit
    public function edit(Marca $marca)
    {
        return view('backend.accounting.marca.modal.edit', compact('marca'));
    }

    // PUT/PATCH /marcas/{marca}
   public function update(Request $request, Marca $marca)
{
	$validator = Validator::make($request->all(), [
        'marca' => [
            'required',
            'max:50',
            Rule::unique('marcas', 'marca')->ignore($marca->id),
            new SimilarNameRule('marcas', 'marca', $marca->id)
        ],'modelos' => 'required|array',
        'modelos.*' => 'exists:modelos,id',
    ]);

    if ($validator->fails()) {
        if($request->ajax()){
            return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
        }
        return redirect()->back() 
            ->withErrors($validator)
            ->withInput();
    }
	
	 $marca->update($request->only('marca', 'activo'));
	 $marca->modelos()->sync($request->modelos);
	 
	  if(!$request->ajax()){
            return redirect()->route('marcas.index')->with('success', _lang('Saved Successfully'));
        }else{
			
			 return response()->json([
				'result' => 'success',
				'action' => 'update', 
				'message' => "Registro actualizado correctamente...",
				'data' => $marca->id
			]);
        }

}

    // DELETE /marcas/{marca}
		public function destroy(Marca $marca)
		{
			$nuevoEstado = $marca->activo == "Si" ? "No" : "Si";
			$marca->activo = $nuevoEstado;
			$marca->save();
			//$marca->update(['activo' => $nuevoEstado]);
			$mensaje = $nuevoEstado == "No" ? _lang('Disabled Successfully') : _lang('Enabled Successfully');
			return response()->json([
				'result' => 'success',
				'action' => 'delete', // Mantener 'delete' para que tu JS detecte la acción
				'message' => $mensaje,
				'data' => $marca->id
			]);
		}

 public function actualizaActivo(Request $request)
    {
        $id = $request->id;

        $marca = Marca::find($id);
		 if (!$marca) {
            return back()->with('error', _lang('Sorry, Car not found !'));
        }
		
		if (isset($request->activo)) {
			$marca->activo = $request->activo;
		}
		
        $marca->save();
		
       return response()->json([
				'result' => 'success',
				'action' => 'update', // Mantener 'delete' para que tu JS detecte la acción
				'message' => "Registro actualizado correctamente...",
				'data' => $marca->id
			]);
    }

// Dentro de ProductController.php
	public function createLinea()
	{
		return view('backend.accounting.marca.modal.createlinea');
	}

	public function storelinea(Request $request)
	{
		 $validator = Validator::make($request->all(), [
					'marca' => [
					'required',
					'max:50',
					'unique:marcas,marca',
					new SimilarNameRule('marcas', 'marca') // Ajustado a tu tabla y columna
				],	]);

        if ($validator->fails()) {
            if($request->ajax()){
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('marcas.createLinea')
                    ->withErrors($validator)
                    ->withInput();
            }
        }
		
			    // Crear la marca
		$marca = Marca::create($request->only('marca'));
		
		 if(! $request->ajax()){
            return redirect()->route('marcas.createLinea')->with('success', _lang('Saved Successfully'));
        }else{
			 return response()->json([
            'result' => 'success',
            'action' => 'store',
            'message' => _lang('Saved Successfully'),
            'id' => $marca->id,
            'marca' => $marca->marca
			]);
			
        }
		

	}
	
	
	public function createMarcaModeloLinea(Request $request)
	{
		
		 $idMarca = $request->query('idMarca');
		 $marca = Marca::find($idMarca);
		 if (!$marca) {
            return back()->with('error', _lang('Sorry, Car not found !'));
         }
		return view('backend.accounting.marca.modal.createlineaModelo', compact('marca'));
	}
	
	
	
	public function storeMarcaModeloLinea(Request $request)
{
    $validator = Validator::make($request->all(), [
        'idMarca' => 'required|integer|exists:marcas,id',
        'modelo'  => [
            'required',
            'max:150',
            new SimilarNameRule('modelos', 'modelo'), // Valida nombres sospechosamente parecidos
        ],
    ], [
        'idMarca.required' => 'La marca es obligatoria.',
        'idMarca.exists'   => 'La marca seleccionada no es válida.',
        'modelo.required'  => 'El nombre del modelo es obligatorio.',
        'modelo.max'       => 'El nombre del modelo no puede superar los 150 caracteres.',
    ]); 

    if ($validator->fails()) {
        if ($request->ajax()) {
            return response()->json([
                'result'  => 'error', 
                'message' => $validator->errors()->all()
            ]);
        }
        return redirect()->back()->withErrors($validator)->withInput();
    }
    
    $modelo = Modelo::firstOrCreate([
        'modelo' => $request->modelo
    ]);
   
    $marca = Marca::find($request->idMarca);
    
    if ($marca->modelos()->where('idModelo', $modelo->id)->exists()) {
        return response()->json([
            'result'  => 'error',
            'message' => ['Este modelo ya se encuentra asociado a la marca seleccionada.']
        ]);
    }

    $marca->modelos()->attach($modelo->id);

    if ($request->ajax()) {
        return response()->json([
            'result'  => 'success',
            'action'  => 'store',
            'message' => _lang('Saved Successfully'),
            'id'      => $modelo->id,       
            'modelo'  => $modelo->modelo,   
            'idMarca' => $marca->id         
        ]);
    }

    return redirect()->back()->with('success', _lang('Saved Successfully'));
}


	
}
