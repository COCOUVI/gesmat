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
                    @if ($equipements->count())
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div class="text-muted">
                                Affichage de {{ $equipements->firstItem() }} à
                                {{ $equipements->lastItem() }} sur {{ $equipements->total() }} équipements
                            </div>
                            <div>
                                {{ $equipements->links() }}
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
