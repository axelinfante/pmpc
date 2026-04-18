<?php

namespace App\Http\Controllers;

use App\Invoice;
use App\TipoComprobante;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;


class TipoComprobanteController extends Controller {
    public function __construct() {
		date_default_timezone_set(get_company_option('timezone', get_option('timezone', 'Asia/Dhaka')));
	}

    /**
	 * Display a listing of the resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
		return view('backend.accounting.tipo_comprobante.list');
	}

    public function get_table_data() {

		$currency = currency();

		$tiposComprobante = TipoComprobante::select('tipo_comprobante.*')
			->where("tipo_comprobante.company_id", company_id())
			->orderBy("tipo_comprobante.id", "desc");

		return Datatables::eloquent($tiposComprobante)
			->addColumn('action', function ($trans) {
				return '<form action="' . action('TipoComprobanteController@destroy', $trans['id']) . '" class="text-center" method="post">'
					. '<a href="#" data-title="Editar Tipo de Comprobante" class="btn btn-warning btn-xs ajax-modal disabled"><i class="ti-pencil"></i></a>&nbsp;'
					. '<a href="' . action('TipoComprobanteController@show', $trans['id']) . '" data-title="Ver tipo de comprobante" class="btn btn-info btn-xs ajax-modal"><i class="ti-eye"></i></a>&nbsp;'
					. csrf_field()
						. '<input name="_method" type="hidden" value="DELETE">'
						. '<button class="btn btn-danger btn-xs btn-remove" type="submit"><i class="ti-eraser"></i></button>'
						. '</form>';
			})
			->setRowId(function ($trans) {
				return "row_" . $trans->id;
			})
			->rawColumns(['numero', 'descripcion', 'action'])
			->make(true);
	}

    /**
	 * Show the form for creating a new resource.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function create(Request $request) {
		if (!$request->ajax()) {
			return view('backend.accounting.tipo_comprobante.create');
		} else {
			return view('backend.accounting.tipo_comprobante.modal.create');
		}
	}

    /**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		$validator = Validator::make($request->all(), [
			'numero' => 'required',
			'descripcion' => 'required'
		]);

		if ($validator->fails()) {
			if ($request->ajax()) {
				return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
			} else {
				return redirect('tipocomprobante/create')
					->withErrors($validator)
					->withInput();
			}
		}

		$tipoComprobante = new TipoComprobante();
		$tipoComprobante->numero = $request->input('numero');
        $tipoComprobante->descripcion = $request->input('descripcion');
		$tipoComprobante->company_id = company_id();

		$tipoComprobante->save();

		if (!$request->ajax()) {
			return redirect('tipocomprobante/create')->with('success', _lang('Saved Sucessfully'));
		} else {
			return response()->json(['result' => 'success', 'action' => 'store', 'message' => _lang('Saved Sucessfully'), 'data' => $tipoComprobante]);
		}

	}

    /**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show(Request $request, $id) {
		$transaction = TipoComprobante::where("id", $id)
			->where("company_id", company_id())->first();
		if (!$request->ajax()) {
			return view('backend.accounting.tipo_comprobante.view', compact('transaction', 'id'));
		} else {
			return view('backend.accounting.tipo_comprobante.modal.view', compact('transaction', 'id'));
		}

	}

    /**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {
		DB::beginTransaction();
		$tipoComprobante = TipoComprobante::where("id", $id)->where("company_id", company_id())->first();

		$tipoComprobante->delete();
		DB::commit();
		return redirect('tipocomprobante')->with('success', _lang('Removed Sucessfully'));
	}
}
