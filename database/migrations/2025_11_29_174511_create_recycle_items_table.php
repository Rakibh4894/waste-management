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
        Schema::create('recycle_items', function (Blueprint $table) {
            $table->id();
            $table->integer('recycle_process_id');
            $table->integer('item_type');
            $table->string('item_name');
            $table->decimal('quantity', 10, 2)->nullable();
            $table->string('unit')->nullable();
            $table->decimal('weight', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recycle_items');
    }
};
