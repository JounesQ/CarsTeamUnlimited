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
        
        Schema::create('vehicles', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->string('title', 200);
            $table->string('slug', 200)->unique();
            $table->string('status', 20)->default('available');

            $table->integer('year');
            $table->string('make', 50);
            $table->string('model', 50);
            $table->string('vehicle_type', 20)->default('car');
            $table->string('category', 50)->nullable();

            $table->string('transmission', 30)->default('automatic');
            $table->string('fuel_type', 30)->nullable();
            $table->string('color', 50)->nullable();
            $table->integer('door_count')->nullable();
            $table->integer('seat_capacity')->nullable();
            $table->integer('mileage')->nullable();
            $table->string('grade', 50)->nullable();

            $table->decimal('price', 12, 2);
            $table->boolean('is_negotiable')->default(true);
            $table->decimal('down_payment', 12, 2)->nullable();
            $table->boolean('dp_all_in')->default(true);

            $table->json('financing_options')->nullable();

            $table->unsignedInteger('views_count')->default(0);

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
