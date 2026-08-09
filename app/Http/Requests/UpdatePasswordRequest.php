<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (app()->environment('local')) {
            \Illuminate\Support\Facades\Log::info('PASSWORD-REQ', [
                'accept' => $this->headers->get('Accept'),
                'xrw' => $this->headers->get('X-Requested-With'),
                'expectsJson' => $this->expectsJson(),
            ]);
        }

        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
