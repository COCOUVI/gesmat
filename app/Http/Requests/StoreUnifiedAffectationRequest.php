<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreUnifiedAffectationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'interlocuteur_type' => ['required', 'in:user,collaborateur_externe'],
            'employe_id' => ['required_if:interlocuteur_type,user', 'nullable', 'exists:users,id'],
            'collaborateur_externe_id' => ['required_if:interlocuteur_type,collaborateur_externe', 'nullable', 'exists:collaborateur_externes,id'],
            'motif' => ['required', 'string', 'max:500'],
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
            'interlocuteur_type.required' => 'Le type de destinataire est requis',
            'interlocuteur_type.in' => 'Le type de destinataire doit être un employé ou un collaborateur externe',
            'employe_id.required_if' => 'Un employé doit être sélectionné',
            'collaborateur_externe_id.required_if' => 'Un collaborateur externe doit être sélectionné',
            'equipements.required' => 'Au moins un équipement est requis',
            'quantites.required' => 'Les quantités sont requises',
        ];
    }

    /**
     * Get validated data in the format expected by CreateUnifiedAffectationAction
     */
    public function getActionData(): array
    {
        $data = $this->validated();

        $interlocuteurType = $data['interlocuteur_type'];
        $interlocuteurId = $interlocuteurType === 'user'
            ? $data['employe_id']
            : $data['collaborateur_externe_id'];

        return [
            'interlocuteur_type' => $interlocuteurType,
            'interlocuteur_id' => (int) $interlocuteurId,
            'motif' => $data['motif'],
            'equipements' => $data['equipements'],
            'quantites' => array_map('intval', $data['quantites']),
            'dates_retour' => $data['dates_retour'] ?? [],
        ];
    }

    /**
     * Prepare data for validation/processing
     */
    protected function prepareForValidation(): void
    {
        // Map employe_id/collaborateur_externe_id based on interlocuteur_type
        if ($this->input('interlocuteur_type') === 'collaborateur_externe') {
            $this->merge([
                'collaborateur_externe_id' => $this->input('collaborateur_externe_id'),
                'employe_id' => null,
            ]);
        } else {
            $this->merge([
                'employe_id' => $this->input('employe_id'),
                'collaborateur_externe_id' => null,
            ]);
        }
    }
}
