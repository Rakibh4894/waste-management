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
        Schema::create('waste_request_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('waste_request_id')
                  ->constrained('waste_requests')
                  ->onDelete('cascade'); // delete images if request is deleted

            $table->string('image_path'); // store image path (e.g., uploads/waste/123.jpg)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('waste_request_images');
    }
};
