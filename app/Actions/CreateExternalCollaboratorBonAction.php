<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Affectation;
use App\Models\Bon;
use App\Models\CollaborateurExterne;
use App\Models\Equipement;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class CreateExternalCollaboratorBonAction
{
    /**
     * @param  array{
     *     collaborateur_id: int|string,
     *     motif: string,
     *     type: string,
     *     equipements: array<int, int|string>,
     *     quantites: array<int, int|string>,
     *     dates_retour?: array<int, string|null>
     * }  $validated
     * @return array{
     *     bon: Bon,
     *     collaborateur: CollaborateurExterne,
     *     pdf_path: string,
     *     equipements_info: array<int, array{nom: string, quantite: int, date_retour: string|null}>
     * }
     */
    public function handle(User $actor, array $validated): array
    {
        /** @var array{
         *     bon: Bon,
         *     collaborateur: CollaborateurExterne,
         *     pdf_path: string,
         *     equipements_info: array<int, array{nom: string, quantite: int, date_retour: string|null}>
         * } $result
         */
        $result = DB::transaction(function () use ($actor, $validated): array {
            $collaborateur = CollaborateurExterne::findOrFail((int) $validated['collaborateur_id']);
            $pdfPath = 'bon_collaborateurs/bon_collab_'.time().'.pdf';

            $bon = Bon::create([
                'collaborateur_externe_id' => $collaborateur->id,
                'motif' => $validated['motif'],
                'statut' => $validated['type'],
                'fichier_pdf' => $pdfPath,
            ]);

            $lignes = $this->buildLines(
                $validated['equipements'],
                $validated['quantites'],
                $validated['dates_retour'] ?? []
            );

            $bonEquipements = $lignes
                ->mapWithKeys(fn (array $line): array => [
                    $line['equipement_id'] => ['quantite' => $line['quantite']],
                ])
                ->all();

            if (! empty($bonEquipements)) {
                $bon->equipements()->attach($bonEquipements);
            }

            if ($validated['type'] === 'sortie') {
                foreach ($lignes as $line) {
                    Affectation::create([
                        'equipement_id' => $line['equipement_id'],
                        'collaborateur_externe_id' => $collaborateur->id,
                        'date_retour' => $line['date_retour'],
                        'quantite_affectee' => $line['quantite'],
                        'statut' => 'active',
                        'created_by' => $actor->nom.' '.$actor->prenom,
                    ]);
                }
            }

            $equipements = Equipement::whereIn('id', $lignes->pluck('equipement_id')->all())
                ->get()
                ->keyBy('id');

            $equipementsInfo = $lignes->map(function (array $line) use ($equipements): array {
                /** @var Equipement|null $equipement */
                $equipement = $equipements->get($line['equipement_id']);

                return [
                    'nom' => $equipement?->nom ?? 'Inconnu',
                    'quantite' => $line['quantite'],
                    'date_retour' => $line['date_retour'],
                ];
            })->values()->all();

            return [
                'bon' => $bon,
                'collaborateur' => $collaborateur,
                'pdf_path' => $pdfPath,
                'equipements_info' => $equipementsInfo,
            ];
        });

        return $result;
    }

    /**
     * @param  array<int, int|string>  $equipements
     * @param  array<int, int|string>  $quantites
     * @param  array<int, string|null>  $datesRetour
     * @return Collection<int, array{equipement_id: int, quantite: int, date_retour: string|null}>
     */
    private function buildLines(array $equipements, array $quantites, array $datesRetour = []): Collection
    {
        $groupedLines = [];
        $orderedKeys = [];

        foreach ($equipements as $index => $equipementId) {
            $quantite = (int) ($quantites[$index] ?? 0);
            $equipementId = (int) $equipementId;
            $dateRetour = $datesRetour[$index] ?? null;
            $dateRetour = $dateRetour !== null && $dateRetour !== '' ? $dateRetour : null;

            if ($equipementId <= 0 || $quantite <= 0) {
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

        return collect($orderedKeys)->map(
            fn (string $key): array => $groupedLines[$key]
        );
    }
}
