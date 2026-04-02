@extends('employee.homedash')
@section('content')
    <div class="main-panel">
        <div class="content-wrapper px-4 py-3">
            <div class="card shadow-sm">
                <!-- Header simple et professionnel -->
                <div class="card-header bg-danger text-white">
                    <h4 class="mb-0 d-flex align-items-center">
                        <i class="mdi mdi-alert-circle me-2"></i>
                        Mes pannes signalées
                    </h4>
                </div>

                <div class="card-body">
                    @if ($pannes->count())
                        <div class="table-responsive">
                            <table class="table table-striped table-hover smart-data-table" data-table-title="vos pannes">
                                <thead class="table bg-danger">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col" class="text-white">
                                            <i class="mdi mdi-desktop-tower me-1"></i>
                                            Équipement
                                        </th>
                                        <th scope="col" class="text-white">
                                            <i class="mdi mdi-text-box-outline me-1"></i>
                                            Description
                                        </th>
                                        <th scope="col" class="text-white">
                                            <i class="mdi mdi-calendar me-1"></i>
                                            Date
                                        </th>
                                        <th scope="col" class="text-white">
                                            <i class="mdi mdi-alert-octagon me-1"></i>
                                            Statut
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pannes as $index => $panne)
                                        <tr
                                            class="
                                        bg
                                    ">
                                            <td class="fw-bold">
                                                {{ $loop->iteration }}
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="me-2">
                                                        <i class="mdi mdi-desktop-tower text-danger fs-5"></i>
                                                    </div>
                                                    <span class="fw-semibold">{{ $panne->equipement->nom }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-dark" title="{{ $panne->description }}">
                                                    {{ Str::limit($panne->description, 50) }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-dark">
                                                    <i class="mdi mdi-clock-outline me-1"></i>
                                                    <span class="fw-semibold">
                                                        {{ $panne->created_at->format('d/m/Y') }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if ($panne->statut === 'resolu')
                                                    <span class="badge bg-success fs-6 px-3 py-2">
                                                        <i class="mdi mdi-check-circle me-1"></i>
                                                        Résolu
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger fs-6 px-3 py-2">
                                                        <i class="mdi mdi-alert-circle me-1"></i>
                                                        En panne
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <!-- Message vide avec contexte de panne -->
                        <div class="text-center py-5">
                            <div class="mb-4">
                                <i class="mdi mdi-wrench-outline text-danger" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="text-muted mb-2">Aucune panne signalée</h5>
                            <p class="text-muted">Parfait ! Aucun incident technique à signaler pour le moment.</p>
                            <a href="{{ route('signaler.panne') }}"
                                class="btn btn-danger mt-3 text-white text-decoration-none">
                                <i class="mdi mdi-plus me-2"></i>
                                Signaler une nouvelle panne
                            </a>

                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        /* Styles Bootstrap personnalisés pour le contexte pannes */
        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(0, 0, 0, .02);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(220, 53, 69, .1) !important;
        }

        .table-warning {
            background-color: rgba(255, 193, 7, .1) !important;
        }

        .table-success {
            background-color: rgba(25, 135, 84, .1) !important;
        }

        .card-header.bg-danger {
            background-color: #dc3545 !important;
        }

        .badge {
            font-weight: 600;
        }

        .table th {
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
        }

        .table td {
            vertical-align: middle;
        }
    </style>
@endsection
