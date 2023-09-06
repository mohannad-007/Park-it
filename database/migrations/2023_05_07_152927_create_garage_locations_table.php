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
        Schema::create('garage_locations', function (Blueprint $table) {
            $table->id();
            $table->double("Longitude_lines");//خطوط الطول
            $table->double("Latitude_lines");//خطوط العرض
            $table->string("city");//syria
            $table->string("country");//damas
            $table->string("street");//medan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garage_locations');
    }
};
