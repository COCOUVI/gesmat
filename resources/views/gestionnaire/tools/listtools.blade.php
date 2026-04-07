@extends('gestionnaire.tools.layouts.gestionlay')
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

            {{-- Affichage du message de succès --}}
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
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
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($equipements as $equip)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="/{{ $equip->image_path }}" alt="{{ $equip->nom }}"
                                                    class="equipment-img"
                                                    onclick="showImagePopup('/{{ $equip->image_path }}', '{{ $equip->nom }}')">
                                                <span>{{ $equip->nom }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $equip->description }}</td>
                                        <td>{{ $equip->categorie->nom }}</td>
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
                                            <a href="{{ route('tools.put', $equip->id) }}">
                                                <i class="mdi mdi-pencil edit-icon action-icon" title="Modifier"></i>
                                            </a>

                                            <a href="#" class="text-decoration-none"
                                                data-bs-toggle="modal"
                                                data-bs-target="#replenishModal{{ $equip->id }}"
                                                title="Réapprovisionner">
                                                <i class="mdi mdi-plus-box action-icon" style="color: #28a745; cursor: pointer;"></i>
                                            </a>

                                            <form action="{{ route('gestionnaire.tools.destroy', $equip->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Confirmer la suppression de cet équipement ?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" style="border:none; background:none; padding:0;">
                                                    <i class="mdi mdi-delete delete-icon action-icon" title="Supprimer"></i>
                                                </button>
                                            </form>
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
                                <label for="quantiteReplenish{{ $equip->id }}" class="form-label fw-semibold">
                                    Quantité à ajouter <span class="text-danger">*</span>
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
                                <label for="deposantSelect{{ $equip->id }}" class="form-label">
                                    Qui dépose le matériel ? (Optionnel)
                                </label>
                                <select name="deposant_id" id="deposantSelect{{ $equip->id }}" 
                                        class="form-select @error('deposant_id') is-invalid @enderror">
                                    <option value="">-- Réapprovisionnement anonyme --</option>
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

                            {{-- Ou renseigner le nom --}}
                            <div class="mb-3">
                                <label for="deposantNomLibre{{ $equip->id }}" class="form-label">
                                    Ou renseigner un nom
                                </label>
                                <input type="text" name="deposant_nom_libre" id="deposantNomLibre{{ $equip->id }}" 
                                       class="form-control @error('deposant_nom_libre') is-invalid @enderror"
                                       placeholder="Fournisseur, personne externe..." value="{{ old('deposant_nom_libre') }}">
                                <small class="text-muted">À remplir si la personne n'est pas dans la liste</small>
                                @error('deposant_nom_libre')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
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
