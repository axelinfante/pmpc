<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use App\Notifications\DBChannel;
use App\Notifications\WhatsAppChannel; 

class TaskUpdated extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    private $task;

    public function __construct($task)
    {
        $this->task = $task;
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
            'id'      		=> $this->task->id,
            'url'     		=> 'tasks/'.$this->task->id,
            'client_url'    => '',
            'title'   		=> _lang('Updated Task'),
            'content' 		=> _lang('Task').' '.$this->task->id.'# '.$this->task->title,
        ];
    }
    public function toInfobip($notifiable)
    {

       $message=  _lang('Updated Task'). ' '.  _lang('Task').' '.$this->task->id.'# '.$this->task->title;
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
        $message=  _lang('Updated Task'). ' '.  _lang('Task').' '.$this->task->id.'# '.$this->task->title;
        return [
            'message' => $message,
        ];
    }
}
