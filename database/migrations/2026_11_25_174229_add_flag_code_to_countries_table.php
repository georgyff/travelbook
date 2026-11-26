<?php

use App\Services\CountryFlags;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Adds each country's flag as its two-letter ISO code, filled in from the country name where it matches.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->char('flag_code', 2)->nullable()->after('name');
        });

        $flags = new CountryFlags;

        foreach (DB::table('countries')->get(['id', 'name']) as $country) {
            $code = $flags->codeFor($country->name);

            if ($code !== null) {
                DB::table('countries')->where('id', $country->id)->update(['flag_code' => $code]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('flag_code');
        });
    }
};
