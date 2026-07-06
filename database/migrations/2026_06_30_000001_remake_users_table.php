<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['finger', 'chosen_finger', 'notifications']);
            $table->string('hora_entrada', 5)->default('09:00')->after('inicio_almoco');
            $table->string('hora_saida', 5)->default('18:00')->after('hora_entrada');
            $table->date('birthdate')->nullable()->after('hora_saida');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['hora_entrada', 'hora_saida', 'birthdate']);
            $table->boolean('finger')->default(false);
            $table->string('chosen_finger')->default('Right Thumb');
            $table->boolean('notifications')->default(true);
        });
    }
};
