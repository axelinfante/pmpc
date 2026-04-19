<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\DBChannel;
use App\Notifications\WhatsAppChannel; 

class PagosCarCreated extends Notification
{
    use Queueable;

    private $transaccion;

    public function __construct($transaccion)
    {
        $this->transaccion = $transaccion;
    }

    public function via($notifiable)
    {
        return [DBChannel::class, WhatsAppChannel::class]; // Agregar el canal de whatsapp
    }

    public function toArray($notifiable)
    {

        $prioridad =  $this->transaccion->payment_priority == null ? 'Normal' : ucwords(str_replace('_', ' ', $this->transaccion->payment_priority));
        $vehiculo =  $this->transaccion->pagos_car->id_car;


        return [
            'id' => $this->transaccion->id,
            'url' => route('expense.show', $this->transaccion->id),
            'client_url' => route('expense.show', $this->transaccion->id),
            'title' => 'Pago '. $prioridad ,
            'content' => ' por concepto de '. $this->transaccion->expense_type->name . ' vehiculo #'.$vehiculo
        ];
    }

    public function toTwilio($notifiable)
    {
       // $message= _lang('Pago ha sido creado'). ' '. _lang('transaccion') . ' #' . $this->transaccion->id;

        $prioridad =  $this->transaccion->payment_priority == null ? 'Normal' : ucwords(str_replace('_', ' ', $this->transaccion->payment_priority));
        $vehiculo =  $this->transaccion->pagos_car->id_car;

        $message= 'Pago '. $prioridad . ' por concepto de '. $this->transaccion->expense_type->name . ' vehiculo #'.$vehiculo;
        

        return [
            'message' => $message,
        ];
    }
    
}

