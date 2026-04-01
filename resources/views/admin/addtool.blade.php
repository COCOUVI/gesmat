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
                                        placeholder="Ex: Ordinateur HP">
                                    @error('nom')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="equipmentBrand" class="form-label">Marque</label>
                                    <input type="text" class="form-control" name="marque" id="equipmentBrand"
                                        placeholder="Ex: Dell, HP, Lenovo" required>
                                    @error('marque')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="equipmentCategory" class="form-label required-label">Catégorie</label>
                                    <select class="form-select" name="categorie_id" id="equipmentCategory" required>
                                        <option value="" selected disabled>Choisissez une catégorie</option>
                                        @foreach ($categories as $cat)
                                            <option value="{{ $cat->id }}">{{ $cat->nom }}</option>
                                        @endforeach
                                    </select>
                                    @error('categorie_id')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-12">
                                    <label for="equipmentDescription" class="form-label">Description</label>
                                    <textarea class="form-control" name="description" id="equipmentDescription" rows="3"
                                        placeholder="Ex: Ordinateur portable Core i5, 8Go RAM, SSD 256Go" required></textarea>
                                    @error('description')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="acquisitionDate" class="form-label">Date d'acquisition</label>
                                    <input type="date" class="form-control" name="date_acquisition" id="acquisitionDate" required>
                                    @error('date_acquisition')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="equipmentImage" class="form-label">Image</label>
                                    <input type="file" class="form-control" name="image_path" id="equipmentImage"
                                        accept="image/*">
                                    @error('image_path')
                                        <div class="text-danger small">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="equipmentQuantity" class="form-label required-label">Quantité</label>
                                    <input type="number" min="1" class="form-control" name="quantite"
                                        id="equipmentQuantity" required placeholder="Ex: 5">
                                    @error('quantite')
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

