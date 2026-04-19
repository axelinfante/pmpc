<?php

namespace App\Imports;

use App\Aseguradora;
use App\Cars;
use App\Estado;
use App\Lugar_entregas;
use App\Marca;
use App\MarcaModelo;
use App\Modelo;
use App\Seguimiento_car;
use Hamcrest\Type\IsNumeric;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;

class CarsUpdateImport implements ToModel, WithStartRow 
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $id = preg_replace('/[^0-9]+/', '', $row[0]);
        $dominio = $row[2];
        //dd($id);
        $fecha_asignacion = null;
        $fecha_confirmacion_contacto = null;
        $fecha_ingreso = null;
        $tipo_bajaEx = null;
        
        
        

       if($id == '') {
        return null;
       }
        /// estados 


        $estado =  trim(preg_replace('/[0-9]+/', '', $row[1]));
        if ($estado != '') {
            $estado = Estado::where('estado','like','%'.$estado)->first();
            $idEstado = $estado->id ?? null;
            if(empty($estado)) {
                // $estado = new Estado();
                // $estado->estado = $estado;
                // $estado->save();
                // $idEstado =   $estado->id;
            }
           
            
        }else{
            
            $idEstado = null;
        }
        //--



        
        $car = Cars::where('dominio',$dominio)->first();
        
        // dd($car);
        if(!empty($car)){
            $car->idEstado = $idEstado;
            $car->save();

            // if($id == 3192) :
            //     dump($id);
            //     dump($car);
            //     dump($idEstado);
            // endif;
            // dd($car);
        }else{
            // $car = Cars::find('100000'.$id);
            // if(!empty($car)){
            //     $car->idEstado = $idEstado;
            //     $car->save();
            //     // dd($car);
            // }
            

        }



         

        

        return $car;
    }

    private function validarFechas ($fecha) {
        $arr = explode('/', $fecha);

        // if ($fecha == '31-12-1969'){
        //     dd($fecha);
        // }
        if(count($arr) == 3 && checkdate($arr[1],$arr[0],$arr[2])){
             $result = true;
        }else{
            $result = false;
            //dd($fecha);
        }
        return $result;
    }
    /**
     * @return int
    */
    public function startRow(): int
    {
        return 2;
    }
}
