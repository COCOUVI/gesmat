<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreHelpRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return ['message' => ['required', 'string', 'min:10', 'max:2000']];
    }

    public function messages(): array
    {
        return [
            'message.required' => 'Le message est requis',
            'message.min' => 'Le message doit contenir au moins 10 caractères',
            'message.max' => 'Le message ne doit pas dépasser 2000 caractères',
        ];
    }
}
