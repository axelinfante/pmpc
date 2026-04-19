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

class CarsImportIfNotExist implements ToModel, WithStartRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $id = preg_replace('/[^0-9]+/', '', $row[0]);
        //dd($id);
        $fecha_asignacion = null;
        $fecha_confirmacion_contacto = null;
        $fecha_ingreso = null;
        $tipo_bajaEx = null;



        /// piezas faltantes / observaciones
        $observaciones_admin = $row[1];

        ////_
        $tipo_baja = [
            1 => '04 D',
            2 => '04 C',
            3 => 'Moto c/alta motor',
            4 => 'Moto baja definitiva',
            5 => 'BD',
            6 => 'Alta de Motor',
        ];

        foreach ($tipo_baja as $key => $t) {
            $tsin = str_replace(' ', '', $t);
            $tsin2 = str_replace(' ', '', $row[2]);
            if ($tsin == $tsin2) {
                $tipo_bajaEx = $key;
            }
        }

        /// estados 


        $estado = trim(preg_replace('/[0-9]+/', '', $row[3]));
        if ($estado != '') {
            $estado = Estado::where('estado', 'like', '%' . $estado)->first();
            $idEstado = $estado->id ?? null;
        } else {
            $idEstado = null;
        }
        //--
        //motor vendido
        $motor_vendido = strtolower(trim($row[4])) == 'vendido' ? 1 : 0;
        //


        //lugar de entrega  ubicacion
        $datos = [
            'SIN RETIRAR' => 'No retirado',


        ];

        $lugar = trim(preg_replace('/[0-9]+/', '', $row[5]));

        if (!empty($datos[$lugar])) {
            $lugar = $datos[$lugar];
        }

        $l = Lugar_entregas::where('nombre', 'like', '%' . $lugar)->first();

        $idLugar_entrega = $l->id ?? null;
        //_

















        //marca
        $m = trim($row[6]);
        $marca = Marca::where('marca', $m)->first();

        if (empty($marca)) {
            $marca = new Marca();
            $marca->marca = $m;
            $marca->save();
        }

        //modelo 
        $mo = trim($row[7]);
        $modelo = Modelo::where('modelo', $mo)->first();

        if (empty($modelo)) {
            $modelo = new Modelo();
            $modelo->modelo = $mo;
            $modelo->save();
        }

        $marca_modelo = MarcaModelo::where('idMarca', $marca->id ?? null)->where('idModelo', $modelo->id ?? null)->first();

        if (empty($marca_modelo)) {




            $marca_modelo = new MarcaModelo();

            $marca_modelo->idMarca = $marca->id;
            $marca_modelo->idModelo = $modelo->id;
            $marca_modelo->save();

            // dd();

        }

        ///vendedor 
        
        //vendedor
        // dd($marca_modelo);
        // dd($fecha_asignacion);
        if (!intval($id)) {
            return null;
        }



        // $fecha_asignacion = date("Y/m/d", strtotime($fecha_asignacion));
        // $fecha_confirmacion_contacto = date("Y/m/d", strtotime($fecha_confirmacion_contacto));

        // dd($fecha_asignacion);
        $dominio = trim($row[9]);
        $vehi = Cars::where('dominio', $dominio)->first();

        if (!empty($vehi) || $idEstado == 1 || trim($dominio) == '') {
            return null;
        }

        if (false) {
// dd([

//     'id' => '100000' . $id,
//     'fecha_asignacion' => $fecha_asignacion,
//     'fecha_confirmacion_contacto' => $fecha_confirmacion_contacto,
//     'fecha_ingreso' => $fecha_ingreso,
//     'tipo_baja' => $tipo_bajaEx,
//     // 'idAseguradora' => $idAseguradora,
//     'idLugar_entrega' => $idLugar_entrega,
//     'idEstado' => $idEstado,
//     'observaciones_admin' => $observaciones_admin,
//     'motor_nro' => $row[8],
//     'dominio' => $row[9],
//     // 'chasis' => $row[16],
//     'idMarca_modelo' => $marca_modelo->id ?? null,
//     'company_id' => 2
// ]);
            $car = new Cars([

                'id' => '100000' . $id,
                'fecha_asignacion' => $fecha_asignacion,
                'fecha_confirmacion_contacto' => $fecha_confirmacion_contacto,
                'fecha_ingreso' => $fecha_ingreso,
                'tipo_baja' => $tipo_bajaEx,
                // 'idAseguradora' => $idAseguradora,
                'idLugar_entrega' => $idLugar_entrega,
                'idEstado' => $idEstado,
                'observaciones_admin' => $observaciones_admin,
                'motor_nro' => $row[8],
                'dominio' => $row[9],
                // 'chasis' => $row[16],
                'idMarca_modelo' => $marca_modelo->id ?? null,
                'company_id' => 2
            ]);
        } else {
            $car = new Cars([

                'id' => $id,
                'fecha_asignacion' => $fecha_asignacion,
                'fecha_confirmacion_contacto' => $fecha_confirmacion_contacto,
                'fecha_ingreso' => $fecha_ingreso,
                'tipo_baja' => $tipo_bajaEx,
                // 'idAseguradora' => $idAseguradora,
                'idLugar_entrega' => $idLugar_entrega,
                'idEstado' => $idEstado,
                'observaciones_admin' => $observaciones_admin,
                'motor_nro' => $row[8],
                'dominio' => $row[9],
                // 'chasis' => $row[16],
                'idMarca_modelo' => $marca_modelo->id ?? null,
                'company_id' => 1
            ]);
        }



        new Seguimiento_car([
            'idCar' => $car['id'],
            'motor_vendido_reservado' => $motor_vendido,
            'ubicacion' => $row[5] //ubicacion final

        ]);

        return $car;
    }

    private function validarFechas($fecha)
    {
        $arr = explode('/', $fecha);

        // if ($fecha == '31-12-1969'){
        //     dd($fecha);
        // }
        if (count($arr) == 3 && checkdate($arr[1], $arr[0], $arr[2])) {
            $result = true;
        } else {
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
