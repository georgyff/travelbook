<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

/**
 * A new order for a list of records, given as their ids from first to last.
 */
class ReorderRequest extends FormRequest
{
    /**
     * The logbook runs locally without accounts, so anyone using it may edit.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'distinct'],
        ];
    }

    /**
     * The submitted ids, checked to be exactly the ids of the list being reordered.
     *
     * @param  Collection<int, int>  $expectedIds
     * @return list<int>
     *
     * @throws ValidationException
     */
    public function orderedIdsMatching(Collection $expectedIds): array
    {
        $ids = array_map('intval', $this->validated('ids'));

        if (collect($ids)->sort()->values()->all() !== $expectedIds->map(fn (mixed $id): int => (int) $id)->sort()->values()->all()) {
            throw ValidationException::withMessages([
                'ids' => 'The list changed since the page loaded. Reload and try again.',
            ]);
        }

        return $ids;
    }
}
