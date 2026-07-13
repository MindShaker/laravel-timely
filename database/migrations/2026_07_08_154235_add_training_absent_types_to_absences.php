<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE absences MODIFY COLUMN type ENUM('vacation','birthday','client','internal','undefined','training','absent') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE absences MODIFY COLUMN type ENUM('vacation','birthday','client','internal','undefined') NOT NULL");
    }
};
