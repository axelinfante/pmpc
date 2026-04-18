<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use OwenIt\Auditing\Models\Audit;
use DataTables;

class ActivityLogController extends Controller
{

    function __construct()
    {
        //$this->middleware('permission:ver-registro-actividad', ['only' => ['index']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
		if ($request->ajax()) {
			
			$data  = \OwenIt\Auditing\Models\Audit::with('user')->orderBy('created_at', 'desc');
			 
            return Datatables::eloquent($data)
                ->addIndexColumn()
				->addColumn('model', function ($data) {
					return "$data->auditable_type (id: $data->auditable_id )";
				})
				->addColumn('usuario', function ($data) {
					return $data->user->name ?? '';
				})
				->addColumn('valores_ant', function ($data) {
					$datos='<table>';
                    foreach($data->old_values as $attribute => $value){
                      $datos.='<tr>
                        <td><b>'.$attribute .'</b></td>
                        <td>'. $value .'</td>
                      </tr>';
                    }
                  $datos.= '</table>';
					return $datos;
				})
				->addColumn('valores_nue', function ($data) {
					$datos='<table>';
                    foreach($data->new_values as $attribute => $value){
                      $datos.='<tr>
                        <td><b>'.$attribute .'</b></td>
                        <td>'. $value .'</td>
                      </tr>';
                    }
                  $datos.= '</table>';
					return $datos;
				})
				->rawColumns(['valores_ant','valores_nue'])
                ->make(true);
		}	
		return view('backend.accounting.activityLog.index');
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
