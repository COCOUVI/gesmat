<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StorePanneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'equipement_id' => 'required|exists:equipements,id',
            'description' => 'required|string|min:10|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'equipement_id.required' => "L'équipement est requis",
            'equipement_id.exists' => "L'équipement sélectionné n'existe pas",
            'description.required' => 'La description est requise',
            'description.min' => 'La description doit contenir au moins 10 caractères',
        ];
    }
}
