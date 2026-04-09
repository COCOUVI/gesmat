<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];

        if ($this->user()?->role === 'admin') {
            $rules['role'] = ['required', 'string', 'in:admin,gestionnaire,employé,employe,employée'];
            $rules['poste'] = ['required', 'string', 'max:255'];
            $rules['service'] = ['required', 'string', 'max:255'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom est requis.',
            'prenom.required' => 'Le prénom est requis.',
            'email.required' => "L'adresse e-mail est requise.",
            'email.email' => "L'adresse e-mail n'est pas valide.",
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'role.required' => 'Le rôle est requis.',
            'poste.required' => 'Le poste est requis.',
            'service.required' => 'Le service est requis.',
        ];
    }
}
