<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('vehicles', 'can_test_drive')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->boolean('can_test_drive')->default(true)->after('price');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('vehicles', 'can_test_drive')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->dropColumn('can_test_drive');
            });
        }
    }
};
