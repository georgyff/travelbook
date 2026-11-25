<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Continents and regions become one thing: a region. Each country belongs to one region, which is
 * both its group in the country index and a row in the Regions progress list.
 *
 * Each continent becomes the region with the same name (ignoring case), or a new region added at the
 * end. Region country lists are dropped: a region's countries are now the logbook countries in it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable()->after('id')->constrained()->restrictOnDelete();
        });

        $now = now();
        $nextPosition = (int) DB::table('regions')->max('position');
        $regionIdsByName = DB::table('regions')->get(['id', 'name'])
            ->mapWithKeys(fn (object $region): array => [mb_strtolower(trim($region->name)) => $region->id])
            ->all();

        foreach (DB::table('continents')->orderBy('position')->orderBy('id')->get() as $continent) {
            $key = mb_strtolower(trim($continent->name));

            $regionIdsByName[$key] ??= DB::table('regions')->insertGetId([
                'name' => $continent->name,
                'position' => ++$nextPosition,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('countries')->where('continent_id', $continent->id)->update(['region_id' => $regionIdsByName[$key]]);
        }

        Schema::table('countries', function (Blueprint $table) {
            $table->dropIndex(['continent_id', 'position']);
            $table->dropConstrainedForeignId('continent_id');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->foreignId('region_id')->nullable(false)->change();
            $table->index(['region_id', 'position']);
        });

        Schema::drop('region_countries');
        Schema::drop('continents');
    }

    /**
     * Restores continents from regions (one continent per region) and rebuilds each region's
     * country list from the countries in it.
     */
    public function down(): void
    {
        Schema::create('continents', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
        });

        Schema::create('region_countries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->unique(['region_id', 'name']);
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->foreignId('continent_id')->nullable()->after('id')->constrained()->restrictOnDelete();
        });

        $now = now();

        foreach (DB::table('regions')->orderBy('position')->orderBy('id')->get() as $region) {
            $continentId = DB::table('continents')->insertGetId([
                'name' => $region->name,
                'position' => $region->position,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('countries')->where('region_id', $region->id)->update(['continent_id' => $continentId]);

            $countryNames = DB::table('countries')->where('region_id', $region->id)->orderBy('position')->orderBy('id')->pluck('name');
            foreach ($countryNames as $index => $name) {
                DB::table('region_countries')->insert([
                    'region_id' => $region->id,
                    'name' => $name,
                    'position' => $index + 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }

        Schema::table('countries', function (Blueprint $table) {
            $table->dropIndex(['region_id', 'position']);
            $table->dropConstrainedForeignId('region_id');
        });

        Schema::table('countries', function (Blueprint $table) {
            $table->foreignId('continent_id')->nullable(false)->change();
            $table->index(['continent_id', 'position']);
        });
    }
};
