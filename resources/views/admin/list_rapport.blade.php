@extends('admin.layouts.adminlay')
@section('content')
    <div class="container mt-5">
        <h4 class="mb-4 fw-bold text-primary">Liste des Rapports</h4>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if ($rapports->isEmpty())
            <div class="alert alert-info">Aucun rapport disponible.</div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-hover shadow-sm align-middle">
                    <thead class="table-primary">
                        <tr>
                            <th>Date de création</th>
                            <th>Fichier PDF</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rapports as $rapport)
                            <tr>
                                <td>{{ $rapport->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <a href="{{ asset('storage/' . $rapport->file_path) }}" download
                                        class="btn btn-outline-primary btn-sm">
                                        ⬇️ Télécharger
                                    </a>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
           <div class="mt-2">{{ $rapports->links() }}</div>
        @endif
    </div>
@endsection
