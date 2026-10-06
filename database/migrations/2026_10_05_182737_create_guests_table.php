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
       if (!Schema::hasTable('guests')) {
            Schema::create('guests', function (Blueprint $table) {
                $table->unsignedInteger('id')->autoIncrement();
                $table->primary('id');
                $table->unsignedInteger('reserve_id');
                $table->string('name', 255);
                $table->string('last_name', 255);
                $table->string('phone', 50);
                $table->timestamps();

                $table->foreign('reserve_id', 'fk_guests_reserve')->references('id')->on('reserves')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
