<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\DBChannel;
use App\Notifications\WhatsAppChannel; 

class CambioEstadoVehiculo extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $reserva;

    public function __construct($reserva)
    {
        $this->reserva = $reserva;
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
            'id'      		=> $this->reserva->quotation_number,
            'url'     		=> route('reservas.index'),
			'client_url'    => route('reservas.index'),
            'title'   		=> _lang('Estado de vehiculo modificado'),
            'content' 		=> _lang('Vehiculo').'# '.$this->reserva->car_id .' Reserva # '.$this->reserva->quotation_number,
        ];
    }

    public function toInfobip($notifiable)
    {

       $message= 'Estado de vehiculo modificado.'. _lang('Vehiculo').'# '.$this->reserva->car_id;
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
        $message= 'Estado de vehiculo modificado.'. _lang('Vehiculo').' #'.$this->reserva->car_id;

        return [
            'message' => $message,
        ];
    }

}
