<?php

namespace App\Http\Controllers;

use App\Provincia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProvinciaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
        $provincias = Provincia::orderBy("id","desc")
            ->get();
        return view('backend.accounting.provincia.list',compact('provincias'));
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
            return view('backend.accounting.provincia.create');
        }else{
            return view('backend.accounting.provincia.modal.create');
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
            'provincia' => 'required|max:100',

        ]);

        if ($validator->fails()) {
            if($request->ajax()){
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('provincia.create')
                    ->withErrors($validator)
                    ->withInput();
            }
        }


        $provincia = new provincia();
        $provincia->provincia = $request->input('provincia');


        $provincia->save();

        if(! $request->ajax()){
            return redirect()->route('provincia.create')->with('success', _lang('Saved Successfully'));
        }else{
            return response()->json(['result'=>'success','action'=>'store','message'=>_lang('Saved Successfully'),'data'=>$provincia, 'table' => '#provincias_table']);
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
        $provincia = Provincia::find($id);
        if(! $request->ajax()){
            return view('backend.accounting.provincia.view',compact('provincia','id'));
        }else{
            return view('backend.accounting.provincia.modal.view',compact('provincia','id'));
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
        $provincia = Provincia::find($id);
        if(! $request->ajax()){
            return view('backend.accounting.provincia.edit',compact('provincia','id'));
        }else{
            return view('backend.accounting.provincia.modal.edit',compact('provincia','id'));
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
            'provincia' => 'required|max:100',

        ]);

        if ($validator->fails()) {
            if($request->ajax()){
                return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);
            }else{
                return redirect()->route('provincia.edit', $id)
                    ->withErrors($validator)
                    ->withInput();
            }
        }


        $provincia = Provincia::find($id);
        $provincia->provincia = $request->input('provincia');

        //$role->company_id = company_id();

        $provincia->save();

        if(! $request->ajax()){
            return redirect()->route('provincia.index')->with('success', _lang('Updated Successfully'));
        }else{
            return response()->json(['result'=>'success','action'=>'update', 'message'=>_lang('Updated Successfully')
                ,'data'=>$provincia, 'table' => '#provincias_table']);
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
        $provincia = Provincia::where("id",$id);
        $provincia ->delete();
        return redirect()->route('provincia.index')->with('success',_lang('Deleted Successfully'));
    }
}
