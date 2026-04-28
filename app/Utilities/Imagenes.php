<?php

/**
 * Created by PhpStorm.
 * User: MALZ
 * Date: 05/08/2023
 * Time: 11:00
 */

namespace App\Utilities;

use App\Imagen;
use File;

trait Imagenes
{

    public function uploadImg($request, $param)
    {

        $path = public_path() . '/uploads/' . $param['dir'];

        $files = $request->file('imagen');
        if ($files && is_array($files)) {
            foreach ($files as $file) {
                $fileName = time() . $file->getClientOriginalName();
                $file->move($path, $fileName);

                $imagen = new \App\Imagen();
                $imagen->idCar = $param['idCar'] ?? null;
                $imagen->idProduct = $param['idProduct'] ?? null;
                $imagen->company_id = company_id();
                $imagen->img = $fileName;
                $imagen->save();
            }
        }

        $files = $request->file('imagen_recepcion');
        if ($files && is_array($files)) {
            foreach ($files as $file) {
                $fileName = time() . $file->getClientOriginalName();
                $file->move($path, $fileName);

                $imagen = new \App\Imagen();
                $imagen->idCar = $param['idCar'] ?? null;
                $imagen->idProduct = $param['idProduct'] ?? null;
                $imagen->company_id = company_id();
                $imagen->img = $fileName;
                $imagen->seccion = 'receptor';
                $imagen->save();
            }
        }
    }

    public function deleteImgsByIdCarOridProd($param)
    {
        //eliminar archivo
        $imagenes = Imagen::orWhere('idProduct', $param['idProduct'] ?? null)->orWhere(
            'idProduct',
            $param['idProduct']
        )->get();

        foreach ($imagenes as $img) {
            unlink(public_path('uploads/products/' . $img->img));
        }
        //eliminar bd
        Imagen::where('idProduct', $param['idProduct'] ?? null)->delete();
        Imagen::where('idCar', $param['idCar'] ?? null)->delete();
    }


    public function validarDir($carpeta){
            
            if(!File::isDirectory($carpeta)){
             File::makeDirectory($carpeta, 0777, true, true);
            } 
    }


}
