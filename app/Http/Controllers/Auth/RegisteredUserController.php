<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\IdentifiantsEnvoyes;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('admin.Adduserpage');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'prenom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required'],
            'service' => ['required'],
            'poste' => ['required'],
        ]);

        // Générer un mot de passe aléatoire
        $randomPassword = Str::random(10);

        // Créer l'utilisateur avec le mot de passe hashé
        $user = User::create([
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'poste' => $request->poste,
            'role' => $request->role,
            'service' => $request->service,
            'email' => $request->email,
            'password' => Hash::make($randomPassword),
        ]);

        // Envoyer un email avec les identifiants
        Mail::to($user->email)->send(new IdentifiantsEnvoyes($user, $randomPassword));

        return redirect()->back()->with("success", "Utilisateur ajouté avec succès. Un email contenant les identifiants a été envoyé.");
    }
}
