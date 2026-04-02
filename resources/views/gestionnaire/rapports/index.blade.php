@extends('admin.layouts.adminlay')
@section('content')
    <div class="container-fluid py-4">
        <!-- Titre -->
        <div class="row mb-4">
            <div class="col-12">
                <h2 class="h3 text-dark fw-bold mb-0">
                    <i class="fas fa-file-alt text-primary me-2"></i>
                    Mes Rapports
                </h2>
            </div>
        </div>

        <!-- Message de succès -->
        @if (session('success'))
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                </div>
            </div>
        @endif

        <!-- Tableau dans une carte -->
        @if ($rapports->count() > 0)
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 smart-data-table" data-table-title="les rapports">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="px-4 py-3 fw-semibold text-muted">#</th>
                                            <th class="px-4 py-3 fw-semibold text-muted">
                                                <i class="fas fa-calendar me-1"></i> Date
                                            </th>
                                            <th class="px-4 py-3 fw-semibold text-muted">
                                                <i class="fas fa-file-text me-1"></i> Contenu (extrait)
                                            </th>
                                            <th class="px-4 py-3 fw-semibold text-muted text-center">
                                                <i class="fas fa-download me-1"></i> PDF
                                            </th>
                                            <th class="px-4 py-3 fw-semibold text-muted text-center">
                                                <i class="fas fa-trash me-1"></i> Supprimer
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($rapports as $rapport)
                                            <tr class="align-middle">
                                                <td class="px-4 py-3">
                                                    <span class="badge bg-light text-dark fw-semibold px-3 py-2">
                                                        {{ $rapport->id }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div>
                                                        <div class="fw-semibold text-dark">
                                                            {{ $rapport->created_at->format('d/m/Y H:i') }}
                                                        </div>
                                                        <small class="text-muted">
                                                            {{ $rapport->created_at->diffForHumans() }}
                                                        </small>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3">

                                                    <!-- <a href="#"  -->
                                                    <a href="{{ route('gestionnaire.rapports.show', $rapport->id) }}"
                                                        class="text-dark d-inline-flex align-items-center"
                                                        style="cursor:pointer; max-width: 400px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; text-decoration: none;">
                                                        <i class="fas fa-eye me-1 text-primary"></i>
                                                        <span>{{ Str::limit($rapport->contenu, 50) }}</span>
                                                    </a>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <a href="{{ route('gestionnaire.rapports.download', $rapport->id) }}"
                                                        target="_blank" class="btn btn-primary btn-sm px-3 py-2">
                                                        <i class="fas fa-download me-1"></i> Télécharger
                                                    </a>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <form action="{{ route('gestionnaire.rapports.destroy', $rapport->id) }}"
                                                        method="POST"
                                                        onsubmit="return confirm('Voulez-vous vraiment supprimer ce rapport ?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm px-3 py-2">
                                                            <i class="fas fa-trash me-1"></i> Supprimer
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <div class="alert alert-info text-center">
                Aucune Rapport pour le moment.
            </div>
        @endif
    </div>

    <!-- Modale Bootstrap pour afficher le contenu complet -->
    <div class="modal fade" id="modalContenu" tabindex="-1" aria-labelledby="modalContenuLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalContenuLabel">Contenu complet du rapport</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body" id="modalContenuBody" style="white-space: pre-wrap;">
                    <!-- Contenu injecté par JS -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                </div>
            </div>
        </div>
    </div>

    <style>
        .card {
            border-radius: 12px;
            overflow: hidden;
        }

        .table> :not(caption)>*>* {
            padding: 1rem;
            border-color: #f1f3f4;
        }

        .contenu-extrait:hover {
            color: rgb(0, 201, 27);
            /* bootstrap blue */
            text-decoration: underline;
        }

        .table-hover>tbody>tr:hover>td {
            background-color: rgba(13, 110, 253, 0.04);
        }

        .badge {
            border-radius: 8px;
        }

        .btn {
            border-radius: 8px;
            font-weight: 500;
        }

        .alert {
            border-radius: 10px;
        }

        .table thead th {
            background-color: #f8f9fa !important;
            border-bottom: 2px solid #e9ecef;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
    </style>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var modalContenu = document.getElementById('modalContenu');
            var modalContenuBody = document.getElementById('modalContenuBody');

            document.querySelectorAll('.contenu-extrait').forEach(function(el) {
                el.addEventListener('click', function() {
                    var contenu = this.getAttribute('data-contenu');
                    modalContenuBody.textContent = contenu;
                });
            });
        });
    </script>
@endpush
