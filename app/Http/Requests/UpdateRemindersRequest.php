<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRemindersRequest extends FormRequest
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
            'notifications_enabled' => ['nullable', 'boolean'],
            'drink_water_reminder' => ['nullable', 'boolean'],
            'period_reminder' => ['nullable', 'boolean'],
            'cycle_reminder' => ['nullable', 'boolean'],
            'water_interval_minutes' => ['required', 'in:30,45,60,90,120'],
        ];
    }
}
