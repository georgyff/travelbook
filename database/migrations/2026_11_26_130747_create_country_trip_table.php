<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The Logbook countries a trip went to, in the order they were travelled.
 * Deleting a country from the Logbook takes it off its trips.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('country_trip', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('position');
            $table->unique(['trip_id', 'country_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('country_trip');
    }
};
