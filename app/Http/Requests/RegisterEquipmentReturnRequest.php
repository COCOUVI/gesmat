<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class RegisterEquipmentReturnRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'quantite_saine_retournee' => ['nullable', 'integer', 'min:0'],
            'pannes_retournees' => ['nullable', 'array'],
            'pannes_retournees.*' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
