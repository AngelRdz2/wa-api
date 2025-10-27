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
        // ... en la función up() de create_whatsapp_instances_table.php

Schema::create('whatsapp_instances', function (Blueprint $table) {
    $table->id(); // Crea el campo 'id' (Primary Key, auto-incrementable)
    
    // Nombre para identificarla fácilmente (ej: "Instancia Mora 30")
    $table->string('name')->unique(); 
    
    // Campo para asignar el área (ej: "Mora 30", "VTO").
    $table->string('area')->unique(); 
    
    // El ID único que te da waapi.app
    $table->string('instance_id')->unique(); 
    
    // El token/clave de API de esa instancia
    $table->string('api_key'); 
    
    // Campo para guardar el número de teléfono real del WhatsApp (opcional, pero útil)
    $table->string('phone')->nullable(); 
    
    $table->timestamps();
});


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
       // ... en la función down()
Schema::dropIfExists('whatsapp_instances');
    }
};
