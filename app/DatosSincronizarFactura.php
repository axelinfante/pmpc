<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DatosSincronizarFactura extends Model
{
    use SoftDeletes;

    // Vincular explícitamente con el nombre exacto de tu tabla
    //protected $table = 'datos_sincronizar_factura';

    // Campos habilitados para asignación masiva
    protected $fillable = [
        'invoice_id', 
        'json_pmpc',
        'json_afip',
        'factura_fiscal',
        'estado',
        'mensaje_error',
    ];

    // Mutadores automáticos para los campos JSON
    protected $casts = [
        'json_pmpc' => 'array',
        'json_afip' => 'array',
    ];
	
	
	 /**
     * Relación con el modelo Invoice.
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }
	
}
