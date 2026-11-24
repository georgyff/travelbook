<?php

namespace App\Http\Requests;

use App\Models\Continent;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ContinentRequest extends FormRequest
{
    /**
     * The logbook runs locally without accounts, so anyone using it may edit.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name'))]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Continent|null $continent */
        $continent = $this->route('continent');

        return [
            'name' => ['required', 'string', 'max:100', Rule::unique('continents', 'name')->ignore($continent)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Give the continent a name.',
            'name.unique' => 'There is already a continent with this name.',
        ];
    }
}
