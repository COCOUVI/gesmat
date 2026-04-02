@extends('gestionnaire.tools.layouts.gestionlay')

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
                @if ($equipement_lost->isEmpty())
                    <p class="text-center text-muted">Aucun matériel en retard n'a été trouvé.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle smart-data-table" data-table-title="les retours d'équipements">
                            <thead class="table-light">
                                <tr>
                                    <th>Matériel</th>
                                    <th>Employé</th>
                                    <th>Date prévue de retour</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($equipement_lost as $affectation)
                                    <tr>
                                        <td>{{ $affectation->equipement->nom ?? 'Inconnu' }}</td>
                                        <td>{{ $affectation->user->nom ?? '-' }} {{ $affectation->user->prenom ?? '' }}</td>
                                        <td>{{ \Carbon\Carbon::parse($affectation->date_retour)->format('d/m/Y') }}</td>
                                        <td>
                                            <form action="{{ route('gestionnaire.equipements.retourner', $affectation->id) }}" method="POST" style="display: inline;">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-success">Retourner</button>
                                            </form>
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
@endsection
@push('scripts')
    @if(session('pdf'))
        <script>
            window.onload = function () {
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
