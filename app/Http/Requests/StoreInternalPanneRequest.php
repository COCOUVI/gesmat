<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class StoreInternalPanneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'equipement_id' => ['required', 'integer', 'exists:equipements,id'],
            'quantite' => ['required', 'integer', 'min:1'],
            'description' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }
}
