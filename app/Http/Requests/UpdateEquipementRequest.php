<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateEquipementRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'marque' => ['string', 'max:255'],
            'categorie_id' => ['required', 'exists:categories,id'],
            'description' => ['string'],
            'date_acquisition' => ['required', 'date'],
            'quantite' => ['nullable', 'integer', 'min:0'],
            'seuil_critique' => ['required', 'integer', 'min:0'],
            'image_path' => ['nullable', 'image', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => "Le nom de l'equipement est obligatoire.",
            'description.required' => 'La description est obligatoire.',
            'categorie_id.required' => 'Veuillez choisir une categorie.',
            'categorie_id.exists' => 'La categorie selectionnee est invalide.',
            'quantite.integer' => 'La quantite doit etre un nombre entier.',
            'quantite.min' => 'La quantite ne peut pas etre negative.',
            'seuil_critique.required' => 'Le seuil critique est obligatoire.',
            'seuil_critique.integer' => 'Le seuil critique doit etre un nombre entier.',
            'seuil_critique.min' => 'Le seuil critique ne peut pas etre negatif.',
            'image_path.image' => 'Le fichier doit etre une image valide.',
            'image_path.max' => "L'image ne doit pas depasser 2 Mo.",
            'date_acquisition.required' => "La date d\'acquisition est requise",
        ];
    }
}
