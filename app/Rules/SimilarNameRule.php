<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\DB;

class SimilarNameRule implements Rule
{
    protected string $similarFound = '';

    public function __construct(
        protected string $table,
        protected string $column = 'name',
        protected ?int $ignoreId = null
    ) {}

    /**
     * Determina si la regla de validación pasa.
     */
    public function passes($attribute, $value)
    {
        // 1. Tokenizar y normalizar el texto ingresado por el usuario
        $tokensNuevos = $this->tokenizar($value);
        if (empty($tokensNuevos)) {
            return true;
        }

        // 2. Extraer candidatos optimizados (Búsqueda inicial rápida por la primera palabra clave)
        $query = DB::table($this->table)->select($this->column);
        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }

        // Filtro indexado: el registro debe contener al menos la primera palabra del modelo
        $candidatos = $query->where($this->column, 'LIKE', '%' . $tokensNuevos[0] . '%')
            ->pluck($this->column);

        // 3. Comparación de Intersección Exacta
        foreach ($candidatos as $nombreExistente) {
            $tokensExistentes = $this->tokenizar($nombreExistente);

            // Si la cantidad de palabras descriptivas difiere, no son el mismo producto
            if (count($tokensNuevos) !== count($tokensExistentes)) {
                continue;
            }

            // Comparamos el contenido exacto de ambos arrays sin importar el orden de escritura
            sort($tokensNuevos);
            sort($tokensExistentes);

            if ($tokensNuevos === $tokensExistentes) {
                $this->similarFound = $nombreExistente;
                return false; // Son exactamente iguales palabra por palabra, se bloquea.
            }
        }

        return true; // Hay al menos una palabra de diferencia técnica, se aprueba.
    }

    /**
     * Convierte el texto en un array de tokens normalizados y limpios.
     */
    protected function tokenizar(string $texto): array
    {
        // Pasar a minúsculas y normalizar caracteres comunes
        $texto = strtolower(trim($texto));
        
        // Reemplazar guiones, barras o símbolos por espacios para unificar criterios
        $texto = str_replace(['-', '/', '_', '.', ','], ' ', $texto);
        
        // Eliminar espacios dobles o múltiples
        $texto = preg_replace('/\s+/', ' ', $texto);

        // Separar por espacios, eliminar elementos vacíos y quitar duplicados de palabras
        $tokens = array_filter(explode(' ', $texto));
        
        return array_values(array_unique($tokens));
    }

    /**
     * Mensaje de error.
     */
    public function message()
    {
        return "Ya existe un registro con exactamente las mismas especificaciones: {$this->similarFound}.";
    }
}
