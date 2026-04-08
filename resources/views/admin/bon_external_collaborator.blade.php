@extends('admin.layouts.adminlay')
@section('content')
    @php
        $selectedType = old('type', 'sortie');
    @endphp
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
                <h4 class="mb-0" id="bon-title">
                    {{ $selectedType === 'entrée' ? 'Enregistrer une livraison de matériel' : 'Assigner des équipements à un collaborateur externe' }}
                </h4>
                <p class="mb-0 small mt-2" id="bon-subtitle">
                    {{ $selectedType === 'entrée'
                        ? 'Le bon d’entrée enregistre le matériel livré par le collaborateur et augmente le stock.'
                        : 'Le bon de sortie enregistre le matériel emprunté par le collaborateur et crée les affectations externes.' }}
                </p>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <label class="form-label fw-semibold d-block">Type de bon</label>
                    <div class="d-flex flex-wrap gap-2" id="bon-type-toggle">
                        <button type="button"
                            class="btn {{ $selectedType === 'entrée' ? 'btn-success active' : 'btn-outline-success' }} bon-type-btn"
                            data-type="entrée">
                            Bon d’entrée
                        </button>
                        <button type="button"
                            class="btn {{ $selectedType === 'sortie' ? 'btn-primary active' : 'btn-outline-primary' }} bon-type-btn"
                            data-type="sortie">
                            Bon de sortie
                        </button>
                    </div>
                </div>

                <div class="alert {{ $selectedType === 'entrée' ? 'alert-success' : 'alert-info' }}" id="bon-helper">
                    <strong>À savoir :</strong>
                    <span id="bon-helper-text">
                        {{ $selectedType === 'entrée'
                            ? 'Utilise ce formulaire quand un collaborateur externe livre du matériel. Le stock des équipements sélectionnés sera augmenté.'
                            : 'Utilise ce formulaire quand un collaborateur externe emprunte du matériel. Des affectations externes seront créées et le stock disponible diminuera.' }}
                    </span>
                </div>

                <form action="{{ route('HandleBon') }}" method="POST">
                    @csrf

                    <input type="hidden" name="type" id="bon-type" value="{{ $selectedType }}">

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
                            $oldDatesRetour = old('dates_retour', [null]);
                        @endphp
                        @foreach ($oldEquipements as $index => $equipement_id)
                            <div class="equipement-item row g-3 mb-3 align-items-end">
                                <div class="col-md-4">
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
                                <div class="col-md-3 date-retour-group {{ $selectedType === 'entrée' ? 'd-none' : '' }}">
                                    <label class="form-label">Date de retour</label>
                                    <input type="date" name="dates_retour[]"
                                        class="form-control @error('dates_retour.' . $index) is-invalid @enderror"
                                        value="{{ $oldDatesRetour[$index] ?? '' }}"
                                        @disabled($selectedType === 'entrée')>
                                    <div class="form-text">Optionnelle pour un prêt temporaire.</div>
                                    @error('dates_retour.' . $index)
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
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-outline-danger remove-btn w-100">Retirer la ligne</button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="btn {{ $selectedType === 'entrée' ? 'btn-outline-success' : 'btn-outline-primary' }} mb-3" id="add-equipement">
                        <i class="mdi mdi-plus"></i> Ajouter un équipement
                    </button>

                    <hr>

                    <div class="d-flex justify-content-end gap-2">
                        <a href="{{ route('CreateBon') }}" class="btn btn-secondary">Annuler</a>
                        <button type="submit" class="btn {{ $selectedType === 'entrée' ? 'btn-success' : 'btn-primary' }}" id="submit-bon-button">
                            <i class="mdi mdi-file-document-outline me-1"></i> Générer le bon
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <template id="equipement-row-template">
        <div class="equipement-item row g-3 mb-3 align-items-end">
            <div class="col-md-4">
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
            <div class="col-md-3 date-retour-group {{ $selectedType === 'entrée' ? 'd-none' : '' }}">
                <label class="form-label">Date de retour</label>
                <input type="date" name="dates_retour[]" class="form-control" @disabled($selectedType === 'entrée')>
                <div class="form-text">Optionnelle pour un prêt temporaire.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Quantité</label>
                <input type="number" name="quantites[]" class="form-control" min="1" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-outline-danger remove-btn w-100">Retirer la ligne</button>
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
            const typeInput = document.getElementById('bon-type');
            const typeButtons = document.querySelectorAll('.bon-type-btn');
            const title = document.getElementById('bon-title');
            const subtitle = document.getElementById('bon-subtitle');
            const helper = document.getElementById('bon-helper');
            const helperText = document.getElementById('bon-helper-text');
            const submitButton = document.getElementById('submit-bon-button');

            const typeConfig = {
                'entrée': {
                    title: 'Enregistrer une livraison de matériel',
                    subtitle: 'Le bon d’entrée enregistre le matériel livré par le collaborateur et augmente le stock.',
                    helperClass: 'alert-success',
                    helperText: 'Utilise ce formulaire quand un collaborateur externe livre du matériel. Le stock des équipements sélectionnés sera augmenté.',
                    addButtonClass: 'btn-outline-success',
                    submitButtonClass: 'btn-success',
                },
                sortie: {
                    title: 'Assigner des équipements à un collaborateur externe',
                    subtitle: 'Le bon de sortie enregistre le matériel emprunté par le collaborateur et crée les affectations externes.',
                    helperClass: 'alert-info',
                    helperText: 'Utilise ce formulaire quand un collaborateur externe emprunte du matériel. Des affectations externes seront créées et le stock disponible diminuera.',
                    addButtonClass: 'btn-outline-primary',
                    submitButtonClass: 'btn-primary',
                }
            };

            function updateStockInfo(row) {
                const select = row.querySelector('.equipement-select');
                const stockInfo = row.querySelector('.stock-info');
                const option = select?.selectedOptions?.[0];
                const selectedType = typeInput.value;

                if (stockInfo && option && option.value) {
                    const disponible = option.dataset.disponible;
                    const externe = option.dataset.externe;

                    if (selectedType === 'entrée') {
                        stockInfo.textContent = `Stock actuel avant entrée: ${disponible} | Déjà sorti externe: ${externe}`;
                    } else {
                        stockInfo.textContent = `Stock disponible: ${disponible} | Déjà sorti externe: ${externe}`;
                    }
                } else {
                    stockInfo.textContent = 'Sélectionnez un équipement pour voir les stocks.';
                }
            }

            function updateDateRetourVisibility() {
                const selectedType = typeInput.value;

                wrapper.querySelectorAll('.date-retour-group').forEach((group) => {
                    const input = group.querySelector('input[name="dates_retour[]"]');
                    const hidden = selectedType === 'entrée';

                    group.classList.toggle('d-none', hidden);

                    if (input) {
                        input.disabled = hidden;

                        if (hidden) {
                            input.value = '';
                        }
                    }
                });
            }

            function updateTypeUi() {
                const selectedType = typeInput.value;
                const config = typeConfig[selectedType];

                title.textContent = config.title;
                subtitle.textContent = config.subtitle;
                helper.classList.remove('alert-info', 'alert-success');
                helper.classList.add(config.helperClass);
                helperText.textContent = config.helperText;
                addButton.classList.remove('btn-outline-primary', 'btn-outline-success');
                addButton.classList.add(config.addButtonClass);
                submitButton.classList.remove('btn-primary', 'btn-success');
                submitButton.classList.add(config.submitButtonClass);

                typeButtons.forEach((button) => {
                    const isActive = button.dataset.type === selectedType;

                    button.classList.toggle('active', isActive);
                    button.classList.toggle('btn-success', button.dataset.type === 'entrée' && isActive);
                    button.classList.toggle('btn-outline-success', button.dataset.type === 'entrée' && !isActive);
                    button.classList.toggle('btn-primary', button.dataset.type === 'sortie' && isActive);
                    button.classList.toggle('btn-outline-primary', button.dataset.type === 'sortie' && !isActive);
                });

                updateDateRetourVisibility();
                refreshAllRows();
            }

            function refreshRemoveButtons() {
                const rows = wrapper.querySelectorAll('.equipement-item');

                rows.forEach((row) => {
                    const button = row.querySelector('.remove-btn');

                    if (button) {
                        button.disabled = rows.length === 1;
                    }
                });
            }

            function refreshAllRows() {
                wrapper.querySelectorAll('.equipement-item').forEach(row => {
                    updateStockInfo(row);
                });

                refreshRemoveButtons();
            }

            function addEquipementField() {
                const clone = template.content.firstElementChild.cloneNode(true);
                wrapper.appendChild(clone);
                if (window.initEnhancedSelects) {
                    window.initEnhancedSelects(clone);
                }
                updateStockInfo(clone);
                refreshRemoveButtons();
            }

            refreshAllRows();

            if (addButton) {
                addButton.addEventListener('click', addEquipementField);
            }

            typeButtons.forEach((button) => {
                button.addEventListener('click', function() {
                    typeInput.value = this.dataset.type;
                    updateTypeUi();
                });
            });

            wrapper.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-btn')) {
                    const rows = wrapper.querySelectorAll('.equipement-item');
                    if (rows.length > 1) {
                        e.target.closest('.equipement-item').remove();
                        refreshRemoveButtons();
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

            updateTypeUi();
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
