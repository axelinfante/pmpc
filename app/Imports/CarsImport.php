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

class CarsImport implements ToModel, WithStartRow 
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
        
        
        if(is_numeric($row[1]) && $this->validarFechas(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[1])->format('d/m/Y')) ){
            $fecha_asignacion = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[1])->format('d/m/Y');
            $fecha_asignacion =  \DateTime::createFromFormat('d/m/Y',\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[1])->format('d/m/Y'));
            if ($fecha_asignacion !== false) {
                $fecha_asignacion = $fecha_asignacion->format('Y-m-d');
            }else{
                $fecha_asignacion = null;
            }
        }
        if(is_numeric($row[2]) && $this->validarFechas(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2])->format('d/m/Y'))){
            $fecha_confirmacion_contacto = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2])->format('d/m/Y');

            $fecha_confirmacion_contacto = \DateTime::createFromFormat('d/m/Y',\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[2])->format('d/m/Y'));

            if ($fecha_confirmacion_contacto !== false) {
                $fecha_confirmacion_contacto = $fecha_confirmacion_contacto->format('Y-m-d');
            }else{
                $fecha_confirmacion_contacto = null;
            }
        }
        //// cia aseguradora
        $datos = [
            'rus' => 'RÍO URUGUAY',
            'smg' => 'SMG SEGUROS',

        ];

        $aseg = trim(preg_replace('/[0-9]+/', '', $row[3])); 
        
        if(!empty($datos[strtolower($aseg)])) {
            $aseg = $datos[strtolower($aseg)];
        }

        $a = Aseguradora::where('nombre','like','%'.$aseg)->first();

        if (empty($a)) {
            $a = new Aseguradora();
            $a->nombre = $aseg;
            $a->save();
        }

        $idAseguradora = $a->id;
        //--- </cia----////

        //retirado el
            $row[4];
        //_

        //lugar de entrega
        $datos = [
            'SIN RETIRAR' => 'No retirado',
            

        ];

            $lugar = trim(preg_replace('/[0-9]+/', '', $row[5])); 

            if(!empty($datos[$lugar])) {
                $lugar = $datos[$lugar];
            }

            $l = Lugar_entregas::where('nombre','like','%'.$lugar)->first();

            $idLugar_entrega = $l->id ?? null;
        //_

        if(is_numeric($row[6]) && $this->validarFechas(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[6])->format('d/m/Y'))){
            $fecha_ingreso = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[6])->format('d/m/Y');
            $fecha_ingreso = \DateTime::createFromFormat('d/m/Y',\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[6])->format('d/m/Y'));
            if ($fecha_ingreso !== false) {
                $fecha_ingreso = $fecha_ingreso->format('Y-m-d');
            }else{
                $fecha_ingreso = null;
            }
            
            
        }

        /// piezas faltantes / observaciones
        $observaciones_admin = $row[7];

        ////_

        $tipo_baja = [
            1 => '04 D',
            2 => '04 C',
            3 => 'Moto c/alta motor',
            4 => 'Moto baja definitiva',
            5 => 'BD',
            6 => 'Alta de Motor',
        ];

        foreach($tipo_baja as $key=>$t) {
            $tsin = str_replace(' ','',$t);
            $tsin2 = str_replace(' ','',$row[8]);
            if($tsin == $tsin2) {
                $tipo_bajaEx = $key;
            }
        }




        /// estados 


        $estado =  trim(preg_replace('/[0-9]+/', '', $row[9]));
        if ($estado != '') {
            $estado = Estado::where('estado','like','%'.$estado)->first();
            $idEstado = $estado->id ?? null;
        }else{
            $idEstado =  null;
        }
        //--



        //motor vendido
        $motor_vendido = strtolower(trim($row[10])) == 'vendido' ? 1 : 0 ;
        //
        //ubicacion --colocaremos ubicacion final 
            $row[11];
        //

        //marca
        $m = trim($row[12]);
        $marca = Marca::where('marca',$m)->first();

        if(empty($marca)) {
            $marca = new Marca();
            $marca->marca = $m;
            $marca->save();
        }

        //modelo 
        $mo = trim($row[13]);
        $modelo = Modelo::where('modelo',$mo)->first();

        if(empty($modelo)) {
            $modelo = new Modelo();
            $modelo->modelo = $mo;
            $modelo->save();
        }

        $marca_modelo = MarcaModelo::where('idMarca',$marca->id ??null)->where('idModelo',$modelo->id ??null)->first();
        
        if(empty($marca_modelo)) {
        

           

            $marca_modelo = new MarcaModelo();
        
            $marca_modelo->idMarca = $marca->id;
            $marca_modelo->idModelo = $modelo->id;
            $marca_modelo->save();

           // dd();

        }

        ///vendedor 
        $row[17];
        //vendedor
        // dd($marca_modelo);
   // dd($fecha_asignacion);
        if(!intval($id)) {
            return null;
        }
        

        
        // $fecha_asignacion = date("Y/m/d", strtotime($fecha_asignacion));
        // $fecha_confirmacion_contacto = date("Y/m/d", strtotime($fecha_confirmacion_contacto));
        
// dd($fecha_asignacion);

        $vehi = Cars::find($id);

        if(!empty($vehi) && $id >= 3000){
            return null;
        }

        if(true) {
            $car = new Cars([
            
                'id' => '100000'.$id,
                'fecha_asignacion' => $fecha_asignacion,
                'fecha_confirmacion_contacto' => $fecha_confirmacion_contacto,
                'fecha_ingreso' => $fecha_ingreso,
                'tipo_baja' => $tipo_bajaEx,
                'idAseguradora' => $idAseguradora,
                'idLugar_entrega' => $idLugar_entrega,
                'idEstado' => $idEstado,
                'observaciones_admin' => $observaciones_admin,
                'motor_nro' => $row[14],
                'dominio' => $row[15],
                'chasis' => $row[16],
                'idMarca_modelo' => $marca_modelo->id ?? null,
                'company_id' => 2
            ]);
        }else{
            $car = new Cars([
            
                'id' => $id,
                'fecha_asignacion' => $fecha_asignacion,
                'fecha_confirmacion_contacto' => $fecha_confirmacion_contacto,
                'fecha_ingreso' => $fecha_ingreso,
                'tipo_baja' => $tipo_bajaEx,
                'idAseguradora' => $idAseguradora,
                'idLugar_entrega' => $idLugar_entrega,
                'idEstado' => $idEstado,
                'observaciones_admin' => $observaciones_admin,
                'motor_nro' => $row[14],
                'dominio' => $row[15],
                'chasis' => $row[16],
                'idMarca_modelo' => $marca_modelo->id ?? null,
                'company_id' => 1
            ]);
        }

         

        new Seguimiento_car([
            'idCar' => $car['id'],
            'motor_vendido_reservado' => $motor_vendido,
            'ubicacion' => $row[18] //ubicacion final

        ]);

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
