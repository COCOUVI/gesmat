@extends('admin.layouts.adminlay')
@section('content')
    <div class="container mt-5">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card shadow-lg">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Assigner des équipements à un collaborateur externe</h4>
                <p class="mb-0 small mt-2">Créez une affectation d'équipements pour le collaborateur. Les retours seront gérés depuis la liste d'affectations.</p>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <strong>À savoir:</strong> Une affectation génère un bon de <strong>sortie</strong>. 
                    Les<strong>retours</strong> sont gérés depuis la liste d'affectations avec le bouton <strong>Retour</strong>.
                </div>

                <form action="{{ route('HandleBon') }}" method="POST">
                    @csrf

                    {{-- Type de bon hidden (toujours sortie) --}}
                    <input type="hidden" name="type" value="sortie">

                    {{-- Sélection collaborateur --}}
                    <div class="mb-3">
                        <label for="collaborateur" class="form-label">Collaborateur externe <span class="text-danger">*</span></label>
                        <select class="form-select @error('collaborateur_id') is-invalid @enderror" name="collaborateur_id"
                            id="collaborateur" required>
                            <option value="">-- Sélectionnez un collaborateur --</option>
                            @foreach ($collaborateurs as $collab)
                                <option value="{{ $collab->id }}" @selected(old('collaborateur_id') == $collab->id)>
                                    {{ $collab->nom }} {{ $collab->prenom }}
                                </option>
                            @endforeach
                        </select>
                        @error('collaborateur_id')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Motif --}}
                    <div class="mb-3">
                        <label for="motif" class="form-label">Motif <span class="text-danger">*</span></label>
                        <textarea name="motif" id="motif" rows="3"
                            class="form-control @error('motif') is-invalid @enderror" required
                            placeholder="Décrivez le motif du bon...">{{ old('motif') }}</textarea>
                        @error('motif')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <hr>

                    {{-- Équipements dynamiques --}}
                    <h5>Équipements</h5>
                    <div id="equipement-wrapper">
                        @php
                            $oldEquipements = old('equipements', [null]);
                            $oldQuantites = old('quantites', [null]);
                        @endphp
                        @foreach ($oldEquipements as $index => $equipement_id)
                            <div class="equipement-item row mb-3">
                                <div class="col-md-8">
                                    <label class="form-label">Équipement</label>
                                    <select name="equipements[]"
                                        class="form-select equipement-select @error('equipements.' . $index) is-invalid @enderror"
                                        required>
                                        <option value="">-- Sélectionner un équipement --</option>
                                        @foreach ($equipements_groupes as $categorie)
                                            @if ($categorie->equipements->count() > 0)
                                                <optgroup label="{{ $categorie->nom }}">
                                                    @foreach ($categorie->equipements as $equip)
                                                        <option value="{{ $equip->id }}"
                                                            data-disponible="{{ $equip->getQuantiteDisponible() }}"
                                                            data-externe="{{ $equip->getQuantiteAffecteeExterne() }}"
                                                            @selected($equipement_id == $equip->id)>
                                                            {{ $equip->nom }}
                                                            (Dispo: {{ $equip->getQuantiteDisponible() }} | Externe:
                                                            {{ $equip->getQuantiteAffecteeExterne() }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                        @endforeach
                                    </select>
                                    <div class="form-text stock-info mt-1">
                                        Sélectionnez un équipement pour voir les stocks.
                                    </div>
                                    @error('equipements.' . $index)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Quantité</label>
                                    <input type="number" name="quantites[]"
                                        class="form-control @error('quantites.' . $index) is-invalid @enderror" min="1"
                                        required value="{{ $oldQuantites[$index] ?? '' }}">
                                    @error('quantites.' . $index)
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger btn-sm remove-btn w-100">Retirer</button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="btn btn-outline-primary mb-3" id="add-equipement">
                        <i class="mdi mdi-plus"></i> Ajouter un équipement
                    </button>

                    <hr>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('CreateBon') }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn btn-success">
                            <i class="mdi mdi-file-document-outline me-1"></i> Générer le bon
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <template id="equipement-row-template">
        <div class="equipement-item row mb-3">
            <div class="col-md-8">
                <label class="form-label">Équipement</label>
                <select name="equipements[]" class="form-select equipement-select" required>
                    <option value="">-- Sélectionner un équipement --</option>
                    @foreach ($equipements_groupes as $categorie)
                        @if ($categorie->equipements->count() > 0)
                            <optgroup label="{{ $categorie->nom }}">
                                @foreach ($categorie->equipements as $equip)
                                    <option value="{{ $equip->id }}"
                                        data-disponible="{{ $equip->getQuantiteDisponible() }}"
                                        data-externe="{{ $equip->getQuantiteAffecteeExterne() }}">
                                        {{ $equip->nom }}
                                        (Dispo: {{ $equip->getQuantiteDisponible() }} | Externe:
                                        {{ $equip->getQuantiteAffecteeExterne() }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    @endforeach
                </select>
                <div class="form-text stock-info mt-1">
                    Sélectionnez un équipement pour voir les stocks.
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Quantité</label>
                <input type="number" name="quantites[]" class="form-control" min="1" required>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm remove-btn w-100">Retirer</button>
            </div>
        </div>
    </template>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const addButton = document.getElementById('add-equipement');
            const wrapper = document.getElementById('equipement-wrapper');
            const template = document.getElementById('equipement-row-template');
            const typeSelect = document.getElementById('type');

            function updateStockInfo(row) {
                const select = row.querySelector('.equipement-select');
                const stockInfo = row.querySelector('.stock-info');
                const option = select?.selectedOptions?.[0];

                if (stockInfo && option && option.value) {
                    const disponible = option.dataset.disponible;
                    const externe = option.dataset.externe;
                    stockInfo.textContent = `Stock disponible: ${disponible} | Sortie externe: ${externe}`;
                } else {
                    stockInfo.textContent = 'Sélectionnez un équipement pour voir les stocks.';
                }
            }

            function refreshAllRows() {
                wrapper.querySelectorAll('.equipement-item').forEach(row => {
                    updateStockInfo(row);
                });
            }

            function addEquipementField() {
                const clone = template.content.firstElementChild.cloneNode(true);
                wrapper.appendChild(clone);
                updateStockInfo(clone);
            }

            refreshAllRows();

            if (addButton) {
                addButton.addEventListener('click', addEquipementField);
            }

            wrapper.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-btn')) {
                    const rows = wrapper.querySelectorAll('.equipement-item');
                    if (rows.length > 1) {
                        e.target.closest('.equipement-item').remove();
                    } else {
                        alert('Au moins un équipement est requis.');
                    }
                }
            });

            wrapper.addEventListener('change', function(e) {
                if (e.target.classList.contains('equipement-select')) {
                    updateStockInfo(e.target.closest('.equipement-item'));
                }
            });
        });
    </script>

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
