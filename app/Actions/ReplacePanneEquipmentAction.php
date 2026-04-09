<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Affectation;
use App\Models\Bon;
use App\Models\Panne;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

final readonly class ReplacePanneEquipmentAction
{
    /**
     * @return array{
     *     panne: Panne,
     *     bon: Bon,
     *     pdf_path: string,
     *     replacement_quantity: int,
     *     replacement_affectation: Affectation
     * }
     */
    public function handle(User $actor, Panne $panne, int $quantiteRemplacement): array
    {
        /** @var array{
         *     panne: Panne,
         *     bon: Bon,
         *     pdf_path: string,
         *     replacement_quantity: int,
         *     replacement_affectation: Affectation
         * } $result
         */
        $result = DB::transaction(function () use ($actor, $panne, $quantiteRemplacement): array {
            $panne->load(['equipement', 'affectation.user']);

            throw_if($panne->estInterne() || ! $panne->affectation, Exception::class, 'Le remplacement ne peut se faire que sur une panne liée à une affectation active.');

            $quantiteRemplacable = $panne->getQuantiteRemplacable();

            throw_if($quantiteRemplacable <= 0, Exception::class, 'Aucune quantité n’est disponible pour un remplacement immédiat.');

            if ($quantiteRemplacement > $quantiteRemplacable) {
                throw new Exception(sprintf(
                    'Vous ne pouvez remplacer que %d unité(s) pour cette panne.',
                    $quantiteRemplacable
                ));
            }

            $affectationOrigine = $panne->affectation;
            $utilisateur = $affectationOrigine->user;

            $panne->quantite_retournee_stock = $panne->getQuantiteRetourneeAuStock() + $quantiteRemplacement;
            $panne->statut = $panne->getQuantiteNonResolue() === 0 ? 'resolu' : 'en_attente';
            $panne->save();

            $nouvelleQuantiteRetournee = $affectationOrigine->getQuantiteRetournee() + $quantiteRemplacement;
            $affectationOrigine->update([
                'quantite_retournee' => $nouvelleQuantiteRetournee,
                'statut' => $nouvelleQuantiteRetournee >= $affectationOrigine->quantite_affectee ? 'retourné' : 'retour_partiel',
            ]);

            $affectationRemplacement = Affectation::create([
                'equipement_id' => $panne->equipement_id,
                'user_id' => $utilisateur->id,
                'demande_id' => null,
                'date_retour' => $affectationOrigine->date_retour,
                'created_by' => $actor->nom.' '.$actor->prenom,
                'quantite_affectee' => $quantiteRemplacement,
                'quantite_retournee' => 0,
                'statut' => 'active',
            ]);

            $pdfName = 'bon_sortie_remplacement_'.$panne->id.'_'.now()->timestamp.'.pdf';
            $pdfPath = 'bon_sortie/'.$pdfName;

            $bon = Bon::create([
                'user_id' => $utilisateur->id,
                'motif' => 'Remplacement d’équipement en panne : '.$panne->equipement->nom,
                'statut' => 'sortie',
                'fichier_pdf' => $pdfPath,
            ]);

            return [
                'panne' => $panne->fresh(['equipement', 'affectation.user', 'user']),
                'bon' => $bon,
                'pdf_path' => $pdfPath,
                'replacement_quantity' => $quantiteRemplacement,
                'replacement_affectation' => $affectationRemplacement,
            ];
        });

        return $result;
    }
}
