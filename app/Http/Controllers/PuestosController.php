<?php

namespace App\Http\Controllers;
use App\Puesto;
use Throwable;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

//use Illuminate\Routing\Controller;
class PuestosController extends Controller
{
	 
	 public function index()
    {
		$company_id = empty(session('cia')) ? company_id_arr() : company_id_arr();
 	    $registro = Puesto::with('asignado')->with('company')
		->whereIn('company_id', $company_id)
		->orderBy("company_id", "asc")
		->get();

		return response()->json(['data' => $registro]);
    }
	
	  /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'puesto_id' => 'required',
            'puesto' => 'required|max:10',
            'user_id' => 'required',
            'predeterminada' => 'required',
            'activo' => 'required',
            'company_id' => 'required',
        ]);
          
       $puesto= Puesto::updateOrCreate([
                   'id' => $request->puesto_id
                ],
                [
                    'predeterminada' => $request->predeterminada, 
                    'activo' => $request->activo,
					'puesto' => $request->puesto,'user_id' => $request->user_id,'company_id' => $request->company_id
                ]);   
		if ($request->predeterminada==1){
			Puesto::where('id', '<>', $puesto->id)->where('company_id', '=', $puesto->company_id)->update(['predeterminada' => false]);
		}				
       
        return response()->json(['success'=>'Puesto grabado correctamente.']);
    }


/*$flight = Flight::updateOrCreate(
    ['departure' => 'Oakland', 'destination' => 'San Diego'], // Search attributes
    ['price' => 99, 'discounted' => 1] // Values to update or set on creation
);*/
   
	 /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function destroy($id): JsonResponse
    {
        Puesto::find($id)->delete();
        return response()->json(['success'=>'Puesto eliminado.']);
    }


}
