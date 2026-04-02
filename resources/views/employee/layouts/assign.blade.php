@extends('employee.homedash')
@section('content')
    <div class="container" style="margin-top:74px;">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Mes équipements assignés</h5>
            </div>
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                @endif

                @if ($affectations->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped align-middle smart-data-table" data-table-title="vos affectations">
                            <thead class="table-light">
                                <tr>
                                    <th>Équipement</th>
                                    <th>Origine</th>
                                    <th>Quantité affectée</th>
                                    <th>Retournée</th>
                                    <th>Encore chez moi</th>
                                    <th>Saine</th>
                                    <th>En panne</th>
                                    <th>Date de retour</th>
                                    <th>Statut</th>
                                    <th>Photo</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($affectations as $affectation)
                                    <tr>
                                        <td class="text-wrap">{{ $affectation->equipement->nom }}</td>
                                        <td>
                                            {{ $affectation->getOrigineLibelle() }}
                                            @if ($affectation->demande_id)
                                                <div class="small text-muted">Demande #{{ $affectation->demande_id }}</div>
                                            @endif
                                        </td>
                                        <td>{{ $affectation->quantite_affectee }}</td>
                                        <td>{{ $affectation->getQuantiteRetournee() }}</td>
                                        <td>{{ $affectation->getQuantiteActive() }}</td>
                                        <td>{{ $affectation->getQuantiteSaineActive() }}</td>
                                        <td>{{ $affectation->getQuantitePannesNonResolues() }}</td>
                                        <td>
                                            {{ $affectation->date_retour ? $affectation->date_retour->format('d/m/Y') : 'Aucune date prévue' }}
                                        </td>
                                        <td>{{ $affectation->getStatutAffichage() }}</td>
                                        <td>
                                            <a href="#" data-bs-toggle="modal"
                                                data-bs-target="#imageModal{{ $affectation->id }}">
                                                <img src="/{{ $affectation->equipement->image_path }}" alt="Photo"
                                                    class="img-fluid rounded shadow-sm" style="max-width: 80px;">
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <p class="text-muted">Aucun équipement assigné pour le moment.</p>
                @endif

                @foreach ($affectations as $affectation)
                    <div class="modal fade" id="imageModal{{ $affectation->id }}" tabindex="-1"
                        aria-labelledby="imageModalLabel{{ $affectation->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-xl">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title">{{ $affectation->equipement->nom }}</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Fermer"></button>
                                </div>
                                <div class="modal-body text-center">
                                    <img src="/{{ $affectation->equipement->image_path }}" class="img-fluid rounded shadow"
                                        alt="Image de {{ $affectation->equipement->nom }}">
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
        <div class="mt-2">{{ $affectations->links() }}</div>
    </div>
@endsection
