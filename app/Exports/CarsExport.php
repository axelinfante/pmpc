<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CarsExport  implements FromCollection, WithHeadings
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
            'Dominio',
            'Anulado',
            'Fecha Asignación',
            'Tramitador',
            'Aseguradora',
            'Tramitador Compañía',
            'Siniestro',
            'Marca/Modelo',
            'Motor',
            'Tipo Baja',
            'Asegurado',
            'Contacto',
            'Lugar Retiro',
            'Localidad',
            'Provincia',
            'Estado',
            'Entregado A',
            'Fecha Entrega',
            'Observación Admin',
            'Fecha Recepción',
            'Coordinar Retiro',
            'Fecha Envío Doc',
            'Chasis',
            'Fecha Confirmación Contacto',
            'Fecha Límite Retiro',
            'Responsable Retiro',
            'CRP Nro',
            'Entregar En',
            'Fecha Retiro',
            'Fecha Ingreso',
            'Observación Gerente/Operario',
            'Observación Retiro',
        ];
    }
  
}


