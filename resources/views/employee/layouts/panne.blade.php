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
                <div class="card shadow-lg" style="margin-top: 75px;">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">Signaler une Panne</h4>
                    </div>
                    <div class="card-body">
                        {{-- Afficher tous les messages d'erreur --}}
                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>Erreur(s) détectée(s):</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if($affectations->isEmpty())
                            <div class="alert alert-info" role="alert">
                                <i class="mdi mdi-information-outline me-2"></i>
                                Vous n'avez pas d'équipements assignés pour le moment.
                            </div>
                        @else
                            <form action="{{ route('post.HandlePanne') }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label for="equipement_id" class="form-label">Équipement concerné</label>
                                    <select name="equipement_id" id="equipement_id" 
                                            class="form-select @error('equipement_id') is-invalid @enderror" 
                                            required onchange="updateQuantiteMax()">
                                        <option value="">-- Sélectionnez un équipement --</option>
                                        @foreach($affectations as $affectation)
                                            <option value="{{ $affectation->equipement->id }}" 
                                                    data-quantite-affectee="{{ $affectation->quantite_affectee }}"
                                                    data-quantite-en-panne="{{ $affectation->pannes->sum('quantite') }}"
                                                    {{ old('equipement_id') == $affectation->equipement->id ? 'selected' : '' }}>
                                                {{ $affectation->equipement->nom }}
                                                <em>(Affecté: {{ $affectation->quantite_affectee }}, 
                                                    Disponible: {{ $affectation->quantite_affectee - $affectation->pannes->sum('quantite') }})</em>
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('equipement_id')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Affichage des infos d'équipement sélectionné --}}
                                <div class="mb-3 p-3 bg-light rounded" id="equipement-info" style="display: none;">
                                    <p class="mb-1"><strong>Quantité affectée:</strong> <span id="quantite-affectee">0</span></p>
                                    <p class="mb-1"><strong>Quantité déjà signalée en panne:</strong> <span id="quantite-en-panne">0</span></p>
                                    <p class="mb-0"><strong>Quantité disponible à signaler:</strong> <span id="quantite-disponible" class="text-primary">0</span></p>
                                </div>

                                <div class="mb-3">
                                    <label for="quantite" class="form-label">Nombre d'équipements en panne</label>
                                    <input type="number" name="quantite" id="quantite" 
                                           class="form-control @error('quantite') is-invalid @enderror" 
                                           min="1" value="{{ old('quantite', 1) }}" required>
                                    <small class="form-text text-muted">
                                        Vous pouvez signaler au maximum <span id="max-quantite">0</span> équipement(s)
                                    </small>
                                    @error('quantite')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Description de la panne</label>
                                    <textarea name="description" id="description" 
                                              class="form-control @error('description') is-invalid @enderror" 
                                              rows="4" placeholder="Décrivez le problème..." 
                                              required>{{ old('description') }}</textarea>
                                    <small class="form-text text-muted">Minimum 10 caractères</small>
                                    @error('description')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-grid d-sm-flex justify-content-sm-end gap-2">
                                    <a href="" class="btn btn-outline-secondary">
                                        <i class="mdi mdi-arrow-left me-1"></i> Retour
                                    </a>
                                    <button type="submit" class="btn btn-danger">
                                        <i class="mdi mdi-alert-circle-outline me-1"></i> Signaler la panne
                                    </button>
                                </div>
                            </form>

                            <script>
                                // Mettre à jour les infos et le max quantité au changement d'équipement
                                function updateQuantiteMax() {
                                    const select = document.getElementById('equipement_id');
                                    const option = select.options[select.selectedIndex];
                                    
                                    if (option.value === '') {
                                        document.getElementById('equipement-info').style.display = 'none';
                                        document.getElementById('quantite').max = 1;
                                        document.getElementById('max-quantite').textContent = '0';
                                        return;
                                    }

                                    const quantiteAffectee = parseInt(option.dataset.quantiteAffectee) || 0;
                                    const quantiteEnPanne = parseInt(option.dataset.quantiteEnPanne) || 0;
                                    const quantiteDisponible = quantiteAffectee - quantiteEnPanne;

                                    document.getElementById('quantite-affectee').textContent = quantiteAffectee;
                                    document.getElementById('quantite-en-panne').textContent = quantiteEnPanne;
                                    document.getElementById('quantite-disponible').textContent = quantiteDisponible;
                                    document.getElementById('max-quantite').textContent = quantiteDisponible;
                                    document.getElementById('quantite').max = quantiteDisponible;
                                    document.getElementById('equipement-info').style.display = 'block';
                                }

                                // Initialiser à la charge de la page
                                document.addEventListener('DOMContentLoaded', function() {
                                    updateQuantiteMax();
                                });
                            </script>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

