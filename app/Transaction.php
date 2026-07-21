<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'trans_date',
        'account_id',
        'chart_id',
        'type',
        'dr_cr',
        'amount',
        'base_amount',
        'payer_payee_id',
        'invoice_id',
        'purchase_id',
        'purchase_return_id',
        'project_id',
        'payment_method_id',
        'reference',
        'attachment',
        'note',
        'company_id',
        'tipo_comprobante_id',
        'razon_social',
        'banco',
        'cheque_nro',
        'cheque_vencimiento',
        'cheque_entregado_a',
        'imputar_a',
        'detalle_rubro',
        'usd',
        'tasa',
        'status',
        'id_comision',
        'id_quotation',
        'amount_usd',
        'amount_peso',
        'trans_asoc',
    ];

    public function account()
    {
        return $this->belongsTo('App\Account')->withDefault();
    }

    public function income_type()
    {
        return $this->belongsTo('App\ChartOfAccount', "chart_id")->withDefault();
    }

    /*public function payer()
    {
        return $this->belongsTo('App\Contact',"payer_payee_id")->withDefault();
    }*/

    public function payer()
    {
        return $this->belongsTo('App\User', "payer_payee_id")->withDefault();
    }

    public function expense_type()
    {
        return $this->belongsTo('App\ChartOfAccount', "chart_id")->withDefault();
    }

    public function Invoice()
    {
        return $this->belongsTo('App\Invoice', 'invoice_id');
    }
	
	public function Reserva()
    {
        return $this->belongsTo('App\Quotation', 'id_quotation');
    }

    public function payment_method()
    {
        return $this->belongsTo('App\PaymentMethod', "payment_method_id")->withDefault();
    }

    public function project()
    {
        return $this->belongsTo('App\Project', "project_id")->withDefault();
    }

    public function tipo_comprobante()
    {
        return $this->belongsTo('App\TipoComprobante', "tipo_comprobante_id")->withDefault();
    }

    public function cuenta_imputar()
    {
        return $this->belongsTo('App\Account', "imputar_a")->withDefault();
    }
    public function pagos_car()
    {
        return $this->hasone('App\Pagos_car', "id_gasto", 'id')->withDefault();
    }

    public function movimientos_cuenta()
    {
        return $this->morphMany(CuentaCorriente::class, ' ');
    }
}