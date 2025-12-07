<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('monthly_bill_amounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('city_corporation_id');
            $table->unsignedBigInteger('ward_id');
            $table->decimal('amount', 10, 2);
            $table->boolean('is_active')->default(false);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('city_corporation_id')->references('id')->on('city_corporations')->onDelete('cascade');
            $table->foreign('ward_id')->references('id')->on('wards')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('monthly_bill_amounts');
    }
};
