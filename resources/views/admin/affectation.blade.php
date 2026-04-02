@extends('admin.layouts.adminlay')
@section('content')
    @php
        $hasEquipementDispo = false;
        $optionsHtml = '';
        $stocksParEquipement = [];
        $oldEquipements = old('equipements', ['']);
        $oldQuantites = old('quantites', ['']);
        $oldDatesRetour = old('dates_retour', ['']);
    @endphp

    @foreach ($equipements_groupes as $categorie)
        @if ($categorie->equipements->count() > 0)
            @php
                $hasEquipementDispo = true;
                $optionsHtml .= '<optgroup label="' . e($categorie->nom) . '">';
            @endphp

            @foreach ($categorie->equipements as $equipement)
                @php
                    $stocksParEquipement[$equipement->id] = $equipement->getQuantiteDisponible();
                    $optionsHtml .= '<option value="' . e($equipement->id) . '" data-stock-disponible="' . e($equipement->getQuantiteDisponible()) . '">' . e($equipement->nom) . ' (Stock disponible: ' . e($equipement->getQuantiteDisponible()) . ')</option>';
                @endphp
            @endforeach

            @php
                $optionsHtml .= '</optgroup>';
            @endphp
        @endif
    @endforeach

    @if (!$hasEquipementDispo)
        @php $optionsHtml .= '<option value="">Aucun équipement disponible</option>'; @endphp
    @endif

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
                <h4 class="mb-1">Affectation directe d'équipements</h4>
                <p class="mb-0 small">Le stock affiché correspond au stock disponible réel au moment de l'affectation.</p>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    Deux lignes avec le même équipement et la même date de retour seront fusionnées en une seule affectation.
                    Si les dates de retour sont différentes, des affectations distinctes seront créées.
                </div>

                <form action="{{ route('handle.affectation') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="employe_id" class="form-label">Employé</label>
                        <select name="employe_id" id="employe_id"
                            class="form-select @error('employe_id') is-invalid @enderror" required>
                            <option value="">-- Sélectionnez un employé --</option>
                            @foreach ($employes as $employe)
                                <option value="{{ $employe->id }}"
                                    @selected((string) old('employe_id') === (string) $employe->id)>
                                    {{ $employe->nom }} {{ $employe->prenom }}
                                </option>
                            @endforeach
                        </select>
                        @error('employe_id')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="motif" class="form-label">Motif</label>
                        <textarea name="motif" id="motif" class="form-control @error('motif') is-invalid @enderror" rows="3" required>{{ old('motif') }}</textarea>
                        @error('motif')
                            <span class="text-danger">{{ $message }}</span>
                        @enderror
                    </div>

                    <hr>

                    <div id="equipement-wrapper">
                        @foreach ($oldEquipements as $index => $selectedEquipement)
                            @php
                                $stockDisponible = $selectedEquipement !== '' && isset($stocksParEquipement[(int) $selectedEquipement])
                                    ? $stocksParEquipement[(int) $selectedEquipement]
                                    : null;
                            @endphp
                            <div class="equipement-item row mb-3">
                                <div class="col-md-4">
                                    <label class="form-label">Équipement</label>
                                    <select name="equipements[]" class="form-select equipement-select @error('equipements.' . $index) is-invalid @enderror" required>
                                        <option value="">-- Sélectionner un équipement --</option>
                                        @foreach ($equipements_groupes as $categorie)
                                            @if ($categorie->equipements->count() > 0)
                                                <optgroup label="{{ $categorie->nom }}">
                                                    @foreach ($categorie->equipements as $equipement)
                                                        <option value="{{ $equipement->id }}"
                                                            data-stock-disponible="{{ $equipement->getQuantiteDisponible() }}"
                                                            @selected((string) $selectedEquipement === (string) $equipement->id)>
                                                            {{ $equipement->nom }} (Stock disponible: {{ $equipement->getQuantiteDisponible() }})
                                                        </option>
                                                    @endforeach
                                                </optgroup>
                                            @endif
                                        @endforeach
                                    </select>
                                    <div class="form-text stock-info">
                                        @if ($stockDisponible !== null)
                                            Stock disponible actuellement : {{ $stockDisponible }}
                                        @else
                                            Sélectionnez un équipement pour voir le stock disponible.
                                        @endif
                                    </div>
                                    @error('equipements.' . $index)
                                        <span class="text-danger d-block mt-1">{{ $message }}</span>
                                    @enderror
                                    @error('equipements')
                                        <span class="text-danger d-block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Date de retour</label>
                                    <input type="date" name="dates_retour[]" class="form-control @error('dates_retour.' . $index) is-invalid @enderror"
                                        value="{{ $oldDatesRetour[$index] ?? '' }}">
                                    <div class="form-text">Optionnelle pour une affectation temporaire.</div>
                                    @error('dates_retour.' . $index)
                                        <span class="text-danger d-block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Quantité à affecter</label>
                                    <input type="number" name="quantites[]"
                                        class="form-control @error('quantites.' . $index) is-invalid @enderror" min="1" required
                                        value="{{ $oldQuantites[$index] ?? '' }}">
                                    @error('quantites.' . $index)
                                        <span class="text-danger d-block mt-1">{{ $message }}</span>
                                    @enderror
                                    @error('quantites')
                                        <span class="text-danger d-block mt-1">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-danger btn-sm remove-btn">Supprimer</button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <button type="button" class="btn btn-outline-primary mb-3" id="add-equipement">
                        <i class="mdi mdi-plus"></i> Ajouter un équipement
                    </button>

                    <div class="text-end">
                        <button type="submit" class="btn btn-success" @disabled(!$hasEquipementDispo)>Affecter</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <template id="equipement-row-template">
        <div class="equipement-item row mb-3">
            <div class="col-md-4">
                <label class="form-label">Équipement</label>
                <select name="equipements[]" class="form-select equipement-select" required>
                    <option value="">-- Sélectionner un équipement --</option>
                    {!! $optionsHtml !!}
                </select>
                <div class="form-text stock-info">Sélectionnez un équipement pour voir le stock disponible.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date de retour</label>
                <input type="date" name="dates_retour[]" class="form-control">
                <div class="form-text">Optionnelle pour une affectation temporaire.</div>
            </div>
            <div class="col-md-3">
                <label class="form-label">Quantité à affecter</label>
                <input type="number" name="quantites[]" class="form-control" min="1" required>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm remove-btn">Supprimer</button>
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

            function refreshRemoveButtons() {
                const rows = wrapper.querySelectorAll('.equipement-item');

                rows.forEach((row) => {
                    const button = row.querySelector('.remove-btn');

                    if (button) {
                        button.disabled = rows.length === 1;
                    }
                });
            }

            function updateStockInfo(row) {
                const select = row.querySelector('.equipement-select');
                const info = row.querySelector('.stock-info');
                const option = select?.selectedOptions?.[0];
                const stock = option?.dataset?.stockDisponible;

                if (!info) {
                    return;
                }

                if (stock) {
                    info.textContent = `Stock disponible actuellement : ${stock}`;
                    return;
                }

                info.textContent = 'Sélectionnez un équipement pour voir le stock disponible.';
            }

            function addEquipementField() {
                const clone = template.content.firstElementChild.cloneNode(true);
                wrapper.appendChild(clone);
                updateStockInfo(clone);
                refreshRemoveButtons();
            }

            wrapper.querySelectorAll('.equipement-item').forEach(updateStockInfo);
            refreshRemoveButtons();

            if (addButton) {
                addButton.addEventListener('click', addEquipementField);
            }

            wrapper.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-btn')) {
                    const rows = wrapper.querySelectorAll('.equipement-item');

                    if (rows.length === 1) {
                        return;
                    }

                    e.target.closest('.equipement-item').remove();
                    refreshRemoveButtons();
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
