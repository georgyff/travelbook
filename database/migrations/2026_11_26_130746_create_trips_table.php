<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Trips. Total cost, the trip's regions and the average per day are derived, so not stored.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status')->default('idea');
            $table->string('type')->nullable();
            $table->date('starts_on')->nullable();
            $table->date('ends_on')->nullable();
            $table->unsignedSmallInteger('days')->nullable();
            $table->unsignedInteger('walked_steps')->nullable();
            $table->decimal('walked_km', 8, 1)->nullable();
            $table->unsignedInteger('pictures')->nullable();
            $table->decimal('cost_travel', 10, 2)->nullable();
            $table->decimal('cost_housing', 10, 2)->nullable();
            $table->decimal('cost_in_travel', 10, 2)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
