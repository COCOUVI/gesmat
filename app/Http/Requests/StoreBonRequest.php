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
            'collaborateur_id' => ['required', 'exists:collaborateur_externes,id'],
            'motif' => ['required', 'string', 'max:1000'],
            'type' => ['required', 'in:entrée,sortie'],
            'equipements' => ['required', 'array', 'min:1'],
            'equipements.*' => ['required', 'exists:equipements,id'],
            'quantites' => ['required', 'array', 'min:1'],
            'quantites.*' => ['required', 'integer', 'min:1'],
            'dates_retour' => ['nullable', 'array'],
            'dates_retour.*' => ['nullable', 'date'],
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
            'type.in' => 'Le type doit être entrée ou sortie.',
            'equipements.required' => 'Au moins un équipement est requis.',
            'equipements.array' => 'La liste des équipements est invalide.',
            'equipements.*.required' => 'Chaque ligne doit contenir un équipement.',
            'equipements.*.exists' => 'Un des équipements sélectionnés est invalide.',
            'quantites.required' => 'Au moins une quantité est requise.',
            'quantites.array' => 'La liste des quantités est invalide.',
            'quantites.*.required' => 'Chaque ligne doit contenir une quantité.',
            'quantites.*.integer' => 'Chaque quantité doit être un nombre entier.',
            'quantites.*.min' => 'Chaque quantité doit être supérieure ou égale à 1.',
            'dates_retour.array' => 'La liste des dates de retour est invalide.',
            'dates_retour.*.date' => 'Chaque date de retour doit être une date valide.',
        ];
    }
}
