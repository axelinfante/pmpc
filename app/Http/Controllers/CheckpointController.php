<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Checkpoint;
use Illuminate\Support\Facades\Validator;
use DataTables;

class CheckpointController extends Controller
{
    //
    public function index()
    {
        $checkpoints = Checkpoint::all();

        return view('backend.accounting.checkpoint.list');
    }

    public function show(Request $request)
    {}
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'checkpoint' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
        }

        $checkpoint = new Checkpoint;
        $checkpoint->nombre = $request->checkpoint;
        $checkpoint->save();

        return response()->json([
            'result' => 'success', 'action' => 'update', 'message' => _lang('Save sucessfully'),
            'data' => $checkpoint
        ]);
    }

    public function get_table_data($tramite = null)
    {

        if ($tramite){

           /*  $checkpoints = Checkpoint::leftJoin('vehiculos_checkpoints', 'vehiculos_checkpoints.checkpoint_id', '=', 'checkpoints.id')
            ->select(['checkpoints.id', 'checkpoints.nombre'])
            ->selectRaw("(CASE WHEN vehiculos_checkpoints.vehiculo_id IS NOT NULL THEN TRUE ELSE FALSE END) as marca")
            ->where('vehiculos_checkpoints.vehiculo_id',$tramite); */

            $checkpoints = Checkpoint::leftJoin('vehiculos_checkpoints', function($join) use ($tramite) {
                $join->on('vehiculos_checkpoints.checkpoint_id', '=', 'checkpoints.id')
                     ->where('vehiculos_checkpoints.vehiculo_id', '=', $tramite);
            })
            ->select(['checkpoints.id', 'checkpoints.nombre'])
            ->selectRaw("(CASE WHEN vehiculos_checkpoints.vehiculo_id IS NOT NULL THEN TRUE ELSE FALSE END) as marca");


        } else {
            $checkpoints = Checkpoint::orderBy("id", "asc")->select(['id', 'nombre']);
            $checkpoints->selectRaw("'' as marca");
        }
       
        return Datatables::eloquent($checkpoints)

            ->setRowId(function ($checkpoints) {
                return "row_" . $checkpoints->id;
            })
            ->make(true);
    }
}
