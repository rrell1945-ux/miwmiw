<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PeriodRequest extends FormRequest
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
            'start_date' => ['required', 'date', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'flow' => ['nullable', 'in:light,normal,heavy'],
            'mood' => ['nullable', 'string', 'max:30'],
            'symptoms' => ['nullable', 'array'],
            'symptoms.*' => ['string', 'distinct'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
