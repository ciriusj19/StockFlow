<?php

namespace App\Http\Requests;

use App\Enums\MovementType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreStockMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::enum(MovementType::class)],
            'quantity' => [
                'required',
                'numeric',
                Rule::when(
                    $this->input('type') === MovementType::Adjustment->value,
                    ['gte:0'],
                    ['gt:0'],
                ),
            ],
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
