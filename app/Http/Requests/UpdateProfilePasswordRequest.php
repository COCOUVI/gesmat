<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateProfilePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed', 'different:current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.required' => 'Le mot de passe actuel est requis.',
            'current_password.current_password' => 'Le mot de passe actuel est incorrect.',
            'password.required' => 'Le nouveau mot de passe est requis.',
            'password.min' => 'Le nouveau mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du nouveau mot de passe ne correspond pas.',
            'password.different' => 'Le nouveau mot de passe doit être différent du mot de passe actuel.',
        ];
    }
}
