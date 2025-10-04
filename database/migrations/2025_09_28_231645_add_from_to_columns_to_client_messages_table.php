<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('client_messages', function (Blueprint $table) {
            $table->string('from_number')->nullable()->after('id');
            $table->string('to_number')->nullable()->after('from_number');
        });
    }

    public function down(): void
    {
        Schema::table('client_messages', function (Blueprint $table) {
            $table->dropColumn(['from_number', 'to_number']);
        });
    }
};

