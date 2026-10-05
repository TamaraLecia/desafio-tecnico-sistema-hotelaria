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
        if (!Schema::hasTable('reserves')) {
            Schema::create('reserves', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('hotel_id');
                $table->unsignedInteger('room_id');
                $table->date('check_in');
                $table->date('check_out');
                $table->decimal('total', 10, 2);
                $table->timestamps();

                $table->foreign('hotel_id', 'fk_reserves_hotel')->references('id')->on('hotels')->onDelete('cascade');

                $table->foreign('room_id', 'fk_reserves_room')->references('id')->on('rooms')->onDelete('cascade');
            });
        }

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reserves');
    }
};
