<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // ... en la función up()

        Schema::table('client_messages', function (Blueprint $table) {
         // 1. Crea la columna y la llave foránea
         $table->foreignId('whatsapp_instance_id') 
             ->nullable() // Lo hacemos nullable por si hay mensajes muy antiguos sin instancia
               ->constrained('whatsapp_instances') // Indica que referencia a la tabla 'whatsapp_instances'
                ->after('client_id'); // Posiciona la columna después de 'client_id' (o donde prefieras)
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       // ... en la función down() (para poder revertir la migración)

        Schema::table('client_messages', function (Blueprint $table) {
            $table->dropConstrainedForeignId('whatsapp_instance_id');
        });
    }
};
