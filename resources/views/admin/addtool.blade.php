@extends('admin.layouts.adminlay')
@section('content')
    <div class="container-fluid mt-4 px-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show">
                        <strong>Veuillez corriger les erreurs suivantes :</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                    </div>
                @endif

                <div class="card shadow">
                    <div class="card-header bg-primary text-white d-flex align-items-center">
                        <i class="mdi mdi-laptop me-2 fs-4"></i>
                        <div>
                            <h5 class="mb-0">Ajouter un nouvel équipement</h5>
                            <small>Remplissez les détails de l'équipement à ajouter</small>
                        </div>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('addTool') }}" enctype="multipart/form-data">
                            @method('post')
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="equipmentName" class="form-label required-label">Nom de l'équipement</label>
                                    <input type="text" class="form-control" name="nom" id="equipmentName" required
                                        placeholder="Ex: Ordinateur HP" value="{{ old('nom') }}">
                                    @error('nom')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="equipmentBrand" class="form-label">Marque</label>
                                    <input type="text" class="form-control" name="marque" id="equipmentBrand"
                                        placeholder="Ex: Dell, HP, Lenovo" required value="{{ old('marque') }}">
                                    @error('marque')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="equipmentCategory" class="form-label required-label">Catégorie</label>
                                    <select class="form-select" name="categorie_id" id="equipmentCategory" required>
                                        <option value="" selected disabled>Choisissez une catégorie</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}" @selected((string) old('categorie_id') === (string) $cat->id)>{{ $cat->nom }}</option>
                                        @endforeach
                                    </select>
                                    @error('categorie_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <label for="equipmentDescription" class="form-label">Description</label>
                                    <textarea class="form-control" name="description" id="equipmentDescription" rows="3"
                                        placeholder="Ex: Ordinateur portable Core i5, 8Go RAM, SSD 256Go" required>{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="acquisitionDate" class="form-label">Date d'acquisition</label>
                                    <input type="date" class="form-control" name="date_acquisition" id="acquisitionDate" required value="{{ old('date_acquisition') }}">
                                    @error('date_acquisition')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="equipmentImage" class="form-label required-label">Image</label>
                                    <input type="file" class="form-control" name="image_path" id="equipmentImage"
                                        accept="image/*" required>
                                    @error('image_path')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="equipmentQuantity" class="form-label required-label">Quantité</label>
                                    <input type="number" min="1" class="form-control" name="quantite"
                                        id="equipmentQuantity" required placeholder="Ex: 5" value="{{ old('quantite') }}">
                                    @error('quantite')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="criticalThreshold" class="form-label required-label">Seuil critique</label>
                                    <input type="number" min="0" class="form-control" name="seuil_critique"
                                        id="criticalThreshold" required placeholder="Ex: 1" value="{{ old('seuil_critique', 1) }}">
                                    @error('seuil_critique')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                {{-- Section : Qui dépose cet équipement --}}
                                <div class="col-12 mt-4">
                                    <div class="border-top pt-4">
                                        <h6 class="mb-3"><i class="mdi mdi-account-check me-2"></i>Qui dépose cet équipement ? (Optionnel)</h6>
                                        <p class="text-muted small">Renseignez la provenance du matériel pour traçabilité</p>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="deposantSelect" class="form-label">Sélectionner un déposant</label>
                                    <select class="form-select" name="deposant_id" id="deposantSelect">
                                        <option value="">-- Aucun (libre/anonyme) --</option>
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

                                <div class="col-md-6">
                                    <label for="deposantNomLibre" class="form-label">Ou renseigner le nom</label>
                                    <input type="text" class="form-control" name="deposant_nom_libre" id="deposantNomLibre"
                                        placeholder="Ex: Fournisseur XYZ, Jean Dupont..." value="{{ old('deposant_nom_libre') }}">
                                    <small class="text-muted">À remplir si la personne n'est pas dans la liste</small>
                                    @error('deposant_nom_libre')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mt-4">
                                <div class="col-12 d-flex flex-column flex-sm-row justify-content-end align-items-stretch gap-2">
                                    <button type="reset" class="btn btn-outline-secondary">
                                        <i class="mdi mdi-refresh me-1"></i> Réinitialiser
                                    </button>
                                    <button type="submit" class="btn btn-success">
                                        <i class="mdi mdi-plus-circle me-1"></i> Ajouter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
{{-- telechargement automatique du pdf--}}
@push('scripts')
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
