<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * The whole itinerary of a trip. Each column arrives as its own list (stop_dates[],
 * stop_cities[]…), one entry per row, so an error like "stop_cities.2" points at the cell typed in.
 * Rows left completely blank are dropped.
 */
class TripItineraryRequest extends FormRequest
{
    /**
     * Form list => stop attribute, in table order.
     *
     * @var array<string, string>
     */
    public const COLUMNS = [
        'stop_dates' => 'visited_on',
        'stop_cities' => 'city',
        'stop_transport' => 'transport_cost',
        'stop_hotel' => 'hotel_cost',
        'stop_times' => 'times',
        'stop_countries' => 'country',
    ];

    /**
     * The logbook runs locally without accounts, so anyone using it may edit.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Trim the text cells.
     */
    protected function prepareForValidation(): void
    {
        foreach (['stop_cities', 'stop_times', 'stop_countries'] as $column) {
            $values = $this->input($column, []);

            if (is_array($values)) {
                $this->merge([$column => array_map(fn (mixed $value): ?string => is_string($value) && trim($value) !== '' ? trim($value) : null, $values)]);
            }
        }
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $money = ['nullable', 'numeric', 'min:0', 'max:99999999'];
        $rules = [];

        foreach (array_keys(self::COLUMNS) as $column) {
            $rules[$column] = ['array', 'max:300'];
        }

        return $rules + [
            'stop_dates.*' => ['nullable', 'date'],
            'stop_cities.*' => ['nullable', 'string', 'max:150'],
            'stop_transport.*' => $money,
            'stop_hotel.*' => $money,
            'stop_times.*' => ['nullable', 'string', 'max:100'],
            'stop_countries.*' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'stop_dates.*.date' => 'Use a date.',
            'stop_transport.*.numeric' => 'The transport cost must be a number.',
            'stop_hotel.*.numeric' => 'The hotel cost must be a number.',
            '*.*.min' => 'Use zero or more.',
            '*.*.max' => 'That’s too long.',
        ];
    }

    /**
     * The filled-in rows as stop attributes, in table order.
     *
     * @return list<array<string, mixed>>
     */
    public function rows(): array
    {
        $validated = $this->validated();
        $rowCount = max(array_map(fn (string $column): int => count($validated[$column] ?? []), array_keys(self::COLUMNS)));
        $rows = [];

        for ($index = 0; $index < $rowCount; $index++) {
            $row = [];
            foreach (self::COLUMNS as $column => $attribute) {
                $row[$attribute] = $validated[$column][$index] ?? null;
            }

            if (array_filter($row, fn (mixed $value): bool => $value !== null) !== []) {
                $rows[] = $row;
            }
        }

        return $rows;
    }
}
