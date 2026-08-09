<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PeriodExtendRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'until' => ['nullable', 'date', 'before_or_equal:today'],
            'flow' => ['nullable', 'in:light,normal,heavy'],
            'mood' => ['nullable', 'string', 'max:30'],
            'symptoms' => ['nullable', 'array'],
            'symptoms.*' => ['string', 'distinct'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
