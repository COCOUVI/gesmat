<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreBonRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'collaborateur_id' => 'required|exists:collaborateur_externes,id',
            'motif' => 'required|string|max:1000',
            'type' => 'required|in:entrée,sortie,autre',
        ];
    }

    public function messages(): array
    {
        return [
            'collaborateur_id.required' => 'Le collaborateur est requis.',
            'collaborateur_id.exists' => 'Le collaborateur sélectionné est invalide.',
            'motif.required' => 'Le motif est requis.',
            'motif.string' => 'Le motif doit être une chaîne de caractères.',
            'motif.max' => 'Le motif ne doit pas dépasser 1000 caractères.',
            'type.required' => 'Le type est requis.',
            'type.in' => 'Le type doit être entrée, sortie ou autre.',
        ];
    }
}
