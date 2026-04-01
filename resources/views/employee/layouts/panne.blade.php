@extends('employee.homedash')

@section("content")
    @if(session('success'))
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
            <div class="toast align-items-center text-white bg-success border-0 show" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        {{ session('success') }}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-md-10 col-lg-8">
                <div class="card shadow-lg "style="margin-top:75px;">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">Signaler une Panne</h4>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('post.HandlePanne') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="equipement_id" class="form-label">Équipement concerné</label>
                                <select name="equipement_id" id="equipement_id" class="form-select" required>
                                    <option value="">-- Sélectionnez un équipement --</option>
                                    @forelse($equipements_user as $equipement)
                                        <option value="{{ $equipement->id }}">{{ $equipement->nom }}</option>
                                    @empty
                                           <option disabled>Aucun équipement disponible pour le moment</option>
                                    @endforelse
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="quantite" class="form-label">Nombre d'équipements affectés</label>
                                <input type="number" name="quantite" id="quantite" class="form-control" min="1" value="1" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description de la panne</label>
                                <textarea name="description" id="description" class="form-control" rows="4" placeholder="Décrivez le problème..." required></textarea>
                            </div>

                            <div class="d-grid d-sm-flex justify-content-sm-end">
                                <button type="submit" class="btn btn-danger w-100 w-sm-auto">
                                    <i class="mdi mdi-alert-circle-outline me-1"></i> Signaler la panne
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
