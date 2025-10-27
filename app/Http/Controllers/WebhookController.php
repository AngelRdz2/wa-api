<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClientMessage;
use App\Models\WhatsappInstance;
use App\Models\Client as ClientModel;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WebhookController extends Controller
{
    public function handle(Request $request)
    {
        Log::alert('📩 WEBHOOK HIT: La solicitud llegó a Laravel.');

        try {
            $payload = $request->getContent();
            $signature = $request->header('X-WAAPI-HMAC');
            $secret = config('services.waapi.webhook_secret');
            $expected = hash_hmac('sha256', $payload, $secret);

            // 🔐 Validación HMAC (opcional en pruebas)
            // if (!is_string($signature) || !hash_equals($expected, $signature)) {
            //     Log::error('Firma HMAC inválida', ['received' => $signature, 'expected' => $expected]);
            //     return response()->json(['status' => 'invalid_signature'], 403);
            // }

            $data = $request->all();
            Log::debug('📦 Payload recibido', ['data' => $data]);

            $event = strtolower($data['event'] ?? $data['type'] ?? '');
            Log::info('🔍 Evento recibido:', ['event' => $event]);

            $waapiInstanceId = $data['instance_id']
                ?? $data['instanceId']
                ?? $data['data']['instance_id']
                ?? $data['data']['instanceId']
                ?? null;

            if (!$waapiInstanceId) {
                Log::warning('⚠️ Webhook ignorado: Falta el instance_id.', $data);
                return response()->json(['status' => 'ignored: missing instance_id'], 200);
            }

            $instance = WhatsappInstance::where('instance_id', $waapiInstanceId)->first();
            if (!$instance) {
                Log::error('❌ Instancia no encontrada para el ID: ' . $waapiInstanceId);
                return response()->json(['status' => 'instance_not_found'], 200);
            }

            if ($event === 'message') {
                $msg = $data['data']['message'] ?? null;
                Log::info('🧪 Mensaje recibido:', ['msg' => $msg]);

                if (!is_array($msg)) {
                    Log::info('⚠️ Estructura de mensaje no válida, se ignora.');
                    return response()->json(['status' => 'ignored_invalid_structure'], 200);
                }

                if ($msg['fromMe'] ?? true) {
                    Log::info('📤 Mensaje saliente recibido, se ignora.');
                    return response()->json(['status' => 'ignored_outbound'], 200);
                }

                $from = $msg['from'] ?? null;
                $to = $msg['to'] ?? null;
                $content = $msg['body'] ?? null;
                $timestamp = $msg['timestamp'] ?? $msg['t'] ?? time();

                if ($from && $to && $content && trim($content) !== '') {
                    $cleanClientPhone = preg_replace('/[^0-9]/', '', $from);

                    $client = ClientModel::firstOrCreate(
                        ['phone' => $cleanClientPhone],
                        ['name' => 'Cliente Chat', 'dui' => '000000000', 'date' => Carbon::now()->toDateString()]
                    );

                    $user = $client->users()->first()
                        ?? $instance->users()->first()
                        ?? User::role('admin')->first();

                    $userId = $user?->id;

                    if ($user && !$client->users->contains($user)) {
                        $client->users()->attach($user->id);
                    }

                    ClientMessage::create([
                        'client_id' => $client->id,
                        'user_id' => $userId,
                        'whatsapp_instance_id' => $instance->id,
                        'from_number' => $from,
                        'to_number' => $to,
                        'message' => $content,
                        'direction' => 'inbound',
                        'received_at' => Carbon::createFromTimestamp($timestamp),
                    ]);

                    Log::info('✅ Mensaje entrante guardado para cliente: ' . $client->phone);
                } else {
                    Log::info('⚠️ Mensaje incompleto o vacío, no se guarda.');
                    return response()->json(['status' => 'ignored_empty'], 200);
                }
            } else {
                Log::info('ℹ️ Evento no procesado: ' . $event);
            }

            return response()->json(['status' => 'ok'], 200);

        } catch (\Exception $e) {
            Log::error('🔥 Error FATAL en webhook: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }
}
