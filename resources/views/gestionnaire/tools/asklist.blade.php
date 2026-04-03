@extends('gestionnaire.tools.layouts.gestionlay')
@section('content')
    <div class="container mt-4">
        <h4 class="mb-4 fw-bold text-primary">Liste des demandes d’équipement</h4>

        <div class="table-responsive">
            <table class="table table-striped align-middle table-bordered shadow-sm smart-data-table" data-table-title="les demandes">
                <thead class="table-primary">
                    <tr>
                        <th scope="col">Date</th>
                        <th scope="col">Équipement(s) demandés</th>
                        <th scope="col">Motif</th>
                        <th scope="col" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($demandes as $demande)
                        <tr>
                            <td>{{ $demande->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                @foreach($demande->equipements as $equipement)
                                    {{ $equipement->nom }} ({{ $equipement->pivot->nbr_equipement }})<br>
                                @endforeach
                            </td>
                            <td>{{ $demande->motif }}</td>
                            <td class="text-center">
                                <form action="" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-success me-1">
                                        <i class="mdi mdi-check-circle-outline"></i> Valider
                                    </button>
                                </form>

                                <form action="**" method="POST" class="d-inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="mdi mdi-close-circle-outline"></i> Rejeter
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted">Aucune demande pour le moment.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
