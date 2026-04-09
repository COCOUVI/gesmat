<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Affectation;
use App\Models\Categorie;
use App\Models\Demande;
use App\Models\Equipement;
use App\Models\Panne;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

final readonly class DashboardMetricsService
{
    public function __construct(
        private DashboardCacheService $dashboardCacheService,
    ) {}

    /**
     * @return array{
     *     nbr_equipement: int,
     *     nbr_user: int,
     *     nbr_affect: int,
     *     nbr_panne: int,
     *     statsParMois: array<int, int>,
     *     distribution: array<int, array{label: string, count: int}>,
     *     growth: float
     * }
     */
    public function getAdminMetrics(): array
    {
        /** @var array{
         *     nbr_equipement: int,
         *     nbr_user: int,
         *     nbr_affect: int,
         *     nbr_panne: int,
         *     statsParMois: array<int, int>,
         *     distribution: array<int, array{label: string, count: int}>,
         *     growth: float
         * } $metrics
         */
        $metrics = Cache::remember(
            $this->dashboardCacheService->adminMetricsKey(),
            now()->addSeconds(30),
            function (): array {
                $nbrEquipement = (int) Equipement::query()->sum('quantite');
                $nbrUser = User::query()->count();
                $nbrAffect = $this->activeAffectationQuantity();
                $nbrPanne = $this->unresolvedPanneQuantity();

                $now = now();
                $userThisMonth = User::query()
                    ->whereMonth('created_at', $now->month)
                    ->whereYear('created_at', $now->year)
                    ->count();
                $previousMonth = $now->copy()->subMonth();
                $userPreviousMonth = User::query()
                    ->whereMonth('created_at', $previousMonth->month)
                    ->whereYear('created_at', $previousMonth->year)
                    ->count();

                $growth = 0.0;

                if ($userPreviousMonth > 0) {
                    $growth = (($userThisMonth - $userPreviousMonth) / $userPreviousMonth) * 100;
                } elseif ($userThisMonth > 0) {
                    $growth = 100.0;
                }

                return [
                    'nbr_equipement' => $nbrEquipement,
                    'nbr_user' => $nbrUser,
                    'nbr_affect' => $nbrAffect,
                    'nbr_panne' => $nbrPanne,
                    'statsParMois' => $this->monthlyAffectationStats(),
                    'distribution' => $this->categoryDistribution(),
                    'growth' => $growth,
                ];
            }
        );

        return $metrics;
    }

    /**
     * @return array{nbr_accept: int, nbr_en_attente: int, nbr_non_resolue: int, nbr_assign: int}
     */
    public function getEmployeeMetrics(int $userId): array
    {
        /** @var array{nbr_accept: int, nbr_en_attente: int, nbr_non_resolue: int, nbr_assign: int} $metrics */
        $metrics = Cache::remember(
            $this->dashboardCacheService->employeeMetricsKey($userId),
            now()->addSeconds(30),
            function () use ($userId): array {
                $demandeStats = Demande::query()
                    ->where('user_id', $userId)
                    ->selectRaw("
                        COALESCE(SUM(CASE WHEN statut = 'acceptee' THEN 1 ELSE 0 END), 0) as nbr_accept,
                        COALESCE(SUM(CASE WHEN statut = 'en_attente' THEN 1 ELSE 0 END), 0) as nbr_en_attente
                    ")
                    ->first();

                return [
                    'nbr_accept' => (int) ($demandeStats?->nbr_accept ?? 0),
                    'nbr_en_attente' => (int) ($demandeStats?->nbr_en_attente ?? 0),
                    'nbr_non_resolue' => $this->unresolvedPanneQuantity($userId),
                    'nbr_assign' => $this->activeAffectationQuantity($userId),
                ];
            }
        );

        return $metrics;
    }

    private function activeAffectationQuantity(?int $userId = null): int
    {
        $query = Affectation::query();

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return (int) $query
            ->selectRaw(
                'COALESCE(SUM(CASE WHEN quantite_affectee > COALESCE(quantite_retournee, 0) THEN quantite_affectee - COALESCE(quantite_retournee, 0) ELSE 0 END), 0) as aggregate'
            )
            ->value('aggregate');
    }

    private function unresolvedPanneQuantity(?int $userId = null): int
    {
        $query = Panne::query();

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return (int) $query
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN statut = 'resolu' THEN 0 ELSE quantite - COALESCE(quantite_resolue, 0) END), 0) as aggregate"
            )
            ->value('aggregate');
    }

    /**
     * @return array<int, int>
     */
    private function monthlyAffectationStats(): array
    {
        $driver = Affectation::query()->getConnection()->getDriverName();
        $monthExpression = $driver === 'sqlite'
            ? "CAST(strftime('%m', created_at) AS INTEGER)"
            : 'MONTH(created_at)';

        $totalsByMonth = Affectation::query()
            ->whereYear('created_at', now()->year)
            ->selectRaw($monthExpression . ' as month_number, COALESCE(SUM(quantite_affectee), 0) as total')
            ->groupBy('month_number')
            ->pluck('total', 'month_number');

        $stats = [];

        for ($month = 1; $month <= 12; $month++) {
            $stats[$month] = (int) ($totalsByMonth[$month] ?? 0);
        }

        return $stats;
    }

    /**
     * @return array<int, array{label: string, count: int}>
     */
    private function categoryDistribution(): array
    {
        return Categorie::query()
            ->leftJoin('equipements', 'equipements.categorie_id', '=', 'categories.id')
            ->selectRaw('categories.nom as label, COALESCE(SUM(equipements.quantite), 0) as count')
            ->groupBy('categories.id', 'categories.nom')
            ->get()
            ->map(fn ($row): array => [
                'label' => (string) $row->label,
                'count' => (int) $row->count,
            ])
            ->all();
    }
}
