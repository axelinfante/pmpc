<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\DBChannel;
use App\Notifications\WhatsAppChannel; 

class InvoiceProductMercadoLibre extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $product;

    public function __construct($product)
    {
        $this->product = $product;
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
            'id'      		=> $this->product->id,
            'url'     		=> route('products.show',$this->product->id),
			'client_url'    => route('products.show',$this->product->id),
            'title'   		=> 'Se ha vendido un producto fuera de mercado libre',
            'content' 		=> _lang('Product').'# '.$this->product->id.' '.$this->product->item->item_name,
        ];
    }
    public function toInfobip($notifiable)
    {

       $message= 'Se ha vendido un producto fuera de mercado libre  '.$this->product->id.' '.$this->product->item->item_name;
       $message.= '. Para mas detalle visite: ' . route('products.show',$this->product->id);
       
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
       
       $message= 'Se ha vendido un producto fuera de mercado libre '.$this->product->id.' '.$this->product->item->item_name;
       $message.= '. Para mas detalle visite: ' . route('products.show',$this->product->id);
        return [
            'message' => $message,
        ];
    }
}
