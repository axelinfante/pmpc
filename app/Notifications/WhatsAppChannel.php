<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class WhatsAppChannel
{
    public function send($notifiable, Notification $notification)
    {
        // Enviar mensaje usando Infobip si las variables están definidas
        if (env('WHATSAPP_FROM') && env('WHATSAPP_URL') && env('WHATSAPP_APPKEY')) {
            $this->sendViaInfobip($notifiable, $notification);
        }

        // Enviar mensaje usando Twilio si las variables están definidas
        if (env('TWILIO_ACCOUNT_SID') && env('TWILIO_AUTH_TOKEN') && env('TWILIO_WHATSAPP_FROM')) {
            $this->sendViaTwilio($notifiable, $notification);
        }

        return true;
    }

    private function sendViaInfobip($notifiable, Notification $notification)
    {
        $notifiables = is_array($notifiable) ? $notifiable : [$notifiable];

        $client = new Client();
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'App ' . env('WHATSAPP_APPKEY')
        ];

        foreach ($notifiables as $notifiable) {
            if (!empty($notifiable->phone_number)) {
                try {
                    $body = $notification->toInfobip($notifiable);
                    $client->post(env('WHATSAPP_URL'), [
                        'headers' => $headers,
                        'json' => $body['message']
                    ]);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }
    }

    private function sendViaTwilio($notifiable, Notification $notification)
    {
        $notifiables = is_array($notifiable) ? $notifiable : [$notifiable];

        $client = new Client();
        $url = 'https://api.twilio.com/2010-04-01/Accounts/' . env('TWILIO_ACCOUNT_SID') . '/Messages.json';

        $headers = [
            'Authorization' => 'Basic ' . base64_encode(env('TWILIO_ACCOUNT_SID') . ':' . env('TWILIO_AUTH_TOKEN')),
        ];

        foreach ($notifiables as $notifiable) {

            if (!empty($notifiable->phone_number)) {

                try {
                    // Elimina caracteres invisibles como U+202A, U+202C, etc.
                    $number = preg_replace('/[\x{200E}\x{200F}\x{202A}-\x{202E}]/u', '', $notifiable->phone_number);

                    // Elimina espacios, guiones, paréntesis, etc.
                    $number = preg_replace('/[^\d+]/', '', $number);

                    $body = $notification->toTwilio($notifiable);
                    $response = $client->post($url, [
                        'auth' => [env('TWILIO_ACCOUNT_SID'), env('TWILIO_AUTH_TOKEN')],


                        'form_params' => [
                            'ContentSid' => env('TWILIO_TEMPLATE_ID_1'),
                            'To' => 'whatsapp:+' . $number,
                            'From' => 'whatsapp:' . env('TWILIO_WHATSAPP_FROM'),
                            'ContentVariables' => json_encode(
                                [
                                    "1" => $body['message']
                                ]
                            )
                        ]

                    ]);

                    //return json_decode($response->getBody(), true);

                } catch (\Exception $e) {

                    Log::error('Error enviando Notificacion WhatsApp : ' . $e->getMessage());


                    continue;
                }
            }
        }
    }
}
