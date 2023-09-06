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
        Schema::create('garage_employees', function (Blueprint $table) {
//            $table->increments('id');
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->integer('phone_number')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('address',255);
            $table->timestamps();
            $table->unsignedBigInteger('garage_id');
            $table->foreign('garage_id')->references('id')->on('garages')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('garage_employees');
    }
};
