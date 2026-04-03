@extends('employee.homedash')
@section('content')
    <div class="content-wrapper">
        <div class="row justify-content-center">
            <div class="col-md-10 grid-margin stretch-card">
                <div class="card mt-5">
                    <div class="card-body" style="padding-top: 1.5rem;">
                        <h4 class="card-title">Faire une demande d'équipement</h4>
                        {{--  Message de succès --}}
                        @if (session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Fermer"></button>
                            </div>
                        @endif
                        <form method="POST" action="{{ route('demande.soumise') }}">
                            @csrf
                            <div class="row g-3">
                                <div class="form-group col-12 col-md-6">
                                    <label for="lieu">Lieu</label>
                                    <input type="text" name="lieu" class="form-control" value="{{ old('lieu') }}" required>
                                    @error('lieu')
                                        <span class="text-danger mt-2">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group col-12 col-md-6">
                                    <label for="motif">Usage</label>
                                    <textarea name="motif" class="form-control" rows="3" required>{{ old('motif') }}</textarea>
                                    @error('motif')
                                        <span class="text-danger mt-2">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <hr>

                            <div id="equipement-wrapper">
                                <div class="equipement-item row gy-2 mb-3">
                                    <div class="col-12 col-md-6">
                                        <label>Équipement</label>
                                        <select name="equipements[]" class="form-control equipement-select" required>
                                            <option value="">-- Sélectionner un équipement --</option>
                                            @foreach ($equipements_par_categorie as $categorie)
                                              @if ($categorie->equipements->count())
                                                <optgroup label="{{ $categorie->nom }}">
                                                    @foreach ($categorie->equipements as $equipement)
                                                        <option value="{{ $equipement->id }}"
                                                            data-stock-disponible="{{ $equipement->getQuantiteDisponible() }}"
                                                            {{ old('equipements.0') == $equipement->id ? 'selected' : '' }}>
                                                            {{ $equipement->nom }} (Stock disponible: {{ $equipement->getQuantiteDisponible() }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                              @endif
                                            @endforeach
                                        </select>

                                        @error('equipements')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-md-4">
                                        <label>Quantité</label>
                                        <input type="number" name="quantites[]" class="form-control" min="1"
                                            value="{{ old('quantites.0') }}" required>
                                        <small class="text-muted stock-help">Stock disponible actuellement : --</small>
                                        @error('quantites')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                    <div class="col-12 col-md-2 d-flex align-items-end">
                                        <button type="button"
                                            class="btn btn-danger btn-sm w-100 remove-btn">Supprimer</button>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-column flex-sm-row gap-2 mb-3">
                                <button type="button" class="btn btn-outline-primary w-100 w-sm-auto" id="add-equipement">
                                    <i class="mdi mdi-plus"></i> Ajouter un équipement
                                </button>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">
                                    Soumettre la demande
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="/js1/dashboard.js"></script>
    <script>
        document.getElementById('add-equipement').addEventListener('click', function() {
            let wrapper = document.getElementById('equipement-wrapper');

            let newField = document.createElement('div');
            newField.classList.add('equipement-item', 'row', 'mb-3');
            newField.innerHTML = `
                <div class="col-md-6">
                    <label>Équipement</label>
                    <select name="equipements[]" class="form-control equipement-select" required>
                        <option value="">-- Sélectionner un équipement --</option>
                        @foreach ($equipements_par_categorie as $categorie)
                            <optgroup label="{{ $categorie->nom }}">
                                @foreach ($categorie->equipements as $equipement)
                                    <option value="{{ $equipement->id }}" data-stock-disponible="{{ $equipement->getQuantiteDisponible() }}">{{ $equipement->nom }} (Stock disponible: {{ $equipement->getQuantiteDisponible() }})</option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label>Quantité</label>
                    <input type="number" name="quantites[]" class="form-control" min="1" required>
                    <small class="text-muted stock-help">Stock disponible actuellement : --</small>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-sm remove-btn w-100">Supprimer</button>
                </div>
            `;
            wrapper.appendChild(newField);
        });

        // Supprimer une ligne
        document.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('remove-btn')) {
                e.target.closest('.equipement-item').remove();
            }
        });

        document.addEventListener('change', function(e) {
            if (e.target && e.target.classList.contains('equipement-select')) {
                const selectedOption = e.target.options[e.target.selectedIndex];
                const stockDisponible = selectedOption.dataset.stockDisponible ?? '--';
                const container = e.target.closest('.equipement-item');
                const stockHelp = container.querySelector('.stock-help');

                if (stockHelp) {
                    stockHelp.textContent = `Stock disponible actuellement : ${stockDisponible}`;
                }
            }
        });

        document.querySelectorAll('.equipement-select').forEach(function(select) {
            select.dispatchEvent(new Event('change'));
        });
    </script>
@endsection
