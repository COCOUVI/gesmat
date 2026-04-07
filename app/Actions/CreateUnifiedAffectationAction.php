<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Affectation;
use App\Models\Bon;
use App\Models\CollaborateurExterne;
use App\Models\Equipement;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Unified action for creating affectations (assignments) for both employees and external collaborators.
 *
 * Handles:
 * - Direct affectation to employee
 * - Direct affectation to external collaborator
 * - Automatic bon creation with interlocuteur tracking
 */
final readonly class CreateUnifiedAffectationAction
{
    /**
     * @param  array{
     *     interlocuteur_type: 'user'|'collaborateur_externe',
     *     interlocuteur_id: int|string,
     *     motif: string,
     *     equipements: array<int, int|string>,
     *     quantites: array<int, int|string>,
     *     dates_retour?: array<int, string|null>
     * }  $validated
     * @return array{
     *     bon: Bon,
     *     interlocuteur: User|CollaborateurExterne,
     *     pdf_path: string,
     *     motif: string,
     *     affectations_details: array<int, array{nom: string, quantite: int, date_retour: string|null}>
     * }
     */
    public function handle(User $actor, array $validated): array
    {
        /** @var array{
         *     bon: Bon,
         *     interlocuteur: User|CollaborateurExterne,
         *     pdf_path: string,
         *     motif: string,
         *     affectations_details: array<int, array{nom: string, quantite: int, date_retour: string|null}>
         * } $result
         */
        $result = DB::transaction(function () use ($actor, $validated): array {
            $interlocuteurType = $validated['interlocuteur_type'];
            $interlocuteurId = (int) $validated['interlocuteur_id'];

            // Récupère l'interlocuteur (employé ou collaborateur externe)
            if ($interlocuteurType === 'user') {
                $interlocuteur = User::findOrFail($interlocuteurId);
                throw_unless(in_array($interlocuteur->role, ['employe', 'employé', 'employée'], true), Exception::class, "L'utilisateur sélectionné n'est pas un employé.");
            } else {
                $interlocuteur = CollaborateurExterne::findOrFail($interlocuteurId);
            }

            $lignesAffectation = $this->normalizeLines(
                $validated['equipements'],
                $validated['quantites'],
                $validated['dates_retour'] ?? []
            );

            $equipementIds = array_values(array_unique(array_column($lignesAffectation, 'equipement_id')));
            $equipements = Equipement::whereIn('id', $equipementIds)->get()->keyBy('id');
            $affectationsDetails = [];
            $quantitesReservees = [];

            foreach ($lignesAffectation as $ligneAffectation) {
                $equipementId = $ligneAffectation['equipement_id'];
                $quantite = $ligneAffectation['quantite'];
                $rawDate = $ligneAffectation['date_retour'];
                $equipement = $equipements->get($equipementId);

                if (! $equipement) {
                    throw new Exception(sprintf('Équipement ID %d introuvable.', $equipementId));
                }

                $this->ensureAvailability(
                    $equipement,
                    $quantite,
                    $quantitesReservees[$equipementId] ?? 0
                );

                // Crée l'affectation
                $affectationData = [
                    'equipement_id' => $equipementId,
                    'date_retour' => $rawDate ?: null,
                    'created_by' => $actor->nom.' '.$actor->prenom,
                    'quantite_affectee' => $quantite,
                    'statut' => 'active',
                ];

                if ($interlocuteurType === 'user') {
                    $affectationData['user_id'] = $interlocuteurId;
                } else {
                    $affectationData['collaborateur_externe_id'] = $interlocuteurId;
                }

                Affectation::create($affectationData);

                $quantitesReservees[$equipementId] = ($quantitesReservees[$equipementId] ?? 0) + $quantite;

                $affectationsDetails[] = [
                    'nom' => $equipement->nom,
                    'quantite' => $quantite,
                    'date_retour' => $rawDate ?: null,
                ];
            }

            $pdfName = 'bon_sortie_'.($interlocuteurType === 'user' ? 'employee_' : 'collaborateur_').$interlocuteurId.'_'.now()->timestamp.'.pdf';
            $pdfPath = 'bon_sortie/'.$pdfName;

            // Prépare les données du bon
            $bonData = [
                'motif' => $validated['motif'],
                'statut' => 'sortie',
                'fichier_pdf' => $pdfPath,
                'interlocuteur_type' => $interlocuteurType,
                'interlocuteur_id' => $interlocuteurId,
            ];

            // Ajoute les anciens champs pour la rétro-compatibilité
            if ($interlocuteurType === 'user') {
                $bonData['user_id'] = $interlocuteurId;
            } else {
                $bonData['collaborateur_externe_id'] = $interlocuteurId;
            }

            $bon = Bon::create($bonData);

            return [
                'bon' => $bon,
                'interlocuteur' => $interlocuteur,
                'pdf_path' => $pdfPath,
                'motif' => $validated['motif'],
                'affectations_details' => $affectationsDetails,
            ];
        });

        return $result;
    }

    /**
     * @param  array<int, int|string>  $equipements
     * @param  array<int, int|string>  $quantites
     * @param  array<int, string|null>  $datesRetour
     * @return array<int, array{equipement_id: int, quantite: int, date_retour: string|null}>
     */
    private function normalizeLines(array $equipements, array $quantites, array $datesRetour = []): array
    {
        $groupedLines = [];
        $orderedKeys = [];

        foreach ($equipements as $index => $equipementId) {
            $equipementId = (int) $equipementId;
            $quantite = (int) ($quantites[$index] ?? 0);
            $dateRetour = $datesRetour[$index] ?? null;
            $dateRetour = $dateRetour !== null && $dateRetour !== '' ? $dateRetour : null;
            if ($equipementId <= 0) {
                continue;
            }
            if ($quantite <= 0) {
                continue;
            }

            $groupKey = $equipementId.'|'.($dateRetour ?? 'sans-date');

            if (! array_key_exists($groupKey, $groupedLines)) {
                $groupedLines[$groupKey] = [
                    'equipement_id' => $equipementId,
                    'quantite' => 0,
                    'date_retour' => $dateRetour,
                ];
                $orderedKeys[] = $groupKey;
            }

            $groupedLines[$groupKey]['quantite'] += $quantite;
        }

        return array_map(fn ($groupKey) => $groupedLines[$groupKey], $orderedKeys);
    }

    private function ensureAvailability(Equipement $equipement, int $quantite, int $alreadyReserved): void
    {
        $disponible = $equipement->getQuantiteDisponible() - $alreadyReserved;

        if ($quantite > $disponible) {
            throw new Exception(sprintf(
                "Quantité insuffisante pour l'équipement « %s » (disponible : %d, demandée : %d).",
                $equipement->nom,
                $disponible,
                $quantite
            ));
        }
    }
}
