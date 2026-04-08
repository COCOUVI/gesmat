<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Requests\UpdateProfilePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

final class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();
        $view = in_array($user->role, ['employe', 'employé', 'employée'], true)
            ? 'profile.employee_edit'
            : 'profile.admin_edit';

        return view($view, [
            'user' => $request->user(),
            'roleOptions' => $this->roleOptions(),
            'posteOptions' => $this->posteOptions(),
            'serviceOptions' => $this->serviceOptions(),
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validated();
        $data = [
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'email' => $validated['email'],
        ];

        if ($user->role === 'admin') {
            $data['role'] = $validated['role'];
            $data['poste'] = $validated['poste'];
            $data['service'] = $validated['service'];
        }

        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return to_route('profile.edit')->with('success', 'Votre profil a été mis à jour avec succès.');
    }

    public function updatePassword(UpdateProfilePasswordRequest $request): RedirectResponse
    {
        $request->user()->forceFill([
            'password' => Hash::make($request->validated()['password']),
        ])->save();

        return to_route('profile.edit')->with('success_password', 'Votre mot de passe a été mis à jour avec succès.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }

    /**
     * @return array<string, string>
     */
    private function roleOptions(): array
    {
        return [
            'admin' => 'Administrateur',
            'gestionnaire' => 'Gestionnaire',
            'employé' => 'Employé',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function posteOptions(): array
    {
        return [
            'stagiaire' => 'Stagiaire',
            'technicien' => 'Technicien',
            'electricien' => 'Électricien',
            'rigger' => 'Rigger',
            'support_technique' => 'Support technique',
            'secretariat' => 'Secrétariat',
            'comptabilite' => 'Comptabilité',
            'team_leader' => 'Team Leader',
            'directeur_technique' => 'Directeur Technique',
            'directeur_general' => 'Directeur Général',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function serviceOptions(): array
    {
        return [
            'secretariat' => 'Secrétariat',
            'comptabilite' => 'Comptabilité',
            'deploiement_ftth' => 'Déploiement FTTH',
            'deploiement_fttr' => 'Déploiement FTTR',
            'deploiement_reseaux' => 'Déploiement Réseaux',
            'deploiement_securise_video' => 'Déploiement Sécurisé et Vidéo Surveillance',
            'service_informatique' => 'Service Informatique',
            'direction' => 'Direction',
        ];
    }
}
