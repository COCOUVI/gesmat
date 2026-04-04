<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreDemandeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lieu' => 'required|string|max:255',
            'motif' => 'required|string|min:3|max:2000',
            'equipements' => 'required|array|min:1',
            'equipements.*' => 'required|integer|exists:equipements,id',
            'quantites' => 'required|array|min:1',
            'quantites.*' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'lieu.required' => 'Le lieu est requis.',
            'lieu.string' => 'Le lieu doit être une chaîne de caractères.',
            'lieu.max' => 'Le lieu ne doit pas dépasser 255 caractères.',
            'motif.required' => 'Le motif est requis.',
            'motif.string' => 'Le motif doit être une chaîne de caractères.',
            'motif.min' => 'Le motif doit contenir au moins 3 caractères.',
            'motif.max' => 'Le motif ne doit pas dépasser 2000 caractères.',
            'equipements.required' => 'Veuillez sélectionner au moins un équipement.',
            'equipements.array' => 'La liste des équipements est invalide.',
            'equipements.min' => 'Veuillez sélectionner au moins un équipement.',
            'equipements.*.required' => 'Chaque ligne doit contenir un équipement.',
            'equipements.*.integer' => 'Chaque équipement sélectionné est invalide.',
            'equipements.*.exists' => 'Un des équipements sélectionnés est invalide.',
            'quantites.required' => 'Veuillez indiquer la quantité demandée.',
            'quantites.array' => 'La liste des quantités est invalide.',
            'quantites.min' => 'Veuillez indiquer au moins une quantité.',
            'quantites.*.required' => 'Chaque ligne doit contenir une quantité.',
            'quantites.*.integer' => 'Chaque quantité doit être un nombre entier.',
            'quantites.*.min' => 'Chaque quantité doit être supérieure ou égale à 1.',
        ];
    }
}
