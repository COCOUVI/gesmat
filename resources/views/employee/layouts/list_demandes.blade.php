@extends('employee.homedash')

@push('styles')
    <style>
        .table thead th {
            background-color: rgb(93, 161, 93);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.85rem;
        }

        .table tbody tr:hover {
            background-color: #f9fcff;
        }

        .badge-status {
            font-size: 0.85rem;
            padding: 0.4em 0.7em;
            border-radius: 0.375rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .badge-status i {
            font-size: 1rem;
        }

        .card-title {
            font-size: 1.2rem;
        }
    </style>
@endpush

@section('content')
<div class="main-panel">
    <div class="content-wrapper px-4 py-3">

        <div class="card dashboard-card shadow-sm p-3">
            @if ($demandes->count())
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle smart-data-table" data-table-title="vos demandes">
                        <thead >
                            <tr>
                                <th class="text-white">#</th>
                                <th class="text-white">Motif</th>
                                <th class="text-white">Lieu</th>
                                <th class="text-white">Date</th>
                                <th class="text-white">Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($demandes as $index => $demande)
                                @php $statutAffichage = $demande->getStatutAffichage(); @endphp
                                <tr>
                                    <td>{{ $loop->iteration + ($demandes->currentPage() - 1) * $demandes->perPage() }}</td>
                                    <td>{{ Str::limit($demande->motif, 60) }}</td>
                                    <td>{{ $demande->lieu }}</td>
                                    <td>{{ $demande->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge-status 
                                            @if($statutAffichage === 'acceptee') bg-success
                                            @elseif($statutAffichage === 'rejetee') bg-danger
                                            @elseif($statutAffichage === 'partiellement_servie') bg-info text-dark
                                            @else bg-warning text-dark @endif">
                                            <i class="mdi 
                                                @if($statutAffichage === 'acceptee') mdi-check-circle-outline
                                                @elseif($statutAffichage === 'rejetee') mdi-close-circle-outline
                                                @elseif($statutAffichage === 'partiellement_servie') mdi-progress-check
                                                @else mdi-timer-sand @endif">
                                            </i>
                                            {{ str_replace('_', ' ', ucfirst($statutAffichage)) }}
                                        </span>
                                        <div class="mt-2 small text-muted">
                                            Servi : {{ $demande->getQuantiteTotaleServie() }} / {{ $demande->getQuantiteTotaleDemandee() }}
                                        </div>
                                        <div class="mt-2 small">
                                            @foreach ($demande->equipements as $equipement)
                                                @php
                                                    $quantiteDemandee = (int) $equipement->pivot->nbr_equipement;
                                                    $quantiteServie = $demande->getQuantiteServiePourEquipement($equipement->id);
                                                    $quantiteRestante = $demande->getQuantiteRestantePourEquipement($equipement->id, $quantiteDemandee);
                                                @endphp
                                                <div>
                                                    {{ $equipement->nom }} :
                                                    demandé {{ $quantiteDemandee }},
                                                    servi {{ $quantiteServie }},
                                                    restant {{ $quantiteRestante }}
                                                </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 d-flex justify-content-center">
                    {{ $demandes->links('pagination::bootstrap-5') }}
                </div>
            @else
                <div class="no-data text-center">
                    <i class="mdi mdi-file-outline fs-1 text-muted"></i>
                    <p class="text-muted">Aucune demande effectuée pour le moment.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
