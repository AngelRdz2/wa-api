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
        Schema::create('client_messages', function (Blueprint $table) {
            $table->id();

            // Teléfono del cliente (quien envía o recibe)
            $table->string('from_number')->nullable();  // número del remitente
            $table->string('to_number')->nullable();   // número del destinatario

            // Contenido del mensaje
            $table->text('message');

            // inbound = recibido | outbound = enviado
            $table->enum('direction', ['inbound', 'outbound']);

            // Fecha/hora que envía WAAPI (no siempre coincide con created_at)
            $table->timestamp('received_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_messages');
    }
};
