@extends('admin.layouts.adminlay')
@section('content')
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header bg-warning text-dark">
                <h4 class="mb-0">Matériels non retournés</h4>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                @endif
                @if ($equipement_lost->isEmpty())
                    <p class="text-center text-muted">Aucun matériel en retard n'a été trouvé.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle smart-data-table" data-table-title="les retours d'équipements">
                            <thead class="table-light">
                                <tr>
                                    <th>Matériel</th>
                                    <th>Employé</th>
                                    <th>Quantité active</th>
                                    <th>Dont en panne</th>
                                    <th>Date prévue de retour</th>
                                    <th>Échéance</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($equipement_lost as $affectation)
                                    <tr>
                                        <td>{{ $affectation->equipement->nom ?? 'Inconnu' }}</td>
                                        <td>{{ $affectation->user->nom ?? '-' }} {{ $affectation->user->prenom ?? '' }}</td>
                                        <td>{{ $affectation->getQuantiteActive() }}</td>
                                        <td>{{ $affectation->getQuantitePannesNonResolues() }}</td>
                                        <td>{{ \Carbon\Carbon::parse($affectation->date_retour)->format('d/m/Y') }}</td>
                                        <td>
                                            @if ($affectation->date_retour && $affectation->date_retour->isPast())
                                                <span class="badge bg-danger">En retard</span>
                                            @else
                                                <span class="badge bg-info text-dark">À venir</span>
                                            @endif
                                        </td>
                                        <td>
                                            <button type="button" class="btn btn-success btn-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#returnModalLost{{ $affectation->id }}">
                                                <i class="mdi mdi-undo"></i> Retourner
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach

                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @foreach ($equipement_lost as $affectation)
        <div class="modal fade" id="returnModalLost{{ $affectation->id }}" tabindex="-1" aria-hidden="true">
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
                                    <label class="form-label">
                                        Quantité en panne à retourner
                                    </label>
                                    <input type="number" name="pannes_retournees[{{ $panne->id }}]" class="form-control"
                                        min="0" max="{{ $panne->getQuantiteEncoreChezEmploye() }}" value="0">
                                    <small class="text-muted">
                                        Panne #{{ $panne->id }} : maximum {{ $panne->getQuantiteEncoreChezEmploye() }}
                                    </small>
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
