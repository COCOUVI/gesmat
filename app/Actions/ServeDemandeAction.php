<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Affectation;
use App\Models\Bon;
use App\Models\Demande;
use App\Models\Equipement;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

final readonly class ServeDemandeAction
{
    /**
     * @param  array<int, int|string|null>  $quantitesAAffecter
     * @param  array<int, string|null>  $datesRetour
     * @return array{
     *     demande: Demande,
     *     bon: Bon,
     *     pdf_path: string,
     *     assigned_total: int,
     *     affectations_details: array<int, array{nom: string, reference: string, quantite: int, date_retour: string|null}>,
     *     is_fully_served: bool
     * }
     */
    public function handle(User $actor, Demande $demande, array $quantitesAAffecter = [], array $datesRetour = []): array
    {
        /** @var array{
         *     demande: Demande,
         *     bon: Bon,
         *     pdf_path: string,
         *     assigned_total: int,
         *     affectations_details: array<int, array{nom: string, reference: string, quantite: int, date_retour: string|null}>,
         *     is_fully_served: bool
         * } $result
         */
        $result = DB::transaction(function () use ($actor, $demande, $quantitesAAffecter, $datesRetour): array {
            $demande->loadMissing(['equipements', 'affectations', 'user']);
            $equipementsData = $demande->equipements;

            if ($equipementsData->isEmpty()) {
                throw new Exception('Cette demande ne contient aucun équipement à servir.');
            }

            $affectationsDetails = [];
            $quantitesReservees = [];
            $assignedTotal = 0;

            foreach ($equipementsData as $equipement) {
                $quantiteDemandee = (int) ($equipement->pivot->nbr_equipement ?? 1);
                $quantiteRestante = $demande->getQuantiteRestantePourEquipement($equipement->id, $quantiteDemandee);

                if ($quantiteRestante === 0) {
                    continue;
                }

                $quantite = (int) ($quantitesAAffecter[$equipement->id] ?? 0);
                $rawDate = $datesRetour[$equipement->id] ?? null;

                if ($quantite === 0) {
                    continue;
                }

                if ($quantite > $quantiteRestante) {
                    throw new Exception(sprintf(
                        'La quantité à affecter pour « %s » dépasse le restant à servir (%d).',
                        $equipement->nom,
                        $quantiteRestante
                    ));
                }

                $this->ensureAvailability(
                    $equipement,
                    $quantite,
                    $quantitesReservees[$equipement->id] ?? 0
                );

                Affectation::create([
                    'equipement_id' => $equipement->id,
                    'user_id' => $demande->user_id,
                    'demande_id' => $demande->id,
                    'date_retour' => $rawDate ?: null,
                    'created_by' => $actor->nom.' '.$actor->prenom,
                    'quantite_affectee' => $quantite,
                    'statut' => 'active',
                ]);

                $quantitesReservees[$equipement->id] = ($quantitesReservees[$equipement->id] ?? 0) + $quantite;
                $assignedTotal += $quantite;

                $affectationsDetails[] = [
                    'nom' => $equipement->nom,
                    'reference' => $equipement->reference ?? '',
                    'quantite' => $quantite,
                    'date_retour' => $rawDate ?: null,
                ];
            }

            if ($assignedTotal === 0) {
                throw new Exception('Aucune quantité n’a été affectée. Veuillez saisir au moins une quantité à servir.');
            }

            $pdfName = 'bon_sortie_demande_'.$demande->id.'_'.now()->timestamp.'.pdf';
            $pdfPath = 'bon_sortie/'.$pdfName;

            $bon = Bon::create([
                'user_id' => $demande->user_id,
                'motif' => $demande->motif ?? 'Affectation automatique de demande',
                'statut' => 'sortie',
                'fichier_pdf' => $pdfPath,
            ]);

            $demande->refresh()->load(['equipements', 'affectations', 'user']);
            $isFullyServed = $demande->estEntierementServie();

            if ($isFullyServed) {
                $demande->update(['statut' => 'acceptee']);
                $demande->refresh()->load(['equipements', 'affectations', 'user']);
            }

            return [
                'demande' => $demande,
                'bon' => $bon,
                'pdf_path' => $pdfPath,
                'assigned_total' => $assignedTotal,
                'affectations_details' => $affectationsDetails,
                'is_fully_served' => $isFullyServed,
            ];
        });

        return $result;
    }

    private function ensureAvailability(Equipement $equipement, int $quantite, int $quantiteReservee = 0): void
    {
        if ($quantite <= 0) {
            throw new Exception('La quantité à affecter doit être supérieure à zéro.');
        }

        $quantiteDisponible = max(0, $equipement->getQuantiteDisponible() - $quantiteReservee);

        if ($quantiteDisponible < $quantite) {
            throw new Exception(sprintf(
                "Quantité insuffisante pour l'équipement « %s » (disponible : %d, demandée : %d).",
                $equipement->nom,
                $quantiteDisponible,
                $quantite
            ));
        }
    }
}
