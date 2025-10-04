<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            // Primero eliminar la FK
            $table->dropForeign(['classification_id']);

            // Luego eliminar la columna
            $table->dropColumn('classification_id');
        });
    }

    public function down(): void
    {
        Schema::table('message_templates', function (Blueprint $table) {
            $table->unsignedBigInteger('classification_id')->after('id');

            // Volver a crear la FK si quieres
            $table->foreign('classification_id')->references('id')->on('moratorium_classifications');
        });
    }
};
