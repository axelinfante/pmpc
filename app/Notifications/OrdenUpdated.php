<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Notifications\DBChannel;
use App\Notifications\WhatsAppChannel; 

class OrdenUpdated extends Notification
{
    use Queueable;

    private $Orden;

    public function __construct($Orden)
    {
        $this->Orden = $Orden;
    }

    public function via($notifiable)
    {
        return [DBChannel::class, WhatsAppChannel::class]; // Agregar el canal de whatsapp
    }

    public function toArray($notifiable)
    {
        return [
            'id' => $this->Orden->id,
            'url' => route('orden-desarme.show', $this->Orden->id),
            'client_url' => route('orden-desarme.show', $this->Orden->id),
            'title' => _lang('Orden de desarme Actualizada'),
            'content' => _lang('Orden') . '# ' . $this->Orden->id,
        ];
    }

    public function toInfobip($notifiable)
    {

       $message= _lang('Orden de desarme Actualizada'). ' '. _lang('Orden') . ' #' . $this->Orden->id;
      

      

        return [
            'message' =>  [
                'from' => env('WHATSAPP_FROM'),
                'to' => $notifiable->phone_number,
                'messageId' => 'test-message-'.uniqid(),
                'content' => [
                    'text' => $message
                ],
                'callbackData' => 'Callback data'
            ],
        ];
    }

    public function toTwilio($notifiable)
    {
        $message= _lang('Orden de desarme Actualizada'). ' '. _lang('Orden') . ' #' . $this->Orden->id. ' '. _lang('Venta') . ' #' . $this->Orden->venta->invoice_number;

        return [
            'message' => $message,
        ];
    }
    
}

