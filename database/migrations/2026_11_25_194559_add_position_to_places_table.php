<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Places get a hand-set order within their type in each country. Existing places keep the order
 * they were shown in (by id).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->unsignedInteger('position')->default(0)->after('is_capital');
            $table->index(['country_id', 'place_type_id', 'position']);
        });

        $nextPositions = [];
        foreach (DB::table('places')->orderBy('id')->get(['id', 'country_id', 'place_type_id']) as $place) {
            $group = $place->country_id.'-'.$place->place_type_id;
            $nextPositions[$group] = ($nextPositions[$group] ?? 0) + 1;

            DB::table('places')->where('id', $place->id)->update(['position' => $nextPositions[$group]]);
        }
    }

    public function down(): void
    {
        Schema::table('places', function (Blueprint $table) {
            $table->dropIndex(['country_id', 'place_type_id', 'position']);
            $table->dropColumn('position');
        });
    }
};
