@extends('layouts.app')

@section('title', 'Contenus')
@section('path_name', 'Contenus')

@section('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" />
<style>
  .text-nowrap {
    width: 250px;
  }

  .create-btn {
    position: absolute;
    right: 100px;
  }

  .empty-state {
    text-align: center;
    padding: 4rem 2rem;
  }

  .empty-state-icon {
    font-size: 4rem;
    color: #dee2e6;
    margin-bottom: 1rem;
  }

  .empty-state h3 {
    color: #495057;
    margin-bottom: 0.5rem;
  }

  .empty-state p {
    color: #6c757d;
    margin-bottom: 1.5rem;
  }

  .status-badge {
    padding: 0.25rem 0.75rem;
    border-radius: 20px;
    font-size: 0.75rem;
    font-weight: 500;
  }

  .status-publié {
    background-color: #d4edda;
    color: #155724;
  }

  .status-en-attente {
    background-color: #fff3cd;
    color: #856404;
  }

  .status-brouillon {
    background-color: #e2e3e5;
    color: #383d41;
  }
</style>
@endsection

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h3 class="card-title mb-0">Liste des contenus</h3>
    <a href="{{ route('contenus.create') }}" class="create-btn btn btn-primary btn-sm">Créer</a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table id="contenus-table" class="table table-striped table-bordered table-sm" style="width:100%">
        <thead>
          <tr>
            <th>Titre</th>
            <th>Type</th>
            <th>Région</th>
            <th>Auteur</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($contenus as $contenu)
            <tr>
              <td>{{ $contenu->titre ?? '' }}</td>
              <td>{{ $contenu->typeContenu->nom_contenu ?? 'N/A' }}</td>
              <td>{{ $contenu->region->nom_region ?? 'N/A' }}</td>
              <td>{{ $contenu->auteur->getFullName() }}</td>
              <td>
                <span class="status-badge status-{{ str_replace(' ', '-', strtolower($contenu->statut ?? '')) }}">
                  {{ ucfirst($contenu->statut ?? '') }}
                </span>
              </td>
              <td class="text-nowrap">
                <a href="{{ route('contenus.show', $contenu) }}" class="btn btn-sm btn-outline-warning"><i class="fas fa-eye"></i></a>
                <a href="{{ route('contenus.edit', $contenu) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="6">
                <div class="empty-state">
                  <div class="empty-state-icon">
                    <i class="bi bi-file-earmark"></i>
                  </div>
                  <h3>Aucun contenu enregistré</h3>
                  <p>Il n'y a pas encore de contenu dans la base de données.</p>
                  <a href="{{ route('contenus.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>Créer du contenu
                  </a>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js" crossorigin="anonymous"></script>
<script>
  $(function() {
    const contenuCount = {{ count($contenus) }};
    if (contenuCount > 0) {
      $('#contenus-table').DataTable({
        fixedHeader: true,
        pageLength: 10,
        lengthMenu: [10, 25, 50, 100],
        columnDefs: [
          { orderable: false, targets: -1 }
        ],
        language: {
          url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/fr-FR.json"
        }
      });
    }
  });
</script>
@endsection
