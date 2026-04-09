<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Bon;
use App\Models\Equipement;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

final readonly class CreateEquipementAction
{
    /**
     * @param  array{
     *     nom: string,
     *     marque: string,
     *     description: string,
     *     date_acquisition: mixed,
     *     quantite: int|string,
     *     seuil_critique: int|string,
     *     categorie_id: int|string,
     *     deposant_id?: string|null,
     *     deposant_nom_libre?: string|null
     * }  $validated
     * @return array{equipement: Equipement, bon: Bon, pdf_path: string}
     */
    public function handle(User $actor, array $validated, ?UploadedFile $image = null): array
    {
        /** @var array{equipement: Equipement, bon: Bon, pdf_path: string} $result */
        $result = DB::transaction(function () use ($actor, $validated, $image): array {
            $imagePath = null;

            if ($image instanceof UploadedFile) {
                $nomNettoye = preg_replace('/[^a-zA-Z0-9-_]/', '', mb_strtolower(str_replace(' ', '-', $validated['nom'])));
                $imageName = time().'_'.$nomNettoye.'.'.$image->getClientOriginalExtension();
                $image->move(public_path('pictures/equipements'), $imageName);
                $imagePath = 'pictures/equipements/'.$imageName;
            }

            $equipement = Equipement::create([
                'nom' => $validated['nom'],
                'marque' => $validated['marque'],
                'description' => $validated['description'],
                'date_acquisition' => $validated['date_acquisition'],
                'quantite' => (int) $validated['quantite'],
                'seuil_critique' => (int) $validated['seuil_critique'],
                'image_path' => $imagePath,
                'categorie_id' => (int) $validated['categorie_id'],
            ]);

            $pdfPath = 'bon_entree/bon_entree_'.$equipement->id.'.pdf';

            // Prepare interlocuteur data
            $interlocuteurType = 'libre';
            $interlocuteurId = null;
            $interlocuteurNomLibre = null;

            if (! empty($validated['deposant_id'])) {
                if (str_starts_with($validated['deposant_id'], 'user_')) {
                    $interlocuteurType = 'user';
                    $interlocuteurId = (int) str_replace('user_', '', $validated['deposant_id']);
                } elseif (str_starts_with($validated['deposant_id'], 'collab_')) {
                    $interlocuteurType = 'collaborateur_externe';
                    $interlocuteurId = (int) str_replace('collab_', '', $validated['deposant_id']);
                }
            }

            if (! empty($validated['deposant_nom_libre'])) {
                $interlocuteurNomLibre = $validated['deposant_nom_libre'];
            }

            $bon = Bon::create([
                'motif' => 'Ajout de nouvel équipement : '.$equipement->nom,
                'user_id' => $actor->id,
                'statut' => 'entrée',
                'fichier_pdf' => $pdfPath,
                'interlocuteur_type' => $interlocuteurType,
                'interlocuteur_id' => $interlocuteurId,
                'interlocuteur_nom_libre' => $interlocuteurNomLibre,
            ]);

            return [
                'equipement' => $equipement,
                'bon' => $bon,
                'pdf_path' => $pdfPath,
            ];
        });

        return $result;
    }
}
