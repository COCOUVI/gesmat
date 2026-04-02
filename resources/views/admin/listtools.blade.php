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

                    @if ($equipements->count())
                        <div class="row mt-4">
                            <div class="col-12 col-md-6 text-center text-md-start mb-2 mb-md-0">
                                <span class="text-muted">
                                    Affichage de {{ $equipements->firstItem() }} à
                                    {{ $equipements->lastItem() }} sur {{ $equipements->total() }} équipements
                                </span>
                            </div>
                            <div class="col-12 col-md-6 d-flex justify-content-center justify-content-md-end">
                                <div class="pagination-wrapper">
                                    {{ $equipements->links() }}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
@push('popups')
    <div class="image-popup" id="imagePopup">
        <div class="image-popup-content">
            <span class="close-popup" onclick="closeImagePopup()">&times;</span>
            <img id="popupImage" src="" alt="Image agrandie">
            <div class="image-popup-title" id="popupImageTitle"></div>
        </div>
    </div>
@endpush
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tableBody = document.getElementById('equip-table-body');

        const searchDesktopInput = document.getElementById('navbar-search');
        const searchMobileInput = document.getElementById('navbar-search-mobile');

        const searchIconDesktop = document.getElementById('search-icon-desktop');
        const searchIconMobile = document.getElementById('search-icon-mobile');

        function filterEquipments(term) {
            const rows = tableBody.getElementsByTagName('tr');
            const query = term.toLowerCase().trim();

            Array.from(rows).forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(query) ? '' : 'none';
            });
        }

        if (searchIconDesktop) {
            searchIconDesktop.addEventListener('click', function () {
                const term = searchDesktopInput?.value || '';
                filterEquipments(term);
            });
        }

        if (searchIconMobile) {
            searchIconMobile.addEventListener('click', function () {
                const term = searchMobileInput?.value || '';
                filterEquipments(term);
            });
        }
    });
</script>


@endpush
