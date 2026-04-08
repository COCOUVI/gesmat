@extends('employee.homedash')
@push('styles')
    <style>
        body {
            background-color: #f8f9fa;
        }

        .employee-stat-card {
            background: #ffffff;
            border-radius: 1rem;
            border-left: 5px solid #0f8b4c;
            box-shadow: 0 0.5rem 1.25rem rgba(15, 23, 42, 0.08);
            padding: 1.1rem 1.15rem;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            height: 100%;
        }

        .employee-stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 0.9rem 1.8rem rgba(15, 23, 42, 0.12);
        }

        .employee-stat-card .stat-label {
            font-size: 0.78rem;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
            margin-bottom: 0.35rem;
        }

        .employee-stat-card .stat-value {
            font-size: 1.9rem;
            font-weight: 800;
            color: #1f2937;
            line-height: 1;
        }

        .employee-stat-card .stat-icon-wrap {
            width: 3rem;
            height: 3rem;
            border-radius: 999px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .employee-stat-card.stat-assign {
            border-left-color: #0f8b4c;
        }

        .employee-stat-card.stat-assign .stat-icon-wrap {
            background: rgba(15, 139, 76, 0.14);
            color: #0f8b4c;
        }

        .employee-stat-card.stat-waiting {
            border-left-color: #d97706;
        }

        .employee-stat-card.stat-waiting .stat-icon-wrap {
            background: rgba(217, 119, 6, 0.14);
            color: #d97706;
        }

        .employee-stat-card.stat-accepted {
            border-left-color: #166534;
        }

        .employee-stat-card.stat-accepted .stat-icon-wrap {
            background: rgba(22, 101, 52, 0.14);
            color: #166534;
        }

        .employee-stat-card.stat-broken {
            border-left-color: #dc2626;
        }

        .employee-stat-card.stat-broken .stat-icon-wrap {
            background: rgba(220, 38, 38, 0.14);
            color: #dc2626;
        }

        .dashboard-card {
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgb(0 0 0 / 0.075);
            transition: box-shadow 0.3s ease;
            background: white;
            /* suppression de height: 100% */
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 1.5rem;
        }

        .dashboard-card:hover {
            box-shadow: 0 0.5rem 1rem rgb(0 123 255 / 0.25);
        }

        .card-title i {
            margin-right: 0.5rem;
        }

        .stat-card {
            padding: 1.5rem;
            color: white;
            border-radius: 0.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon {
            font-size: 3rem;
            opacity: 0.75;
        }

        .stat-primary {
            background: #0d6efd;
        }

        .stat-success {
            background: #198754;
        }

        .stat-warning {
            background: #ffc107;
            color: #212529;
        }

        .stat-danger {
            background: #dc3545;
        }

        .table th,
        .table td {
            vertical-align: middle !important;
        }

        .table-responsive {
            max-height: 360px;
            /* limite la hauteur */
            overflow-y: auto;
        }

        .no-data {
            text-align: center;
            padding: 2rem;
            color: #999;
        }

        .no-data i {
            font-size: 3rem;
            margin-bottom: 0.75rem;
        }

        .actions-rapides .btn {
            font-size: 1rem;
            padding: 0.85rem 1rem;
            border-radius: 0.4rem;
        }

        /* Pour que la colonne de droite s'aligne bien verticalement */
        .col-lg-4.d-flex.flex-column.gap-4>.dashboard-card {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        /* S'assurer que la liste des pannes prenne tout l'espace disponible */
        .list-group {
            flex-grow: 1;
            overflow-y: auto;
        }
    </style>
@endpush


@section('content')
    <div class="main-panel">
        <div class="content-wrapper px-4 py-3">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h5 class="text-muted">Bienvenue sur votre espace J-Tools</h5>
                    <h2 class="fw-bold">Bonjour {{ $user->nom }} {{ $user->prenom }} !</h2>
                </div>
                <a href="{{ route('page.aide') }}" class="btn btn-outline-info">
                    <i class="mdi mdi-help-circle-outline"></i> Aide
                </a>
            </div>

            <!-- Statistiques principales -->
            <div class="row g-4 mb-4">
                <div class="col-sm-6 col-xl-3">
                    <div class="employee-stat-card stat-assign">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <p class="stat-label">Unités assignées</p>
                                <p class="stat-value mb-0">{{ $nbr_assign }}</p>
                            </div>
                            <div class="stat-icon-wrap">
                                <i class="fas fa-box-open fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="employee-stat-card stat-waiting">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <p class="stat-label">Demandes en attente</p>
                                <p class="stat-value mb-0">{{ $nbr_en_attente }}</p>
                            </div>
                            <div class="stat-icon-wrap">
                                <i class="fas fa-hourglass-half fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="employee-stat-card stat-accepted">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <p class="stat-label">Demandes acceptées</p>
                                <p class="stat-value mb-0">{{ $nbr_accept }}</p>
                            </div>
                            <div class="stat-icon-wrap">
                                <i class="fas fa-check-circle fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="employee-stat-card stat-broken">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div>
                                <p class="stat-label">Unités en panne</p>
                                <p class="stat-value mb-0">{{ $nbr_non_resolue }}</p>
                            </div>
                            <div class="stat-icon-wrap">
                                <i class="fas fa-triangle-exclamation fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section principale -->
            <div class="row g-4">
                <!-- Équipements récents -->
                <div class="col-lg-8">
                    <div class="card dashboard-card shadow-sm p-3">
                        <h4 class="card-title fw-bold text-primary mb-3">
                            <i class="mdi mdi-laptop"></i> Équipements récemment affectés
                        </h4>

                        @if ($affectations->count())
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nom</th>
                                            <th>Catégorie</th>
                                            <th>Date d'affectation</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($affectations as $affectation)
                                            <tr>
                                                <td>{{ $affectation->equipement->nom }}</td>
                                                <td>{{ $affectation->equipement->categorie->nom ?? 'Non défini' }}</td>
                                                <td>{{ \Carbon\Carbon::parse($affectation->created_at)->format('d/m/Y') }}
                                                </td>
                                             
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="no-data">
                                <i class="mdi mdi-laptop-off"></i>
                                <p>Aucun équipement affecté récemment.</p>
                            </div>
                        @endif
                    </div>
                    <!-- Demandes récentes -->
                    <div class="card dashboard-card shadow-sm p-3 mt-4">
                        <h4 class="card-title fw-bold text-primary mb-3">
                            <i class="mdi mdi-file-document"></i> Demandes Recentes
                        </h4>

                        @if ($demandes->count())
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>N°</th>
                                            <th>Objet</th>
                                            <th>Date</th>
                                            <th>Statut</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($demandes as $index => $demande)
                                            <tr>
                                                <td> {{ $index + 1 }}</td>
                                                <td>{{ $demande->motif }}</td>
                                                <td>{{ \Carbon\Carbon::parse($demande->created_at)->format('d/m/Y') }}</td>
                                                <td>
                                                    <span
                                                        class="badge
                                    @if ($demande->statut === 'acceptee') bg-success
                                    @elseif($demande->statut === 'rejetee') bg-danger
                                    @else bg-warning text-dark @endif
                                ">
                                                        {{ ucfirst($demande->statut) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="no-data">
                                <i class="mdi mdi-file-document-outline"></i>
                                <p>Aucune demande récente.</p>
                            </div>
                        @endif
                    </div>


                </div>

                <!-- Actions rapides & Dernières pannes -->
                <div class="col-lg-4 d-flex flex-column gap-4">

                    <!-- Actions rapides -->
                    <div class="card dashboard-card shadow-sm p-3 actions-rapides">
                        <h4 class="card-title fw-bold text-primary mb-3">
                            <i class="mdi mdi-flash"></i> Actions rapides
                        </h4>
                        <a href="{{ route('demande.equipement') }}" class="btn btn-primary w-100 mb-2">
                            <i class="mdi mdi-laptop"></i> Demander un équipement
                        </a>
                        <a href="{{ route('signaler.panne') }}" class="btn btn-danger w-100 mb-2">
                            <i class="mdi mdi-alert"></i> Signaler une panne
                        </a>
                        <a href="{{ route('listes.demandes') }}" class="btn btn-secondary w-100">
                            <i class="mdi mdi-file-document-outline"></i> Consulter les demandes
                        </a>
                    </div>

                    <!-- Dernières pannes -->
                    <div class="card dashboard-card shadow-sm p-3">
                        <h4 class="card-title fw-bold text-primary mb-3">
                            <i class="mdi mdi-bug"></i> Dernières pannes signalées
                        </h4>
                        @if ($pannes->count())
                            <ul class="list-group list-group-flush">
                                @foreach ($pannes->take(5) as $panne)
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $panne->equipement->nom }}</strong><br>
                                            <small>{{ Str::limit($panne->description, 50) }}</small>
                                        </div>
                                        <span class="badge {{ $panne->statut === 'resolu' ? 'bg-success' : 'bg-danger' }}">
                                            {{ ucfirst($panne->statut) }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        @else
                            <div class="no-data">
                                <i class="mdi mdi-bug-outline"></i>
                                <p>Aucune panne signalée.</p>
                            </div>
                        @endif
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
