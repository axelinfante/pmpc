<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExpensesCarsExport  implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'Interno',
            'Fecha',
            'Razón Social',
            'Monto',
            'Pagador',
            'Tipo de Comprobante',
            'Cuenta',
            'Imputar A',
            'Tipo de Gasto',
            'Detalle del Rubro',
            'Método de Pago',
            'Banco',
            'Cheque Nro',
            'Cheque Vencimiento',
            'Cheque Entregado A',
            'Tasa',
            'Estatus',
            'Prioridad de Pago',
        ];
    }
  
}


