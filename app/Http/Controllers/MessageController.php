<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Http;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller; // Asume que estás en un controlador de Laravel
class MessageController extends Controller
{
    public function index(){

        return view('index');
    }
    private $token = '3FpCLjIUrWTE6WhE94ByGoQ8fLESkZVgYx6RiWYIc90145e3';




    public function sendMessage(Request $request)
    {
        $request->validate([
            //'phone' => 'required|string',
            //'message' => 'required|string',
        ]);
        //dd(\App\Models\Client::getMessage(3));
        //$phone = $request->input('phone'); // El número de teléfono del destinatario
        $message = $request->input('message');
        $apiToken = '3FpCLjIUrWTE6WhE94ByGoQ8fLESkZVgYx6RiWYIc90145e3';

        $clientMessage = new Client();

        try {
            // 1. Obtener la instancia (esto ya lo tienes y funciona)
            $response = $clientMessage->request('GET', 'https://waapi.app/api/v1/instances', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiToken,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ]
            ]);

            $responseBody = json_decode($response->getBody(), true);
            $instances = $responseBody['instances'];

            $instanceId = null;
            if (!empty($instances)) {
                $instanceId = $instances[0]['id'];
            } else {
                return response()->json(['error' => 'No se encontraron instancias activas.'], 400);
            }

            $sendMessageUrl = "https://waapi.app/api/v1/instances/{$instanceId}/client/action/send-message";
            \App\Models\Client::sendMassive($clientMessage, $sendMessageUrl, $apiToken);
            /*$response = $clientMessage->request('POST', $sendMessageUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $apiToken,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'chatId' => "503$phone@c.us",
                    'message' => $message,
                ],
            ]);*/

            //$responseBody = json_decode($response->getBody(), true);

            if ($response->getStatusCode() === 200) {
                return response()->json(['message' => 'Mensaje enviado con éxito.', 'data' => $responseBody]);
            } else {
                return response()->json(['error' => 'Error al enviar el mensaje.', 'details' => $responseBody], $response->getStatusCode());
            }

        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $responseBody = json_decode($response->getBody()->getContents(), true);
            return response()->json(['error' => 'Error de cliente con la API.', 'status_code' => $statusCode, 'details' => $responseBody], $statusCode);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ha ocurrido un error inesperado.', 'details' => $e->getMessage()], 500);
        }
    }

}
