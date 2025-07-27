<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MessageTemplatesSeeder extends Seeder
{
    public function run()
    {
        DB::table('message_templates')->insert([
            [
                'categoria' => 'morosos',
                'mensaje' => 'Hola {nombre}, tu factura {factura} tiene un saldo pendiente de {monto}.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'categoria' => 'normales',
                'mensaje' => 'Estimado {nombre}, su factura {factura} ha sido procesada con éxito.',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            // Agrega más plantillas si quieres
        ]);
    }
}
