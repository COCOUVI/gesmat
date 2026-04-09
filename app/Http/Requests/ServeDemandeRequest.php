<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class ServeDemandeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantites_a_affecter' => ['required', 'array'],
            'quantites_a_affecter.*' => ['nullable', 'integer', 'min:0'],
            'dates_retour' => ['nullable', 'array'],
            'dates_retour.*' => ['nullable', 'date'],
        ];
    }
}
