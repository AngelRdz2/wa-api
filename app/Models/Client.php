<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{

    public static function sendMassive($clientMessage, $sendMessageUrl, $apiToken){
        $clients = self::all();
        foreach ($clients as $client){
            $phone = $client->phone;
            $message = self::getMessage($client->id);
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
    public static function getMessage($clientId)
    {
        $client = Client::where('clients.id', $clientId)
            ->join('moratorium_classifications as mc', 'mc.id', '=', 'clients.moratorium_classification_id')
            ->join('message_templates as mt', 'mt.moratorium_classification_id', '=', 'mc.id')
            ->select('clients.name as client_name', 'mt.template')
            ->first();

        if (!$client) {
            return 'Cliente no encontrado o sin plantilla asociada.';
        }

        $message = str_replace(':nombre_cliente', $client->client_name, $client->template);
        return $message;
    }

}
