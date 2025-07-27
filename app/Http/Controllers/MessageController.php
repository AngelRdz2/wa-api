<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use GuzzleHttp\Client;
use App\Models\MessageTemplate;
use App\Models\MoratoriumClassification;
use App\Models\ClientMessage;
use App\Services\WaapiService; // Asegúrate que esta clase exista y esté bien namespaceada

class MessageController extends Controller
{
    private $apiToken;

    public function __construct()
    {
        $this->apiToken = env('WAAPI_API_TOKEN');
    }

    // Mostrar formulario para subir Excel
    public function formUploadExcel()
    {
        return view('subir-excel');
    }

    // Procesar el archivo Excel y guardar datos en sesión
    public function subirExcel(Request $request)
    {
        $request->validate([
            'excel' => 'required|file|mimes:xlsx,xls,csv|max:2048',
        ]);

        $coleccion = Excel::toCollection(null, $request->file('excel'));

        if ($coleccion->isEmpty()) {
            return back()->withErrors(['El archivo está vacío o no se pudo leer.']);
        }

        $hoja = $coleccion->first();
        $datosPorCategoria = [];

        foreach ($hoja as $fila) {
            if (empty($fila[0]) || empty($fila[2])) {
                continue; // Ignorar filas sin número o sin clasificación
            }

            $numero = preg_replace('/\D/', '', $fila[0]); // Solo números
            $estatus = isset($fila[1]) ? trim($fila[1]) : '';
            $clasificacion = trim($fila[2]);
            $nombre = isset($fila[3]) ? trim($fila[3]) : '';
            $factura = isset($fila[4]) ? trim($fila[4]) : '';
            $monto = isset($fila[5]) ? trim($fila[5]) : '';

            if ($numero && $clasificacion) {
                $datosPorCategoria[$clasificacion][] = [
                    'numero' => $numero,
                    'tipo_mensaje' => $estatus,
                    'nombre' => $nombre,
                    'factura' => $factura,
                    'monto' => $monto,
                ];
            }
        }

        session(['numeros_por_categoria' => $datosPorCategoria]);

        // Redirigir a vista previa
        return redirect()->route('messages.preview');
    }

    // Mostrar formulario para enviar mensajes (solo botón)
    public function formSendMessages()
    {
        $categorias = session('numeros_por_categoria', []);
        return view('send-messages', compact('categorias'));
    }

    // Enviar mensajes masivos usando plantillas según la categoría (mora)
    public function sendMessage(Request $request)
    {
        $datosPorCategoria = session('numeros_por_categoria', []);

        if (empty($datosPorCategoria)) {
            return back()->withErrors(['message' => 'No hay números cargados para enviar mensajes.']);
        }

        $client = new Client();

        try {
            $response = $client->request('GET', 'https://waapi.app/api/v1/instances', [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->apiToken,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ]
            ]);

            $responseBody = json_decode($response->getBody(), true);
            $instances = $responseBody['instances'] ?? [];

            if (empty($instances)) {
                return back()->withErrors(['message' => 'No se encontraron instancias activas.']);
            }

            $instanceId = $instances[0]['id'];
            $sendMessageUrl = "https://waapi.app/api/v1/instances/{$instanceId}/client/action/send-message";

            foreach ($datosPorCategoria as $categoria => $numeros) {
                $plantilla = MessageTemplate::whereHas('classification', function($query) use ($categoria) {
                    $query->where('name', $categoria);
                })->first();

                if (!$plantilla) {
                    continue;
                }

                $templateContent = $plantilla->template;

                foreach ($numeros as $dato) {
                    $numero = $dato['numero'];
                    $chatId = "503{$numero}@c.us";

                    $msgPersonalizado = str_replace(
                        ['{nombre}', '{factura}', '{monto}'],
                        [$dato['nombre'], $dato['factura'], $dato['monto']],
                        $templateContent
                    );

                    $client->request('POST', $sendMessageUrl, [
                        'headers' => [
                            'Authorization' => 'Bearer ' . $this->apiToken,
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                        ],
                        'json' => [
                            'chatId' => $chatId,
                            'message' => $msgPersonalizado,
                        ],
                    ]);
                }
            }

            return back()->with('status', 'Mensajes enviados correctamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Error al enviar mensajes: ' . $e->getMessage()]);
        }
    }

    // Vista previa mensajes antes de enviar
    public function previewMessages()
    {
        $datosPorCategoria = session('numeros_por_categoria', []);

        if (empty($datosPorCategoria)) {
            return back()->withErrors(['message' => 'No hay datos cargados para mostrar vista previa.']);
        }

        $mensajesPorCategoria = [];

        foreach ($datosPorCategoria as $categoria => $numeros) {
            $plantilla = MessageTemplate::whereHas('classification', function($query) use ($categoria) {
                $query->where('name', $categoria);
            })->first();

            if (!$plantilla) continue;

            $templateContent = $plantilla->template;

            foreach ($numeros as $dato) {
                $msgPersonalizado = str_replace(
                    ['{nombre}', '{factura}', '{monto}'],
                    [$dato['nombre'], $dato['factura'], $dato['monto']],
                    $templateContent
                );

                $mensajesPorCategoria[$categoria][] = [
                    'numero' => $dato['numero'],
                    'mensaje' => $msgPersonalizado,
                ];
            }
        }

        return view('messages-preview', compact('mensajesPorCategoria'));
    }

    // Responder mensaje recibido
    public function reply(Request $request)
    {
        $phone = $request->input('phone');
        $message = $request->input('message');

        // Enviar usando WaapiService (asegúrate de tener esta clase creada)
        $waapi = new WaapiService();
        $waapi->sendMessage($phone, $message);

        // Guardar el mensaje en la base de datos
        ClientMessage::create([
            'phone' => $phone,
            'message' => $message,
            'direction' => 'outbound',
        ]);

        return redirect()->route('messages.responses')->with('success', 'Mensaje enviado');
    }

    // Mostrar mensajes recibidos y respuestas
    public function showResponses()
    {
        $mensajes = ClientMessage::orderBy('created_at', 'desc')->get();
        return view('responses', compact('mensajes'));
    }
}
