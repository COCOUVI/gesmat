<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreRapportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contenu' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'contenu.required' => 'Le contenu du rapport est requis.',
            'contenu.string' => 'Le contenu doit être une chaîne de caractères.',
        ];
    }
}
