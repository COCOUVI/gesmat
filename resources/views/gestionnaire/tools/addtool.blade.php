@extends('gestionnaire.tools.layouts.gestionlay')
@section('content')
    <div class="row">
        <div class="col-12 grid-margin">

            @if (session('success'))
                <div class="alert alert-green">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">x</button>
                    {{ session('success') }}
                </div>
            @endif

            <!-- Formulaire d'ajout ou de modification d'équipement -->
            <div class="card form-card">
                <div class="form-header">
                    <div class="d-flex align-items-center">
                        <div class="form-icon">
                            <i class="mdi mdi-laptop"></i>
                        </div>
                        <div>
                            <h4 class="mb-0">
                                {{ isset($equipement) ? 'Modifier un équipement' : 'Ajouter un nouvel équipement' }}
                            </h4>
                            <p class="mb-0">
                                {{ isset($equipement) ? 'Modifiez les détails de l’équipement' : 'Remplissez les détails de l’équipement à ajouter à l’inventaire' }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="form-body">
                    <form method="POST"
                        action="{{ isset($equipement) ? route('gestionnaire.tools.update', $equipement->id) : route('storeTool') }}"
                        enctype="multipart/form-data">
                        @csrf
                        @if (isset($equipement))
                            @method('PUT')
                        @endif

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="equipmentName" class="form-label required-label">Nom de l'équipement</label>
                                    <input type="text" class="form-control" id="equipmentName" required name="nom"
                                        value="{{ old('nom') ?? $equipement->nom ?? '' }}"
                                        placeholder="Entrez le nom de l'équipement">
                                    @error('nom')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <x-equipement-etat-select name="etat" label="" :model="isset($equipement) ? $equipement : null" />
                                </div>
                            </div>
                        </div>

                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="equipmentBrand" class="form-label">Marque</label>
                                    <input type="text" class="form-control" id="equipmentBrand" name="marque"
                                        value="{{ old('marque') ?? $equipement->marque ?? '' }}"
                                        placeholder="Entrez la marque">
                                    @error('marque')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="equipmentCategory" class="form-label required-label">Catégorie</label>
                                    <select class="form-select" id="equipmentCategory" required name="categorie_id">
                                        <option value="" disabled {{ !isset($equipement) ? 'selected' : '' }}>Sélectionnez une catégorie</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}"
                                                {{ (old('categorie_id') ?? $equipement->categorie_id ?? '') == $cat->id ? 'selected' : '' }}>
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
                                    <textarea class="form-control" id="equipmentDescription" rows="3" name="description"
                                        placeholder="Entrez une description de l'équipement">{{ old('description') ?? $equipement->description ?? '' }}</textarea>
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
                                        value="{{ old('date_acquisition') ?? $equipement->date_acquisition ?? '' }}">
                                    @error('date_acquisition')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="equipmentImage" class="form-label">Image de l'équipement</label>
                                    <input type="file" class="form-control" id="equipmentImage" accept="image/*"
                                        name="image_path">
                                    @error('image_path')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="equipmentQuantity" class="form-label required-label">Quantité disponible</label>
                                    <input type="number" class="form-control" id="equipmentQuantity"
                                        name="quantite-" min="1" required placeholder="Ex : 10"
                                        value="{{ old('quantite') ?? $equipement->quantite_disponible ?? '' }}">
                                    @error('quantite')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-footer">
                            <div class="d-flex justify-content-end">
                                <button type="reset" class="btn btn-reset me-3">
                                    <i class="mdi mdi-refresh me-1"></i> Réinitialiser
                                </button>
                                <button type="submit" class="btn btn-submit">
                                    <i class="mdi mdi-check-circle me-1"></i>
                                    {{ isset($equipement) ? 'Mettre à jour' : 'Ajouter l\'équipement' }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Fin du formulaire -->
        </div>
    </div>
@endsection
