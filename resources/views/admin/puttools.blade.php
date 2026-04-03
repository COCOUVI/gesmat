@extends('admin.layouts.adminlay')
@section('content')
    <div class="row">
        <div class="col-12 grid-margin">
            @session('success')
                <div class="alert alert-green">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
                    {{ $value }}
                </div>
            @endsession

            <!-- Formulaire de modification d'équipement -->
            <div class="card form-card">
                <div class="form-header">
                    <div class="d-flex align-items-center">
                        <div class="form-icon">
                            <i class="mdi mdi-laptop"></i>
                        </div>
                        <div>
                            <h4 class="mb-0">Modifier l'équipement</h4>
                            <p class="mb-0">Modifiez les détails de l'équipement dans l'inventaire</p>
                        </div>
                    </div>
                </div>
                <div class="form-body">
                    <form method="POST" action="{{ route('putTool', $equipement->id) }}" enctype="multipart/form-data">
                        @method('PUT')
                        @csrf
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="equipmentName" class="form-label required-label">Nom de l'équipement</label>
                                    <input type="text" class="form-control" id="equipmentName"
                                        placeholder="Entrez le nom de l'équipement" required name="nom"
                                        value="{{ $equipement->nom }}">
                                    @error('nom')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="equipmentBrand" class="form-label">Marque</label>
                                    <input type="text" class="form-control" id="equipmentBrand"
                                        placeholder="Entrez la marque" name="marque" value="{{ $equipement->marque }}">
                                    @error('marque')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="equipmentCategory" class="form-label required-label">Catégorie</label>
                                    <select class="form-select" id="equipmentCategory" required name="categorie_id">
                                        <option value="" disabled>Sélectionnez une catégorie</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}"
                                                {{ $equipement->categorie_id == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('categorie_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="equipmentDescription" class="form-label">Description</label>
                                    <textarea class="form-control" id="equipmentDescription" rows="3"
                                        placeholder="Entrez une description de l'équipement" name="description">{{ $equipement->description }}</textarea>
                                    @error('description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="acquisitionDate" class="form-label">Date d'acquisition</label>
                                    <input type="date" class="form-control" id="acquisitionDate" name="date_acquisition"
                                        value="{{ $equipement->date_acquisition }}">
                                    @error('date_acquisition')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="equipmentImage" class="form-label">Image de l'équipement</label>
                                    <input type="file" class="form-control" id="equipmentImage" accept="image/*"
                                        name="image_path" onchange="previewImage(this)" value="">
                                    @error('image_path')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    @if ($equipement->image_path)
                                        <div class="mt-2">
                                            <small class="text-muted">Image actuelle :</small>
                                            <img id="imagePreview" src="{{$equipement->image_path}}"
                                                alt="Image actuelle" class="img-thumbnail mt-1"
                                                style="max-width: 150px; max-height: 150px; display: block;">
                                        </div>
                                    @else
                                        <img id="imagePreview" src="" alt="Aperçu de l'image"
                                            class="img-thumbnail mt-2"
                                            style="max-width: 150px; max-height: 150px; display: none;">
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="equipmentQuantity" class="form-label required-label">Quantité disponible</label>
                                    <input type="number" class="form-control" id="equipmentQuantity"
                                        name="quantite" min="1" required placeholder="Ex : 10"
                                        value="{{ old('quantite', $equipement->quantite) }}">
                                    @error('quantite')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="criticalThreshold" class="form-label required-label">Seuil critique</label>
                                    <input type="number" class="form-control" id="criticalThreshold"
                                        name="seuil_critique" min="0" required placeholder="Ex : 1"
                                        value="{{ old('seuil_critique', $equipement->seuil_critique ?? 1) }}">
                                    @error('seuil_critique')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>


                        </div>

                        <div class="form-footer">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('ShowToolpage') }}" class="btn btn-reset">
                                    <i class="mdi mdi-arrow-left me-1"></i> Retour
                                </a>
                                <div>
                                    <button type="reset" class="btn btn-reset me-3">
                                        <i class="mdi mdi-refresh me-1"></i> Réinitialiser
                                    </button>
                                    <button type="submit" class="btn btn-submit">
                                        <i class="mdi mdi-content-save me-1"></i> Enregistrer les modifications
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Fin du formulaire de modification d'équipement -->
        </div>
    </div>
@endsection
