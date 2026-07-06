<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('log_approvals');
        Schema::dropIfExists('admin_logs');
        Schema::dropIfExists('logs');
        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        // Legacy tables are not restored on rollback
    }
};
