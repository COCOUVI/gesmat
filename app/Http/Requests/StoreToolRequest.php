<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreToolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string'],
            'marque' => ['required', 'min:2'],
            'categorie_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['required', 'string'],
            'date_acquisition' => ['required', 'date'],
            'image_path' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'quantite' => ['required', 'integer', 'min:1'],
            'seuil_critique' => ['required', 'integer', 'min:0'],
            'is_anonymous' => ['sometimes', 'in:0,1'],
            'deposant_id' => ['nullable', 'string'],
            'deposant_anonymous_nom' => ['nullable', 'string', 'max:100'],
            'deposant_anonymous_prenom' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => "Le nom de l'équipement est requis.",
            'nom.string' => "Le nom de l'équipement doit être une chaîne de caractères.",
            'marque.required' => "La marque de l'équipement est requise.",
            'marque.min' => 'La marque doit contenir au moins :min caractères.',
            'categorie_id.required' => 'La catégorie est requise.',
            'categorie_id.exists' => 'La catégorie sélectionnée est invalide.',
            'description.required' => 'La description est obligatoire.',
            'description.string' => 'La description doit être une chaîne de caractères.',
            'date_acquisition.required' => "La date d'acquisition est requise.",
            'date_acquisition.date' => "La date d'acquisition doit être une date valide.",
            'image_path.required' => "L'image de l'équipement est requise.",
            'image_path.image' => 'Le fichier doit être une image.',
            'image_path.mimes' => 'Le fichier doit être de type jpeg, png, jpg ou gif.',
            'image_path.max' => "L'image ne doit pas dépasser 2 Mo.",
            'quantite.required' => 'La quantité est requise.',
            'quantite.integer' => 'La quantité doit être un nombre entier.',
            'quantite.min' => 'La quantité minimale est 1.',
            'seuil_critique.required' => 'Le seuil critique est requis.',
            'seuil_critique.integer' => 'Le seuil critique doit être un nombre entier.',
            'seuil_critique.min' => 'Le seuil critique ne peut pas être négatif.',
        ];
    }

    /**
     * Get the deposant name for display on the bon
     */
    public function getDeposantName(): ?string
    {
        // If anonymous mode, combine nom and prénom
        $anonymousNom = $this->input('deposant_anonymous_nom');
        $anonymousPrenom = $this->input('deposant_anonymous_prenom');

        if ($anonymousNom || $anonymousPrenom) {
            $nom = $anonymousNom ?? '';
            $prenom = $anonymousPrenom ?? '';

            return mb_trim("{$nom} {$prenom}") ?: null;
        }

        // If deposant_id is set, resolve it
        $deposantId = $this->input('deposant_id');
        if (! $deposantId) {
            return null;
        }

        if (str_starts_with($deposantId, 'user_')) {
            $userId = (int) str_replace('user_', '', $deposantId);
            $user = \App\Models\User::find($userId);

            return $user ? "{$user->nom} {$user->prenom}" : null;
        }

        if (str_starts_with($deposantId, 'collab_')) {
            $collabId = (int) str_replace('collab_', '', $deposantId);
            $collab = \App\Models\CollaborateurExterne::find($collabId);

            return $collab ? $collab->nom : null;
        }

        return null;
    }

    /**
     * Get data prepared for the CreateEquipementAction
     */
    public function getActionData(): array
    {
        $data = $this->validated();
        $result = [
            'nom' => $data['nom'],
            'marque' => $data['marque'],
            'categorie_id' => $data['categorie_id'],
            'description' => $data['description'],
            'date_acquisition' => $data['date_acquisition'],
            'quantite' => $data['quantite'],
            'seuil_critique' => $data['seuil_critique'],
        ];

        // Handle deposant data
        $isAnonymous = (int) ($data['is_anonymous'] ?? 0);
        if ($isAnonymous) {
            // Anonymous mode: combine nom and prenom
            $nom = $data['deposant_anonymous_nom'] ?? '';
            $prenom = $data['deposant_anonymous_prenom'] ?? '';
            $result['deposant_nom_libre'] = mb_trim("{$nom} {$prenom}");
        } else {
            // Selected mode
            if ($data['deposant_id'] ?? null) {
                $result['deposant_id'] = $data['deposant_id'];
            }
        }

        return $result;
    }

    protected function after()
    {
        return function ($validator) {
            $isAnonymous = (int) ($this->input('is_anonymous') ?? 0);
            $deposantId = $this->input('deposant_id');
            $anonymousNom = $this->input('deposant_anonymous_nom');
            $anonymousPrenom = $this->input('deposant_anonymous_prenom');

            // If anonymous is checked, at least one of nom or prenom should be filled
            if ($isAnonymous && ! $anonymousNom && ! $anonymousPrenom) {
                $validator->errors()->add('deposant_anonymous_nom', 'Veuillez remplir au moins le nom ou le prénom de l\'anonyme.');
            }

            // If anonymous is not checked but a deposant is selected via select, it should not have anon values
            if (! $isAnonymous && ($anonymousNom || $anonymousPrenom)) {
                // This shouldn't happen with JS, but validate anyway
                $validator->errors()->add('deposant_id', 'Sélectionnez un mode: soit anonyme, soit une personne de la liste.');
            }
        };
    }
}
