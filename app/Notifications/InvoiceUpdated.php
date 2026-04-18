<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\DBChannel;
use App\Notifications\WhatsAppChannel; 

class InvoiceUpdated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $invoice;

    public function __construct($invoice)
    {
        $this->invoice = $invoice;
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
            'id'      		=> $this->invoice->id,
            'url'     		=> 'client/view_invoice/'.md5($this->invoice->id),
			'client_url'    => 'client/view_invoice/'.md5($this->invoice->id),
            'title'   		=> _lang('Updated Invoice'),
            'content' 		=> _lang('Invoice').'# '.$this->invoice->invoice_number,
        ];
    }
    public function toInfobip($notifiable)
    {

       $message= _lang('Updated Invoice'). ' '. _lang('Invoice').'# '.$this->invoice->invoice_number;
       $message.= '. Para mas detalle visite: ' . route('invoices.show', $this->invoice->id);
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
        //$message= _lang(' Invoice').' #'.$this->invoice->invoice_number;
       /// $message.= '. Para mas detalle visite: ' . route('invoices.show', $this->invoice->id);
       $message= _lang('Updated Invoice'). ' '. _lang('Invoice').' #'.$this->invoice->invoice_number;
       $message.= '. Para mas detalle visite: ' . route('invoices.show', $this->invoice->id);
     

        return [
            'message' => $message,
        ];
    }
}
