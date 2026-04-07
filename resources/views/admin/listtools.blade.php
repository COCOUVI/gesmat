@extends('admin.layouts.adminlay')
@push('styles')
    <style>
        /* Supprimer tout effet de focus visuel */
        a:focus,
        a:focus-visible {
            outline: none !important;
            box-shadow: none !important;
        }

        /* Pour tous les liens contenant uniquement des icônes */
        .action-icon {
            color: inherit;
            /* garder la couleur normale */
            text-decoration: none;
            /* enlever tout soulignement */
            transition: color 0.2s ease-in-out;
            font-size: 18px;
            margin-right: 8px;
            cursor: pointer;
        }

        .action-icon:hover {
            color: #007bff;
            /* couleur au survol */
        }

        /* Supprime la bordure bleue sur icône quand on clique */
        .action-icon:focus {
            outline: none;
            box-shadow: none;
        }
    </style>
@endpush

@section('content')
    <div class="page-header">
        <h3 class="page-title">
            <span class="page-title-icon bg-gradient-toolzy-primary text-white mr-2">
                <i class="mdi mdi-laptop"></i>
            </span>
            Inventaire des équipements
        </h3>
        <nav aria-label="breadcrumb">
            <ul class="breadcrumb">
                <li class="breadcrumb-item"><a href="#">Gestion des équipements</a></li>
                <li class="breadcrumb-item active" aria-current="page">Inventaire</li>
            </ul>
        </nav>
    </div>

    <div class="row">
        <div class="col-12">
            @if (session('deleted'))
                <div
                    class="alert alert-success shadow-sm d-flex align-items-center justify-content-between px-4 py-3 rounded mb-4">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-check-circle-outline fs-4 me-2 text-success"></i>
                        <span class="text-success fw-semibold">{{ session('deleted') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            @endif
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-4">Liste des équipements</h4>

                    <div class="table-responsive">
                        <table class="table smart-data-table" data-table-title="les équipements">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Description</th>
                                    <th>Catégorie</th>
                                    <th>Qte</th>
                                    <th>Statut</th>
                                    <th>Stock</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="equip-table-body">
                                @forelse ($equipements as $equip)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="/{{ $equip->image_path }}" alt="{{ $equip->nom }}"
                                                    class="equipment-img"
                                                    onclick="showImagePopup('{{ $equip->image_path }}', '{{ $equip->nom }}')">
                                                <span>{{ $equip->nom }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $equip->description }}</td>
                                        <td>{{ $equip->categorie->nom }}</td>
                                        <td>{{ $equip->quantite }}</td>
                                        <td>
                                            @php
                                                $disponible = $equip->getQuantiteDisponible();
                                                $enPanne = $equip->getQuantiteEnPanne();
                                                
                                                if ($disponible > 0 && $enPanne == 0) {
                                                    $etatBadge = ['Disponible', 'success'];
                                                } elseif ($disponible > 0 && $enPanne > 0) {
                                                    $etatBadge = ['Partiellement en panne', 'warning'];
                                                } else {
                                                    $etatBadge = ['Non disponible', 'danger'];
                                                }
                                            @endphp
                                            <span class="badge bg-{{ $etatBadge[1] }}">
                                                {{ $etatBadge[0] }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $qte = $equip->quantite;
                                                if ($qte <= 2) {
                                                    $status = ['Insuffisant', 'danger']; // rouge
                                                } elseif ($qte <= 4) {
                                                    $status = ['Faible', 'warning']; // orange
                                                } elseif ($qte <= 10) {
                                                    $status = ['Moyen', 'info']; // bleu clair
                                                } else {
                                                    $status = ['Suffisant', 'success']; // vert
                                                }
                                            @endphp

                                            <span class="badge badge-{{ $status[1] }}">
                                                {{ $status[0] }}
                                            </span>
                                        </td>

                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <a href="{{ route('putToolpage', $equip->id) }}"
                                                    class="text-decoration-none">
                                                    <i class="mdi mdi-pencil edit-icon action-icon fs-5"
                                                        title="Modifier"></i>
                                                </a>
                                                <a href="#" class="text-decoration-none"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#replenishModal{{ $equip->id }}"
                                                    title="Réapprovisionner">
                                                    <i class="mdi mdi-plus-box action-icon fs-5" style="color: #28a745;"></i>
                                                </a>
                                                <a href="{{ route('DeleteTool', $equip->id) }}"
                                                    class="text-decoration-none">
                                                    <i class="mdi mdi-delete delete-icon action-icon fs-5"
                                                        title="Supprimer"></i>
                                                </a>
                                            </div>
                                        </td>

                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            Aucun équipement existant.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Find all anonymous checkboxes in replenishment modals
            document.querySelectorAll('.is-anonymous-check').forEach(checkbox => {
                const equipId = checkbox.id.replace('isAnonymous', '');
                const selectGroup = document.getElementById('selectGroup' + equipId);
                const manualGroup = document.getElementById('manualGroup' + equipId);
                const deposantSelect = document.getElementById('deposantSelect' + equipId);

                function updateDisplay() {
                    if (checkbox.checked) {
                        selectGroup.style.display = 'none';
                        manualGroup.classList.remove('d-none');
                        deposantSelect.value = '';
                        deposantSelect.name = '';
                    } else {
                        selectGroup.style.display = 'block';
                        manualGroup.classList.add('d-none');
                        deposantSelect.name = 'deposant_id';
                        const nomInput = manualGroup.querySelector('input[name="deposant_anonymous_nom"]');
                        const prenomInput = manualGroup.querySelector('input[name="deposant_anonymous_prenom"]');
                        if (nomInput) nomInput.value = '';
                        if (prenomInput) prenomInput.value = '';
                    }
                }

                checkbox.addEventListener('change', updateDisplay);
                updateDisplay(); // Initialize on page load
            });
        });
    </script>
@endpush

@push('popups')
    {{-- Modales de réapprovisionnement pour chaque équipement --}}
    @forelse ($equipements as $equip)
        <div class="modal fade" id="replenishModal{{ $equip->id }}" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title">
                            <i class="mdi mdi-plus-box me-2"></i>Réapprovisionner : {{ $equip->nom }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <form action="{{ route('replenish.equipment') }}" method="POST">
                        @csrf
                        <input type="hidden" name="equipement_id" value="{{ $equip->id }}">
                        
                        <div class="modal-body">
                            {{-- Information équipement (readonly) --}}
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Équipement</label>
                                <input type="text" class="form-control" 
                                       value="{{ $equip->nom }} - {{ $equip->marque }}" disabled>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Stock actuel</label>
                                <input type="number" class="form-control" 
                                       value="{{ $equip->quantite }}" disabled>
                            </div>

                            {{-- Quantité à ajouter --}}
                            <div class="mb-3">
                                <label for="quantiteReplenish{{ $equip->id }}" class="form-label fw-semibold required-label">
                                    Quantité à ajouter
                                </label>
                                <input type="number" name="quantite" id="quantiteReplenish{{ $equip->id }}" 
                                       class="form-control @error('quantite') is-invalid @enderror"
                                       min="1" required placeholder="Ex: 5" value="{{ old('quantite') }}">
                                @error('quantite')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Qui dépose le matériel --}}
                            <div class="mb-3">
                                <label class="form-label">
                                    Qui dépose le matériel ? (Optionnel)
                                </label>
                                <div class="form-check mb-2">
                                    <input type="hidden" name="is_anonymous" value="0">
                                    <input type="checkbox" class="form-check-input is-anonymous-check" name="is_anonymous" value="1" id="isAnonymous{{ $equip->id }}">
                                    <label class="form-check-label" for="isAnonymous{{ $equip->id }}">
                                        <small>Anonyme - Remplir manuellement</small>
                                    </label>
                                </div>

                                {{-- Mode: Sélection (par défaut) --}}
                                <div class="deposant-select-group" id="selectGroup{{ $equip->id }}">
                                    <select name="deposant_id" class="form-select deposant-select @error('deposant_id') is-invalid @enderror"
                                            id="deposantSelect{{ $equip->id }}">
                                        <option value="">-- Choisir une personne --</option>
                                        <optgroup label="Employés">
                                            @forelse ($employes as $emp)
                                                <option value="user_{{ $emp->id }}" @selected(old('deposant_id') === "user_$emp->id")>
                                                    {{ $emp->nom }} {{ $emp->prenom }}
                                                </option>
                                            @empty
                                            @endforelse
                                        </optgroup>
                                        <optgroup label="Collaborateurs externes">
                                            @forelse ($collaborateurs as $collab)
                                                <option value="collab_{{ $collab->id }}" @selected(old('deposant_id') === "collab_$collab->id")>
                                                    {{ $collab->nom }}
                                                </option>
                                            @empty
                                            @endforelse
                                        </optgroup>
                                    </select>
                                    @error('deposant_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Mode: Entrée manuelle (anonyme) --}}
                                <div class="deposant-manual-group d-none" id="manualGroup{{ $equip->id }}">
                                    <input type="text" name="deposant_anonymous_nom" placeholder="Nom" 
                                           class="form-control mb-2 @error('deposant_anonymous_nom') is-invalid @enderror"
                                           value="{{ old('deposant_anonymous_nom') }}">
                                    <input type="text" name="deposant_anonymous_prenom" placeholder="Prénom" 
                                           class="form-control @error('deposant_anonymous_prenom') is-invalid @enderror"
                                           value="{{ old('deposant_anonymous_prenom') }}">
                                    @error('deposant_anonymous_nom')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                    @error('deposant_anonymous_prenom')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            {{-- Ou renseigner le nom --}}
                            {{-- REMOVED: Functionality moved to anonymous mode toggle --}}
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-success">
                                <i class="mdi mdi-check me-1"></i>Confirmer réappro
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @empty
    @endforelse

    <div class="image-popup" id="imagePopup">
        <div class="image-popup-content">
            <span class="close-popup" onclick="closeImagePopup()">&times;</span>
            <img id="popupImage" src="" alt="Image agrandie">
            <div class="image-popup-title" id="popupImageTitle"></div>
        </div>
    </div>
@endpush
