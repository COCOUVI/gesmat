<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreDirectAffectationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'employe_id' => 'required|exists:users,id',
            'motif' => 'required|string|max:500',
            'equipements' => 'required|array|min:1',
            'equipements.*' => 'required|exists:equipements,id',
            'quantites' => 'required|array|min:1',
            'quantites.*' => 'required|integer|min:1',
            'dates_retour' => 'nullable|array',
            'dates_retour.*' => 'nullable|date',
        ];
    }

    public function messages(): array
    {
        return [
            'equipements.required' => 'le champ equipement est requis',
            'quantites.required' => 'le champ quantité est requis',
        ];
    }
}
