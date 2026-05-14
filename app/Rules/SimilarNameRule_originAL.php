<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule; // <-- Cambia la interfaz
use Illuminate\Support\Facades\DB;

class SimilarNameRule implements Rule
{
    public function __construct(
        protected string $table,
        protected string $column = 'name',
        protected ?int $ignoreId = null,
        protected int $threshold = 3
    ) {}

    /**
     * Determina si la regla de validación pasa.
     */
    public function passes($attribute, $value)
    {
        $valorNuevo = strtolower(trim($value));
        
        $query = DB::table($this->table)->select($this->column);

        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        $candidatos = $query->whereRaw("SOUNDEX({$this->column}) = SOUNDEX(?)", [$valorNuevo])
            ->pluck($this->column);

        foreach ($candidatos as $nombreExistente) {
            if (levenshtein($valorNuevo, strtolower($nombreExistente)) <= $this->threshold) {
                // Si encontramos una similitud, la validación falla
                $this->similarFound = $nombreExistente; 
                return false;
            }
        }

        return true;
    }

    protected $similarFound = '';

    /**
     * Obtiene el mensaje de error de validación.
     */
    public function message()
    {
        return "El nombre es demasiado parecido a uno existente: {$this->similarFound}.";
    }
}


	/*
	uso 
'title' => ['required', new SimilarNameRule('categories', 'title')]
update
'name' => ['required', new SimilarNameRule('products', 'name', $product->id)]

new SimilarNameRule('products', 'name', null, 1)*/

