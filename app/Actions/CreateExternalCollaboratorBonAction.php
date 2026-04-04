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
     *     quantites: array<int, int|string>
     * }  $validated
     * @return array{
     *     bon: Bon,
     *     collaborateur: CollaborateurExterne,
     *     pdf_path: string,
     *     equipements_info: array<int, array{nom: string, quantite: int}>
     * }
     */
    public function handle(User $actor, array $validated): array
    {
        /** @var array{
         *     bon: Bon,
         *     collaborateur: CollaborateurExterne,
         *     pdf_path: string,
         *     equipements_info: array<int, array{nom: string, quantite: int}>
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

            $lignes = $this->buildLines($validated['equipements'], $validated['quantites']);

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
     * @return Collection<int, array{equipement_id: int, quantite: int}>
     */
    private function buildLines(array $equipements, array $quantites): Collection
    {
        $lines = collect();

        foreach ($equipements as $index => $equipementId) {
            $quantite = (int) ($quantites[$index] ?? 0);
            $equipementId = (int) $equipementId;

            if ($equipementId <= 0 || $quantite <= 0) {
                continue;
            }

            $lines->push([
                'equipement_id' => $equipementId,
                'quantite' => $quantite,
            ]);
        }

        return $lines;
    }
}
