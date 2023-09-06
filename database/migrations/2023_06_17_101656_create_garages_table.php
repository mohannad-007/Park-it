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
        Schema::create('garages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->integer('floor_number');
            $table->boolean('is_open')->nullable();
            $table->double('price_per_hour');
            $table->integer('parks_number');
            $table->time('time_open');
            $table->time('time_close');
            $table->string('garage_information', '10000');
            $table->timestamps();
            $table->foreignId("garage_locations_id")->constrained('garage_locations')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garages');
    }
};
