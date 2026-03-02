<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds details_and_financing column if missing (production DB may have been created before it existed).
     */
    public function up(): void
    {
        if (!Schema::hasColumn('vehicles', 'details_and_financing')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->text('details_and_financing')->nullable()->after('price');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('vehicles', 'details_and_financing')) {
            Schema::table('vehicles', fn (Blueprint $table) => $table->dropColumn('details_and_financing'));
        }
    }
};
