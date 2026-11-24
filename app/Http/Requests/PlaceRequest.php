<?php

namespace App\Http\Requests;

use App\Enums\CompletionStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceRequest extends FormRequest
{
    /**
     * The logbook runs locally without accounts, so anyone using it may edit.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Trim the name, drop blank fact rows and read the capital checkbox as a boolean.
     */
    protected function prepareForValidation(): void
    {
        $facts = $this->input('facts', []);

        $this->merge([
            'name' => trim((string) $this->input('name')),
            'is_capital' => $this->boolean('is_capital'),
            'facts' => is_array($facts)
                ? array_values(array_filter(array_map(fn (mixed $fact): string => trim((string) $fact), $facts), fn (string $fact): bool => $fact !== ''))
                : $facts,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'place_type_id' => ['required', 'integer', Rule::exists('place_types', 'id')],
            'is_capital' => ['boolean'],
            'status' => ['required', Rule::enum(CompletionStatus::class)],
            'short_history' => ['nullable', 'string', 'max:10000'],
            'facts' => ['array', 'max:50'],
            'facts.*' => ['string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Give the place a name.',
            'place_type_id.required' => 'Choose a type.',
            'status.required' => 'Choose a status.',
            'facts.*.max' => 'Keep each fact under 1000 characters.',
        ];
    }
}
