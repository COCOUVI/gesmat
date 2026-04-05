<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEquipementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'categorie_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['nullable', 'string'],
            'marque' => ['nullable', 'string'],
            'quantite' => ['required', 'integer', 'min:1'],
            'date_acquisition' => ['nullable', 'date'],
            'image_path' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est requis.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'categorie_id.required' => 'La catégorie est requise.',
            'categorie_id.exists' => 'La catégorie sélectionnée est invalide.',
            'quantite.required' => 'La quantité est requise.',
            'quantite.integer' => 'La quantité doit être un nombre entier.',
            'quantite.min' => 'La quantité minimale est 1.',
            'date_acquisition.date' => "La date d'acquisition doit être une date valide.",
            'image_path.image' => 'Le fichier doit être une image.',
            'image_path.max' => "L'image ne doit pas dépasser 2 Mo.",
        ];
    }
}
