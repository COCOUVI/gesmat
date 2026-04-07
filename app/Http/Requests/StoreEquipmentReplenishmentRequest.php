<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreEquipmentReplenishmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'equipement_id' => ['required', 'exists:equipements,id'],
            'quantite' => ['required', 'integer', 'min:1'],
            'is_anonymous' => ['sometimes', 'in:0,1'],
            'deposant_id' => ['nullable', 'string'],
            'deposant_anonymous_nom' => ['nullable', 'string', 'max:100'],
            'deposant_anonymous_prenom' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'equipement_id.required' => 'L\'équipement est requis',
            'equipement_id.exists' => 'L\'équipement sélectionné n\'existe pas',
            'quantite.required' => 'La quantité est requise',
            'quantite.integer' => 'La quantité doit être un nombre entier',
            'quantite.min' => 'La quantité doit être au moins 1',
        ];
    }

    /**
     * Get the deposant name for display on the bon
     */
    public function getDeposantName(): ?string
    {
        // If anonymous mode, combine nom and prenom
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
     * Get validated data formatted for CreateUnifiedStockEntryAction
     */
    public function getActionData(): array
    {
        $data = $this->validated();

        $interlocuteurType = 'libre';
        $interlocuteurId = null;
        $interlocuteurNomLibre = null;

        // Handle deposant data
        $isAnonymous = (int) ($data['is_anonymous'] ?? 0);
        if ($isAnonymous) {
            // Anonymous mode: combine nom and prenom
            $nom = $data['deposant_anonymous_nom'] ?? '';
            $prenom = $data['deposant_anonymous_prenom'] ?? '';
            $interlocuteurNomLibre = mb_trim("{$nom} {$prenom}");
        } else {
            // Selected mode
            if ($data['deposant_id'] ?? null) {
                if (str_starts_with($data['deposant_id'], 'user_')) {
                    $interlocuteurType = 'user';
                    $interlocuteurId = (int) str_replace('user_', '', $data['deposant_id']);
                } elseif (str_starts_with($data['deposant_id'], 'collab_')) {
                    $interlocuteurType = 'collaborateur_externe';
                    $interlocuteurId = (int) str_replace('collab_', '', $data['deposant_id']);
                }
            }
        }

        return [
            'equipement_id' => (int) $data['equipement_id'],
            'quantite' => (int) $data['quantite'],
            'type' => 'reapprovisionnement',
            'interlocuteur_type' => $interlocuteurType,
            'interlocuteur_id' => $interlocuteurId,
            'interlocuteur_nom_libre' => $interlocuteurNomLibre,
        ];
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
