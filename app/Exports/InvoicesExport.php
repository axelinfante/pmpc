<?php

namespace App\Exports;

use App\Invoice;
use App\InvoiceItem;
use App\Transaction;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoicesExport implements FromQuery, WithHeadings, WithMapping
{
    protected $request;

    public function __construct($request)
    {
        $this->request = $request;
        
    }

    public function query()
    {
        $projects = DB::table('projects')
            ->select('id', 'name as contact_name', DB::raw('"projects" as type'));

        $all_contacts = DB::table('contacts')
            ->select('id', 'contact_name', DB::raw('"contacts" as type'))
            ->union($projects);

        $invoices = Invoice::joinSub($all_contacts, 'all_contacts', function ($join) {
            $join->on('invoices.related_id', '=', 'all_contacts.id')
                ->on('invoices.related_to', '=', 'all_contacts.type');
        })
            ->select("invoices.*", "all_contacts.contact_name", "all_contacts.id as contact_id")
            ->orderBy('invoices.id', 'desc');

        if ($this->request->has('invoice_number')) {
            $invoices->where('invoice_number', 'like', "%{$this->request->get('invoice_number')}%");
        }

        if ($this->request->has('date_range')) {
            $date_range = explode(" - ", $this->request->get('date_range'));
           // $invoices->whereBetween('invoice_date', [$date_range[0], $date_range[1]]);
            $invoices->whereRaw("DATE(invoice_date) BETWEEN STR_TO_DATE(?, '%d-%m-%Y') AND STR_TO_DATE(?, '%d-%m-%Y')", [$date_range[0], $date_range[1]]);

        }


        if (strTolower(auth()->user()->role->name) == 'vendedor') {
            $invoices->where('invoices.user_id', auth()->id());
        }
        $aFacturar = $this->request->get('facturar', false);
        if ($aFacturar) {
            $invoices->where('invoices.facturar', 1)->where('invoices.facturado', null);
        }
        if ($this->request->has('revendedor')) {
            $invoices->where('revendedor', $this->request->get('revendedor'));
        }

        if ($this->request->has('company_id')) {
            $invoices->where('company_id', $this->request->get('company_id'));
        }

     

        if ($this->request->has('status')) {
            $invoices->whereIn('status', json_decode($this->request->get('status')));
        }

           // Iterar sobre las columnas enviadas desde DataTables
    foreach ($this->request->input('columns') as $index => $column) {
        $columnName = $column['data']; // Nombre de la columna
        $searchValue = $column['search']['value']; // Valor de búsqueda

        // Ignorar si la columna no es "searchable" o no tiene un valor de búsqueda
        if ($column['searchable'] !== "true" || empty($searchValue)) {
            continue;
        }

        // Aplicar filtros según el nombre de la columna
        switch ($columnName) {
            case 'producto':
                $invoices->orWhereHas('invoice_items', function ($subQuery) use ($searchValue) {
                    $subQuery->whereHas('product', function ($subQuery) use ($searchValue) {
                        $subQuery->whereHas('item', function ($subQuery) use ($searchValue) {
                            $subQuery->where('item_name', 'like', "%{$searchValue}%");
                        });
                    });
                });
                break;

            case 'vendedor':

                
                $invoices->orWhereHas('vendedor', function ($subQuery) use ($searchValue) {
                    $subQuery->where('name', 'like', "%{$searchValue}%");
                });
                break;

            case 'fecha_pago':
                $invoices->orWhereHas('transaction', function ($subQuery) use ($searchValue) {
                    $subQuery->where('trans_date', 'like', "%{$searchValue}%");
                });
                break;

            case 'porcentajeComision':
                $invoices->orWhereHas('comision', function ($subQuery) use ($searchValue) {
                    $subQuery->where('porcentaje', 'like', "%{$searchValue}%");
                });
                break;

            case 'invoice_number':
                $invoices->where('invoice_number', 'like', "%{$searchValue}%");
                break;

            case 'contact_name':
                $invoices->whereRaw('all_contacts.contact_name like ?', ["%{$searchValue}%"]);
                break;

            default:
                // Si la columna no requiere un filtro especial, ignórala
                break;
        }
    }


      

        // Aplica otros filtros según tu lógica
        return $invoices;
    }

    public function headings(): array
    {

        if (strtolower(auth()->user()->role->name) != 'gerencial') {
            if (strtolower(auth()->user()->role->name) == 'despacho')
                $columns = [
                    "#",
                    "invoice_number",
                    "contact_name",
                    "invoice_date",
                    "fecha_entrega",
                    "producto",
                    "vendedor",
                    "grand_total",
                    "monto_adeudado",
                    "status",
                    "ubicacion",

                ];
            else

                $columns = [
                    "#",
                    "invoice_number",
                    "contact_name",
                    "invoice_date",
                    "fecha_entrega",
                    "fecha_pago",
                    "producto",
                    "vendedor",
                    "porcentajeComision",
                    "grand_total",
                    "monto_adeudado",
                    "status",
                    "ubicacion"
                ];
        } else {
            $columns = [
                "#",
                "invoice_number",
                "contact_name",
                "invoice_date",
                "fecha_entrega",
                "fecha_pago",
                "producto",
                "vendedor",
                "porcentajeComision",
                "comision",
                "grand_total",
                "monto_adeudado",
                "status",
                "ubicacion",

            ];
        }

        return   $columns;
    }

    public function map($invoice): array
    {
        if (strtolower(auth()->user()->role->name) != 'gerencial') {
            if (strtolower(auth()->user()->role->name) == 'despacho')

                return [
                    $invoice->id,
                    //$invoice->invoice_number,
                    $this->get_invoice_number($invoice),
                    $invoice->contact_name,
                    date(
                        get_company_option('date_format', 'Y-m-d'),
                        strtotime($invoice->invoice_date ?: '')
                    ),

                    $invoice->fecha_entrega ?
                        date(get_company_option('date_format', 'Y-m-d'), strtotime($invoice->fecha_entrega)) : '',

                    $this->get_producto($invoice->id),
                    $invoice->vendedor->name,
                    decimalPlace($invoice->grand_total, currency($invoice->client->currency)),
                    decimalPlace($invoice->grand_total - $invoice->paid, currency($invoice->client->currency)),
                    $invoice->status,
                    $invoice->ubicacion,
                ];
            else


                return [
                    $invoice->id,
                    //$invoice->invoice_number,
                    $this->get_invoice_number($invoice),
                    $invoice->contact_name,
                    date(
                        get_company_option('date_format', 'Y-m-d'),
                        strtotime($invoice->invoice_date ?: '')
                    ),

                    $invoice->fecha_entrega ?
                        date(get_company_option('date_format', 'Y-m-d'), strtotime($invoice->fecha_entrega)) : '',

                    $this->get_fecha_pago($invoice->id),
                    $this->get_producto($invoice->id),
                    $invoice->vendedor->name,
                    $invoice->comision->porcentaje,
                    decimalPlace($invoice->grand_total, currency($invoice->client->currency)),
                    decimalPlace($invoice->grand_total - $invoice->paid, currency($invoice->client->currency)),
                    $invoice->status,
                    $invoice->ubicacion,
                ];
        } else {
            return [
                $invoice->id,
                //$invoice->invoice_number,
                $this->get_invoice_number($invoice),
                $invoice->contact_name,
                date(
                    get_company_option('date_format', 'Y-m-d'),
                    strtotime($invoice->invoice_date ?: '')
                ),

                $invoice->fecha_entrega ?
                    date(get_company_option('date_format', 'Y-m-d'), strtotime($invoice->fecha_entrega)) : '',

                $this->get_fecha_pago($invoice->id),
                $this->get_producto($invoice->id),
                $invoice->vendedor->name,
                $invoice->comision->porcentaje,
                decimalPlace($invoice->comision->monto, currency($invoice->client->currency)),
                decimalPlace($invoice->grand_total, currency($invoice->client->currency)),
                decimalPlace($invoice->grand_total - $invoice->paid, currency($invoice->client->currency)),
                $invoice->status,
                $invoice->ubicacion,
            ];
        }
    }

    public function get_producto($invoice_id)
    {

        $invoice_item = InvoiceItem::where('invoice_id', $invoice_id)->get();

        //dd($invoice_item);
        $html = '';
        if (!empty($invoice_item)):

            foreach ($invoice_item as $item):
                $html .= $item->product->item->item_name . '; ';
            endforeach;

        endif;

        return $html;
    }

    public function get_fecha_pago($invoice_id)
    {
        $date_format = get_company_option('date_format', 'Y-m-d');
        $transactions = Transaction::where("invoice_id", $invoice_id)->orderBy('id', 'desc')->first();
        if (isset($transactions)) {
            return date($date_format, strtotime($transactions->trans_date));
        }
        return '';
    }

    public function get_invoice_number($invoice)
    {
        $in = 'VEN-';
        if ($invoice->company_id == 1) {
            $in .= 'PM-';
        } else if ($invoice->company_id == 2) {
            $in .= 'PC-';
        }
        return "$in$invoice->invoice_number";


    }


   


}
