<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\DBChannel;
use App\Notifications\WhatsAppChannel; 

class CambioEstadoVehiculoGerenciales extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $car;

    public function __construct($car)
    {
        $this->car = $car;
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
            'id'      		=> $this->car->id,
            'url'     		=> route('vehiculo.index'),
			'client_url'    => route('vehiculo.index'),
            'title'   		=> _lang('Estado de vehiculo modificado'),
            'content' 		=> _lang('Vehiculo').'# '.$this->car->str_interno(),
        ];
    }

    public function toInfobip($notifiable)
    {

       $message= 'Estado de vehiculo modificado.'. _lang('Vehiculo').'# '.$this->car->str_interno();
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
        //$message= 'información del estado de vehiculo, '. _lang('Vehiculo ').'#'.$this->reserva->car_id;
        $message= 'Estado de vehiculo modificado.'. _lang('Vehiculo').' #'.$this->car->str_interno();

        return [
            'message' => $message,
        ];
    }

}
