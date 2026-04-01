<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreDemandeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'lieu' => 'required',
            'motif' => 'required',
            'quantites' => 'required',
            'equipements' => 'required',
        ];
    }

    public function messages(): array
    {
        return [
            'lieu.required' => 'Le lieu est requis',
            'motif.required' => 'Le motif est requis',
            'quantites.required' => 'Une quanitée est requise pour votre demande',
            'equipements.required' => 'Un equipements est requis pour la demande',
        ];
    }
}
