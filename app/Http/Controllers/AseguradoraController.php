<?php

namespace App\Http\Controllers;

use App\Aseguradora;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AseguradoraController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $aseguradoras = Aseguradora::orderBy("id","desc")
            ->get();
        return view('backend.accounting.aseguradora.list',compact('aseguradoras'));
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
            return view('backend.accounting.aseguradora.create');
        }else{
            return view('backend.accounting.aseguradora.modal.create');
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
            'nombre' => 'required|max:50',

        ]);

        if ($validator->fails()) {
            if($request->ajax()){
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('aseguradora.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }


        $aseguradora = new Aseguradora();
        $aseguradora->nombre = $request->input('nombre');


        $aseguradora->save();

        if(! $request->ajax()){
            return redirect()->route('aseguradora.create')->with('success', _lang('Saved Successfully'));
        }else{
            return response()->json(['result'=>'success','action'=>'store','message'=>_lang('Saved Successfully'),'data'=>$aseguradora, 'table' => '#aseguradoras_table']);
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
        $aseguradora = Aseguradora::find($id);
        if(! $request->ajax()){
            return view('backend.accounting.aseguradora.view',compact('aseguradora','id'));
        }else{
            return view('backend.accounting.aseguradora.modal.view',compact('aseguradora','id'));
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
        $aseguradora = Aseguradora::find($id);
        if(! $request->ajax()){
            return view('backend.accounting.aseguradora.edit',compact('aseguradora','id'));
        }else{
            return view('backend.accounting.aseguradora.modal.edit',compact('aseguradora','id'));
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
            'nombre' => 'required|max:50',

        ]);

        if ($validator->fails()) {
            if($request->ajax()){
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('aseguradora.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }


        $aseguradora = Aseguradora::find($id);
        $aseguradora->nombre = $request->input('nombre');

        //$role->company_id = company_id();

        $aseguradora->save();

        if(! $request->ajax()){
            return redirect()->route('aseguradora.index')->with('success', _lang('Updated Successfully'));
        }else{
            return response()->json(['result'=>'success','action'=>'update', 'message'=>_lang('Updated Successfully')
                ,'data'=>$aseguradora, 'table' => '#aseguradoras_table']);
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
        $aseguradora = Aseguradora::where("id",$id);
        $aseguradora ->delete();
        return redirect()->route('aseguradora.index')->with('success',_lang('Deleted Successfully'));
    }
}
