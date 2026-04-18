<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PiezasExport  implements FromCollection, WithHeadings
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
            'id',
            'Interno',
            'product',
            'marca_modelo',
            'nro_motor',
            'nro_oblea',
            'deposito',
            'ubicacion',
            'description',
           
        ];
    }
  
}



