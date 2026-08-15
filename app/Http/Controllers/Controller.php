<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;
    protected $control = [1 =>'Terminado', 2 => 'Reclamar doc'];
	
	protected $comisiones = [
    'Camiones'            => ['percentage' => 4,   'fixed' => 0],
    'Motos enteras'       => ['percentage' => 4,   'fixed' => 0],
    'Mercado Libre'       => ['percentage' => 5,   'fixed' => 0],
    'Ruedas'              => ['percentage' => 4,   'fixed' => 0],
    'Motores'             => ['percentage' => 2.5, 'fixed' => 0],
    'Reventa'        	  => ['percentage' => 3,   'fixed' => 0],
    'Lote'                => ['percentage' => 3,   'fixed' => 0],
    'Venta normal' => ['percentage' => 7,   'fixed' => 0],
    'Venta menos a 30000' => ['percentage' => 7,   'fixed' => 1000],
	];
   
/*   protected $comisiones = [
        'Camiones' => 4,
        'Motos enteras' => 4,
        'Mercado Libre' => 5,
        'Ruedas' => 4,
        'Motores' => 2.5,
        'Reventa' => 3,
        'Lote' => 3,
        'Venta normal' => 7,// si en mayor a 30.000 y si es < 7 + 1000
        'Venta menos a 30000' => 7,// si en mayor a 30.000 y si es < 7 + 1000
    ];*/

    public $estadosIva = [
        'Responsable inscripto',
        'Consumidor final',
        'Monotributo',
    ];

    public $tipoBaja = [
        1 => '04 D',
        2 => '04 C',
        3 => 'Moto c/alta motor',
        4 => 'Moto baja definitiva',
        5 => 'BD',
        6 => 'Alta de Motor',
    ];

 public $responsable_entregas = [
            1 => 'Asegurado',
            2 => 'Gestor Compañia',
            3 => 'Productor',
            4 => 'Compañia'
        ];
        //tipo de baja

 public    $tipo_baja = [
            1 => '04 D',
            2 => '04 C',
            3 => 'Moto c/alta motor',
            4 => 'Moto baja definitiva',
            5 => 'BD',
            6 => 'Alta de Motor',
        ];




}
