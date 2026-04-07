<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Bon;
use App\Models\Categorie;
use App\Models\Equipement;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;

/**
 * Unified action for creating stock entries (new equipment or replenishment).
 *
 * Handles:
 * - Adding new equipment to inventory
 * - Replenishing existing equipment stock
 * - Automatic bon creation with 'libre' interlocuteur type
 */
final readonly class CreateUnifiedStockEntryAction
{
    /**
     * @param  array{
     *     type: 'nouvel_equipement'|'reapprovisionnement',
     *     source?: string,
     *     categorie_id?: int|string,
     *     nom?: string,
     *     marque?: string,
     *     description?: string,
     *     reference?: string,
     *     quantite: int|string,
     *     date_acquisition?: string,
     *     equipement_id?: int|string
     * }  $validated
     * @return array{
     *     type: 'nouvel_equipement'|'reapprovisionnement',
     *     equipement: Equipement,
     *     bon: Bon,
     *     pdf_path: string,
     *     quantite_added: int
     * }
     */
    public function handle(User $actor, array $validated): array
    {
        /** @var array{
         *     type: 'nouvel_equipement'|'reapprovisionnement',
         *     equipement: Equipement,
         *     bon: Bon,
         *     pdf_path: string,
         *     quantite_added: int
         * } $result
         */
        $result = DB::transaction(function () use ($actor, $validated): array {
            $type = $validated['type'];
            $quantite = (int) $validated['quantite'];

            throw_if($quantite <= 0, Exception::class, 'La quantité doit être supérieure à 0.');

            if ($type === 'nouvel_equipement') {
                return $this->handleNewEquipment($actor, $validated, $quantite);
            }
            if ($type === 'reapprovisionnement') {
                return $this->handleReplenishment($actor, $validated, $quantite);
            }

            throw new Exception('Type d\'entrée de stock invalide.');
        });

        return $result;
    }

    /**
     * @param  array{
     *     source?: string,
     *     categorie_id: int|string,
     *     nom: string,
     *     marque: string,
     *     description: string,
     *     reference?: string,
     *     quantite: int|string,
     *     date_acquisition?: string
     * }  $validated
     */
    private function handleNewEquipment(User $actor, array $validated, int $quantite): array
    {
        $categorieId = (int) $validated['categorie_id'];
        $categorie = Categorie::findOrFail($categorieId);

        // Crée le nouvel équipement
        $equipement = Equipement::create([
            'categorie_id' => $categorieId,
            'nom' => $validated['nom'],
            'marque' => $validated['marque'],
            'description' => $validated['description'],
            'reference' => $validated['reference'] ?? null,
            'quantite' => $quantite,
            'date_acquisition' => $validated['date_acquisition'] ?? now()->toDateString(),
            'image_path' => null,
        ]);

        // Génère le bon d'entrée
        $source = $validated['source'] ?? 'Fournisseur externe';
        $pdfName = 'bon_entree_nouvel_equipement_'.$equipement->id.'_'.now()->timestamp.'.pdf';
        $pdfPath = 'bon_entree/'.$pdfName;

        $bon = Bon::create([
            'motif' => sprintf('Ajout de nouvel équipement : %s (%s), quantité : %d', $equipement->nom, $equipement->marque, $quantite),
            'statut' => 'entrée',
            'fichier_pdf' => $pdfPath,
            'interlocuteur_type' => 'libre',
            'interlocuteur_nom_libre' => $source,
        ]);

        $bon->equipements()->attach($equipement->id, ['quantite' => $quantite]);

        return [
            'type' => 'nouvel_equipement',
            'equipement' => $equipement,
            'bon' => $bon,
            'pdf_path' => $pdfPath,
            'quantite_added' => $quantite,
        ];
    }

    /**
     * @param  array{
     *     source?: string,
     *     equipement_id: int|string,
     *     quantite: int|string
     * }  $validated
     */
    private function handleReplenishment(User $actor, array $validated, int $quantite): array
    {
        $equipementId = (int) $validated['equipement_id'];
        $equipement = Equipement::findOrFail($equipementId);

        // Met à jour la quantité
        $ancienneQuantite = $equipement->quantite;
        $equipement->update([
            'quantite' => $ancienneQuantite + $quantite,
        ]);

        // Génère le bon d'entrée
        $source = $validated['source'] ?? 'Réapprovisionnement interne';
        $pdfName = 'bon_entree_reappro_'.$equipement->id.'_'.now()->timestamp.'.pdf';
        $pdfPath = 'bon_entree/'.$pdfName;

        $bon = Bon::create([
            'motif' => sprintf('Réapprovisionnement : %s, quantité ajoutée : %d', $equipement->nom, $quantite),
            'statut' => 'entrée',
            'fichier_pdf' => $pdfPath,
            'interlocuteur_type' => 'libre',
            'interlocuteur_nom_libre' => $source,
        ]);

        $bon->equipements()->attach($equipement->id, ['quantite' => $quantite]);

        return [
            'type' => 'reapprovisionnement',
            'equipement' => $equipement->fresh(),
            'bon' => $bon,
            'pdf_path' => $pdfPath,
            'quantite_added' => $quantite,
        ];
    }
}
