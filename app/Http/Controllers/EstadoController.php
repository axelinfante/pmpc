<?php

namespace App\Http\Controllers;

use App\Estado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class EstadoController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $estados = Estado::orderBy("id","desc")
            ->get();
        return view('backend.accounting.estado.list',compact('estados'));
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
            return view('backend.accounting.estado.create');
        }else{
            return view('backend.accounting.estado.modal.create');
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
            'estado' => 'required|max:100',

        ]);

        if ($validator->fails()) {
            if($request->ajax()){
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('estado.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }


        $estado = new estado();
        $estado->estado = $request->input('estado');


        $estado->save();

        if(! $request->ajax()){
            return redirect()->route('estado.create')->with('success', _lang('Saved Successfully'));
        }else{
            return response()->json(['result'=>'success','action'=>'store','message'=>_lang('Saved Successfully'),'data'=>$estado, 'table' => '#estados_table']);
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
        $estado = Estado::find($id);
        if(! $request->ajax()){
            return view('backend.accounting.estado.view',compact('estado','id'));
        }else{
            return view('backend.accounting.estado.modal.view',compact('estado','id'));
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
        $estado = Estado::find($id);
        if(! $request->ajax()){
            return view('backend.accounting.estado.edit',compact('estado','id'));
        }else{
            return view('backend.accounting.estado.modal.edit',compact('estado','id'));
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
            'estado' => 'required|max:100',

        ]);

        if ($validator->fails()) {
            if($request->ajax()){
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('estado.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }


        $estado = Estado::find($id);
        $estado->estado = $request->input('estado');

        //$role->company_id = company_id();

        $estado->save();

        if(! $request->ajax()){
            return redirect()->route('estado.index')->with('success', _lang('Updated Successfully'));
        }else{
            return response()->json(['result'=>'success','action'=>'update', 'message'=>_lang('Updated Successfully')
                ,'data'=>$estado, 'table' => '#estados_table']);
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
        $estado = Estado::where("id",$id);
        $estado ->delete();
        return redirect()->route('estado.index')->with('success',_lang('Deleted Successfully'));
    }
}
