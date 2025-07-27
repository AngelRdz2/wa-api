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
    $table->string('phone');
    $table->text('message');
    $table->enum('direction', ['inbound', 'outbound']); // recibido o enviado
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
