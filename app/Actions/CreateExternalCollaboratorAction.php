<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\CollaborateurExterne;
use Illuminate\Http\UploadedFile;

final readonly class CreateExternalCollaboratorAction
{
    /**
     * @param  array{nom: string, prenom: string}  $validated
     */
    public function handle(array $validated, ?UploadedFile $identityCard = null): CollaborateurExterne
    {
        $cartePath = null;

        if ($identityCard instanceof UploadedFile) {
            $filename = 'carte_'.time().'_'.preg_replace('/\s+/', '_', $validated['nom']).'.'.$identityCard->getClientOriginalExtension();
            $identityCard->move(public_path('collaborateurs/cartes'), $filename);
            $cartePath = 'collaborateurs/cartes/'.$filename;
        }

        return CollaborateurExterne::create([
            'nom' => $validated['nom'],
            'prenom' => $validated['prenom'],
            'carte_chemin' => $cartePath,
        ]);
    }
}
