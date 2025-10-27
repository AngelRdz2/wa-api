<?php

namespace App\Http\Controllers;

use App\Models\WhatsappInstance;
use App\Models\Client as ClientModel;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use GuzzleHttp\Client;
use App\Models\MessageTemplate;
use App\Models\MoratoriumClassification;
use App\Models\ClientMessage;
use App\Services\WaapiService;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Exception\ClientException; // Importante para errores 4xx
use GuzzleHttp\Exception\ServerException; // Importante para errores 5xx

class MessageController extends Controller
{
    // ----------------------------------------------------------------------
    // FUNCIONES DE CARGA Y PREVISUALIZACIÓN (SE MANTIENEN IGUAL)
    // ----------------------------------------------------------------------

    public function formUploadExcel()
    {
        return view('subir-excel');
    }

    public function subirExcel(Request $request)
{
    $request->validate([
        'excel' => 'required|file|mimes:xlsx,xls,csv|max:2048',
    ]);

    $coleccion = Excel::toCollection(null, $request->file('excel'));

    if ($coleccion->isEmpty()) {
        return back()->withErrors(['El archivo está vacío o no se pudo leer.']);
    }

    $hoja = $coleccion->first()->slice(1);
    $datosPorCategoria = [];

    foreach ($hoja as $fila) {
        if (!isset($fila[0]) || !isset($fila[2]) || empty($fila[0]) || empty($fila[2])) {
            continue;
        }

        $numero = preg_replace('/\D/', '', $fila[0]);
        $estatus = isset($fila[1]) ? trim($fila[1]) : '';
        $clasificacion = trim($fila[2]);
        $nombre = isset($fila[3]) ? trim($fila[3]) : '';
        $factura = isset($fila[4]) ? trim($fila[4]) : '';
        $monto = isset($fila[5]) ? trim($fila[5]) : '';
        $dui = isset($fila[6]) ? trim($fila[6]) : null;
        $encargadoCorreo = isset($fila[6]) ? trim(strtolower($fila[6])) : null;
        $encargadoId = $encargadoCorreo
        ? User::where('email', $encargadoCorreo)->value('id')
        : null;

        if ($numero && $clasificacion) {
            $datosPorCategoria[$clasificacion][] = [
                'numero' => $numero,
                'tipo_mensaje' => $estatus,
                'nombre' => $nombre,
                'factura' => $factura,
                'monto' => $monto,
                'dui' => $dui,
                'encargado_id' => $encargadoId,
                'encargadoCorreo' => $encargadoCorreo,
            ];

            // ✅ Vincular cliente al encargado dentro del bucle
            if ($encargadoId) {
                $cliente = ClientModel::firstOrCreate(
                    ['phone' => $numero],
                    [
                        'name' => $nombre,
                        'dui' => $dui ?? '000000000',
                        'date' => now()->toDateString(),
                    ]
                );

                $cliente->users()->syncWithoutDetaching([$encargadoId]);
            }
        }
    }

    if (empty($datosPorCategoria)) {
        return back()->withErrors(['El archivo no contiene datos válidos con número y clasificación.']);
    }

    session(['numeros_por_categoria' => $datosPorCategoria]);

    return redirect()->route('messages-preview');
}


    public function previewMessages()
    {
        $datosPorCategoria = session('numeros_por_categoria', []);
        $mensajesPorCategoria = [];

        foreach ($datosPorCategoria as $categoriaExcel => $numeros) {
            // CRÍTICO: Usar LOWER() en la base de datos es menos eficiente.
            // Es mejor normalizar el valor de la sesión a minúsculas para la búsqueda.
            $clasificacion = MoratoriumClassification::where('name', 'LIKE', $categoriaExcel)->first();
            
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
    
    public function formSendMessages()
    {
        $categorias = session('numeros_por_categoria', []);
        $instances = WhatsappInstance::all(); 

        return view('send-messages', compact('categorias', 'instances'));
    }

    // ----------------------------------------------------------------------
    // MANEJO DE INSTANCIAS Y ENVÍO (ENVÍO MASIVO) - CORREGIDO
    // ----------------------------------------------------------------------

    /**
     * Envía mensajes masivos y maneja los errores de la API de WAAPI.
     * * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function sendMessage(Request $request)
{
    $datosPorCategoria = session('numeros_por_categoria', []);
    if (empty($datosPorCategoria)) {
        return back()->withErrors(['message' => 'No hay números cargados.']);
    }

    $request->validate(['whatsapp_instance_id' => 'required|exists:whatsapp_instances,id']);
    
    $instance = WhatsappInstance::findOrFail($request->whatsapp_instance_id);
    $instanceId = $instance->instance_id; 
    $apiKey = $instance->api_key; 
    $sendMessageUrl = "https://waapi.app/api/v1/instances/{$instanceId}/client/action/send-message";
    
    $senderUserId = Auth::id(); // Usuario logueado
    $isAdmin = Auth::user()->hasRole('admin');

    $client = new Client(['verify' => false]); 
    $totalSent = 0;
    $totalFailed = 0;
    
    foreach ($datosPorCategoria as $categoria => $numeros) {
        $plantilla = MessageTemplate::whereHas('classification', function ($query) use ($categoria) {
            $query->where('name', $categoria);
        })->first();

        if (!$plantilla) continue;

        foreach ($numeros as $dato) {
            $clientName = $dato['nombre'] ?? 'Cliente Sin Nombre'; 
            $clientDui = $dato['dui'] ?? '000000000'; 
            $currentDate = now()->toDateString(); 
            
            $encargadoCorreo = $dato['encargadoCorreo'] ?? null;
            $encargadoId = $senderUserId; // valor por defecto

            if ($encargadoCorreo) {
            $encargado = User::where('email', $encargadoCorreo)->first();
            if ($encargado) {
                $encargadoId = $encargado->id;
            }
        }


            // 🔒 Seguridad: solo el admin puede enviar mensajes de otros encargados
            if (!$isAdmin && $encargadoId !== $senderUserId) {
                Log::warning('Usuario no autorizado para enviar mensaje a cliente de otro encargado.', [
                    'usuario' => $senderUserId,
                    'encargado_id' => $encargadoId,
                    'numero' => $dato['numero'],
                ]);
                $totalFailed++;
                continue;
            }

            // 1. Crear o actualizar cliente
            $clientModel = ClientModel::firstOrCreate(
                ['phone' => $dato['numero']],
                [
                    'name' => $clientName,
                    'dui' => $clientDui, 
                    'date' => $currentDate,
                    'moratorium_classification_id' => $plantilla->moratorium_classification_id ?? null 
                ]
            );

            // 2. Vincular cliente al encargado
            if ($encargadoId && $user = User::find($encargadoId)) {
                $clientModel->users()->syncWithoutDetaching([$encargadoId]);
            }

            // 3. Personalizar mensaje
            $chatId = "503{$dato['numero']}@c.us";
            $msgPersonalizado = str_replace(
                ['{nombre}', '{factura}', '{monto}'],
                [$dato['nombre'], $dato['factura'], $dato['monto']],
                $plantilla->template
            );

            // 4. Enviar mensaje vía WAAPI
            try {
                $response = $client->post($sendMessageUrl, [
                    'headers' => $this->getDynamicHeaders($apiKey), 
                    'json' => [
                        'chatId' => $chatId,
                        'message' => $msgPersonalizado,
                    ],
                ]);

                if ($response->getStatusCode() >= 400) {
                    $errorBody = $response->getBody()->getContents();
                    Log::error('WAAPI ERROR al enviar (Status ' . $response->getStatusCode() . '):', [
                        'error' => $errorBody,
                        'numero' => $dato['numero']
                    ]);
                    $totalFailed++;
                    continue;
                }

                // ✅ 5. Registrar mensaje con el encargado real
                ClientMessage::create([
                    'client_id' => $clientModel->id,
                    'whatsapp_instance_id' => $instance->id,
                    'from_number' => $instance->phone,
                    'to_number' => $dato['numero'],
                    'message' => $msgPersonalizado,
                    'direction' => 'outbound',
                    'received_at' => now(),
                    'user_id' => $encargadoId, // 🔥 CORREGIDO: no usar senderUserId
                ]);
                $totalSent++;

            } catch (ClientException $e) {
                $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'N/A';
                Log::error('Guzzle/WAAPI Client Error 4xx:', [
                    'error' => $e->getMessage(), 
                    'response' => $errorBody,
                    'numero' => $dato['numero']
                ]);
                $totalFailed++;
                continue; 
            } catch (ServerException $e) {
                $errorBody = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'N/A';
                Log::error('Guzzle/WAAPI Server Error 5xx:', [
                    'error' => $e->getMessage(), 
                    'response' => $errorBody,
                    'numero' => $dato['numero']
                ]);
                $totalFailed++;
                continue; 
            } catch (\Exception $e) {
                Log::error('Error general al enviar mensaje:', [
                    'error' => $e->getMessage(),
                    'numero' => $dato['numero']
                ]);
                $totalFailed++;
                continue;
            }
        }
    }

    session()->forget('numeros_por_categoria'); 

    $mensajeFinal = "Proceso terminado. Mensajes enviados: {$totalSent}. Fallidos: {$totalFailed}.";

    return back()->with('status', $mensajeFinal);
}


    // ----------------------------------------------------------------------
    // RESPUESTA MANUAL - CORREGIDO
    // ----------------------------------------------------------------------
    
    /**
     * Envía una respuesta manual y registra el mensaje solo si el envío es exitoso.
     * * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
   public function reply(Request $request)
{
    $request->validate([
        'phone' => 'required|string',
        'message' => 'required|string',
        'whatsapp_instance_id' => 'required|exists:whatsapp_instances,id', 
    ]);

    $instance = WhatsappInstance::findOrFail($request->whatsapp_instance_id);
    $numero = preg_replace('/[^0-9]/', '', $request->phone);
    if (str_starts_with($numero, '503')) {
    $numero = substr($numero, 3);
}
    $senderUserId = Auth::id(); 
    $user = Auth::user();

    $instanceId = $instance->instance_id; 
    $apiKey = $instance->api_key; 
    $sendMessageUrl = "https://waapi.app/api/v1/instances/{$instanceId}/client/action/send-message";
    $chatId = "503{$numero}@c.us";
    $client = new Client(['verify' => false]);
    $messageStatus = 'success';

    // ✅ Crear el cliente si no existe
    $clientModel = ClientModel::firstOrCreate(
        ['phone' => $numero],
        ['name' => 'Cliente Chat', 'dui' => '000000000', 'date' => now()->toDateString()] 
    );

    // ✅ Vincular automáticamente al encargado si no lo estaba
    $clientModel->users()->syncWithoutDetaching([$senderUserId]);

    // ✅ Validar si el usuario tiene permiso para responder
    if (!$user->hasRole('admin') && !$clientModel->users->contains($user)) {
        return back()->withErrors(['message' => 'No tienes permiso para responder a este cliente.']);
    }

    // 🚨 Enviar mensaje vía WAAPI
    try {
        $response = $client->post($sendMessageUrl, [
            'headers' => $this->getDynamicHeaders($apiKey), 
            'json' => [
                'chatId' => $chatId,
                'message' => $request->message,
            ],
        ]);
        
        if ($response->getStatusCode() >= 400) {
            $errorBody = $response->getBody()->getContents();
            Log::error('WAAPI ERROR al responder (Status ' . $response->getStatusCode() . '):', ['error' => $errorBody]);
            return back()->withErrors(['message' => 'Error de la API de WhatsApp: ' . $errorBody]);
        }

    } catch (ClientException $e) {
        $errorDetails = $e->hasResponse() ? $e->getResponse()->getBody()->getContents() : 'Error desconocido';
        Log::error('Guzzle/WAAPI Client Error 4xx al responder:', ['error' => $errorDetails]);
        return back()->withErrors(['message' => 'Error de la API: ' . $errorDetails]);

    } catch (ServerException $e) {
        Log::warning('WAAPI INESTABLE: Error 500 al responder.', ['error' => $e->getMessage()]);
        $messageStatus = 'warning';

    } catch (\Exception $e) {
        Log::error('Error general al responder mensaje:', [$e->getMessage()]);
        return back()->withErrors(['message' => 'Error general al responder mensaje: ' . $e->getMessage()]);
    }

    // ✅ Guardar mensaje localmente
    try {
        ClientMessage::create([
            'client_id' => $clientModel->id,
            'whatsapp_instance_id' => $instance->id,
            'from_number' => $instance->phone, 
            'to_number' => $numero,
            'message' => $request->message,
            'direction' => 'outbound',
            'received_at' => now(),
            'user_id' => $senderUserId,
        ]);
        
    } catch (\Exception $e) {
        Log::error('Error FATAL al guardar mensaje localmente:', [$e->getMessage()]);
        return back()->withErrors(['message' => 'Error de base de datos al guardar la respuesta.']);
    }

    // ✅ Redirección y notificación
    $notification = $messageStatus === 'warning'
        ? ['warning' => 'Mensaje enviado (advertencia): API de WhatsApp inestable (500).']
        : ['success' => 'Mensaje enviado correctamente.'];
        
    return redirect()->route('responses', ['phone' => $numero])->with($notification);
}

    // ----------------------------------------------------------------------
    // FILTRADO DE RESPUESTAS (Optimizado) - SE MANTIENE IGUAL
    // ----------------------------------------------------------------------
public function showResponses(Request $request)
{
    $user = Auth::user();
    $isAdmin = $user->hasRole('admin');

    $userInstanceIds = $user->whatsappInstances()->pluck('whatsapp_instances.id')->toArray();
    $instance = WhatsappInstance::find(head($userInstanceIds));

    // ✅ Cargar todos los mensajes visibles para el usuario
    $allUserMessages = ClientMessage::query()
        ->when(!$isAdmin, function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->orWhereNull('user_id');
        })
        ->orderBy('received_at', 'desc')
        ->get();

    // ✅ Agrupar por número limpio
    $chatsConRespuestas = $allUserMessages->groupBy(function ($item) {
        $numero = $item->direction === 'inbound' ? $item->from_number : $item->to_number;
        return preg_replace('/[^0-9]/', '', $numero);
    });

    // ✅ Inicializar conversación vacía
    $conversacion = collect();
    $numeroSeleccionado = $request->get('phone');

    if ($numeroSeleccionado) {
        $numeroLimpio = preg_replace('/[^0-9]/', '', $numeroSeleccionado);

        // ✅ Crear cliente si no existe
        $clientModel = ClientModel::firstOrCreate(
            ['phone' => $numeroLimpio],
            ['name' => 'Cliente Chat', 'dui' => '000000000', 'date' => now()->toDateString()]
        );

        // ✅ Vincular automáticamente al usuario si no está vinculado
        $clientModel->users()->syncWithoutDetaching([$user->id]);

        // ✅ Cargar conversación completa (sin filtrar por user_id)
        $conversacion = ClientMessage::where(function ($query) use ($numeroLimpio) {
                $query->where('from_number', 'LIKE', "%{$numeroLimpio}%")
                      ->orWhere('to_number', 'LIKE', "%{$numeroLimpio}%");
            })
            ->orderBy('received_at', 'asc')
            ->get();
    }

    return view('responses', compact('chatsConRespuestas', 'conversacion', 'numeroSeleccionado', 'instance'));
}



    // ----------------------------------------------------------------------
    // FUNCIÓN DE AYUDA (Header Dinámico) - SE MANTIENE IGUAL
    // ----------------------------------------------------------------------

    private function getDynamicHeaders(string $apiKey): array
    {
        return [
            'Authorization' => 'Bearer ' . $apiKey,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }
    
    // ----------------------------------------------------------------------
    // TEST INSTANCE (SE MANTIENE IGUAL)
    // ----------------------------------------------------------------------

    public function testInstance($id)
    {
        $instance = WhatsappInstance::findOrFail($id);
        $client = new \GuzzleHttp\Client();
        $url = "https://waapi.app/api/v1/instances/{$instance->instance_id}/client/action/send-message";

        try {
            $client->post($url, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $instance->api_key,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'chatId' => '50300000000@c.us',
                    'message' => 'Test de conexión',
                ],
            ]);
            return response()->json(['status' => 'Activa']);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return response()->json(['status' => 'No autorizada']);
        }
    }
}