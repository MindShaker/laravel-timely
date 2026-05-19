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
        Schema::table('logs', function (Blueprint $blueprint) {
            // Adiciona a coluna 'status' com o valor padrão 'approved'
            // O 'after' serve para organizar a coluna visualmente na base de dados (ajusta se preferires depois de outro campo)
            $blueprint->string('status')
                      ->default('approved')
                      ->after('updated_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('logs', function (Blueprint $blueprint) {
            // Remove a coluna caso faças um php artisan migrate:rollback
            $blueprint->dropColumn('status');
        });
    }
};