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
        Schema::table('users', function (Blueprint $table) {
        // Usamos ENUM para limitar las opciones a tus áreas.
        // Esto se relaciona con el campo 'area' de la tabla 'whatsapp_instances'.
        $table->enum('area', ['VTO', 'Mora 30', 'General'])
          ->default('General')
          ->after('email'); 
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         Schema::table('users', function (Blueprint $table) {
         $table->dropColumn('area');
        });
    }
};
