<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\DBChannel;
use App\Notifications\WhatsAppChannel; 

class OrdenCreated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $Orden;

    public function __construct($Orden)
    {
        $this->Orden = $Orden;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        return [DBChannel::class,WhatsAppChannel::class];
    }


    /**
     * Get the array representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            'id' => $this->Orden->id,
            'url' => route('orden-desarme.show', $this->Orden->id),
            'client_url' => route('orden-desarme.show', $this->Orden->id),
            'title' => _lang('Orden de desarme Creada'),
            'content' => _lang('Orden') . '# ' . $this->Orden->id,
        ];
    }
    public function toInfobip($notifiable)
    {

       $message= _lang('Orden de desarme Creada').' '. _lang('Orden de Desarme').'# '.$this->orden->orden_number;
        // return [
        //     'messages' => [
        //         [
        //             'from' => env('WHATSAPP_FROM'),
        //             'to' =>$notifiable->phone_number, 
        //             'messageId' => uniqid(),
        //             'content' => [
        //                 'templateName' => 'message_test',
        //                 'templateData' => [
        //                     'body' => [
        //                         'placeholders' => [$message],
        //                     ],
        //                 ],
        //                 'language' => 'en',
        //             ],
        //         ],
        //     ],
        // ];
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
       // $message= _lang('Nueva factura ha sido creada ').' '. _lang('Orden de Desarme').'# '.$this->orden->orden_number;
       ///$message.= '. Para mas detalle visite: ' . route('ordens.show', $this->orden->id);
       $message= _lang('Orden de desarme Creada').' '. _lang('Orden de Desarme').' #'.$this->Orden->id . ' '. _lang('Venta') . ' #' . $this->Orden->venta->invoice_number;
        return [
            'message' => $message,
        ];
    }
}
