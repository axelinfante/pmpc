<?php

namespace App\Http\Controllers;

use App\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
       // $items = Item::orderBy("id","desc")->get();
      //  return view('backend.accounting.item.list',compact('items'));
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
            return view('backend.accounting.item.create');
        }else{
            return view('backend.accounting.item.modal.create');
        }
    }

   
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'item_name' => 'required|max:150|unique:items'
        ]);

        if ($validator->fails()) {
            if($request->ajax()){
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('item.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }
		
		$item = Item::create(
				[
				'item_name'   => $request->item_name,
				'item_type'   => $request->item_type,
				'company_id'  => company_id(),
				'allCar' 	  => $request->allcar ?? Null,
				'activo' 	  => $request->activo ?? 'No'
				]
			);

        
        if(! $request->ajax()){
            return redirect()->route('item.create')->with('success', _lang('Saved Successfully'));
        }else{
            return response()->json(['result'=>'success','action'=>'store','message'=>_lang('Saved Successfully'),'data'=>$item->id]);
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
     /*   //
        $marcaModelo = MarcaModelo::find($id);
        if(! $request->ajax()){
            return view('backend.accounting.marca_modelo.view',compact('marcaModelo','id'));
        }else{
            return view('backend.accounting.marca_modelo.modal.view',compact('marcaModelo','id'));
        }
		*/
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
      /*  $marcaModelo = MarcaModelo::find($id);
        if(! $request->ajax()){
            return view('backend.accounting.marca_modelo.edit',compact('marcaModelo','id'));
        }else{
            return view('backend.accounting.marca_modelo.modal.edit',compact('marcaModelo','id'));
        }
		*/
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
      /*  $validator = Validator::make($request->all(), [
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

*/

/*
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
        }*/
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
		
       $item = Item::where("id",$id)->first();
	   if ($item){
		   $item->activo='No';
		   $item->save();
		   return response()->json(['result' => 'success', 'action' => 'update', 'message' => _lang('Updated sucessfully'), 'data' => $item]);
		}
		
    }

   
}
