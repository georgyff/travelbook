<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The itinerary on a trip page: one row per stop, every cell typed in by hand.
 * City and country are plain text, not linked to the Logbook.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_stops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->date('visited_on')->nullable();
            $table->string('city')->nullable();
            $table->decimal('transport_cost', 10, 2)->nullable();
            $table->decimal('hotel_cost', 10, 2)->nullable();
            $table->string('times')->nullable();
            $table->string('country')->nullable();
            $table->timestamps();
            $table->index(['trip_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_stops');
    }
};
