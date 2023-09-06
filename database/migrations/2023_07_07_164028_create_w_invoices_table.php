<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    //اسم مستخدم وتاريخ ووقت اشغال والقيمة
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('w_invoices', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->double('money');
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->date('date');
            $table->double('Duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('w_invoices');
    }
};
