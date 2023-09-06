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
        Schema::create('w_parks', function (Blueprint $table) {
//            $table->increments('id');
            $table->id();
            $table->date('date');
            $table->time('time_begin');
            $table->time('time_end');
            $table->time('time_reservation');
            $table->double('price');
            $table->boolean('pay');
            $table->timestamps();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->unsignedBigInteger('parking_id');
            $table->foreign('parking_id')->references('id')->on('parkings')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('garage_id')->constrained('garages')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('floor_id')->constrained('floors')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('car_id')->constrained('cars')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('w_parks');
    }
};
