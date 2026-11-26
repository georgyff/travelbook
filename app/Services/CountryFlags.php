<?php

namespace App\Services;

use Locale;
use Transliterator;

/**
 * Country flags as emoji, keyed by ISO 3166-1 alpha-2 code (plus XK for Kosovo).
 *
 * Codes are matched to country names using the English region names that ICU (the intl extension)
 * knows, normalised for accents, "&"/"and" and a leading "The", plus aliases for common names that
 * ICU spells differently.
 */
class CountryFlags
{
    /**
     * Common names that differ from ICU's English names, normalised as by normalize().
     *
     * @var array<string, string>
     */
    private const ALIASES = [
        'ivory coast' => 'CI',
        'cote divoire' => 'CI',
        'east timor' => 'TL',
        'timor leste' => 'TL',
        'vatican' => 'VA',
        'vatican city' => 'VA',
        'holy see' => 'VA',
        'cape verde' => 'CV',
        'cabo verde' => 'CV',
        'czech republic' => 'CZ',
        'democratic republic of the congo' => 'CD',
        'dr congo' => 'CD',
        'drc' => 'CD',
        'congo kinshasa' => 'CD',
        'republic of the congo' => 'CG',
        'congo' => 'CG',
        'congo brazzaville' => 'CG',
        'micronesia' => 'FM',
        'federated states of micronesia' => 'FM',
        'myanmar' => 'MM',
        'burma' => 'MM',
        'hong kong' => 'HK',
        'macau' => 'MO',
        'macao' => 'MO',
        'turkey' => 'TR',
        'turkiye' => 'TR',
        'united states of america' => 'US',
        'usa' => 'US',
        'uk' => 'GB',
        'great britain' => 'GB',
        'england' => 'GB',
        'swaziland' => 'SZ',
        'palestine' => 'PS',
        'kosovo' => 'XK',
        'north korea' => 'KP',
        'south korea' => 'KR',
        'korea' => 'KR',
        'russia' => 'RU',
        'laos' => 'LA',
        'syria' => 'SY',
        'iran' => 'IR',
        'vietnam' => 'VN',
        'brunei' => 'BN',
        'bolivia' => 'BO',
        'venezuela' => 'VE',
        'tanzania' => 'TZ',
        'moldova' => 'MD',
        'taiwan' => 'TW',
        'gambia' => 'GM',
        'bahamas' => 'BS',
        'netherlands' => 'NL',
        'holland' => 'NL',
        'macedonia' => 'MK',
        'saint kitts and nevis' => 'KN',
        'saint lucia' => 'LC',
        'saint vincent and the grenadines' => 'VC',
        'sao tome and principe' => 'ST',
        'bosnia' => 'BA',
        'trinidad' => 'TT',
        'antigua' => 'AG',
        'faroe islands' => 'FO',
        'faroes' => 'FO',
    ];

    /**
     * Codes ICU names that have no flag of their own: groupings and test regions, and retired codes
     * that share a name with the current one (CS and YU "Serbia", DD "Germany", SU "Russia"…).
     *
     * @var list<string>
     */
    private const EXCLUDED_CODES = [
        'EU', 'EZ', 'QO', 'UN', 'XA', 'XB', 'ZZ',
        'AN', 'BU', 'CS', 'DD', 'DY', 'FX', 'HV', 'NH', 'RH', 'SU', 'TP', 'UK', 'VD', 'YD', 'YU', 'ZR',
    ];

    /** @var array<string, string>|null Code => English name. */
    private static ?array $names = null;

    /**
     * The ISO code for a country name, or null when it can't be matched.
     */
    public function codeFor(string $countryName): ?string
    {
        $key = self::normalize($countryName);

        if (isset(self::ALIASES[$key])) {
            return self::ALIASES[$key];
        }

        foreach (self::names() as $code => $name) {
            if (self::normalize($name) === $key) {
                return $code;
            }
        }

        return null;
    }

    /**
     * The flag emoji for a code: two regional indicator symbols, e.g. "KH" → 🇰🇭.
     */
    public function emoji(string $code): string
    {
        $code = strtoupper($code);

        return mb_chr(0x1F1E6 + ord($code[0]) - 65).mb_chr(0x1F1E6 + ord($code[1]) - 65);
    }

    /**
     * Every country and territory with a flag, as code => English name, sorted by name.
     *
     * @return array<string, string>
     */
    public function options(): array
    {
        return self::names();
    }

    /**
     * @return array<string, string>
     */
    private static function names(): array
    {
        if (self::$names !== null) {
            return self::$names;
        }

        $names = [];
        foreach (range('A', 'Z') as $first) {
            foreach (range('A', 'Z') as $second) {
                $code = $first.$second;
                $name = Locale::getDisplayRegion('und-'.$code, 'en');

                if ($name !== '' && $name !== $code && ! in_array($code, self::EXCLUDED_CODES, true)) {
                    $names[$code] = $name;
                }
            }
        }

        asort($names, SORT_NATURAL | SORT_FLAG_CASE);

        return self::$names = $names;
    }

    /**
     * Lower-case ASCII words: "São Tomé & Príncipe" → "sao tome and principe", "The Gambia" → "gambia".
     */
    private static function normalize(string $name): string
    {
        static $toAscii = null;
        $toAscii ??= Transliterator::create('Any-Latin; Latin-ASCII; Lower()');

        $ascii = $toAscii ? $toAscii->transliterate($name) : mb_strtolower($name);
        $words = preg_replace('/[^a-z0-9]+/', ' ', str_replace('&', ' and ', $ascii));

        return preg_replace('/^the /', '', trim($words));
    }
}
