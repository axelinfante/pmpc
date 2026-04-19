<?php

namespace App\Http\Controllers;

use App\Lugar_entregas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LugarEntregaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $lugar_entregas = Lugar_entregas::orderBy("id","desc")
            ->get();
        return view('backend.accounting.lugar_entrega.list',compact('lugar_entregas'));
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
            return view('backend.accounting.lugar_entrega.create');
        }else{
            return view('backend.accounting.lugar_entrega.modal.create');
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
        //
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|max:100',

        ]);

        if ($validator->fails()) {
            if($request->ajax()){
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('lugar_entrega.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }


        $lugar_entrega = new Lugar_entregas();
        $lugar_entrega->nombre = $request->input('nombre');


        $lugar_entrega->save();

        if(! $request->ajax()){
            return redirect()->route('lugar_entrega.create')->with('success', _lang('Saved Successfully'));
        }else{
            return response()->json(['result'=>'success','action'=>'store','message'=>_lang('Saved Successfully'),'data'=>$lugar_entrega, 'table' => '#lugar_entregas_table']);
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
        $lugar_entrega = Lugar_entregas::find($id);
        if(! $request->ajax()){
            return view('backend.accounting.lugar_entrega.view',compact('lugar_entrega','id'));
        }else{
            return view('backend.accounting.lugar_entrega.modal.view',compact('lugar_entrega','id'));
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
        $lugar_entrega = Lugar_entregas::find($id);
        if(! $request->ajax()){
            return view('backend.accounting.lugar_entrega.edit',compact('lugar_entrega','id'));
        }else{
            return view('backend.accounting.lugar_entrega.modal.edit',compact('lugar_entrega','id'));
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
            'nombre' => 'required|max:100',

        ]);

        if ($validator->fails()) {
            if($request->ajax()){
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('lugarentrega.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }


        $lugar_entrega = Lugar_entregas::find($id);
        $lugar_entrega->nombre = $request->input('nombre');

        //$role->company_id = company_id();

        $lugar_entrega->save();

        if(! $request->ajax()){
            return redirect()->route('lugar_entrega.index')->with('success', _lang('Updated Successfully'));
        }else{
            return response()->json(['result'=>'success','action'=>'update', 'message'=>_lang('Updated Successfully')
                ,'data'=>$lugar_entrega, 'table' => '#lugar_entregas_table']);
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
        $lugar_entrega = Lugar_entregas::where("id",$id);
        $lugar_entrega ->delete();
        return redirect()->route('lugarentrega.index')->with('success',_lang('Deleted Successfully'));
    }
}
