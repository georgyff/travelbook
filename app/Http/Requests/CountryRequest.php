<?php

namespace App\Http\Requests;

use App\Enums\CompletionStatus;
use App\Models\Country;
use App\Services\LogbookProgress;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CountryRequest extends FormRequest
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
        return [
            'name' => ['required', 'string', 'max:100', $this->nameIsUnique(...)],
            'region_id' => ['required', 'integer', Rule::exists('regions', 'id')],
            'status' => ['required', Rule::enum(CompletionStatus::class)],
            'history' => ['nullable', 'string', 'max:20000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Give the country a name.',
            'region_id.required' => 'Choose a region.',
            'status.required' => 'Choose a status.',
        ];
    }

    /**
     * Country names must be unique ignoring case, so "Laos" and "laos" can't both exist.
     */
    private function nameIsUnique(string $attribute, mixed $value, Closure $fail): void
    {
        /** @var Country|null $current */
        $current = $this->route('country');
        $normalized = LogbookProgress::normalizeName((string) $value);

        $isTaken = Country::query()
            ->when($current, fn ($query) => $query->whereKeyNot($current->getKey()))
            ->pluck('name')
            ->contains(fn (string $name): bool => LogbookProgress::normalizeName($name) === $normalized);

        if ($isTaken) {
            $fail('There is already a country with this name.');
        }
    }
}
