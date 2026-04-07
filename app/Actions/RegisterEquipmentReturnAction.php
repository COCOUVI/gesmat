<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Affectation;
use App\Models\Bon;
use Exception;
use Illuminate\Support\Facades\DB;

final readonly class RegisterEquipmentReturnAction
{
    /**
     * @param  array{quantite_saine_retournee?: int|string|null, pannes_retournees?: array<int, int|string|null>}  $validated
     * @return array{
     *     affectation: Affectation,
     *     healthy_returned: int,
     *     broken_returned: int,
     *     total_returned: int,
     *     bon: Bon,
     *     pdf_path: string
     * }
     */
    public function handle(Affectation $affectation, array $validated): array
    {
        /** @var array{
         *     affectation: Affectation,
         *     healthy_returned: int,
         *     broken_returned: int,
         *     total_returned: int,
         *     bon: Bon,
         *     pdf_path: string
         * } $result
         */
        $result = DB::transaction(function () use ($affectation, $validated): array {
            $affectation->load(['equipement', 'user', 'collaborateurExterne', 'pannes' => function ($query): void {
                $query->where('statut', '!=', 'resolu');
            }]);

            $quantiteSaineRetournee = (int) ($validated['quantite_saine_retournee'] ?? 0);
            $pannesRetournees = $validated['pannes_retournees'] ?? [];
            $quantitePanneRetournee = 0;

            if ($quantiteSaineRetournee > $affectation->getQuantiteSaineActive()) {
                throw new Exception(sprintf(
                    'Vous ne pouvez retourner que %d unité(s) saine(s) pour cette affectation.',
                    $affectation->getQuantiteSaineActive()
                ));
            }

            foreach ($affectation->pannes as $panne) {
                $quantiteRetourPanne = (int) ($pannesRetournees[$panne->id] ?? 0);

                if ($quantiteRetourPanne > $panne->getQuantiteEncoreChezEmploye()) {
                    throw new Exception(sprintf(
                        'La quantité retournée pour la panne #%d dépasse le maximum autorisé (%d).',
                        $panne->id,
                        $panne->getQuantiteEncoreChezEmploye()
                    ));
                }

                if ($quantiteRetourPanne > 0) {
                    $panne->update([
                        'quantite_retournee_stock' => $panne->getQuantiteRetourneeAuStock() + $quantiteRetourPanne,
                    ]);
                }

                $quantitePanneRetournee += $quantiteRetourPanne;
            }

            $quantiteRetourneeTotale = $quantiteSaineRetournee + $quantitePanneRetournee;

            throw_if($quantiteRetourneeTotale <= 0, Exception::class, 'Veuillez saisir au moins une quantité à retourner.');

            if ($quantiteRetourneeTotale > $affectation->getQuantiteActive()) {
                throw new Exception(sprintf(
                    'Vous ne pouvez retourner que %d unité(s) au total pour cette affectation.',
                    $affectation->getQuantiteActive()
                ));
            }

            $nouvelleQuantiteRetournee = $affectation->getQuantiteRetournee() + $quantiteRetourneeTotale;
            $updateData = [
                'quantite_retournee' => $nouvelleQuantiteRetournee,
                'statut' => $nouvelleQuantiteRetournee >= $affectation->quantite_affectee ? 'retourné' : 'retour_partiel',
                'returned_at' => $affectation->returned_at ?? now(),
            ];

            $affectation->update($updateData);
            $equipement = $affectation->equipement;
            $pdfName = 'bon_entree_retour_'.$affectation->id.'_'.now()->timestamp.'.pdf';
            $pdfPath = 'bon_entree/'.$pdfName;

            $bonData = [
                'motif' => sprintf(
                    'Retour de matériel : %s (total: %d, sain: %d, en panne: %d)',
                    $equipement->nom,
                    $quantiteRetourneeTotale,
                    $quantiteSaineRetournee,
                    $quantitePanneRetournee
                ),
                'statut' => 'entrée',
                'fichier_pdf' => $pdfPath,
            ];

            if ($affectation->estPourCollaborateur()) {
                $bonData['collaborateur_externe_id'] = $affectation->collaborateur_externe_id;
                $bonData['interlocuteur_type'] = 'collaborateur_externe';
                $bonData['interlocuteur_id'] = $affectation->collaborateur_externe_id;
            } else {
                $bonData['user_id'] = $affectation->user_id;
                $bonData['interlocuteur_type'] = 'user';
                $bonData['interlocuteur_id'] = $affectation->user_id;
            }

            $bon = Bon::create($bonData);

            return [
                'affectation' => $affectation->fresh(['user', 'collaborateurExterne', 'equipement', 'pannes']),
                'healthy_returned' => $quantiteSaineRetournee,
                'broken_returned' => $quantitePanneRetournee,
                'total_returned' => $quantiteRetourneeTotale,
                'bon' => $bon,
                'pdf_path' => $pdfPath,
            ];
        });

        return $result;
    }
}
