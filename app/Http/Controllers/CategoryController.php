<?php

namespace App\Http\Controllers;

use App\Categoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Categoria::all();

        return view('backend.accounting.categories.list',compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('backend.accounting.categories.modal.create');
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
            'nombre' => 'required',
            'color' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);

        }

        $category = new Categoria;
        $category->nombre = $request->nombre;
        $category->color = $request->color;
        $category->save();

        return response()->json(['result'=>'success','action'=>'update', 'message'=>_lang('Save sucessfully'),
            'data'=>$category]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {

        $category = Categoria::find($id);
        return view('backend.accounting.categories.modal.edit',compact('id','category'));
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
            'nombre' => 'required',
            'color' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result'=>'error','message'=>$validator->errors()->all()]);

        }

        $category = Categoria::find($id);
        $category->nombre = $request->nombre;
        $category->color = $request->color;
        $category->save();

        return response()->json(['result'=>'success','action'=>'update', 'message'=>_lang('Updated sucessfully'),'data'=>$category]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        Categoria::find($id)->delete();
        return redirect()->back()->with('success',_lang('Deleted sucessfully'));
    }
}
