<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('notifications', 'read') && ! Schema::hasColumn('notifications', 'is_read')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->renameColumn('read', 'is_read');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('notifications', 'is_read') && ! Schema::hasColumn('notifications', 'read')) {
            Schema::table('notifications', function (Blueprint $table) {
                $table->renameColumn('is_read', 'read');
            });
        }
    }
};
