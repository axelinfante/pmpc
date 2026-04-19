<?php

namespace App\Exports;


use Barryvdh\DomPDF\Facade as PDF;

class CarsExportPdf
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
    public function generate()
    {

        $pdf = app('dompdf.wrapper');
        $pdf = Pdf::loadView('backend.accounting.vehiculo.export.pdf', ['cars' => $this->data])
            ->setPaper('a3', 'landscape'); // A3 horizontal para acomodar todas las columnas

        return $pdf->download('vehiculos.pdf');
    }
}
