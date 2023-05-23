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
        Schema::create('ingredients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->enum('name', ['BEEF', 'ONION', 'CHEESE'])->unique();
            $table->integer('total_weight');
            $table->integer('remaining_weight');
            $table->enum('weight_unit', ['GRAM', 'KILOGRAM', 'LITRE']);
            $table->enum('stock_status', ['FILLED', 'NOTIFIED', 'EMPTY'])->default('FILLED');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
