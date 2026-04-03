@extends('admin.layouts.adminlay')

@push('styles')
    <style>
        table th,
        table td {
            border-right: 1px solid #dee2e6;
            border-left: 1px solid #dee2e6;
        }

        table th:last-child,
        table td:last-child {
            border-right: none;
        }

        table {
            border-collapse: collapse;
        }

        table thead th {
            background-color: #f8f9fa;
        }
    </style>
@endpush

@section('content')
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
    <h4 class="mb-4">Liste des Bons</h4>
    <div class="table-responsive">
        <table class="table table-striped table-hover align-middle smart-data-table" data-table-title="les bons">
            <thead>
                <tr>
                    <th>Numéro</th>
                    <th>Date de création</th>
                    <th>Type</th>
                    <th>Téléchargement</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($bons as $bon)
                    <tr>
                        <td>{{ $bon->id }}</td>
                        <td>{{ $bon->created_at->format('d/m/Y H:i') }}</td>
                        <td>{{ $bon->statut }}</td>
                        <td>
                            @if ($bon->fichier_pdf)
                                <a href="{{ asset('storage/' . $bon->fichier_pdf) }}" class="btn btn-sm btn-primary"
                                    download>
                                    Télécharger
                                </a>
                            @else
                                <span class="text-muted">Non disponible</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5"><span>Aucun bon disponible pour le moment</span></td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
