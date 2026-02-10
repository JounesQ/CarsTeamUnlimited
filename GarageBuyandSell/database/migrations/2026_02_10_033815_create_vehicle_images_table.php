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
        // vehicle_images table
        Schema::create('vehicle_images', function (Blueprint $table) {
            $table->id();

            $table->uuid('vehicle_id');
            $table->string('image_path', 255);
            $table->unsignedTinyInteger('position'); // 1–4
            $table->boolean('is_primary')->default(false);

            $table->timestamps();

            $table->foreign('vehicle_id')
                ->references('id')
                ->on('vehicles')
                ->onDelete('cascade');

            $table->unique(['vehicle_id', 'position']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_images');
    }
};
