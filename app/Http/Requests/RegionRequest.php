<?php

namespace App\Http\Requests;

use App\Models\Region;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegionRequest extends FormRequest
{
    /**
     * The logbook runs locally without accounts, so anyone using it may edit.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Trim the name and the country names, and drop blank country rows.
     */
    protected function prepareForValidation(): void
    {
        $countries = $this->input('countries', []);

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'countries' => is_array($countries)
                ? array_values(array_filter(array_map(fn (mixed $name): string => trim((string) $name), $countries), fn (string $name): bool => $name !== ''))
                : $countries,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Region|null $region */
        $region = $this->route('region');

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('regions', 'name')->ignore($region)],
            'countries' => ['array', 'max:300'],
            'countries.*' => ['string', 'max:100', 'distinct:ignore_case'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Give the region a name.',
            'name.unique' => 'There is already a region with this name.',
            'countries.*.distinct' => 'This country is already in the list.',
        ];
    }
}
