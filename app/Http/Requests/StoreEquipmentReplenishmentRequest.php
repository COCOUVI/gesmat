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
            'deposant_id' => ['nullable', 'string'],
            'deposant_nom_libre' => ['nullable', 'string', 'max:500'],
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
     * Get validated data formatted for CreateUnifiedStockEntryAction
     */
    public function getActionData(): array
    {
        $data = $this->validated();

        $interlocuteurType = 'libre';
        $interlocuteurId = null;
        $interlocuteurNomLibre = null;

        if ($data['deposant_id']) {
            if (str_starts_with($data['deposant_id'], 'user_')) {
                $interlocuteurType = 'user';
                $interlocuteurId = (int) str_replace('user_', '', $data['deposant_id']);
            } elseif (str_starts_with($data['deposant_id'], 'collab_')) {
                $interlocuteurType = 'collaborateur_externe';
                $interlocuteurId = (int) str_replace('collab_', '', $data['deposant_id']);
            }
        }

        if ($data['deposant_nom_libre']) {
            $interlocuteurNomLibre = $data['deposant_nom_libre'];
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
}
