<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClientMessage;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
 public function handle(Request $request)
{
    try {
        $payload = json_encode($request->all(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $signature = $request->header('X-WAAPI-HMAC');
        $expected = hash_hmac('sha256', $payload, env('WAAPI_WEBHOOK_SECRET'));

        if (!is_string($signature) || !hash_equals($expected, $signature)) {
            throw new \Exception('Firma HMAC inválida');
        }

        $data = $request->all();
        if (isset($data['event']) && $data['event'] === 'message') {
            $msg = $data['data']['message'] ?? null;

            if (!is_array($msg)) {
                throw new \Exception('Estructura de mensaje inesperada');
            }

            $from = $msg['from'] ?? null;
            $to = $msg['to'] ?? null;
            $content = $msg['body'] ?? null;
            $direction = isset($msg['fromMe']) && $msg['fromMe'] ? 'outbound' : 'inbound';

            if ($from && $to && $content && trim($content) !== '') {
                Log::info('Valor de phone:', [$from]);
                ClientMessage::create([
                    'from_number' => $from,
                    'to_number' => $to,
                    'message' => $content,
                    'direction' => $direction,
                    'received_at' => now(),
                    'phone' => $from,
                ]);
                Log::info('Mensaje guardado correctamente');
            } else {
                Log::info('Mensaje incompleto o vacío, no se guarda');
                return response()->json(['status' => 'ignored'], 200);
            }
        }

        return response()->json(['status' => 'ok'], 200);

    } catch (\Exception $e) {
        Log::error('Error en webhook: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

}
