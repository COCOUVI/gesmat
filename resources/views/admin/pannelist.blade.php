@extends('admin.layouts.adminlay')

@section('content')
    <div class="container mt-5">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif

        <div class="card shadow-lg">
            <div class="card-header bg-dark text-white d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <h4 class="mb-1">Liste des pannes signalées</h4>
                    <p class="mb-0 small">Résolution partielle, panne interne et remplacement sont gérés depuis cet écran.</p>
                </div>
                <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#internalPanneModal">
                    Déclarer une panne interne
                </button>
            </div>
            <div class="card-body">
                @if ($pannes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped table-hover align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th class="text-white">Équipement</th>
                                    <th class="text-white">Origine</th>
                                    <th class="text-white">Signalée</th>
                                    <th class="text-white">Encore chez l'employé</th>
                                    <th class="text-white">En panne interne</th>
                                    <th class="text-white">Résoluble</th>
                                    <th class="text-white">Description</th>
                                    <th class="text-white">Déclarant</th>
                                    <th class="text-white">Date</th>
                                    <th class="text-white text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pannes as $panne)
                                    @php
                                        $quantiteRemplacable = $panne->affectation
                                            ? min($panne->getQuantiteEncoreChezEmploye(), $panne->equipement->getQuantiteDisponible())
                                            : 0;
                                    @endphp
                                    <tr>
                                        <td>{{ $panne->equipement->nom }}</td>
                                        <td>
                                            {{ $panne->getOrigineLibelle() }}
                                            @if ($panne->affectation)
                                                <div class="small text-muted">
                                                    {{ $panne->affectation->user->nom }} {{ $panne->affectation->user->prenom }}
                                                </div>
                                            @endif
                                        </td>
                                        <td>{{ $panne->quantite }}</td>
                                        <td>{{ $panne->getQuantiteEncoreChezEmploye() }}</td>
                                        <td>{{ $panne->getQuantiteInterneNonResolue() }}</td>
                                        <td>{{ $panne->getQuantiteResolvable() }}</td>
                                        <td>{{ $panne->description }}</td>
                                        <td>{{ $panne->user->nom }} {{ $panne->user->prenom }}</td>
                                        <td>{{ $panne->created_at->format('d/m/Y H:i') }}</td>
                                        <td class="text-center">
                                            <div class="d-flex flex-column gap-2">
                                                @if ($panne->getQuantiteResolvable() > 0)
                                                    <button type="button" class="btn btn-sm btn-success"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#resolveModal{{ $panne->id }}">
                                                        Résoudre
                                                    </button>
                                                @else
                                                    <span class="small text-muted">Aucune quantité à résoudre</span>
                                                @endif

                                                @if ($quantiteRemplacable > 0)
                                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#replaceModal{{ $panne->id }}">
                                                        Remplacer
                                                    </button>
                                                @elseif ($panne->affectation && $panne->getQuantiteEncoreChezEmploye() > 0)
                                                    <span class="small text-muted">Pas de stock pour remplacer</span>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    @if ($panne->getQuantiteResolvable() > 0)
                                        <div class="modal fade" id="resolveModal{{ $panne->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-success text-white">
                                                        <h5 class="modal-title">Résolution partielle de {{ $panne->equipement->nom }}</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                                    </div>
                                                    <form action="{{ route('pannes.resolu', $panne->id) }}" method="POST">
                                                        @csrf
                                                        @method('PUT')
                                                        <div class="modal-body">
                                                            <label class="form-label">Quantité à résoudre</label>
                                                            <input type="number" name="quantite_resolue" class="form-control"
                                                                min="1" max="{{ $panne->getQuantiteResolvable() }}"
                                                                value="{{ $panne->getQuantiteResolvable() }}" required>
                                                            <small class="text-muted">
                                                                Maximum résoluble maintenant : {{ $panne->getQuantiteResolvable() }}
                                                            </small>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                            <button type="submit" class="btn btn-success">Confirmer</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if ($quantiteRemplacable > 0)
                                        <div class="modal fade" id="replaceModal{{ $panne->id }}" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title">Remplacer une quantité en panne</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                                                    </div>
                                                    <form action="{{ route('pannes.remplacer', $panne->id) }}" method="POST">
                                                        @csrf
                                                        <div class="modal-body">
                                                            <p class="mb-2">La quantité remplacée sera retournée au stock en panne, puis une nouvelle affectation saine sera créée.</p>
                                                            <label class="form-label">Quantité à remplacer</label>
                                                            <input type="number" name="quantite_remplacement" class="form-control"
                                                                min="1" max="{{ $quantiteRemplacable }}"
                                                                value="{{ $quantiteRemplacable }}" required>
                                                            <small class="text-muted">
                                                                Maximum remplaçable maintenant : {{ $quantiteRemplacable }}
                                                            </small>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                                                            <button type="submit" class="btn btn-primary">Confirmer</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-2">{{ $pannes->links() }}</div>
                    </div>
                @else
                    <div class="alert alert-info text-center">
                        Aucune panne signalée pour le moment.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="modal fade" id="internalPanneModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Déclarer une panne interne</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <form action="{{ route('pannes.store-interne') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="equipement_id" class="form-label">Équipement</label>
                            <select name="equipement_id" id="equipement_id" class="form-select" required>
                                <option value="">-- Sélectionnez un équipement --</option>
                                @foreach ($equipementsInternes as $equipement)
                                    <option value="{{ $equipement->id }}">
                                        {{ $equipement->nom }} (stock disponible: {{ $equipement->getQuantiteDisponible() }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="quantite" class="form-label">Quantité en panne</label>
                            <input type="number" name="quantite" id="quantite" class="form-control" min="1" required>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                        <button type="submit" class="btn btn-danger">Enregistrer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
