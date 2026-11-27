<?php

namespace App\Http\Requests;

use App\Enums\TripStatus;
use App\Enums\TripType;
use App\Models\Country;
use App\Services\LogbookProgress;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;

/**
 * A trip from the edit dialog. Countries arrive as names typed (with suggestions) in a list and
 * must match countries in the Logbook, ignoring case.
 */
class TripRequest extends FormRequest
{
    /** @var array<string, int>|null Normalised Logbook country name => id. */
    private ?array $countryIdsByName = null;

    /**
     * The logbook runs locally without accounts, so anyone using it may edit.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Trim the name and drop blank country and place rows.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => trim((string) $this->input('name')),
            'countries' => $this->filledNames('countries'),
            'places_visited' => $this->filledNames('places_visited'),
        ]);
    }

    /**
     * A list input's entries, trimmed, without the blank ones. Anything that isn't a list is left for the rules to reject.
     */
    private function filledNames(string $key): mixed
    {
        $names = $this->input($key, []);

        if (! is_array($names)) {
            return $names;
        }

        return array_values(array_filter(array_map(fn (mixed $name): string => trim((string) $name), $names), fn (string $name): bool => $name !== ''));
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $money = ['nullable', 'numeric', 'min:0', 'max:99999999'];

        return [
            'name' => ['required', 'string', 'max:150'],
            'status' => ['required', Rule::enum(TripStatus::class)],
            'type' => ['nullable', Rule::enum(TripType::class)],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'walked_steps' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'walked_km' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'pictures' => ['nullable', 'integer', 'min:0', 'max:999999999'],
            'cost_travel' => $money,
            'cost_housing' => $money,
            'cost_in_travel' => $money,
            'countries' => ['array', 'max:100'],
            'countries.*' => ['string', 'distinct:ignore_case', $this->isLogbookCountry(...)],
            'places_visited' => ['array', 'max:200'],
            'places_visited.*' => ['string', 'max:150', 'distinct:ignore_case'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Give the trip a name.',
            'status.required' => 'Choose a status.',
            'ends_on.after_or_equal' => 'The end date can’t be before the start date.',
            'countries.*.distinct' => 'This country is already on the trip.',
            'places_visited.*.distinct' => 'This place is already listed.',
            'places_visited.*.max' => 'Keep each place under 150 characters.',
            '*.integer' => 'Use a whole number.',
            '*.numeric' => 'Use a number.',
            '*.min' => 'Use zero or more.',
        ];
    }

    /**
     * The trip's fields, without its countries. The dialog always sends the whole trip, so a field
     * left out is saved as empty.
     *
     * @return array<string, mixed>
     */
    public function tripAttributes(): array
    {
        $optional = ['type', 'starts_on', 'ends_on', 'days', 'walked_steps', 'walked_km', 'pictures', 'cost_travel', 'cost_housing', 'cost_in_travel'];

        return [...array_fill_keys($optional, null), 'places_visited' => [], ...Arr::except($this->validated(), ['countries'])];
    }

    /**
     * The ids of the trip's countries, in the order given.
     *
     * @return list<int>
     */
    public function countryIds(): array
    {
        return array_map(
            fn (string $name): int => $this->countryIdsByName()[LogbookProgress::normalizeName($name)],
            $this->validated('countries', []),
        );
    }

    private function isLogbookCountry(string $attribute, mixed $value, Closure $fail): void
    {
        if (! isset($this->countryIdsByName()[LogbookProgress::normalizeName((string) $value)])) {
            $fail('“'.$value.'” isn’t in the Logbook. Pick a country from the suggestions, or add it to the Logbook first.');
        }
    }

    /**
     * @return array<string, int>
     */
    private function countryIdsByName(): array
    {
        return $this->countryIdsByName ??= Country::query()
            ->pluck('id', 'name')
            ->mapWithKeys(fn (int $id, string $name): array => [LogbookProgress::normalizeName($name) => $id])
            ->all();
    }
}
