<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\WorkflowActionMail;
use App\Models\Affectation;
use App\Models\Bon;
use App\Models\Demande;
use App\Models\Equipement;
use App\Models\Panne;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

final class WorkflowNotificationService
{
    public function notifyDemandeSubmitted(Demande $demande): void
    {
        $demande->loadMissing(['user', 'equipements']);

        $employee = $demande->user;
        if (! $employee instanceof User) {
            return;
        }

        $details = [
            ['label' => 'Employé', 'value' => $this->fullName($employee)],
            ['label' => 'Lieu', 'value' => (string) $demande->lieu],
            ['label' => 'Motif', 'value' => (string) $demande->motif],
            ['label' => 'Date', 'value' => $demande->created_at->format('d/m/Y H:i')],
        ];

        $highlights = $demande->equipements->map(function ($equipement): string {
            $quantite = (int) ($equipement->pivot->nbr_equipement ?? 0);

            return sprintf('%s : %d', $equipement->nom, $quantite);
        })->values()->all();

        $this->safeSend(
            $employee,
            new WorkflowActionMail(
                'Votre demande d’équipement a bien été enregistrée',
                'Demande d’équipement enregistrée',
                $this->fullName($employee),
                'Votre demande a été enregistrée avec succès. Les administrateurs et gestionnaires ont été notifiés.',
                $details,
                $highlights, [
                    ['label' => 'Voir ma demande', 'url' => route('listes.demandes')],
                ], null,
                null,
                'Nous reviendrons vers vous dès qu’une décision ou une affectation aura été effectuée.'
            )
        );

        foreach ($this->adminAndManagers() as $recipient) {
            $this->safeSend(
                $recipient,
                new WorkflowActionMail(
                    'Nouvelle demande d’équipement à traiter',
                    'Nouvelle demande à traiter',
                    $this->fullName($recipient),
                    sprintf(
                        '%s a initié une nouvelle demande d’équipement. Une action de votre part est attendue.',
                        $this->fullName($employee)
                    ),
                    $details,
                    $highlights, [
                        ['label' => 'Consulter les demandes', 'url' => route('liste.demandes')],
                    ], null,
                    null,
                    'Connectez-vous à la plateforme pour consulter et traiter cette demande.'
                )
            );
        }
    }

    public function notifyPanneReported(Panne $panne): void
    {
        $panne->loadMissing(['user', 'equipement', 'affectation']);

        $employee = $panne->user;
        if (! $employee instanceof User) {
            return;
        }

        $details = [
            ['label' => 'Employé', 'value' => $this->fullName($employee)],
            ['label' => 'Équipement', 'value' => (string) $panne->equipement?->nom],
            ['label' => 'Quantité', 'value' => (string) $panne->quantite],
            ['label' => 'Affectation', 'value' => $panne->affectation_id ? '#'.$panne->affectation_id : 'Stock interne'],
            ['label' => 'Date', 'value' => $panne->created_at->format('d/m/Y H:i')],
        ];

        $this->safeSend(
            $employee,
            new WorkflowActionMail(
                'Votre signalement de panne a bien été enregistré',
                'Signalement de panne enregistré',
                $this->fullName($employee),
                'Votre signalement a été transmis avec succès aux administrateurs et gestionnaires.',
                $details,
                [(string) $panne->description], [
                    ['label' => 'Voir mon historique', 'url' => route('historique.pannes')],
                ], null,
                null,
                'Vous serez informé dès qu’une résolution ou un remplacement sera effectué.'
            )
        );

        foreach ($this->adminAndManagers() as $recipient) {
            $this->safeSend(
                $recipient,
                new WorkflowActionMail(
                    'Nouveau signalement de panne à traiter',
                    'Nouveau signalement de panne',
                    $this->fullName($recipient),
                    sprintf(
                        '%s a signalé une panne sur du matériel affecté. Une prise en charge est attendue.',
                        $this->fullName($employee)
                    ),
                    $details,
                    [(string) $panne->description], [
                        ['label' => 'Gérer les pannes', 'url' => route('equipements.pannes')],
                    ], null,
                    null,
                    'Connectez-vous à la plateforme pour décider d’une résolution ou d’un remplacement.'
                )
            );
        }
    }

    /**
     * @param  array<int, array{nom: string, quantite: int, date_retour: ?string}>  $affectationsDetails
     */
    public function notifyDirectAffectation(User $employee, string $motif, array $affectationsDetails, ?Bon $bon = null): void
    {
        $details = [
            ['label' => 'Employé', 'value' => $this->fullName($employee)],
            ['label' => 'Motif', 'value' => $motif],
            ['label' => 'Date', 'value' => now()->format('d/m/Y H:i')],
        ];

        $highlights = array_map(
            fn (array $line): string => sprintf(
                '%s : %d%s',
                $line['nom'],
                $line['quantite'],
                $line['date_retour'] ? ' | retour prévu le '.$line['date_retour'] : ''
            ),
            $affectationsDetails
        );

        $this->safeSend(
            $employee,
            new WorkflowActionMail(
                'Une affectation d’équipement a été réalisée à votre nom',
                'Affectation d’équipement réalisée',
                $this->fullName($employee),
                'Une affectation d’équipement a été enregistrée avec succès à votre nom.',
                $details,
                $highlights, [
                    ['label' => 'Voir mes affectations', 'url' => route('equipements.assignes')],
                ], $bon?->fichier_pdf,
                $this->attachmentNameForBon($bon),
                'Le bon de sortie correspondant est joint à cet e-mail.'
            )
        );
    }

    /**
     * @param  array<int, array{nom: string, quantite: int, date_retour: ?string}>  $affectationsDetails
     */
    public function notifyDemandeServed(Demande $demande, array $affectationsDetails, ?Bon $bon = null): void
    {
        $demande->loadMissing('user');
        $employee = $demande->user;

        if (! $employee instanceof User) {
            return;
        }

        $demande->loadMissing(['equipements', 'affectations']);
        $statut = $demande->estEntierementServie() ? 'totalement servie' : 'partiellement servie';

        $details = [
            ['label' => 'Employé', 'value' => $this->fullName($employee)],
            ['label' => 'Motif', 'value' => (string) $demande->motif],
            ['label' => 'Statut', 'value' => $statut],
            ['label' => 'Quantité servie', 'value' => sprintf('%d / %d', $demande->getQuantiteTotaleServie(), $demande->getQuantiteTotaleDemandee())],
        ];

        $highlights = array_map(
            fn (array $line): string => sprintf(
                '%s : %d%s',
                $line['nom'],
                $line['quantite'],
                $line['date_retour'] ? ' | retour prévu le '.$line['date_retour'] : ''
            ),
            $affectationsDetails
        );

        $this->safeSend(
            $employee,
            new WorkflowActionMail(
                'Votre demande d’équipement a été traitée',
                'Traitement de votre demande',
                $this->fullName($employee),
                sprintf('Votre demande a été %s. Les équipements servis sont détaillés ci-dessous.', $statut),
                $details,
                $highlights, [
                    ['label' => 'Voir mes demandes', 'url' => route('listes.demandes')],
                ], $bon?->fichier_pdf,
                $this->attachmentNameForBon($bon),
                'Le bon de sortie correspondant est joint à cet e-mail.'
            )
        );
    }

    public function notifyEquipmentReturned(Affectation $affectation, int $healthyReturned, int $brokenReturned, ?Bon $bon = null): void
    {
        $affectation->loadMissing(['user', 'equipement']);

        // Skip notifications for external collaborators (no email in system)
        if ($affectation->estPourCollaborateur()) {
            return;
        }

        $employee = $affectation->user;

        if (! $employee instanceof User) {
            return;
        }

        $details = [
            ['label' => 'Équipement', 'value' => (string) $affectation->equipement?->nom],
            ['label' => 'Quantité retournée', 'value' => (string) ($healthyReturned + $brokenReturned)],
            ['label' => 'Retour sain', 'value' => (string) $healthyReturned],
            ['label' => 'Retour en panne', 'value' => (string) $brokenReturned],
            ['label' => 'Date', 'value' => now()->format('d/m/Y H:i')],
        ];

        $this->safeSend(
            $employee,
            new WorkflowActionMail(
                'Votre retour d’équipement a été enregistré',
                'Retour d’équipement enregistré',
                $this->fullName($employee),
                'Le retour d’équipement a été enregistré avec succès dans le système.',
                $details,
                [], [
                    ['label' => 'Voir mes affectations', 'url' => route('equipements.assignes')],
                ], $bon?->fichier_pdf,
                $this->attachmentNameForBon($bon),
                'Le bon d’entrée correspondant est joint à cet e-mail.'
            )
        );
    }

    public function notifyPanneResolved(Panne $panne, int $resolvedQuantity): void
    {
        $panne->loadMissing(['equipement', 'affectation.user', 'user']);

        $employee = $panne->affectation?->user;
        if (! $employee instanceof User) {
            return;
        }

        $details = [
            ['label' => 'Équipement', 'value' => (string) $panne->equipement?->nom],
            ['label' => 'Quantité résolue', 'value' => (string) $resolvedQuantity],
            ['label' => 'Signalement', 'value' => '#'.$panne->id],
            ['label' => 'Date', 'value' => now()->format('d/m/Y H:i')],
        ];

        $this->safeSend(
            $employee,
            new WorkflowActionMail(
                'Votre signalement de panne a été mis à jour',
                'Panne résolue',
                $this->fullName($employee),
                'Une résolution a été enregistrée sur le signalement de panne que vous aviez initié.',
                $details,
                [(string) $panne->description], [
                    ['label' => 'Voir mon historique', 'url' => route('historique.pannes')],
                ], null,
                null,
                'Vous pouvez consulter l’historique de vos pannes depuis votre espace employé.'
            )
        );
    }

    public function notifyPanneReplacement(Panne $panne, int $replacementQuantity, ?Bon $bon = null): void
    {
        $panne->loadMissing(['equipement', 'affectation.user']);

        $employee = $panne->affectation?->user;
        if (! $employee instanceof User) {
            return;
        }

        $details = [
            ['label' => 'Équipement', 'value' => (string) $panne->equipement?->nom],
            ['label' => 'Quantité remplacée', 'value' => (string) $replacementQuantity],
            ['label' => 'Signalement', 'value' => '#'.$panne->id],
            ['label' => 'Date', 'value' => now()->format('d/m/Y H:i')],
        ];

        $this->safeSend(
            $employee,
            new WorkflowActionMail(
                'Un remplacement de matériel a été effectué',
                'Remplacement de matériel effectué',
                $this->fullName($employee),
                'Le matériel signalé en panne a été remplacé avec succès.',
                $details,
                [(string) $panne->description],
                [
                    ['label' => 'Voir mon historique', 'url' => route('historique.pannes')],
                ],
                $bon?->fichier_pdf,
                $this->attachmentNameForBon($bon),
                'Le bon de sortie correspondant est joint à cet e-mail.'
            )
        );
    }

    public function notifyUpcomingReturnReminder(Affectation $affectation): void
    {
        $affectation->loadMissing(['user', 'equipement']);
        $employee = $affectation->user;

        if (! $employee instanceof User || $affectation->date_retour === null) {
            return;
        }

        $daysLeft = max(0, now()->startOfDay()->diffInDays($affectation->date_retour->copy()->startOfDay(), false));

        $details = [
            ['label' => 'Équipement', 'value' => (string) $affectation->equipement?->nom],
            ['label' => 'Quantité active', 'value' => (string) $affectation->getQuantiteActive()],
            ['label' => 'Date prévue de retour', 'value' => $affectation->date_retour->format('d/m/Y')],
            ['label' => 'Jours restants', 'value' => (string) $daysLeft],
        ];

        $this->safeSend(
            $employee,
            new WorkflowActionMail(
                'Rappel : une date de retour approche',
                'Rappel de retour d’équipement',
                $this->fullName($employee),
                'La date de retour prévue pour une affectation approche. Merci de prendre les dispositions nécessaires.',
                $details,
                [], [
                    ['label' => 'Voir mes affectations', 'url' => route('equipements.assignes')],
                ], null,
                null,
                'Si un retour partiel ou un incident doit être déclaré, merci de contacter l’administration du parc.'
            )
        );
    }

    public function notifyCriticalStockAlertIfNeeded(Equipement $equipement, ?int $previousAvailable = null, ?int $previousThreshold = null): void
    {
        $equipement->loadMissing('categorie');

        $currentAvailable = $equipement->getQuantiteDisponible();
        $currentThreshold = max(0, (int) $equipement->seuil_critique);

        if ($currentAvailable > $currentThreshold) {
            return;
        }

        if ($previousAvailable !== null && $previousThreshold !== null && $previousAvailable <= $previousThreshold) {
            return;
        }

        $details = [
            ['label' => 'Équipement', 'value' => (string) $equipement->nom],
            ['label' => 'Catégorie', 'value' => (string) $equipement->categorie?->nom],
            ['label' => 'Stock total', 'value' => (string) $equipement->quantite],
            ['label' => 'Stock disponible', 'value' => (string) $currentAvailable],
            ['label' => 'Seuil critique', 'value' => (string) $currentThreshold],
            ['label' => 'Date', 'value' => now()->format('d/m/Y H:i')],
        ];

        $highlights = [
            sprintf('Quantité affectée active : %d', $equipement->getQuantiteAffectee()),
            sprintf('Quantité en panne interne : %d', $equipement->getQuantiteEnPanneInterne()),
            sprintf('État actuel : %s', $equipement->getEtat()),
        ];

        foreach ($this->adminAndManagers() as $recipient) {
            $this->safeSend(
                $recipient,
                new WorkflowActionMail(
                    'Alerte stock critique : '.$equipement->nom,
                    'Seuil critique atteint',
                    $this->fullName($recipient),
                    sprintf(
                        'Le stock disponible de « %s » a atteint ou franchi son seuil critique. Une vérification ou un réapprovisionnement est recommandé.',
                        $equipement->nom
                    ),
                    $details,
                    $highlights,
                    [
                        ['label' => 'Gérer les affectations', 'url' => route('page.listeAffectations')],
                    ],
                    null,
                    null,
                    'Consultez la plateforme pour suivre les mouvements de stock liés à cet équipement.'
                )
            );
        }
    }

    private function adminAndManagers(): Collection
    {
        return User::query()
            ->whereIn('role', ['admin', 'gestionnaire'])
            ->get()
            ->filter(fn (User $user): bool => filled($user->email))
            ->unique('email')
            ->values();
    }

    private function safeSend(User $recipient, WorkflowActionMail $mail): void
    {
        if (! filled($recipient->email)) {
            return;
        }

        try {
            $mail->afterCommit();
            Mail::to($recipient->email)->queue($mail);
        } catch (Throwable $throwable) {
            Log::error('Erreur lors de l’envoi d’un email workflow.', [
                'recipient_id' => $recipient->id,
                'recipient_email' => $recipient->email,
                'message' => $throwable->getMessage(),
            ]);
        }
    }

    private function fullName(User $user): string
    {
        return mb_trim($user->nom.' '.$user->prenom);
    }

    private function attachmentNameForBon(?Bon $bon): ?string
    {
        if ($bon === null || $bon->fichier_pdf === null) {
            return null;
        }

        return basename($bon->fichier_pdf);
    }
}
