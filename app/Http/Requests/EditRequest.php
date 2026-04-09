<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class EditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // $userId = $this->route('putuser') ? $this->route('putuser')->id : null;

        return [
            'prenom' => ['required', 'string'],
            'nom' => ['required', 'string'],
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($this->user),
            ],
            'password' => ['nullable', 'min:8'],
            'password_confirmation' => ['nullable'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'prenom.required' => 'Le prénom est obligatoire',
            'nom.required' => 'Le nom est obligatoire',
            // 'password.required' => 'Le mot de passe est obligatoire',
            'email.required' => "L'email est obligatoire",
            'email.email' => 'L\'email doit être une adresse valide',
            'email.unique' => 'Cet email est déjà utilisé',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation()
    {
        if ($this->password === null) {
            $this->request->remove('password');
            $this->request->remove('password_confirmation');
        }
    }
}
