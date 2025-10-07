<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use GuzzleHttp\Client;
use App\Models\MessageTemplate;
use App\Models\MoratoriumClassification;
use App\Models\ClientMessage;
use App\Services\WaapiService;
use App\Models\User;

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

        $hoja = $coleccion->first()->slice(1); // Saltar encabezados
        $datosPorCategoria = [];

        foreach ($hoja as $fila) {
            $userName = explode(",",$fila[6]);
            $userId = User::whereIn('name', $userName)->pluck('id')->toArray();
            dd($userId);
            if (empty($fila[0]) || empty($fila[2])) continue;

            $numero = preg_replace('/\D/', '', $fila[0]);
            $estatus = trim($fila[1] ?? '');
            $clasificacionExcel = strtolower(trim($fila[2]));
            $nombre = trim($fila[3] ?? '');
            $factura = trim($fila[4] ?? '');
            $monto = trim($fila[5] ?? '');

            if ($numero && $clasificacionExcel) {
                $datosPorCategoria[$clasificacionExcel][] = compact('numero', 'estatus', 'nombre', 'factura', 'monto');
            }
        }

        session(['numeros_por_categoria' => $datosPorCategoria]);

        return redirect()->route('messages-preview');
    }

    // Mostrar vista previa de mensajes
    public function previewMessages()
    {
        $datosPorCategoria = session('numeros_por_categoria', []);
        $mensajesPorCategoria = [];

        foreach ($datosPorCategoria as $categoriaExcel => $numeros) {
            $clasificacion = MoratoriumClassification::whereRaw('LOWER(name) = ?', [$categoriaExcel])->first();
            if (!$clasificacion) continue;

            $plantilla = MessageTemplate::where('moratorium_classification_id', $clasificacion->id)->first();
            if (!$plantilla) continue;

            foreach ($numeros as $dato) {
                $msgPersonalizado = str_replace(
                    ['{nombre}', '{factura}', '{monto}'],
                    [$dato['nombre'], $dato['factura'], $dato['monto']],
                    $plantilla->template
                );

                $mensajesPorCategoria[$clasificacion->name][] = [
                    'numero' => $dato['numero'],
                    'mensaje' => $msgPersonalizado,
                ];
            }
        }

        return view('messages-preview', compact('mensajesPorCategoria'));
    }

    // Formulario para confirmar y enviar mensajes
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
            $response = $client->get('https://waapi.app/api/v1/instances', [
                'headers' => $this->getHeaders()
            ]);

            $instances = json_decode($response->getBody(), true)['instances'] ?? [];
            if (empty($instances)) {
                return back()->withErrors(['message' => 'No se encontraron instancias activas.']);
            }

            $instanceId = $instances[0]['id'];
            $sendMessageUrl = "https://waapi.app/api/v1/instances/{$instanceId}/client/action/send-message";

            foreach ($datosPorCategoria as $categoria => $numeros) {
                $plantilla = MessageTemplate::whereHas('classification', function ($query) use ($categoria) {
                    $query->where('name', $categoria);
                })->first();

                if (!$plantilla) continue;

                foreach ($numeros as $dato) {
                    $chatId = "503{$dato['numero']}@c.us";
                    $msgPersonalizado = str_replace(
                        ['{nombre}', '{factura}', '{monto}'],
                        [$dato['nombre'], $dato['factura'], $dato['monto']],
                        $plantilla->template
                    );

                    $client->post($sendMessageUrl, [
                        'headers' => $this->getHeaders(),
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

    // Responder mensaje recibido
public function reply(Request $request)
{
    $request->validate([
        'phone' => 'required|string',
        'message' => 'required|string',
    ]);

    \Log::info('Método reply ejecutado', [
        'phone' => $request->phone,
        'message' => $request->message,
    ]);

    $client = new \GuzzleHttp\Client();

    try {
        // Obtener instancia activa
        $response = $client->get('https://waapi.app/api/v1/instances', [
            'headers' => $this->getHeaders()
        ]);

        $instances = json_decode($response->getBody(), true)['instances'] ?? [];
        if (empty($instances)) {
            \Log::error('No se encontraron instancias activas en WAAPI');
            return back()->withErrors(['message' => 'No se encontraron instancias activas.']);
        }

        $instanceId = $instances[0]['id'];
        $sendMessageUrl = "https://waapi.app/api/v1/instances/{$instanceId}/client/action/send-message";

        // Limpiar número y formatear chatId
        $numero = preg_replace('/[^0-9]/', '', $request->phone);
        $chatId = "{$numero}@c.us";

        \Log::info('Enviando mensaje a WAAPI', [
            'chatId' => $chatId,
            'message' => $request->message,
            'url' => $sendMessageUrl,
        ]);

        // Enviar mensaje
        $waapiResponse = $client->post($sendMessageUrl, [
            'headers' => $this->getHeaders(),
            'json' => [
                'chatId' => $chatId,
                'message' => $request->message,
            ],
        ]);

        $body = $waapiResponse->getBody()->getContents();
        \Log::info('Respuesta WAAPI:', ['body' => $body]);

        // Guardar en base de datos
        ClientMessage::create([
            'from_number' => env('MY_PHONE_NUMBER'),
            'to_number' => $chatId,
            'message' => $request->message,
            'direction' => 'outbound',
            'received_at' => now(),
            'phone' => $chatId,
        ]);

        return redirect()->route('responses')->with('success', 'Mensaje enviado correctamente');

    } catch (\Exception $e) {
        \Log::error('Error al enviar mensaje:', [$e->getMessage()]);
        return back()->withErrors(['message' => 'Error al enviar mensaje: ' . $e->getMessage()]);
    }
}




    // Mostrar mensajes recibidos y respuestas
    public function showResponses()
    {
        $mensajes = ClientMessage::latest()->get();
        return view('responses', compact('mensajes'));
    }

    // Encabezados comunes para WAAPI
    private function getHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }
}
