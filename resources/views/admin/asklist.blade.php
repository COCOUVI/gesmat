@extends('admin.layouts.adminlay')
@push('styles')
<style>
    .alert-hold {
        background-color: #fffbe6;
        border: 1px solid #ffe58f;
        color: #664d03;
    }
</style>
@endpush

@section('content')
    <div class="container mt-4">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="mdi mdi-check-circle-outline me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="mdi mdi-alert-circle-outline me-2"></i>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif
        @if (session('hold'))
            <div
                class="alert alert-hold shadow-sm d-flex align-items-center justify-content-between px-4 py-3 rounded mb-4 bg-warning">
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-timer-sand fs-4 me-2 text-dark"></i>
                    <span class="text-dark fw-semibold">{{ session('hold') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif

        <h4 class="mb-4 fw-bold text-primary">Liste des demandes d’équipement</h4>

        <div class="table-responsive">
            <table class="table table-striped align-middle table-bordered shadow-sm smart-data-table" data-table-title="les demandes">
                <thead class="table-primary">
                    <tr>
                        <th>Date</th>
                        <th>Équipement(s) demandés(Quantité)</th>
                        <th>Motif</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($demandes as $demande)
                        @php $statutAffichage = $demande->getStatutAffichage(); @endphp
                        <tr>
                            <td>{{ $demande->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                 @foreach ($demande->equipements as $equipement)
                                    @php
                                        $quantiteDemandee = (int) $equipement->pivot->nbr_equipement;
                                        $quantiteServie = $demande->getQuantiteServiePourEquipement($equipement->id);
                                        $quantiteRestante = $demande->getQuantiteRestantePourEquipement($equipement->id, $quantiteDemandee);
                                    @endphp
                                    {{ $equipement->nom }}
                                    (demandé: {{ $quantiteDemandee }}, servi: {{ $quantiteServie }}, restant: {{ $quantiteRestante }})<br>
                                 @endforeach
                            </td>
                            <td>{{ $demande->motif }}</td>
                            <td class="text-center">
                                {{-- Bouton Vérifier (ouvre modal) --}}
                                <button class="btn btn-sm btn-info me-1" data-bs-toggle="modal"
                                    data-bs-target="#verificationModal{{ $demande->id }}">
                                    <i class="mdi mdi-eye-check-outline"></i> Vérifier
                                </button>

                                @if ($statutAffichage !== 'partiellement_servie')
                                    <form action="{{ route('refuser.demande', $demande->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="mdi mdi-close-circle-outline"></i> Rejeter
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>

                        {{-- Modal Vérification --}}
                        <div class="modal fade" id="verificationModal{{ $demande->id }}" tabindex="-1"
                            aria-labelledby="modalLabel{{ $demande->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title" id="modalLabel{{ $demande->id }}">
                                            Vérification de la demande du {{ $demande->created_at->format('d/m/Y H:i') }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Fermer"></button>
                                    </div>
                                    <div class="modal-body">
                                        @php $demandePeutEtreServie = false; @endphp
                                        <form action="{{ route('valider.demande', $demande->id) }}" method="POST"
                                            id="validationForm{{ $demande->id }}">
                                            @csrf @method('PUT')
                                            <ul class="list-group">
                                                @foreach ($demande->equipements as $equipement)
                                                    @php
                                                        $quantiteDemandee = (int) $equipement->pivot->nbr_equipement;
                                                        $quantiteServie = $demande->getQuantiteServiePourEquipement($equipement->id);
                                                        $quantiteRestante = $demande->getQuantiteRestantePourEquipement($equipement->id, $quantiteDemandee);
                                                        $stockDisponible = $equipement->getQuantiteDisponible();
                                                        $quantiteServable = min($quantiteRestante, $stockDisponible);
                                                        $quantiteParDefaut = old('quantites_a_affecter.' . $equipement->id, $quantiteServable);
                                                        $demandePeutEtreServie = $demandePeutEtreServie || $quantiteServable > 0;
                                                    @endphp
                                                    <li class="list-group-item">
                                                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                            <div>
                                                                <strong>{{ $equipement->nom }}</strong>
                                                            </div>
                                                            <span class="badge {{ $quantiteServable > 0 ? 'bg-success' : 'bg-danger' }}">
                                                                Stock disponible : {{ $stockDisponible }}
                                                            </span>
                                                        </div>

                                                        <div class="row mt-3 g-3">
                                                            <div class="col-md-3">
                                                                <label class="form-label mb-1">Demandé</label>
                                                                <input type="number" class="form-control" value="{{ $quantiteDemandee }}" disabled>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label mb-1">Déjà servi</label>
                                                                <input type="number" class="form-control" value="{{ $quantiteServie }}" disabled>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label mb-1">Reste à servir</label>
                                                                <input type="number" class="form-control" value="{{ $quantiteRestante }}" disabled>
                                                            </div>
                                                            <div class="col-md-3">
                                                                <label class="form-label mb-1">À affecter maintenant</label>
                                                                <input type="number"
                                                                    name="quantites_a_affecter[{{ $equipement->id }}]"
                                                                    class="form-control"
                                                                    min="0"
                                                                    max="{{ $quantiteRestante }}"
                                                                    value="{{ $quantiteParDefaut }}">
                                                            </div>
                                                        </div>

                                                        <div class="mt-3">
                                                            <label class="form-label mb-1">Date de retour prévue</label>
                                                            <input type="date"
                                                                name="dates_retour[{{ $equipement->id }}]"
                                                                class="form-control"
                                                                value="{{ old('dates_retour.' . $equipement->id) }}">
                                                        </div>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </form>

                                        @unless ($demandePeutEtreServie)
                                            <div class="alert alert-warning mt-3 mb-0">
                                                Aucune quantité ne peut être servie pour le moment. La demande reste en attente.
                                            </div>
                                        @endunless
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success me-auto"
                                            form="validationForm{{ $demande->id }}"
                                            {{ $demandePeutEtreServie ? '' : 'disabled' }}>
                                                <i class="mdi mdi-check"></i> Servir la demande
                                        </button>

                                        <form action="{{ route('loading.demande', $demande->id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <button type="submit" class="btn btn-warning">
                                                <i class="mdi mdi-timer-sand"></i> Mettre en attente
                                            </button>
                                        </form>

                                        <button type="button" class="btn btn-secondary"
                                            data-bs-dismiss="modal">Fermer</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Aucune demande pour le moment.</td>
                        </tr>
                    @endforelse

                </tbody>
            </table>
            <div class="mt-2">{{ $demandes->links() }}</div>
        </div>
    </div>
@endsection
@push('scripts')
    <!-- CSS Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- JS Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @if (session('pdf'))
        <script>
            window.onload = function() {
                const link = document.createElement('a');
                link.href = "{{ session('pdf') }}";
                link.download = "";
                document.body.appendChild(link);
                link.click();
                document.body.removeChild(link);
            }
        </script>
    @endif
@endpush
