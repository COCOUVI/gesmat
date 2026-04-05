<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreCollaboratorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'chemin_carte' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est requis.',
            'nom.string' => 'Le nom doit être une chaîne de caractères.',
            'nom.max' => 'Le nom ne doit pas dépasser 255 caractères.',
            'prenom.required' => 'Le prénom est requis.',
            'prenom.string' => 'Le prénom doit être une chaîne de caractères.',
            'prenom.max' => 'Le prénom ne doit pas dépasser 255 caractères.',
            'chemin_carte.required' => "La carte d'identité est requise.",
            'chemin_carte.file' => 'Le fichier doit être une carte valide.',
            'chemin_carte.mimes' => 'Le fichier doit être de type jpg, jpeg, png ou pdf.',
            'chemin_carte.max' => 'La carte ne doit pas dépasser 2 Mo.',
        ];
    }
}
