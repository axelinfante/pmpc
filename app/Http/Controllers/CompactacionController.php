<?php
namespace App\Http\Controllers;

use App\HistorialStateCar;
use Illuminate\Http\Request;
use DataTables;

class CompactacionController extends Controller
{
    public function index()
    {
        return view('backend.accounting.compactacion.index');
    }

    public function get_data(Request $request)
{
	$company_id = company_id_arr();

	$query = HistorialStateCar::select('historial_state_cars.*', 'estados.estado as nombre_estado')
		->join('estados', 'historial_state_cars.id_new_state', '=', 'estados.id')
		->with([
			'vehiculo',
			'vehiculo.marca_modelo.marca',
			'vehiculo.marca_modelo.modelo'
		])
		->where('id_new_state', 1) 
		->whereHas('vehiculo', function($q) use ($company_id) {
			$q->whereIn('company_id', $company_id); 
		})
		->orderBy('historial_state_cars.fecha', 'desc');

    return DataTables::of($query)
        ->editColumn('interno', function ($row) {
            return $row->vehiculo->str_interno() ?? $row->vehiculo->id ?? '';
        })
        ->addColumn('marca', function ($row) {
            return $row->vehiculo->marca_modelo->marca->marca ?? '';
        })
        ->addColumn('modelo', function ($row) {
            return $row->vehiculo->marca_modelo->modelo->modelo ?? '';
        })
        ->editColumn('estado', function ($row) {
            return $row->nombre_estado ?? ''; 
        })
        ->editColumn('fecha_cambio', function ($row) {
            return $row->fecha ? \Carbon\Carbon::parse($row->fecha)->format('d/m/Y') : null;
        })
        ->filterColumn('marca', function ($query, $keyword) {
            $query->whereHas('vehiculo.marca_modelo.marca', function ($sub) use ($keyword) {
                $sub->where('marca', 'like', "%{$keyword}%");
            });
        })
        ->filterColumn('modelo', function ($query, $keyword) {
            $query->whereHas('vehiculo.marca_modelo.modelo', function ($sub) use ($keyword) {
                $sub->where('modelo', 'like', "%{$keyword}%");
            });
        })
        ->filterColumn('estado', function ($query, $keyword) {
            $query->where('estados.estado', 'like', "%{$keyword}%");
        })
		->filterColumn('fecha_cambio', function ($query, $keyword) {
					$date_range = ($keyword != '') ? explode(" - ", $keyword) : array();
                    if (count($date_range) == 2) {
                        $query->whereDate('fecha', '>=', $date_range[0])
                            ->whereDate('fecha', '<=', $date_range[1]);
                    }
			})
        ->rawColumns(['interno', 'estado']) 
        ->make(true);
}
}