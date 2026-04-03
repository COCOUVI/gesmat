@extends("admin.layouts.adminlay")
@section("content")
<div class="container mt-5">
    <h4 class="mb-4 fw-bold text-primary">Liste des Rapports</h4>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($rapports->isEmpty())
        <div class="alert alert-info">Aucun rapport disponible.</div>
    @else
        <div class="table-responsive">
            <table class="table table-bordered table-hover shadow-sm align-middle smart-data-table" data-table-title="les rapports">
                <thead class="table-primary">
                    <tr>
                        <th>Date de création</th>
                        <th>Fichier PDF</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rapports as $rapport)
                        <tr>
                            <td>{{ $rapport->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <a href="{{ asset('storage/' . $rapport->fichier_pdf) }}" target="_blank" class="btn btn-outline-secondary btn-sm">
                                    📄 Voir le rapport
                                </a>
                            </td>
                            <td class="text-center">
                                <form action="" method="POST"/>
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm">
                                        <i class="mdi mdi-delete"></i> Supprimer
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
