<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Affectation;
use App\Models\Bon;
use App\Models\Equipement;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

final readonly class CreateDirectAffectationAction
{
    /**
     * @param  array{
     *     employe_id: int|string,
     *     motif: string,
     *     equipements: array<int, int|string>,
     *     quantites: array<int, int|string>,
     *     dates_retour?: array<int, string|null>
     * }  $validated
     * @return array{
     *     employe: User,
     *     bon: Bon,
     *     pdf_path: string,
     *     motif: string,
     *     affectations_details: array<int, array{nom: string, quantite: int, date_retour: string|null}>
     * }
     */
    public function handle(User $actor, array $validated): array
    {
        /** @var array{
         *     employe: User,
         *     bon: Bon,
         *     pdf_path: string,
         *     motif: string,
         *     affectations_details: array<int, array{nom: string, quantite: int, date_retour: string|null}>
         * } $result
         */
        $result = DB::transaction(function () use ($actor, $validated): array {
            $employe = User::findOrFail((int) $validated['employe_id']);

            throw_unless(in_array($employe->role, ['employe', 'employé', 'employée'], true), Exception::class, "L'utilisateur sélectionné n'est pas un employé.");

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

                Affectation::create([
                    'equipement_id' => $equipementId,
                    'user_id' => $employe->id,
                    'demande_id' => null,
                    'date_retour' => $rawDate ?: null,
                    'created_by' => $actor->nom.' '.$actor->prenom,
                    'quantite_affectee' => $quantite,
                    'statut' => 'active',
                ]);

                $quantitesReservees[$equipementId] = ($quantitesReservees[$equipementId] ?? 0) + $quantite;

                $affectationsDetails[] = [
                    'nom' => $equipement->nom,
                    'quantite' => $quantite,
                    'date_retour' => $rawDate ?: null,
                ];
            }

            $pdfName = 'bon_sortie_'.$employe->id.'_'.now()->timestamp.'.pdf';
            $pdfPath = 'bon_sortie/'.$pdfName;

            $bon = Bon::create([
                'user_id' => $employe->id,
                'motif' => $validated['motif'],
                'statut' => 'sortie',
                'fichier_pdf' => $pdfPath,
                'interlocuteur_type' => 'user',
                'interlocuteur_id' => $employe->id,
            ]);

            return [
                'employe' => $employe,
                'bon' => $bon,
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

        return array_map(
            fn (string $key): array => $groupedLines[$key],
            $orderedKeys
        );
    }

    private function ensureAvailability(Equipement $equipement, int $quantite, int $quantiteReservee = 0): void
    {
        throw_if($quantite <= 0, Exception::class, 'La quantité à affecter doit être supérieure à zéro.');

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
