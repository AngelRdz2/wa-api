<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClientMessage;

class WebhookController extends Controller
{
   public function handle(Request $request)
{
    \Log::info('Webhook recibido', $request->all());

    $data = $request->all();

    if (isset($data['type']) && $data['type'] === 'message' && isset($data['message'])) {
        $phoneWithSuffix = $data['message']['from']; // ej: "50312345678@c.us"
        $phone = preg_replace('/@.*$/', '', $phoneWithSuffix);

        $messageText = $data['message']['body'] ?? '';

        ClientMessage::create([
            'phone' => $phone,
            'message' => $messageText,
            'direction' => 'inbound',
        ]);
    }

    return response()->json(['status' => 'success']);
}

    
}
