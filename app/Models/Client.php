<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{

    public static function sendMassive($clientMessage, $sendMessageUrl, $apiToken, $message){
        $clients = self::all();
        foreach ($clients as $client){
            $phone = $client->phone;
            $clientMessage->request('POST', $sendMessageUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiToken,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'chatId' => "503$phone@c.us",
                    'message' => $message,
                ],
            ]);
        }
    }
}
