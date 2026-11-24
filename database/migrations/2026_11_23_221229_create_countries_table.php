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
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('continent_id')->constrained()->restrictOnDelete();
            $table->string('name')->unique();
            $table->string('status')->default('not_completed');
            $table->text('history')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['continent_id', 'position']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('countries');
    }
};
