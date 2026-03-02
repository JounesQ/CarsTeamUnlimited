<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Drops columns that VehicleSeeder used but are not in the vehicles table schema.
     * Current schema: id, title, slug, status, make, price, details_and_financing, timestamps.
     */
    public function up(): void
    {
        $obsoleteColumns = [
            'year',
            'model',
            'vehicle_type',
            'category',
            'transmission',
            'fuel_type',
            'color',
            'door_count',
            'seat_capacity',
            'mileage',
            'grade',
            'is_negotiable',
            'down_payment',
            'dp_all_in',
            'financing_options',
            'views_count',
        ];

        $existing = array_values(array_filter($obsoleteColumns, fn ($col) => Schema::hasColumn('vehicles', $col)));

        if (!empty($existing)) {
            Schema::table('vehicles', fn (Blueprint $table) => $table->dropColumn($existing));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cannot restore dropped columns without knowing original types.
        // Run a separate migration to re-add if needed.
    }
};
