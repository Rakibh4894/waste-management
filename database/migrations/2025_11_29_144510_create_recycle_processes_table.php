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
        Schema::create('recycle_processes', function (Blueprint $table) {
            $table->id();

            // Link to Waste Request
            $table->integer('waste_request_id');
            $table->foreign(columns: 'waste_request_id')->references('id')->on('waste_requests')->onDelete('cascade');

            // Sorting Info
            $table->enum('recycle_status', [
                'waiting_for_sorting',
                'sorting_completed',
                'sent_to_recycling',
                'recycling_in_process',
                'recycled',
                'failed'
            ])->default('waiting_for_sorting');

            $table->string('sorted_material')->nullable(); // plastic, metal, glass, paper...
            $table->unsignedBigInteger('sorting_officer_id')->nullable();
            $table->timestamp('sorting_completed_at')->nullable();

            // Recycling Info
            $table->string('recycled_output')->nullable(); // pellets, compost, paper sheet, etc.
            $table->unsignedBigInteger('recycling_operator_id')->nullable();
            $table->timestamp('recycling_completed_at')->nullable();

            // Additional notes
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recycle_processes');
    }
};
