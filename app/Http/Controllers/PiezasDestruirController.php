<?php
namespace App\Http\Controllers;

use App\ProductReturn;
use Illuminate\Http\Request;
use DataTables;
use Illuminate\Support\Facades\Validator;
use App\Rules\SimilarNameRule;
use Illuminate\Validation\Rule;

class PiezasDestruirController extends Controller
{
    public function index()
    {
        return view('backend.accounting.piezasdestruir.index'); 
    }

    public function get_data(Request $request)
    {
        $query = ProductReturn::select('products_returns.*')
            ->with(['producto.item', 'company', 'invoice.client'])
            ->where('status', 'descompuesto')
            ->orderBy('created_at', 'desc');

        return DataTables::of($query)
            ->editColumn('return_date', function ($row) {
                return $row->return_date 
                    ? \Carbon\Carbon::parse($row->return_date)->format('d-m-Y') 
                    : null;
            })
            ->editColumn('product_name', function ($row) {
                return "(".($row->producto->id ?? ''). ") " . ($row->producto->item->item_name ?? '');
            })
            ->editColumn('invoice_id', function ($row) {
                return $row->invoice->invoice_number ?? $row->invoice_id;
            })
            ->editColumn('client', function ($row) {
                return $row->invoice->client->contact_name ?? '';
            })
            ->editColumn('note', function ($row) {
                return str_replace('undefined', '', $row->note ?? '');
            })
            ->editColumn('status', function ($row) {
                return ucfirst($row->status ?? '');
            })
            ->filterColumn('client', function ($query, $keyword) {
                $query->whereHas('invoice.client', function ($subQuery) use ($keyword) {
                    $subQuery->where('contact_name', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('product_name', function ($query, $keyword) {
                $query->orwhereHas('producto', function ($str) use ($keyword) {
                    $str->where('products.id', 'like', "%{$keyword}%");
                    $str->orwhereHas('item', function ($str) use ($keyword) {
                        $str->where('items.item_name', 'like', "%{$keyword}%");
                    });
                });
            })
            ->filterColumn('invoice_id', function ($query, $keyword) {
                $query->where(function ($query) use ($keyword) {
                    $query->whereHas('invoice', function ($subQuery) use ($keyword) {
                        $subQuery->where('invoice_number', 'like', "%{$keyword}%");
                    })->orWhere('invoice_id', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('return_date', function ($query, $keyword) {
                $query->whereRaw("DATE_FORMAT(return_date, '%d-%m-%Y') like ?", ["%{$keyword}%"]);
            })

            ->filterColumn('status', function ($query, $keyword) {
                $query->where('status', 'like', "%{$keyword}%");
            })
            ->rawColumns(['product_name'])
            ->make(true);
    }
}