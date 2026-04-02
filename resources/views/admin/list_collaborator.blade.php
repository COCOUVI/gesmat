@extends('admin.layouts.adminlay')
@section('content')
    <div class="container mt-5">
        @if (session('remove'))
            <div
                class="alert alert-danger alert-dismissible fade show d-flex justify-content-between align-items-center px-4 py-3 rounded mb-4 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="mdi mdi-delete-forever-outline fs-4 me-2 text-danger"></i>
                    <span class="fw-semibold text-danger">{{ session('remove') }}</span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
            </div>
        @endif
        <div class="card shadow-lg">
            <div class="card-header bg-dark text-white">
                <h4 class="mb-0">Liste des Collaborateurs Externes</h4>
            </div>
            <div class="card-body">
                @if ($collaborateurs->isEmpty())
                    <p class="text-center text-muted">Aucun collaborateur externe trouvé.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle smart-data-table" data-table-title="les collaborateurs">
                            <thead class="table-light">
                                <tr>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Carte</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($collaborateurs as $collaborateur)
                                    <tr>
                                        <td>{{ $collaborateur->nom }}</td>
                                        <td>{{ $collaborateur->prenom }}</td>
                                        <td>
                                            @if ($collaborateur->carte_chemin)
                                                <img src="{{ $collaborateur->carte_chemin }}" alt="Carte" width="80"
                                                    class="img-thumbnail" style="cursor: pointer;"
                                                    onclick="showImagePopup('{{ $collaborateur->carte_chemin }}', 'Carte de {{ $collaborateur->prenom }} {{ $collaborateur->nom }}')">
                                            @else
                                                <span class="text-muted">Non fournie</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('collaborateurs.destroy', $collaborateur->id) }}"
                                                method="POST" />
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm">
                                                <i class="mdi mdi-delete"></i> Supprimer
                                            </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-2">{{ $collaborateurs->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('popups')
    <!-- Même popup que pour les équipements -->
    <div class="image-popup" id="imagePopup">
        <div class="image-popup-content">
            <span class="close-popup" onclick="closeImagePopup()">&times;</span>
            <img id="popupImage" src="" alt="Image agrandie">
            <div class="image-popup-title" id="popupImageTitle"></div>
        </div>
    </div>
@endpush

@push('styles')
    <style>
        /* Styles pour la popup d'image */
        .image-popup {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.8);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .image-popup-content {
            position: relative;
            max-width: 90%;
            max-height: 90%;
            text-align: center;
        }

        .image-popup-content img {
            max-width: 100%;
            max-height: 80vh;
            border: 3px solid white;
        }

        .close-popup {
            position: absolute;
            top: -40px;
            right: 0;
            color: white;
            font-size: 35px;
            font-weight: bold;
            cursor: pointer;
        }

        .image-popup-title {
            color: white;
            margin-top: 15px;
            font-size: 18px;
        }
    </style>
@endpush

@push('scripts')
    <script>
        function showImagePopup(imageSrc, title) {
            document.getElementById('popupImage').src = imageSrc;
            document.getElementById('popupImageTitle').textContent = title;
            document.getElementById('imagePopup').style.display = 'flex';
        }

        function closeImagePopup() {
            document.getElementById('imagePopup').style.display = 'none';
        }

        // Fermer la popup si on clique en dehors de l'image
        document.getElementById('imagePopup').addEventListener('click', function(e) {
            if (e.target === this) {
                closeImagePopup();
            }
        });
    </script>
@endpush
