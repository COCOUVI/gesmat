<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEquipementRequest extends FormRequest
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
            'nom' => 'required|string|max:255',
            'etat' => 'required|in:disponible,usagé,en panne,réparé',
            'marque' => 'string|max:255',
            'categorie_id' => 'required|exists:categories,id',
            'description' => 'string',
            'date_acquisition' => 'required|date',
            'quantite' => 'nullable|integer|min:0',
            'image_path' => 'nullable|image|max:2048',
        ];
    }
    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom de l\'equipement est obligatoire.',
            'description.required' => 'La description est obligatoire.',
            'etat.required' => 'Veuillez selectionner un etat pour l\'equipement.',
            'etat.in' => 'L\'etat selectionne est invalide.',
            'categorie_id.required' => 'Veuillez choisir une categorie.',
            'categorie_id.exists' => 'La categorie selectionnee est invalide.',
            'quantite.integer' => 'La quantite doit etre un nombre entier.',
            'quantite.min' => 'La quantite ne peut pas etre negative.',
            'image_path.image' => 'Le fichier doit etre une image valide.',
            'image_path.max' => 'L\'image ne doit pas depasser 2 Mo.',
            'date_acquisition.required' => "La date d\'acquisition est requise"
        ];
    }
}
