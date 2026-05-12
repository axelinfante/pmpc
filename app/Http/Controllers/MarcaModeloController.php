<?php

namespace App\Http\Controllers;

use App\Marca;
use App\MarcaModelo;
use App\Modelo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Rules\SimilarNameRule;
use DataTables;

class MarcaModeloController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
	if ($request->ajax()) {
		//$marca_modelos = MarcaModelo::orderBy("id","desc")->with('marca')->get();
		 $data = MarcaModelo::orderBy("id","desc")->with('marca');
            return Datatables::of($data)
					->addColumn('marca', function ($data) {
						return $data->marca->marca ?? ''; 
                    })
					->addColumn('modelo', function ($data) {
						return $data->modelo->modelo ?? ''; 
                    })
					->addColumn('action', function ($data) {
						$result=' <div class="dropdown">
								  <button class="btn btn-primary dropdown-toggle btn-xs" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> ' . _lang("Action") . '
								  <i class="fas fa-angle-down"></i>
								  </button>
								  <form action="'. action("MarcaModeloController@destroy", $data->id) .'" method="post">
									'. csrf_field() .'
									<input name="_method" type="hidden" value="DELETE">
									
									<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
										<a href="'. action('MarcaModeloController@edit', $data->id) .'" data-title="'. _lang
										('Update') .'" class="dropdown-item ajax-modal"><i class="mdi
										mdi-pencil"></i>'. _lang('Edit') .'</a>
										<a href="'. action('MarcaModeloController@show', $data->id) .'" data-title="'. _lang
										('View') .'" class="dropdown-item ajax-modal"><i class="mdi
										mdi-eye"></i>
											'. _lang('View') .'</a>
										<button class="btn-remove dropdown-item" type="submit"><i class="mdi mdi-delete"></i> '. _lang('Delete') .'</button>
									</div>
								  </form>
								</div>';
						return $result;
					})
                    ->rawColumns(['action'])
                    ->make(true);
		}
		//$marca_modelos = MarcaModelo::orderBy("id","desc")->with('marca')->limit(10)->get();
		$marca_modelos = null;
        return view('backend.accounting.marca_modelo.list',compact('marca_modelos'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        //
        if( ! $request->ajax()){
            return view('backend.accounting.marca_modelo.create');
        }else{
            return view('backend.accounting.marca_modelo.modal.create');
        }
    }

    public function editModelo($id)
    {
        $marcaModelo = MarcaModelo::with('modelo')->find($id);
        $modelo = $marcaModelo->modelo;

      
        return view('backend.accounting.marca_modelo.modal.editModelo',compact('id','modelo'));
      
    }
    public function updatedModelo(Request $request) 
    {
        $id = $request->id;
        $modeloName = $request->modelo;

        $modelo = Modelo::find($id);
        $modelo->modelo = $modeloName;
        $modelo->save();

        return response()->json(['result'=>'success','action'=>'updated','message'=>_lang('Saved Successfully'),'data'=>$modelo, 'table' => '#modelo']);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'marca' => [
					'required',
					'max:50',
					'unique:marcas,marca',
					new SimilarNameRule('marcas', 'marca')
				],'modelo' => [
					'required',
					'max:50',
					'unique:modelos,modelo',
					new SimilarNameRule('modelos', 'modelo') // Ajustado a tu tabla y columna
				],
        ]);
        if ($validator->fails()) {
            if($request->ajax()){
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('marcamodelo.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }

        //comparar si ya existe la marca
        $marca = Marca::whereRaw('lower(marca) = ? ',strtolower(trim($request->input('marca'))))->first();
        if(!isset($marca)){
            $marca = new Marca();
            $marca->marca = trim($request->input('marca'));
            $marca->save();
        }

        $model = new Modelo();
        $model->modelo = $request->input('modelo');
        $model->save();

        $marca_model = new MarcaModelo;
        $marca_model->idModelo = $model->id;
        $marca_model->idMarca= $marca->id;
        $marca_model->save();




        if(! $request->ajax()){
            return redirect()->route('marcamodelo.create')->with('success', _lang('Saved Successfully'));
        }else{
            return response()->json(['result'=>'success','action'=>'store','message'=>_lang('Saved Successfully'),'data'=>$marca_model, 'table' => '#marca_modelos_table']);
        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, $id)
    {
        //
        $marcaModelo = MarcaModelo::find($id);
        if(! $request->ajax()){
            return view('backend.accounting.marca_modelo.view',compact('marcaModelo','id'));
        }else{
            return view('backend.accounting.marca_modelo.modal.view',compact('marcaModelo','id'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        //
        $marcaModelo = MarcaModelo::find($id);
        if(! $request->ajax()){
            return view('backend.accounting.marca_modelo.edit',compact('marcaModelo','id'));
        }else{
            return view('backend.accounting.marca_modelo.modal.edit',compact('marcaModelo','id'));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $validator = Validator::make($request->all(), [
            'marca' => 'required|max:50',
            'modelo' => 'required',
        ]);

        if ($validator->fails()) {
            if($request->ajax()){
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('marca_modelo.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }




        $marcaModelo = MarcaModelo::find($id);
        $marca = Marca::find($marcaModelo->idMarca);
        if(strtolower( $marca->marca) != strtolower( tirm($request->input('marca'))) ){
            $marcaNueva = new Marca();
            $marcaNueva->marca = trim($request->input('marca'));
            $marcaNueva->save();
        }else{
            $marca->marca = trim($request->input('marca'));
            $marca->save();
        }

        $modelo = Modelo::find($marcaModelo->idModelo);
        $modelo->modelo = $request->input('modelo');
        $modelo->save();
        //$role->company_id = company_id();

        //$marcaModelo->save();

        if(! $request->ajax()){
            return redirect()->route('marca_modelo.index')->with('success', _lang('Updated Successfully'));
        }else{
            return response()->json(['result'=>'success','action'=>'update', 'message'=>_lang('Updated Successfully'),'data'=>$marcaModelo, 'table' => '#marca_modelo_table']);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $marcaModelo = MarcaModelo::where("id",$id)->first();
        Modelo::where('id',$marcaModelo->idModelo)->delete();
        $marcaModelo = MarcaModelo::where("id",$id);
        $marcaModelo->delete();
        return redirect()->route('marcamodelo.index')->with('success',_lang('Deleted Successfully'));
    }

    public function modelosAjax($idMarca=0)
    {
        $idMarca=$idMarca ?? 0;
        $MarcaModelo = MarcaModelo::where('idMarca',$idMarca)->with('modelo')->get();
        return response()->json($MarcaModelo,200);
    }

    public function importMarcaModelo()
    {
        //cargar archivo storage  public disk
        //$file = storage_path(). '/app/public/estados.xlsx' ;
        $file = storage_path(). '/app/public/marcas_modelos.xlsx' ;

        $spreadsheet =  IOFactory::load($file);
        $totalDeHojas = $spreadsheet->getSheetCount();

        $datos = [];
        for ($indiceHoja = 0; $indiceHoja < $totalDeHojas; $indiceHoja++) {

            # Obtener hoja en el índice que vaya del ciclo
            $hojaActual = $spreadsheet->getSheet($indiceHoja);

            $hojaActual->getRowIterator();
            foreach($hojaActual->getRowIterator(2) as $fila){
                foreach($fila->getCellIterator('a', 'a') as $celda){
                    //$res = $celda->getValue();
                    //dd($fila->getCellIterator('a', 'a'));
                    //$resSinNumeros = $res ;preg_replace('/[0-9]+/', '', $res);

                    //dd( $celda->getColumn());
                    if(strpos ('a', $celda->getColumn())){
                        //$datos[$resSinNumeros] = $resSinNumeros;
                    }
                    $fila = $celda->getRow();
                    $coordenadas = 'B' .$fila;
                    $coordenadasMar = 'A' .$fila;
                    $modelo =$hojaActual->getCell( $coordenadas);
                    $marca =$hojaActual->getCell( $coordenadasMar);
                    //dd();

                    $datos[] = [
                        'marca' => $marca->getValue(),
                        'modelo' => $modelo->getValue()
                    ];



                }

            }


            # Imprimir
            //echo "En <strong>$coordenadas</strong> tenemos el valor <strong>$valorRaw</strong>. ";
//            echo "Formateado es: <strong>$valorFormateado</strong>. ";
//            echo "Calculado es: <strong>$valorCalculado</strong><br><br>";

        }

        $i = 0;
        foreach ($datos as $dat) :


            if(!empty($dat['marca']) && !empty($dat['modelo'])) {


                try{
                    DB::beginTransaction();
                    $modelo = new Modelo();

                    $modelo-> modelo = $dat['modelo'];
                    $modelo->save();



                    $marca = Marca::whereRaw('lower(marca) = ? ',strtolower(trim($dat['marca'])))->first();
                    if(!isset($marca)){
                        $marca = new Marca();

                        $marca-> marca = $dat['marca'];
                        $marca->save();
                    }

                    $marca_modelo = new MarcaModelo();

                    $marca_modelo-> idModelo = $modelo->id;
                    $marca_modelo-> idMarca = $marca->id;
                    $marca_modelo->save();
                    DB::commit();
                }catch(\PDOException $e) {
                    throw ($e);
                    //DB::rollBack();
                }
            }
            //$estados = new Estado();

        endforeach;

    }
}
