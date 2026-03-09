<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds views_count to track how many times a vehicle is viewed from CustomerGarage.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('vehicles', 'views_count')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->unsignedInteger('views_count')->default(0)->after('details_and_financing');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('vehicles', 'views_count')) {
            Schema::table('vehicles', fn (Blueprint $table) => $table->dropColumn('views_count'));
        }
    }
};
