<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * How many times a place was visited and how many pictures were taken there, both typed in by hand.
 * Empty until filled in.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->unsignedInteger('times_visited')->nullable()->after('status');
            $table->unsignedInteger('pictures')->nullable()->after('times_visited');
        });
    }

    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->dropColumn(['times_visited', 'pictures']);
        });
    }
};
