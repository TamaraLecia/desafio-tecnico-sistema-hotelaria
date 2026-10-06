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
      if (!Schema::hasTable('rooms')) {
            Schema::create('rooms', function (Blueprint $table) {
                $table->unsignedInteger('id')->autoIncrement();
                $table->primary('id');
                $table->unsignedInteger('hotel_id'); 
                $table->string('name', 255);
                $table->timestamps();

                $table->foreign('hotel_id', 'fk_rooms_hotel')->references('id')->on('hotels')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
