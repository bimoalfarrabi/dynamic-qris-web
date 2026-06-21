<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
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
            'amount' => 'required|integer|min:1|max:100000000',
            'external_id' => 'sometimes|string|max:255',
            'expiry_seconds' => 'sometimes|integer|min:60|max:86400',
        ];
    }
}
