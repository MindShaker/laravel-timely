<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE absences MODIFY COLUMN type ENUM('vacation','birthday','client','internal','undefined') NOT NULL");

        Schema::table('absences', function (Blueprint $table) {
            $table->boolean('remote')->default(false)->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('absences', function (Blueprint $table) {
            $table->dropColumn('remote');
        });

        DB::statement("ALTER TABLE absences MODIFY COLUMN type ENUM('vacation','birthday') NOT NULL");
    }
};
