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
        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->unsignedInteger('id')->autoIncrement();
                $table->primary('id');
                $table->unsignedInteger('reserve_id');
                $table->string('method', 50);
                $table->decimal('value', 10, 2);
                $table->timestamps();

                $table->foreign('reserve_id', 'fk_payments_reserve')->references('id')->on('reserves')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
