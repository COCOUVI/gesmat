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
        ]);
    }

    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $user->fill($request->validated());

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
}
