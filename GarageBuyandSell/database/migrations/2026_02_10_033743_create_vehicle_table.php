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
            $table->string('make', 50);
            $table->integer('price');
            $table->text('details_and_financing')->nullable();

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
