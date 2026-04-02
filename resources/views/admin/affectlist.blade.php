@extends('admin.layouts.adminlay')
@section('content')
    <div class="container-fluid mt-4 px-4">
        <div class="card shadow">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Liste des Affectations</h4>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                @endif
                @if (session('remove'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="mdi mdi-alert-circle-outline me-2"></i>
                        {{ session('remove') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                @endif

                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th class="text-center">Équipement</th>
                                <th class="text-center">Origine</th>
                                <th class="text-center">Affectée</th>
                                <th class="text-center">Retournée</th>
                                <th class="text-center">Encore chez l'employé</th>
                                <th class="text-center">Saine chez l'employé</th>
                                <th class="text-center">En panne chez l'employé</th>
                                <th class="text-center">Affecté à</th>
                                @if (auth()->user()->role === 'admin')
                                    <th>Effectué par</th>
                                @endif
                                <th class="text-center">Date</th>
                                <th class="text-center">date_retour</th>
                                <th class="text-center">Statut</th>
                                <th class="text-center">Actions</th>
    
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($affectations as  $affectation)
                                <tr>
                                    <td class="text-nowrap">{{ $affectation->id }}</td>
                                    <td class="text-nowrap">{{ $affectation->equipement->nom ?? 'Inconnu' }}</td>
                                    <td class="text-nowrap">
                                        {{ $affectation->getOrigineLibelle() }}
                                        @if ($affectation->demande_id)
                                            <div class="small text-muted">Demande #{{ $affectation->demande_id }}</div>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">{{ $affectation->quantite_affectee ?? 1 }}</td>
                                    <td class="text-nowrap">{{ $affectation->getQuantiteRetournee() }}</td>
                                    <td class="text-nowrap">{{ $affectation->getQuantiteActive() }}</td>
                                    <td class="text-nowrap">{{ $affectation->getQuantiteSaineActive() }}</td>
                                    <td class="text-nowrap">{{ $affectation->getQuantitePannesNonResolues() }}</td>
                                    <td class="text-nowrap">{{ $affectation->user->nom }} {{ $affectation->user->prenom }}
                                    </td>
                                    @if (auth()->user()->role == 'admin')
                                        <td class="text-nowrap">{{ $affectation->created_by ?? 'Admin' }}</td>
                                    @endif
                                    <td class="text-nowrap">{{ $affectation->created_at->format('d/m/Y') }}</td>
                                    <td>{{ $affectation->date_retour ? $affectation->date_retour->format('d/m/Y') : 'Aucune date de retour enregistrée ou équipement non concerné par un retour.' }}
                                    </td>
                                    <td>{{ $affectation->getStatutAffichage() }}</td>
                                    <td>
                                        <div class="d-flex flex-column gap-2">
                                            @if ($affectation->getQuantiteActive() > 0)
                                                <button type="button" class="btn btn-sm btn-success"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#returnModal{{ $affectation->id }}">
                                                    Retour
                                                </button>
                                            @else
                                                <span class="text-muted">Complet</span>
                                            @endif

                                            @if ($affectation->peutEtreAnnulee())
                                                <form action="{{ route('affectation.annuler', $affectation->id) }}" method="POST"
                                                    onsubmit="return confirm('Voulez-vous vraiment annuler cette affectation ?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                                        Annuler
                                                    </button>
                                                </form>
                                            @else
                                                <span class="small text-muted">{{ $affectation->getMotifBlocageAnnulation() }}</span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="13" class="text-center">Aucune affectation enregistrée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-2">{{ $affectations->links() }}</div>
            </div>
        </div>
    </div>

    @foreach ($affectations as $affectation)
        @if ($affectation->getQuantiteActive() > 0)
            <div class="modal fade" id="returnModal{{ $affectation->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-success text-white">
                            <h5 class="modal-title">Retour partiel de {{ $affectation->equipement->nom }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>
                        <form action="{{ route('affectation.retourner', $affectation->id) }}" method="POST">
                            @csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Quantité saine à retourner</label>
                                    <input type="number" name="quantite_saine_retournee" class="form-control"
                                        min="0" max="{{ $affectation->getQuantiteSaineActive() }}"
                                        value="{{ $affectation->getQuantiteSaineActive() }}">
                                    <small class="text-muted">Maximum sain disponible : {{ $affectation->getQuantiteSaineActive() }}</small>
                                </div>

                                @foreach ($affectation->pannes->where('statut', '!=', 'resolu') as $panne)
                                    <div class="mb-3">
                                        <label class="form-label">Quantité en panne à retourner</label>
                                        <input type="number" name="pannes_retournees[{{ $panne->id }}]" class="form-control"
                                            min="0" max="{{ $panne->getQuantiteEncoreChezEmploye() }}" value="0">
                                        <small class="text-muted">Panne #{{ $panne->id }} : maximum {{ $panne->getQuantiteEncoreChezEmploye() }}</small>
                                    </div>
                                @endforeach
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                <button type="submit" class="btn btn-success">Confirmer le retour</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    @endforeach
@endsection
@push('scripts')
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
