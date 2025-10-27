<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instance_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('whatsapp_instance_id')->constrained()->onDelete('cascade');
            $table->unique(['user_id', 'whatsapp_instance_id']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('instance_user');
    }
};