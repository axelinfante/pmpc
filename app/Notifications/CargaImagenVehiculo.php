<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\DBChannel;
use App\Notifications\WhatsAppChannel; 

class CargaImagenVehiculo extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $Auto;

    public function __construct($Auto)
    {
        $this->Auto = $Auto;
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
            'id'      		=> $this->Auto->id,
            'url'     		=> route('vehiculo.edit', $this->Auto->id),
			'client_url'    => route('vehiculo.edit', $this->Auto->id),
            'title'   		=> _lang('Imagenes de vehiculos cargadas'),
            'content' 		=> _lang('Vehiculo').'# '.$this->Auto->id,
        ];
    }

    public function toInfobip($notifiable)
    {

       $message= 'Imagenes de vehiculos cargadas.'. _lang('Vehiculo').'# '.$this->Auto->id;
      // $message = 'Orden de desarme Actualizada. Orden # <a href="' . route('orden-desarme.show', $this->Orden->id) . '">' . $this->Orden->id . '</a>';
    
    
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
       // $message= 'Carga de imágenes de vehículos, '. _lang('Vehiculo').' #'.$this->Auto->id;
        $message= 'Imagenes de vehiculos cargadas.'. _lang('Vehiculo').' #'.$this->Auto->id;


        return [
            'message' => $message,
        ];
    }

}
