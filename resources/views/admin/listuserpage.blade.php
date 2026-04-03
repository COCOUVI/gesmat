@extends('admin.layouts.adminlay')

@section('content')
    <div class="row">
        <!-- Message de succès -->
        @if (session('deleted'))
            <div class="col-lg-12">
                <div
                    class="alert alert-success shadow-sm d-flex align-items-center justify-content-between px-4 py-3 rounded mb-4">
                    <div class="d-flex align-items-center">
                        <i class="mdi mdi-check-circle fs-4 me-2 text-success"></i>
                        <span class="text-success fw-semibold">{{ session('deleted') }}</span>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fermer"></button>
                </div>
            </div>
        @endif

        <!-- Tableau des utilisateurs -->
        <div class="col-lg-12 grid-margin stretch-card">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Liste des utilisateurs</h4>
                    <p class="card-description">
                        Utilisez les boutons pour <code>éditer</code> ou <code>supprimer</code> un utilisateur.
                    </p>
                    <div class="table-responsive">
                        <table class="table table-striped align-middle smart-data-table" data-table-title="les utilisateurs">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Email</th>
                                    <th>Poste</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td>{{ $user->nom }}</td>
                                        <td>{{ $user->prenom }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->poste }}</td>
                                        <td class="text-center">
                                            <a href="{{ route('edituser', $user->id) }}"
                                                class="btn btn-sm btn-outline-primary" title="Éditer">
                                                <i class="mdi mdi-pencil"></i>
                                            </a>
                                            <a href="{{ route('deleteuser', $user->id) }}"
                                                class="btn btn-sm btn-outline-danger" title="Supprimer">
                                                <i class="mdi mdi-delete"></i>
                                            </a>
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
@endsection
