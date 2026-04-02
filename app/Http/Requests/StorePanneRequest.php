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
            'affectation_id' => 'required|exists:affectations,id',
            'quantite' => 'required|integer|min:1',
            'description' => 'required|string|min:10|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'affectation_id.required' => "L'affectation est requise",
            'affectation_id.exists' => "L'affectation sélectionnée n'existe pas",
            'quantite.required' => 'La quantité est requise',
            'quantite.min' => 'La quantité minimale est de 1',
            'description.required' => 'La description est requise',
            'description.min' => 'La description doit contenir au moins 10 caractères',
        ];
    }
}
