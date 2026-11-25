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

    protected function prepareForValidation(): void
    {
        $this->merge(['name' => trim((string) $this->input('name'))]);
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
        ];
    }
}
